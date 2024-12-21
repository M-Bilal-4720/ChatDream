const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .version() // Optional: for cache busting
    .sourceMaps(); // Optional: for debugging
