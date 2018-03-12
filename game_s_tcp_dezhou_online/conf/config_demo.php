<?php

namespace gf\conf;

class Config
{
	const WASHCARD = true;	//洗牌
	const DEBUG = true;
	const IS_ROOM_OWNER_PAY = true;  //是否房主扣钻
	const PLATFORM = 'gfplay';
	const GAME_TYPE = 10;

	const LOG_FILE = '/data/www/tcp/game_s_tcp_dezhou/log/swoole.log';
	
	public static $set_num_arr = array(4=>2, 8=>3, 16=>4);	//8局1钻
	//public static $set_num_arr_south = array(4=>1, 8=>2);	//
	
	const API_KEY = 'NCBDpay';
	const GAME_PORT = 150;

	const WEB_HOST = '127.0.0.1:80';
	const WEB_PATH = '/mahjong/game_s_http_dezhou/index.php';
	const RPC_KEY = 'gfplay is best gfplay is best';
	
	const WHITE_IP = array('10.135.21.11', '127.0.0.1');	//远程连接白名单
	
	

}
