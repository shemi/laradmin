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
    .copy('node_modules/tinymce/skins', 'publishable/public/tinymce/skins')
    .copy('node_modules/tinymce/plugins', 'publishable/public/tinymce/plugins')
    .copy('node_modules/tinymce/themes', 'publishable/public/tinymce/themes')
    .copy('resources/assets/fonts', 'publishable/public/fonts')
    .copy('resources/assets/images', 'publishable/public/images')
    .options({
        extractVueStyles: true,
        processCssUrls: false,
        purifyCss: false,
        uglify: {},
        postCss: []
    });