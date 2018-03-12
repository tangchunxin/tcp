<?php
/**
 * @author xuqiang76@163.com
 * @final 20160929
 */

namespace gf\inc;

use gf\conf\Config;

class BaseFunction
{
	const WEB_C_VERSION = '0.0.3';
	
	public static function web_curl($data = array('act'=>1))
	{
		$host_arr = explode(':', Config::WEB_HOST);
		if(empty($host_arr[1]))
		{
			$host_arr[1] = 80;
		}

		$cli = new \Swoole\Http\Client($host_arr[0], intval($host_arr[1]));
		$cli->setHeaders(array('User-Agent' => 'swoole-http-client'));
		$cli->setCookies(array());

		$randkey = self::encryptMD5($data);
		$data_send = array('randkey'=>$randkey, 'c_version'=>self::WEB_C_VERSION, 'parameter'=>json_encode($data) );
		//var_dump($data_send);
		$cli->post(Config::WEB_PATH, $data_send, function ($cli)
		{
	        if(!empty(Config::DEBUG))
	        {
				var_dump($cli->body);
	//			$cli->get('/index.php', function ($cli)
	//			{
	//				var_dump($cli->cookies);
	//				var_dump($cli->headers);
	//			});
	        }
			unset($cli);
			$cli = NULL;
		});
	}
	
	public static function encryptMD5($data)
	{
		$content = '';
		if(!$data || !is_array($data))
		{
			return $content;
		}
		ksort($data);
		foreach ($data as $key => $value)
		{
			$content = $content.$key.$value;
		}
		if(!$content)
		{
			return $content;
		}

		return self::sub_encryptMD5($content);
	}

	public static function sub_encryptMD5($content)
	{
		$content = $content.Config::RPC_KEY;
		$content = md5($content);
		if( strlen($content) > 10 )
		{
			$content = substr($content, 0, 10);
		}
		return $content;
	}

}