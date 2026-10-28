    // Limites : on ne pourra choisir que des couleurs entre ces valeurs
const LIMITES = {
    h: [0, 360],   // teinte en degrés
    s: [20, 100],  // saturation en %
    v: [30, 100]   // luminosité en %
};

// Couleur de départ
let h = 180;
let s = 60;
let v = 80;

// On récupère les éléments du HTML grâce à leur id
const canvas = document.getElementById('carre');
const ctx = canvas.getContext('2d');
const zone = document.getElementById('zone');
const rond = document.getElementById('rond');
const hInput = document.getElementById('h');

// Le curseur de teinte utilise les limites choisies
hInput.min = LIMITES.h[0];
hInput.max = LIMITES.h[1];
hInput.value = h;
    
// Convertit HSV (h : 0-360, s et v : 0-100) en [rouge, vert, bleu]
function hsvToRgb(h, s, v) {
    s = s / 100;
    v = v / 100;
    function f(n) {
        const k = (n + h / 60) % 6;
        return Math.round(255 * (v - v * s * Math.max(0, Math.min(k, 4 - k, 1))));
    }
    return [f(5), f(3), f(1)];
}

// Transforme [r, g, b] en texte CSS, par exemple "rgb(255,0,0)"
function css(rgb) {
    return 'rgb(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ')';
}
// Dessine le carré : saturation de gauche à droite, luminosité de haut en bas
function dessinerCarre() {
    const largeur = canvas.width;
    const hauteur = canvas.height;
    const image = ctx.createImageData(largeur, hauteur);

    for (let y = 0; y < hauteur; y++) {
        const lum = LIMITES.v[1] - (LIMITES.v[1] - LIMITES.v[0]) * y / (hauteur - 1);
        for (let x = 0; x < largeur; x++) {
            const sat = LIMITES.s[0] + (LIMITES.s[1] - LIMITES.s[0]) * x / (largeur - 1);
            const rgb = hsvToRgb(h, sat, lum);
            const i = (y * largeur + x) * 4;
            image.data[i] = rgb[0];
            image.data[i + 1] = rgb[1];
            image.data[i + 2] = rgb[2];
            image.data[i + 3] = 255;
        }
    }
    ctx.putImageData(image, 0, 0);
}
// Dégradé arc-en-ciel du curseur de teinte, entre son min et son max
function dessinerTeinte() {
    const couleurs = [];
    for (let i = 0; i <= 12; i++) {
        const teinte = LIMITES.h[0] + (LIMITES.h[1] - LIMITES.h[0]) * i / 12;
        couleurs.push(css(hsvToRgb(teinte, 100, 100)));
    }
    hInput.style.background = 'linear-gradient(to right, ' + couleurs.join(', ') + ')';
}
// Met à jour l'affichage avec la couleur actuelle
function afficher() {
    // Position du rond dans le carré (de 0 à 1)
    const x = (s - LIMITES.s[0]) / (LIMITES.s[1] - LIMITES.s[0]);
    const y = (LIMITES.v[1] - v) / (LIMITES.v[1] - LIMITES.v[0]);
    rond.style.left = (x * 100) + '%';
    rond.style.top = (y * 100) + '%';

    // Couleur de l'aperçu
    document.getElementById('apercu').style.background = css(hsvToRgb(h, s, v));

    // Valeurs envoyées avec le formulaire
    document.getElementById('s').value = s;
    document.getElementById('v').value = v;
}

// Transforme la position de la souris en saturation et luminosité
function choisir(event) {
    const bord = zone.getBoundingClientRect();
    let x = (event.clientX - bord.left) / bord.width;
    let y = (event.clientY - bord.top) / bord.height;

    // On empêche de sortir du carré
    x = Math.min(1, Math.max(0, x));
    y = Math.min(1, Math.max(0, y));

    s = Math.round(LIMITES.s[0] + (LIMITES.s[1] - LIMITES.s[0]) * x);
    v = Math.round(LIMITES.v[1] - (LIMITES.v[1] - LIMITES.v[0]) * y);
    afficher();
}
// Quand on appuie dans le carré
zone.addEventListener('pointerdown', function (event) {
    zone.setPointerCapture(event.pointerId);
    choisir(event);
});

// Quand on bouge en gardant appuyé
zone.addEventListener('pointermove', function (event) {
    if (zone.hasPointerCapture(event.pointerId)) {
        choisir(event);
    }
});
// Quand on bouge le curseur de teinte
hInput.addEventListener('input', function () {
    h = Number(hInput.value);
    dessinerCarre();
    afficher();
});

// Au chargement de la page
dessinerTeinte();
dessinerCarre();
afficher();
