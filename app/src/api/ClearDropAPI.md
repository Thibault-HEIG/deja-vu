# Déjà-vu: Adding an item (image upload, JS, API, storage, workflow)

This document explains how a clothing photo goes from the user's device to the wardrobe grid, based on:

| File | Role |
|---|---|
| `app/public/addItem.php` | Page + form + POST handler + final image saving |
| `app/public/js/addItem.js` | Eager background removal on file selection |
| `app/src/api/removeBackground.php` | Server-side proxy to the ClearBackdrop API |
| `app/src/databaseSecurity.php` | Server-side validation of all form inputs |

Not covered (not provided): `colorPicker.js`, `colorPicker.css`, `itemRepository::addItem()`, `seasonRepository.php`, and the constants `CATEGORIES`, `ALLOWED_IMAGE_TYPES`, `LIMITS`.

> **Known issues are listed in section 8.** Several of them prevent the flow from working as written. Read them before relying on this document as a description of a working system.

---

## 1. Big picture

The photo is sent **twice**, on purpose, to give a fallback:

1. **Eagerly, on file selection**: JS sends the photo to a PHP proxy, which calls the background-removal API and stores a transparent PNG in a **temporary** folder.
2. **At form submit**: the browser also posts the original file (it is a normal `<input type="file">` inside the form), plus a hidden field `tmp_file` pointing to the temporary PNG.

At submit, PHP prefers the processed PNG. If there is none (API failed), it falls back to the raw upload.

Why a proxy instead of calling the API from JS: the API key would be visible to anyone in the browser. PHP keeps it server-side.

---

## 2. Image upload (`addItem.php`)

### 2.1 Form

- `<form method="post" enctype="multipart/form-data">`: required for files to reach `$_FILES`.
- `<input type="file" id="photo" name="photo" accept="image/*" required>`: the raw photo. `accept` is only a browser hint, not security.
- `<input type="hidden" id="tmp_file" name="tmp_file">`: filled by JS with the path of the processed PNG (e.g. `img/tmp/tmp_65f….png`).
- Other fields: `category`, `season_id` (radios from DB), `hue` / `saturation` / `brightness` (custom color picker, **chosen by the user**, not computed from the image), `rate` (1 to 5).

### 2.2 POST handler

1. Cast inputs (`season_id`, `hue`, `rate` to int; `saturation` and `brightness` divided by 100, so the form sends 0 to 100 and the DB stores 0 to 1).
2. `getInputErrors(...)` validates everything (section 5).
3. If no errors: `saveItemImage()` stores the file, `addItem()` inserts the DB row, then redirect to `index.php#wardrobe` (POST/redirect/GET, avoids double submission on refresh).
4. If errors: the page re-renders with an error list.

### 2.3 `saveItemImage()` (final storage)

1. Ensures `img/uploads/user_<id>/` exists (`mkdir` 0775).
2. **Path A (normal)**: `basename()` on `tmp_file` (strips `../` tricks), checks the file exists in `img/tmp/`, then `rename()` it to `img/uploads/user_<id>/item_<uniqid>.png`.
3. **Path B (fallback)**: reads the real MIME with `mime_content_type()`, derives the extension from `ALLOWED_IMAGE_TYPES`, and `move_uploaded_file()` to `img/uploads/user_<id>/item_<uniqid>.<ext>`.
4. Returns the path **relative to `public/`**; this string is stored in `clothes.img_url` and used directly as `<img src>` on the home page.

Using `uniqid()` for the filename solves the "need the DB id before the filename" problem: the file can be saved before the row exists.

---

## 3. JS script (`addItem.js`)

Triggered on the `change` event of `#photo`:

1. **Local preview**: `URL.createObjectURL(file)` shows the photo instantly.
2. **Status + lock**: shows "Suppression du fond en cours…" and disables the submit button.
3. **Request**: `fetch('../src/api/removeBackground.php', { method: 'POST', body: FormData })` with the file under the key `photo`.
4. **Success**: replaces the preview with the transparent PNG (`data.tmpFile`), writes the path into `#tmp_file`, status "Fond supprimé ✓".
5. **Failure**: shows the error, empties `#tmp_file` (so PHP uses the raw upload), status "Échec : …".
6. **Always** (`finally`): re-enables submit.

It is `async/await` so the UI is not blocked while the API call (up to 30 s) runs.

The form still works without JS: `tmp_file` stays empty and Path B is used. That is the intended progressive enhancement.

---

## 4. API call (`removeBackground.php`)

Endpoint (intended): `POST` with `multipart/form-data`, field `photo`.
Response: JSON `{ "tmpFile": "img/tmp/tmp_xxx.png" }` or `{ "error": "..." }` with HTTP 400 (bad upload) or 502 (API failure).

Steps:

1. Checks a file arrived with `UPLOAD_ERR_OK`.
2. Creates the temp dir if missing.
3. `curl` POST to `https://clearbackdrop.com/api/v1/remove-background` with header `X-API-Key: <BG_REMOVER_API>` (key read with `getenv`), timeout 30 s.
4. If curl fails or status is not 200 → 502 with a generic message.
5. Otherwise writes the response body (expected: PNG bytes) to `tmp_<uniqid>.png` and returns its relative path.

The API key must come from the environment (`BG_REMOVER_API`), never from the repo. `.env` is gitignored.

> The `CURLOPT_POSTFIELDS` array currently contains only a placeholder comment (`// ... unchanged`). As written, **no image is sent to the API**. It must contain a `CURLFile` built from `$_FILES['photo']`. The multipart field name expected by ClearBackdrop is not confirmed here; check their documentation (comparable services use `image_file`).

---

## 5. Validation (`databaseSecurity.php`)

`getInputErrors()` merges five checks:

| Check | Rule |
|---|---|
| Image | OK if `tmp_file` points to an existing file in `public/img/tmp/`; else the raw upload must have no error and a MIME present in `ALLOWED_IMAGE_TYPES` |
| Category | key must exist in `CATEGORIES` |
| Season | integer 1 to 4 |
| Color | hue, saturation, brightness within `LIMITS` |
| Rate | integer 1 to 5 |

All of this is server-side, which is the only validation that counts (client-side can be bypassed).

---

## 6. Where images are stored, and when

| Moment | Location | Lifetime |
|---|---|---|
| File selected | nowhere on the server (browser memory only, `createObjectURL`) | until page closes |
| API call succeeded | `public/img/tmp/tmp_<uniqid>.png` | **until the form is submitted; never cleaned otherwise** |
| Form submitted (OK, API path) | `public/img/uploads/user_<id>/item_<uniqid>.png` (renamed from tmp) | permanent |
| Form submitted (OK, fallback path) | `public/img/uploads/user_<id>/item_<uniqid>.<jpg/png/webp>` | permanent |
| Form submitted, validation errors | nothing moved; tmp file stays in `img/tmp/` | orphaned |
| PHP raw upload | PHP temp dir (`tmp_name`) | deleted automatically at end of request |

The DB stores only the relative path in `clothes.img_url` (e.g. `img/uploads/user_1/item_abc.png`). Files live on disk, not in the database.

Both `img/tmp/` and `img/uploads/` are inside `public/` on purpose: the browser must load them directly.

---

## 7. Workflow from A to Z

| # | Step | Where |
|---|---|---|
| 1 | User opens the add-item page from the `+` card | `index.php` → `addItem.php` (GET) |
| 2 | Page loads seasons from DB, renders the form | `addItem.php` |
| 3 | User picks a photo | `#photo` |
| 4 | Instant local preview, submit disabled | `addItem.js` |
| 5 | Photo POSTed to the proxy | `fetch` → `removeBackground.php` |
| 6 | Proxy calls ClearBackdrop with the secret key | `curl` |
| 7 | Transparent PNG saved to `public/img/tmp/` | `removeBackground.php` |
| 8 | JSON returned; preview swaps to the PNG; `tmp_file` filled; submit enabled | `addItem.js` |
| 9 | User sets category, season, color, rating, clicks "Ajouter" | form |
| 10 | Browser POSTs the form (raw photo **and** `tmp_file`) | `addItem.php` |
| 11 | Server validates all inputs | `databaseSecurity.php` |
| 12a | Valid: PNG renamed to `uploads/user_<id>/item_<uniqid>.png` (or raw file moved if no PNG) | `saveItemImage()` |
| 12b | Invalid: form re-rendered with errors | `addItem.php` |
| 13 | Row inserted in `clothes` with `img_url` | `addItem()` |
| 14 | Redirect to `index.php#wardrobe` | `header('Location: …')` |
| 15 | Grid renders `<img src="img_url">` per item | `index.php` |
| 16 | Push to `main` deploys code via FTPS | `.github/workflows/deploy.yml` |

---

## 8. Known issues (found while reading)

### Blocking

1. **Argument order mismatch → fatal error on every submit.** `getInputErrors($upload, $tmpFile, $category, $season_id, $hue, …, $rate)` is declared with `$tmpFile` second, but `addItem.php` calls it with `$category` second and `tmp_file` last. The last argument (a string) lands on `int $rate` → `TypeError`. Fix the call order (and declare `$tmpFile = ''` **last**: a default value before required params is deprecated since PHP 8.0).
2. **Wrong temp directory.** `removeBackground.php` writes to `__DIR__ . '/img/tmp'`. If the file sits in `src/api/` (as the `fetch` URL implies), that is `src/api/img/tmp`, but `addItem.php` and `databaseSecurity.php` look in `public/img/tmp/`, and the browser preview loads `img/tmp/...` relative to `public/`. Result: the processed image is never found. The temp dir must be `__DIR__ . '/../../public/img/tmp'` (or the script must live in `public/`).
3. **API request body is a placeholder** (section 4): nothing is sent to ClearBackdrop.
4. **Proxy URL is not reachable by design.** `app/README.md` says `src/` is not web-accessible, yet the browser must call `../src/api/removeBackground.php`. It works locally only because Docker serves the whole repo; on a host whose document root is `public/`, that URL returns 404. Move the endpoint into `public/api/`.

### Important

5. **Photo size.** There is no resizing. PHP defaults (`upload_max_filesize` 2 MB) reject most phone photos at the proxy **and** at submit. If the total form exceeds `post_max_size`, `$_POST` and `$_FILES` arrive empty. Downscale in JS (canvas) before sending, and raise limits if needed.
6. **Double upload.** The raw photo is sent again at submit even when the PNG exists. Wasted bandwidth on mobile. Option: send the raw file only when `tmp_file` is empty (e.g. remove the input's `name` on success).
7. **Orphaned temp files.** Reselecting a photo, abandoning the page, or failing validation leaves files in `img/tmp/` forever, and each one cost an API credit. Add a cleanup (delete files older than ~1 h on each proxy call, or via cron).
8. **Validation failure loses the processed image.** The page re-renders without `tmp_file` and without the file input, so the user must reselect the photo and pay for another API call. Keep `tmp_file` in the re-rendered form.
9. **Proxy is unauthenticated and unvalidated.** Anyone can POST any file to it, burning API quota and filling disk. Validate MIME and size, require the user session once auth exists, and rate-limit.
10. **Silent failures.** HTTP status and `curl_error()` are not logged, so a missing key (empty `X-API-Key`) looks the same as an API outage. Log them server-side.
11. **Secrets in env.** `compose.yaml` only passes `DB_NAME`, `DB_USER`, `DB_PASSWORD` to the container, so `BG_REMOVER_API` is empty locally unless added. How it is set on Infomaniak is not defined in the repo.
12. **Response not verified.** The proxy saves whatever the API returns as `.png`. Check the body is an image (e.g. `getimagesizefromstring`) before saving, otherwise an error JSON becomes a broken "image".
13. **Race in JS.** Selecting a second photo while the first request runs can let the older response overwrite the newer one. Use an `AbortController` or ignore stale responses.

### Housekeeping

- Add `app/public/img/tmp/` and `app/public/img/uploads/` to `.gitignore`.
- Check the deploy does not delete or overwrite production uploads (`FTP-Deploy-Action` syncs from the repo); consider adding `app/public/img/uploads/**` and `app/public/img/tmp/**` to `exclude`.
- Free background-removal quotas are small. Check ClearBackdrop's limits before demo day; the fallback path exists for that reason.
