/**
 * addItem.js — Handles eager background removal on file selection.
 *
 * When the user selects a photo:
 * 1. Shows an instant local preview.
 * 2. Calls removeBackground.php to strip the background.
 * 3. On success, displays the transparent PNG and stores its temporary path
 *    in a hidden field for form submission.
 * 4. On failure, notifies the user and falls back to uploading the raw photo.
 */

const photoInput   = document.getElementById('photo');
const previewImg   = document.getElementById('photo-preview');
const photoStatus  = document.getElementById('photo-status');
const tmpFileInput = document.getElementById('tmp_file');
const submitBtn    = document.querySelector('[type="submit"]');

photoInput.addEventListener('change', async () => {
    const file = photoInput.files[0];
    if (!file) return;

    // Immediate local preview for responsive UX
    previewImg.src    = URL.createObjectURL(file);
    previewImg.hidden = false;

    setStatus('loading', 'Suppression du fond en cours…');
    submitBtn.disabled = true;

    const formData = new FormData();
    formData.append('photo', file);

    try {
        const response = await fetch('../src/api/removeBackground.php', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (!response.ok || data.error) {
            throw new Error(data.error || 'Erreur lors du traitement de l’image.');
        }

        // Show transparent PNG and set path for form submission
        previewImg.src     = data.tmpFile;
        tmpFileInput.value = data.tmpFile;
        setStatus('done', 'Fond supprimé ✓');

    } catch (err) {
        console.error('Erreur traitement photo:', err);
        setStatus('error', `Échec : ${err.message}`);
        // Fallback: clear temp path so form submits the raw file
        tmpFileInput.value = '';
    } finally {
        submitBtn.disabled = false;
    }
});

/**
 * Updates the status message below the file input.
 * @param {'idle'|'loading'|'done'|'error'} state
 * @param {string} message
 */
function setStatus(state, message) {
    photoStatus.textContent   = message;
    photoStatus.dataset.state = state;
}

