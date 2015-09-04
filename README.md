# wp-stripe-donations
A WordPress plugin to manage donations via Stripe payment gateway

This project is still in development - nothing to see here yet other than some initial setup.


## Developing and building
The javascript will not work if loaded directly into the browser. It has been written in a module pattern defined by npm/common.js and requires a build step using Browserify. Any changes to your code will be compiled and saved into the factchecker.js and factchecker.min.js files - ready for loading by the browser. 

There are some dev dependencies that should be installed on your dev machine:
- [Node.js](http://nodejs.org) and [npm](http://npmjs.org) is a framework for the tools and package management I am using. Download and instal from [nodejs.org](http://nodejs.org). 
- [Browserify](http://browserify.org) for building the js modules into a single file.  `npm install -g browserify`
- [uglifyjs](https://github.com/mishoo/UglifyJS2) for minifying the js.  `npm install -g uglify-js`
- [exorcist](https://github.com/thlorenz/exorcist) for maintaining separate Sourcemaps with browserify and uglify `npm install exorcist -g`
- [fsmonitor](https://www.npmjs.com/package/fsmonitor) for watching files and triggering automated builds `npm install -g fsmonitor`

Local dependencies are defined in `package.json` and can be loaded by running `npm install` on the command line in the same directory. This will create a `node_modules` folder with the module dependecies. 

#### when making changes...

With all of the above installed (global and local module dependencies) - from the command line run `npm run watch` and start editing your files. Every time you save a change, browserify is run and the output js is generated (note, this takes a second, so leave some time before refreshing your browser)
