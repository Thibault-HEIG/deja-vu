I've rewritten README.md from your four real files, and the flow it documents has bugs that stop it working as written.

**What the flow does:**
1. When the user picks a photo, `addItem.js` sends it to a PHP proxy, which calls ClearBackdrop and saves a transparent PNG in `img/tmp/`.
2. At submit, `saveItemImage()` renames that PNG to `img/uploads/user_<id>/item_<uniqid>.png` and stores the relative path in `clothes.img_url`.
3. If the API step failed, the raw photo posted with the form is moved there instead.

**Blocking bugs:**
1. **Wrong argument order:** your `getInputErrors()` call in `addItem.php` doesn't match its signature. The `tmp_file` string lands on `int $rate`, so every submit throws a `TypeError`.
2. **Wrong temp folder:** `removeBackground.php` writes to `__DIR__/img/tmp`. In `src/api/` that is not `public/img/tmp`, where everything else looks, so the processed image is never found.
3. **Empty API request:** `CURLOPT_POSTFIELDS` still contains `// ... unchanged`, so no image is sent to ClearBackdrop. I couldn't find ClearBackdrop's docs in my search, so the README doesn't state its field name. Check it yourself.
4. **Unreachable proxy:** `fetch('../src/api/...')` only works locally. `src/` is meant to be non-public, so on production this likely returns 404. Move the endpoint to `public/api/`.

**Other important issues:**
- Phone photos exceed PHP's 2 MB default and are rejected both at the proxy and at submit, and nothing resizes them.
- The photo is uploaded twice.
- `img/tmp/` is never cleaned, and each orphaned file cost an API credit.
- After a validation error the user must reselect the photo and pay for another API call.
- The proxy has no auth, MIME or size check.
- `compose.yaml` doesn't pass `BG_REMOVER_API` to the container.

Correction to my previous version: I had wrongly said PHP computes hue, saturation and brightness from the image. They come from your color picker. `colorPicker.js` and `addItem()` weren't provided, so those parts aren't documented.
