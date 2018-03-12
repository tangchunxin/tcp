<?php
/**
 * @author xuqiang76@163.com
 * @final 20161025
 */

namespace gf\inc;

class ConstConfig
{
	public static $hu_data = array();
	public static $hu_data_feng = array();
	public static $hu_data_feng_shun = array();

	public static $hu_data_insert = array();
	public static $hu_data_insert_feng = array();	
	
	public static function get_hu_data()
	{
		self::$hu_data = require("./inc/mahjong_data.php");
		self::$hu_data_feng = require("./inc/mahjong_data_feng.php");
		self::$hu_data_feng_shun = require("./inc/mahjong_data_feng_shun.php");
		
		self::$hu_data_insert = require("./inc/mahjong_data_insert.php");
		self::$hu_data_insert_feng = require("./inc/mahjong_data_insert_feng.php");		
	}

	//------------房间状态--------------	
	const ROOM_STATE_NULL = 0;
	const ROOM_STATE_OVER = 1;
	const ROOM_STATE_OPEN = 2;
	const ROOM_STATE_GAMEING = 3;	
	
	//－－－－－－－－－－－－－基础参数 －－－－－－－－－－－－－－－－－－－

	const BASE_CARD_NUM_FENG = 136; // 牌的个数,万条筒共108张 + 字牌 28张
	const BASE_CARD_NUM = 108; // 牌的个数,万条筒共108张
	const BASE_CARD_NUM_ZHONG = 120; // 牌的个数,万条筒共108张 + 中发白12张
	const BASE_CARD_NUM_HONG_ZHONG = 112; // 牌的个数,万条筒+红中 共108张
	const BASE_HOLD_CARD_NUM = 13; // 开始时手持牌的数量,庄家除外

	const BASE_CARD_NUM_TUIDABING = 36; // 牌的个数,筒共36张
	const BASE_HOLD_CARD_NUM_TUIDABING = 2; // 开始时手持牌的数量

	const BASE_LANDLORD_CARD_NUM = 54; // 斗地主牌的个数
	const BASE_LANDLORD_HOLD_CARD_NUM = 17; // 斗地主开始时手持牌的数量
	const BASE_LANDLORD_LEFT_CARD_NUM = 3; // 斗地主底牌数量

	//---------------记分----------------
	const SCORE_BASE = 1;					// 基数分
	const SCORE_FLEE_MUL = 3;	    // 逃跑分
	const SCORE_ZHA_HU = 3;	// 诈胡分

	//----------------------录像脚本-------------------------------------
	const RECORD_CHI = 1;
	const RECORD_PENG = 2;
	const RECORD_ZHIGANG = 3;
	const RECORD_ANGANG = 4;
	const RECORD_ZHUANGANG = 5;
	const RECORD_HU = 6;
	const RECORD_ZIMO = 7;
	const RECORD_DISCARD = 8;
	const RECORD_DRAW = 9;
	const RECORD_DEALER = 10;
	const RECORD_GENZHUANG = 11;
	const RECORD_HUAN3 = 12;
	const RECORD_DINGQUE = 13;
	const RECORD_PAOZI = 14;
	const RECORD_FANHUN = 15;
	const RECORD_DRAW_ALL = 16;
	const RECORD_MINGLOU = 17;
	const RECORD_ZHUANIAO = 18;
	const RECORD_XIAOSA = 19;

	CONST RECORD_PENG_ZA = 20;
	CONST RECORD_ZHIGANG_ZA = 21;
	CONST RECORD_ANGANG_ZA = 22;
	CONST RECORD_BIAN = 23;
	CONST RECORD_ZUAN = 24;

	const RECORD_KOU_CARD = 25;
	const RECORD_YIKOUXIANG = 26;
	const RECORD_HU_QIANGGANG = 27;	//兼容老版客户端 ，以后改成27
	const RECORD_DRAW_ALL_DABING = 28; //推大饼 发牌
	const RECORD_PASS = 29; //过牌

	//－－－－－－－－－－－ 牌的类型－－－－－－－－－－－－－－－－－－－－－－－－－
	const PAI_TYPE_WAN = 0;
	const PAI_TYPE_TIAO = 1;
	const PAI_TYPE_TONG = 2;
	const PAI_TYPE_FENG = 3;
	const PAI_TYPE_DRAGON = 4;
	const PAI_TYPE_PAI_TYPE_INVALID = 255;

	//－－－－－－－－－－－ 倒牌的类型－－－－－－－－－－－－－－－－－－－－－－－－－
	const DAO_PAI_TYPE_SHUN = 1; //顺子
	const DAO_PAI_TYPE_KE = 2; //刻
	const DAO_PAI_TYPE_MINGGANG = 3; //直杠，别人打给我地明杠
	const DAO_PAI_TYPE_ANGANG = 4; //暗杠
	const DAO_PAI_TYPE_WANGANG = 5; //弯杠，先碰再自己摸到第二种明杠

	const DAO_PAI_TYPE_BIAN = 10;	//沧州的边
	const DAO_PAI_TYPE_ZUAN = 11;	//沧州的钻
	const DAO_PAI_TYPE_ZA = 12;	//沧州的砸
	const DAO_PAI_TYPE_MINGGANG_ZA = 13;	//沧州的直杠砸
	const DAO_PAI_TYPE_ANGANG_ZA = 14;	//沧州的暗杠砸

	//－－－－－－－－－－－ 斗地主牌的类型－－－－－－－－－－－－－－－－－－－－－－－－－
	const PAI_TYPE_LANDLORD_ONE = 1;	//单张
	const PAI_TYPE_LANDLORD_PAIR = 2;	//对子
	const PAI_TYPE_LANDLORD_JOKER_BOMB = 3;	//火箭（王炸）
	const PAI_TYPE_LANDLORD_TRIPLE = 4;	//三张
	const PAI_TYPE_LANDLORD_TRIPLE_ONE = 5;	//三带一单

	const PAI_TYPE_LANDLORD_TRIPLE_PAIR = 6;	//三带一对
	const PAI_TYPE_LANDLORD_BOMB = 7;	//炸蛋
	const PAI_TYPE_LANDLORD_QUARTET_TWO = 8;	//四带二单
	const PAI_TYPE_LANDLORD_QUARTET_TWO_PAIR = 9;	//四带二对
	const PAI_TYPE_LANDLORD_STRAIGHT = 10;	//单顺

	const PAI_TYPE_LANDLORD_STRAIGHT_PAIR = 11;	//连对
	const PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE = 12;	//连三
	const PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE_ONE = 13;	//飞机带单
	const PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE_PAIR = 14;	//飞机带对
	const PAI_TYPE_LANDLORD_INVALID = 255;	//非法牌型

	//－－－－－－－－－－－－－阶段状态 －－－－－－－－－－－－－－－－－－－
	//const SYSTEMPHASE_WAITING_START = 1; // 用户进入而又没开始的状态
	const SYSTEMPHASE_HUAN_3 = 2; // 换三张阶段
	const SYSTEMPHASE_DING_QUE = 3; // 定缺阶段
	const SYSTEMPHASE_THINKING_OUT_CARD = 4; // 思考打牌阶段
	const SYSTEMPHASE_CHOOSING = 5; // 竞争抉择阶段,有碰,杠,胡时的抉择
	const SYSTEMPHASE_SET_OVER = 6; // 结束阶段
	const SYSTEMPHASE_XIA_PAO = 9; // 下炮阶段
	const SYSTEMPHASE_KOU_CARD = 10; // 扣四阶段
	
	const SYSTEMPHASE_LANDLORD_SHOW_BRFORE = 20; // 斗地主发牌之前明牌
	const SYSTEMPHASE_LANDLORD_SHOW_AFTER = 21; // 斗地主发牌之之后明牌
	const SYSTEMPHASE_LANDLORD_DIBS = 22; // 斗地主叫牌阶段
	const SYSTEMPHASE_LANDLORD_DOUBLE = 23; // 斗地主叫牌之后加倍
	const SYSTEMPHASE_LANDLORD_PLAY_CARD = 24; // 斗地主打牌阶段
	const SYSTEMPHASE_LANDLORD_SET_OVER = 25; // 斗地主结束阶段

	const SYSTEMPHASE_INVALID = 255; // 非法阶段，还没有开始

	//－－－－－－－－－－－－－玩家状态 －－－－－－－－－－－－－－－－－－－
	const PLAYER_STATUS_HUAN3ING = 1;	//换三张中	
	const PLAYER_STATUS_DINGQUEING = 2;	//定缺中	
	const PLAYER_STATUS_WAITING = 3;    //等待中
	const PLAYER_STATUS_THINK_OUTCARD = 4;  //思考出牌中
	const PLAYER_STATUS_CHOOSING = 5;      //竞争选择中
	const PLAYER_STATUS_BLOOD_HU = 6;	//血战到底胡牌
	const PLAYER_STATUS_HUAZHU = 7;
	const PLAYER_STATUS_DAJIAO = 8;	
	const PLAYER_STATUS_XIA_PAO = 9;	//下炮子中
	const PLAYER_STATUS_KOU_CARD = 10; // 扣四阶段
	
	const PLAYER_LANDLORD_STATUS_DIBS = 20; // 斗地主叫牌阶段
	const PLAYER_LANDLORD_STATUS_WAITING = 21;    //斗地主等待中
	const PLAYER_LANDLORD_STATUS_THINK_OUTCARD = 22;  //斗地主思考出牌中
	const PLAYER_LANDLORD_STATUS_THINK_SHOW = 23;  //斗地主思考明牌
	const PLAYER_LANDLORD_STATUS_THINK_DOUBLE = 24;  //斗地主思考翻倍

	const PLAYER_STATUS_PLAYER_STATUS_INVALIDE = 255;

	//－－－－－－－－－－－－－跟庄状态 －－－－－－－－－－－－－－－－－－－
	const FOLLOW_STATUS = 1;    //可跟庄
	const NOT_FOLLOW_STATUS = 0;    //不可跟庄
	const N_FOLLOWSCORE = 1;    //可跟庄分

	//－－－－－－－－－－－－－结束原因 －－－－－－－－－－－－－－－－－－－
	const END_REASON_FLEE = 1;      //逃跑了
	const END_REASON_HU = 2;       //胡了
	const END_REASON_NOCARD = 3;  //没有牌了

	//－－－－－－－－－－－－－玩家终局时输赢状态 －－－－－－－－－－－－－－－－－－－
	const WIN_STATUS_NOTHING = 0; //事不关己
	const WIN_STATUS_ZI_MO = 1; //自摸
	const WIN_STATUS_CHI_PAO = 2; //吃炮

	//－－－－－－－－－－－－－玩家终局时输赢状态 －－－－－－－－－－－－－－－－－－－
	const LOSE_STATUS_FANG_PAO = 6; //放炮
	const LOSE_STATUS_BAOPAI_QIANGGANG = 7; //抢杠包牌


	const CARD_INDEX = [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10,11,12,13,14,15	//万
	,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31	//条
	,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47	//筒
	,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63	//东南西北中发白
	,64,65,66,67,68,69,70,71,72,73							//春夏秋冬梅兰竹菊，空白牌
	];

	const ALL_CARD_108 = [1,1,1,1,    2,2,2,2,     3,3,3,3,     4,4,4,4,     5,5,5,5,     6,6,6,6,     7,7,7,7,     8,8,8,8,     9,9,9,9
	,17,17,17,17, 18,18,18,18, 19,19,19,19, 20,20,20,20, 21,21,21,21, 22,22,22,22, 23,23,23,23, 24,24,24,24, 25,25,25,25
	,33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41
	];
	
	const ALL_CARD_112 = [1,1,1,1,    2,2,2,2,     3,3,3,3,     4,4,4,4,     5,5,5,5,     6,6,6,6,     7,7,7,7,     8,8,8,8,     9,9,9,9
	,17,17,17,17, 18,18,18,18, 19,19,19,19, 20,20,20,20, 21,21,21,21, 22,22,22,22, 23,23,23,23, 24,24,24,24, 25,25,25,25
	,33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41
	,53,53,53,53
	];

	const ALL_CARD_120 = [1,1,1,1,    2,2,2,2,     3,3,3,3,     4,4,4,4,     5,5,5,5,     6,6,6,6,     7,7,7,7,     8,8,8,8,     9,9,9,9
	,17,17,17,17, 18,18,18,18, 19,19,19,19, 20,20,20,20, 21,21,21,21, 22,22,22,22, 23,23,23,23, 24,24,24,24, 25,25,25,25
	,33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41
	,53,53,53,53, 54,54,54,54, 55,55,55,55
	];
	
	const ALL_CARD_136 = [1,1,1,1,    2,2,2,2,     3,3,3,3,     4,4,4,4,     5,5,5,5,     6,6,6,6,     7,7,7,7,     8,8,8,8,     9,9,9,9
	,17,17,17,17, 18,18,18,18, 19,19,19,19, 20,20,20,20, 21,21,21,21, 22,22,22,22, 23,23,23,23, 24,24,24,24, 25,25,25,25
	,33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41
	,49,49,49,49, 50,50,50,50, 51,51,51,51, 52,52,52,52, 53,53,53,53, 54,54,54,54, 55,55,55,55
	];

	const ALL_CARD_36 = [33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41];
	//////////////////////////////////////////////////////////////////////////////

	const CARD_INDEX_TEST = [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10,11,12,13,14,15	//万
	,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31	//条
	,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47	//筒
	,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63	//东南西北中发白
	,64,65,66,67,68,69,70,71,72,73							//春夏秋冬梅兰竹菊，空白牌
	];

	const ALL_CARD_108_TEST = [4,6,7,8,9,23,24,25,17,17,33,34,35,5,17,19,20,22,38,37,21,23,39,37,39,23,41,25,21,3,36,5,38,25,40,39,35,39,7,3,37,3,5,22,36,2,20,4,17,20,34,2,18,40,25,24,23,21,41,33,9,18,40,18,19,7,6,21,41,33,40,36,1,35,37,2,2,22,19,9,1,5,4,6,1,33,38,8,7,24,41,19,8,36,18,1,4,20,22,24,35,3,6,38,9,34,34,8];

	const ALL_CARD_112_TEST = [6,6,36,37,24,25,17,19,34,41,41,41,41,6,38,18,23,1,1,2,2,3,3,4,4,5,40,33,9,9,9,9,34,34,5,20,3,5,38,7,18,18,25,17,24,35,7,2,5,22,24,8,34,20,35,40,8,33,19,21,38,17,22,35,6,3,40,20,23,39,36,38,4,25,2,21,8,7,19,7,19,33,18,1,39,39,21,35,23,40,17,36,37,1,36,25,37,22,24,4,37,33,21,22,23,39,20,8,53,53,53,53];

	const ALL_CARD_120_TEST = [1,1,1,1,    2,2,2,2,     3,3,3,3,     4,4,4,4,     5,5,5,5,     6,6,6,6,     7,7,7,7,     8,8,8,8,     9,9,9,9
	,17,17,17,17, 18,18,18,18, 19,19,19,19, 20,20,20,20, 21,21,21,21, 22,22,22,22, 23,23,23,23, 24,24,24,24, 25,25,25,25
	,33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41
	,53,53,53,53, 54,54,54,54, 55,55,55,55
	];

	const ALL_CARD_136_TEST = [1,1,1,1,2,2,2,2,3,3,3,3,4,17,17,17,17,18,18,18,18,19,19,19,19,20,33,33,33,33,34,34,34,34,35,35,35,35,36,4,36,9,52,49,9,21,37,39,7,41,51,39,5,54,6,52,22,53,5,24,5,20,5,23,55,40,7,36,40,9,6,6,21,20,53,22,25,8,25,38,25,7,49,37,54,21,53,52,55,50,37,8,23,41,40,24,54,24,7,55,8,39,8,51,36,38,49,49,24,39,50,9,22,41,20,41,54,51,52,4,23,38,50,37,22,23,50,21,40,4,6,25,38,53,51,55];

	const ALL_CARD_36_TEST = [33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41];

	/////////
	const CARD_TEMP_LANDLORD = 
	[
	    [0, 0, 0, 0, 0, 0]
	    , [0, 0, 0, 0, 0, 0]    // 3  sum， diamonds， clubs， hearts， spades， joker
	    , [0, 0, 0, 0, 0, 0]    // 4
	    , [0, 0, 0, 0, 0, 0]    // 5
	    , [0, 0, 0, 0, 0, 0]    // 6
	    , [0, 0, 0, 0, 0, 0]    // 7

	    , [0, 0, 0, 0, 0, 0]    // 8
	    , [0, 0, 0, 0, 0, 0]    // 9
	    , [0, 0, 0, 0, 0, 0]    // 10
	    , [0, 0, 0, 0, 0, 0]    // J
	    , [0, 0, 0, 0, 0, 0]    // Q

	    , [0, 0, 0, 0, 0, 0]    // K
	    , [0, 0, 0, 0, 0, 0]    // A
	    , [0, 0, 0, 0, 0, 0]    // 2
	    , [0, 0, 0, 0, 0, 0]    // w small joker
	    , [0, 0, 0, 0, 0, 0]    // W big JOKER
	];
	
	const ALL_CARD_LANDLORD = 
	[
	    9,10,11,12	//3
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
	    ,97,98,99,100	//A
	    ,105,106,107,108	//2
	    ,117	//w
	    ,125	//W
	];

	const ALL_CARD_LANDLORD_TEST = 
	[ 10, 108, 43, 34, 57, 27, 17, 42, 82, 107, 106, 44, 28, 89, 84, 97, 41, 12, 65, 66, 83, 20, 9, 75, 18, 91, 98, 25, 33, 19, 50, 99, 11, 52, 90, 92, 76, 74, 73, 60, 59, 58, 51, 35, 36, 68, 81, 100, 125, 105, 67, 26, 117, 49 ];

	//错误码及描述
    const PARAMETER_ERROR = 1;
    const ROOM_PHASE_ERROR = 2;
    const ROOM_NOT_EXIST = 3;
    const ROOM_OWNER_ERROR = 4;
    const NOT_BELONG_THIS_ROOM = 5;
    const ROOM_IS_FULL = 6;
    const ROOM_BE_OCCUPIED = 7;
    const DISBANDED_ROOM_REQUEST = 8;
    const HAVE_UNFINISHED_GAME = 9;
    const NOT_HAVE_EAT = 10;
    const NOT_HAVE_ZHIGANG = 12;
    const GANG_ERROR = 13;
    const CURRENT_USER_ERROR = 14;
    const FREQUENT_REQUEST = 15;
    const HU_ERROR = 16;
    const CAN_ONLY_OUT_CARD = 17;
    const FRAUD = 18;
    const MISPLACED = 19;
    const NOT_NEED_SELECT = 20;
    const NOT_HAVE_PENG = 21;

    public static $error = [
        self::PARAMETER_ERROR => '参数错误',
        self::ROOM_PHASE_ERROR => '房间状态错误',
        self::ROOM_NOT_EXIST => '房间不存在',
        self::ROOM_OWNER_ERROR => '房主错误',
        self::NOT_BELONG_THIS_ROOM => '用户不属于本房间',
        self::ROOM_IS_FULL => '房间已满',
        self::ROOM_BE_OCCUPIED => '此房间已经被占用',
        self::DISBANDED_ROOM_REQUEST => '解散房间请求错误',
        self::HAVE_UNFINISHED_GAME => '你有未结束的游戏',
        self::NOT_HAVE_EAT => '当前用户无吃牌',
        self::NOT_HAVE_ZHIGANG => '当前用户无直杠',
        self::NOT_HAVE_PENG => '当前用户无碰',
        self::GANG_ERROR => '杠牌错误',
        self::CURRENT_USER_ERROR => '当前用户错误',
        self::FREQUENT_REQUEST => '连续发送胡牌信息',
        self::HU_ERROR => '胡牌错误',
        self::CAN_ONLY_OUT_CARD => '当前用户状态只能出牌',
        self::FRAUD => '诈胡',
        self::MISPLACED => '出牌错误',
        self::NOT_NEED_SELECT => '当前用户无需选择',
    ];


}


