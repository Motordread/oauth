<?php

class oauth_ok
{
    private static $auth_ok         = 'http://www.odnoklassniki.ru/oauth/authorize?';
    private static $token_ok        = 'http://api.odnoklassniki.ru/oauth/token.do';
    private static $user_info_ok    = 'http://api.odnoklassniki.ru/fb.do';

    public static function get_token($code,$client) {
        try{
            if(!$code) throw new Exception("No code to auth odnoklassniki :". __METHOD__.' line :'.__LINE__);
            if(!$client) throw new Exception("No client data to auth odnoklassniki :". __METHOD__.' line :'.__LINE__);
            $get_token = [
                'code' => $code,
                'redirect_uri' => self::get_redirect_url(),
                'client_id' => $client['client_id'],
                'client_secret' => $client['client_secret'],
                'grant_type' => 'authorization_code',            ];
            return json_decode(self::get_method(self::$token_ok,$get_token),true);
        }
        catch(Exception $e) {
            debug($e->getMessage());
        }
    }
    public static function get_user_data($code){
        $client = self::get_conf();
        $token = self::get_token($code,$client);
        var_dump($token);
        $sig = md5('application_key='.$client['public_key'].'format=JSONmethod=users.getCurrentUser'.md5($token['access_token'].$client['client_secret']));

        $get_user_info = [
            'application_key'   => $client['public_key'],
            'format'            => 'JSON',
            'method'            => 'users.getCurrentUser',
            'access_token'      => $token['access_token'],
            'sig'               => $sig,
        ];
        return json_decode(self::get_method(self::$user_info_ok,$get_user_info,'get'),true);
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
        $ok = self::get_conf();
        debug($ok,'ok params ');
        $params = [
            'client_id'     => $ok['client_id'],
            'response_type' =>'code',
            'redirect_uri'  => self::get_redirect_url(),
        ];

//        debug(self::get_redirect_url(),'self::get_redirect_url ');
        return self::$auth_ok.http_build_query($params);
    }

    public static function get_conf(){
        return config::get('odnoklassniki');
    }

    public static function get_redirect_url($url=false) {
        if($url) return $url;
        else return ('http://'.$_SERVER['HTTP_HOST'].'/oauth/ok');
    }
}