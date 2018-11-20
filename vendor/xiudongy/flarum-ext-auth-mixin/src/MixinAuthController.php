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
use Illuminate\Support\Facades\Cache;


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
            if(isset($_COOKIE['mixin_access_token'])) {
                $token = $_COOKIE['mixin_access_token']; 
            } else {
                $mixin->setModel('oauth');
                $result = $mixin->getOauthToken($client_id, $_GET['code'], $client_secret);
                if(isset($result['data']['access_token'])) {
                    $token = $result['data']['access_token'];
                    setcookie("mixin_access_token", $token, time()+3600);
                } else {
                    echo '<pre>';var_dump($result);exit;
                }
            }
            if($token) {
                $mixin->setModel('me');
                $profile = $mixin->readProfile($token);
                //$identification = ['email' => $profile['data']['identity_number'].'@miniwenda.com'];
                $identification = ['mixin_id' => $profile['data']['identity_number']];
                $suggestions = [
                    'username' => $profile['data']['full_name'],
                    'avatarUrl' => $profile['data']['avatar_url']
                ];
                $user = User::where('mixin_id', $profile['data']['identity_number'])->first();
                if(!$user) {
                    $user = new User;
                    $user->username = $profile['data']['full_name'];
                    $user->join_time = time();
                    $user->email = $profile['data']['identity_number'].'@vcdiandian.com'; 
                    $user->password = md5($profile['data']['identity_number'].rand(100,1000000000)); 
                    $user->mixin_id = $profile['data']['identity_number'];
                    $user->is_activated = 1;
                    $user->save();
                }
                $response = $this->authResponse->make($request, $identification, $suggestions);
                if($this->isMobile()) {
                    header("Location:/");
                    exit;
                }
                return $response;
            } 
        }
    }
    protected function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return TRUE;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])) {
            return stristr($_SERVER['HTTP_VIA'], "wap") ? TRUE : FALSE;// 找不到为flase,否则为TRUE
        }
        // 判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = [ 'mobile', 'nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap' ];
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return TRUE;
            }
        }
        if (isset ($_SERVER['HTTP_ACCEPT'])) { // 协议法，因为有可能不准确，放到最后判断
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== FALSE) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === FALSE || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return TRUE;
            }
        }
        return FALSE;
    }
}
