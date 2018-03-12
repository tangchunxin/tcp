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

	public static $hu_data_insert = array();
	public static $hu_data_insert_feng = array();

	public static function get_hu_data()
	{
		self::$hu_data = require("./inc/mahjong_data.php");
		self::$hu_data_feng = require("./inc/mahjong_data_feng.php");

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
	const BASE_HOLD_CARD_NUM = 13; // 开始时手持牌的数量,庄家除外

	//---------------记分----------------
	const SCORE_BASE = 1;					// 基数分
	const SCORE_FLEE_MUL = 3;	    // 逃跑分
	const SCORE_ZHA_HU = 3;	// 诈胡分

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


	//－－－－－－－－－－－－－阶段状态 －－－－－－－－－－－－－－－－－－－
	//const SYSTEMPHASE_WAITING_START = 1; // 用户进入而又没开始的状态
	const SYSTEMPHASE_XIA_PAO = 9; // 下炮阶段
	const SYSTEMPHASE_THINKING_OUT_CARD = 4; // 思考打牌阶段
	const SYSTEMPHASE_CHOOSING = 5; // 竞争抉择阶段,有碰,杠,胡时的抉择
	const SYSTEMPHASE_SET_OVER = 6; // 结束阶段
	const SYSTEMPHASE_INVALID = 255; // 非法阶段，还没有开始

	//－－－－－－－－－－－－－玩家状态 －－－－－－－－－－－－－－－－－－－
	const PLAYER_STATUS_XIA_PAO = 9;	//下炮子中
	const PLAYER_STATUS_WAITING = 3;    //等待中
	const PLAYER_STATUS_THINK_OUTCARD = 4;  //思考出牌中
	const PLAYER_STATUS_CHOOSING = 5;      //竞争选择中
	const PLAYER_STATUS_BLOOD_HU = 6;	//胡牌
	const PLAYER_STATUS_PLAYER_STATUS_INVALIDE = 255;

	//－－－－－－－－－－－－－跟庄状态 －－－－－－－－－－－－－－－－－－－
	const FOLLOW_STATUS = 1;    //可跟庄
	const NOT_FOLLOW_STATUS = 0;    //不可跟庄
	const N_FOLLOWSCORE = 1;    //可跟庄分

	//－－－－－－－－－－－－－结束原因 －－－－－－－－－－－－－－－－－－－
	const END_REASON_FLEE = 1;      //逃跑了
	const END_REASON_HU = 2;       //胡了
	const END_REASON_NOCARD = 3;  //没有牌了


	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21; //平胡
	const HU_TYPE_QIDUI = 22; //七对
	const HU_TYPE_PENGPENGHU = 23; //对对胡

	const HU_TYPE_FENGDING_TYPE_INVALID  = 0; //错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_TIANHU = 61; //天胡
	const ATTACHED_HU_DIHU = 62; //地胡
	const ATTACHED_HU_ZIMOFAN = 63; //自摸   默认
	const ATTACHED_HU_GANGKAI = 64; //杠开
	const ATTACHED_HU_QIANGGANG = 65; //抢杠

	const ATTACHED_HU_DADIAOCHE = 66; //大吊车
	const ATTACHED_HU_JIANGYISE = 67; //将一色
	const ATTACHED_HU_HUNYISE = 68; //混一色
	const ATTACHED_HU_QINGYISE = 69; //清一色
	const ATTACHED_HU_YITIAOLONG = 70; //一条龙
	const ATTACHED_HU_HAIDILAOYUE = 71; //海底捞月


	//－－－－－－－－－－－－－玩家终局时输赢状态 －－－－－－－－－－－－－－－－－－－
	const WIN_STATUS_NOTHING = 0; //事不关己
	const WIN_STATUS_ZI_MO = 1; //自摸
	const WIN_STATUS_CHI_PAO = 2; //吃炮
	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 3;  //直杠3分
	const M_ANGANG_SCORE = 2;   //暗杠  2分
	const M_WANGANG_SCORE = 1;   //弯杠  1分

	//－－－－－－－－－－－－－玩家终局时输赢状态 －－－－－－－－－－－－－－－－－－－
	const LOSE_STATUS_FANG_PAO = 6; //放炮
	const LOSE_STATUS_BAOPAI_QIANGGANG = 7; //抢杠包牌

	public static $hu_type_arr = array(
	self::HU_TYPE_PINGHU=>[self::HU_TYPE_PINGHU, 1, '平胡']  //平胡  不就倍分 算底分  作为低分2分

	,self::HU_TYPE_QIDUI=>[self::HU_TYPE_QIDUI, 7, '七对']
	,self::HU_TYPE_PENGPENGHU=>[self::HU_TYPE_PENGPENGHU, 2, '对对胡']


	);

	public static $attached_hu_arr = array(
	self::ATTACHED_HU_TIANHU=>[self::ATTACHED_HU_TIANHU, 5, '天胡']
	,self::ATTACHED_HU_DIHU=>[self::ATTACHED_HU_DIHU, 5, '地胡']
	,self::ATTACHED_HU_ZIMOFAN=>[self::ATTACHED_HU_ZIMOFAN, 0, '自摸']  //作为低分3分
	,self::ATTACHED_HU_GANGKAI=>[self::ATTACHED_HU_GANGKAI, 2, '杠上花']
	,self::ATTACHED_HU_QIANGGANG=>[self::ATTACHED_HU_QIANGGANG, 1, '抢杠']

	,self::ATTACHED_HU_QINGYISE=>[self::ATTACHED_HU_QINGYISE, 5, '清一色']
	,self::ATTACHED_HU_YITIAOLONG=>[self::ATTACHED_HU_YITIAOLONG, 4, '一条龙']
	,self::ATTACHED_HU_JIANGYISE=>[self::ATTACHED_HU_JIANGYISE, 5, '将一色']
	,self::ATTACHED_HU_HUNYISE=>[self::ATTACHED_HU_HUNYISE, 2, '混一色']
	,self::ATTACHED_HU_DADIAOCHE=>[self::ATTACHED_HU_DADIAOCHE, 2, '大吊车']
	,self::ATTACHED_HU_HAIDILAOYUE=>[self::ATTACHED_HU_HAIDILAOYUE, 2, '海底捞']
	);


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

	const ALL_CARD_136 = [1,1,1,1,    2,2,2,2,     3,3,3,3,     4,4,4,4,     5,5,5,5,     6,6,6,6,     7,7,7,7,     8,8,8,8,     9,9,9,9
	,17,17,17,17, 18,18,18,18, 19,19,19,19, 20,20,20,20, 21,21,21,21, 22,22,22,22, 23,23,23,23, 24,24,24,24, 25,25,25,25
	,33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41
	,49,49,49,49, 50,50,50,50, 51,51,51,51, 52,52,52,52, 53,53,53,53, 54,54,54,54, 55,55,55,55
	];
    
}


