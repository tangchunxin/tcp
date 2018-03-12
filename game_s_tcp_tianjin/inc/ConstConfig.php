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
	const ROOM_STATE_NULL = 0;         // 未定义
	const ROOM_STATE_OVER = 1;         // 空闲
	const ROOM_STATE_OPEN = 2;         // 开放
	const ROOM_STATE_GAMEING = 3;      // 正在游戏	
	
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
	const RECORD_CHI = 1;    //吃牌
	const RECORD_PENG = 2;  //碰牌
	const RECORD_ZHIGANG = 3;   //直杠
	const RECORD_ANGANG = 4;   //暗杠
	const RECORD_ZHUANGANG = 5;  //弯杠
	const RECORD_HU = 6;   //胡
	const RECORD_ZIMO = 7;  //自摸
	const RECORD_DISCARD = 8;  //出牌
	const RECORD_DRAW = 9;  //发牌
	const RECORD_DEALER = 10;  //开始游戏
	const RECORD_GENZHUANG = 11;  //跟庄
	const RECORD_HUAN3 = 12;   //换三张
	const RECORD_DINGQUE = 13; //定缺
	const RECORD_PAOZI = 14;  //下炮子
	const RECORD_FANHUN = 15;  //翻混
	const RECORD_DRAW_ALL = 16; //批量发牌
	const RECORD_MINGLOU = 17;  //明楼
	const RECORD_ZHUANIAO = 18;  //抓鸟
	const RECORD_XIAOSA = 19;   //消失

	CONST RECORD_PENG_ZA = 20;  //碰砸
	CONST RECORD_ZHIGANG_ZA = 21;  //直杠砸
	CONST RECORD_ANGANG_ZA = 22;  //暗杠砸
	CONST RECORD_BIAN = 23;  //边
	CONST RECORD_ZUAN = 24;  //钻

	const RECORD_KOU_CARD = 25;  //扣牌
	const RECORD_YIKOUXIANG = 26;  //一口香
	const RECORD_HU_QIANGGANG = 27;	//抢杠胡
	const RECORD_DRAW_ALL_DABING = 28; //推大饼 发牌
	const RECORD_PASS = 29; //过牌
	const RECORD_CONTEND_BANKER = 30; //拉庄
	const RECORD_KOU_TING = 31;//扣听                                  

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
	const DAO_PAI_TYPE_YINGANG = 6; //银杠
	const DAO_PAI_TYPE_JINGANG = 7; //金杠

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
	const SYSTEMPHASE_CONTEND_BANKER = 11; // 拉庄阶段
	const SYSTEMPHASE_KOU_TING = 12; // 扣听阶段
	
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
	const PLAYER_STATUS_XIA_PAO = 9;	// 下炮子中
	const PLAYER_STATUS_KOU_CARD = 10; 	// 扣四阶段
	const PLAYER_STATUS_CONTEND_BANKER = 11; 	// 拉庄阶段
	const PLAYER_STATUS_KOU_TING = 12; 	// 扣听阶段

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

	const ALL_CARD_112_BAIBAN = [1,1,1,1,    2,2,2,2,     3,3,3,3,     4,4,4,4,     5,5,5,5,     6,6,6,6,     7,7,7,7,     8,8,8,8,     9,9,9,9
	,17,17,17,17, 18,18,18,18, 19,19,19,19, 20,20,20,20, 21,21,21,21, 22,22,22,22, 23,23,23,23, 24,24,24,24, 25,25,25,25
	,33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41
	,55,55,55,55
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
    const SYSTERM_PHASE_ERROR = 22;
    const NOT_HAVE_OUT_CARD = 23;

    public static $error_describe = [
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
        self::SYSTERM_PHASE_ERROR => '系统状态错误',
        self::NOT_HAVE_OUT_CARD => '没有出牌',
    ];

}


