<?php
/**
 * добавить dependency
 * __construct
 * get_method(curl)(post)(get)
 *
 */

class oauth_google
{
    private static $auth_google         = 'https://accounts.google.com/o/oauth2/auth?';
    private static $token_google        = 'https://accounts.google.com/o/oauth2/token';
    private static $user_info__google   = 'https://www.googleapis.com/oauth2/v1/userinfo';
    private static $redirect_uri    = false;
    private static $token           = false;

    public static function get_token($code,$client) {
        try{
            if(!$code) throw new Exception("No code to auth google :". __METHOD__.' line :'.__LINE__);
            if(!$client) throw new Exception("No client data to auth google :". __METHOD__.' line :'.__LINE__);
            $get_token = [
                'client_id'     => $client['id'],
                'client_secret' => $client['key'],
                'redirect_uri'  => self::get_redirect_url(),
                'grant_type'    => 'authorization_code',
                'code'          => $code,
            ];
            return json_decode(self::get_method(self::$token_google,$get_token),true);
        }
        catch(Exception $e){
            error_log($e->getMessage());
        }
    }
    public static function get_user_data($code){
        $client = self::get_conf();
        $token = self::get_token($code,$client);
        $get_user_info = [
            'client_id'     => $client['id'],
            'client_secret' => $client['key'],
            'redirect_uri'  => self::get_redirect_url(),
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'access_token'  => $token['access_token']
        ];
        return json_decode(self::get_method(self::$user_info__google,$get_user_info,'get'),true);
    }

    /**
     * @param $http
     * @param $params
     * @return mixed
     */
    public static  function get_method($http,$params,$method = 'post'){
        if($method == 'post')return http::post($http,$params);
        else return http::get($http,$params);
    }

    /**
     * @param array $params передаваемые параметры(scope )
     * @param $url
     * @return string
     */
    public static function get_url()
    {
        $google = self::get_conf();
        debug($google,'google param ');
        $params = [
            'client_id'     => $google['id'],
            'client_secret' => $google['secret'],
            'response_type' =>'code',
            'redirect_uri'  => self::get_redirect_url(),
            'scope'         =>'https://www.googleapis.com/auth/userinfo.email https://www.google.com/m8/feeds',
        ];
        debug(self::get_redirect_url(),'self::get_redirect_url ');
        return self::$auth_google.http_build_query($params);

    }

    public static function get_conf(){
        return config::get('google_login');
    }
    public static function get_redirect_url($url=false){
        if($url) return $url;
        else return ('http://'.$_SERVER['HTTP_HOST'].'/oauth/google');
    }
}