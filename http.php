<?php

class http
{

	public static $info = [];
	public static function  request ( $url,  $params = null, $method = 'GET', array $headers = [ ], $timeout = 10, $additional = [], $get_info = false )
	{
		self::$info = [];
		$ch = curl_init();
		$params = (is_array($params) ? http_build_query($params) : $params);
		if ( $method == 'GET' ) {
			if ( !empty( $params ) ) {
				$url .= ( strpos( $url, '?' ) ? '&' : '?' ) . $params ;
			}

			curl_setopt( $ch, CURLOPT_URL, $url );
		} else {
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
		}

		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_VERBOSE, 0 );

		if (is_array($additional)) {
			foreach ($additional as $option=>$value) {
				curl_setopt( $ch, $option, $value );
			}
		}

		$result = curl_exec( $ch );
		if (!$result) {
			error_log('[DEBUG] http: error: ' . curl_error($ch). '; url: '.$url);
		} else {
			if ($get_info) {
				if ($get_info === true) {
					self::$info = curl_getinfo($ch);
				} else {
					self::$info[$get_info] = curl_getinfo($ch, $get_info);
				}
			}
		}

		curl_close( $ch );

		return $result;
	}

	public static function get ( $url, array $params = [ ], array $headers = [ ], $timeout = 10 )
	{
		return self::request( $url, $params, 'GET', $headers, $timeout );
	}

	public static function post ( $url, $params = null , array $headers = [ ], $timeout = 10 )
	{
		return self::request( $url, $params, 'POST', $headers, $timeout );
	}
}
