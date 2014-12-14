<?php

class oauth_yandex
{
    private static $auth_ya          = 'https://oauth.yandex.ru/authorize?';
    private static $token_ya         = 'https://oauth.yandex.ru/token';
    private static $user_info_ya     = 'https://login.yandex.ru/info';
    public  static $url_request_params;

    public static function get_token($code,$client){
        try{
            if(!$code) throw new Exception("No code to auth yandex :". __METHOD__.' line :'.__LINE__);
            if(!$client) throw new Exception("No client data to auth yandex :". __METHOD__.' line :'.__LINE__);
            $params = [
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'client_id'     => $client['id'],
                'client_secret' => $client['key'],
            ];

            return  json_decode(self::get_method(self::$token_ya, $params),true);
        }
        catch(Exception $e) {
            debug($e->getMessage());
        }
    }
    public static function get_user_data($code){
        $client = self::get_conf();
        $token = self::get_token($code,$client);
//        var_dump($_SESSION);exit;
        $get_user_info = [
            'format'       => 'json',
            'oauth_token'  => $token['access_token']
        ];

        return json_decode(self::get_method(self::$user_info_ya, $get_user_info,'get'), true);
    }

    /**
     * @param $http
     * @param $params
     * @return mixed
     */
    public static  function get_method($http,$params,$method = 'post'){
        if($method == 'post')   return http::post($http,$params);
        else return http::get($http,$params);
    }

    /**
     * @param array $params передаваемые параметры(scope )
     * @param $url
     * @return string
     */
    public static function get_url()
    {
        $ya = self::get_conf();
        $params = [
            'response_type' => 'code',
            'client_id'     => $ya['id'],
            'display'       => 'popup'
        ];
        return self::$auth_ya.http_build_query($params);
    }

    public static function get_conf(){
        return config::get('yandex');
    }

    public static function get_redirect_url($url=false) {
        if($url) return $url;
        else return ('http://'.$_SERVER['HTTP_HOST'].'/oauth/yandex');
    }
}