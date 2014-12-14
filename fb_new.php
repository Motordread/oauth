<?php

class oauth_fb_new
{
    private static $auth_fb         = 'https://www.facebook.com/dialog/oauth?';
    private static $token_fb        = 'https://graph.facebook.com/oauth/access_token';
    private static $user_info_fb    = 'https://graph.facebook.com/me';
    public  static $email;

    public static function get_token($code,$client) {
        try{
            if(!$code) throw new Exception("No code to auth fb:". __METHOD__.' line :'.__LINE__);
            if(!$client) throw new Exception("No client data to auth fb:". __METHOD__.' line :'.__LINE__);
            $get_token = [
                'client_id'     => $client['id'],
                'redirect_uri'  => self::get_redirect_url(),
                'client_secret' => $client['secret'],
                'code'          => $code,
            ];
            parse_str(self::get_method(self::$token_fb,$get_token,'get'));
            if($access_token) return $access_token;
            else throw new Exception("No token data to auth fb:". __METHOD__.' line :'.__LINE__);
        }
        catch(Exception $e){
            debug($e->getMessage());
        }
    }
    public static function get_user_data($code){
        $client = self::get_conf();
        $token = self::get_token($code,$client);

        $get_user_info = [
            'access_token' => $token,
        ];
        return json_decode(self::get_method(self::$user_info_fb,$get_user_info,'get'),true);
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
        $fb = self::get_conf();
        debug($fb,'fb params ');
        $params = [
            'client_id' => $fb['id'],
            'secret_key'=>$fb['secret'],
            'redirect_uri'  => self::get_redirect_url(),
//                    'redirect_uri'  => 'http://znakomster.dev',
            'scope' => implode(',', $fb['scope'])
        ];

//        debug(self::get_redirect_url(),'self::get_redirect_url ');
        return self::$auth_fb.http_build_query($params);
    }

    public static function get_conf(){
        return config::get('facebook');
    }

    public static function get_redirect_url($url=false) {
        if($url) return $url;
        else return ('http://'.$_SERVER['HTTP_HOST'].'/oauth/fb');
    }
}