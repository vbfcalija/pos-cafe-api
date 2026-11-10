<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Serves the pos-cafe-client static build (nuxt generate, ssr:false) as the
// NativePHP mobile app's WebView entry point — see NATIVEPHP_START_URL. Must
// be the root path: the SPA's own client-side Vue Router expects to be
// mounted at "/" (its pages are index.vue -> "/", pos/index.vue -> "/pos",
// etc.) — serving this shell from any other path (e.g. "/spa") means Vue
// Router itself renders a 404 "Page not found" the instant it boots, even
// though the server-side response was a valid 200.
//
// The SPA is only served inside the NativePHP shell app. On the web the API
// serves no pages: "/" sends the visitor on to APP_URL. If APP_URL is this very
// host there is nowhere to send them (it would redirect to itself forever), so
// that case is a plain 404.
if (config('nativephp-internal.running')) {
    Route::get('/', function () {
        return response(file_get_contents(resource_path('native/spa.html')), 200)
            ->header('Content-Type', 'text/html');
    });
} else {
    Route::get('/', function (Request $request) {
        $target = rtrim((string) config('app.url'), '/');

        abort_if($target === '' || $target === $request->getSchemeAndHttpHost(), 404);

        return redirect()->away($target);
    });
}
