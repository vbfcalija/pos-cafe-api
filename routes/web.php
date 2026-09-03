<?php

use Illuminate\Support\Facades\Route;

// Serves the pos-cafe-client static build (nuxt generate, ssr:false) as the
// NativePHP mobile app's WebView entry point — see NATIVEPHP_START_URL. Must
// be the root path: the SPA's own client-side Vue Router expects to be
// mounted at "/" (its pages are index.vue -> "/", pos/index.vue -> "/pos",
// etc.) — serving this shell from any other path (e.g. "/spa") means Vue
// Router itself renders a 404 "Page not found" the instant it boots, even
// though the server-side response was a valid 200.
Route::get('/', function () {
    return response(file_get_contents(resource_path('native/spa.html')), 200)
        ->header('Content-Type', 'text/html');
});
