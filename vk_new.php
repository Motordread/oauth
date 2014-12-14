<?php

class oauth_vk
{
    private static $auth_vk         = 'http://oauth.vk.com/authorize?';
    private static $token_vk        = 'https://oauth.vk.com/access_token';
    private static $user_info_vk    = 'https://api.vk.com/method/users.get';
    public  static $email;

    public static function get_token($code,$client) {
        try{
            if(!$code) throw new Exception("No code to auth vk :". __METHOD__.' line :'.__LINE__);
            if(!$client) throw new Exception("No client data to auth vk :". __METHOD__.' line :'.__LINE__);
            $get_token = [
                'client_id'     => $client['id'],
                'client_secret' => $client['secret'],
                'redirect_uri'  => self::get_redirect_url(),
                'code'          => $code,
            ];
            return json_decode(self::get_method(self::$token_vk,$get_token),true);
        }
        catch(Exception $e){
            debug($e->getMessage());
        }
    }
    public static function get_user_data($code){
        $client = self::get_conf();
        $token = self::get_token($code,$client);
        if($token['email']){
            self::$email = $token['email'];
        }
        $get_user_info = [
            'uids'  => $token['user_id'],
            'fields'       => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big,email',
            'access_token' => $token['access_token']
        ];
        return json_decode(self::get_method(self::$user_info_vk,$get_user_info,'get'),true);
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
        $vk = self::get_conf();
        debug($vk,'vk params ');
        $params = [
            'client_id'     => $vk['id'],
            'response_type' => 'code',
            'redirect_uri'  => self::get_redirect_url(),
            'scope'         => 'email',//email
            'v'             => '5.5'
        ];

//        debug(self::get_redirect_url(),'self::get_redirect_url ');
        return self::$auth_vk.http_build_query($params);
    }

    public static function get_conf(){
        return config::get('vk');
    }

    public static function get_redirect_url($url=false) {
        if($url) return $url;
        else return ('http://'.$_SERVER['HTTP_HOST'].'/oauth/vk');
    }
}