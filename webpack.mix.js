let mix = require('laravel-mix');

mix.js('resources/assets/js/app.js', 'js')
    .sass('resources/assets/scss/app.scss', 'css')
    .sourceMaps(false)
    .setPublicPath('./publishable/public')
    .browserSync({
        proxy: 'laradmin.test',
        files: [
            'src/**/*.php',
            'resources/views/**/*.php',
            'publishable/public/**/*.js',
            'publishable/public/**/*.css'
        ]
    })
    .options({
        extractVueStyles: true,
        processCssUrls: false,
        purifyCss: false,
        uglify: {},
        postCss: []
    });