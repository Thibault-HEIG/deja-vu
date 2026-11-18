/**
 * generateOutfit.js — Seed item picker popup
 *
 * How it works:
 * 1. Clicking the "seed-trigger" button opens a popup overlay.
 * 2. The popup shows all your wardrobe items as a grid of images.
 * 3. Clicking an image selects it: we store the item ID in a hidden <input>
 *    so it gets sent with the form, and we show a thumbnail in the trigger.
 * 4. Clicking "×" or the dark backdrop closes the popup.
 */

// Grab references to the elements we need
const overlay   = document.querySelector('.popup-overlay');   // dark backdrop
const trigger   = document.querySelector('.seed-trigger');    // button that opens popup
const closeBtn  = document.querySelector('.popup-close');     // "×" button
const clearBtn  = document.querySelector('.popup-clear');     // "Aucune sélection" button
const hiddenInput = document.querySelector('#seed-item-id'); // hidden form field
const thumbnail = document.querySelector('.seed-trigger img');// small preview image
const label     = document.querySelector('.seed-trigger span');
const items     = document.querySelectorAll('.popup-grid img'); // all wardrobe images

// -- Open the popup when clicking the trigger --
trigger.addEventListener('click', function () {
    overlay.classList.add('open');
});

// -- Close the popup when clicking "×" --
closeBtn.addEventListener('click', function () {
    overlay.classList.remove('open');
});

// -- Close the popup when clicking the dark backdrop (but not the panel itself) --
// `event.target` is the actual element that was clicked.
// If it's the overlay itself (not something inside the panel), close.
overlay.addEventListener('click', function (event) {
    if (event.target === overlay) {
        overlay.classList.remove('open');
    }
});

// -- Select an item when clicking its image --
// `data-id` and `src` are HTML attributes we read with `dataset` and `src`.
items.forEach(function (img) {
    img.addEventListener('click', function () {
        // Write the item's ID into the hidden input so it's sent with the form
        hiddenInput.value = img.dataset.id;

        // Show a thumbnail preview in the trigger button
        thumbnail.src = img.src;
        trigger.classList.add('has-selection');
        label.textContent = 'Changer le vêtement';

        // Close the popup
        overlay.classList.remove('open');
    });
});

// -- Clear selection --
clearBtn.addEventListener('click', function () {
    hiddenInput.value = '';
    thumbnail.src = '';
    trigger.classList.remove('has-selection');
    label.textContent = 'Sélectionner';
    overlay.classList.remove('open');
});
