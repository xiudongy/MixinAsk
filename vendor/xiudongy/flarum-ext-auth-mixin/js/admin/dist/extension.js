'use strict';

System.register('flarum/auth/mixin/components/mixinSettingsModal', ['flarum/components/SettingsModal'], function (_export, _context) {
  "use strict";

  var SettingsModal, mixinSettingsModal;
  return {
    setters: [function (_flarumComponentsSettingsModal) {
      SettingsModal = _flarumComponentsSettingsModal.default;
    }],
    execute: function () {
      mixinSettingsModal = function (_SettingsModal) {
        babelHelpers.inherits(mixinSettingsModal, _SettingsModal);

        function mixinSettingsModal() {
          babelHelpers.classCallCheck(this, mixinSettingsModal);
          return babelHelpers.possibleConstructorReturn(this, Object.getPrototypeOf(mixinSettingsModal).apply(this, arguments));
        }

        babelHelpers.createClass(mixinSettingsModal, [{
          key: 'className',
          value: function className() {
            return 'mixinSettingsModal Modal--small';
          }
        }, {
          key: 'title',
          value: function title() {
            return app.translator.trans('flarum-auth-mixin.admin.mixin_settings.title');
          }
        }, {
          key: 'form',
          value: function form() {
            return [m(
              'div',
              { className: 'Form-group' },
              m(
                'label',
                null,
                app.translator.trans('flarum-auth-mixin.admin.mixin_settings.api_key_label')
              ),
              m('input', { className: 'FormControl', bidi: this.setting('flarum-auth-mixin.api_key') })
            ), m(
              'div',
              { className: 'Form-group' },
              m(
                'label',
                null,
                app.translator.trans('flarum-auth-mixin.admin.mixin_settings.api_secret_label')
              ),
              m('input', { className: 'FormControl', bidi: this.setting('flarum-auth-mixin.api_secret') })
            )];
          }
        }]);
        return mixinSettingsModal;
      }(SettingsModal);

      _export('default', mixinSettingsModal);
    }
  };
});;
'use strict';

System.register('flarum/auth/mixin/main', ['flarum/app', 'flarum/auth/mixin/components/mixinSettingsModal'], function (_export, _context) {
  "use strict";

  var app, mixinSettingsModal;
  return {
    setters: [function (_flarumApp) {
      app = _flarumApp.default;
    }, function (_flarumAuthmixinComponentsmixinSettingsModal) {
      mixinSettingsModal = _flarumAuthmixinComponentsmixinSettingsModal.default;
    }],
    execute: function () {

      app.initializers.add('flarum-auth-mixin', function () {
        app.extensionSettings['flarum-auth-mixin'] = function () {
          return app.modal.show(new mixinSettingsModal());
        };
      });
    }
  };
});