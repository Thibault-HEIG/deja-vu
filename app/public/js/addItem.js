/**
 * addItem.js — Handles eager background removal on file selection.
 *
 * When the user picks a photo, the file is immediately sent to removeBackground.php
 * (our server-side proxy to the ClearBackdrop API). While processing, a spinner
 * replaces the file input area. On success the transparent PNG preview is shown
 * and its server-side temp path is stored in a hidden input, which the main form
 * uses instead of the original file on submit.
 *
 * States: idle → uploading → done | error
 */

const photoInput      = document.getElementById('photo');
const previewImg      = document.getElementById('photo-preview');
const photoStatus     = document.getElementById('photo-status');
const tmpFileInput    = document.getElementById('tmp_file');
const submitBtn       = document.querySelector('[type="submit"]');

photoInput.addEventListener('change', async () => {
    const file = photoInput.files[0];
    if (!file) return;

    // Show a local preview immediately for instant feedback
    previewImg.src     = URL.createObjectURL(file);
    previewImg.hidden  = false;

    setStatus('loading', 'Suppression du fond en cours…');
    submitBtn.disabled = true;

    const formData = new FormData();
    formData.append('photo', file);

    try {
        const response = await fetch('removeBackground.php', {
            method: 'POST',
            body: formData,
        });

        const rawText = await response.text();

        let data;
        try {
            data = JSON.parse(rawText);
        } catch {
            console.error('Réponse non-JSON reçue du serveur:', rawText);
            throw new Error(`Réponse invalide du serveur (voir console) : ${rawText.slice(0, 150)}`);
        }

        if (!response.ok || data.error) {
            throw new Error(data.error ?? `Erreur HTTP ${response.status}`);
        }

        // Replace local preview with the processed transparent PNG from the server
        previewImg.src     = data.tmpFile;
        tmpFileInput.value = data.tmpFile;

        setStatus('done', 'Fond supprimé ✓');
        submitBtn.disabled = false;

    } catch (err) {
        console.error('Erreur traitement photo:', err);
        setStatus('error', `Échec : ${err.message}`);
        // Still allow submission with the original file as fallback
        tmpFileInput.value = '';
        submitBtn.disabled = false;
    }
});

/**
 * Updates the status message below the file input.
 * @param {'idle'|'loading'|'done'|'error'} state
 * @param {string} message
 */
function setStatus(state, message) {
    photoStatus.textContent  = message;
    photoStatus.dataset.state = state;
}
