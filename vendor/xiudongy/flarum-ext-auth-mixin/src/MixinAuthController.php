<?php
/*
 * This file is part of Flarum.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flarum\Auth\Mixin;

use Flarum\Forum\AuthenticationResponseFactory;
use Flarum\Http\Controller\ControllerInterface;
use Flarum\Settings\SettingsRepositoryInterface;
use League\OAuth1\Client\Server\mixin;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\RedirectResponse;
use Zamseam\Mixin\MixinClient;
use Flarum\Core\User;

class MixinAuthController implements ControllerInterface
{
    /**
     * @var AuthenticationResponseFactory
     */
    protected $authResponse;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param AuthenticationResponseFactory $authResponse
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(AuthenticationResponseFactory $authResponse, SettingsRepositoryInterface $settings)
    {
        $this->authResponse = $authResponse;
        $this->settings = $settings;
    }

    /**
     * @param Request $request
     * @return \Psr\Http\Message\ResponseInterface|RedirectResponse
     */
    public function handle(Request $request)
    {
        $client_id = $this->settings->get("flarum-auth-mixin.client_id");
        $client_secret = $this->settings->get("flarum-auth-mixin.client_secret");
        $private_key = $this->settings->get("flarum-auth-mixin.private_key");
        $session_id = $this->settings->get("flarum-auth-mixin.session_id");
        if (!isset($_GET['code'])) {
            $authorizationUrl = 'https://mixin.one/oauth/authorize?client_id='.$client_id.'&scope=PROFILE:READ+PHONE:READ+ASSETS:READ';
            header('Location: ' . $authorizationUrl);
            exit;
        } else {
            $mixin = new MixinClient([
                'uid' => '',
                'session_id' => $session_id,
                'private_key' => $private_key
            ]);
            $mixin->setModel('oauth');
            $result = $mixin->getOauthToken($client_id, $_GET['code'], $client_secret);
            if(isset($result['data']['access_token'])) {
                $token = $result['data']['access_token'];
                //$_SESSION['mixin_access_token'] = $token;
                $mixin->setModel('me');
                $profile = $mixin->readProfile($token);
                //$identification = ['email' => $profile['data']['identity_number'].'@miniwenda.com'];
                $identification = ['mixin_id' => $profile['data']['identity_number']];
                $suggestions = [
                    'username' => $profile['data']['full_name'],
                    'avatarUrl' => $profile['data']['avatar_url']
                ];
                $user = User::where('mixin_id', $profile['data']['identity_number'])->first();
                if(!$user) {$user = new User;
                    $user->username = $profile['data']['full_name'];
                    $user->join_time = time();
                    $user->email = $profile['data']['identity_number'].'@vcdiandian.com'; 
                    $user->password = md5($profile['data']['identity_number'].rand(100,1000000000)); 
                    $user->mixin_id = $profile['data']['identity_number'];
                    $user->is_activated = 1;
                    $user->save();
                }
                $this->authResponse->make($request, $identification, $suggestions);
                header("Location:/");
                exit;
            } else {
                echo '<pre>';var_dump($result);exit;
            }
        }
    }
}
