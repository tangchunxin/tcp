<?php  
/**
 * @author xuqiang76@163.com
 * @final 20161025
 */

exit();

//华北测试地址(内网/公网)，如果是服务端调用可以用内网地址
10.163.36.55:150
211.159.160.191:150
//德州正式服务器
211.159.158.74:150

///////////////////////////////////////////////////////////////////////////////////
//客户端发出的协议///////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////

//心跳 
	OK
	{"act":"c_tiao", "rid":666666}
//链接tcp服务器之后马上绑定通讯频道，使用房间号rid（确保同一个房间的用户在同一个通讯频道）
	OK 绑定
	{"act":"c_bind", "rid":666666, "rid_key":"asdfasdfasfd"}
//聊天表情等 
	OK
	{"act":"c_chat", "rid":666666, "uid":123321, "type":1, "content":"d12"}	//type 类型  客户端定义
//开始阶段
	OK 用户加入房间（房主首先进入，其他用户才能在进入；房主的 rule 字段不能空）
	{"act":"c_join_room", "rid":666666, "uid":123321, "is_room_owner":1, "game_type":1, "uname":"傻大猫", "head_pic":"http://img.com/head.jpg", "sex":1}
	OK 准备开始游戏，4个房用户都 ready 了，则房间成为3状态（state 0 未定义 1 空闲 2 开放 3 正在游戏（ready/下一局））
	{"act":"c_ready", "rid":666666, "uid":666666}
	OK 申请解散游戏 yes  1 解散 2 不解散
	{"act":"c_cancle_game", "rid":666666, "uid":666666, "yes":1}
	OK 中途掉线，返回游戏 取得数据
	{"act":"c_get_game", "rid":666666, "uid":666666}	
//下炮子阶段
	OK	客户端下炮子
	{"act":"c_pao_zi", "rid":666666, "uid":666666, "pao_zi_num":0}	//	

//思考打牌阶段
	OK 客户端自摸胡
	{"act":"c_zimo_hu", "rid":666666, "uid":666666}
	OK	暗杠
	{"act":"c_an_gang", "rid":666666, "uid":666666, "gang_card":19}
	OK 弯杠
	{"act":"c_wan_gang", "rid":666666, "uid":666666, "gang_card":6}
	OK 出牌
	{"act":"c_out_card", "rid":666666, "uid":666666, "is_14":0, "out_card":17}	// is_14（是否打出的是第14张牌） 和 out_card（打出非第14张牌的牌名）， 两者必有一个非0
	OK 取消弯杠暗杠自摸胡
	{"act":"c_cancle_gang", "rid":666666, "uid":666666}
//竞争抉择阶段,有碰,杠,胡时的抉择
	//吃
	{"act":"c_eat", "rid":666666, "uid":666666 ,"num":1}  // num 吃法  1,2,3  
	OK 碰
	{"act":"c_peng", "rid":666666, "uid":666666}
	OK 直杠
	{"act":"c_zhigang", "rid":666666, "uid":666666}
	OK	胡（包括抢弯杠胡）
	{"act":"c_hu", "rid":666666, "uid":666666}
	OK 放弃吃碰杠胡动作
	{"act":"c_cancle_choice", "rid":666666, "uid":666666, "type":1} //（按最高的类型传值）0放弃无操作  1放弃吃 2放弃碰 3放弃杠 4放弃胡 
//结束阶段
	OK 客户端准备好了
	{"act":"c_ready", "rid":666666, "uid":666666}
//其他
	判定过手胡协议
	{"act":"c_hu_give_up", "rid":666666, "uid":666666}

///////////////////////////////////////////////////////////////////////////////////
//服务端发出的协议///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////
OK 各协议执行状态信息
	{"act":"s_result", "info":"c_****" "code":0, "desc":324}	//c_hu_give_up 当 code 为 0 代表可以胡牌
OK 新用户加入房间
	{"act":"s_join_room", "data":{}}// m_room_players 用户信息（包含掉线信息）； m_ready 用户ready情况
OK 有用户准备游戏
	{"act":"s_ready", "data":{}}	// m_room_players 用户信息（包含掉线信息）； m_ready 用户ready情况
OK 有用户申请结束游戏结果
	{"act":"s_cancle_game", "data":{}}	//'is_cancle' 投票结果 0无结果 1结束 2不结束；m_cancle_first 发起人座位号； 'm_cancle' 全部玩家投票信息
OK 牌局状态改变
	{"act":"s_sys_phase_change", "data":{}}
OK 表情和聊天	
	{"act":"s_chat", "data":{"type":1, "content":"123", "chair":1, "uid":123321}}	//type 1 表情 2 语音 3 文字
OK	一局结束
	{"act":"s_game_over", "data":{}}
OK	杠碰胡 通知客户端
	{"act":"s_act", "data":{"cmd":"c_peng", "chair":1, "card":6}}  //cmd = 's_follow' 表示跟庄
OK  用户在线状态
	{"act":"s_flee", "data":{"flee_time":[]}}
	
///////////////////////////////////////////////////////////////////////////////////
//http服务器发来的协议///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////
OK 开房间，房间成为2状态（state 0 未定义 1 空闲 2 开放 3 正在游戏）
{"act":"c_open_room", "rid":666666, "uid":123321, "rule":{}, "game_type":1}
OK 是否能加入房间
{"act":"c_get_room", "rid":666666, "uid":123321}

///////////////////////////////////////////////////////////////////////////////////
//状态和值///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////

base_player_count	//牌局玩家数
m_dice	//两个骰子点数
m_hu_desc	// 详细的胡牌类型(七小对 天胡, 地胡, 碰碰胡.......)
m_room_id	房间号
m_room_owner	房主 uid

m_fan_hun_card  翻混牌
m_hun_card      混牌

m_room_players	玩家信息
{
    uid
    is_room_owner	是否房主
    ip
    uname
    head_pic
    flee_time	//掉线时间戳 0 没掉线 非0掉线了
}
m_rule	游戏规则
	game_type	//游戏类型 用户客户端分辨游戏类型	5保定		
	player_count	//玩家人数 2 3 4
	set_num		//房间局数 8局1钻 16局2钻			
	min_fan		//胡牌最小番 0
	top_fan		//不限封顶 255

	is_feng;		//带风牌 0 否 1 是
	is_chipai;    //吃牌  0 否 1 是
	is_258_jiang;  //跟庄 0 否 1 是
	is_zimo;  //跟庄 0 否 1 是


	is_qingyise_fan;	//清一色加番 0 否 1 是
	is_yitiaolong_fan;   //一条龙加番  0 否 1 是
	is_ganghua_fan;	//杠上花加番 0 否 1 是
	is_qidui_fan; //七对加番  0 否 1 是
	is_hunyise_fan; //混一色加番  0 否 1 是
	is_dadiaoche_fan; //大吊车加番  0 否 1 是
    is_haidilaoyue_fan;//海底捞月 0 否 1 是
			


m_sysPhase	当前阶段状态
    SYSTEMPHASE_THINKING_OUT_CARD = 4; // 思考打牌阶段
    SYSTEMPHASE_CHOOSING = 5; // 竞争抉择阶段,有碰,杠,胡时的抉择
    SYSTEMPHASE_SET_OVER = 6; // 结束阶段
    SYSTEMPHASE_INVALID = 255; // 非法阶段，还没有开始
	
m_nSetCount	当前局数
m_nChairBanker	庄家的位置（座位号）
m_chairCurrentPlayer	当前出牌者
m_nCountAllot	发到第几张牌
m_bChooseBuf	玩家的选择胡,吃,碰,杠等命令 0 无 1 有操作
m_nNumTableCards	玩家桌面牌数量
m_nTableCards	玩家的桌面牌数组

m_sStandCard	玩家倒牌
{
    num = 0; // 倒牌数
    type = array(); // 顺，刻，明杠，暗杠 //类型	
	    DAO_PAI_TYPE_SHUN = 1; //顺子
	    DAO_PAI_TYPE_KE = 2; //刻
	    DAO_PAI_TYPE_MINGGANG = 3; //别人打给我地明杠
	    DAO_PAI_TYPE_ANGANG = 4; //暗杠
	    DAO_PAI_TYPE_WANGANG = 5; //弯杠，先碰再自己摸到第二种明杠	
    who_give_me = array(); // 谁打给我上的
    card = array(); // 被上的牌
    first_card = array(); // 牌型的第一张牌
}

m_sOutedCard	刚打出的牌
{
    chair; //谁打出的
    card; //是什么牌
}
m_sPlayer_len	玩家手持牌长度
m_sPlayer_state	玩家状态
    PLAYER_STATUS_WAITING = 3;
    PLAYER_STATUS_THINK_OUTCARD = 4;
    PLAYER_STATUS_CHOOSING = 5;
    PLAYER_STATUS_BLOOD_HU = 6;	//血战到底胡牌
    PLAYER_STATUS_HUAZHU = 7;
    PLAYER_STATUS_DAJIAO = 8;
    PLAYER_STATUS_PLAYER_STATUS_INVALIDE = 255;
	
m_sPlayer_card_taken_now	是否有摸到的牌(0 , 1)
	
m_chairSendCmd	发命令的玩家
m_currentCmd	当前命令
m_bChairHu		玩家是否胡牌
m_bChairHu_order	玩家胡牌顺序（一胡二胡三胡）
m_HuCurt		胡牌详情
{
    state;
    card;
    card_state	//胡牌这张牌的状态（1 实 0 虚一炮多响排在后面的玩家）
    jiang_card;
    type;
    count;
    method = array();


	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	HU_TYPE_PINGHU = 21; //平胡
	HU_TYPE_QIDUI = 22; //七对
	HU_TYPE_PENGPENGHU = 23; //对对胡

	HU_TYPE_FENGDING_TYPE_INVALID  = 0; //错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	ATTACHED_HU_TIANHU = 61; //天胡 
	ATTACHED_HU_DIHU = 62; //地胡 
	ATTACHED_HU_ZIMOFAN = 63; //自摸   默认
	ATTACHED_HU_GANGKAI = 64; //杠开
	ATTACHED_HU_QIANGGANG = 65; //抢杠

	ATTACHED_HU_DADIAOCHE = 66; //大吊车
	ATTACHED_HU_JIANGYISE = 67; //将一色
	ATTACHED_HU_HUNYISE = 68; //混一色
	ATTACHED_HU_QINGYISE = 69; //清一色
	ATTACHED_HU_YITIAOLONG = 70; //一条龙
    ATTACHED_HU_HAIDILAOYUE = 71; //海底捞月


    gain_chair = array();
}
m_nNumFan	玩家番数
m_Score	//本局胡分数结构
	[
		{
			score		// 分
			win_count		// 胜
			lose_count	// 负
			draw_count	// 和
			flee_count	// 逃跑
			set_count		// 局数	
		}
	]
m_wTotalScore: 	//本房间总结
	[
      {
		n_score		//房总分
		n_zimo		//总自摸次数
		n_jiepao	//总接炮次数
		n_dianpao	//总点炮次数
		n_angang	//总暗杠次数
		n_zhigang_wangang	//总明杠次数
      }
    ]
m_own_paozi	炮子信息
{
    recv = false; // 是否收到客户端定缺消息
    num; // 数量 0 1 2 3 4 255
}    

m_bHaveGang	//本轮是否有杠，判断杠上开花
m_sQiangGang	//抢杠结构
m_sGangPao	//杠炮结构

m_only_out_card	//只能出牌 不能碰杠胡

//---------------------------------------------私有数据
m_sPlayer	手牌
{
    state; //玩家状态
    len; //手拿多少张牌,不包括 card_taken_now;
    card_taken_now; //表示拿到那张牌的id，如果吃碰杠了，则是第14张牌
    card=[]; //存放4种牌型的数组,cCardes[0][0]:  0:万字牌数,1:条子,2:筒子
    seen_out_card; //能否看到出的牌，在竞争选择时候用
}

m_nHuGiveUp	//过手胡牌名