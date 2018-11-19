var gulp = require('flarum-gulp');

gulp({
  modules: {
    'flarum/auth/mixin': 'src/**/*.js'
  }
});
