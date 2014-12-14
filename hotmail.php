<?php

class oauth_hotmail2
{
    protected static $authorize_url = 'https://login.live.com/oauth20_authorize.srf?';
    protected static $access_token_url = 'https://login.live.com/oauth20_token.srf';
    protected static $api_url = 'https://apis.live.net/v5.0/';

    public static function get_token($code,$client) {
        try{
            if(!$code) throw new Exception("No code to auth ht :". __METHOD__.' line :'.__LINE__);
            if(!$client) throw new Exception("No client data to auth ht :". __METHOD__.' line :'.__LINE__);
            $get_token = [
                'client_id'     => $client['id'],
                'client_secret' => $client['secret'],
                'code'          => $code,
                'redirect_uri'  => self::get_redirect_url(),
                'grant_type'    => 'authorization_code',

            ];
            return json_decode(self::get_method(self::$access_token_url,$get_token,'get'),true);
        }
        catch(Exception $e){
            error_log($e->getMessage());
        }
    }
    public static function get_user_data($code){
        $client = self::get_conf();
        $token = self::get_token($code,$client);
        $get_user_info = [
            'access_token'  => $token['access_token']
        ];
        return json_decode(self::get_method(self::$api_url.'me',$get_user_info,'get'),true);
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
        $ht = self::get_conf();
        debug($ht,'ht param ');
        $params = [
            'client_id' => $ht['id'],
            'redirect_uri' => self::get_redirect_url(),
            'response_type' => 'code',
            'scope' => implode(',', ['wl.basic', 'wl.signin', 'wl.emails', 'wl.birthday', 'wl.contacts_birthday']),
        ];
        return self::$authorize_url.http_build_query($params);


    }

    public static function get_conf(){
        return config::get('social-networks')['hotmail'];
    }
    public static function get_redirect_url($url=false){
        if($url) return $url;
        else return ('http://'.$_SERVER['HTTP_HOST'].'/oauth/ht');
    }
}