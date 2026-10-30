<?php

const LIMITS = [
    'h' => [0, 360],   // teinte en degrés
    's' => [0, 70],  // saturation en %
    'v' => [20, 90]   // luminosité en %
];

const DAYS_FR = [
    'Dimanche',
    'Lundi',
    'Mardi',
    'Mercredi',
    'Jeudi',
    'Vendredi',
    'Samedi',
];

const MONTHS_FR = [
    'janvier',
    'février',
    'mars',
    'avril',
    'mai',
    'juin',
    'juillet',
    'août',
    'septembre',
    'octobre',
    'novembre',
    'décembre',
];

const CATEGORIES = [
    'top'       => 'Haut',
    'bottom'    => 'Bas',
    'jacket'    => 'Veste',
    'shoes'     => 'Chaussures',
    'accessory' => 'Accessoire',
];

// Types d'images autorisés : type MIME => extension
const ALLOWED_IMAGE_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];
