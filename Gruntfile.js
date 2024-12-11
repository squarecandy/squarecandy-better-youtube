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
			preflight: {
				files: [
					// common
					{
						expand: true,
						cwd: 'node_modules/squarecandy-common/common',
						//src: '**/*',
						src: [ '**/*', '!gitignore' ], // !! skip gitignore bc we don't want to ignore /vendor
						dest: '',
						dot: true,
						rename( dest, matchedSrcPath ) {
							// the exact file name .gitignore is reserved by npm
							// so we track it as /common/gitignore (no dot) and rename on copy
							if ( matchedSrcPath === 'gitignore' ) {
								return dest + '.gitignore';
							}
							// default for all other files
							return dest + matchedSrcPath;
						},
					},
					// plugin
					{
						expand: true,
						cwd: 'node_modules/squarecandy-common/plugin',
						src: '**/*',
						dest: '',
						dot: true,
					},
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
				src: [ '*.php', 'inc/*.php' ],
			},
			options: {
				bin: './vendor/squizlabs/php_codesniffer/bin/phpcs',
				standard: 'phpcs.xml',
			},
		},
		stylelint: {
			src: [ 'css/*.scss', 'css/**/*.scss', 'css/*.css' ],
		},
		run: {
			stylelintfix: {
				cmd: 'npx',
				args: [ 'stylelint', 'css/*.scss', '--fix' ],
			},
			eslintfix: {
				cmd: 'eslint',
				args: [ 'js/*.js', 'Gruntfile.js', '--fix' ],
			},
			bump: {
				cmd: 'npm',
				args: [ 'run', 'release', '--', '--prerelease', 'dev', '--skip.tag', '--skip.changelog' ],
			},
			ding: {
				cmd: 'tput',
				args: [ 'bel' ],
			},
		},
		eslint: {
			gruntfile: {
				src: [ 'Gruntfile.js' ],
			},
			src: {
				src: [ 'js' ],
			},
		},
		watch: {
			css: {
				files: [ 'css/*.scss' ],
				tasks: [ 'run:stylelintfix', 'sass', 'postcss', 'string-replace', 'run:ding' ],
			},
			js: {
				files: [ 'js/*.js' ],
				tasks: [ 'run:eslintfix', 'terser', 'run:ding' ],
			},
		},
		'string-replace': {
			dist: {
				files: [
					{
						expand: true,
						cwd: 'dist/css/',
						src: '*.min.css.map',
						dest: 'dist/css/',
					},
				],
				options: {
					replacements: [
						// place files inline example
						{
							pattern: /(file:\/\/\/([^,]*)\/wp-content)+/g,
							replacement: '/wp-content',
						},
					],
				},
			},
		},
	} );

	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-terser' );
	grunt.loadNpmTasks( 'grunt-phpcs' );
	grunt.loadNpmTasks( 'grunt-stylelint' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( '@lodder/grunt-postcss' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-run' );
	grunt.loadNpmTasks( 'grunt-string-replace' );

	grunt.registerTask( 'default', [ 'run:stylelintfix', 'run:eslintfix', 'sass', 'postcss', 'terser', 'string-replace', 'watch' ] );
	grunt.registerTask( 'compile', [ 'sass', 'postcss', 'copy:preflight', 'terser', 'string-replace' ] );
	grunt.registerTask( 'lint', [ 'stylelint', 'eslint', 'phpcs' ] );
	grunt.registerTask( 'bump', [ 'run:bump' ] );
	grunt.registerTask( 'preflight', [ 'compile', 'lint', 'bump', 'run:ding' ] );
};
