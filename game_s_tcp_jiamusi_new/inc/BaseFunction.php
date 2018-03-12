<?php
/**
 * @author xuqiang76@163.com
 * @final 20160929
 */

namespace gf\inc;

use gf\conf\Config;
use gf\inc\Room;

class BaseFunction
{
	const WEB_C_VERSION = '0.0.3';
	
	public static function web_curl($data = array('act'=>1), $act = null, $act_data = null)	
	{
		$result ='';
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
		$cli->post(Config::WEB_PATH, $data_send, function ($cli) use ($act, $act_data)
		{	     	
	       // if(!empty(Config::DEBUG))
	        //{
				//$result = json_decode($cli->body,true);
				//var_dump($result['data']['room_type']);				
				//$cli->get('/index.php', function ($cli)
				//{
					//var_dump($cli->cookies);
					//var_dump($cli->headers);
					//var_dump($cli->body);
				//});
	       // }
            $result = json_decode($cli->body,true);

			if($act == 'get_conf')
			{	 	
				$result = json_decode($cli->body,true);
				if ($result && empty($result['code']) && empty($result['sub_code'] ) )
				{					
					if(isset(Room::$$act_data))
					{
						Room::$$act_data = $result;											
					}
				}
			}
			unset($cli);
			$cli = NULL;
		});
		return $result;
	}
	
	public static function need_currency($room_type,$game_type, $set_num, &$currency_type = 1)
	{
		$return = 0;
		$currency_type = 1;
		foreach ($room_type as $obj_game_currency)
		{
			if($obj_game_currency['game_type'] == $game_type)
			{
				foreach ($obj_game_currency['set_num'] as $key => $val)
				{
					if($val == $set_num)
					{
						$return = $obj_game_currency['currency'][$key];
					}
				}
				if(isset($obj_game_currency['use_currency']))
				{
					$currency_type = $obj_game_currency['use_currency'];
				}
			}
		}
		return $return;
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