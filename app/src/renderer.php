<?php
/**
 * Renderer : renders a component with the given data.
 *
 * @param string $component The name of the component to render (without the .php extension).
 * @param array $data An associative array of data to be extracted into variables for the component.
 */
function render(string $component, array $data = []): void {

    $componentPath = __DIR__ . '/../public/components/' . $component . '.php';

    // `extract()` transforme les clés du tableau en variables locales à la
    // fonction. `EXTR_SKIP` empêche d'écraser les variables qui existent déjà
    // ici (`$component`, `$data` et `$componentPath`).
    extract($data, EXTR_SKIP);

    require $componentPath;
}
