<?php

class oauth_yahoo2
{
    private static $token_yahoo         = 'https://api.login.yahoo.com/oauth/v2/get_token';
    private static $token_request_yahoo = 'https://api.login.yahoo.com/oauth/v2/get_request_token';
    private static $user_info_yahoo     = 'https://social.yahooapis.com/v1/user';
    public  static $url_request_params;

    public static function get_token($oauth_token,$client,$oauth_verifier) {
        try{
            if(!$oauth_token) throw new Exception("No oauth_token to auth yahoo :". __METHOD__.' line :'.__LINE__);
            if(!$client) throw new Exception("No client data to auth yahoo :". __METHOD__.' line :'.__LINE__);
            if(!$oauth_verifier) throw new Exception("No verifier params to auth yahoo :". __METHOD__.' line :'.__LINE__);
            $method = 'PLAINTEXT';
            $params = [
                'oauth_consumer_key' => $client['client_id'],
                'oauth_signature_method' => $method,
                'oauth_version' => '1.0',
                'oauth_verifier' => $oauth_verifier,
                'oauth_token' => $oauth_token,
                'oauth_nonce' => uniqid(),
                'oauth_timestamp' => time(),
            ];
            $params['oauth_signature'] = self::get_sign($params, self::$token_yahoo, $client['client_secret'].'&'.$_SESSION['oauth_yahoo']['oauth_token_secret'], $method);
            $result = http::get(self::$token_yahoo, $params);
            parse_str($result, $response);
            return $response;
        }
        catch(Exception $e) {
            debug($e->getMessage());
        }
    }
    public static function get_user_data($oauth_token,$oauth_verifier){
        $client = self::get_conf();
        $token = self::get_token($oauth_token,$client,$oauth_verifier);
//        var_dump($_SESSION);exit;
        $get_user_info = [
            'realm' => 'yahooapis.com',
            'oauth_consumer_key' => $client['client_id'],
            'oauth_nonce' => uniqid(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0',
            'oauth_token' => $token['oauth_token'],
            'format' => 'json'
        ];

        $url = self::$user_info_yahoo.'/'.$token['xoauth_yahoo_guid'].'/profile';
        $sign_key = $client['client_secret'].'&'.$token['oauth_token_secret'] ;
        $params['oauth_signature'] = self::get_sign($get_user_info, $url, $sign_key);
        $response = http::get($url, $params);
        $data = json_decode($response, true);

        return $data;
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
        $yh = self::get_conf();
        $params = [
            'oauth_consumer_key' => $yh['client_id'],
            'oauth_nonce' => uniqid(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0',
            'oauth_callback' => $yh['redirect_uri']
        ];

        $params['oauth_signature'] = self::get_sign($params, self::$token_request_yahoo, $yh['client_secret'].'&');
        $str = http::get(self::$token_request_yahoo, $params);
//        var_dump(self::$url_request_params);
        //tokens params
        parse_str($str, $response);
        $_SESSION['oauth_yahoo']['oauth_token_secret']  = $response['oauth_token_secret'];
        if (!$response['xoauth_request_auth_url']) {
            debug(['$response' => $response, 'params' => $params,'host' => $_SERVER['HTTP_HOST']],'yahoo: fail get login url: ');
        }
        return $response['xoauth_request_auth_url'];
    }

    public static function get_conf(){
        return config::get('social-networks')['yahoo'];
    }

    public static function get_redirect_url($url=false) {
        if($url) return $url;
        else return ('http://'.$_SERVER['HTTP_HOST'].'/oauth/yahoo');
    }

    public static  function get_sign(array $params, $url, $sign_key, $method = 'HMAC-SHA1') {
        if ('PLAINTEXT' == $method) {
            $sign = $sign_key;
        } elseif('HMAC-SHA1' == $method) {
            ksort($params);
            $coded_params = [];
            foreach($params as $key => $value) {
                $coded_params[] = $key.'='.rawurlencode($value);
            }
            $base_string = 'GET&'.rawurlencode($url).'&'.rawurlencode(implode('&', $coded_params));
            $sign = base64_encode(hash_hmac('sha1', $base_string, $sign_key, true));
        } else {
            $sign = '';
        }

        return $sign;
    }

}