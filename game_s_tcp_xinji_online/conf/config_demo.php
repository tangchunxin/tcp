<?php

namespace gf\conf;

class Config
{
	const WASHCARD = true;	//洗牌
	const DEBUG = true;
	const IS_ROOM_OWNER_PAY = true;  //是否房主扣钻
	const PLATFORM = 'gfplay';
	const GAME_TYPE = 5;
	const IS_CIRCLE = true;  //按圈打牌  

	const LOG_FILE = '/data/www/tcp/game_s_tcp_baoding/log/swoole.log';
	
	public static $set_num_arr = array(1=>2, 2=>3, 4=>4);	//圈
	//public static $set_num_arr_south = array(4=>1, 8=>2);	//
	
	const API_KEY = 'NCBDpay';
	const GAME_PORT = 120;

	const WEB_HOST = '127.0.0.1:80';
	const WEB_PATH = '/mahjong/game_s_http_baoding/index.php';
	const RPC_KEY = 'gfplay is best gfplay is best';
	
	const WHITE_IP = array('10.135.21.11', '127.0.0.1');	//远程连接白名单
	
	

}
