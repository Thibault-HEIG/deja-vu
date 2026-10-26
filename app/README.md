# Architecture — Déjà-vu

## Overview

Server-side PHP app (no framework). The entry points in `public/` handle HTTP requests, delegate business logic to repositories in `src/`, and render reusable PHP components. The outfit-generation algorithm lives in its own sub-namespace under `src/algorithm/`.

---

## Directory tree

```
app/
│
├── data/
│   └── synthetic-data.sql          # Seed script: inserts seasons, users, and sample items into the DB
│
├── public/                         # Web root — everything here is directly accessible via HTTP
│   ├── index.php                   # Home page: displays the generated outfit + the wardrobe
│   ├── addItem.php                 # Form to add a new clothing item
│   ├── generateOutfit.php          # Form to generate a new outfit
│   │
│   ├── components/                 # Reusable PHP partials, rendered via src/renderer.php
│   │   ├── head.php                # HTML <head> (charset, viewport, CSS imports, title/description)
│   │   └── footer.php              # Page footer with author credits
│   │
│   ├── css/
│   │   ├── styles.css              # Entry point: imports reset, theme, and index stylesheets
│   │   ├── reset.css               # Box-model and margin reset (applied globally)
│   │   ├── theme.css               # CSS custom properties (colors, typography, spacing tokens)
│   │   └── *.css                   # Page-specific layout rules
│   └── img/
│       ├── icons/                  # UI SVG icons (logo, add button, settings, season icons)
│       └── uploads/                # User-uploaded clothing photos, organised by user_<id>/item_<id>.png
│
└── src/                            # Back-end logic — not directly accessible from the web
    ├── Database.php                # Singleton PDO connection
    ├── constants.php               # Shared application constants
    ├── renderer.php                # render($component, $data) helper
    ├── itemRepository.php          # DB queries for items (get by id, get by user, insert, delete…)
    ├── userRepository.php          # DB queries for users (get by id, update preferences…)
    │
    ├── models/
    │   ├── Item.php                # Item entity: id, category, hue/saturation/brightness, season, rating, image URL
    │   ├── Outfit.php              # Outfit entity: top + bottom + shoes + optional accessory and jacket
    │   └── User.php                # User entity: id, name, color-harmony weight, season-harmony weight
    │
    └── algorithm/
        ├── OutfitGenerator.php     # Core engine: scores and selects the best item combination given weights and a seed item
        ├── colorScorer.php         # Pure functions for computing colour-harmony scores between two items
        └── model.json              # Color scoring linear regression model
```
