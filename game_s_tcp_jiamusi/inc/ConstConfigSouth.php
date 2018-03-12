<?php
/**
 * @author xuqiang76@163.com
 * @final 20161025
 */

namespace gf\inc;

class ConstConfigSouth
{
	public static $hu_data = array();
	
	public static function get_hu_data()
	{
		self::$hu_data = require("./inc/mahjong_data.php");
	}

	//------------房间状态--------------	
	const ROOM_STATE_NULL = 0;
	const ROOM_STATE_OVER = 1;
	const ROOM_STATE_OPEN = 2;
	const ROOM_STATE_GAMEING = 3;	

	//－－－－－－－－－－－－－基础参数 －－－－－－－－－－－－－－－－－－－

	//const BASE_CARD_NUM = 136; // 牌的个数,万条筒共108张 + 字牌 28张
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
	const DAO_PAI_TYPE_MINGGANG = 3; //别人打给我地明杠
	const DAO_PAI_TYPE_ANGANG = 4; //暗杠
	const DAO_PAI_TYPE_WANGANG = 5; //弯杠，先碰再自己摸到第二种明杠

	//-----------换三张方式--------------
	const HUAN_3_CLOCKWISE = 1;	//顺时针
	const HUAN_3_ANTICLOCKWISE  = 2;	//逆时针
	const HUAN_3_CROSS   = 3;	//交叉
	
	//－－－－－－－－－－－－－阶段状态 －－－－－－－－－－－－－－－－－－－
	//const SYSTEMPHASE_WAITING_START = 1; // 用户进入而又没开始的状态
	const SYSTEMPHASE_HUAN_3 = 2; // 换三张阶段
	const SYSTEMPHASE_DING_QUE = 3; // 定缺阶段
	const SYSTEMPHASE_THINKING_OUT_CARD = 4; // 思考打牌阶段
	const SYSTEMPHASE_CHOOSING = 5; // 竞争抉择阶段,有碰,杠,胡时的抉择
	const SYSTEMPHASE_SET_OVER = 6; // 结束阶段
	const SYSTEMPHASE_INVALID = 255; // 非法阶段，还没有开始

	//－－－－－－－－－－－－－玩家状态 －－－－－－－－－－－－－－－－－－－
	const PLAYER_STATUS_HUAN3ING = 1;	//换三张中	
	const PLAYER_STATUS_DINGQUEING = 2;	//定缺中
	const PLAYER_STATUS_WAITING = 3;
	const PLAYER_STATUS_THINK_OUTCARD = 4;
	const PLAYER_STATUS_CHOOSING = 5;
	const PLAYER_STATUS_BLOOD_HU = 6;	//血战到底胡牌
	const PLAYER_STATUS_HUAZHU = 7;
	const PLAYER_STATUS_DAJIAO = 8;
	const PLAYER_STATUS_PLAYER_STATUS_INVALIDE = 255;

	//－－－－－－－－－－－－－结束原因 －－－－－－－－－－－－－－－－－－－
	const END_REASON_FLEE = 1;
	const END_REASON_HU = 2;
	const END_REASON_NOCARD = 3;

	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21; //平胡 0番
	const HU_TYPE_PENGPENGHU = 22; //碰碰胡 1番
	const HU_TYPE_QINGYISE = 23; //清一色 2番
	const HU_TYPE_YAOJIU = 24; //幺九 3番
	const HU_TYPE_QIDUI = 25; //七对 2番

	const HU_TYPE_QING_PENG = 26; //清碰碰胡 3番
	const HU_TYPE_JIANG_PENG = 27; //将碰3番
	const HU_TYPE_LONG_QIDUI = 28; //龙七对 3番
	const HU_TYPE_QING_QIDUI = 29; //清七对 4番
	const HU_TYPE_QING_YAOJIU = 30; //清幺九 5番

	const HU_TYPE_QINGLONG_QIDUI = 31; //青龙七对 5番
	const HU_TYPE_JIANG_QIDUI = 32; //将七对 4番
	const HU_TYPE_FENGDING_TYPE_INVALID  = 0; //错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_TIANHU = 61; //天胡 3番
	const ATTACHED_HU_DIHU = 62; //地胡 2番
	const ATTACHED_HU_RENHU = 63; //人和 2番
	const ATTACHED_HU_ZIMOFAN = 64; //自摸加番 1番
	const ATTACHED_HU_GANGKAI = 65; //杠开 1番

	const ATTACHED_HU_GANGPAO = 66; //杠炮 1番
	const ATTACHED_HU_QIANGGANG = 67; //抢杠 1番
	const ATTACHED_HU_GEN = 68; //根 1番
	const ATTACHED_HU_GANG = 69; //杠 1番
	const ATTACHED_HU_JINGOU = 70; //金钩 1番

	const ATTACHED_HU_HAIDIHU =71; //海底胡 1番
	const ATTACHED_HU_HAIDIPAO = 72; //海底炮 1番
	const ATTACHED_HU_MENG_QING = 73; //门清 1番
	const ATTACHED_HU_ZHONGZHANG = 74; //中张 1番


	//－－－－－－－－－－－－－玩家终局时输赢状态 －－－－－－－－－－－－－－－－－－－
	const WIN_STATUS_NOTHING = 0; //事不关己
	const WIN_STATUS_ZI_MO = 1; //自摸
	const WIN_STATUS_CHI_PAO = 2; //吃炮
	const WIN_STATUS_HU_DA_JIAO = 3;   //胡大叫
	const WIN_STATUS_HUAZHU = 4;   //查花猪

	//－－－－－－－－－－－－－玩家终局时输赢状态 －－－－－－－－－－－－－－－－－－－
	const LOSE_STATUS_FANG_PAO = 6; //放炮
	const LOSE_STATUS_BAOPAI_QIANGGANG = 7; //抢杠包牌

	public static $hu_type_arr = array(
	self::HU_TYPE_PINGHU=>[self::HU_TYPE_PINGHU, 0, '平胡']
	,self::HU_TYPE_PENGPENGHU=>[self::HU_TYPE_PENGPENGHU, 1, '碰碰胡']
	,self::HU_TYPE_QINGYISE=>[self::HU_TYPE_QINGYISE, 2, '清一色']

	,self::HU_TYPE_QIDUI=>[self::HU_TYPE_QIDUI, 2, '七对']
	,self::HU_TYPE_YAOJIU=>[self::HU_TYPE_YAOJIU, 3, '幺九']
	,self::HU_TYPE_QING_PENG=>[self::HU_TYPE_QING_PENG, 3, '清碰']
	,self::HU_TYPE_JIANG_PENG=>[self::HU_TYPE_JIANG_PENG, 3, '将碰']
	,self::HU_TYPE_LONG_QIDUI=>[self::HU_TYPE_LONG_QIDUI, 3, '龙七对']

	,self::HU_TYPE_QING_QIDUI=>[self::HU_TYPE_QING_QIDUI, 4, '清七对']
	,self::HU_TYPE_JIANG_QIDUI=>[self::HU_TYPE_JIANG_QIDUI, 4, '将七对']
	,self::HU_TYPE_QING_YAOJIU=>[self::HU_TYPE_QING_YAOJIU, 5, '清幺九']
	,self::HU_TYPE_QINGLONG_QIDUI=>[self::HU_TYPE_QINGLONG_QIDUI, 5, '清龙七对']

	);

	public static $attached_hu_arr = array(
	self::ATTACHED_HU_TIANHU=>[self::ATTACHED_HU_TIANHU, 3, '天胡']
	,self::ATTACHED_HU_DIHU=>[self::ATTACHED_HU_DIHU, 2, '地胡']
	,self::ATTACHED_HU_RENHU=>[self::ATTACHED_HU_RENHU, 2, '人胡']
	,self::ATTACHED_HU_ZIMOFAN=>[self::ATTACHED_HU_ZIMOFAN, 1, '自摸加番']
	,self::ATTACHED_HU_GANGKAI=>[self::ATTACHED_HU_GANGKAI, 1, '杠上花']

	,self::ATTACHED_HU_GANGPAO=>[self::ATTACHED_HU_GANGPAO, 1, '杠上炮']
	,self::ATTACHED_HU_QIANGGANG=>[self::ATTACHED_HU_QIANGGANG, 1, '抢杠']
	,self::ATTACHED_HU_GEN=>[self::ATTACHED_HU_GEN, 1, '根']
	,self::ATTACHED_HU_GANG=>[self::ATTACHED_HU_GANG, 1, '杠']
	,self::ATTACHED_HU_JINGOU=>[self::ATTACHED_HU_JINGOU, 1, '金钩']

	,self::ATTACHED_HU_HAIDIHU=>[self::ATTACHED_HU_HAIDIHU, 1, '海底胡']
	,self::ATTACHED_HU_HAIDIPAO=>[self::ATTACHED_HU_HAIDIPAO, 1, '海底炮']
	,self::ATTACHED_HU_MENG_QING=>[self::ATTACHED_HU_MENG_QING, 1, '门清']
	,self::ATTACHED_HU_ZHONGZHANG=>[self::ATTACHED_HU_ZHONGZHANG, 1, '中张']
	
	);

	const CARD_INDEX = [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10,11,12,13,14,15	//万
	,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31	//条
	,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47	//筒
	,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63	//东南西北中发白
	,64,65,66,67,68,69,70,71,72,73							//春夏秋冬梅兰竹菊，空白牌
	];

	const ALL_CARD = [1,1,1,1,    2,2,2,2,     3,3,3,3,     4,4,4,4,     5,5,5,5,     6,6,6,6,     7,7,7,7,     8,8,8,8,     9,9,9,9
	,17,17,17,17, 18,18,18,18, 19,19,19,19, 20,20,20,20, 21,21,21,21, 22,22,22,22, 23,23,23,23, 24,24,24,24, 25,25,25,25
	,33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41
	];
	
//3人抢杠胡 测试
//	const ALL_CARD = [1,2,3,6,    6,6,7,7,     7,8,8,8,     9,2,2,2,     1,1,1,3,     3,3,4,4,     4,5,5,7,     8,8,8,8,     9,9,9,9
//	,17,17,17,9, 18,18,18,18, 19,19,19,19, 20,20,20,20, 21,21,21,21, 22,22,22,22, 23,23,23,23, 24,24,24,24, 25,25,25,25
//	,33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41
//	];

//4人一炮多响测试
//	const ALL_CARD = [1,1,1,1,    2,2,2,2,     3,3,3,3,     7
//	,4,4,4,     5,5,5,5,     6,6,6,6,     7,7
//	,4,4,4,     5,5,5,5,     6,6,6,6,     7,7
//	,4,4,4,     5,5,5,5,     6,6,6,6,     17,7
//	,7, 18,18,18,18, 19,19,19,19, 20,20,20,20, 21,21,21,21, 22,22,22,22, 23,23,23,23, 24,24,24,24, 25,25,25,25
//	,33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41
//	];
	
	//过手胡加番
//	const ALL_CARD = [2,3,5,5,    5,6,6,6,     7,7,7,8,    8
//	,1,4,4,     5,5,5,5,     6,6,6,6,     7,7
//	,4,4,4,     5,5,5,5,     6,6,6,6,     7,7
//	,4,4,4,     5,5,5,5,     6,6,6,6,     17,7
//	,7, 18,18,18,18, 19,19,19,19, 20,20,20,20, 21,21,21,21, 22,22,22,22, 23,23,23,23, 24,24,24,24, 25,25,25,25
//	,33,33,33,33, 34,34,34,34, 35,35,35,35, 36,36,36,36, 37,37,37,37, 38,38,38,38, 39,39,39,39, 40,40,40,40, 41,41,41,41
//	];
}


