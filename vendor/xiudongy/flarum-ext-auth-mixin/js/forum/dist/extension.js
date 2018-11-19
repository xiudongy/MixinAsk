'use strict';

System.register('flarum/auth/mixin/main', ['flarum/extend', 'flarum/app', 'flarum/components/LogInButtons', 'flarum/components/LogInButton'], function (_export, _context) {
  "use strict";

  var extend, app, LogInButtons, LogInButton;
  return {
    setters: [function (_flarumExtend) {
      extend = _flarumExtend.extend;
    }, function (_flarumApp) {
      app = _flarumApp.default;
    }, function (_flarumComponentsLogInButtons) {
      LogInButtons = _flarumComponentsLogInButtons.default;
    }, function (_flarumComponentsLogInButton) {
      LogInButton = _flarumComponentsLogInButton.default;
    }],
    execute: function () {

      app.initializers.add('flarum-auth-mixin', function () {
        extend(LogInButtons.prototype, 'items', function (items) {
          items.add('mixin', m(
            LogInButton,
            {
              className: 'Button LogInButton--mixin',
              icon: 'mixin',
              path: '/auth/mixin' },
              'Login with Mixin Account'
            //app.translator.trans('flarum-auth-mixin.forum.log_in.with_mixin_button')
          ));
        });
      });
    }
  };
});