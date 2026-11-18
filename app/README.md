# Architecture — Déjà-vu
lien vers le site: https://deja-vu.nouveau-souffle.ch/
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
│   ├── api/                        # Web API endpoints
│   │   ├── removeBackground.php    # ClearBackdrop background-removal proxy
│   │   └── ClearDropAPI.md         # Upload & API workflow documentation
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
    ├── databaseSecurity.php        # Input validation helpers
    │
    ├── repositories/               # Data access layer
    │   ├── Repository.php          # Base abstract repository with PDO connection
    │   ├── IGet.php, IDelete.php…  # CRUD interfaces
    │   ├── ItemRepository.php      # DB queries for items
    │   ├── UserRepository.php      # DB queries for users
    │   ├── SeasonRepository.php    # DB queries for seasons
    │   └── OutfitRepository.php    # DB queries for outfits
    │
    ├── models/
    │   ├── Item.php                # Item entity: id, category, hue/saturation/brightness, season, rating, image URL
    │   ├── Outfit.php              # Outfit entity: top + bottom + shoes + optional accessory and jacket
    │   └── User.php                # User entity: id, name, color-harmony weight, season-harmony weight
    │
    └── algorithm/
        ├── OutfitGenerator.php     # Core engine: scores and selects the best item combination given weights and a seed item
        └── model.json              # Color scoring linear regression model
```
