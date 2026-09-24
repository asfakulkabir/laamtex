<?php

/*
 * Router used by `php artisan serve`.
 *
 * Laravel's serve command loads server.php from the project root when it
 * exists. It mirrors the cPanel setup, where the document root is the project
 * root (/public_html) and index.php lives outside the public/ folder. Static
 * assets, uploaded files and Laravel routes are all handled by the root
 * index.php, exactly as they will be on the server.
 */

require __DIR__.'/index.php';