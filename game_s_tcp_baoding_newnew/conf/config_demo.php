<?php

namespace gf\conf;

class Config
{
	const WASHCARD = true;	//洗牌
	const DEBUG = true;
	const TEST_PAI = 1;
	
	//－－－－－－－－－－－－－玩家解散游戏状态 －－－－－－－－－－－－－－－－－－－
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

	const RES_BID = 0;
	const LAND_LORD_BASE = 1;	//斗地主底分

	const ALL_CARD_108_TEST = [4,6,7,8,9,23,24,25,17,17,33,34,35,5,17,19,20,22,38,37,21,23,39,37,39,23,41,25,21,3,36,5,38,25,40,39,35,39,7,3,37,3,5,22,36,2,20,4,17,20,34,2,18,40,25,24,23,21,41,33,9,18,40,18,19,7,6,21,41,33,40,36,1,35,37,2,2,22,19,9,1,5,4,6,1,33,38,8,7,24,41,19,8,36,18,1,4,20,22,24,35,3,6,38,9,34,34,8];

	const ALL_CARD_136_TEST = [1,1,1,1,2,2,2,2,3,3,3,3,4,17,17,17,17,18,18,18,18,19,19,19,19,20,33,33,33,33,34,34,34,34,35,35,35,35,36,4,36,9,52,49,9,21,37,39,7,41,51,39,5,54,6,52,22,53,5,24,5,20,5,23,55,40,7,36,40,9,6,6,21,20,53,22,25,8,25,38,25,7,49,37,54,21,53,52,55,50,37,8,23,41,40,24,54,24,7,55,8,39,8,51,36,38,49,49,24,39,50,9,22,41,20,41,54,51,52,4,23,38,50,37,22,23,50,21,40,4,6,25,38,53,51,55];

	const ALL_CARD_LANDLORD_TEST = 
	[ 10, 108, 43, 34, 57, 27, 17, 42, 82, 107, 106, 44, 28, 89, 84, 97, 41, 12, 65, 66, 83, 20, 9, 75, 18, 91, 98, 25, 33, 19, 50, 99, 11, 52, 90, 92, 76, 74, 73, 60, 59, 58, 51, 35, 36, 68, 81, 100, 125, 105, 67, 26, 117, 49 ];

	const ALL_CARD_DAHONG5_TEST = 
	[ 89, 117, 10, 9, 20, 11, 125, 12, 25, 91, 27, 28, 33, 35, 34, 36, 41, 42, 43, 44, 49, 50, 51, 52, 57, 58, 59, 60, 65, 66, 67, 68, 73, 74, 75, 19, 76, 18, 17, 26];
	
	const ALL_CARD_RUNFAST16_TEST = 
	[  9,10,11,12	//3
	,17,18,19,20	//4
	,25,26,27,28	//5
	,33,34,35,36	//6
	,41,42,43,44	//7

	,49,50,51,52	//8
	,57,58,59,60	//9
	,65,66,67,68	//10
	,73,74,75,76	//J
	,81,82,83,84	//Q

	,89,90,91,92	//K
	,98,99,100	    //A去掉方框A
	,108	       //红2
	];
	
	const ALL_CARD_RUNFAST15D_TEST = 
	[  9,10,11,12	//3
	,17,18,19,20	//4
	,25,26,27,28	//5
	,33,34,35,36	//6
	,41,42,43,44	//7

	,49,50,51,52	//8
	,57,58,59,60	//9
	,65,66,67,68	//10
	,73,74,75,76	//J
	,81,82,83,84	//Q

	,90,91,92	//K
	,99	//A
	,108	//2
	];

}	
