<?php

namespace gf\conf;

class Config
{
	const WASHCARD = true;	//洗牌
	const DEBUG = true;
	const TEST_PAI = 1;
	//－－－－－－－－－－－－－玩家解散游戏状态 －－－－－－－－－－－－－－－－－－－
	const CANCLE_GAME_CLOCKER = 1; //解散房间时是否用倒计时 0 不用 1 用
	const CANCLE_GAME_CLOCKER_NUM = 20; //倒计时设定时间
	const CANCLE_GAME_CLOCKER_LIMIT = 2; //倒计时所剩时间（到此时间，等待心跳解散房间）
	
	const PLATFORM = 'gfplay';
	const LOG_FILE = '/data/www/tcp/game_s_tcp_baoding/log/swoole.log';
	
	const API_KEY = 'NCBDpay';
	const GAME_PORT = 120;

	const WEB_HOST = '127.0.0.1:80';
	const WEB_PATH = '/mahjong/game_s_http_baoding/index.php';
	const RPC_KEY = 'gfplay is best gfplay is best';
	
	const WHITE_IP = array('10.135.21.11', '127.0.0.1');	//远程连接白名单



}
