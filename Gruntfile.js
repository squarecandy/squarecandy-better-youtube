// inspired by https://gist.github.com/jshawl/6225945
// Thanks @jshawl!

// now using grunt-sass to avoid Ruby dependency

module.exports = function( grunt ) {
	const sass = require( 'sass' );
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		sass: {
			// sass tasks
			dist: {
				files: {
					'dist/css/better-youtube.min.css': 'css/better-youtube.scss', // this is our main scss file
				},
			},
			options: {
				implementation: sass,
				compass: true,
				style: 'expanded',
				sourceMap: true,
			},
		},
		postcss: {
			options: {
				map: true, // inline sourcemaps
				processors: [
					require( 'pixrem' )(), // add fallbacks for rem units
					require( 'autoprefixer' )( { grid: 'autoreplace' } ), // add vendor prefixes
					require( 'cssnano' )(), // minify the result
				],
			},
			dist: {
				src: 'dist/css/*.css',
			},
		},
		copy: {
			js: {
				files: [
					{
						expand: true,
						cwd: 'node_modules/magnific-popup/dist',
						src: 'jquery.magnific-popup.min.js',
						dest: 'dist/js/vendor',
					},
					{
						expand: true,
						cwd: 'node_modules/fitvids/dist',
						src: 'fitvids.min.js',
						dest: 'dist/js/vendor',
					},
				],
			},
			css: {
				files: [
					{
						expand: true,
						cwd: 'node_modules/magnific-popup/dist',
						src: 'magnific-popup.css',
						dest: 'dist/css/vendor',
					},
				],
			},
		},
		terser: {
			options: {
				sourceMap: true,
			},
			dist: {
				files: [
					{
						expand: true,
						src: '*.js',
						dest: 'dist/js',
						cwd: 'js',
						ext: '.min.js',
					},
				],
			},
		},
		phpcs: {
			application: {
				src: [ '*.php' ],
			},
			options: {
				bin: './vendor/squizlabs/php_codesniffer/bin/phpcs',
				standard: 'phpcs.xml',
			},
		},
		stylelint: {
			src: [ 'css/*.scss', 'css/**/*.scss', 'css/*.css' ],
		},
		eslint: {
			gruntfile: {
				src: [ 'Gruntfile.js' ],
			},
			src: {
				src: [ 'js' ],
			},
		},
		run: {
			stylelintfix: {
				cmd: 'npx',
				args: [ 'stylelint', 'css/*.scss', '--fix' ],
			},
			eslintfix: {
				cmd: 'eslint',
				args: [ 'js/*.js', '--fix' ],
			},
		},
		watch: {
			css: {
				files: [ 'css/*.scss' ],
				tasks: [ 'run:stylelintfix', 'sass', 'postcss' ],
			},
			js: {
				files: [ 'js/*.js' ],
				tasks: [ 'run:eslintfix', 'terser' ],
			},
		},
	} );

	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-terser' );
	grunt.loadNpmTasks( 'grunt-phpcs' );
	grunt.loadNpmTasks( 'grunt-stylelint' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-postcss' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-run' );
	grunt.registerTask( 'init', [ 'sass', 'postcss', 'copy', 'terser' ] );
	grunt.registerTask( 'default', [ 'run', 'sass', 'postcss', 'terser', 'watch' ] );
	grunt.registerTask( 'preflight', [ 'sass', 'postcss', 'copy', 'terser', 'phpcs', 'stylelint', 'eslint' ] );
};
