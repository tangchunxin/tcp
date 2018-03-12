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

class GameBaoding
{
	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21;                  // 平胡
	const HU_TYPE_SHISANYAO = 22;               // 十三幺...
	const HU_TYPE_QIDUI = 23;                   // 七对
	const HU_TYPE_HAOHUA_QIDUI = 24;            // 豪华七对....
	const HU_TYPE_CHAOJI_QIDUI = 25;            // 超级豪华七对....
	const HU_TYPE_ZHUIZUN_QIDUI = 26;           // 至尊豪华七对....
	const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   // 错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_TIANHU = 61;              // 天胡 
	const ATTACHED_HU_DIHU = 62;                // 地胡 
	const ATTACHED_HU_ZIMOFAN = 63;             // 自摸     默认
	const ATTACHED_HU_GANGKAI = 64;             // 杠开
	const ATTACHED_HU_QIANGGANG = 65;           // 抢杠

	const ATTACHED_HU_QINGYISE = 66;            // 清一色
	const ATTACHED_HU_YITIAOLONG = 67;          // 一条龙
	const ATTACHED_HU_HAIDI = 68;               // 海底捞月 默认
	const ATTACHED_HU_DA8ZHANG = 69;            // 打八张   默认 
	const ATTACHED_HU_MENQING = 70;             // 门清
	const ATTACHED_HU_ZIYISE = 72;            //

	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 3;                 // 直杠 3分
	const M_ANGANG_SCORE = 2;                  // 暗杠 2分
	const M_WANGANG_SCORE = 1;                 // 弯杠 1分

	public static $hu_type_arr = array(
	self::HU_TYPE_PINGHU=>[self::HU_TYPE_PINGHU, 1, '平胡']  //平胡
	,self::HU_TYPE_SHISANYAO=>[self::HU_TYPE_SHISANYAO, 10, '十三幺']
	,self::HU_TYPE_QIDUI=>[self::HU_TYPE_QIDUI, 2, '七对']
	,self::HU_TYPE_HAOHUA_QIDUI=>[self::HU_TYPE_HAOHUA_QIDUI, 4, '豪华七对']
	,self::HU_TYPE_CHAOJI_QIDUI=>[self::HU_TYPE_CHAOJI_QIDUI, 8, '超级豪华七对']
	,self::HU_TYPE_ZHUIZUN_QIDUI=>[self::HU_TYPE_ZHUIZUN_QIDUI, 16, '至尊豪华七对']

	);

	public static $attached_hu_arr = array(
	self::ATTACHED_HU_TIANHU=>[self::ATTACHED_HU_TIANHU, 8, '天胡']
	,self::ATTACHED_HU_DIHU=>[self::ATTACHED_HU_DIHU, 4, '地胡']
	,self::ATTACHED_HU_ZIMOFAN=>[self::ATTACHED_HU_ZIMOFAN, 0, '自摸']  //作为低分3分
	,self::ATTACHED_HU_GANGKAI=>[self::ATTACHED_HU_GANGKAI, 2, '杠上花']
	,self::ATTACHED_HU_QIANGGANG=>[self::ATTACHED_HU_QIANGGANG, 2, '抢杠']

	,self::ATTACHED_HU_QINGYISE=>[self::ATTACHED_HU_QINGYISE, 4, '清一色']
	,self::ATTACHED_HU_ZIYISE=>[self::ATTACHED_HU_ZIYISE, 4, '风一色']
	,self::ATTACHED_HU_YITIAOLONG=>[self::ATTACHED_HU_YITIAOLONG, 2, '一条龙']
	,self::ATTACHED_HU_HAIDI=>[self::ATTACHED_HU_HAIDI, 2, '海底捞月']	
	,self::ATTACHED_HU_MENQING=>[self::ATTACHED_HU_MENQING, 2, '门清']
	,self::ATTACHED_HU_DA8ZHANG=>[self::ATTACHED_HU_DA8ZHANG, 0, '打八张']
	);	


	public $serv;	                                   // socket服务器对象

	public $m_ready = array(0,0,0,0);	               // 用户准备
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
    public $m_cancle_time;                              // 第一次发起解散房间时间

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
	public $m_nHuGiveUp = array();			            // 该轮放弃胡的,m_nHuGiveUp = []: 个数

	// 与客户端无关
	public $m_nCardBuf = array();			            // 牌的缓冲区
	public $m_HuCurt = array();	                        // 胡牌信息

	public $m_nChairBanker;				                // 庄家的位置，
	public $m_nChairBankerNext = 255;				    // 下一局庄家的位置，
	public $m_nCountAllot;					            // 发到第几张牌
	public $m_nAllCardNum = ConstConfig::BASE_CARD_NUM;
	public $m_sOutedCard;			                    // 刚打出的牌
	public $m_sysPhase;				                    // 当前阶段状态
	public $m_chairCurrentPlayer;			            // 当前出牌者
	//public $m_set_end_time;	                            // 本局结束时间

	public $m_deal_card_arr = array();					//每墩发牌数组
	
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
		$this->m_game_type = 201;
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
		if($is_open || ($this->m_rule->set_num <= $this->m_nSetCount && $this->m_bLastGameOver))
		{
			$this->m_game_type = 201;	//游戏类型，见http端协议
			$this->m_room_state = ConstConfig::ROOM_STATE_OVER ;	//房间状态
			$this->m_room_id = 0;	//房间号
			$this->m_room_owner = 0;	//房主
			$this->m_room_players = array();	//玩家信息
			$this->m_start_time = 0;	//开始时间
			$this->m_nSetCount = 0;
			$this->m_nChairBankerNext = 255;
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
        $this->m_cancle_time = 0;
		$this->m_fan_hun_card = 0;	//翻混牌
		$this->m_hun_card = 0;	//混牌

		$this->m_deal_card_arr = array();

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
			$this->m_own_paozi[$i] = new Pao_zi();
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

			$this->m_HuCurt[$i] = new Hu_curt();
			$this->m_hu_desc[$i] = '';
			
			$this->m_is_ting_arr[$i] = 1;
			//$this->m_nHuList[$i] = 0;
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

			$this->_send_cmd('s_flee', array('flee_time'=>$flee_time), Game_cmd::SCO_ALL_PLAYER);
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

			if(!empty($this->m_cancle_time) && ($this->m_cancle_time + Config::CANCLE_GAME_CLOCKER_NUM - time() <= Config::CANCLE_GAME_CLOCKER_LIMIT))
			{
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
						$this->_send_cmd('s_chat', array("type"=>$params['type'], "content"=>$params['content'], "chair"=>$key, "uid"=>$params['uid']), Game_cmd::SCO_ALL_PLAYER);
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

						$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($key, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$key]['uid']);

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
			$this->m_rule = new RuleBaoDing();
			if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3, 4)))
			{
				$params['rule']['player_count'] = 4;
			}

			$params['rule']['min_fan'] = !isset($params['rule']['min_fan']) ? 0 : $params['rule']['min_fan'];
			$params['rule']['top_fan'] = !isset($params['rule']['top_fan']) ? 0 : $params['rule']['top_fan'];
			$params['rule']['is_circle'] = !isset($params['rule']['is_circle']) ? 0 : $params['rule']['is_circle'];
			//$params['rule']['set_num'] = 4;	//test

            $params['rule']['pay_type'] = !isset($params['rule']['pay_type']) ? 0 : $params['rule']['pay_type'];
			$params['rule']['is_feng'] = !isset($params['rule']['is_feng']) ? 1 : $params['rule']['is_feng'];
			$params['rule']['is_yipao_duoxiang'] = !isset($params['rule']['is_yipao_duoxiang']) ? 0 : $params['rule']['is_yipao_duoxiang'];
			$params['rule']['is_chipai'] = !isset($params['rule']['is_chipai']) ? 0 : $params['rule']['is_chipai'];
			$params['rule']['is_paozi'] = !isset($params['rule']['is_paozi']) ? 0 : $params['rule']['is_paozi'];
			$params['rule']['is_qingyise_fan'] = !isset($params['rule']['is_qingyise_fan']) ? 1 : $params['rule']['is_qingyise_fan'];
			$params['rule']['is_ziyise_fan'] = !isset($params['rule']['is_ziyise_fan']) ? 1 : $params['rule']['is_ziyise_fan'];
			$params['rule']['is_yitiaolong_fan'] = !isset($params['rule']['is_yitiaolong_fan']) ? 1 : $params['rule']['is_yitiaolong_fan'];
			$params['rule']['is_shisanyao_fan'] = !isset($params['rule']['is_shisanyao_fan']) ? 1 : $params['rule']['is_shisanyao_fan'];
			$params['rule']['is_ganghua_fan'] = !isset($params['rule']['is_ganghua_fan']) ? 1 : $params['rule']['is_ganghua_fan'];
			$params['rule']['is_qidui_fan'] = !isset($params['rule']['is_qidui_fan']) ? 1 : $params['rule']['is_qidui_fan'];
			$params['rule']['is_tiandi_hu_fan'] = !isset($params['rule']['is_tiandi_hu_fan']) ? 0 : $params['rule']['is_tiandi_hu_fan'];

			$params['rule']['is_za_hu_mian_gang'] = !isset($params['rule']['is_za_hu_mian_gang']) ? 1 : $params['rule']['is_za_hu_mian_gang'];
			$params['rule']['is_dianpao_bao'] = !isset($params['rule']['is_dianpao_bao']) ? 0 : $params['rule']['is_dianpao_bao'];
			$params['rule']['is_kou_card'] = !isset($params['rule']['is_kou_card']) ? 0 : $params['rule']['is_kou_card'];
			$params['rule']['is_kou_dajiang'] = !isset($params['rule']['is_kou_dajiang']) ? 0 : $params['rule']['is_kou_dajiang'];
			$params['rule']['is_wangang_1_lose'] = !isset($params['rule']['is_wangang_1_lose']) ? 0 : $params['rule']['is_wangang_1_lose'];
			$params['rule']['is_menqing_fan'] = !isset($params['rule']['is_menqing_fan']) ? 0 : $params['rule']['is_menqing_fan'];
			$params['rule']['is_zhuang_fan'] = !isset($params['rule']['is_zhuang_fan']) ? 1 : $params['rule']['is_zhuang_fan'];

			$this->m_rule->game_type = $params['rule']['game_type'];
			$this->m_rule->player_count = $params['rule']['player_count'];
			$this->m_rule->min_fan = $params['rule']['min_fan'];
			$this->m_rule->top_fan = $params['rule']['top_fan'];
            $this->m_rule->pay_type = $params['rule']['pay_type'];
			$this->m_rule->is_circle = $params['rule']['is_circle'];
			if(!empty($this->m_rule->is_circle))
			{
				$this->m_rule->set_num = $this->m_rule->is_circle * $this->m_rule->player_count;		//局等于  人*圈					
			}
			else
			{
				$this->m_rule->set_num = $params['rule']['set_num'];
			}

			$this->m_rule->is_feng = $params['rule']['is_feng'];
			$this->m_rule->is_yipao_duoxiang = $params['rule']['is_yipao_duoxiang'];
			$this->m_rule->is_fanhun = $params['rule']['is_fanhun'];
			$this->m_rule->is_chipai = $params['rule']['is_chipai'];
			$this->m_rule->is_genzhuang = $params['rule']['is_genzhuang'];
			$this->m_rule->is_da8zhang = $params['rule']['is_da8zhang'];
			$this->m_rule->is_paozi = $params['rule']['is_paozi'];
			$this->m_rule->is_za_hu_mian_gang = $params['rule']['is_za_hu_mian_gang'];  //默认规则 客户端可不传此 参数

			$this->m_rule->is_qingyise_fan = $params['rule']['is_qingyise_fan'];
			$this->m_rule->is_ziyise_fan = $params['rule']['is_ziyise_fan'];
			$this->m_rule->is_yitiaolong_fan = $params['rule']['is_yitiaolong_fan'];
			$this->m_rule->is_shisanyao_fan = $params['rule']['is_shisanyao_fan'];
			$this->m_rule->is_ganghua_fan = $params['rule']['is_ganghua_fan'];
			$this->m_rule->is_qidui_fan = $params['rule']['is_qidui_fan'];
			$this->m_rule->is_tiandi_hu_fan = $params['rule']['is_tiandi_hu_fan'];
			$this->m_rule->is_dianpao_bao = $params['rule']['is_dianpao_bao'];
			$this->m_rule->is_kou_card = $params['rule']['is_kou_card'];
			$this->m_rule->is_kou_dajiang = $params['rule']['is_kou_dajiang'];
			$this->m_rule->is_wangang_1_lose = $params['rule']['is_wangang_1_lose'];
			$this->m_rule->is_menqing_fan = $params['rule']['is_menqing_fan'];
			$this->m_rule->is_zhuang_fan = $params['rule']['is_zhuang_fan'];
			
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
			$this->_send_cmd('s_join_room', array('m_room_players'=>$this->m_room_players, 'm_ready'=>$this->m_ready), Game_cmd::SCO_ALL_PLAYER);
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
				$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($u_key,true), Game_cmd::SCO_SINGLE_PLAYER , $params['uid']);
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
				$this->_send_cmd('s_ready', array('base_player_count'=>$this->m_rule->player_count, 'm_room_players'=>$this->m_room_players, 'm_ready'=>$this->m_ready, 'm_nSetCount'=>$this->m_nSetCount, 'm_wTotalScore'=>$this->m_wTotalScore), Game_cmd::SCO_ALL_PLAYER);
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
						if($this->_is_clocker() && (Config::CANCLE_GAME_CLOCKER == 1))
						{
							$this->m_cancle_time = time();
						}
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

	//下炮子
	public function c_pao_zi($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| !isset($params['pao_zi_num'])
			|| !in_array($params['pao_zi_num'], array(0, 1, 2, 3, 4 ))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_XIA_PAO || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if ($this->m_own_paozi[$key]->recv)
					{
						$return_send['code'] = 4; $return_send['text'] = '您已经下炮子了'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->handle_pao_zi($key, $params['pao_zi_num']);
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

	//扣四
	public function c_kou_card($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| !isset($params['yes'])
			|| !in_array($params['yes'], array(1, 2))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_KOU_CARD || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					// if ($this->m_own_paozi[$key]->recv)
					// {
					// 	$return_send['code'] = 4; $return_send['text'] = '您扣牌结束了'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					// }
					$this->handle_kou_card($key, $params['yes']);
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

	public function handle_pao_zi($chair, $pao_zi_num)
	{
		$this->m_own_paozi[$chair]->recv = true;
		$this->m_own_paozi[$chair]->num = $pao_zi_num;

		$tmp_paozi_arr = [0, 0, 0, 0];
		for ($i = 0; $i<$this->m_rule->player_count; ++$i)
		{
			$tmp_paozi_arr[$i] = $this->m_own_paozi[$i]->num;
			if (!$this->m_own_paozi[$i]->recv)
			{
				break;
			}
		}

		//开始牌局
		if ($this->m_rule->player_count == $i)
		{
			$this->_set_record_game(ConstConfig::RECORD_PAOZI, $tmp_paozi_arr[0], $tmp_paozi_arr[1], $tmp_paozi_arr[2], $tmp_paozi_arr[3]);

			$this->DealAllCardEx();

			//扣四
			if(!empty($this->m_rule->is_kou_card))
			{
				$this->start_kou_card();
				return true;
			}

			$this->game_to_playing();

			return true;
		}
	}

	public function handle_kou_card($chair, $yes)
	{
		$is_act = false;
		$act_n =	 0;
		$send = true;
		for ($n=0; $n <= 2; $n++)
		{
			$tmp_start = $n * 4;
			for ($i = 0; $i<$this->m_rule->player_count; ++$i)
			{
				if($this->m_sPlayer[$i]->kou_card[$tmp_start + 3][1] == 0)
				{
					if($chair == $i && !$is_act)
					{
						$this->m_sPlayer[$i]->kou_card[$tmp_start][1] = $yes;
						$this->m_sPlayer[$i]->kou_card[$tmp_start + 1][1] = $yes;
						$this->m_sPlayer[$i]->kou_card[$tmp_start + 2][1] = $yes;
						$this->m_sPlayer[$i]->kou_card[$tmp_start + 3][1] = $yes;
						$is_act = true;
						$act_n = $n;
					}
					else
					{
						$send = false;
					}
				}
			}

			for ($j = 0; $j<$this->m_rule->player_count; ++$j)
			{
				if($send && $n < 2 && $this->m_sPlayer[$j]->kou_card[$tmp_start + 3][1] == 2 && $this->m_sPlayer[$j]->kou_card[$tmp_start + 7][1] == 0)
				{
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 4][1] = 2;
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 5][1] = 2;
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 6][1] = 2;
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 7][1] = 2;
				}
			}

			if(!$send)
			{
				break;
			}
			else
			{
				$act_n = $n;
			}

			if($n >= 2)
			{
				break;
			}
		}

		if($n > $act_n)
		{
			$this->start_kou_card();
			return true;
		}
		else if($send)
		{
			$this->game_to_playing();
			return true;
		}
		
		return true;
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
						$return_send['code'] = 4; $return_send['text'] = '当前用户错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->_clear_choose_buf($key);
					if(empty($this->m_rule->is_kou_card) && empty($params['is_14']) && 0 == $this->_list_find($key,$params['out_card']))
					{
						$return_send['code'] = 5; $return_send['text'] = '出牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if(!empty($this->m_rule->is_kou_card) && empty($params['is_14']) )
					{
						$ac = array_count_values($this->m_sPlayer[$key]->kou_card_display);
						if(!empty($ac[$params['out_card']]) && $this->_list_find($key,$params['out_card']) <= $ac[$params['out_card']])
						{
							$return_send['code'] = 5; $return_send['text'] = '出牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
						else if(empty($ac[$params['out_card']]) && $this->_list_find($key,$params['out_card']) <= 0)
						{
							$return_send['code'] = 5; $return_send['text'] = '出牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
					}
					$tmp_card = 0;
					if(!empty($params['is_14']))
					{
						$tmp_card = $this->m_sPlayer[$key]->card_taken_now;
					}
					else if(!empty($params['out_card']))
					{
						$tmp_card = $params['out_card'];
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
					if(empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || 2 > $this->_list_find($key,$this->m_sOutedCard->card))
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
					if(empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || $this->m_sOutedCard->chair != $this->_anti_clock($key,-1))
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
						if( self::is_hu_give_up($temp_card, $this->m_nHuGiveUp[$last_chair]) || !$this->judge_hu($last_chair,$is_fanhun))
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
		$is_ziyise = false;
		$is_yitiaolong = false;
		$hu_type = $this->judge_hu_type_fanhun($chair, $is_qingyise, $is_yitiaolong, $is_ziyise, $is_fanhun);

		if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
		{
			return false;
		}
		
		//记录在全局数据
		$this->m_HuCurt[$chair]->method[0] = $hu_type;
		$this->m_HuCurt[$chair]->count = 1;

		//天地胡处理
		if($this->m_rule->is_tiandi_hu_fan)
		{
			if($this->m_bTianRenHu)
			{
				if($chair == $this->m_nChairBanker)
				{
					$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_TIANHU);
				}
				else
				{
					$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_DIHU);
				}
			}
			else if(0 == $this->m_nDiHu[$chair])
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_DIHU);
			}
		}

		//自摸加番
		// if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		// {
		// 	$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZIMOFAN);
		// }

		//抢杠杠开杠炮
		if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)	// 处理抢杠
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
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

		//字一色
		if($is_ziyise && !empty($this->m_rule->is_ziyise_fan))
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZIYISE);			
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

		//门清
		if(!empty($this->m_rule->is_menqing_fan) && $this->_is_menqing($chair) )
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_MENQING);
		}

		return true;			
	}

	//判断基本牌型+附加牌型+庄分
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

		$attached_fan_sum = 1;
		for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
		{
			if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
			{
				$attached_fan_sum *= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
				$tmp_hu_desc .= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
			}
		}

		//保定附加番的时候不计平胡
		if($hu_type == self::HU_TYPE_PINGHU)
		{
			$fan_sum = ($attached_fan_sum > 1) ? $attached_fan_sum : $fan_sum * $attached_fan_sum;
		}
		else
		{
			$fan_sum = $attached_fan_sum * $fan_sum;
		}
		
		//$fan_sum *= 3;

		//扣四
		if(!empty($this->m_rule->is_kou_card))
		{
			$tmp_times = 1;
			if($this->m_sPlayer[$chair]->kou_card[11][1] == 1 || $this->m_sPlayer[$chair]->kou_card[11][1] == 3)
			{
				$tmp_times = 8;
				$tmp_hu_desc .= '扣 扣 扣 ';
			}
			else if($this->m_sPlayer[$chair]->kou_card[7][1] == 1 || $this->m_sPlayer[$chair]->kou_card[7][1] == 3)
			{
				$tmp_times = 4;
				$tmp_hu_desc .= '扣 扣 ';
			}
			else if($this->m_sPlayer[$chair]->kou_card[3][1] == 1 || $this->m_sPlayer[$chair]->kou_card[3][1] == 3)
			{
				$tmp_times = 2;
				$tmp_hu_desc .= '扣 ';
			}
			else
			{
				$tmp_times = 1;
			}

			$dajiang_num_all = 0;
			if(!empty($this->m_rule->is_kou_dajiang) && $tmp_times >= 2)
			{
				//计算扣大将
				for ($i=0; $i < 3; $i++)
				{ 
					$tmp_dajiang = [0, 0, 0, 0, 0, 0, 0, 0];
					for ($j=0; $j < 4; $j++)
					{ 
						if($this->_get_card_type($this->m_sPlayer[$chair]->kou_card[$i * 4 + $j][0]) == ConstConfig::PAI_TYPE_FENG
							&& ($this->m_sPlayer[$chair]->kou_card[$i * 4 + $j][1] == 1 || $this->m_sPlayer[$chair]->kou_card[$i * 4 + $j][1] == 3)
						)
						{
							$tmp_dajiang[$this->m_sPlayer[$chair]->kou_card[$i * 4 + $j][0] % 16] += 1;
						}
					}
					$dajiang_num = array_count_values($tmp_dajiang);
					if(!empty($dajiang_num[1]))
					{
						$dajiang_num_all += $dajiang_num[1];
					}
				}
				if($dajiang_num_all > 0)
				{
					$tmp_times *= pow(2, $dajiang_num_all);
					$tmp_hu_desc .= '扣大将×'.$dajiang_num_all.' ';					
				}
			}

			$fan_sum *= $tmp_times;
		}

		$fan_sum = $this->_get_max_fan($fan_sum);
		
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

	//判断翻混 
	public function judge_hu_type_fanhun($chair, &$is_qingyise, &$is_yitiaolong, &$is_ziyise, $is_fanhun = false)
	{
		$is_quemen = true;
		$fanhun_num = 0;
		$fanhun_type = 255;
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
			return $this->judge_hu_type($chair, $is_qingyise, $is_yitiaolong, $is_ziyise, $is_quemen);
		}
		else
		{
			$return_type = self::HU_TYPE_FENGDING_TYPE_INVALID;	
			$is_da8zhang = false;
	
			//十三幺牌型，不计缺门
			if(!empty($this->m_rule->is_shisanyao_fan) && !empty($this->m_rule->is_feng))
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
								if($this->m_sPlayer[$chair]->card[$i][$j] > 2)
								{
									$is_shisanyao = false; break 2;
								}
								elseif($this->m_sPlayer[$chair]->card[$i][$j] == 2)
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
				$gen_count_num = 0;
				$da8zhang_replace_fanhun = array(0,0,0,0);
			 
				if($this->m_sStandCard[$chair]->num == 0)
				{	
					//去掉翻混
					$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;	

					for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
					{
						if(0 == $this->m_sPlayer[$chair]->card[$i][0] || (0 == ($this->m_sPlayer[$chair]->card[$i][0] - $fanhun_num) && $i == $fanhun_type && $this->m_sPlayer[$chair]->len > $fanhun_num))
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

			for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
			{
				if(0 == $this->m_sPlayer[$chair]->card[$i][0] || (0 == $this->m_sPlayer[$chair]->card[$i][0]-$fanhun_num && $i == $fanhun_type && $this->m_sPlayer[$chair]->len > $fanhun_num))
				{
					if(!in_array($i, $qing_arr_stand))
					{
						continue;
					}
				}

				$is_qingyise = false;
				$is_ziyise = false;
				$is_da8zhang = false;
				$yitiaolong_tmp = false;
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
						if(!in_array($j, $qing_arr_stand))
						{
							continue;
						}
					}

					$pai_num = $this->m_sPlayer[$chair]->card[$j][0];	//一门牌个数
					$pai_num = ($j == $fanhun_type) ? $pai_num - $fanhun_num : $pai_num;	//混牌的牌型个数得减去混牌个数

					if($pai_num > 0)
					{
						$qing_arr[] = $j;
					}

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

					if($replace_fanhun[$j] > 0)
					{
						$qing_arr[] = $j;
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

						if(0 == $this->m_sPlayer[$chair]->card[$j][0] || ($this->m_sPlayer[$chair]->card[$j][0] == $fanhun_num && $j == $fanhun_type && $this->m_sPlayer[$chair]->len > $fanhun_num))
						{
							if(!in_array($j, $qing_arr_stand))
							{
								continue;
							}
						}
						if($fanhun_num == $need_fanhun && $is_check_hu)
						{
							continue;
						}
						$is_check_hu = true;

						$tmp_replace_fanhun = $replace_fanhun;
						$tmp_replace_fanhun[$j] += ($fanhun_num - $need_fanhun);
						
						if($tmp_replace_fanhun[$j] > 0)
						{
							$qing_arr[] = $j;
						}

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

							$tmp_type_hu_num = 0;
							$tmp_is_big = false;
							$is_hu_data = false;
							$insert_pengpeng_arr = $max_type_hu_arr[1];
							$insert_yitiaolong = $max_type_hu_arr[2];
							foreach ($tmp_hu_data_insert[$num] as $insert_arr)
							{
								foreach ($insert_arr as $insert_item)
								{
									$this->m_sPlayer[$chair]->card[$type][$insert_item] += 1;
								}
								$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
								if(isset($tmp_hu_data[$key]) && ($tmp_hu_data[$key] & 1) == 1)
								{
									$is_hu_data = true;
									$tmp_type_hu_num = 1;

									foreach ($insert_arr as $insert_item)
									{
										$this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
									}
									//碰碰胡和一条龙不会同时存在
									$tmp_pengpeng = $tmp_hu_data[$key] & 8;
									if($tmp_pengpeng && !array_keys($insert_pengpeng_arr, 0))
									{
										$tmp_type_hu_num += 1;
									}

									$tmp_type_yitiaolong = ($insert_yitiaolong || !empty($this->m_rule->is_yitiaolong_fan) && ($tmp_hu_data[$key] & 256) == 256);
									if($tmp_type_yitiaolong)
									{
										$tmp_type_hu_num += 2;
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
									}
									if($tmp_type_hu_num >= 4)
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

						if($max_type_hu_arr[0] > 0)
						{
							$tmp_max_hu = self::$hu_type_arr[self::HU_TYPE_PINGHU][1];
							if(!empty($this->m_rule->is_yitiaolong_fan) && $max_type_hu_arr[2])
							{
								$tmp_max_hu += self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
							}
							if(!empty($this->m_rule->is_pengpenghu_fan) && !array_keys($max_type_hu_arr[1], 0))
							{
								$tmp_max_hu += self::$hu_type_arr[self::HU_TYPE_PENGPENGHU][1];
							}

							if(!empty($this->m_rule->is_qingyise_fan) || !empty($this->m_rule->is_ziyise_fan))
							{
								$tmp_is_qingyise = false;
								$tmp_is_ziyise = false;
								$this->_is_yise($qing_arr, $tmp_is_qingyise, $tmp_is_ziyise);

								if(!empty($this->m_rule->is_qingyise_fan) && $tmp_is_qingyise)
								{
									$tmp_max_hu += self::$attached_hu_arr[self::ATTACHED_HU_QINGYISE][1];
								}
								if(!empty($this->m_rule->is_ziyise_fan) && $tmp_is_ziyise)
								{
									$tmp_max_hu += self::$attached_hu_arr[self::ATTACHED_HU_ZIYISE][1];
								}
							}

							if($tmp_max_hu > $max_hu[0])
							{
								$max_hu[0] = $tmp_max_hu;
								$max_hu[2] = $max_type_hu_arr[2];
								$max_hu[1] = $max_type_hu_arr[1];
							}
						}

						if($max_hu[0] > self::$hu_type_arr[self::HU_TYPE_PINGHU][1] + self::$attached_hu_arr[self::ATTACHED_HU_QINGYISE][1])
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

			if($max_hu[0] >= 0)
			{
				$this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);

				if(!empty($this->m_rule->is_yitiaolong_fan) && $max_hu[2])
				{
					$is_yitiaolong = $max_hu[2];
				}
				if(!empty($this->m_rule->is_pengpenghu_fan) && !empty($max_hu[1]) && !array_keys($max_hu[1], 0))
				{
					return self::HU_TYPE_PENGPENGHU;
				}
				return self::HU_TYPE_PINGHU;
			}
			return $return_type;
		}
	}

	//胡牌类型判断  没有混的情况
	public function judge_hu_type($chair, &$is_qingyise, &$is_yitiaolong, &$is_ziyise, $is_quemen)
	{
		$jiang_arr = array();
		$qidui_arr = array();
		$qing_arr = array();
		$shisanyao_arr = array();
		$pengpeng_arr = array();
		$gen_arr = array();
		
		$bType32 = false;
		$bQiDui = false;
		$bPengPeng = false;  
		$bShiSanYao = false;    //13幺

		$is_qingyise = false;
		$is_ziyise = false;
		$is_yitiaolong = false;   //一条龙
		$is_da8zhang = false;

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
				//return self::HU_TYPE_FENGDING_TYPE_INVALID ;
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
				if($this->m_rule->is_yitiaolong_fan && ($hu_list_val & 256) == 256)//一条龙
				{
					$is_yitiaolong = true;
				}
				$pengpeng_arr[] = $hu_list_val & 8;
				$qidui_arr[] = $hu_list_val & 64;
				$shisanyao_arr[] = $hu_list_val & 128;
				$gen_arr[] = intval($hu_list_val/4096);

				if(($hu_list_val & 1) == 1)
				{
					$jiang_arr[] = $hu_list_val & 32;
				}
				else
				{
					//非32牌型设置
					$jiang_arr[] = 32; $jiang_arr[] = 32;
				}
				$qing_arr[] = $i;
			}
		}

		//倒牌
		for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
		{
			$stand_pai_type = $this->_get_card_type($this->m_sStandCard[$chair]->first_card[$i]);
			$stand_pai_key = $this->m_sStandCard[$chair]->first_card[$i] % 16;
			
			$qidui_arr[] = 0;
			$shisanyao_arr[] = 0;
			$qing_arr[] = $stand_pai_type;

			if(ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i])
			{
				$pengpeng_arr[] = 0;
			}

			if(ConstConfig::DAO_PAI_TYPE_KE == $this->m_sStandCard[$chair]->type[$i] && $this->m_sPlayer[$chair]->card[$stand_pai_type][$stand_pai_key] > 0)
			{
				//手牌，倒牌组合根
				$gen_arr[] = 1;
			}
		}

		$bType32 = (32 == array_sum($jiang_arr));
		$bQiDui = !array_keys($qidui_arr, 0);
		$bPengPeng = !array_keys($pengpeng_arr, 0);
		$bShiSanYao = !array_keys($shisanyao_arr, 0);

		/////////////////////////////附加 番型的处理/////////////////////////////////
		//一色结果
		$this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);

		//一条龙前面处理过
		//
		//打八张判断
		if(!empty($this->m_rule->is_da8zhang))
		{
			$is_da8zhang = $this->_judge_da8zhang($chair,null,false,true);
		}

		//13幺
		if($this->m_rule->is_shisanyao_fan && $this->m_rule->is_feng && $bShiSanYao)
		{
			$is_da8zhang = true;  //13幺  免疫打八张
			$is_quemen = true;	//13幺，不计缺门
		}

		if(!empty($this->m_rule->is_da8zhang) && !$is_da8zhang)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}

		if(!empty($this->m_rule->is_quemen) && !$is_quemen)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}

		//基本牌型的处理///////////////////////////////
		if(!$bType32 && !$bQiDui && !$bShiSanYao)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}

		if($this->m_rule->is_shisanyao_fan && $this->m_rule->is_feng && $bShiSanYao)
		{
			return self::HU_TYPE_SHISANYAO;
		}

		if($bQiDui && $this->m_rule->is_qidui_fan )				//判断七对，可能同时是32牌型
		{
			
			if(array_sum($gen_arr) >= 3)
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

			return self::HU_TYPE_QIDUI ;				
		}

		if($bType32)
		{
			if(!empty($this->m_rule->is_pengpenghu_fan) && $bPengPeng)
			{
				return self::HU_TYPE_PENGPENGHU;
			}
			return self::HU_TYPE_PINGHU;
		}

		return self::HU_TYPE_FENGDING_TYPE_INVALID;
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

		//扣牌
		if(!empty($this->m_rule->is_kou_card))
		{
			$tmp_times = 0;
			for ($i=0; $i < 12; $i++)
			{ 
				if($this->m_sPlayer[$chair]->kou_card[$i][0] == $temp_card && $this->m_sPlayer[$chair]->kou_card[$i][1] == 1)
				{
					$this->m_sPlayer[$chair]->kou_card[$i][1] = 3;
					$tmp_times++;
				}
				if($tmp_times >= 2)
				{
					break;
				}
			}
			$this->m_sPlayer[$chair]->kou_card_display = $this->_set_kou_arr($chair, true);
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
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
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

		if(!empty($this->m_rule->is_kou_card))
		{
			$second_act = false;
			$third_act = false;
			for ($i=0; $i < 12; $i++)
			{ 
				if(!$second_act && $this->m_sPlayer[$chair]->kou_card[$i][0] == $del_card_second_tmp && $this->m_sPlayer[$chair]->kou_card[$i][1] == 1)
				{
					$this->m_sPlayer[$chair]->kou_card[$i][1] = 3;
					$second_act = true;
				}
				if(!$third_act && $this->m_sPlayer[$chair]->kou_card[$i][0] == $del_card_third_tmp && $this->m_sPlayer[$chair]->kou_card[$i][1] == 1)
				{
					$this->m_sPlayer[$chair]->kou_card[$i][1] = 3;
					$third_act = true;
				}
			}
			$this->m_sPlayer[$chair]->kou_card_display = $this->_set_kou_arr($chair, true);
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
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
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

		//扣牌
		if(!empty($this->m_rule->is_kou_card))
		{
			$tmp_times = 0;
			for ($i=0; $i < 12; $i++)
			{ 
				if($this->m_sPlayer[$chair]->kou_card[$i][0] == $temp_card && $this->m_sPlayer[$chair]->kou_card[$i][1] == 1)
				{
					$this->m_sPlayer[$chair]->kou_card[$i][1] = 3;
					$tmp_times++;
				}
				if($tmp_times >= 4)
				{
					break;
				}
			}
			$this->m_sPlayer[$chair]->kou_card_display = $this->_set_kou_arr($chair, true);
		}

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
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
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

		//扣牌
		if(!empty($this->m_rule->is_kou_card))
		{
			$tmp_times = 0;
			for ($i=0; $i < 12; $i++)
			{ 
				if($this->m_sPlayer[$chair]->kou_card[$i][0] == $temp_card && $this->m_sPlayer[$chair]->kou_card[$i][1] == 1)
				{
					$this->m_sPlayer[$chair]->kou_card[$i][1] = 3;
					$tmp_times++;
				}
				if($tmp_times >= 3)
				{
					break;
				}
			}
			$this->m_sPlayer[$chair]->kou_card_display = $this->_set_kou_arr($chair, true);
		}

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
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
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

		//扣牌
		if(!empty($this->m_rule->is_kou_card))
		{
			$tmp_times = 0;
			for ($i=0; $i < 12; $i++)
			{ 
				if($this->m_sPlayer[$chair]->kou_card[$i][0] == $temp_card && $this->m_sPlayer[$chair]->kou_card[$i][1] == 1)
				{
					$this->m_sPlayer[$chair]->kou_card[$i][1] = 3;
					$tmp_times++;
				}
				if($tmp_times >= 1)
				{
					break;
				}
			}
			$this->m_sPlayer[$chair]->kou_card_display = $this->_set_kou_arr($chair, true);
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
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
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

				//if ($this->m_game_type == 201)
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
						//$this->m_nChairBankerNext = $chair;
						if(!empty($this->m_rule->is_circle) && $this->m_nChairBanker != $chair)
						{
							$this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker,1);								
						}
						else
						{
							$this->m_nChairBankerNext = $chair;
						}
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
					$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
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

		$tmp_arr = [];
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

			$tmp_distance = $this->_chair_to($chair, $chair_next);

			$this->m_bChooseBuf[$chair_next] = 1;
			$this->m_sPlayer[$chair_next]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
			$bHaveCmd = 1;

			/////////////
			if($this->_find_peng($chair_next) 
			 ||	$this->_find_zhi_gang($chair_next) 
			 || (1 == $tmp_distance && !empty($this->m_rule->is_chipai) && ($this->_find_eat($chair_next,1) || $this->_find_eat($chair_next,2) || $this->_find_eat($chair_next,3)))
			 )
			{
				$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair_next), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair_next]['uid']);
			}
			else
			{
				$is_fanhun = false;
				if($this->m_sOutedCard->card == $this->m_hun_card)
				{
					$is_fanhun = true;
				}				
				//判断是否有胡
				$this->_list_insert($chair_next, $this->m_sOutedCard->card);
				$this->m_HuCurt[$chair_next]->card = $this->m_sOutedCard->card;
				$tmp_c_hu_result = ( $this->m_is_ting_arr[$chair_next] && !(self::is_hu_give_up($this->m_sOutedCard->card, $this->m_nHuGiveUp[$chair_next])) && $this->judge_hu($chair_next, $is_fanhun));
				$this->m_HuCurt[$chair_next]->clear();
				$this->_list_delete($chair_next, $this->m_sOutedCard->card);				
				
				if($tmp_c_hu_result)
				{
				}
				else
				{
					$this->m_sPlayer[$chair_next]->state = ConstConfig::PLAYER_STATUS_WAITING;
					$tmp_arr[] = $chair_next;
				}
				$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair_next), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair_next]['uid']);
			}
		}

		$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair]['uid']);

		foreach ($tmp_arr as $val_next_chair)
		{
			$tmp_c_act = "c_cancle_choice";
			$this->_clear_choose_buf($val_next_chair, false);
			$this->m_sPlayer[$val_next_chair]->state = ConstConfig::PLAYER_STATUS_WAITING;
			$this->HandleChooseResult($val_next_chair, $tmp_c_act);
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

				//if ($this->m_game_type == 201)
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
				if(!empty($this->m_rule->is_wangang_1_lose))
				{
					//弯杠 扣 点碰玩家分数
					for ($i = 0; $i < $this->m_sStandCard[$this->m_sQiangGang->chair]->num; $i ++)
					{
						if ($this->m_sStandCard[$this->m_sQiangGang->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
						&& $this->m_sStandCard[$this->m_sQiangGang->chair]->card[$i] == $this->m_sQiangGang->card)
						{
							$nGangScore = self::M_WANGANG_SCORE *ConstConfig::SCORE_BASE;

							$tmp_who_give_me = $this->m_sStandCard[$this->m_sQiangGang->chair]->who_give_me[$i];
							//$this->m_wGFXYScore[$tmp_who_give_me] = -$nGangScore;
							$this->m_wGangScore[$tmp_who_give_me][$tmp_who_give_me] -= $nGangScore;

							//$this->m_wGFXYScore[$this->m_sQiangGang->chair] += $nGangScore;
							$this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
							$this->m_wGangScore[$this->m_sQiangGang->chair][$tmp_who_give_me] += $nGangScore;

							$nGangPao += $nGangScore;
							break;
						}

					}
				}
				else
				{
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
					$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
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

				//if ($this->m_game_type == 201)
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
						$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
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
						//if ($this->m_game_type == 201)
						{
							$this->m_bChairHu[$hu_chair] = true;
							$this->m_bChairHu_order[] = $hu_chair;
							$this->m_nCountHu++;
							$this->m_sPlayer[$hu_chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;
						}
						$this->_send_act($this->m_currentCmd, $hu_chair);
					
						if($hu_chair == $this->m_nChairBanker)	//下一局庄家
						{
							$this->m_nChairBankerNext = $hu_chair;
						}
						else if(255 == $this->m_nChairBankerNext)
						{
							if(!empty($this->m_rule->is_circle))
							{
								$this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker,1);
							}
							else
							{
								$this->m_nChairBankerNext = $hu_chair;
							}
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
			$data['m_own_paozi'] = $this->m_own_paozi;
			
			$data['m_wTotalScore'] = $this->m_wTotalScore;
			$data['m_ready'] = $this->m_ready;
			$data['is_cancle'] = $this->m_cancle;
			$data['m_cancle'] = $this->m_cancle;
			$data['m_cancle_first'] = $this->m_cancle_first;

			$data['m_fan_hun_card'] = $this->m_fan_hun_card;		
			$data['m_hun_card'] = $this->m_hun_card;		
		}
		
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
		if(!empty($this->m_cancle_time))
		{
			$data['m_cancle_time'] = $this->m_cancle_time + Config::CANCLE_GAME_CLOCKER_NUM - time(); 
		}

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

		//扣四阶段
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_KOU_CARD)
		{
			for ($i=0; $i<$this->m_rule->player_count; $i++)
			{
				if($i == $chair)
				{
					$data['kou_card'][$i] = $this->_sub_kou_arr($i);
					$data['kou_card_display'][$i] = $this->m_sPlayer[$i]->kou_card_display;
				}
				else
				{
					$data['kou_card'][$i] = array();
					$data['kou_card_display'][$i] = $this->_set_kou_arr($i, false);
				}
			}
			return $data;
		}
		
		if($this->m_sysPhase == ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD || $this->m_sysPhase == ConstConfig::SYSTEMPHASE_CHOOSING)
		{
			$data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;								// 当前出牌者
			$data['m_nNumTableCards'] = $this->m_nNumTableCards;		// 玩家桌面牌数量
			$data['m_nTableCards'] = $this->m_nTableCards;	// 玩家桌面牌
			$data['m_sStandCard'] = $this->m_sStandCard;		// 玩家倒牌
			$data['m_sOutedCard'] = $this->m_sOutedCard;		//刚出的牌

			for ($i=0; $i<$this->m_rule->player_count; $i++)
			{
				if($i == $chair)
				{
					$data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
					$data['m_bChooseBuf'] = $this->m_bChooseBuf[$i];			 //命令缓冲
					$data['m_nHuGiveUp'] = $this->m_nHuGiveUp[$i];
					$data['m_only_out_card'] = $this->m_only_out_card[$i];	

					$data['kou_card'][$i] = $this->m_sPlayer[$i]->kou_card;
					$data['kou_card_display'][$i] = $this->m_sPlayer[$i]->kou_card_display;
				}
				else
				{
					$data['m_sPlayer'][$i] = (object)null;
					
					$data['kou_card'][$i] = array();
					$data['kou_card_display'][$i] = $this->_set_kou_arr($i, false);
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

			for ($i=0; $i<$this->m_rule->player_count; $i++)
			{
				$data['kou_card'][$i] = $this->m_sPlayer[$i]->kou_card;
				$data['kou_card_display'][$i] = $this->m_sPlayer[$i]->kou_card_display;
			}

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
			//$this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker, 1);
			$this->m_nChairBankerNext = $this->m_nChairBanker;
		}

		//准备状态
		$this->m_ready = array(0,0,0,0);

		//本局结束时间
		$this->m_end_time = date('Y-m-d H:i:s', time());

		//写记录
		$this->WriteScore();
		
		//最后一局结束时候修改房间状态
		if(empty($this->m_rule) || $this->m_rule->set_num <= $this->m_nSetCount )
		{
			if(empty($this->m_rule->is_circle) || ($this->m_nChairBanker != $this->m_nChairBankerNext))
			{
				$this->m_room_state = ConstConfig::ROOM_STATE_OVER;	
				$this->m_bLastGameOver = 1;
			}
		}

		//状态变化发消息
		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_game_over', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		$this->_set_game_and_checkout();

		// if(empty($this->m_rule) || $this->m_rule->set_num <= $this->m_nSetCount)
		// {
		// 	$this->m_room_state = ConstConfig::ROOM_STATE_OVER;
		// }
	}

	///////////////////////得分处理///////////////////////////
	//每局个人  +=赢的分  +=输的分  +=庄家 的分
	public function ScoreOneHuCal($chair, &$lost_chair)  
	{
		$fan_sum = $this->judge_fan($chair);  //这个就是  一共多少分
		// if($fan_sum < $this->m_rule->min_fan)
		// {
		// 	$this->m_HuCurt[$chair]->clear();
		// 	return false;
		// }
		$PerWinScore = $fan_sum;	

		$wWinScore = 0;

		$this->m_wHuScore = [0,0,0,0];

		if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		{
			for($i = 0; $i < $this->m_rule->player_count; $i++)
			{
				if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
				{
					continue;	//单用户测试需要关掉
				}

				$banker_fan = 1;
				if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
				{
					$banker_fan = 2;
				}

				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
				$wWinScore = 0;
				$wWinScore += 2 * ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan ;  //自摸翻倍了

				//自摸下跑分翻倍
				$wWinPaoZi = 2*($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$i]->num);
				$this->m_paozi_score[$chair] += $wWinPaoZi;
				$this->m_paozi_score[$i] -= $wWinPaoZi;
				
				$wWinScore = $this->_get_max_fan($wWinScore);

				$this->m_wHuScore[$i] -= $wWinScore;
				$this->m_wHuScore[$chair] += $wWinScore;

				$this->m_wSetLoseScore[$i] -= $wWinScore;
				$this->m_wSetScore[$chair] += $wWinScore;

				$this->m_HuCurt[$chair]->gain_chair[0]++;
				$this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $i;
			}
			return true;
		}
		// 吃炮者算分在此处！！
		else if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
		{
			//砸胡免杠
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

			//点炮大包
			if(!empty($this->m_rule->is_dianpao_bao) && $this->m_rule->is_dianpao_bao == 1)
			{
				$tmp_win_score = 0;
				for($i = 0; $i < $this->m_rule->player_count; $i++)
				{
					if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
					{
						continue;	//单用户测试需要关掉
					}

					$banker_fan = 1;
					if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
					{
						$banker_fan = 2;
					}

					$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
					$wWinScore = 0;
					$wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;

					if($lost_chair == $i)
					{
						$wWinScore *= 2;	//点炮加倍
						$wWinPaoZi = ($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$i]->num);
						$this->m_paozi_score[$chair] += $wWinPaoZi;
						$this->m_paozi_score[$lost_chair] -= $wWinPaoZi;
					}

					$tmp_win_score += $wWinScore;

					$this->m_HuCurt[$chair]->gain_chair[0]++;
					$this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $lost_chair;
				}

				$tmp_win_score = $this->_get_max_fan($tmp_win_score);

				$this->m_wHuScore[$lost_chair] -= $tmp_win_score;
				$this->m_wHuScore[$chair] += $tmp_win_score;

				$this->m_wSetLoseScore[$lost_chair] -= $tmp_win_score;
				$this->m_wSetScore[$chair] += $tmp_win_score;

			}
			else if(!empty($this->m_rule->is_dianpao_bao) && $this->m_rule->is_dianpao_bao == 2)
			{
				//点炮三家出
				for($i = 0; $i < $this->m_rule->player_count; $i++)
				{
					if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
					{
						continue;	//单用户测试需要关掉
					}

					$banker_fan = 1;
					if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
					{
						$banker_fan = 2;
					}

					$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
					$wWinScore = 0;
					$wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;

					if($lost_chair == $i)
					{
						$wWinScore *= 2;	//点炮加倍
						$wWinPaoZi = ($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$i]->num);
						$this->m_paozi_score[$chair] += $wWinPaoZi;
						$this->m_paozi_score[$lost_chair] -= $wWinPaoZi;
					}

					$wWinScore = $this->_get_max_fan($wWinScore);

					$this->m_wHuScore[$i] -= $wWinScore;
					$this->m_wHuScore[$chair] += $wWinScore;

					$this->m_wSetLoseScore[$i] -= $wWinScore;
					$this->m_wSetScore[$chair] += $wWinScore;

					$this->m_HuCurt[$chair]->gain_chair[0]++;
					$this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $i;
				}
			}
			else if(empty($this->m_rule->is_dianpao_bao))
			{
				$banker_fan = 1;
				if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair))
				{
					$banker_fan = 2;
				}

				//一家出
				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;	
				$wWinScore = 0;
				$wWinScore += 2 * ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;	//点炮加倍

				$wWinPaoZi = ($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$lost_chair]->num);
				$this->m_paozi_score[$chair] += $wWinPaoZi;
				$this->m_paozi_score[$lost_chair] -= $wWinPaoZi;

				$wWinScore = $this->_get_max_fan($wWinScore);

				$this->m_wHuScore[$lost_chair] -= $wWinScore;
				$this->m_wHuScore[$chair] += $wWinScore;

				$this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
				$this->m_wSetScore[$chair] += $wWinScore;

				$this->m_HuCurt[$chair]->gain_chair[0] = 1;
				$this->m_HuCurt[$chair]->gain_chair[1]=$lost_chair;
			}

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
			$this->m_Score[$i]->score = $this->m_wSetScore[$i]+ $this->m_wSetLoseScore[$i]+ $this->m_wGangScore[$i][$i] +$this->m_wFollowScore[$i]+ $this->m_paozi_score[$i];
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

			if($this->m_paozi_score[$i]>0 && $this->m_rule->is_paozi)
			{
				$this->m_hu_desc[$i] .= '跑儿+'.$this->m_paozi_score[$i].' ';
			}
			else if($this->m_rule->is_paozi)
			{
				$this->m_hu_desc[$i] .= '跑儿'.$this->m_paozi_score[$i].' ';
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
		if($this->m_rule->is_feng)
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
		$this->m_deal_card_arr = array(['', '', '', ''], ['', '', '', ''], ['', '', '', ''], ['', '', '', '']);
		for($i=0; $i<$this->m_rule->player_count ; $i++)
		{
			$kou_num = 0;
			for($k=0; $k<ConstConfig::BASE_HOLD_CARD_NUM; $k++)
			{
				$temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];	//从牌缓冲区里那张牌
				$this->_list_insert($i, $temp_card);
				$this->m_deal_card_arr[intval($k/4)][$i] .= sprintf("%02d",$temp_card);

				//扣四
				if(!empty($this->m_rule->is_kou_card))
				{
					if($kou_num < 12)
					{
						$this->m_sPlayer[$i]->kou_card[] = [$temp_card, 0];
					}
					$kou_num++;
				}
			}
		}
	}

	public function game_to_playing()
	{
		$tmp_card_arr = $this->m_deal_card_arr;
		for ($n=0; $n <= 3; $n++)
		{
			$this->_set_record_game(ConstConfig::RECORD_DRAW_ALL, intval($tmp_card_arr[$n][0]), intval($tmp_card_arr[$n][1]), intval($tmp_card_arr[$n][2]), intval($tmp_card_arr[$n][3]));

			//扣四
			if(!empty($this->m_rule->is_kou_card) && $n < 3)
			{
				$record_arr = array('', '', '', '');
				$tmp_start = $n * 4;
				for ($i = 0; $i<$this->m_rule->player_count; ++$i)
				{
					if($this->m_sPlayer[$i]->kou_card[$tmp_start + 3][1] == 1)
					{
						$record_arr[$i] = sprintf("%02d",$this->m_sPlayer[$i]->kou_card[$tmp_start][0])
							.sprintf("%02d",$this->m_sPlayer[$i]->kou_card[$tmp_start + 1][0])
							.sprintf("%02d",$this->m_sPlayer[$i]->kou_card[$tmp_start + 2][0])
							.sprintf("%02d",$this->m_sPlayer[$i]->kou_card[$tmp_start + 3][0])
							;
					}
				}
				$this->_set_record_game(ConstConfig::RECORD_KOU_CARD, intval($record_arr[0]), intval($record_arr[1]), intval($record_arr[2]), intval($record_arr[3]));
			}
		}

		//给庄家发第14张牌
		$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->m_nCardBuf[$this->m_nCountAllot++];
		$this->_set_record_game(ConstConfig::RECORD_DRAW, $this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);

		//订 翻混牌
		if(!empty($this->m_rule->is_fanhun))
		{
			$this->m_fan_hun_card = $this->m_nCardBuf[$this->m_nCountAllot++];
			$this->_get_fan_hun($this->m_fan_hun_card);

			$this->_set_record_game(ConstConfig::RECORD_FANHUN, $this->m_nChairBanker, $this->m_fan_hun_card);
		}

		//状态设定
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD ;
		$this->m_chairCurrentPlayer = $this->m_nChairBanker;

		//状态变化发消息
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			if(!empty($this->m_rule->is_kou_card))
			{
				$this->m_sPlayer[$i]->kou_card_display = $this->_set_kou_arr($i);
			}
			if($i != $this->m_nChairBanker)
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_WAITING;
			}
			else
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
				$this->m_bChooseBuf[$i] = 1;
				
				//整理排序
				$this->_list_insert($i, $this->m_sPlayer[$i]->card_taken_now);
				$this->m_sPlayer[$i]->card_taken_now = $this->_find_14_card($i);
			}

			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
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
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
		}
	}

	public function start_kou_card()
	{
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_KOU_CARD;
		for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
		{
			$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_KOU_CARD;
			$this->m_sPlayer[$i]->kou_card_display = $this->_set_kou_arr($i);
			//发消息
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
		}
	}

	//开始玩
	public function on_start_game()			//游戏开始
	{
		$itime = time();
		if(!empty($this->m_rule->is_circle) && $this->m_nChairBankerNext == $this->m_nChairBanker)
		{
			$this->m_nSetCount -= 1;
		}
		//初始化数据，非首局的时候还要相关处理
		$this->InitData();
		$this->m_start_time = $itime;		
		$this->m_nSetCount += 1;
		$this->m_room_state = ConstConfig::ROOM_STATE_GAMEING;
		$this->_set_record_game(ConstConfig::RECORD_DEALER, $this->m_nChairBanker, 0, 0, intval(implode('', $this->m_dice)));
	
		//下炮子
		if(!empty($this->m_rule->is_paozi))
		{
			$this->start_pao_zi();
			return true;
		}	
		//发牌
		$this->DealAllCardEx();

		//扣四
		if(!empty($this->m_rule->is_kou_card))
		{
			$this->start_kou_card();
			return true;
		}

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

		if(!empty($this->m_rule->is_kou_card))
		{
			$ac = array_count_values($this->m_sPlayer[$chair]->kou_card_display);
			$tmp_first_num = empty($ac[$eat_card_first_tmp]) ? 0 : 1;
			$tmp_second_num = empty($ac[$eat_card_second_tmp]) ? 0 : 1;
			$tmp_kou_num = $tmp_first_num + $tmp_second_num;
			if($this->m_sPlayer[$chair]->len - 2 <= count($this->m_sPlayer[$chair]->kou_card_display) - $tmp_kou_num)
			{
				return false;
			}
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

		if(!empty($this->m_rule->is_kou_card))
		{
			$ac = array_count_values($this->m_sPlayer[$chair]->kou_card_display);
			$tmp_num = empty($ac[$this->m_sOutedCard->card]) ? 0 : $ac[$this->m_sOutedCard->card];
			$tmp_kou_num = $tmp_num > 2 ? 2 : $tmp_num;
			if($this->m_sPlayer[$chair]->len - 2 <= count($this->m_sPlayer[$chair]->kou_card_display) - $tmp_kou_num)
			{
				return false;
			}
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

	//找出第14张牌 ，扣四时候效率可以在优化
	private function _find_14_card($chair)
	{
		if(!empty($this->m_rule->is_kou_card))
		{
			foreach ($this->m_sPlayer[$chair]->kou_card_display as $value)
			{
				$this->_list_delete($chair, $value);
			}
		}
		for ($last_type = ConstConfig::PAI_TYPE_FENG; $last_type >= 0 ; $last_type--)
		{
			if($this->m_sPlayer[$chair]->card[$last_type][0] <= 0)
			{
				continue;
			}
			for($i=9; $i>0; $i--)
			{
				if($this->m_sPlayer[$chair]->card[$last_type][$i] > 0)
				{
					$fouteen_card = $this->_get_card_index($last_type, $i);
					$this->_list_delete($chair, $fouteen_card);
					break 2;
				}
			}
		}
		if(!empty($this->m_rule->is_kou_card))
		{
			foreach ($this->m_sPlayer[$chair]->kou_card_display as $value)
			{
				$this->_list_insert($chair, $value);
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
        if (isset($this->m_rule->pay_type))
        {
            if ($this->m_rule->pay_type == 0)
            {
                $this->m_nChairBanker = 0;
            }
            else
            {
                $this->m_nChairBanker = mt_rand(0, ($this->m_rule->player_count-1));
            }
        }
        else
        {
            if(empty($result['data']['winner_currency']))
            {
                $this->m_nChairBanker = 0;
            }
            else
            {
                $this->m_nChairBanker = mt_rand(0, ($this->m_rule->player_count-1));
            }
        }
        return;
	}

	private function _cancle_game()
	{
		$cancle_count = 0;
		$yes_count = 0;
		$no_count = 0;
		$is_cancle = 0;
		if($this->_is_clocker() && (Config::CANCLE_GAME_CLOCKER == 1))
		{
			$cancle_time_start = Config::CANCLE_GAME_CLOCKER_NUM;
		}
		else
		{
			$cancle_time_start = 0;
		}
		

		if($this->m_cancle_first == 255)
		{
			return $is_cancle;
		}

		for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
		{
			if(!empty($this->m_cancle[$i]) || empty($this->m_room_players[$i]))
			{
				//空位子算同意结束牌局，计数
				$cancle_count++;
				if( (!empty($this->m_cancle[$i]) && $this->m_cancle[$i] == 1) || empty($this->m_room_players[$i]))
				{
					$yes_count++;
				}
				//不同意结束牌局，计数
				if(!empty($this->m_cancle[$i]) && $this->m_cancle[$i] == 2)
				{
					$no_count++;
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
		//解散房间超过300秒，则游戏结束
		if(!empty($this->m_cancle_time) && ($this->m_cancle_time + Config::CANCLE_GAME_CLOCKER_NUM - time() <= Config::CANCLE_GAME_CLOCKER_LIMIT))
		{
			$this->m_room_state = ConstConfig::ROOM_STATE_OVER;
			$is_cancle = 1;
		}

		if($cancle_count >= $this->m_rule->player_count - 1 )
		{
			if($yes_count >= $this->m_rule->player_count - 1 )
			{
				$this->m_room_state = ConstConfig::ROOM_STATE_OVER;
				$is_cancle = 1;
			}
			else if($no_count >= 2)
			{
				for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
				{
					$this->m_cancle[$i] = 0;
				}
				$is_cancle = 2;
				$this->m_cancle_time = 0;
				$this->m_cancle_first = 255;
			}
		}

		$this->_send_cmd('s_cancle_game', array('is_cancle'=>$is_cancle, 'm_cancle_first'=>$this->m_cancle_first, 'm_cancle'=>$this->m_cancle, 'cancle_time_start'=>$cancle_time_start));

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
			$this->_send_cmd('s_game_over', $this->OnGetChairScene($this->m_cancle_first, true));

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
		if(in_array($act, [ConstConfig::RECORD_CHI, ConstConfig::RECORD_PENG, ConstConfig::RECORD_ZHIGANG, ConstConfig::RECORD_ANGANG, ConstConfig::RECORD_ZHUANGANG, ConstConfig::RECORD_HU, ConstConfig::RECORD_ZIMO, ConstConfig::RECORD_DISCARD, ConstConfig::RECORD_DRAW, ConstConfig::RECORD_DEALER, ConstConfig::RECORD_FANHUN], ConstConfig::RECORD_HU_QIANGGANG))
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
			|| ( $this->m_nSetCount != 255 && $this->m_rule->set_num <= $this->m_nSetCount && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext))
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
		if(empty($this->m_rule->is_circle) && !empty($result['data']['room_type']))
		{
    		$currency_tmp = BaseFunction::need_currency($result['data']['room_type'],$this->m_game_type,$this->m_rule->set_num);		
		}
		else if(!empty($result['data']['room_type_circle']))
		{
    		$currency_tmp = BaseFunction::need_currency($result['data']['room_type_circle'],$this->m_game_type,($this->m_rule->set_num / $this->m_rule->player_count));		
		}

		//房主付费
        if (isset($this->m_rule->pay_type))
        {
            if ($this->m_rule->pay_type == 0)
            {
                if($this->m_nSetCount == 1 && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext))
                {
                    $currency = !empty($currency_tmp) ? (-$currency_tmp) : 0;
                    BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'checkout_open_room', 'platform'=>'gfplay', 'uid'=>$this->m_room_owner, 'currency'=>$currency,'type'=>1));
                }
            }

            if ($this->m_rule->pay_type == 1)
            {
                if ($this->m_nSetCount == 1 && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext)){
                    $currency_all = !empty($currency_tmp) ? $currency_tmp : 0;
                    $currency = -(ceil($currency_all/$this->m_rule->player_count));
                    for($i = 0; $i < $this->m_rule->player_count; $i++)
                    {
                        BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'checkout_open_room', 'platform'=>'gfplay', 'uid'=>$this->m_room_players[$i]['uid'], 'currency'=>$currency,'type'=>1));

                    }
                }
            }

            if ($this->m_rule->pay_type == 2 && $is_room_over == 1 )
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
        else
        {
            if(empty($result['data']['winner_currency']))
            {
                if($this->m_nSetCount == 1 && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext))
                {
                    $currency = !empty($currency_tmp) ? (-$currency_tmp) : 0;
                    BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'checkout_open_room', 'platform'=>'gfplay', 'uid'=>$this->m_room_owner, 'currency'=>$currency,'type'=>1));
                }
            }
            else
            {
                if($is_room_over == 1 )
                {
                    //大赢家付费
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
	}

	private function _is_quemen($chair, $fanhun_type, $fanhun_num)
	{
		$is_quemen = false;
		//判断缺门(考虑到翻混)
		if(!empty($this->m_rule->is_quemen))
		{
			$is_quemen = true;
			$sum = 0;
			$stand_type_arr = array();

			for($k=0; $k<$this->m_sStandCard[$chair]->num; $k++)
			{
				$stand_pai_type = $this->_get_card_type($this->m_sStandCard[$chair]->first_card[$k]);
				$stand_type_arr[$stand_pai_type] = 1;
			}

			for ($i=ConstConfig::PAI_TYPE_WAN; $i < ConstConfig::PAI_TYPE_TONG; $i++)
			{ 
				$pai_num = $this->m_sPlayer[$chair]->card[$i][0];
				if($fanhun_num > 0 && $this->m_hun_card && $fanhun_type == $i)
				{
					$pai_num = $pai_num - $fanhun_num;
				}
				if($pai_num > 0 || $stand_type_arr[$i] > 0)
				{
					$sum ++;
				}
			}

			if($sum >= 3)
			{
				$is_quemen = false;
			}
		}

		return $is_quemen;
	}

	//判断一色结果，通过引用返回
	private function _is_yise($qing_arr, &$is_qingyise, &$is_ziyise)
	{
		$is_qingyise = false;
		$is_ziyise = false;
		if(1 == count(array_unique($qing_arr)))
		{
			if(!empty($this->m_rule->is_qingyise_fan) && ConstConfig::PAI_TYPE_FENG != $qing_arr[0])
			{
				$is_qingyise = true;
			}
			
			if(!empty($this->m_rule->is_ziyise_fan) && ConstConfig::PAI_TYPE_FENG == $qing_arr[0])
			{
				$is_ziyise = true;
			}
		}
	}
	
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

	private function _set_kou_arr($chair, $display = true)
	{
		$tmp_kou_arr = array();
		foreach ($this->m_sPlayer[$chair]->kou_card as $value)
		{
			if($value[1] == 1)
			{
				if($display)
				{
					$tmp_kou_arr[] = $value[0];
				}
				else
				{
					$tmp_kou_arr[] = 0;
				}
			}
		}
		if($display)
		{
			sort($tmp_kou_arr);
		}
		return $tmp_kou_arr;
	}

	private function _sub_kou_arr($chair)
	{
		$tmp_kou_arr = $this->m_sPlayer[$chair]->kou_card;

		for ($i=0; $i <= 2; $i++)
		{ 
			if($this->m_sPlayer[$chair]->kou_card[$i*4 + 3][1] == 0)
			{
				if(!isset($this->m_sPlayer[$chair]->kou_card[$i*4 - 1]) || $this->m_sPlayer[$chair]->kou_card[$i*4 - 1][1] == 1)
				{
					return array_slice($this->m_sPlayer[$chair]->kou_card, 0, ($i*4+4));
				}
				else if($this->m_sPlayer[$chair]->kou_card[$i*4 - 1][1] == 2)
				{
					return array_slice($this->m_sPlayer[$chair]->kou_card, 0, ($i*4));
				}
			}
		}
		return array_slice($this->m_sPlayer[$chair]->kou_card, 0, (2*4 + 4));
	}

	public function _get_max_fan($fan_sum)
	{
		$return_fan = $fan_sum;
		if (!empty($this->m_rule->top_fan) && $fan_sum > $this->m_rule->top_fan)
		{
			$return_fan = $this->m_rule->top_fan;
		}
		return $return_fan;
	}

	public function _is_clocker()
	{
		if(defined("gf\\conf\\Config::CANCLE_GAME_CLOCKER") && defined("gf\\conf\\Config::CANCLE_GAME_CLOCKER_NUM") && defined("gf\\conf\\Config::CANCLE_GAME_CLOCKER_LIMIT"))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

}
