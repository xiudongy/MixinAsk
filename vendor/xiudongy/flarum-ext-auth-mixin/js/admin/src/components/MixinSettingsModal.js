import SettingsModal from 'flarum/components/SettingsModal';

export default class MixinSettingsModal extends SettingsModal {
  className() {
    return 'mixinSettingsModal Modal--small';
  }

  title() {
    return app.translator.trans('flarum-auth-mixin.admin.mixin_settings.title');
  }

  form() {
    return [
      <div className="Form-group">
        <label>{app.translator.trans('flarum-auth-mixin.admin.mixin_settings.api_key_label')}</label>
        <input className="FormControl" bidi={this.setting('flarum-auth-mixin.api_key')}/>
      </div>,

      <div className="Form-group">
        <label>{app.translator.trans('flarum-auth-mixin.admin.mixin_settings.api_secret_label')}</label>
        <input className="FormControl" bidi={this.setting('flarum-auth-mixin.api_secret')}/>
      </div>
    ];
  }
}
