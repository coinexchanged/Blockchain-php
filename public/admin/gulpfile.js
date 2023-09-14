var url = require('url');
var fs = require('fs');
var path = require('path');
var pkg = require('./package.json');

var gulp = require('gulp');
var uglify = require('gulp-uglify');
var minify = require('gulp-minify-css');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var header = require('gulp-header');
var del = require('del');
var gulpif = require('gulp-if');
var minimist = require('minimist');
var zip = require('gulp-zip');

var livereload = require('gulp-livereload');
var webserver = require('gulp-webserver');

var task = {
  mincss: function() {
    return gulp.src(['./src/css/**/*.css'])
      .pipe(minify())
      .pipe(header.apply(null, ['/** <%= pkg.name %>-v<%= pkg.version %> <%= pkg.license %> License By <%= pkg.homepage %> e-mail:<%= pkg.email %> */\n', { pkg: pkg }]))
      .pipe(gulp.dest('./build/css'));
  },
  minjs: function() {
    return gulp.src('./src/js/*.js')
      .pipe(gulpif(['!app.js', '!config.js'], uglify()))
      .pipe(header.apply(null, ['/** <%= pkg.name %>-v<%= pkg.version %> <%= pkg.license %> License By <%= pkg.homepage %> e-mail:<%= pkg.email %> */\n <%= js %>', { pkg: pkg, js: ';' }]))
      .pipe(gulp.dest('./build/js'));
  },
  file: function() {
    return gulp.src(['./src/images/**/*.{png,jpg,gif,html,mp3,json}'])
      .pipe(rename({}))
      .pipe(gulp.dest('./build/images'));
  }
};
gulp.task('minjs', task.minjs);
gulp.task('mincss', task.mincss);
gulp.task('file', task.file);
gulp.task('default', function() {

});
gulp.task('all', ['clear'], function() {
  for (var key in task) {
    task[key]();
  }
});
gulp.task('clear', function(cb) {
  return del(['./build/*'], cb);
});

//web服务器 用于开发环境
gulp.task('webserver', function() {
  gulp.src('./') // 服务器目录（./代表根目录）
    .pipe(webserver({ // 运行gulp-webserver
      port: 8050, //端口，默认8000
      livereload: true, // 启用LiveReload
      open: true, // 服务器启动时自动打开网页
      directoryListing: {
        enable: true,
        path: 'index.html' //配置默认访问页面
      },
      middleware: function(req, res, next) {
        //mock local data
        var urlObj = url.parse(req.url, true),
          method = req.method;
        if (!urlObj.pathname.match(/^\/api/)) { //不是api开头的数据，直接next
          next();
          return;
        }
        var mockDataFile = path.join(__dirname, urlObj.pathname) + ".js";
        //file exist or not
        fs.access(mockDataFile, fs.F_OK, function(err) {
          if (err) {
            // res.setHeader('Content-Type', 'application/json');
            res.end(JSON.stringify({
              "status": "没有找到此文件",
              "notFound": mockDataFile
            }));
            return;
          }
          var data = fs.readFileSync(mockDataFile, 'utf-8');
          res.setHeader('Content-Type', 'application/json');
          res.end(data);
        });
        next();
      },
      proxies: []
    }));
});