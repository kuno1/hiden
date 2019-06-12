var gulp        = require('gulp'),
    fs          = require('fs'),
    $           = require('gulp-load-plugins')(),
    pngquant    = require('imagemin-pngquant'),
    eventStream = require('event-stream');

// Include Path for Scss
var includesPaths = [
    'assets/scss'
];

// Source directory
var srcDir = {
    scss: [
        'assets/scss/**/*.scss'
    ],
    js: [
        'assets/js/**/*.js',
        '!assets/js/**/_*.js'
    ],
    jsHint: [
        'assets/js/**/*.js'
    ],
    jshintrc: [
        '.jshintrc'
    ],
    img: [
        'assets/img/**/*'
    ]

};
// Destination directory
var destDir = {
    scss: 'dist/css',
    js: 'dist/js',
    img: 'dist/img'
};

// Sass
gulp.task('sass', function () {

  return gulp.src(srcDir.scss)
    .pipe($.plumber({
        errorHandler: $.notify.onError('<%= error.message %>')
    }))
    .pipe($.sassGlob())
    .pipe($.sourcemaps.init())
    .pipe($.sass({
      errLogToConsole: true,
      outputStyle    : 'compressed',
      sourceComments : 'normal',
      sourcemap      : true,
      includePaths   : includesPaths
    }))
    .pipe($.sourcemaps.write('./map'))
    .pipe(gulp.dest(destDir.scss));
});


// Minify All
gulp.task('jsconcat', function () {
  return gulp.src(srcDir.js)
    .pipe($.plumber({
      errorHandler: $.notify.onError('<%= error.message %>')
    }))
    .pipe($.sourcemaps.init({
      loadMaps: true
    }))
    .pipe($.babel({
      "presets": ["es2015"]
    }))
    .pipe($.uglify({
      output:{
        comments: /^!/
      }
    }))
    .pipe($.sourcemaps.write('./map'))
    .pipe(gulp.dest(destDir.js));
});


// JS Hint
gulp.task('jshint', function () {
  return gulp.src(srcDir.jsHint)
    .pipe($.plumber())
    .pipe($.jshint({
      lookup: srcDir.jshintrc
    }))
    .pipe($.jshint.reporter('jshint-stylish'));
});

// JS task
gulp.task('js', gulp.parallel('jshint', 'jsconcat'));


// Build Libraries.
gulp.task('copylib', function () {
  // pass gulp tasks to event stream.
  // return eventStream.merge(
  // );
});

// Image min
gulp.task('imagemin', function () {
  return gulp.src(srcDir.img)
    .pipe($.imagemin({
      progressive: true,
      svgoPlugins: [{removeViewBox: false}],
      use        : [pngquant()]
    }))
    .pipe(gulp.dest(destDir.img));
});


// watch
gulp.task('watch', function () {
  // Make SASS
  gulp.watch(srcDir.scss, gulp.task( 'sass' ) );
  // Uglify all
  gulp.watch(srcDir.jsHint, gulp.task( 'js' ) );
  // Minify Image
  gulp.watch(srcDir.img, gulp.task( 'imagemin' ) );
});

// Build
gulp.task('build', gulp.parallel( 'js', 'sass', 'imagemin') );

// Default Tasks
gulp.task('default', gulp.parallel('watch'));

