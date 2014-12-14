<?php
/**
 * добавить dependency
 * __construct
 * get_method(curl)(post)(get)
 *
 */

class oauth_mailru
{
    private static $mm_token    = 'https://connect.mail.ru/oauth/token';
    private static $mm_api      = 'http://www.appsmail.ru/platform/api';
    private static $auth_mm     = 'https://connect.mail.ru/oauth/authorize?';
    private static $redirect_uri = false;
    private static $token       = false;

    public static function get_token($code,$client) {
        try{
            if(!$code) throw new Exception("No code to auth mailru :". __METHOD__.' line :'.__LINE__);
            if(!$client) throw new Exception("No client data to auth mailru :". __METHOD__.' line :'.__LINE__);
            $get_token = [
                'client_id'     => $client['id'],
                'client_secret' => $client['secret'],
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => self::get_redirect_url(),
            ];
            return json_decode(self::get_method(self::$mm_token,$get_token),true);
        }
        catch(Exception $e){
            error_log($e->getMessage());
        }
    }
    public static function  get_user_data($code){
        $client = self::get_conf();
        $token = self::get_token($code,$client);
        $sign = md5("app_id={$client['id']}method=users.getInfosecure=1session_key={$token['access_token']}{$client['secret']}");
            $get_user_info = [
                'method'       => 'users.getInfo',
                'secure'       => '1',
                'app_id'       => $client['id'],
                'session_key'  => $token['access_token'],
                'sig'          => $sign
            ];
            return json_decode(self::get_method(self::$mm_api,$get_user_info),true);
}

    /**
     * @param $http
     * @param $params
     * @return mixed
     */
    public static  function get_method($http,$params){
        return http::post($http,$params);
    }

    /**
     * @param array $params передаваемые параметры(scope )
     * @param $url
     * @return string
     * todo : сделать чтоб подымал мобильную версию line 73
     */
    public static function get_url()
    {
        $config         = config::get('mailru');
        $client_id      = $config['id'];
        $client_secret  = $config['secret'];
        $host           = self::get_redirect_url();
        $params = [
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'response_type' =>'code',
//            'scope'=>'widget',
            'display'=>'mobile',
            'redirect_uri'  => $host,
        ];
        debug(self::$auth_mm.http_build_query($params),'mailru host:');
        return self::$auth_mm.http_build_query($params);
    }

    public static function get_conf(){
        return config::get('mailru');
    }
    public static function get_redirect_url($url=false){
        if($url) return $url;
        else return 'http://'.$_SERVER['HTTP_HOST'].'/oauth/mailru';
    }
}