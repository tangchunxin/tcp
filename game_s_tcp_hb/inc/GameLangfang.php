<?php
/**
 * @author xuqiang76@163.com
 * @final 20161025
 */

namespace gf\inc;

use gf\inc\ConstConfig;
use gf\conf\Config;
use gf\inc\Room;
use gf\inc\BaseFunction;
use gf\inc\Game_cmd;

class GameLangfang
{
	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21;                  // 平胡
	const HU_TYPE_PENGPENGHU = 22; //碰碰胡
	const HU_TYPE_SHISANYAO = 23;               // 十三幺...
	const HU_TYPE_QIDUI = 24;                   // 七对
	const HU_TYPE_HAOHUA_QIDUI = 25;            // 豪华七对....
	const HU_TYPE_CHAOJI_QIDUI = 26;            // 超级豪华七对....
	const HU_TYPE_ZHUIZUN_QIDUI = 27;           // 至尊豪华七对....
	const HU_TYPE_HUNGANG = 28;               // 混杠
	const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   // 错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－

	const ATTACHED_HU_ZHUANG = 70;              // 庄家     默认
	const ATTACHED_HU_MENQING = 71;             // 门清
	const ATTACHED_HU_DADIAOCHE = 72;           //大吊车
	const ATTACHED_HU_BIAN = 73;          //边卡吊
	const ATTACHED_HU_KA = 74;          //边卡吊
	const ATTACHED_HU_DIAO = 75;          //边卡吊
	const ATTACHED_HU_ZIMOFAN = 76;             // 自摸     默认
	const ATTACHED_HU_GANGKAI = 77;             // 杠开
	const ATTACHED_HU_QIANGGANG = 78;           // 抢杠

	const ATTACHED_HU_QINGYISE = 79;            // 清一色
	const ATTACHED_HU_YITIAOLONG = 80;          // 一条龙
	const ATTACHED_HU_HAIDI = 81;               // 海底捞月 默认
	const ATTACHED_HU_SUHU = 82;               // 素胡
	const ATTACHED_HU_HUNYOU = 83;               // 混悠
	const ATTACHED_HU_XIAOSA = 84;               // 潇洒


	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 3;                 // 直杠 3分
	const M_ANGANG_SCORE = 2;                  // 暗杠 2分
	const M_WANGANG_SCORE = 1;                 // 弯杠 1分

	public static $hu_type_arr = array(
	self::HU_TYPE_PINGHU=>[self::HU_TYPE_PINGHU, 1, '平胡']
	,self::HU_TYPE_PENGPENGHU=>[self::HU_TYPE_PENGPENGHU, 2, '飘胡']
	,self::HU_TYPE_SHISANYAO=>[self::HU_TYPE_SHISANYAO, 10, '十三幺']
	,self::HU_TYPE_QIDUI=>[self::HU_TYPE_QIDUI, 2, '七对']
	,self::HU_TYPE_HAOHUA_QIDUI=>[self::HU_TYPE_HAOHUA_QIDUI, 4, '豪华七对']
	,self::HU_TYPE_CHAOJI_QIDUI=>[self::HU_TYPE_CHAOJI_QIDUI, 8, '超级豪华七对']
	,self::HU_TYPE_ZHUIZUN_QIDUI=>[self::HU_TYPE_ZHUIZUN_QIDUI, 16, '至尊豪华七对']
	,self::HU_TYPE_HUNGANG=>[self::HU_TYPE_HUNGANG, 4, '混杠']

	);

	public static $attached_hu_arr = array(
	self::ATTACHED_HU_ZHUANG=>[self::ATTACHED_HU_ZHUANG, 2, '庄闲']
	,self::ATTACHED_HU_MENQING=>[self::ATTACHED_HU_MENQING, 2, '门清']
	,self::ATTACHED_HU_DADIAOCHE=>[self::ATTACHED_HU_DADIAOCHE, 2, '大吊车']
	,self::ATTACHED_HU_BIAN=>[self::ATTACHED_HU_BIAN, 2, '边']
	,self::ATTACHED_HU_KA=>[self::ATTACHED_HU_KA, 2, '卡']
	,self::ATTACHED_HU_DIAO=>[self::ATTACHED_HU_DIAO, 2, '吊']
	,self::ATTACHED_HU_ZIMOFAN=>[self::ATTACHED_HU_ZIMOFAN, 2, '自摸']

	,self::ATTACHED_HU_GANGKAI=>[self::ATTACHED_HU_GANGKAI, 2, '杠上花']
	,self::ATTACHED_HU_QIANGGANG=>[self::ATTACHED_HU_QIANGGANG, 2, '抢杠']
	,self::ATTACHED_HU_QINGYISE=>[self::ATTACHED_HU_QINGYISE, 2, '清一色']
	,self::ATTACHED_HU_YITIAOLONG=>[self::ATTACHED_HU_YITIAOLONG, 2, '一条龙']
	,self::ATTACHED_HU_HAIDI=>[self::ATTACHED_HU_HAIDI, 2, '海底捞月']

	,self::ATTACHED_HU_HUNYOU=>[self::ATTACHED_HU_HUNYOU, 2, '混儿悠']
	,self::ATTACHED_HU_SUHU=>[self::ATTACHED_HU_SUHU, 2, '素胡']      //2倍
	,self::ATTACHED_HU_XIAOSA=>[self::ATTACHED_HU_XIAOSA, 2, '潇洒']   //2倍

	);


	public $serv;	                                   // socket服务器对象

	public $m_ready = array(0,0,0,0);	               // 用户准备
	public $m_nXiaoSa = array(0,0,0,0); // 潇洒结构
	public $m_game_type;	                           // 游戏 1 血战到底 2 陕西麻将 3河北承德麻将
	public $m_room_state;	                           // 房间状态
	public $m_room_id;	                               // 房间号
	public $m_room_owner;	                           // 房主
	public $m_room_players = array();	               // 玩家信息
	public $m_rule;	                                   // 规则对象
	public $m_start_time;	                           // 开始时间
	public $m_end_time;	                               // 结束时间
	public $m_record_game;	               // 录制脚本

	public $m_dice = array(0,0);	                   // 两个骰子点数
	public $m_hu_desc = array();		               // 详细的胡牌类型(七小对 天胡, 地胡, 碰碰胡.......)
	public $m_nSetCount;	                           // 比赛局数
	public $m_wTotalScore;				               // 总结的分数

	public $m_nChairDianPao;				           // 点炮玩家椅子号
	public $m_nCountHu;		                           // 胡牌玩家个数
	public $m_nCountFlee;	                           // 逃跑玩家个数

	public $m_bChairHu = array();		               // 血战已胡玩家
	public $m_bChairHu_order = array();		           // 血战已胡玩家顺序
	public $m_only_out_card = array();		           // 玩家只能出牌不能碰杠胡

	public $m_bTianRenHu;							   // 以判断地天人胡
	public $m_nDiHu = array();					       // 判断地胡
	public $m_nEndReason;					           // 游戏结束原因

	public $m_sQiangGang;			                   // 抢杠结构
	public $m_sGangPao;				                   // 杠炮结构
	public $m_sFollowCard;				               // 跟庄结构
	public $m_bHaveGang;                               // 是否有杠开
	public $m_own_paozi;	                           // 用户炮子结构
	public $m_paozi_score = array();	               // 炮子的输赢分

	//记分，以后处理
	public $m_wGangScore = array();			           // 刮风下雨总分数
	//public $m_wGFXYScore = array();				   // 刮风下雨临时分数
	public $m_wHuScore = array();					   // 本剧胡整合分数
	public $m_wSetScore = array();				       // 该局的胡分数
	public $m_wSetLoseScore = array();			       // 该局的被胡分数
	public $m_Score = array();	                       // 用户分数结构
	//public $m_wChairBanker = array();	               // 庄家分数结构  2分
	public $m_wFollowScore = array();	               // 跟庄庄家分数结构

	//数据区
	public $m_cancle = array();	                        // 解散房间标志
	public $m_cancle_first;	                            // 解散房间发起人

	public $m_nTableCards = array();		            // 玩家的桌面牌
	public $m_nNumTableCards = array();	                // 玩家桌面牌数量
	public $m_sStandCard = array();			            // 玩家倒牌 Stand_card
	public $m_sPlayer = array();				        // 玩家手牌私有数据 Play_data
	public $m_nNumCheat = array();				        // 玩家i诈胡次数
	public $m_fan_hun_card;	                            // 翻混牌
	public $m_hun_card;	                                // 混牌

	public $m_bFlee = array();                          //逃跑用户

	//处理选择命令
	public $m_bChooseBuf = array();			            // 玩家的选择胡,吃,碰,杠命令 1 等待操作 0 无操作
	public $m_nNumCmdHu;				                // 胡命令的个数
	public $m_chairHu = array();				        // 发出胡命令的玩家
	public $m_chairSendCmd;				                // 当前发命令的玩家
	public $m_currentCmd;			                    // 当前的命令
	public $m_eat_num;			                        // 竞争选择吃法 存储

	// 接收客户端数据
	public $m_nHuGiveUp = array();			            // 该轮放弃胡的,m_nHuGiveUp = [][0]: 个数

	// 与客户端无关
	public $m_nCardBuf = array();			            // 牌的缓冲区
	public $m_HuCurt = array();	                        // 胡牌信息
	public $m_bMaxFan = array();	                    // 是否达到封顶番数

	public $m_nChairBanker;				                // 庄家的位置，
	public $m_nChairBankerNext = 255;				    // 下一局庄家的位置，
	public $m_nCountAllot;					            // 发到第几张牌
	public $m_nAllCardNum = ConstConfig::BASE_CARD_NUM;
	public $m_sOutedCard;			                    // 刚打出的牌
	public $m_sysPhase;				                    // 当前阶段状态
	public $m_chairCurrentPlayer;			            // 当前出牌者
	public $m_set_end_time;	                            // 本局结束时间

	public $m_nHuList = array();			            // 胡牌列表, m_nHuCList = [][0]: 可胡牌的个数
	public $m_nPlayerList = array();			            //玩家手牌数组
	public $m_bLastGameOver; //按圈 打牌  牌局是否最终结束
	public $m_is_ting_arr; //是否听牌，能点炮胡
    public $m_client_ip = array();                      // 用户ip


	///////////////////////静态函数////////////////////////

	//漏胡判断
	public static function is_hu_give_up($hu_card, $hu_give_up)
	{
		if($hu_give_up == 0 || $hu_card == 0)
		{
			return 0;
		}
		else
		{
			if($hu_give_up % 100 == $hu_card)
			{
				return 1;
			}
			else
			{
				return self::is_hu_give_up($hu_card, floor($hu_give_up/100));
			}
		}
	}

	///////////////////////方法/////////////////////////
	//构造方法
	public function __construct($serv)
	{
		$this->serv = $serv;
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_SET_OVER ;
		$this->m_room_state = ConstConfig::ROOM_STATE_NULL ;
		$this->m_game_type = 321;
	}

	public function clear()
	{
		//$this->m_rule->clear();
		$this->InitData();
	}

	//初始化数据
	public function InitData($is_open = false)
	{
		if(empty($this->m_rule))
		{
			echo 'no m_rule '.__LINE__.__CLASS__;
			return false;
		}
		if($is_open || $this->m_rule->set_num <= $this->m_nSetCount && $this->m_bLastGameOver)
		{
			$this->m_game_type = 321;	//游戏类型，见http端协议
			$this->m_room_state = ConstConfig::ROOM_STATE_OVER ;	//房间状态
			$this->m_room_id = 0;	//房间号
			$this->m_room_owner = 0;	//房主
			$this->m_room_players = array();	//玩家信息
			$this->m_start_time = 0;	//开始时间
			$this->m_nSetCount = 0;
			$this->_on_table_status_to_playing();

			$this->m_sPlayer = array();
			$this->m_ready = array(0,0,0,0);
			for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
			{
				$this->m_wTotalScore[$i] = new TotalScore();
			}
		}

		$this->m_record_game = array();

		if($this->m_nChairBankerNext != 255)
		{
			$this->m_nChairBanker = $this->m_nChairBankerNext;
		}
		$this->m_nChairBankerNext = 255;

		//骰子
		$this->m_dice[0] = mt_rand(1,6);
		$this->m_dice[1] = mt_rand(1,6);
		sort($this->m_dice);
		$this->m_nXiaoSa = array(0,0,0,0); //明楼结构

		$this->m_nChairDianPao = 255;
		$this->m_nCountHu = 0;
		$this->m_bChairHu_order = array();
		$this->m_nCountFlee = 0;
		$this->m_bTianRenHu = true; //天胡
		$this->m_nEndReason = 0;

		$this->m_sQiangGang = new Qiang_gang();
		$this->m_sGangPao = new Gang_pao();

		$this->m_sFollowCard = new Follow_card();

		$this->m_bHaveGang = false;
		$this->m_nNumCmdHu = 0;			// 胡命令的个数
		$this->m_chairSendCmd = 255;			// 当前发命令的玩家
		//$this->m_nEatBuf = new Eat_suit();
		$this->m_nCardBuf = array();

		$this->m_nCountAllot = 0;			//还没发牌
		$this->m_sOutedCard = new Outed_card();

		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_SET_OVER ;
		$this->m_chairCurrentPlayer = 255;
		$this->m_currentCmd = 0;			// 当前的命令
		$this->m_eat_num = 0;			// 当前的命令
		$this->m_end_time = '';

		$this->m_cancle_first = 255;
		$this->m_fan_hun_card = 0;	//翻混牌
		$this->m_hun_card = 0;	//混牌
		$this->m_bLastGameOver = 0; //最终结束状态



		for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
		{
			$this->m_bChairHu[$i] = false;
			$this->m_nDiHu[$i] = 0;
			$this->m_wGangScore[$i] = array(0,0,0,0);
			$this->m_wHuScore[$i] = 0;
			$this->m_wSetScore[$i] = 0;
			$this->m_wSetLoseScore[$i] = 0;
			//$this->m_wGFXYScore[$i] = 0;
			$this->m_wFollowScore[$i] = 0;	//跟庄庄家分数结构
			$this->m_Score[$i] = new Score();
			//$this->m_own_paozi[$i] = new Pao_zi();
			$this->m_paozi_score[$i] = 0;


			$this->m_cancle[$i] = 0;
			$this->m_nTableCards[$i] = array();
			$this->m_sStandCard[$i] = new Stand_card();
			$this->m_sPlayer[$i] = new Play_data();
			$this->m_nNumCheat[$i] = 0;
			$this->m_nNumTableCards[$i] = 0;

			$this->m_bFlee[$i] = 0;


			$this->m_bChooseBuf[$i] = 0;
			$this->m_chairHu[$i] = 0;
			$this->m_nHuGiveUp[$i] = 0;
			$this->m_only_out_card[$i] = false;

			$this->m_bMaxFan[$i] = false;
			$this->m_HuCurt[$i] = new Hu_curt();
			$this->m_hu_desc[$i] = '';

			$this->m_nHuList[$i] = 0;
			$this->m_nPlayerList[$i] = array();
			$this->m_is_ting_arr[$i] = 1;
		}
	}

	//玩家在线状态
	public function handle_flee_play($is_force = false)
	{
		$is_flee = false;
		foreach ($this->m_room_players as $key => $room_user)
		{
			if(!$room_user['fd'] || !($this->serv->connection_info($room_user['fd'])))
			{
				//连接不存在了
				if(empty($this->m_room_players[$key]['flee_time']))
				{
					$this->m_room_players[$key]['flee_time'] = time();
					$is_flee = true;
				}
			}
			else
			{
				$this->m_room_players[$key]['flee_time'] = 0;
			}
		}

		if($is_flee || $is_force)
		{
			$flee_time = array();
			for ($i=0; $i<$this->m_rule->player_count; $i++)
			{
				$flee_time[$i] = empty($this->m_room_players[$i]['flee_time']) ? 0 : 1;
			}

			$cmd = new Game_cmd($this->m_room_id, 's_flee', array('flee_time'=>$flee_time), Game_cmd::SCO_ALL_PLAYER );
			$cmd->send($this->serv);
			unset($cmd);
		}

		return $is_flee;
	}

	//心跳
	public function c_tiao($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if($this->m_room_state != ConstConfig::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfig::ROOM_STATE_OPEN )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间已经不存来了'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}
			//有掉线用户
			if($this->handle_flee_play())
			{
				//有人断线，再重复检测游戏结束投票
				$this->_cancle_game();
			}

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));
		return $return_send['code'];
	}

	//表情 文字
	public function c_chat($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['type'])
			|| !isset($params['content'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if($this->m_room_state != ConstConfig::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfig::ROOM_STATE_OPEN )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}
			if(!empty($params['uid']))
			{
				foreach ($this->m_room_players as $key => $room_user)
				{
					if($room_user['uid'] == $params['uid'])
					{
						//$cmd = new Game_cmd($this->m_room_id, 's_chat', array("type"=>$params['type'], "content"=>$params['content']), Game_cmd::SCO_ALL_PLAYER_EXCEPT , $params['uid']);
						$cmd = new Game_cmd($this->m_room_id, 's_chat', array("type"=>$params['type'], "content"=>$params['content'], "chair"=>$key, "uid"=>$params['uid']), Game_cmd::SCO_ALL_PLAYER );
						$cmd->send($this->serv);
						unset($cmd);
					}
				}
			}

		}while(false);

		return $return_send['code'];
	}

    //开新的房间或者加入房间取房间状态（给http server用）
	public function c_get_room($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "room_owner"=>$this->m_room_owner, "desc"=>__LINE__.__CLASS__);
		$itime = time();
		do {
            if(!empty($params['client_ip']))
            {
                $this->m_client_ip[$params['uid']] = $params['client_ip'];
            }

			if (isset($this->m_rule))
            {
                if (empty($this->m_rule->is_circle))
                {
                    $this->m_rule->is_circle = 0;
                }
                $return_send['rule'] = $this->m_rule;
            }

			if($this->m_room_state != ConstConfig::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfig::ROOM_STATE_OPEN )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}
			if($itime - $this->m_start_time > Room::$room_timeout)
			{
				//超时
				$this->m_room_state = ConstConfig::ROOM_STATE_NULL ;
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			foreach ($this->m_room_players as $key => $room_user)
			{
				if(!empty($params['uid']) && $room_user['uid'] == $params['uid'])
				{
					$return_send['code'] = 4; $return_send['text'] = '你有未结束的游戏'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
				}
				else if( $key == $this->m_rule->player_count - 1)
				{
					$return_send['code'] = 3; $return_send['text'] = '房间已满'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
				}
			}
			if(!empty($params['uid']) && count($this->m_room_players) == 0 && $this->m_room_owner == $params['uid'])
			{
				$return_send['code'] = 5; $return_send['text'] = '你有未结束的游戏'; $return_send['desc'] = __LINE__.__CLASS__; break ;
			}
			
		}while(false);

		$this->serv->send($fd,  Room::tcp_encode($return_send, false));
		return $return_send['code'];
	}

	//掉线玩家重新回到游戏，取数据
	public function c_get_game($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {

			if($this->m_room_state != ConstConfig::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfig::ROOM_STATE_OPEN )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间已经不存在了'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			if(!empty($params['uid']))
			{
				foreach ($this->m_room_players as $key => $room_user)
				{
					if($room_user['uid'] == $params['uid'])
					{
						//取消断线记录
						$this->m_room_players[$key]['flee_time'] = 0;
						$this->m_room_players[$key]['fd'] = $fd;

						$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($key, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$key]['uid']);
						$cmd->send($this->serv);
						unset($cmd);

						$is_act = true;
						break;
					}
				}
				$this->handle_flee_play(true);
			}

			if(!$is_act)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间已经不存在了'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));
		return $return_send['code'];
	}

	//开房（给http的server用）
	public function c_open_room($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		$itime = time();
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['game_type'])
			|| empty($params['rule'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_SET_OVER || $this->m_room_state == ConstConfig::ROOM_STATE_GAMEING )
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}
			elseif ($this->m_room_state == ConstConfig::ROOM_STATE_OPEN  && $this->m_room_owner != $params['uid'])
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$this->clear();
			$this->m_rule = new RuleLangfang();
			if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3, 4)))
			{
				$params['rule']['player_count'] = 4;
			}

			$this->m_rule->game_type = $params['rule']['game_type'];
			$this->m_rule->player_count = $params['rule']['player_count'];
			$this->m_rule->set_num = $params['rule']['set_num'];
			$this->m_rule->min_fan = $params['rule']['min_fan'];
			$this->m_rule->top_fan = $params['rule']['top_fan'];

			$this->m_rule->is_feng = $params['rule']['is_feng'];
			$this->m_rule->is_fanhun = $params['rule']['is_fanhun'];
			$this->m_rule->is_chipai = $params['rule']['is_chipai'];
			$this->m_rule->is_zhuang_fan = $params['rule']['is_zhuang_fan'];
			$this->m_rule->is_genzhuang = $params['rule']['is_genzhuang'];
			$this->m_rule->is_xiaosa = $params['rule']['is_xiaosa'];
			$this->m_rule->is_biankadiao = $params['rule']['is_biankadiao'];
			$this->m_rule->is_hungang = $params['rule']['is_hungang'];

			$this->m_rule->is_qingyise_fan = isset($params['rule']['is_qingyise_fan']) ? $params['rule']['is_qingyise_fan'] : 1;
			$this->m_rule->is_yitiaolong_fan = isset($params['rule']['is_yitiaolong_fan']) ? $params['rule']['is_yitiaolong_fan'] : 1;
			$this->m_rule->is_shisanyao_fan = isset($params['rule']['is_shisanyao_fan']) ? $params['rule']['is_shisanyao_fan'] : 1;
			$this->m_rule->is_qidui_fan = isset($params['rule']['is_qidui_fan']) ? $params['rule']['is_qidui_fan'] : 1;
			$this->m_rule->is_ganghua_fan = isset($params['rule']['is_ganghua_fan']) ? $params['rule']['is_ganghua_fan'] : 1;

			$this->m_rule->is_dadiaoche_fan = isset($params['rule']['is_dadiaoche_fan']) ? $params['rule']['is_dadiaoche_fan'] : 1;
			$this->m_rule->is_haidi_fan = isset($params['rule']['is_haidi_fan']) ? $params['rule']['is_haidi_fan'] : 1;
			$this->m_rule->is_suhu_fan = isset($params['rule']['is_suhu_fan']) ? $params['rule']['is_suhu_fan'] : 1;
			$this->m_rule->is_hunyou_fan = isset($params['rule']['is_hunyou_fan']) ? $params['rule']['is_hunyou_fan'] : 1;
			$this->m_rule->is_menqing_fan = isset($params['rule']['is_menqing_fan']) ? $params['rule']['is_menqing_fan'] : 1;
			$this->m_rule->is_yipao_duoxiang = isset($params['rule']['is_yipao_duoxiang']) ? $params['rule']['is_yipao_duoxiang'] : 1;

			$this->m_rule->is_wangang_1_lose = isset($params['rule']['is_wangang_1_lose']) ? $params['rule']['is_wangang_1_lose'] : 1;

			$this->InitData(true);

			$this->m_room_state = ConstConfig::ROOM_STATE_OPEN ;
			$this->m_room_id = $params['rid'];
			$this->m_room_owner = $params['uid'];
			$this->m_room_players = array();
			$this->m_start_time = $itime;
			$this->m_nSetCount = 0;

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode($return_send, false));
		return $return_send['code'];
	}

	//加入房间，房主必须第一个加入房间
	public function c_join_room($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__, "text"=>"");
		$itime = time();
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| !isset($params['is_room_owner'])
			|| empty($params['game_type'])
			|| empty($params['ip'])
			|| !isset($params['uname'])
			|| !isset($params['head_pic'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			//兼容
			if(empty($params['sex']))
			{
				$params['sex'] = 0;
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}
			else if(empty($this->m_room_players) && ($itime - $this->m_start_time) > Room::$room_timeout)
			{
				$this->m_room_state = ConstConfig::ROOM_STATE_OVER ;
				$this->clear();
				$return_send['code'] = 3; $return_send['text'] = '没有此房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if( ($params['is_room_owner'] && $params['uid'] != $this->m_room_owner)
			|| ($params['is_room_owner'] && !empty($this->m_room_players[0]) && $params['uid']!=$this->m_room_players[0]['uid'])
			)
			{
				$return_send['code'] = 4; $return_send['text'] = '房主错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$add_user = true;
			$add_key = -1;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					$add_user = false;
					$add_key = $key;
					break;
				}
				if( $key == $this->m_rule->player_count - 1 && $add_user)
				{
					$return_send['code'] = 5; $return_send['text'] = '房间已满'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
				}
				$add_key = $key+1;
			}

			if($add_key == -1)
			{
				$add_key = 0;
			}

			if($add_key == 0)
			{
				if(!$params['is_room_owner'] || $params['uid'] != $this->m_room_owner )
				{
					$return_send['code'] = 4; $return_send['text'] = '房主错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
				}
				$this->m_room_players[$add_key]['is_room_owner'] = $params['is_room_owner'];
			}
            if(!empty($this->m_client_ip[$params['uid']]))
            {
                $params['ip'] = $this->m_client_ip[$params['uid']];
            }
			$this->m_room_players[$add_key]['fd'] = $fd;
			$this->m_room_players[$add_key]['uid'] = $params['uid'];
			$this->m_room_players[$add_key]['is_room_owner'] = $params['is_room_owner'];
			$this->m_room_players[$add_key]['ip'] = $params['ip'];
			$this->m_room_players[$add_key]['uname'] = $params['uname'];
			$this->m_room_players[$add_key]['head_pic'] = $params['head_pic'];
			$this->m_room_players[$add_key]['sex'] = $params['sex'];
			$this->m_room_players[$add_key]['score'] = 0;


		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));
		if(0 == $return_send['code'])
		{
			$this->handle_flee_play(true);	//更新断线用户
			$cmd = new Game_cmd($this->m_room_id, 's_join_room', array('m_room_players'=>$this->m_room_players, 'm_ready'=>$this->m_ready), Game_cmd::SCO_ALL_PLAYER );
			$cmd->send($this->serv);
			unset($cmd);

			//$this->c_ready($fd, $params);
		}

		return $return_send['code'];
	}

    public function c_leave($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if((ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			//
			if($this->m_nSetCount != 0 || $this->m_sysPhase != ConstConfig::SYSTEMPHASE_SET_OVER)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}
			
			$is_key = 255;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					$is_key = $key;
				}
			}
			if(255 == $is_key || 0 == $is_key)
			{
				$return_send['code'] = 3; $return_send['text'] = '离开房间请求错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$this->handle_flee_play(true);	//更新断线用户
			
			//unset($this->m_room_players[$is_key]);
			array_splice($this->m_room_players,$is_key,1);
			array_splice($this->m_ready,$is_key,1);
			$this->m_ready[] = 0;

			//$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($is_key, true));
			$this->_send_cmd('s_join_room', array('m_room_players'=>$this->m_room_players, 'm_ready'=>$this->m_ready), Game_cmd::SCO_ALL_PLAYER);

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//准备开始
	public function c_ready($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		$itime = time();
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_ready = false;
			$u_key = 255;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					$u_key = $key;
					$this->m_ready[$key] = 1;
					$is_ready = true;
				}
			}
			if(!$is_ready || $u_key == 255)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
			{
				$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($u_key,true), Game_cmd::SCO_SINGLE_PLAYER , $params['uid']);
				$cmd->send($this->serv);
				unset($cmd);
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$ready_count = 0;
			for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
			{
				if(!empty($this->m_ready[$i]))
				{
					$ready_count++;
				}
			}

			if(0 == $return_send['code'])
			{
				$this->handle_flee_play(true);	//更新断线用户
				$cmd = new Game_cmd($this->m_room_id, 's_ready', array('base_player_count'=>$this->m_rule->player_count, 'm_room_players'=>$this->m_room_players, 'm_ready'=>$this->m_ready, 'm_nSetCount'=>$this->m_nSetCount, 'm_wTotalScore'=>$this->m_wTotalScore), Game_cmd::SCO_ALL_PLAYER );
				$cmd->send($this->serv);
				unset($cmd);
			}

			if($ready_count == $this->m_rule->player_count )
			{
				$this->on_start_game();
			}

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//解散房间
	public function c_cancle_game($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['yes'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if((ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_user_cancle = 0;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'] && $params['yes'])
				{
					$this->m_cancle[$key] = $params['yes'];
					$is_user_cancle = 1;
					if($this->m_cancle_first == 255)
					{
						$this->m_cancle_first = $key;
					}
				}
			}
			if(!$is_user_cancle)
			{
				$return_send['code'] = 3; $return_send['text'] = '解散房间请求错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$this->handle_flee_play(true);	//更新断线用户
			$this->_cancle_game();

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	///////////////////出牌阶段//////////////////////
	//胡
	public function c_zimo_hu($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					//连续发胡牌请求
					if(empty($this->m_room_players[$key]['hu_time']) || (time() - $this->m_room_players[$key]['hu_time']) > 2)
					{
						$this->m_room_players[$key]['hu_time'] = time();
					}
					else
					{
						{
							$return_send['code'] = 6; $return_send['text'] = '连续发送胡牌信息'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
					}

					if($this->m_sPlayer[$key]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
					{
						$return_send['code'] = 7; $return_send['text'] = '胡牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					if($key != $this->m_chairCurrentPlayer)
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if($this->m_only_out_card[$key] == true)
					{
						$return_send['code'] = 6; $return_send['text'] = '当前用户状态只能出牌'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if(!$this->HandleHuZiMo($key))	// 诈胡
					{
						$this->_clear_choose_buf($key);
						$return_send['code'] = 5; $return_send['text'] = '诈胡'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					$this->_clear_choose_buf($key);	  //自摸不可能抢杠胡
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

    //暗杠
	public function c_an_gang($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['gang_card'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if($key != $this->m_chairCurrentPlayer)
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					if(4 != $this->_list_find($key,$params['gang_card'])
					&& !(($params['gang_card'] == $this->m_sPlayer[$key]->card_taken_now) && 3 == $this->_list_find($key,$params['gang_card']))
					)
					{
						$return_send['code'] = 5; $return_send['text'] = '杠牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->_clear_choose_buf($key);
					$this->HandleChooseAnGang($key, $params['gang_card']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//弯杠
	public function c_wan_gang($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['gang_card'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if($key != $this->m_chairCurrentPlayer)
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$have_wan_gang = false;
					for ($i = 0; $i < $this->m_sStandCard[$key]->num; $i ++)
					{
						if ($this->m_sStandCard[$key]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
						&& $this->m_sStandCard[$key]->card[$i] == $params['gang_card'])
						{
							$have_wan_gang = true;
							break;
						}
					}
					if(!$have_wan_gang || ($params['gang_card'] != $this->m_sPlayer[$key]->card_taken_now && 0 == $this->_list_find($key,$params['gang_card'])))
					{
						$return_send['code'] = 5; $return_send['text'] = '杠牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->_clear_choose_buf($key);
					$this->HandleChooseWanGang($key, $params['gang_card']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));
		return $return_send['code'];
	}

	//出牌
	public function c_out_card($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| (empty($params['is_14']) && empty($params['out_card']))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if(!isset($params['is_ting']))
			{
				$params['is_ting'] = 1;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if($key != $this->m_chairCurrentPlayer)
					{
						$return_send['code'] = 4; 
						$return_send['text'] = '当前用户错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->_clear_choose_buf($key);
					if($this->m_nXiaoSa[$key] == 0)
					{
						if((empty($params['is_14']) && 0 == $this->_list_find($key,$params['out_card'])))
						{
							$return_send['code'] = 5; 
							$return_send['text'] = '出牌错误'; 
							$return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
						if(!empty($params['minglou']))  //客户端明楼和潇洒 ,都传的是 $params['minglou'] 
						{
							//这里要加听牌判断
							//这里要加听牌判断
							if($this->_is_menqing($key))
							{
								$this->m_nXiaoSa[$key] = 1;
								//状态变化发消息
								$this->_send_act('s_xiaosa', $key);
								$this->_set_record_game(ConstConfig::RECORD_XIAOSA, $key);
							}

						}
					}
					elseif($this->m_nXiaoSa[$key] == 1)
					{
						if(empty($params['is_14']))
						{
							$return_send['code'] = 5; 
							$return_send['text'] = '出牌错误'; 
							$return_send['desc'] = __LINE__.__CLASS__; break 2;
						}	
					}
					$this->HandleOutCard($key, $params['is_14'], $params['out_card'], $params['is_ting']);
					$is_act = true;	
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//自己出牌阶段取消操作（暗杠 弯杠 自摸胡）
	public function c_cancle_gang($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if($key != $this->m_chairCurrentPlayer)
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->m_sPlayer[$key]->state = ConstConfig::PLAYER_STATUS_THINK_OUTCARD ;
					$this->_clear_choose_buf($key);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	/////////////选择阶段/////////////////
	//碰牌
	public function c_peng($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if(!$this->_find_peng($key))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; $return_send['text'] = '当前用户无碰'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if(empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || 2 > $this->_list_find($key,$this->m_sOutedCard->card)|| (!empty($this->m_nXiaoSa[$key])))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '碰牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->_clear_choose_buf($key);
					$this->HandleChooseResult($key, $params['act']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//吃牌
	public function c_eat($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['num'])
			|| !in_array($params['num'],array(1,2,3))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if(!$this->_find_eat($key,$params['num']))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; $return_send['text'] = '当前用户无吃牌'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if(empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || $this->m_sOutedCard->chair != $this->_anti_clock($key,-1)|| (!empty($this->m_nXiaoSa[$key])))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '吃牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->_clear_choose_buf($key);
					$this->HandleChooseResult($key, $params['act'], $params['num']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//直杠
	public function c_zhigang($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					$params['type'] = 0;
					if(!$this->_find_zhi_gang($key))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; $return_send['text'] = '当前用户无直杠'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if(empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || 3 > $this->_list_find($key,$this->m_sOutedCard->card))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '杠牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->_clear_choose_buf($key);
					$this->HandleChooseResult($key, $params['act']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//点炮胡
	public function c_hu($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);

		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_fanhun = false;
			if($this->m_sOutedCard->card == $this->m_hun_card)
			{
				$is_fanhun = true;
			}
			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					//连续发胡牌请求
					if(empty($this->m_room_players[$key]['hu_time']) || (time() - $this->m_room_players[$key]['hu_time']) > 2)
					{
						$this->m_room_players[$key]['hu_time'] = time();
					}
					else
					{
						{
							$return_send['code'] = 6; $return_send['text'] = '连续发送胡牌信息'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
					}

					$params['type'] = 0;
					if( (empty($this->m_sOutedCard->card) && empty($this->m_sQiangGang->card))
					  || ($this->m_sOutedCard->card && $this->m_sOutedCard->chair == $key)
					  || ($this->m_sQiangGang->card && $this->m_sQiangGang->chair == $key)
					  || (!empty($this->m_nXiaoSa[$key]))
					  )
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '胡牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if($this->m_sPlayer[$key]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '胡牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					if($this->m_sQiangGang->card && $this->m_sQiangGang->mark)
					{
						$temp_card = $this->m_sQiangGang->card;
					}
					else if($this->m_sOutedCard->card)
					{
						$temp_card = $this->m_sOutedCard->card;
					}
					else
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '胡牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					$this->_list_insert($key, $temp_card);
					$this->m_HuCurt[$key]->card = $temp_card;
					if(!$this->judge_hu($key,$is_fanhun))
					{
						$this->m_HuCurt[$key]->clear();
						$this->_list_delete($key, $temp_card);
						$this->HandleZhaHu($key);
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; $return_send['text'] = '当前用户诈胡'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					$this->m_HuCurt[$key]->clear();
					$this->_list_delete($key, $temp_card);

					$this->_clear_choose_buf($key,false);
					$this->HandleChooseResult($key, $params['act']);
					$is_act = true;

					//判断截胡和一炮多响
					for ($i=0; $i<$this->m_rule->player_count; $i++)
					{
						$c_act = "c_hu";
						$last_chair = $i;
						if($last_chair == $this->m_chairCurrentPlayer || !($this->m_bChooseBuf[$last_chair]) || $i == $key )
						{
							continue;
						}

						$this->_list_insert($last_chair, $temp_card);
						$this->m_HuCurt[$last_chair]->card = $temp_card;
						if( self::is_hu_give_up($temp_card, $this->m_nHuGiveUp[$last_chair]) || !$this->judge_hu($last_chair,$is_fanhun) || !empty($this->m_nXiaoSa[$last_chair]) )
						{
							$this->m_sPlayer[$last_chair]->state = ConstConfig::PLAYER_STATUS_WAITING;
							$c_act = "c_cancle_choice";
						}
						$this->m_HuCurt[$last_chair]->clear();
						$this->_list_delete($last_chair, $temp_card);

						$this->_clear_choose_buf($last_chair, false);
						$this->HandleChooseResult($last_chair, $c_act);
					}

				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

    //选择阶段取消选择（吃 碰 直杠 点炮胡）
	public function c_cancle_choice($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| !isset($params['type'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$params['act'] = 'c_cancle_choice';

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if(!($this->m_bChooseBuf[$key]))
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户无需选择'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					if(4 == $params['type'])	//过手胡
					{
						$temp_card = $this->m_sOutedCard->card;
						if ($this->m_sQiangGang->mark )
						{
							$temp_card = $this->m_sQiangGang->card;
						}
						$this->_list_insert($key, $temp_card);
						$this->m_HuCurt[$key]->card = $temp_card;

						$is_fanhun = false;
						if($temp_card == $this->m_hun_card)
						{
							$is_fanhun = true;
						}

						if($this->judge_hu($key,$is_fanhun))
						{
							$this->m_nHuGiveUp[$key] = $this->m_nHuGiveUp[$key] * 100 + $temp_card;
						}
						$this->m_HuCurt[$key]->clear();
						$this->_list_delete($key, $temp_card);
					}
					$this->_clear_choose_buf($key, false); //有可能取消的是抢杠胡，这是需要后面判断来补张
					$this->m_sPlayer[$key]->state = ConstConfig::PLAYER_STATUS_WAITING;
					$this->HandleChooseResult($key, $params['act']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//--------------------------------------------------------------------------

	//判断胡
	public function judge_hu($chair, $is_fanhun = false)
	{
		//胡牌型
		$is_qingyise = false;
		$is_yitiaolong = false;
		$is_suhu = false;
		$is_bian = false;
		$is_ka = false;
		$is_diao = false;
		$is_dadiaoche = false;
		$is_hunyou = false;

		//混杠  必须是自摸   点炮不是
		$hu_type = $this->judge_hu_type_fanhun($chair, $is_qingyise, $is_yitiaolong, $is_suhu, $is_bian, $is_ka, $is_diao, $is_dadiaoche, $is_hunyou, $is_fanhun);

		//如果没有胡 判断是不是混杠
		if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID && !empty($this->m_rule->is_hungang) && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		{
			if($this->m_hun_card)
			{
				$hungagn_hu_card = $this->m_hun_card;//
				$hungagn_hu_type = $this->_get_card_type($hungagn_hu_card);
				if($this->m_sPlayer[$chair]->card[$hungagn_hu_type][$hungagn_hu_card%16] == 4)
				{
					$hu_type = self::HU_TYPE_HUNGANG;
					//记录在全局数据
					$this->m_HuCurt[$chair]->method[0] = $hu_type;
					$this->m_HuCurt[$chair]->count = 1;

					//自摸加番
					if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
					{
						$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZIMOFAN);
					}

					//潇洒加番
					if($this->m_nXiaoSa[$chair] && $this->m_rule->is_xiaosa)
					{
						$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_XIAOSA);
					}
				}
			}

			if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
			{
				var_dump(__LINE__);
				return false;
			}
		}
		else
		{   //正常胡牌计算
			if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
			{
				var_dump(__LINE__);
				return false;
			}

			//记录在全局数据
			$this->m_HuCurt[$chair]->method[0] = $hu_type;
			$this->m_HuCurt[$chair]->count = 1;

			//自摸加番
			if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZIMOFAN);
			}

			//潇洒加番
			if($this->m_nXiaoSa[$chair] && $this->m_rule->is_xiaosa)
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_XIAOSA);
			}

			//抢杠杠开杠炮
			if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)	// 处理抢杠
			{
				//$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
			}
			else if(!empty($this->m_rule->is_ganghua_fan) && $this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair)	//杠开
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGKAI);
			}
			else if ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO && $this->m_sGangPao->mark && $this->m_sGangPao->chair != $chair)
			{
				//$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGPAO);
			}

			//清一色
			if($is_qingyise && $this->m_rule->is_qingyise_fan)
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QINGYISE);
			}

			//一条龙
			if($is_yitiaolong && $this->m_rule->is_yitiaolong_fan)
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_YITIAOLONG);
			}

			//海底捞月
			if($this->m_nCountAllot >= $this->m_nAllCardNum - 5) //海底月
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_HAIDI);
			}

			//大吊车
			if($is_dadiaoche && $this->m_rule->is_dadiaoche_fan)
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_DADIAOCHE);
			}

			//边
			if($is_bian && $this->m_rule->is_biankadiao)
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_BIAN);
			}
			//卡
			if($is_ka && $this->m_rule->is_biankadiao)
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_KA);
			}
			//吊
			if($is_diao && $this->m_rule->is_biankadiao)
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_DIAO);
			}

			//门清
			if( !empty($this->m_rule->is_menqing_fan) && $this->_is_menqing($chair))
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_MENQING);
			}

			//素胡
			if($is_suhu && $this->m_rule->is_suhu_fan )
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_SUHU);
			}

			//混儿悠
			if($is_hunyou && $this->m_rule->is_hunyou_fan)
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_HUNYOU);
			}

		}


		return true;
	}

	//判断翻混
	public function judge_hu_type_fanhun($chair, &$is_qingyise, &$is_yitiaolong, &$is_suhu, &$is_bian, &$is_ka, &$is_diao, &$is_dadiaoche, &$is_hunyou, $is_fanhun = false )
	{
		$is_quemen = true;
		$fanhun_num = 0;
		if($this->m_hun_card)
		{
			$fanhun_num = $this->_list_find($chair, $this->m_hun_card);	//手牌翻混个数
			$fanhun_type = $this->_get_card_type($this->m_hun_card);        //翻混牌类型
			$fanhun_card = $this->m_hun_card%16;       //翻混牌
		}

		$fanhun_num = $is_fanhun ? $fanhun_num - 1 : $fanhun_num;	//打出的牌是否为翻混

		//判断缺门(考虑到翻混)
		$is_quemen = false;
		if(!empty($this->m_rule->is_quemen))
		{
			$is_quemen = $this->_is_quemen($chair, $fanhun_type, $fanhun_num);
		}

		if(0 == $this->m_rule->is_fanhun || 0 >= $fanhun_num)	//规则混子 或者 手牌无混中
		{
			return $this->judge_hu_type($chair, $is_qingyise, $is_yitiaolong, $is_suhu, $is_bian, $is_ka, $is_diao, $is_dadiaoche);
		}
		else
		{
			$return_type = self::HU_TYPE_FENGDING_TYPE_INVALID;
			$is_da8zhang = false;
			$is_hunyou = false;

			//十三幺牌型，不计缺门
			if($this->m_rule->is_shisanyao_fan && $this->m_rule->is_feng)
			{
				$is_shisanyao = true;
				$tmp_card_follow = 0;

				$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;	//去掉翻混

				if($this->m_sStandCard[$chair]->num > 0)
				{
					$is_shisanyao = false;
				}
				else
				{
					for($i = ConstConfig::PAI_TYPE_WAN ; $i <= ConstConfig::PAI_TYPE_FENG ; $i++)
					{
						for($j=1; $j<=9; $j++)
						{
							if( $i == ConstConfig::PAI_TYPE_FENG || ( $i != ConstConfig::PAI_TYPE_FENG && ($j == 1 || $j == 9) ) )
							{
								if($this->m_sPlayer[$chair]->card[$i][$j] == 2)
								{
									if( 1 == $tmp_card_follow)  //有且只有一个对
									{
										$is_shisanyao = false; break 2;
									}
									else
									{
										$tmp_card_follow = 1;
									}
								}
								else if($this->m_sPlayer[$chair]->card[$i][$j] > 2)
								{
									$is_shisanyao = false; break 2;
								}
							}
							else if($this->m_sPlayer[$chair]->card[$i][$j] > 0)	//非13幺的牌
							{
								$is_shisanyao = false; break 2;
							}
						}
					}
				}
				//还原手牌中的翻混
				$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += $fanhun_num;	

				if($is_shisanyao)
				{
					$is_da8zhang = true;
					return self::HU_TYPE_SHISANYAO;
				}
			}

			if(!empty($this->m_rule->is_quemen) && !$is_quemen)
			{
				return self::HU_TYPE_FENGDING_TYPE_INVALID ;
			}

			//7对牌型
			if(!empty($this->m_rule->is_qidui_fan))
			{
				$need_fanhun = 0;	//需要混子个数
				$qing_arr = array();
				$hu_qidui = false;
				$is_qingyise = false;
				$is_ziyise = false;
				$da8zhang_replace_fanhun = array(0,0,0,0);

				if($this->m_sStandCard[$chair]->num > 0)
				{
					$hu_qidui = false;
				}
				else
				{
					//去掉翻混
					$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;
					$gen_count_num = 0;//根的个数

					for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
					{
						if(0 == $this->m_sPlayer[$chair]->card[$i][0] || (0 == ($this->m_sPlayer[$chair]->card[$i][0] - $fanhun_num) && $i == $fanhun_type ))
						{
							continue;
						}
						else
						{
							$qing_arr[] = $i;
						}
						for ($j=1; $j<=9; $j++)
						{
							if($this->m_sPlayer[$chair]->card[$i][$j] == 1 || $this->m_sPlayer[$chair]->card[$i][$j] == 3)
							{
								$need_fanhun +=1 ;
								$da8zhang_replace_fanhun[$i]+= 1;
							}
							if($this->m_sPlayer[$chair]->card[$i][$j] == 4 || $this->m_sPlayer[$chair]->card[$i][$j] == 3)
							{
								$gen_count_num += 1;
							}
						}
					}

					$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += $fanhun_num;

					if($need_fanhun <= $fanhun_num)
					{
						$hu_qidui = true;
						$gen_count_num += intval(($fanhun_num - $need_fanhun)/2);
					}

					//打八张
					if(!$is_da8zhang && !empty($this->m_rule->is_da8zhang))
					{
						$is_da8zhang = $this->_judge_da8zhang($chair, $da8zhang_replace_fanhun, $is_fanhun, false, ($fanhun_num - $need_fanhun));
						if(!$is_da8zhang)
						{
							$hu_qidui = false;
						}
					}


					if($hu_qidui)
					{
						//七对也有混悠
						if($this->_is_qidui_hunyou($chair ,false))
						{
							$is_hunyou = true;
						}

						$this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);

						if($gen_count_num >= 3)
						{
							return self::HU_TYPE_ZHUIZUN_QIDUI;
						}
						elseif($gen_count_num == 2)
						{
							return self::HU_TYPE_CHAOJI_QIDUI;
						}
						elseif($gen_count_num == 1)
						{
							return self::HU_TYPE_HAOHUA_QIDUI;
						}
						else
						{
							return self::HU_TYPE_QIDUI;
						}
					}
				}
			}

			//32牌型
			$is_yitiaolong = false;
			$is_qingyise = false;
			$is_ziyise = false;
			$is_hunyou = false;  
			$is_bian = false;
			$is_ka = false;
			$is_diao = false;

			//倒牌
			$qing_arr_stand = array();
			$pengpeng_arr_stand = array(1, 1, 1, 1);
			for($k=0; $k<$this->m_sStandCard[$chair]->num; $k++)
			{
				$tmp_stand_type = $this->_get_card_type( $this->m_sStandCard[$chair]->first_card[$k] );
				$qing_arr_stand[] = $tmp_stand_type;
				if(ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$k])
				{
					$pengpeng_arr_stand[$tmp_stand_type] = 0;
				}
			}
			$qing_arr = $qing_arr_stand;
			$pengpeng_arr = $pengpeng_arr_stand;

			$is_hu_data = false;
			$is_da8zhang = false;
			$yitiaolong_tmp = false;
			$max_hu = array(0=>-1);

			$jiang_judge_arr = array(0=>2,1=>1,2=>0,3=>2,4=>1,5=>0,6=>2,7=>1,8=>0,9=>2,10=>1,11=>0,12=>2,13=>1,14=>0);
			$no_jiang_judge_arr = array(0=>0,1=>2,2=>1,3=>0,4=>2,5=>1,6=>0,7=>2,8=>1,9=>0,10=>2,11=>1,12=>0);

			//在外层  判断是否单  吊/  分两次判断 //混儿悠
			$is_boss_diao = false;
			if($this->_is_boss_diao($chair, $is_fanhun))
			{
				$is_boss_diao = true;
			}
			else
			{
				//判断是不是混儿悠
				if( $this->_is_hunyou($chair))
				{
					$is_hunyou = true;
				}
			}

			for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
			{
				if(0 == $this->m_sPlayer[$chair]->card[$i][0] || (0 == $this->m_sPlayer[$chair]->card[$i][0]-$fanhun_num && $i == $fanhun_type && $this->m_sPlayer[$chair]->len > $fanhun_num ))
				{
					continue;
				}

				$is_qingyise = false;
				$is_ziyise = false;
				$is_da8zhang = false;
				$yitiaolong_tmp = false;
				$is_bian = false;
				$is_ka = false;
				$is_diao = false;
				$qing_arr = $qing_arr_stand;
				$pengpeng_arr = $pengpeng_arr_stand;

				$is_hu_data = false;
				$jiang_type = $i;	//假设将牌是某一门
				$need_fanhun = 0;	//需要混个数
				$replace_fanhun = array(0,0,0,0);

				for($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
				{
					if(0 == $this->m_sPlayer[$chair]->card[$j][0] || ($j == $fanhun_type && 0 == $this->m_sPlayer[$chair]->card[$j][0]-$fanhun_num && $this->m_sPlayer[$chair]->len > $fanhun_num))
					{
						continue;
					}
					else
					{
						if($this->m_sPlayer[$chair]->len > $fanhun_num)
						{
							$qing_arr[] = $j;
						}
					}

					$pai_num = $this->m_sPlayer[$chair]->card[$j][0];	//一门牌个数
					$pai_num = ($j == $fanhun_type) ? $pai_num - $fanhun_num : $pai_num;	//混牌的牌型个数得减去混牌个数

					$tmp_judge_arr = ($jiang_type == $j) ? $jiang_judge_arr : $no_jiang_judge_arr;
					if(!isset($tmp_judge_arr[$pai_num]))
					{
						$need_fanhun = 255; break;
					}
					elseif($tmp_judge_arr[$pai_num] > 0)
					{
						$need_fanhun += $tmp_judge_arr[$pai_num];
						$replace_fanhun[$j] = $tmp_judge_arr[$pai_num];
					}
					else
					{
						if($j == $fanhun_type)
						{
							$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;	//去掉翻混
						}

						$tmp_hu_data = &ConstConfig::$hu_data;
						if($j == ConstConfig::PAI_TYPE_FENG)
						{
							$tmp_hu_data = &ConstConfig::$hu_data_feng;
						}

						$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$j], 1)));
						if(!isset($tmp_hu_data[$key]) || ($tmp_hu_data[$key] & 1 ) != 1)
						{
							$need_fanhun += 3;
							$replace_fanhun[$j] = 3;
						}
						if($j == $fanhun_type)
						{
							$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += $fanhun_num;
						}
					}
				}

				if($need_fanhun <= $fanhun_num)
				{
					$is_check_hu = false;
					for($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
					{
						$yitiaolong_tmp = false;

						$is_da8zhang = false;
						$is_hu_data = false;
						$pengpeng_arr = $pengpeng_arr_stand;
						$max_type_hu_arr = array(0=>-1, 1=>$pengpeng_arr_stand, 0, 0);

						if(empty($this->m_rule->is_feng) && $j == ConstConfig::PAI_TYPE_FENG)
						{
							continue;
						}

						if(0 == $this->m_sPlayer[$chair]->card[$j][0] || ($this->m_sPlayer[$chair]->card[$j][0] == $fanhun_num && $j == $fanhun_type && $this->m_sPlayer[$chair]->len > $fanhun_num))
						{
							continue;
						}
						if($fanhun_num == $need_fanhun && $is_check_hu)
						{
							continue;
						}
						$is_check_hu = true;

						$tmp_replace_fanhun = $replace_fanhun;
						$tmp_replace_fanhun[$j] += ($fanhun_num - $need_fanhun);

						//打八张
						if(!$is_da8zhang && !empty($this->m_rule->is_da8zhang))
						{
							$is_da8zhang = $this->_judge_da8zhang($chair,$tmp_replace_fanhun,$is_fanhun, false, 0);
							if(!$is_da8zhang)
							{
								continue;
							}
						}

						//校验胡
						foreach ($tmp_replace_fanhun as $type => $num)
						{
							$type_len = $this->m_sPlayer[$chair]->card[$type][0] + $num;
							if($type == $fanhun_type)
							{
								$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;	//去掉翻混
								$type_len = $type_len - $fanhun_num;
							}

							if(ConstConfig::PAI_TYPE_FENG == $type)
							{
								$tmp_hu_data = &ConstConfig::$hu_data_feng;
								$tmp_hu_data_insert = &ConstConfig::$hu_data_insert_feng;
							}
							else
							{
								$tmp_hu_data = &ConstConfig::$hu_data;
								$tmp_hu_data_insert = &ConstConfig::$hu_data_insert;
							}

							$is_hu_data = false;
							$tmp_type_hu_num = 0;
							$tmp_is_big = false;
							$tmp_type_yitiaolong = false;

							$insert_pengpeng_arr = $max_type_hu_arr[1];//上一门的数据
							$insert_yitiaolong = $max_type_hu_arr[2];//上一门的数据
							$insert_biankadiao = $max_type_hu_arr[3];//上一门的数据

							foreach ($tmp_hu_data_insert[$num] as $insert_arr)
							{
								//$tmp_max_hu_fan = 0;
								foreach ($insert_arr as $insert_item)
								{
									$this->m_sPlayer[$chair]->card[$type][$insert_item] += 1;
								}

								$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));

								if( isset($tmp_hu_data[$key]) && ($tmp_hu_data[$key] & 1) == 1)
								{
									$is_hu_data = true;
									$tmp_type_hu_num = 1;

									//碰碰胡和一条龙不会同时存在
									$tmp_pengpeng = $tmp_hu_data[$key] & 8;
									if($tmp_pengpeng && !array_keys($insert_pengpeng_arr, 0))
									{
										$tmp_type_hu_num += 1;
									}

									if($type_len >= 9)
									{
										//                      继承上次一条龙
										$tmp_type_yitiaolong = ($insert_yitiaolong || !empty($this->m_rule->is_yitiaolong_fan) && ($tmp_hu_data[$key] & 256) == 256);
										if($tmp_type_yitiaolong)
										{
											$tmp_type_hu_num += 2;
										}
									}

									//如果胡牌是$type类型  或者 胡牌是混子
									if(!$is_hunyou)
									{
										$tmp_judge_biankadiao = $this->HandleBianKaDiao($chair, $type , $insert_arr , $is_fanhun, $is_boss_diao);

										if($insert_biankadiao || $tmp_judge_biankadiao)
										{
											$tmp_type_hu_num += 2;

											if($insert_biankadiao)//继承上次的边 卡 吊
											{
												$tmp_judge_biankadiao = $insert_biankadiao;
											}
										}
									}
									else
									{
										$tmp_judge_biankadiao = self::ATTACHED_HU_HUNYOU;
									}

									if($tmp_type_hu_num >= $max_type_hu_arr[0])
									{
										$tmp_is_big = true;
										$max_type_hu_arr[0] = $tmp_type_hu_num;

										if($insert_pengpeng_arr[$type])
										{
											$max_type_hu_arr[1][$type] = $tmp_pengpeng;
										}

										$max_type_hu_arr[2] = $tmp_type_yitiaolong;
										$max_type_hu_arr[3] = $tmp_judge_biankadiao;
									}

									foreach ($insert_arr as $insert_item)
									{
										$this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
									}

									if($tmp_type_hu_num >= 5)
									{
										break;
									}
								}
								else
								{
									foreach ($insert_arr as $insert_item)
									{
										$this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
									}
								}
							}

							if($type == $fanhun_type)
							{
								$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += $fanhun_num;
							}

							if($is_hu_data)
							{
								if(!$tmp_is_big)
								{
									if($insert_pengpeng_arr[$type])
									{
										$max_type_hu_arr[1][$type] = $tmp_pengpeng;
									}
								}
							}
							else
							{
								$max_type_hu_arr[0] = -1;
								break;
							}
						}

						//整理每门的数据   再和max_hu做对比
						if($max_type_hu_arr[0] > 0)
						{
							$tmp_max_hu = self::$hu_type_arr[self::HU_TYPE_PINGHU][1];

							if(!empty($this->m_rule->is_pengpenghu_fan) && !array_keys($max_type_hu_arr[1], 0))
							{
								$tmp_max_hu += self::$hu_type_arr[self::HU_TYPE_PENGPENGHU][1];
							}
							if(!empty($this->m_rule->is_yitiaolong_fan) && $max_type_hu_arr[2])
							{
								$tmp_max_hu += self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
							}
							if(!empty($this->m_rule->is_biankadiao) && $max_type_hu_arr[3])
							{
								$tmp_max_hu += self::$attached_hu_arr[$max_type_hu_arr[3]][1];
							}

							if($tmp_max_hu > $max_hu[0])
							{
								$max_hu[0] = $tmp_max_hu;
								$max_hu[1] = $max_type_hu_arr[1];//碰碰胡
								$max_hu[2] = $max_type_hu_arr[2];//一条龙
								$max_hu[3] = $max_type_hu_arr[3];//变卡吊
							}
						}

						if($max_hu[0] > self::$hu_type_arr[self::HU_TYPE_PINGHU][1] + self::$attached_hu_arr[self::ATTACHED_HU_BIAN][1]+self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1])
						{
							//最大
							break 2;
						}
					}
				}
				else
				{
					continue;
				}
			}

			if($max_hu[0] > 0)
			{
				$this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);

				//边卡吊
				if($max_hu[3] != self::HU_TYPE_FENGDING_TYPE_INVALID)
				{
					if($max_hu[3] == self::ATTACHED_HU_BIAN)
					{
						$is_bian = true;
					}
					if($max_hu[3] == self::ATTACHED_HU_KA)
					{
						$is_ka = true;
					}
					if($max_hu[3] == self::ATTACHED_HU_DIAO)
					{
						$is_diao = true;
					}
				}

				//一条龙  碰碰胡 不能同时存在
				if(!empty($this->m_rule->is_yitiaolong_fan) && $max_hu[2])
				{
					$is_yitiaolong = $max_hu[2];
				}
				else
				{
					if($is_bian == false && $is_ka == false)
					{
						if(!empty($this->m_rule->is_pengpenghu_fan) && !empty($max_hu[1]) && !array_keys($max_hu[1], 0))
						{
							return self::HU_TYPE_PENGPENGHU;
						}
					}
				}

				return self::HU_TYPE_PINGHU;
			}

			return $return_type;
		}
	}

	//胡牌类型判断  没有混的情况
	public function judge_hu_type($chair, &$is_qingyise, &$is_yitiaolong, &$is_suhu, &$is_bian, &$is_ka, &$is_diao, &$is_dadiaoche)
	{
		$jiang_arr = array();
		$qidui_arr = array();
		$qing_arr = array();
		$shisanyao_arr = array();
		$gen_arr = array();
		$pengpeng_arr = array();
		$type_jiang_arr= array(0,0,0,0);

		$bType32 = false;
		$bQiDui = false;
		$bQing = false;
		$bPengPeng = false;
		$bShiSanYao = false;    //13幺

		$is_bian =  false;
		$is_ka =  false;
		$is_diao =  false;
		$is_qingyise =  false;
		$is_yitiaolong = false;   //一条龙
		$is_suhu = false;
		$is_dadiaoche = false;

		//手牌
		for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG; $i++)
		{
			if(0 == $this->m_sPlayer[$chair]->card[$i][0])
			{
				$shisanyao_arr[] = 0;
				continue;
			}

			$tmp_hu_data = &ConstConfig::$hu_data;
			if(ConstConfig::PAI_TYPE_FENG == $i)
			{
				$tmp_hu_data = &ConstConfig::$hu_data_feng;
			}
			$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));
			if(!isset($tmp_hu_data[$key]))
			{
				//return ConstConfig::HU_TYPE_FENGDING_TYPE_INVALID ;
				$jiang_arr[] = 32; $jiang_arr[] = 32;
				$qidui_arr[] = 0;
				$qing_arr[] = $i;
				$pengpeng_arr[] = 0;
				$shisanyao_arr[] = 0;
			}
			else
			{
				$hu_list_val = $tmp_hu_data[$key];
				//1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen
				if($this->m_rule->is_yitiaolong_fan && ($hu_list_val & 256) == 256 && !$is_yitiaolong )//一条龙
				{
					$is_yitiaolong = true;
				}

				$pengpeng_arr[] = $hu_list_val & 8;
				$qidui_arr[] = $hu_list_val & 64;
				$shisanyao_arr[] = $hu_list_val & 128;
				$gen_arr[] = intval($hu_list_val/4096);

				if(($hu_list_val & 1)== 1)
				{
					$jiang_arr[] = $hu_list_val & 32;
					$type_jiang_arr[$i] = $hu_list_val & 32; 
				}
				else
				{
					//非32牌型设置
					$jiang_arr[] = 32;
					$jiang_arr[] = 32;
				}
				$qing_arr[] = $i;	//ConstConfig::PAI_TYPE_WAN	...
			}
		}

		//倒牌
		for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
		{
			$stand_pai_type = $this->_get_card_type($this->m_sStandCard[$chair]->first_card[$i]);

			$qidui_arr[] = 0;
			$qing_arr[] = $stand_pai_type;
			$shisanyao_arr[] = 0;

			if(ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i])
			{
				$pengpeng_arr[] = 0;
			}
		}

		//记录根到全局数据
		$bType32 = (32 == array_sum($jiang_arr));
		$bShiSanYao = !array_keys($shisanyao_arr, 0);
		$bQiDui = !array_keys($qidui_arr, 0);
		$bPengPeng = !array_keys($pengpeng_arr, 0);

		/////////////////////////////附加 番型的处理/////////////////////////////////
		//清一色结果
		if(!empty($this->m_rule->is_qingyise_fan))
		{
			$bQing = ( 1 == count(array_unique($qing_arr)) && ConstConfig::PAI_TYPE_FENG != $qing_arr[0]);
			if($bQing )
			{
				$is_qingyise = true;
			}
		}
		//一条龙处理结果$is_yitiaolong = true;
		//大吊车
		if(!empty($this->m_rule->is_dadiaoche_fan) && 4 == $this->m_sStandCard[$chair]->num)
		{
			$is_dadiaoche = true;
		}

		//素胡
		if(!empty($this->m_rule->is_suhu_fan) && !empty($this->m_rule->is_fanhun) )
		{
			$is_suhu = true;
		}

		//边卡吊

		if(!empty($this->m_rule->is_biankadiao))
		{
			$type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
			if($bPengPeng)
			{
				//if($this->_is_diao($chair, $type, $is_fanhun))
				if($this->_is_boss_diao($chair, false, $type_jiang_arr))
				{
					$is_diao = true;
				}
			}
			else
			{
				if($this->_is_bian($chair, $type, false, false))
				{
					$is_bian = true;
				}
				else
				{
					if($this->_is_ka($chair, $type, false, false))
					{
						$is_ka = true;
					}
					else
					{
						//if($this->_is_diao($chair, $type, $is_fanhun))
						if($this->_is_boss_diao($chair, false, $type_jiang_arr))
						{
							$is_diao = true;
						}
					}
				}
			}
		}

		///////////////////////基本牌型的处理///////////////////////////////
		//13幺
		if($this->m_rule->is_shisanyao_fan && $this->m_rule->is_feng && $bShiSanYao)
		{
			return self::HU_TYPE_SHISANYAO;
		}

		//七对牌型和32牌型
		if(!$bType32 && !$bQiDui)	//不是32牌型也不是7对
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}

		if($bQiDui && $this->m_rule->is_qidui_fan )				//判断七对，可能同时是32牌型
		{

			if(array_sum($gen_arr) == 3)
			{
				return self::HU_TYPE_ZHUIZUN_QIDUI;
			}
			elseif(array_sum($gen_arr) == 2)
			{
				return self::HU_TYPE_CHAOJI_QIDUI;
			}
			elseif(array_sum($gen_arr) == 1)
			{
				return self::HU_TYPE_HAOHUA_QIDUI;
			}

			return self::HU_TYPE_QIDUI ;			//七对
		}

		if($bType32)
		{
			if($this->m_rule->is_pengpenghu_fan && $bPengPeng)
			{
				return self::HU_TYPE_PENGPENGHU;
			}
			return self::HU_TYPE_PINGHU;
		}

		return self::HU_TYPE_FENGDING_TYPE_INVALID;
	}

	public function judge_fan($chair)
	{
		$fan_sum = 0;
		$hu_type = $this->m_HuCurt[$chair]->method[0];
		if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID )
		{
			return 0;
		}

		$tmp_hu_desc = '(';

		if(isset(self::$hu_type_arr[$hu_type]))
		{
			$fan_sum = self::$hu_type_arr[$hu_type][1];
			$tmp_hu_desc .= self::$hu_type_arr[$hu_type][2].' ';
		}

		$tmp_fanbei = 1;
		for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
		{
			if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
			{
				//素胡 和潇洒,只显示文字,不加分,,,,只算倍
				if($this->m_HuCurt[$chair]->method[$i] != self::ATTACHED_HU_SUHU && $this->m_HuCurt[$chair]->method[$i] !=  self::ATTACHED_HU_XIAOSA)
				{
					$fan_sum += self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
				}
				else
				{
					$tmp_fanbei *=  self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
				}

				//自摸只加分,不需要显示描述
				if($this->m_HuCurt[$chair]->method[$i] != self::ATTACHED_HU_ZIMOFAN)
				{
					$tmp_hu_desc .= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
				}
			}
		}
		//素胡和潇洒翻倍

		$fan_sum = $tmp_fanbei * $fan_sum;

		$this->m_bMaxFan[$chair] = false;
		if ($fan_sum > $this->m_rule->top_fan)
		{
			$fan_sum = $this->m_rule->top_fan;
			$this->m_bMaxFan[$chair] = true;
		}

		//验证混杠  不算自摸的2分
		if($hu_type != self::HU_TYPE_HUNGANG && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO && ($fan_sum -  self::$attached_hu_arr[self::ATTACHED_HU_ZIMOFAN][1]) <= 4)
		{
			if(!empty($this->m_rule->is_hungang) )
			{
				if($this->m_hun_card)
				{
					$hungagn_hu_card = $this->m_hun_card;//
					$hungagn_hu_type = $this->_get_card_type($hungagn_hu_card);
					if($this->m_sPlayer[$chair]->card[$hungagn_hu_type][$hungagn_hu_card%16] == 4)
					{
						//重置混杠
						$fan_sum = 0;
						$hu_type = self::HU_TYPE_HUNGANG;
						$this->m_HuCurt[$chair]->method = array(0 => $hu_type);//充值胡牌
						$this->m_HuCurt[$chair]->count = 1;//充值胡牌个数
						$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZIMOFAN);

						//潇洒加番
						if($this->m_nXiaoSa[$chair] && $this->m_rule->is_xiaosa)
						{
							$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_XIAOSA);
						}

						$tmp_hu_desc = '(';
						if(isset(self::$hu_type_arr[$hu_type]))
						{
							$fan_sum = self::$hu_type_arr[$hu_type][1];
							$tmp_hu_desc .= self::$hu_type_arr[$hu_type][2].' ';
						}

						for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
						{
							if($this->m_HuCurt[$chair]->method[$i] !=  self::ATTACHED_HU_XIAOSA)
							{
								$fan_sum += self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
							}

							//自摸只加分,不需要显示描述
							if($this->m_HuCurt[$chair]->method[$i] != self::ATTACHED_HU_ZIMOFAN)
							{
								$tmp_hu_desc .= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
							}
						}

					}
				}
			}
		}

		if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		{
			$tmp_hu_desc = '自摸胡'.$tmp_hu_desc;
		}
		else
		{
			$tmp_hu_desc = '接炮胡'.$tmp_hu_desc;
		}
		$tmp_hu_desc .= ') ';

		$this->m_hu_desc[$chair] = $tmp_hu_desc;


		return $fan_sum;
	}

	//------------------------------------- 命令处理函数 -----------------------------------
	//处理碰
	public function HandleChoosePeng($chair)
	{
		$temp_card = $this->m_sOutedCard->card;
		$card_type = $this->_get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			echo("uuuuuuuuuuuuuuuuuuuuuu".__LINE__.__CLASS__);
			return false;
		}

		if(($this->_list_find($chair, $temp_card)) >= 2)
		{
			$this->_list_delete($chair, $temp_card);
			$this->_list_delete($chair, $temp_card);
		}
		else
		{
			echo "error asdff".__LINE__.__CLASS__;
			return false;
		}

		// 设置倒牌
		$stand_count = $this->m_sStandCard[$chair]->num;
		$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_KE;
		$this->m_sStandCard[$chair]->first_card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
		$this->m_sStandCard[$chair]->num ++;

		// 找出第14张牌
		$car_14 = $this->_find_14_card($chair);
		if(!$car_14)
		{
			echo "error dddf".__LINE__.__CLASS__;
			return false;
		}

		//置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
		--$this->m_nNumTableCards[$this->m_sOutedCard->chair];
		if($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
		{
			unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
		}

		$this->_set_record_game(ConstConfig::RECORD_PENG, $chair, $temp_card, $this->m_sOutedCard->chair);

		$this->m_sOutedCard->clear();

		$this->m_sPlayer[$chair]->card_taken_now = $car_14;

		for ($i = 0; $i < $this->m_rule->player_count ; $i ++)
		{
			if($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_WAITING;
			}
		}
		// 改变状态
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_THINK_OUTCARD;
		$this->m_chairCurrentPlayer = $chair;
		$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄

		$this->m_sGangPao->clear();
		$this->m_only_out_card[$chair] = true;

		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}

		return true;
	}

	//处理吃牌
	public function HandleChooseEat($chair,$eat_num)
	{
		$temp_card = $this->m_sOutedCard->card;
		$card_type = $this->_get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID || $card_type == ConstConfig::PAI_TYPE_FENG  || $card_type == ConstConfig::PAI_TYPE_DRAGON )
		{
			echo("uuuuuuuuuuuuuuuuuuuuuu".__LINE__.__CLASS__);
			return false;
		}

       	if($eat_num == 1)
		{
			$eat_card_first_tmp = $this->m_sOutedCard->card;
			$del_card_second_tmp = $this->m_sOutedCard->card+1;
			$del_card_third_tmp = $this->m_sOutedCard->card+2;
		}
		elseif($eat_num == 2)
		{
			$eat_card_first_tmp = $this->m_sOutedCard->card-1;
			$del_card_second_tmp = $this->m_sOutedCard->card-1;
			$del_card_third_tmp = $this->m_sOutedCard->card+1;
		}
		elseif($eat_num == 3)
		{
			$eat_card_first_tmp = $this->m_sOutedCard->card-2;
			$del_card_second_tmp = $this->m_sOutedCard->card-2;
			$del_card_third_tmp = $this->m_sOutedCard->card-1;
		}

		if($this->_get_card_type($eat_card_first_tmp) != $card_type)
		{
			echo("uuuuuuuuuuuuuuuuuuuuuu".__LINE__.__CLASS__);
			return false;
		}
		else
		{
			$this->_list_delete($chair, $del_card_second_tmp);
			$this->_list_delete($chair, $del_card_third_tmp);
		}

		// 设置倒牌
		$stand_count = $this->m_sStandCard[$chair]->num;
		$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_SHUN;
		$this->m_sStandCard[$chair]->first_card[$stand_count] = $eat_card_first_tmp;
		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
		$this->m_sStandCard[$chair]->num ++;

		$this->_set_record_game(ConstConfig::RECORD_CHI, $chair, $temp_card, $this->m_sOutedCard->chair, $eat_num);

		// 找出第14张牌
		$car_14 = $this->_find_14_card($chair);
		if(!$car_14)
		{
			echo "error dddf".__LINE__.__CLASS__;
			return false;
		}

		//置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
		--$this->m_nNumTableCards[$this->m_sOutedCard->chair];
		if($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
		{
			unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
		}

		$this->m_sOutedCard->clear();

		$this->m_sPlayer[$chair]->card_taken_now = $car_14;

		for ($i = 0; $i < $this->m_rule->player_count ; $i ++)
		{
			if($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_WAITING;
			}
		}
		// 改变状态
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_THINK_OUTCARD;
		$this->m_chairCurrentPlayer = $chair;
		$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄

		$this->m_eat_num = 0;
		$this->m_sGangPao->clear();
		$this->m_only_out_card[$chair] = true;

		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}
		return true;
	}

	//处理暗杠
	public function HandleChooseAnGang($chair, $gang_card)
	{
		$temp_card = $gang_card;
		$card_type = $this->_get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}

		$this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now);
		$this->m_sPlayer[$chair]->card_taken_now = 0;

		$this->_list_delete($chair, $temp_card);
		$this->_list_delete($chair, $temp_card);
		$this->_list_delete($chair, $temp_card);
		$this->_list_delete($chair, $temp_card);

		// 设置倒牌
		$stand_count = $this->m_sStandCard[$chair]->num;
		$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_ANGANG;
		$this->m_sStandCard[$chair]->first_card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $chair;
		$this->m_sStandCard[$chair]->num ++;

		$this->m_bHaveGang = true;  //for 杠上花

		$GangScore = 0;
		$nGangPao = 0;
		//$this->m_wGFXYScore = [0,0,0,0];
		for ($i=0; $i<$this->m_rule->player_count; ++$i)
		{
			if ($i == $chair)
			{
				continue;
			}

			if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
			{
				$nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;

				//$this->m_wGFXYScore[$i] = -$nGangScore;			//扣本次刮风下雨分
				$this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分

				//$this->m_wGFXYScore[$chair] += $nGangScore;				//赢本次刮风下雨分
				$this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分

				$this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

				$nGangPao += $nGangScore;
			}
		}

		$this->_set_record_game(ConstConfig::RECORD_ANGANG, $chair, $temp_card, $chair);

		$this->m_sGangPao->init_data(true, $gang_card, $chair, ConstConfig::DAO_PAI_TYPE_ANGANG, $nGangPao);

		$this->m_wTotalScore[$chair]->n_angang += 1;

		// 补发张牌给玩家
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
		$this->m_chairCurrentPlayer = $chair;
		if(!($this->DealCard($chair)))
		{
			return;
		}

		//暗杠需要记录入命令
		$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
		$this->m_currentCmd = 'c_an_gang';
		$this->m_sOutedCard->clear();
		if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
		{
			//CCLog("end reason no card");
			return;
		}

		$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}
	}

	//处理直杠
	public function HandleChooseZhiGang($chair)
	{
		$temp_card = $this->m_sOutedCard->card;
		$card_type = $this->_get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}

		$this->_list_delete($chair, $temp_card);
		$this->_list_delete($chair, $temp_card);
		$this->_list_delete($chair, $temp_card);

		// 设置倒牌
		$stand_count = $this->m_sStandCard[$chair]->num;
		$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_MINGGANG;
		$this->m_sStandCard[$chair]->first_card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
		$this->m_sStandCard[$chair]->num ++;
		$stand_count_after = $this->m_sStandCard[$chair]->num;

		$this->m_bHaveGang = true;  //for 杠上花

		$nGangScore = 0;
		$nGangPao = 0;
		//$this->m_wGFXYScore = [0,0,0,0];
		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
			if ($i == $chair)
			{
				continue;
			}

			if ($stand_count_after > 0 && $i == $this->m_sStandCard[$chair]->who_give_me[$stand_count_after-1])
			{
				$nGangScore =self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;

				//$this->m_wGFXYScore[$i] = -$nGangScore;
				$this->m_wGangScore[$i][$i] -= $nGangScore;

				//$this->m_wGFXYScore[$chair] += $nGangScore;
				$this->m_wGangScore[$chair][$chair] += $nGangScore;

				$this->m_wGangScore[$chair][$i] += $nGangScore;

				$nGangPao += $nGangScore;
			}
		}

		$this->_set_record_game(ConstConfig::RECORD_ZHIGANG, $chair, $temp_card, $this->m_sOutedCard->chair);

		$this->m_sGangPao->init_data(true, $temp_card, $chair,ConstConfig::DAO_PAI_TYPE_MINGGANG, $nGangPao);

		$this->m_wTotalScore[$chair]->n_zhigang_wangang += 1;

		// 补发张牌给玩家
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
		$this->m_chairCurrentPlayer = $chair;
		if(!$this->DealCard($chair))
		{
			return;
		}

		//置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
		--$this->m_nNumTableCards[$this->m_sOutedCard->chair];
		if($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
		{
			unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
		}

		$this->m_sOutedCard->clear();

		if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
		{
			//CCLOG("end reason no card");
			return;
		}
		$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);
		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}
	}

	//处理弯杠
	public function HandleChooseWanGang($chair, $gane_card)
	{
		$temp_card = $gane_card;
		$card_type = $this->_get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}

		$card_type_taken_now = $this->_get_card_type($this->m_sPlayer[$chair]->card_taken_now);
		if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type_taken_now)
		{
			echo("错误的牌类型".__LINE__.__CLASS__);
			return false;
		}

		// 改变手持牌，弯杠牌是第14张牌
		if ($this->m_sPlayer[$chair]->card_taken_now == $temp_card)
		{
			$this->m_sPlayer[$chair]->card_taken_now = 0;
		}
		else //弯杠牌在手持牌中
		{
			$this->_list_delete($chair, $temp_card);
			$this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now);
			$this->m_sPlayer[$chair]->card_taken_now = 0;
		}

		// 设置倒牌
		for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i ++)
		{
			if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
			&& $this->m_sStandCard[$chair]->card[$i] == $temp_card)
			{
				$this->m_sStandCard[$chair]->type[$i] = ConstConfig::DAO_PAI_TYPE_WANGANG;
				break;
			}
		}

		// 初始化杠结构
		$this->m_sQiangGang->init_data(true, $temp_card, $chair); //处理抢杠

		$this->m_sOutedCard->clear();

		//若有人可以胡，抢杠胡
		$this->m_nNumCmdHu = 0;	//重置抢胡牌命令个数
		$this->m_chairHu = array();
		$next_chair = $chair;

		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_CHOOSING;
		$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$next_chair = $this->_anti_clock($next_chair);

			if ($this->m_sPlayer[$next_chair]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU )
			{
				continue;
			}

			$this->m_bChooseBuf[$next_chair] = 1;
			$this->m_sPlayer[$next_chair]->state = ConstConfig::PLAYER_STATUS_CHOOSING;

			if($next_chair == $chair)
			{
				$this->m_bChooseBuf[$next_chair] = 0;
				$this->m_sPlayer[$next_chair]->state = ConstConfig::PLAYER_STATUS_WAITING;
			}
		}
		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}

		$this->m_chairSendCmd = 255;							// 当前发命令的玩家
		$this->m_currentCmd = 0;							// 当前的命令
	}

	//处理自摸
	public function HandleHuZiMo($chair)			//处理自摸
	{
		$temp_card = $this->m_sPlayer[$chair]->card_taken_now;
		$card_type = $this->_get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			echo("hu_zi_mo_error".__LINE__.__CLASS__);
			return false;
		}

		$this->_list_insert($chair, $temp_card);
		$this->m_HuCurt[$chair]->state = ConstConfig::WIN_STATUS_ZI_MO;
		$this->m_HuCurt[$chair]->card = $temp_card;

		$bHu = $this->judge_hu($chair);

		$this->_list_delete($chair, $temp_card);

		if(!$bHu) //诈胡
		{
			echo("有人诈胡".__LINE__.__CLASS__);
			$this->HandleZhaHu($chair);
			$this->m_HuCurt[$chair]->clear();
		}
		else
		{
			$tmp_lost_chair = 255;

			if($this->ScoreOneHuCal($chair, $tmp_lost_chair))
			{
				//总计自摸
				if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
				{
					$this->m_wTotalScore[$chair]->n_zimo += 1;
					$this->m_currentCmd = 'c_zimo_hu';
				}

				$this->m_chairSendCmd = $this->m_chairCurrentPlayer;

				//if ($this->m_game_type == 321)
				{
					$this->m_bChairHu[$chair] = true;
					$this->m_bChairHu_order[] = $chair;
					$this->m_nCountHu++;
					$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;

					//$this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now); //整理完毕

					//去除胡牌者 card_taken_now  这个牌就只有在 m_HuCurt 有
					$this->m_sPlayer[$chair]->card_taken_now = 0;

					if(255 == $this->m_nChairBankerNext)	//下一局庄家
					{
						$this->m_nChairBankerNext = $chair;
					}

					$this->m_nEndReason = ConstConfig::END_REASON_HU;

					$this->_set_record_game(ConstConfig::RECORD_ZIMO, $chair, $temp_card, $chair);

					$this->HandleSetOver();

					//发消息
					$this->_send_act($this->m_currentCmd, $chair);

					return true;
				}
			}
			else	//番数不够，判诈胡，一般进不来
			{
				echo("有人诈胡".__LINE__.__CLASS__);
				$this->HandleZhaHu($chair);
				$this->m_HuCurt[$chair]->clear();

				$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
				$this->m_chairCurrentPlayer = $chair;
				$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_THINK_OUTCARD ;

				//发消息
				$this->handle_flee_play(true);	//更新断线用户
				for($i=0; $i<$this->m_rule->player_count ; $i++)
				{
					$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
					$cmd->send($this->serv);
					unset($cmd);
				}
			}
		}
	}

	//处理出牌
	public function HandleOutCard($chair, $is_14 = false, $out_card = 0, $is_ting = 1)
	{
		//一旦有人出牌，表示上一轮竞争已经结束, 可以清CMD
		$this->m_chairSendCmd = 255;							// 当前发命令的玩家
		$this->m_currentCmd = 0;							// 当前的命令
		$this->m_eat_num = 0;

		// 更新桌面牌
		if($this->m_sOutedCard->card)
		{
			//echo("出牌没更新".__LINE__.__CLASS__);
			//$oldOutChair = $this->m_sOutedCard->chair;
			//$this->m_nTableCards[$oldOutChair][$this->m_nNumTableCards[$oldOutChair]++] = $this->m_sOutedCard->card;
			$this->m_sOutedCard->clear();
		}

		// 清除杠标记
		$this->m_sQiangGang->clear();
		$this->m_bHaveGang = false;

		//接收数据
		$this->m_sOutedCard->chair = $chair;
		$pos  = $is_14;
		$temp_out_card  = $out_card;

		//若打出的是第14张牌
		if($pos)
		{
			$this->m_sOutedCard->card = $this->m_sPlayer[$chair]->card_taken_now;
			$this->m_sPlayer[$chair]->card_taken_now = 0;
		}
		else if($temp_out_card)	//若打出的是第1-13张牌, 要整理牌列表
		{

			$this->m_sOutedCard->card = $temp_out_card;
			if(!$this->_list_delete($chair,$this->m_sOutedCard->card))
			{
				echo "出牌错误".__LINE__.__CLASS__;
				return false;
			}

			$card_type = $this->_get_card_type($this->m_sPlayer[$chair]->card_taken_now);
			if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
			{
				echo "出牌错误".__LINE__.__CLASS__;
				return false;
			}
			$this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now); //整理完毕
			$this->m_sPlayer[$chair]->card_taken_now = 0;
		}

		$this->m_is_ting_arr[$chair] = $is_ting;
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_WAITING ;

		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_CHOOSING ;
		$chair_next = $chair;

		$this->m_nNumCmdHu = 0;	//重置抢胡牌命令个数
		$this->m_chairHu = array();
		$bHaveCmd = 0;

		//置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
		$this->m_nTableCards[$chair][$this->m_nNumTableCards[$chair]] = $this->m_sOutedCard->card;
		$this->m_nNumTableCards[$chair]++;

		$this->m_bTianRenHu = false; //判断天人胡标志
		$this->m_nDiHu[$chair] = 1;

		$this->m_only_out_card[$chair] = false;

		$this->_set_record_game(ConstConfig::RECORD_DISCARD, $chair, $this->m_sOutedCard->card, $chair);

		$this->_send_act('c_out_card', $chair, $this->m_sOutedCard->card);

		$this->handle_flee_play(true);	//更新断线用户
		for ( $i=0; $i < $this->m_rule->player_count - 1; $i++)
		{
			$chair_next = $this->_anti_clock($chair_next);
			if ($this->m_sPlayer[$chair_next]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU )
			{
				continue;
			}
			if ($chair_next == $chair)
			{
				continue;
			}
			$this->m_bChooseBuf[$chair_next] = 1;
			$this->m_sPlayer[$chair_next]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
			$bHaveCmd = 1;

			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($chair_next), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair_next]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}

		$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($chair), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair]['uid']);
		$cmd->send($this->serv);
		unset($cmd);

		if(!$bHaveCmd)	//其他用户无需操作(一个用户的时候 单人麻将发牌)
		{
			$next_chair = $chair;
			do
			{
				$next_chair = $this->_anti_clock($next_chair);
			} while ($this->m_bChairHu[$next_chair]);

			//			if ($next_chair == $chair)
			//			{
			//				echo("find unHu player error, chair".__LINE__.__CLASS__);
			//				return false;
			//			}

			$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
			$this->m_chairCurrentPlayer = $next_chair;
			$this->m_sGangPao->clear();

			if(!$this->DealCard($next_chair))
			{
				echo(" return false in ".__LINE__.__CLASS__);
				return false;
			}
			if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
			{
				echo("end reason no card".__LINE__.__CLASS__);
				return false;
			}

			$this->handle_flee_play(true);	//更新断线用户
			for ( $i=0; $i<$this->m_rule->player_count; $i++)
			{
				$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
				$cmd->send($this->serv);
				unset($cmd);
			}
		}

		return true;
	}

	public function HandleChooseResult($chair, $nCmdID, $eat_num = null)
	{
		$this->handle_flee_play(true);

		//处理竞争
		$order_cmd = array('c_cancle_choice'=>0, 'c_eat'=>1, 'c_peng'=>2, 'c_zhigang'=>3, 'c_hu'=>4);
		if(empty($this->m_currentCmd) || ($order_cmd[$nCmdID] > $order_cmd[$this->m_currentCmd] && $order_cmd[$nCmdID] >= $order_cmd['c_cancle_choice']))	//吃, 碰, 杠竞争
		{
			$this->m_chairSendCmd = $chair;
			$this->m_currentCmd	= $nCmdID;
			$this->m_eat_num = $eat_num;
		}
		if($nCmdID == 'c_hu')
		{
			$this->m_chairHu[$this->m_nNumCmdHu ++] = $chair;
		}

		//等待大家都选了竞争 吃碰杠胡 再去执行
		$sum = 0;
		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			if($i == $this->m_chairCurrentPlayer || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU )
			{
				continue;
			}
			$sum += $this->m_bChooseBuf[$i];
		}
		if($sum > 0)
		{
			return false;
		}

		if($this->m_currentCmd != 'c_cancle_choice')
		{
			//漏胡重置
			$card_chair = 255;
			if ($this->m_sQiangGang->mark )
			{
				$card_chair = $this->m_sQiangGang->chair;
			}
			else if ($this->m_sOutedCard->card)
			{
				$card_chair = $this->m_sOutedCard->chair;
			}
			if($card_chair != 255)
			{
				$tmp_chair = $this->m_chairSendCmd;
				for($i = 0; $i< $this->m_rule->player_count; $i ++)
				{
					$this->m_nHuGiveUp[$tmp_chair] = 0;
					//本人与动牌的玩家之间的上家
					$tmp_chair = $this->_anti_clock($tmp_chair, -1);
					if($tmp_chair == $card_chair || $tmp_chair == $this->m_chairSendCmd)
					{
						break;
					}
				}
			}
		}

		$temp_card=0;
		$card_type = ConstConfig::PAI_TYPE_PAI_TYPE_INVALID;

		if ($this->m_sQiangGang->mark )	// 处理抢杠
		{
			$temp_card = $this->m_sQiangGang->card;
			$bHaveHu = false;
			$record_hu_chair = array();

			$this->_do_c_hu($temp_card, $this->m_sQiangGang->chair, $bHaveHu, $record_hu_chair);

			$this->m_sGangPao->clear();

			if($bHaveHu) //抢杠胡,处理原来的杠
			{
				if($record_hu_chair && is_array($record_hu_chair))
				{
					$this->_set_record_game(ConstConfig::RECORD_HU_QIANGGANG, $record_hu_chair, $this->m_sQiangGang->card, $this->m_sQiangGang->chair);
				}
				//$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
				$this->m_sOutedCard->chair = $this->m_sQiangGang->chair;
				$this->m_sOutedCard->card = $this->m_sQiangGang->card;
				$this->m_currentCmd = 'c_hu';

				// 设置倒牌, 抢杠后杠牌变成刻子
				for ($i = 0; $i < $this->m_sStandCard[$this->m_sOutedCard->chair]->num; $i ++)
				{
					if ($this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
					&& $this->m_sStandCard[$this->m_sOutedCard->chair]->card[$i] == $this->m_sOutedCard->card)
					{
						$this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] = ConstConfig::DAO_PAI_TYPE_KE; break;
					}
				}

				//if ($this->m_game_type == 321)
				{
					$this->m_nEndReason = ConstConfig::END_REASON_HU;
					$this->HandleSetOver();
					return;
				}
			}
			else // 给杠的玩家补张
			{

				$GangScore = 0;
				$nGangPao = 0;
				//$m_wGFXYScore = [0,0,0,0];
				for ( $i=0; $i<$this->m_rule->player_count; ++$i)
				{
					if ($i == $this->m_sQiangGang->chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
					{
						continue;
					}
					$nGangScore = self::M_WANGANG_SCORE *ConstConfig::SCORE_BASE;

					//$this->m_wGFXYScore[$i] = -$nGangScore;
					$this->m_wGangScore[$i][$i] -= $nGangScore;

					//$this->m_wGFXYScore[$this->m_sQiangGang->chair] += $nGangScore;
					$this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
					$this->m_wGangScore[$this->m_sQiangGang->chair][$i] += $nGangScore;

					$nGangPao += $nGangScore;
				}

				$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
				$this->m_chairCurrentPlayer = $this->m_sQiangGang->chair;

				$this->m_bHaveGang = true; //for 杠上花
				$this->m_sGangPao->init_data(true, $this->m_sQiangGang->card, $this->m_sQiangGang->chair, ConstConfig::DAO_PAI_TYPE_WANGANG, $nGangPao);

				$this->_set_record_game(ConstConfig::RECORD_ZHUANGANG, $this->m_sQiangGang->chair, $this->m_sQiangGang->card, $this->m_sQiangGang->chair);

				$this->m_wTotalScore[$this->m_sQiangGang->chair]->n_zhigang_wangang += 1;

				//摸杠需要记录入命令
				$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
				$this->m_currentCmd = 'c_wan_gang';

				if(!$this->DealCard($this->m_chairCurrentPlayer))
				{
					return;
				}

				if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
				{
					return;
				}

				//状态变化发消息
				$this->_send_act($this->m_currentCmd, $this->m_sQiangGang->chair, $this->m_sQiangGang->card);
				$this->handle_flee_play(true);	//更新断线用户
				for ($i=0; $i < $this->m_rule->player_count ; $i++)
				{
					$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
					$cmd->send($this->serv);
					unset($cmd);
				}

				$this->m_sQiangGang->clear();

				return;
			}
		}
		else	// 不是抢杠
		{
			$bHaveHu = false;
			$record_hu_chair = array();
			$temp_card = $this->m_sOutedCard->card;

			$this->_do_c_hu($temp_card, $this->m_sOutedCard->chair, $bHaveHu, $record_hu_chair);

			$this->m_sGangPao->clear();

			if($bHaveHu)
			{
				if($record_hu_chair && is_array($record_hu_chair))
				{
					$this->_set_record_game(ConstConfig::RECORD_HU, $record_hu_chair, $this->m_sOutedCard->card, $this->m_sOutedCard->chair);
				}
				//$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
				$this->m_currentCmd = 'c_hu';

				//置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
				--$this->m_nNumTableCards[$this->m_sOutedCard->chair];
				if($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
				{
					unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
				}

				//if ($this->m_game_type == 321)
				{
					$this->m_nEndReason = ConstConfig::END_REASON_HU;
					$this->HandleSetOver();
					return;
				}
			}

			//没有胡， 继续处理其他命令
			switch($this->m_currentCmd)
			{
				case 'c_peng':
					$this->HandleChoosePeng($this->m_chairSendCmd);
					break;
				case 'c_zhigang':
					$this->HandleChooseZhiGang($this->m_chairSendCmd);
					break;
				case 'c_eat':
					$this->HandleChooseEat($this->m_chairSendCmd,$this->m_eat_num);
					break;
				case 'c_cancle_choice':	// 发牌给下家
					//跟庄处理
					$this->_genzhuang_do();
				default:  //预防有人诈胡后,游戏得以继续
					$this->m_sGangPao->clear();

					$next_chair = $this->m_chairCurrentPlayer;
					$next_chair = $this->_anti_clock($next_chair);

					if ($next_chair == $this->m_chairCurrentPlayer)
					{
						echo("find unHu player error, chair".__LINE__.__CLASS__);
						return;
					}

					$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
					$this->m_chairCurrentPlayer = $next_chair;

					if(!$this->DealCard($next_chair))
					{
						return;
					}
					if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
					{
						echo("end reason no card");
						return;
					}

					//状态变化发消息
					$this->_send_act($this->m_currentCmd, $chair);
					$this->handle_flee_play(true);	//更新断线用户
					for ($i=0; $i < $this->m_rule->player_count ; $i++)
					{
						$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
						$cmd->send($this->serv);
						unset($cmd);
					}
					break;
			}
		}

		$this->m_nNumCmdHu = 0;
		$this->m_chairHu = array();
	}

	private function _do_c_hu($temp_card, $dian_pao_chair, &$bHaveHu, &$record_hu_chair)
	{
		$card_type = $this->_get_card_type($temp_card);
		if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
		{
			echo("错误的牌类型，发生在-> 抢杠".__LINE__.__CLASS__);
			return false;
		}

		//截胡和一炮多响
		if ($this->m_nNumCmdHu)
		{
			$tmp_hu_arr = array();
			if(empty($this->m_rule->is_yipao_duoxiang))
			{
				//计算距离最近的玩家
				$distance = $this->m_rule->player_count;
				$hu_chair = ConstConfig::PLAYER_STATUS_PLAYER_STATUS_INVALIDE;
				for ($i=0; $i<$this->m_nNumCmdHu; $i++)
				{
					$tmp_distance = $this->_chair_to($this->m_chairCurrentPlayer, $this->m_chairHu[$i]);
					if($tmp_distance < $distance)
					{
						$distance = $tmp_distance;
						$hu_chair = $this->m_chairHu[$i];
					}
					else
					{
						unset($this->m_chairHu[$i]);
					}
				}
				if($hu_chair != ConstConfig::PLAYER_STATUS_PLAYER_STATUS_INVALIDE)
				{
					$tmp_hu_arr[] = $hu_chair;
				}
			}
			else
			{
				$tmp_hu_arr = $this->m_chairHu;
			}

			foreach ($tmp_hu_arr as $hu_chair)
			{
				$is_fanhun = false;
				if($temp_card == $this->m_hun_card)
				{
					$is_fanhun = true;
				}
				$this->_list_insert($hu_chair, $temp_card);
				$this->m_HuCurt[$hu_chair]->state = ConstConfig::WIN_STATUS_CHI_PAO;   //抢杠算作吃炮
				$this->m_nChairDianPao = $dian_pao_chair;
				$this->m_HuCurt[$hu_chair]->card = $temp_card;
				$bHu = $this->judge_hu($hu_chair,$is_fanhun);
				$this->_list_delete($hu_chair, $temp_card);
				if(!$bHu)
				{
					echo("有人诈胡 at".__LINE__.__CLASS__);
					$this->HandleZhaHu($hu_chair);
					$this->m_HuCurt[$hu_chair]->clear();
				}
				else
				{
					if($this->ScoreOneHuCal($hu_chair, $dian_pao_chair))
					{
						$bHaveHu = true;
						$record_hu_chair[] = $hu_chair;
						if($this->m_HuCurt[$hu_chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
						{
							$this->m_wTotalScore[$hu_chair]->n_jiepao += 1;
							$this->m_wTotalScore[$this->m_nChairDianPao]->n_dianpao += 1;
						}
						//if ($this->m_game_type == 321)
						{
							$this->m_bChairHu[$hu_chair] = true;
							$this->m_bChairHu_order[] = $hu_chair;
							$this->m_nCountHu++;
							$this->m_sPlayer[$hu_chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;
						}
						$this->_send_act($this->m_currentCmd, $hu_chair);
						if(255 == $this->m_nChairBankerNext || $hu_chair == $this->m_nChairBanker)	//下一局庄家
						{
							$this->m_nChairBankerNext = $hu_chair;
						}
					}
					else
					{
						$this->HandleZhaHu($hu_chair);
						$this->m_HuCurt[$hu_chair]->clear();
					}
				}
			}
		}
	}

	//--------------------------------------------------------------------

	public function HandleZhaHu($chair)
	{
		//以后另做处理，客户端诈胡等于作弊
		$this->m_nNumCheat[$chair]++;
	}

	//取得所有玩家数据
	public function OnGetChairScene($chair, $is_more=false)
	{
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_INVALID)
		{
			echo("sysPhase invalid,".__LINE__.__CLASS__."\n");
			return false;
		}

		$data = array();
		if($is_more)
		{
			$data['base_player_count'] = $this->m_rule->player_count;
			$data['m_room_players'] = $this->m_room_players;
			$data['m_rule'] = clone $this->m_rule;
			$data['m_dice'] = $this->m_dice;
			$data['m_Score'] = $this->m_Score;		//分数
			//$data['m_own_paozi'] = $this->m_own_paozi;

			$data['m_wTotalScore'] = $this->m_wTotalScore;
			$data['m_ready'] = $this->m_ready;
			$data['is_cancle'] = $this->m_cancle;
			$data['m_cancle'] = $this->m_cancle;
			$data['m_cancle_first'] = $this->m_cancle_first;

			$data['m_fan_hun_card'] = $this->m_fan_hun_card;
			$data['m_hun_card'] = $this->m_hun_card;
		}

		$data['m_nXiaoSa'] = $this->m_nXiaoSa;          // 潇洒
		$data['m_nChairBanker'] = $this->m_nChairBanker;  //庄家
		$data['m_nSetCount'] = $this->m_nSetCount;
		$data['m_sysPhase'] = $this->m_sysPhase;	// 当前的阶段
		$data['m_nCountAllot'] = $this->m_nCountAllot;	// 发到第几张
		$data['m_nAllCardNum'] = $this->m_nAllCardNum;	//牌总数
		$data['m_bHaveGang'] = $this->m_bHaveGang;
		$data['m_sQiangGang'] = $this->m_sQiangGang;
		$data['m_sGangPao'] = $this->m_sGangPao;
		$data['m_bTianRenHu'] = $this->m_bTianRenHu;  //天胡
		$data['m_nDiHu'] = $this->m_nDiHu;            //地胡
		$data['m_bChairHu'] = $this->m_bChairHu;
		$data['m_bChairHu_order'] = $this->m_bChairHu_order;
		$data['m_HuCurt'] = $this->m_HuCurt;		//胡牌详情
		$data['m_bLastGameOver'] = $this->m_bLastGameOver;		//胡牌最终结束

		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$data['m_sPlayer_len'][$i] = $this->m_sPlayer[$i]->len;
			$data['m_sPlayer_state'][$i] = $this->m_sPlayer[$i]->state;
			$data['m_sPlayer_card_taken_now'][$i] = intval(0 != $this->m_sPlayer[$i]->card_taken_now);
			if($is_more && !empty($data['m_room_players'][$i]))
			{
				$data['m_room_players'][$i]['fd'] = 0;
			}
		}

		//下炮子阶段
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_XIA_PAO)
		{
			$data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;								// 当前出牌者
			return $data;
		}

		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
		{
			$data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;								// 当前出牌者
			$data['m_nNumTableCards'] = $this->m_nNumTableCards;		// 玩家桌面牌数量
			$data['m_nTableCards'] = $this->m_nTableCards;	// 玩家桌面牌
			$data['m_sStandCard'] = $this->m_sStandCard;		// 玩家倒牌
			$data['m_sOutedCard'] = $this->m_sOutedCard;		//刚出的牌

			for ($i=0; $i<$this->m_rule->player_count; $i++)                                         // 玩家手持牌长度
			{
				if($i == $chair)
				{
					$data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
					$data['m_bChooseBuf'] = $this->m_bChooseBuf[$i];			 //命令缓冲
					$data['m_nHuGiveUp'] = $this->m_nHuGiveUp[$i];
					$data['m_only_out_card'] = $this->m_only_out_card[$i];
				}
				else
				{
					if(!empty($this->m_nXiaoSa[$i]))
					{
						$data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
					}
					else
					{
						$data['m_sPlayer'][$i] = (object)null;
					}
				}
			}

			//$data['m_chairSendCmd'] = $this->m_chairSendCmd;                  //发命令的玩家
			//$data['m_currentCmd'] = $this->m_currentCmd;

			return $data;
		}

		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_CHOOSING )
		{

			$data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;			                    // 当前出牌者
			$data['m_nNumTableCards'] = $this->m_nNumTableCards;		// 玩家桌面牌数量
			$data['m_nTableCards'] = $this->m_nTableCards;	// 玩家桌面牌
			$data['m_sStandCard'] = $this->m_sStandCard;		// 玩家倒牌
			$data['m_sOutedCard'] = $this->m_sOutedCard;		//刚出的牌

			for ($i=0; $i<$this->m_rule->player_count; $i++)                                         // 玩家手持牌长度
			{
				if($i == $chair)
				{
					$data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
					$data['m_bChooseBuf'] = $this->m_bChooseBuf[$i];			 //命令缓冲
					$data['m_nHuGiveUp'] = $this->m_nHuGiveUp[$i];
					$data['m_only_out_card'] = $this->m_only_out_card[$i];
				}
				else
				{
					if(!empty($this->m_nXiaoSa[$i]))
					{
						$data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
					}
					else
					{
						$data['m_sPlayer'][$i] = (object)null;
					}
				}
			}

			return $data;
		}
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_SET_OVER )
		{
			$data['m_nEndReason'] = $this->m_nEndReason;										//结束原因
			//$data['m_nCountFlee'] = $this->m_nCountFlee;

			$data['m_nNumTableCards'] = $this->m_nNumTableCards;		// 玩家桌面牌数量
			$data['m_nTableCards'] = $this->m_nTableCards;	// 玩家桌面牌
			$data['m_sStandCard'] = $this->m_sStandCard;		// 玩家倒牌
			$data['m_sOutedCard'] = $this->m_sOutedCard;		//刚出的牌

			$data['m_sPlayer'] = $this->m_sPlayer;			// 玩家数据
			if(isset($data['m_sPlayer']['']))
			{
				unset($data['m_sPlayer']['']);
			}
			foreach ($data['m_sPlayer'] as $tmp_key => $tmp_val)
			{
				if($tmp_key >= $this->m_rule->player_count)
				{
					$data['m_sPlayer'][$tmp_key] = (object)null;
				}
			}

			$data['m_hu_desc'] = $this->m_hu_desc;
			$data['m_end_time'] = $this->m_end_time;

			return $data;
		}
		return true;
	}

	//发牌
	public function DealCard($chair)
	{
		if ($this->m_bChairHu[$chair])	//未胡玩家发牌
		{
			if ($this->m_nCountHu >= 1)
			{
				return false;
			}
			$this->m_chairCurrentPlayer = $this->_anti_clock($chair);
			return $this->DealCard($this->m_chairCurrentPlayer);
		}

		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_WAITING;
				$this->m_sPlayer[$i]->card_taken_now = 0;
			}
		}

		if(empty($this->m_nCardBuf[$this->m_nCountAllot]))				//没牌啦
		{
			//echo("没牌啦".__LINE__.__CLASS__);
			$this->m_nEndReason = ConstConfig::END_REASON_NOCARD;
			$this->HandleSetOver();
			return true;
		}

		$the_card = $this->m_nCardBuf[$this->m_nCountAllot];
		$this->m_nCountAllot++;

		$this->m_sPlayer[$chair]->card_taken_now = $the_card;

		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
		$this->m_bChooseBuf[$chair] = 1;

		$this->m_nHuGiveUp[$chair] = 0;	//重置过手胡

		$this->_set_record_game(ConstConfig::RECORD_DRAW, $chair, $the_card, $chair);

		return true;
	}

	public function HandleSetOver()
	{
		if($this->m_sysPhase == ConstConfig::SYSTEMPHASE_SET_OVER)
		{
			return false;
		}

		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_SET_OVER;
		//m_sOutedCard->clear();

		if ($this->m_nEndReason == ConstConfig::END_REASON_HU)
		{
			$this->CalcHuScore(); //正常算分，此时无逃跑得失相等
		}
		else if ($this->m_nEndReason==ConstConfig::END_REASON_NOCARD)
		{
			$this->CalcNoCardScore();
		}
		else if($this->m_nEndReason == ConstConfig::END_REASON_FLEE )		//逃跑结算游戏
		{
			//逃跑牌局等待，不结算
			//$this->CalcFleeScore();
		}
		else
		{
			echo(__LINE__.__CLASS__."Unknow end reason: ".$this->m_nEndReason);
		}

		//下一局庄家
		if($this->m_nEndReason==ConstConfig::END_REASON_NOCARD && 255 == $this->m_nChairBankerNext)
		{
			$this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker, 0);
		}

		//准备状态
		$this->m_ready = array(0,0,0,0);

		//本局结束时间
		$this->m_end_time = date('Y-m-d H:i:s', time());

		//写记录
		$this->WriteScore();

		//最后一局结束时候修改房间状态
		if(empty($this->m_rule) || $this->m_rule->set_num <= $this->m_nSetCount)
		{
			$this->m_room_state = ConstConfig::ROOM_STATE_OVER;
			$this->m_bLastGameOver = 1;
		}
		//状态变化发消息
		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_game_over', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}

		$this->_set_game_and_checkout();

	}

	///////////////////////得分处理///////////////////////////
	//每局个人  +=赢的分  +=输的分  +=庄家 的分
	public function ScoreOneHuCal($chair, &$lost_chair)
	{
		$fan_sum = $this->judge_fan($chair);  //这个就是  一共多少分
		if($fan_sum < $this->m_rule->min_fan)
		{
			$this->m_HuCurt[$chair]->clear();
			return false;
		}
		$PerWinScore = $fan_sum;

		$wWinScore = 0;

		$this->m_wHuScore = [0,0,0,0];

		if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		{
			$chairBaoPai = 255;

			for($i = 0; $i < $this->m_rule->player_count; $i++)
			{
				if($i == $chair)
				{
					continue;	//单用户测试需要关掉
				}

				if ($this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
				{
					continue;
				}

				if($chairBaoPai != 255)
				{
					$lost_chair = $chairBaoPai;	//包牌用户
				}
				else
				{
					$lost_chair = $i;
				}

				$banker_fan = 0;	//庄家分
				if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair))
				{
					$banker_fan = 2;
				}

				$wWinScore = 0;
				$wWinScore +=  $PerWinScore + $banker_fan;  //赢的分 加  庄家的分

				$this->m_wHuScore[$lost_chair] -= $wWinScore;
				$this->m_wHuScore[$chair] += $wWinScore;

				$this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
				$this->m_wSetScore[$chair] += $wWinScore;

				$this->m_HuCurt[$chair]->gain_chair[0]++;
				$this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $lost_chair;
			}
			return true;
		}
		// 吃炮者算分在此处！！
		else if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
		{
			$chairBaoPai = 255;

			$banker_fan = 0;	//庄家翻倍
			if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair))
			{
				$banker_fan = 2;
			}

			if(!empty($this->m_rule->is_za_hu_mian_gang))
			{
				for($j = 0; $j < $this->m_rule->player_count; $j++)
				{
					$k = $lost_chair;
					//退回玩家k赢玩家j的杠分
					if ($k != $j && $this->m_wGangScore[$k][$j] > 0)
					{
						$this->m_wGangScore[$j][$j] += $this->m_wGangScore[$k][$j];
						$this->m_wGangScore[$k][$k] -= $this->m_wGangScore[$k][$j];
						$this->m_wGangScore[$k][$j] = 0;
					}
				}
			}

			$wWinScore = 0;
			$wWinScore += $PerWinScore + $banker_fan ;  //赢的分 加  庄家的分

			$this->m_wHuScore[$lost_chair] -= $wWinScore;
			$this->m_wHuScore[$chair] += $wWinScore;

			$this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
			$this->m_wSetScore[$chair] += $wWinScore;

			$this->m_HuCurt[$chair]->gain_chair[0] = 1;
			$this->m_HuCurt[$chair]->gain_chair[1]=$lost_chair;


			return true;
		}

		echo("此人没有胡".__LINE__.__CLASS__);
		return false;
	}

	//每局牌局最终  分  赢的分-输的分
	public function CalcHuScore()
	{
		$cash = 0;
		//	Score_Struct score[PLAYER_COUNT];
		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$this->m_Score[$i]->clear();
		}
		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$this->m_Score[$i]->score = $this->m_wSetScore[$i]+ $this->m_wSetLoseScore[$i]+ $this->m_wGangScore[$i][$i] +$this->m_wFollowScore[$i];
			$this->m_Score[$i]->set_count = $this->m_nSetCount;
			if ($this->m_Score[$i]->score > 0)
			{
				$this->m_Score[$i]->win_count = 1;
			}
			else
			{
				$this->m_Score[$i]->lose_count = 1;
			}

			$this->m_room_players[$i]['score'] = $this->m_Score[$i]->score;
		}
	}

	///荒庄结算
	public function CalcNoCardScore()
	{
		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$this->m_Score[$i]->clear();
		}

		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$this->m_wGangScore[$i][$i] = 0;
			$this->m_wFollowScore[$i] = 0;
			$this->m_Score[$i]->score = 0;
			$this->m_Score[$i]->set_count = $this->m_nSetCount;

			if ($this->m_Score[$i]->score>0)
			{
				$this->m_Score[$i]->win_count = 1;
			}
			else
			{
				$this->m_Score[$i]->lose_count = 1;
			}

			$this->m_room_players[$i]['score'] = $this->m_Score[$i]->score;
		}
	}

	//总分处理
	public function WriteScore()
	{
		for($i = 0; $i < $this->m_rule->player_count; $i++)
		{
			$this->m_wTotalScore[$i]->n_score += $this->m_Score[$i]->score;

			if($this->m_wSetScore[$i])
			{
				$this->m_hu_desc[$i] = $this->m_hu_desc[$i].'+'.($this->m_wSetScore[$i]).' ';
			}
			else
			{
				$this->m_hu_desc[$i] = '';
			}

			if($this->m_wSetLoseScore[$i])
			{
				$this->m_hu_desc[$i] .= '被胡'.$this->m_wSetLoseScore[$i].' ';
			}

			if($this->m_wGangScore[$i][$i]>0)
			{
				$this->m_hu_desc[$i] .= '杠分+'.$this->m_wGangScore[$i][$i].' ';
			}
			else
			{
				$this->m_hu_desc[$i] .= '杠分'.$this->m_wGangScore[$i][$i].' ';
			}

			if($this->m_rule->is_genzhuang)
			{
				if($this->m_wFollowScore[$i]>0)
				{
					$this->m_hu_desc[$i] .= '跟庄+'.$this->m_wFollowScore[$i].' ';
				}
				else
				{
					$this->m_hu_desc[$i] .= '跟庄'.$this->m_wFollowScore[$i].' ';
				}
			}


		}
	}

	//洗牌
	public function WashCard()
	{
		if(!empty($this->m_rule->is_feng))
		{
			$this->m_nCardBuf = ConstConfig::ALL_CARD_136;
			$this->m_nAllCardNum = ConstConfig::BASE_CARD_NUM_FENG;
			if(defined("gf\\conf\\Config::TEST_PAI") && Config::TEST_PAI)
			{
				$this->m_nCardBuf = ConstConfig::ALL_CARD_136_TEST;
			}
		}
		else 
		{
			$this->m_nCardBuf = ConstConfig::ALL_CARD_108;
			$this->m_nAllCardNum = ConstConfig::BASE_CARD_NUM;
			if(defined("gf\\conf\\Config::TEST_PAI") && Config::TEST_PAI)
			{
				$this->m_nCardBuf = ConstConfig::ALL_CARD_108_TEST;
			}
		}

		if(Config::WASHCARD)
		{
			shuffle($this->m_nCardBuf); shuffle($this->m_nCardBuf);	//为了测试 不洗牌
		}
	}

	//批量发牌
	public function DealAllCardEx()
	{
		$temp_card = 255;
		$this->WashCard();

		//$this->_deal_test_card();

		//给每人发13张牌,整合成每个用户发一圈牌(4张)
		$tmp_card_arr = array(['', '', '', ''], ['', '', '', ''], ['', '', '', ''], ['', '', '', '']);
		for($i=0; $i<$this->m_rule->player_count ; $i++)
		{
			for($k=0; $k<ConstConfig::BASE_HOLD_CARD_NUM; $k++)
			{
				$temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];	//从牌缓冲区里那张牌
				$this->_list_insert($i, $temp_card);
				$tmp_card_arr[intval($k/4)][$i] .= sprintf("%02d",$temp_card);
			}
		}
		foreach ($tmp_card_arr as $tmp_card_item)
		{
			$this->_set_record_game(ConstConfig::RECORD_DRAW_ALL, intval($tmp_card_item[0]), intval($tmp_card_item[1]), intval($tmp_card_item[2]), intval($tmp_card_item[3]));
		}

		//给庄家发第14张牌
		$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->m_nCardBuf[$this->m_nCountAllot++];
		$this->_set_record_game(ConstConfig::RECORD_DRAW, $this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);

		//整理排序
		$this->_list_insert($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
		$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->_find_14_card($this->m_nChairBanker);

		//订 翻混牌
		if(!empty($this->m_rule->is_fanhun))
		{
			$this->m_fan_hun_card = $this->m_nCardBuf[$this->m_nCountAllot++];
			$this->_get_fan_hun($this->m_fan_hun_card);

			$this->_set_record_game(ConstConfig::RECORD_FANHUN, $this->m_nChairBanker, $this->m_fan_hun_card);
		}
	}

	public function game_to_playing()
	{
		//状态设定
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD ;
		$this->m_chairCurrentPlayer = $this->m_nChairBanker;

		$this->m_sPlayer[$this->m_nChairBanker]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
		$this->m_bChooseBuf[$this->m_nChairBanker] = 1;

		//状态变化发消息
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}
		$this->handle_flee_play(true);	//更新断线用户
	}

	public function start_pao_zi()
	{
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_XIA_PAO;
		for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
		{
			$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_XIA_PAO;
			//发消息
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}
	}

	//开始玩
	public function on_start_game()			//游戏开始
	{
		$itime = time();
		//初始化数据，非首局的时候还要相关处理
		$this->InitData();
		$this->m_start_time = $itime;
		$this->m_nSetCount += 1;
		$this->m_room_state = ConstConfig::ROOM_STATE_GAMEING;

		$this->_set_record_game(ConstConfig::RECORD_DEALER, $this->m_nChairBanker, 0, 0, intval(implode('', $this->m_dice)));

		//下炮子
		// if(!empty($this->m_rule->is_paozi))
		// {
		// 	$this->start_pao_zi();
		// 	return true;
		// }
		//发牌
		$this->DealAllCardEx();
		$this->game_to_playing();

		return true;
	}

	////////////////////////////其他///////////////////////////
	//玩家i相对于玩家j的位置,如(0,3),返回1(即下家)
	private function _chair_to($i, $j)
	{
		$tmp_chair = ($j - $i + $this->m_rule->player_count) % ($this->m_rule->player_count);
		return $tmp_chair;
	}

	//返回chair逆时针转 n 的玩家
	private function _anti_clock($chair, $n = 1)
	{
		$tmp_chair = ($chair + $this->m_rule->player_count + $n) % ($this->m_rule->player_count);
		return $tmp_chair;
	}

	private function _send_act($cmd, $chair, $card=0)
	{
		$this->_send_cmd('s_act', array('cmd'=>$cmd, 'chair'=>$chair, 'card'=>$card), Game_cmd::SCO_ALL_PLAYER);
	}

	//向客户端发送后台处理数据
	private function _send_cmd($act, $data, $scope = Game_cmd::SCO_ALL_PLAYER, $uid = 0)
    {
        $cmd = new Game_cmd($this->m_room_id, $act, $data, $scope, $uid);
        $cmd->send($this->serv);
        unset($cmd);
    }

	//插入牌
	private function _list_insert($chair, $card)
	{
		$card_type = $this->_get_card_type($card);
		if($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			echo("错误牌类型，_list_insert".__LINE__.__CLASS__);
			return false;
		}
		$card_key = $card % 16;
		//if($this->m_sPlayer[$chair]->card[$card_type][$card_key] < 4)
		{
			$this->m_sPlayer[$chair]->card[$card_type][$card_key] += 1;
			$this->m_sPlayer[$chair]->card[$card_type][0] += 1;
			$this->m_sPlayer[$chair]->len += 1;
			return true;
		}
		return false;
	}

	//删除牌
	private function _list_delete($chair, $card)
	{
		$card_type = $this->_get_card_type($card);
		if($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}
		$card_key = $card%16;
		if($this->m_sPlayer[$chair]->card[$card_type][$card_key] > 0)
		{
			$this->m_sPlayer[$chair]->card[$card_type][$card_key] -= 1;
			$this->m_sPlayer[$chair]->card[$card_type][0] -= 1;
			$this->m_sPlayer[$chair]->len -= 1;
			return true;
		}
		return false;
	}

	// 查找牌，返回个数
	private function _list_find($chair, $card)
	{
		$card_type = $this->_get_card_type($card);
		if($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}
		$card_key = $card%16;
		return $this->m_sPlayer[$chair]->card[$card_type][$card_key];
	}

	//返回牌的类型
	private function _get_card_type($card)
	{
		if($card <= 9 && $card >= 1)	return ConstConfig::PAI_TYPE_WAN;
		if($card <= 25 && $card >= 17)	return ConstConfig::PAI_TYPE_TIAO;
		if($card <= 41 && $card >= 33)	return ConstConfig::PAI_TYPE_TONG;
		if($card <= 55 && $card >= 49)	return ConstConfig::PAI_TYPE_FENG;
		if($card <= 72 && $card >= 65)	return ConstConfig::PAI_TYPE_DRAGON;
		return ConstConfig::PAI_TYPE_PAI_TYPE_INVALID;
	}

	//  牌index
	private function _get_card_index($type, $key)
	{
		//四川麻将没有风牌和花牌
		if($type >=ConstConfig::PAI_TYPE_WAN  && $type <=ConstConfig::PAI_TYPE_DRAGON && $key >=1 && $key <=9)
		{
			return $type * 16 + $key;
		}
		return 0;
	}

	//取消选择buf
	private function _clear_choose_buf($chair, $ClearGang=true)
	{
		if($ClearGang)
		{
			$this->m_sQiangGang->clear();
		}
		$this->m_bChooseBuf[$chair] = 0;
	}

	//判断有没有吃
	private function _find_eat($chair,$num)
	{
		if($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
		{
			return false;
		}

		$card_type = $this->_get_card_type($this->m_sOutedCard->card);
		if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type || ConstConfig::PAI_TYPE_FENG == $card_type || ConstConfig::PAI_TYPE_DRAGON == $card_type)
		{
			return false;
		}

		if($num == 1)
		{
			$eat_card_first_tmp = $this->m_sOutedCard->card+1;
			$eat_card_second_tmp = $this->m_sOutedCard->card+2;
		}
		elseif($num == 2)
		{
			$eat_card_first_tmp = $this->m_sOutedCard->card-1;
			$eat_card_second_tmp = $this->m_sOutedCard->card+1;
		}
		elseif($num == 3)
		{
			$eat_card_first_tmp = $this->m_sOutedCard->card-2;
			$eat_card_second_tmp = $this->m_sOutedCard->card-1;
		}

		$card_count_first_tmp = $this->_list_find($chair, $eat_card_first_tmp);
		$card_count_second_tmp = $this->_list_find($chair, $eat_card_second_tmp);

		if (  $card_count_first_tmp >= 1 && $card_count_second_tmp >= 1 )
		{
			return true;
		}

		return false;
	}

	//判断有没有碰
	private function _find_peng($chair)
	{
		if($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
		{
			return false;
		}

		$card_type = $this->_get_card_type($this->m_sOutedCard->card);
		if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
		{
			return false;
		}

		$card_count = $this->_list_find($chair, $this->m_sOutedCard->card);

		if (  $card_count == 2 || $card_count == 3 )
		{
			return true;
		}

		return false;
	}

	// 判断有没有别人打来的明杠
	private function _find_zhi_gang($chair)
	{
		if($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
		{
			return false;
		}

		$card_type = $this->_get_card_type($this->m_sOutedCard->card);
		if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
		{
			return false;
		}

		$card_count = $this->_list_find($chair, $this->m_sOutedCard->card);
		if($card_count == 3)
		{
			return true;
		}
		return false;
	}

	//找出第14张牌
	private function _find_14_card($chair)
	{
		$last_type = ConstConfig::PAI_TYPE_DRAGON;
		while(empty($this->m_sPlayer[$chair]->card[$last_type][0]))
		{
			$last_type --;
			if($last_type < 0)
			{
				break;
			}
		}
		if($last_type < 0)
		{
			echo ("竟然没有牌aaaaaaaas".__LINE__.__CLASS__ );
			return false;
		}

		for($i=9; $i>0; $i--)
		{
			if($this->m_sPlayer[$chair]->card[$last_type][$i] > 0)
			{
				$fouteen_card = $this->_get_card_index($last_type, $i);
				$this->m_sPlayer[$chair]->card[$last_type][$i] -= 1;
				$this->m_sPlayer[$chair]->card[$last_type][0] -= 1;
				$this->m_sPlayer[$chair]->len -= 1;
				break;
			}
		}

		if(empty($fouteen_card))
		{
			return false;
		}

		return $fouteen_card;
	}

	//掷骰定庄家
	private function _on_table_status_to_playing()
	{
		$result = Room::$get_conf;
		if(empty($result['data']['winner_currency']))
		{
			$this->m_nChairBanker = 0;
		}
		else
		{
			$this->m_nChairBanker = mt_rand(0, ($this->m_rule->player_count-1));
		}
		return;
	}

	private function _cancle_game()
	{
		$cancle_count = 0;
		$yes_count = 0;
		$is_cancle = 0;
		$flee_count = 0;
		
		if($this->m_cancle_first == 255)
		{
			return $is_cancle;
		}

		for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
		{
			if(!empty($this->m_cancle[$i]) || empty($this->m_room_players[$i]) || !empty($this->m_room_players[$i]['flee_time']))
			{
				//空位子和断线用户都算同意结束牌局
				$cancle_count++;
				if( (!empty($this->m_cancle[$i]) && $this->m_cancle[$i] == 1) || empty($this->m_room_players[$i]) || !empty($this->m_room_players[$i]['flee_time']))
				{
					if(!empty($this->m_room_players[$i]['flee_time']))
					{
						$flee_count++;
					}
					$yes_count++;
				}
			}
			if($this->m_room_state != ConstConfig::ROOM_STATE_GAMEING && !empty($this->m_room_players[$i]['uid']) && $this->m_room_owner == $this->m_room_players[$i]['uid'] && $this->m_cancle[$i] == 1)
			{
				//游戏还没开始的时候 房主可以直接结束房间
				$cancle_count = $this->m_rule->player_count ;
				$yes_count = $this->m_rule->player_count ;
				break;
			}
		}
		if($cancle_count >= $this->m_rule->player_count - 1 )
		{
			if($yes_count >= $this->m_rule->player_count - 1 && $flee_count < $yes_count)
			{
				$this->m_room_state = ConstConfig::ROOM_STATE_OVER;
				$is_cancle = 1;
			}
			else if($cancle_count == $this->m_rule->player_count)
			{
				for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
				{
					$this->m_cancle[$i] = 0;
				}
				$is_cancle = 2;
				$this->m_cancle_first = 255;
			}
		}

		$cmd = new Game_cmd($this->m_room_id, 's_cancle_game', array('is_cancle'=>$is_cancle, 'm_cancle_first'=>$this->m_cancle_first, 'm_cancle'=>$this->m_cancle), Game_cmd::SCO_ALL_PLAYER );
		$cmd->send($this->serv);
		unset($cmd);

		if($is_cancle == 1)
		{
			$is_log = false;
			if($this->m_nSetCount > 1)
			{
				$is_log = true;
			}
			$this->m_sysPhase = ConstConfig::SYSTEMPHASE_SET_OVER;
			$this->m_nSetCount = 255;	//用于解散结束牌局判定
			$this->m_ready = array(0,0,0,0);
			$this->m_end_time = date('Y-m-d H:i:s', time());
			//发送结束结算
			$cmd = new Game_cmd($this->m_room_id, 's_game_over', $this->OnGetChairScene($this->m_cancle_first, true), Game_cmd::SCO_ALL_PLAYER );
			$cmd->send($this->serv);
			unset($cmd);

			$this->_set_game_and_checkout($is_log);

			$this->clear();
		}

		return $is_cancle;
	}

	//倒牌某门牌的个数
	private function _stand_type_count($chair,$card_type)
	{
		$card_num = 0;

		if( $this->m_sStandCard[$chair]->num > 0)//有倒牌
		{
			for($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
			{
				if($this->_get_card_type($this->m_sStandCard[$chair]->card[$i]) == $card_type)
				{
					if(ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i] || ConstConfig::DAO_PAI_TYPE_KE == $this->m_sStandCard[$chair]->type[$i])
					{
						 $card_num += 3 ;
					}
					elseif(ConstConfig::DAO_PAI_TYPE_MINGGANG == $this->m_sStandCard[$chair]->type[$i] || ConstConfig::DAO_PAI_TYPE_ANGANG == $this->m_sStandCard[$chair]->type[$i] || ConstConfig::DAO_PAI_TYPE_WANGANG == $this->m_sStandCard[$chair]->type[$i])
					{
						$card_num += 4;
					}
				}
			}
		}
		return $card_num;
	}

	//跟庄
	private function _genzhuang_do()
	{
		if( !empty($this->m_rule->is_genzhuang) && $this->m_sFollowCard->status == ConstConfig::FOLLOW_STATUS &&  4 == $this->m_rule->player_count )
		{
			if(0 == $this->m_sFollowCard->follow_card && $this->m_sOutedCard->chair == $this->m_nChairBanker)
			{
				$this->m_sFollowCard->follow_card = $this->m_sOutedCard->card;
				$this->m_sFollowCard->num +=1 ;
			}
			elseif($this->m_sFollowCard->follow_card == $this->m_sOutedCard->card)
			{
				$this->m_sFollowCard->num +=1 ;
			}
			else
			{
				$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;
			}

			if($this->m_sFollowCard->num >= $this->m_rule->player_count)
			{
				for( $i=0; $i<$this->m_rule->player_count ; $i++)
				{
					$nFollowScore = ConstConfig::N_FOLLOWSCORE;
					if($i == $this->m_nChairBanker)
					{
						continue;
					}
					$this->m_wFollowScore[$this->m_nChairBanker] -= $nFollowScore;
					$this->m_wFollowScore[$i] += $nFollowScore;
				}

				$this->_set_record_game(ConstConfig::RECORD_GENZHUANG, $this->m_nChairBanker, $this->m_sFollowCard->follow_card);

				//状态变化发消息
				$this->_send_act('c_follow', 0 ,$this->m_sFollowCard->follow_card);
				$this->m_sFollowCard->clear();//更新跟庄标记 status=1,
			}
		}
	}

	private function _judge_da8zhang($chair, $replace_fanhun , $is_fanhun = false, $rule_no_fanhun = false, $add_fanhun_num = 0)
	{
		$is_da8zhang = false;
		if($rule_no_fanhun) //规则无翻混或手牌无翻混
		{
			for($k = ConstConfig::PAI_TYPE_WAN ; $k <= ConstConfig::PAI_TYPE_FENG ; $k++)
			{
				$tmp_stand_num = 0;
				$tmp_stand_num = $this->_stand_type_count($chair,$k); //倒牌中 $i牌型个数
				if(!$is_da8zhang && $this->m_sPlayer[$chair]->card[$k][0] + $tmp_stand_num >= 8)
				{
					$is_da8zhang = true;
					break;
				}
			}
		}
		else
		{
			//有翻混
			$da8zhang_fanhun_num = 0;

			if($this->m_hun_card)
			{
				$da8zhang_fanhun_num = $this->_list_find($chair, $this->m_hun_card);	//手牌翻混个数
				$da8zhang_fanhun_type = $this->_get_card_type($this->m_hun_card);        //翻混牌类型

				if($is_fanhun)//打出的牌是否为翻混
				{
					$da8zhang_fanhun_num = $da8zhang_fanhun_num - 1;
				}
			}

			for($k = ConstConfig::PAI_TYPE_WAN ; $k <= ConstConfig::PAI_TYPE_FENG ; $k++)
			{
				$tmp_stand_num = 0;
				$tmp_stand_num = $this->_stand_type_count($chair,$k); //倒牌中 $i牌型个数

				if(!$is_da8zhang )
				{
					if(($this->m_sPlayer[$chair]->card[$k][0] + $tmp_stand_num - $da8zhang_fanhun_num + $replace_fanhun[$k]) + $add_fanhun_num >= 8 && $k == $da8zhang_fanhun_type)
					{
						$is_da8zhang = true;
						break;
					}
					elseif(($this->m_sPlayer[$chair]->card[$k][0] + $tmp_stand_num + $replace_fanhun[$k] + $add_fanhun_num) >= 8 && $k != $da8zhang_fanhun_type)
					{
						$is_da8zhang = true;
						break;
					}
				}
			}
		}

		return $is_da8zhang;
	}

	private function _deal_test_card()
	{
		//发测试牌
		for($i = 0; $i < $this->m_rule->player_count; $i++)
		{
			$power = 0;
			if($i == 0)
			{
				$power = 25;
			}
			if(defined("gf\\conf\\Config::WHITE_UID") && in_array($this->m_room_players[$i]['uid'], Config::WHITE_UID))
			{
				$power = 100;
			}
			if( mt_rand(1, 100) <= $power )
			{
				$this->_change_pai($i);
				break;
			}
		}
	}

	private function _change_pai($chair)
	{
		$change_arr = array();
		$pai = mt_rand(ConstConfig::PAI_TYPE_WAN ,ConstConfig::PAI_TYPE_TIAO);
		$key = mt_rand(1,7);
		$change_arr[] = $this->_get_card_index($pai, $key);
		$change_arr[] = $this->_get_card_index($pai, $key + 1);
		$change_arr[] = $this->_get_card_index($pai, $key + 2);
		$key = mt_rand(1,7);
		$change_arr[] = $this->_get_card_index($pai, $key);
		$change_arr[] = $this->_get_card_index($pai, $key + 1);
		$change_arr[] = $this->_get_card_index($pai, $key + 2);
		//		$key = mt_rand(1,9);
		//		$change_arr[] = $this->_get_card_index($pai, $key);
		//		$change_arr[] = $this->_get_card_index($pai, $key);
		//		$change_arr[] = $this->_get_card_index($pai, $key);

		$index = 0;
		foreach ($change_arr as $change_item)
		{
			if($this->m_nCardBuf[$index] != $change_item)
			{
				for($k = $index + 1; $k < $this->m_nAllCardNum; $k++)
				{
					if ($this->m_nCardBuf[$k] == $change_item)
					{
						$this->m_nCardBuf[$k] = $this->m_nCardBuf[$index];
						$this->m_nCardBuf[$index] = $change_item;
						break;
					}
				}
			}
			$index = $index + 1;
		}

		if($chair != 0)
		{
			$offset = $chair * 13;
			for($m = 1; $m <= 13; $m++)
			{
				$tmp = $this->m_nCardBuf[$m];
				$this->m_nCardBuf[$m] = $this->m_nCardBuf[$m + $offset];
				$this->m_nCardBuf[$m + $offset] = $tmp;
			}
		}
	}

	private function _get_fan_hun($fan_hun_card)
	{
		$temp_type = $this->_get_card_type($fan_hun_card);
		$temp_card_index = $fan_hun_card % 16;

		if($temp_type == ConstConfig::PAI_TYPE_WAN || $temp_type == ConstConfig::PAI_TYPE_TIAO || $temp_type ==ConstConfig::PAI_TYPE_TONG )
		{
			$tmp_index_array = array(0,2,3,4,5,6,7,8,9,1);
		}
		elseif($temp_type == ConstConfig::PAI_TYPE_FENG)
		{
			$tmp_index_array = array(0,2,3,4,1,6,7,5);
		}
		elseif($temp_type == ConstConfig::PAI_TYPE_DRAGON)
		{
			$tmp_index_array = array(0,2,3,4,1,6,7,8,5);
		}
		else
		{
			echo("混牌错误，出现未定义类型的牌".__LINE__.__CLASS__);
			return false;
		}

		$this->m_hun_card = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index
		return $this->m_hun_card;
	}

	private function _set_record_game($act, $param_1 = 0, $param_2 = 0, $param_3 = 0, $param_4 = 0)
	{
		$param_1_tmp = 0;
		$param_3_tmp = 0;
		if(in_array($act, [ConstConfig::RECORD_CHI, ConstConfig::RECORD_PENG, ConstConfig::RECORD_ZHIGANG, ConstConfig::RECORD_ANGANG, ConstConfig::RECORD_ZHUANGANG, ConstConfig::RECORD_HU, ConstConfig::RECORD_ZIMO, ConstConfig::RECORD_DISCARD, ConstConfig::RECORD_DRAW, ConstConfig::RECORD_DEALER, ConstConfig::RECORD_FANHUN, ConstConfig::RECORD_XIAOSA, ConstConfig::RECORD_HU_QIANGGANG]))
		{
			if(is_array($param_1))
			{
				foreach ($param_1 as $value)
				{
					$param_1_tmp += pow(2, $value);
				}
			}
			else
			{
				$param_1_tmp += pow(2, $param_1);
			}

			$param_3_tmp += pow(2, $param_3);

		}
		else
		{
			$param_1_tmp = $param_1;
			$param_3_tmp = $param_3;
		}

		$this->m_record_game[] = $act.'|'.$param_1_tmp.'|'.$param_2.'|'.$param_3_tmp.'|'.$param_4;
	}

	private function _set_game_info()
	{
		$game_info = [];
		$game_info['date'] = date('m-d H:i:s', time());
		$game_info['rule'] = $this->m_rule;
		$game_info['play'] = $this->m_room_players;
		$game_info['game'] = implode(',', $this->m_record_game);

		if(!$game_info['game'])
		{
			return false;
		}
		return $game_info;
	}

	//回调web
	private function _set_game_and_checkout($is_log=false)
	{
		$itime = time();
		$uid_arr = array();
		foreach ($this->m_room_players as $key => $room_user)
		{
			if(!empty($room_user['uid']))
			{
				$uid_arr[] = $room_user['uid'];
			}
		}

		$is_room_over = 0;
		if( empty($this->m_rule)
			|| ( $this->m_nSetCount != 255 && $this->m_rule->set_num <= $this->m_nSetCount  && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext))
			|| ( $this->m_nSetCount == 255 && $is_log )
			)
		{
			$is_room_over = 1;
		}

		//web set_game_log
		$tmp_game_info = $this->_set_game_info();
		if($tmp_game_info && $this->m_nSetCount != 255)	//非投票解散的牌局
		{
			BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'set_game_log', 'platform'=>'gfplay', 'rid'=>$this->m_room_id,'uid'=>$this->m_room_owner, 'uid_arr'=>implode(',', $uid_arr)
			, 'game_info'=>json_encode($tmp_game_info, JSON_UNESCAPED_UNICODE),'type'=>1, 'is_room_over'=>$is_room_over
			, 'game_type'=>$this->m_game_type, 'play_time'=>$itime - $this->m_start_time));
		}

		//扣费
		$result = Room::$get_conf;
		if(!empty($result['data']['room_type']))
		{
        	$currency_tmp = BaseFunction::need_currency($result['data']['room_type'],$this->m_game_type,$this->m_rule->set_num);
		}

		if(empty($result['data']['winner_currency']))
		{
			if($this->m_nSetCount == 1)
			{
				$currency = !empty($currency_tmp) ? (-$currency_tmp) : 0;
				BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'checkout_open_room', 'platform'=>'gfplay', 'uid'=>$this->m_room_owner, 'currency'=>$currency,'type'=>1));
			}
		}
		else
		{
			if($is_room_over == 1)
			{
				$big_score = 0;
				$winner_arr	= array();
				for($i = 0; $i < $this->m_rule->player_count; $i++)
				{
					if($this->m_wTotalScore[$i]->n_score > $big_score)
					{
						$big_score = $this->m_wTotalScore[$i]->n_score;
						$winner_arr	= array();
						$winner_arr[] = $this->m_room_players[$i]['uid'];
					}
					else if($this->m_wTotalScore[$i]->n_score == $big_score && !empty($this->m_room_players[$i]))
					{
						$winner_arr[] = $this->m_room_players[$i]['uid'];
					}
				}
				$winner_count = 1;
				if($winner_arr)
				{
					$winner_count = count($winner_arr);
				}
				$currency_all = !empty($currency_tmp) ? $currency_tmp : 0;
				$currency = -(intval($currency_all/$winner_count));
				foreach ($winner_arr as $item_user)
				{
					BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'checkout_open_room', 'platform'=>'gfplay', 'uid'=>$item_user, 'currency'=>$currency,'type'=>1));
				}
			}
		}
	}

	//判断一色结果，通过引用返回
	private function _is_yise($qing_arr, &$is_qingyise, &$is_ziyise)
	{
		if(1 == count(array_unique($qing_arr)))
		{
			if(!empty($this->m_rule->is_qingyise_fan) && ConstConfig::PAI_TYPE_FENG != $qing_arr[0])
			{
				$is_qingyise = true;
			}
			else if(!empty($this->m_rule->is_ziyise))
			{
				$is_ziyise = true;
			}
			else
			{
				$is_qingyise = false;
				$is_ziyise = false;
			}
		}
	}

	//是否门清
	private function _is_menqing($chair)
	{
		$return = true;
		for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i ++)
		{
			if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_SHUN
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG_ZA
			  )
			{
				$return = false;
				break;
			}
		}
		return $return;
	}

	/////////////////////////////混悠处理///////////////////////////////////////////
	//混儿悠
	private function _is_hunyou($chair)
	{
		$return = false;
		if($this->m_HuCurt[$chair]->state != ConstConfig::WIN_STATUS_ZI_MO)
		{
			return false;
		}

		$temp_card = $this->m_sPlayer[$chair]->card_taken_now;
		$card_type = $this->_get_card_type($temp_card);
		$hun_card = $this->m_hun_card;
		$hun_type = $this->_get_card_type($this->m_hun_card);

		if($temp_card == $hun_card)  //抓来的牌是混 ,那就两张混子
		{
			//如果抓来的是个混,那手牌也需要又一个混
			if($this->m_sPlayer[$chair]->card[$hun_type][$hun_card%16] < 2)
			{
				return false;
			}
		}

		//去掉混牌和自摸的牌
		$this->_list_delete($chair,$temp_card);
		$this->_list_delete($chair,$hun_card);

		//判断能不能胡 并且没有将牌

		if($this->_judge_hun_32type($chair,false))
		{
			$return = true;
		}

		//还原手牌
		$this->_list_insert($chair,$temp_card);
		$this->_list_insert($chair,$hun_card);

		return $return;
	}

	//七对混悠
	private function _is_qidui_hunyou($chair,$is_fanhun = false)
	{
		if($this->m_HuCurt[$chair]->state != ConstConfig::WIN_STATUS_ZI_MO)
		{
			return false;
		}
		$temp_card = $this->m_HuCurt[$chair]->card;
		$card_type = $this->_get_card_type($temp_card);

		$hun_num = 0;
		if($this->m_hun_card)
		{
			$hun_num = $this->_list_find($chair, $this->m_hun_card);	//手牌翻混个数//-1 是为了  去掉混做将牌,
			$hun_type = $this->_get_card_type($this->m_hun_card);        //翻混牌类型
			$hun_card = $this->m_hun_card;       //翻混牌
			$hun_card_index = $hun_card%16;       //翻混牌
		}

		$hun_num = $is_fanhun ? $hun_num - 1 : $hun_num;	//打出的牌是否为翻混  混悠 必须是自摸

		$tmp_hun_num = 0;
		if($temp_card == $hun_card)  //抓来的牌 那就两张混子
		{
			//如果抓来的是个混,那手牌也需要又一个混
			$tmp_hun_num = 2;
			if($this->m_sPlayer[$chair]->card[$hun_type][$hun_card_index] < 2)
			{
				return false;
			}
		}
		else
		{
			$tmp_hun_num = 1;
		}

		if(!empty($this->m_rule->is_qidui_fan))
		{
			$need_fanhun = 0;	//需要混子个数
			$hu_qidui = false;

			if($this->m_sStandCard[$chair]->num > 0)
			{
				$hu_qidui = false;
			}
			else
			{
				//去掉混牌和自摸的牌
				$this->_list_delete($chair,$temp_card);
				$this->_list_delete($chair,$hun_card);

				$this->m_sPlayer[$chair]->card[$hun_type][$hun_card_index] = $is_fanhun ? 1 : 0;
				$gen_count_num = 0;//根的个数

				for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
				{
					if(0 == $this->m_sPlayer[$chair]->card[$i][0] || (0 == ($this->m_sPlayer[$chair]->card[$i][0] - ($hun_num - $tmp_hun_num)) && $i == $hun_type ))
					{
						continue;
					}
					for ($j=1; $j<=9; $j++)
					{
						if($this->m_sPlayer[$chair]->card[$i][$j] == 1 || $this->m_sPlayer[$chair]->card[$i][$j] == 3)
						{
							$need_fanhun +=1 ;
						}
					}
				}

				//还原手牌
				$this->_list_insert($chair,$temp_card);
				$this->_list_insert($chair,$hun_card);

				$this->m_sPlayer[$chair]->card[$hun_type][$hun_card_index] += ($hun_num - $tmp_hun_num);

				if($need_fanhun <= ($hun_num - $tmp_hun_num))
				{
					$hu_qidui = true;
				}

				if($hu_qidui)
				{
					return true;
				}
			}
		}

		return false;
	}

	///////////////////////////以下是边卡和"吊"的处理///////////////////////////
	//做key值  去  计算边卡吊
	public function HandleBianKaDiao($chair, $type , $insert_arr = array() , $is_fanhun = false, $is_boss_diao = false)
	{
		$return_type = self::HU_TYPE_FENGDING_TYPE_INVALID;
		if(($this->m_HuCurt[$chair]->card == $this->m_hun_card && $is_fanhun == false) || $type == $this->_get_card_type($this->m_HuCurt[$chair]->card))
		{
			if(!empty($this->m_rule->is_biankadiao))
			{
				//                        当前循环的类型
				if($this->_is_bian($chair, $type, $insert_arr ,$is_fanhun))
				{
					$return_type = self::ATTACHED_HU_BIAN;
				}
				else
				{
					if($this->_is_ka($chair, $type, $insert_arr, $is_fanhun))
					{
						$return_type = self::ATTACHED_HU_KA;
					}
					else
					{
						if($is_boss_diao && $this->_is_diao($chair,  $type, $insert_arr ,$is_fanhun))
						{
							$return_type = self::ATTACHED_HU_DIAO;
						}
					}
				}
			}
		}

		return $return_type;
	}

	//边
	private function _is_bian($chair, $type, $insert_arr = array(), $is_fanhun = false)
	{
		$return = false;
		//胡的牌
		$hu_card = $this->m_HuCurt[$chair]->card;
		$hu_type = $this->_get_card_type($hu_card);
		$hu_card_index = $hu_card % 16;

		if( $hu_card == $this->m_hun_card && $is_fanhun == false )
		{
			//抓来的是混子 ,看看这个混子 替换的是什么牌,,,如果手牌有12  89
			foreach ($insert_arr as $insert_item)
			{
				if($insert_item == 3 || $insert_item == 7)
				{
					$tmp_bian18 = 1;
					$tmp_bian29 = 2;
					if($insert_item == 7)
					{
						$tmp_bian18 = 8;
						$tmp_bian29 = 9;
					}

					//如果手里有12,那就 去掉123  看是不是胡牌
					if($this->m_sPlayer[$chair]->card[$type][$insert_item] > 0 && $this->m_sPlayer[$chair]->card[$type][$tmp_bian18] > 0 && $this->m_sPlayer[$chair]->card[$type][$tmp_bian29] > 0)
					{
						if($this->_judge_bian_ka($chair, $type, $insert_item, $tmp_bian18, $tmp_bian29))
						{
							return true;
						}
					}
				}
			}
		}
		else
		{
			if($hu_type == $type && $hu_type != ConstConfig::PAI_TYPE_FENG && in_array($hu_card_index ,[3,7]) )
			{
				if($hu_card_index == 3 || $hu_card_index == 7)
				{
					$tmp_bian18 = 1;
					$tmp_bian29 = 2;
					if($hu_card_index == 7)
					{
						$tmp_bian18 = 8;
						$tmp_bian29 = 9;
					}

					//如果手里有12,那就 去掉123  看是不是胡牌
					if($this->m_sPlayer[$chair]->card[$hu_type][$tmp_bian18] > 0 && $this->m_sPlayer[$chair]->card[$hu_type][$tmp_bian29] > 0)
					{
						//                               牌的类型   胡的牌           删除2          删3
						if($this->_judge_bian_ka($chair, $hu_type, $hu_card_index, $tmp_bian18, $tmp_bian29))
						{
							return true;
						}
					}
				}
			}
		}
		return $return;
	}

	//卡
	private function  _is_ka($chair, $type, $insert_arr = array(), $is_fanhun = false)
	{
		$return = false;
		//胡的牌
		$hu_card = $this->m_HuCurt[$chair]->card;
		$hu_type = $this->_get_card_type($hu_card);
		$hu_card_index = $hu_card % 16;

		if($hu_card == $this->m_hun_card && $is_fanhun == false)
		{
			foreach ($insert_arr as $insert_item)
			{
				if($insert_item != 1 && $insert_item != 9)
				{
					$tmp_ka4 = $insert_item - 1;
					$tmp_ka6 = $insert_item + 1;

					//如果手里有12,那就 去掉123  看是不是胡牌
					if($this->m_sPlayer[$chair]->card[$type][$insert_item] > 0 && $this->m_sPlayer[$chair]->card[$type][$tmp_ka4] > 0 && $this->m_sPlayer[$chair]->card[$type][$tmp_ka6] > 0)
					{
						if($this->_judge_bian_ka($chair, $type, $insert_item, $tmp_ka4, $tmp_ka6))
						{
							return true;
						}
					}
				}
			}
		}
		else
		{
			if($hu_type == $type && $hu_type != ConstConfig::PAI_TYPE_FENG && ($hu_card_index != 1 && $hu_card_index != 9))
			{
				$tmp_ka4 = $hu_card_index - 1;
				$tmp_ka6 = $hu_card_index + 1;

				//如果手里有12,那就 去掉123  看是不是胡牌
				if($this->m_sPlayer[$chair]->card[$type][$tmp_ka4] > 0 && $this->m_sPlayer[$chair]->card[$type][$tmp_ka6] > 0)
				{
					if($this->_judge_bian_ka($chair, $type, $hu_card_index, $tmp_ka4, $tmp_ka6))
					{
						return true;
					}
				}
			}
		}

		return $return;
	}

	//"吊"  只是能判断是将牌.不一定是单将,!!!  is_boss_diao=>单吊
	private function  _is_diao($chair, $type, $insert_arr = array(), $is_fanhun = false)
	{
		$return = false;
		//胡的牌
		$hu_card = $this->m_HuCurt[$chair]->card;
		$hu_type = $this->_get_card_type($hu_card);
		$hu_card_index = $hu_card % 16;

		//我需要干一件事情  //把所有的可能都判断一次  是不是单吊
		if($hu_card == $this->m_hun_card && $is_fanhun == false)
		{
			foreach ($insert_arr as $insert_item)
			{
				//如果手里有12,那就 去掉123  看是不是胡牌
				if($this->m_sPlayer[$chair]->card[$type][$insert_item] >=2 )
				{
					if($this->_judge_diao($chair, $type, $insert_item))
					{
						return true;
					}
				}
			}
		}
		else
		{
			if($hu_type == $type)
			{
				if($this->m_sPlayer[$chair]->card[$hu_type][$hu_card_index] >=2 )
				{
					if($this->_judge_diao($chair, $hu_type, $hu_card_index))
					{
						return true;
					}
				}
			}
		}

		return $return;
	}

	//judge 边卡
	private function _judge_bian_ka($chair, $hu_type, $hu_card_index, $tmp_first, $tmp_second)
	{
		$return = false;

		$this->m_sPlayer[$chair]->card[$hu_type][$hu_card_index] -= 1;
		$this->m_sPlayer[$chair]->card[$hu_type][$tmp_first] -= 1;
		$this->m_sPlayer[$chair]->card[$hu_type][$tmp_second] -= 1;

	    // 直接做key
		$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$hu_type], 1)));

		if(!empty(ConstConfig::$hu_data[$key]))
		{
			$hu_list_val = ConstConfig::$hu_data[$key];    //一定是非风牌
		}
		if(isset($hu_list_val) && ($hu_list_val & 1) == 1 ) //是32牌型  或者 是个根
		{
			$return = true;
		}

		$this->m_sPlayer[$chair]->card[$hu_type][$hu_card_index] += 1;
		$this->m_sPlayer[$chair]->card[$hu_type][$tmp_first] += 1;
		$this->m_sPlayer[$chair]->card[$hu_type][$tmp_second] += 1;

		return $return;
	}

	//judge 吊
	private function _judge_diao($chair, $hu_type, $hu_card_index)
	{
		$return = false;

		$this->m_sPlayer[$chair]->card[$hu_type][$hu_card_index] -= 1;
		$this->m_sPlayer[$chair]->card[$hu_type][$hu_card_index] -= 1;

	    // 直接做key
		$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$hu_type], 1)));

		if(!empty(ConstConfig::$hu_data[$key]))
		{
			$hu_list_val = ConstConfig::$hu_data[$key];    //一定是非风牌
		}

		if(isset($hu_list_val) && ($hu_list_val & 1) == 1 && ($hu_list_val & 32 )!= 32 ) //是32牌型  或者 是个根
		{
			$return = true;
		}

		$this->m_sPlayer[$chair]->card[$hu_type][$hu_card_index] += 1;
		$this->m_sPlayer[$chair]->card[$hu_type][$hu_card_index] += 1;

		return $return;
	}

	///////////////////以下是做单吊处理/////////////////////////////////////////////
    //单吊
	private function  _is_boss_diao($chair, $is_fanhun = false, $type_jiang_arr = array())
	{
		$return = true;
		//胡的牌
		$hu_card = $this->m_HuCurt[$chair]->card;
		$hu_type = $this->_get_card_type($hu_card);
		$hu_card_index = $hu_card % 16;

		//混子牌
		$have_hun_sum = 0;
		if($this->m_hun_card)
		{
			$hun_type = $this->_get_card_type($this->m_hun_card);
			$have_hun_sum = $this->_list_find($chair, $this->m_hun_card);
			if($is_fanhun)
			{
				$have_hun_sum = $have_hun_sum - 1;
			}
		}

		if($have_hun_sum <= 0)//没有混牌
		{
			if($this->m_sPlayer[$chair]->card[$hu_type][$hu_card_index] >= 2) //在手牌中的个数要大于2张
			{
				//没有混牌
				if( 32 == array_sum($type_jiang_arr) && $type_jiang_arr[$hu_type] == 32)//胡牌这门 如果是将门,如果是多吊,那也只能是这一门
				{
					//去掉手牌中的这两张牌
					$this->_list_delete($chair,$hu_card);
					$this->_list_delete($chair,$hu_card);

					$tmp_hu_data = &ConstConfig::$hu_data;
					if(ConstConfig::PAI_TYPE_FENG == $hu_type)
					{
						$tmp_hu_data = &ConstConfig::$hu_data_feng;
					}
					$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$hu_type], 1)));

					if(!empty($tmp_hu_data[$key]))
					{
						$hu_list_val = $tmp_hu_data[$key];
					}
					if(!isset($hu_list_val))
					{
						//还原胡的牌型
						$this->_list_insert($chair,$hu_card);
						$this->_list_insert($chair,$hu_card);
						//如果去掉手牌中的将牌都不胡牌  那就不是吊
						$return = false;
					}
					else
					{
						//还原胡的牌型
						$this->_list_insert($chair,$hu_card);
						$this->_list_insert($chair,$hu_card);
						//如果胡牌了再判断是不是单将
						if(!$this->_judge_dan_jiang($chair))
						{
							$return = false;
						}
						else
						{
						    $return =  true; //是 单  吊
						}

					}
				}
				else
				{
					$return = false;
				}
			}
			else
			{
				$return = false;
			}
		}
		else//抓来的不是混.手里必有混子   或者抓来的是混子   都需要把混子删掉
		{
			if($hu_card != $this->m_hun_card && $this->m_sPlayer[$chair]->card[$hu_type][$hu_card_index] < 2)//如果抓来的不是一个混子 ,那手牌要有>2
			{
				return false;
			}
			$n = 0;

			//删除掉胡的牌 ,,看看 胡其他牌的时候是不是将对
			$this->_list_delete($chair, $hu_card);

			$this->_splayer_card($chair, $hu_type, $is_fanhun);
			foreach($this->m_nPlayerList[$chair]  as $player_card_item)
			{
				if($player_card_item == $this->m_hun_card )//去掉胡的牌 和手中的混子
				{
					continue;
				}
				//删除非混子牌   看是否有将对
				$this->_list_delete($chair, $player_card_item);

				if($this->m_sPlayer[$chair]->len == 0 && $hu_card == $this->m_hun_card)
				{
					$this->_list_insert($chair, $player_card_item);
					$this->_list_insert($chair, $hu_card); //还原手牌
					return true;
				}
				else
				{
					if($this->_judge_hun_32type($chair,false))//judge需要修改成 能判断有没有将对的是 1不是32
					{
						$this->_list_insert($chair, $player_card_item);
						if($n >= 1)
						{
							$return = false; break;
						}
						else
						{
							$n += 1;
						}
					}
					else
					{
						$this->_list_insert($chair, $player_card_item);
					}
				}

			}

			if($n == 0)
			{
				$return = false;
			}

			$this->_list_insert($chair, $hu_card); //还原手牌
		}

		return $return;
	}

    //单将对
	private function _judge_dan_jiang($chair)
	{
		$return = true;
		$hu_card = $this->m_HuCurt[$chair]->card;

		$hu_type = $this->_get_card_type($hu_card);
		$hu_card_index = $hu_card % 16;

		$tmp_card_arr = array(1,2,3,4,5,6,7,8,9);

		$this->_list_delete($chair, $hu_card); //把胡的牌去掉

		foreach($tmp_card_arr as $key => $del_card)
		{
			if($this->m_sPlayer[$chair]->card[$hu_type][$del_card] == 0 || $del_card == $hu_card_index)
			{
				continue;//这门牌   依次去掉手牌中看还能不能满足&1,如果满足了 则是多吊
			}
			$del_card_index = $this->_get_card_index($hu_type,$del_card);

			$this->_list_delete($chair, $del_card_index);

			$tmp_hu_data = &ConstConfig::$hu_data;
			if(ConstConfig::PAI_TYPE_FENG == $hu_type)
			{
				$tmp_hu_data = &ConstConfig::$hu_data_feng;
			}

			$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$hu_type], 1)));
			$this->_list_insert($chair, $del_card_index);
			if(!empty($tmp_hu_data[$key]))
			{
				$hu_list_val = $tmp_hu_data[$key];
			}

			if(isset($hu_list_val)  && ($hu_list_val & 1) == 1 && ($hu_list_val & 32) != 32 )
			{

				$return = false; break;//有第二个将牌  则return false
			}
		}

		$this->_list_insert($chair, $hu_card);//还原胡的牌
		return $return;
	}

    //手牌中的牌
	private function _splayer_card($chair, $type, $is_fanhun = false)
	{
		//$n = 0;
		$this->m_nPlayerList[$chair] = array();  //因为  放弃胡 也会调用,,所以每次调用时候 先清空
		$hun_type = $this->_get_card_type($this->m_hun_card);

		if($type == $hun_type && $is_fanhun == false)//胡牌是混子,,给全部手牌
		{
			for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
			{
				if($this->m_sPlayer[$chair]->card[$i][0] == 0)
				{
					continue;
				}
				for($j=1;$j<=9;$j++)
				{
					if($this->m_sPlayer[$chair]->card[$i][$j] > 0)
					{
						//$n++;
						$tmp_card_index = $this->_get_card_index($i,$j);
						$this->m_nPlayerList[$chair][] = $tmp_card_index;
					}
				}
			}
		}
		else //胡牌不是真混子,只给本门牌
		{
			for($j=1;$j<=9;$j++)
			{
				if($this->m_sPlayer[$chair]->card[$type][$j] > 0)
				{
					$tmp_card_index = $this->_get_card_index($type,$j);
					$this->m_nPlayerList[$chair][] = $tmp_card_index;
				}
			}
		}
	}

	//表示12张牌  满足32牌型  且没有将对
	private function _judge_hun_32type($chair,$is_fanhun = false)
	{
		$fanhun_num = 0;
		if($this->m_hun_card)
		{
			$fanhun_num = $this->_list_find($chair, $this->m_hun_card);	//手牌翻混个数
			$fanhun_type = $this->_get_card_type($this->m_hun_card);        //翻混牌类型
			$fanhun_card = $this->m_hun_card%16;       //翻混牌
		}

		if($is_fanhun)//打出的牌是否为翻混
		{
			$fanhun_num = $fanhun_num - 1;
		}

		$return_type = false;

		//32牌型
		$is_hu_data_tmp = false;

		$no_jiang_judge_arr = array(0=>0,1=>2,2=>1,3=>0,4=>2,5=>1,6=>0,7=>2,8=>1,9=>0,10=>2,11=>1,12=>0);

		$need_fanhun = 0;	//需要红中翻混个数
		$replace_fanhun = array(0,0,0,0);

		for($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
		{
			if(0 == $this->m_sPlayer[$chair]->card[$j][0]
			|| (0 == $this->m_sPlayer[$chair]->card[$j][0]-$fanhun_num && $j == $fanhun_type )
			)
			{
				continue;
			}

			$pai_num = $this->m_sPlayer[$chair]->card[$j][0];	//一门牌个数
			if($j == $fanhun_type)	//混牌的牌型个数得减去混牌个数
			{
				$pai_num -= $fanhun_num;
			}

			//无将牌
			if($pai_num > 12)
			{
				break;
			}

			if($no_jiang_judge_arr[$pai_num] > 0)
			{
				$need_fanhun += $no_jiang_judge_arr[$pai_num];
				$replace_fanhun[$j] = $no_jiang_judge_arr[$pai_num];
			}
			else
			{
				//如果牌数量正合适，去判断是否胡牌数组里有
				if($j == $fanhun_type)
				{
					$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 0;	//去掉翻混
					if($is_fanhun)
					{
						$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 1;//打出的牌是翻混
					}
				}

				$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$j], 1)));
				if($j == ConstConfig::PAI_TYPE_FENG)
				{
					if(!isset(ConstConfig::$hu_data_feng[$key]) || (ConstConfig::$hu_data_feng[$key] & 1) != 1)
					{
						$need_fanhun += 3;
						$replace_fanhun[$j] = 3;
					}
				}
				else
				{
					if(!isset(ConstConfig::$hu_data[$key]) || (ConstConfig::$hu_data[$key] & 1 )!= 1)
					{
						$need_fanhun += 3;
						$replace_fanhun[$j] = 3;
					}
				}

				//还原手牌中的翻混
				if($j == $fanhun_type)
				{
					$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $fanhun_num;	//
					if($is_fanhun)
					{
						$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += 1;//
					}
				}
			}

			if($need_fanhun > $fanhun_num)
			{
				break;
			}
		}

		if($need_fanhun <= $fanhun_num)
		{
			//重置1 4情况的红中
			if($need_fanhun == 1 && $fanhun_num == 4)
			{
				foreach ($replace_fanhun as $type => $num)
				{
					if($num == 1)
					{
						//？？番数不一定最大，因为32牌型只有清一色和平胡两种，所以此处没有更大的番
						$replace_fanhun[$type] = 4;
					}
				}
			}

			//重置0 3 情况
			if($need_fanhun == 0 && $fanhun_num == 3)
			{
				foreach ( $replace_fanhun as $type => $num )
				{
					if( $this->m_sPlayer[$chair]->card[$type][0] > 0 )
					{
						$replace_fanhun[$type] = 3;
						break;
					}
				}
			}

			//校验胡
			foreach ($replace_fanhun as $type => $num)
			{
				if(0 == $num)
				{
					$is_hu_data_tmp = false;
					if($type == $fanhun_type)
					{
						$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 0;	//去掉翻混
						if($is_fanhun)
						{
							$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 1;//打出的牌是翻混
						}
					}
					$tmp_hu_data = &ConstConfig::$hu_data;
					if(ConstConfig::PAI_TYPE_FENG == $type)
					{
						$tmp_hu_data = &ConstConfig::$hu_data_feng;
					}

					$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
					if(isset($tmp_hu_data[$key]) && ($tmp_hu_data[$key] & 1) == 1 && ($tmp_hu_data[$key] & 32) != 32)
					{
						$is_hu_data_tmp = true;
					}

					if($type == $fanhun_type)
					{
						$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $fanhun_num;	//
						if($is_fanhun)
						{
							$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += 1;//
						}
					}

					//continue;
				}
				else
				{
					if(ConstConfig::PAI_TYPE_FENG  == $type)
					{
						$is_hu_data_tmp = false;
						if($type == $fanhun_type)
						{
							$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 0;	//去掉翻混
							if($is_fanhun)
							{
								$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 1;//打出的牌是翻混
							}
						}

						foreach (ConstConfig::$hu_data_insert_feng[$num] as $insert_arr)
						{
							foreach ($insert_arr as $insert_item)
							{
								$this->m_sPlayer[$chair]->card[$type][$insert_item] += 1;
							}
							$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
							if(isset(ConstConfig::$hu_data_feng[$key]) && (ConstConfig::$hu_data_feng[$key] & 1) == 1)
							{
								$is_hu_data_tmp = true;

								foreach ($insert_arr as $insert_item)
								{
									$this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
								}
							}
							else
							{
								foreach ($insert_arr as $insert_item)
								{
									$this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
								}
							}
						}

						//还原手牌中的翻混
						if($type == $fanhun_type)
						{
							$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $fanhun_num;	//
							if($is_fanhun)
							{
								$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += 1;//
							}
						}

						if(!$is_hu_data_tmp)
						{
							break;
						}
					}
					else
					{
						$is_hu_data_tmp = false;
						if($type == $fanhun_type)
						{
							$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 0;	//去掉翻混
							if($is_fanhun)
							{
								$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 1;//打出的牌是翻混
							}
						}

						foreach (ConstConfig::$hu_data_insert[$num] as $insert_arr)
						{
							foreach ($insert_arr as $insert_item)
							{
								$this->m_sPlayer[$chair]->card[$type][$insert_item] += 1;
							}
							$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));

							if(isset(ConstConfig::$hu_data[$key]) && (ConstConfig::$hu_data[$key] & 1) == 1)
							{
								$is_hu_data_tmp = true;
								$key_arr[$type] = $key;

								foreach ($insert_arr as $insert_item)
								{
									$this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
								}
							}
							else
							{
								foreach ($insert_arr as $insert_item)
								{
									$this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
								}
							}
						}

						//还原手牌中的翻混
						if($type == $fanhun_type)
						{
							$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $fanhun_num;	//
							if($is_fanhun)
							{
								$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += 1;//
							}
						}

						if(!$is_hu_data_tmp)
						{
							break;
						}
					}
				}
			}

			if($is_hu_data_tmp)
			{
				return true;
			}
		}

		return $return_type;
	}
}
