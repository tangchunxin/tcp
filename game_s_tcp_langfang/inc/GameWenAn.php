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

class GameWenAn
{
    const GAME_TYPE = 421;
	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21; 					// 平胡
	const HU_TYPE_QIDUI = 22; 					// 七对
    const HU_TYPE_SHISANYAO = 23;               // 十三幺
    const HU_TYPE_LUANYAO = 24;           	    // 乱幺
	const HU_TYPE_FENGDING_TYPE_INVALID  = 0; 	// 错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－

    const ATTACHED_HU_GANGKAI = 61;             // 杠开
    const ATTACHED_HU_SUHU = 62;                // 素胡
    const ATTACHED_HU_YITIAOLONG = 63;          // 一条龙
    const ATTACHED_HU_BENHUNLONG = 64;          // 本混龙
    const ATTACHED_HU_HUNDIAO = 65;             // 混吊
    const ATTACHED_HU_HUNDIAOHUN = 66;          // 混吊混

    const ATTACHED_HU_WUKUI = 67;				//捉五魁
    const ATTACHED_HU_DIAOWUWAN = 68;			//吊五万

	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 1;  				// 直杠  1分
	const M_ANGANG_SCORE = 2;   				// 暗杠  1分
	const M_WANGANG_SCORE = 1;  				// 弯杠  1分

	public static $hu_type_arr = array(
	self::HU_TYPE_PINGHU=>array(self::HU_TYPE_PINGHU, 1, '平胡')
	,self::HU_TYPE_QIDUI=>array(self::HU_TYPE_QIDUI, 1, '七对')
    ,self::HU_TYPE_SHISANYAO=>array(self::HU_TYPE_SHISANYAO, 1, '十三幺')
    ,self::HU_TYPE_LUANYAO=>array(self::HU_TYPE_LUANYAO, 1, '乱幺')
	);

	public static $attached_hu_arr = array(
        self::ATTACHED_HU_GANGKAI=>array(self::ATTACHED_HU_GANGKAI, 2, '杠上花')
    ,self::ATTACHED_HU_SUHU=>array(self::ATTACHED_HU_SUHU, 2, '素胡')
    ,self::ATTACHED_HU_YITIAOLONG=>array(self::ATTACHED_HU_YITIAOLONG, 3, '一条龙')
    ,self::ATTACHED_HU_BENHUNLONG=>array(self::ATTACHED_HU_BENHUNLONG, 6, '本混龙')
    ,self::ATTACHED_HU_HUNDIAO=>array(self::ATTACHED_HU_HUNDIAO, 2, '混吊')
    ,self::ATTACHED_HU_HUNDIAOHUN=>array(self::ATTACHED_HU_HUNDIAOHUN, 4, '混吊混(五)')
    ,self::ATTACHED_HU_WUKUI=>array(self::ATTACHED_HU_WUKUI, 2, '捉五魁')
    ,self::ATTACHED_HU_DIAOWUWAN=>array(self::ATTACHED_HU_DIAOWUWAN, 2, '吊五万')
	);

	
	public $serv;						// socket服务器对象

	public $m_ready = array(0,0,0,0);	// 用户准备
	public $m_game_type;				// 421 廊坊文安
	public $m_room_state;				// 房间状态
	public $m_room_id;					// 房间号
	public $m_room_owner;				// 房主
	public $m_room_players = array();	// 玩家信息
	public $m_rule;						// 规则对象
	public $m_start_time;				// 开始时间
	public $m_end_time;	 				// 结束时间
	public $m_record_game = array();	// 录制脚本

	public $m_dice = array(0,0);	    // 两个骰子点数
	public $m_hu_desc = array();	    // 详细的胡牌类型(七小对 天胡, 地胡, 碰碰胡.......)
	public $m_nSetCount;				// 比赛局数
	public $m_wTotalScore;				// 总结的分数

	public $m_nChairDianPao;			// 点炮玩家椅子号
	public $m_nCountHu;					// 胡牌玩家个数
	public $m_nCountFlee;				// 逃跑玩家个数

	public $m_bChairHu = array();		// 血战已胡玩家
	public $m_bChairHu_order = array();	// 血战已胡玩家顺序
	public $m_only_out_card = array();	// 玩家只能出牌不能碰杠胡

	public $m_bTianRenHu;				// 判断地天人胡
	public $m_nDiHu = array();			// 判断地胡

	public $m_nEndReason;				// 游戏结束原因

	public $m_sQiangGang;				// 抢杠结构
	public $m_sGangPao;					// 杠炮结构
	public $m_bHaveGang;    			// 是否有杠开

	//记分，以后处理
	public $m_wGangScore = array();		// 刮风下雨总分数
	public $m_wGFXYScore = array();		// 刮风下雨临时分数
	public $m_wHuScore = array();		// 本剧胡整合分数
	public $m_wSetScore = array();		// 该局的胡分数
	public $m_wSetLoseScore = array();	// 该局的被胡分数
	public $m_Score = array();			// 用户分数结构
	//public $m_wChairBanker = array();	// 庄家分数结构  2分
	//public $m_wFollowScore = array();	// 跟庄庄家分数结构


	//数据区
	public $m_cancle = array();			// 解散房间标志
	public $m_cancle_first;				// 解散房间发起人
	public $m_cancle_time;				// 解散房间开始时间

	public $m_nTableCards = array();	// 玩家的桌面牌
	public $m_nNumTableCards = array();	// 玩家桌面牌数量
	public $m_sStandCard = array();		// 玩家倒牌 Stand_card
	public $m_sPlayer = array();		// 玩家手牌私有数据 Play_data
	public $m_nNumCheat = array();		// 玩家i诈胡次数

	//逃跑用户
	public $m_bFlee = array();

	//处理选择命令
	public $m_bChooseBuf = array();		// 玩家的选择胡,吃,碰,杠命令 1 等待操作 0 无操作
	public $m_nNumCmdHu;				// 胡命令的个数
	public $m_chairHu = array();		// 发出胡命令的玩家
	public $m_chairSendCmd;				// 当前发命令的玩家
	public $m_currentCmd;				// 当前的命令
	public $m_eat_num;					// 竞争选择吃法 存储 

	// 接收客户端数据
	//public $m_nJiang = array();		// 判断胡牌的将,不能胡时将为255;
	public $m_nHuGiveUp = array();		// 该轮放弃胡的番数,m_nHuGiveUp = []: 个数

	// 与客户端无关
	public $m_nCardBuf = array();		// 牌的缓冲区
	public $m_HuCurt = array();			// 胡牌信息

	public $m_bMaxFan = array();		// 是否达到封顶番数

	public $m_nChairBanker;				// 庄家的位置，
	public $m_nChairBankerNext = 255;	// 下一局庄家的位置，
	public $m_nCountAllot;				// 发到第几张牌
	public $m_nAllCardNum = ConstConfig::BASE_CARD_NUM;
	public $m_sOutedCard;				// 刚打出的牌
	public $m_sysPhase;					// 当前阶段状态
	public $m_chairCurrentPlayer;		// 当前出牌者
	public $m_set_end_time;				// 本局结束时间
	public $m_bLastGameOver;            //按圈 打牌  牌局是否最终结束
	public $m_is_ting_arr;              //是否听牌，能点炮胡


	public $m_fan_hun_card;			    //翻混牌
	public $m_hun_card = array();	    //混牌(混牌有两张使用数组)
	//public $m_nHuList = array();		// 胡牌列表, m_nHuCList = [][0]: 可胡牌的个数

    public $m_own_lazhuang;             //拉庄数据
    public $m_paozi_score = array();    //拉庄得分
    public $m_gangkai_num = array();    //连续杠的次数

    public $agent_uid;                                  // 公会房代理的玩家id
    public $is_agent_payed;                             // 判断会长是否付过费
    public $m_client_ip = array();                      // 用户ip

    public $ALL_CARD = array();                         //做牌数组

	/************************************************************************/
	/*                               函数区                                 */
	/************************************************************************/

	public function __construct($serv)
	{
		$this->serv = $serv;
		
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_SET_OVER ;
		$this->m_room_state = ConstConfig::ROOM_STATE_NULL ;
		$this->m_game_type = self::GAME_TYPE;
	}

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
			echo 'error InitData'.__LINE__.__CLASS__;
			return false;
		}
		if($is_open || ($this->m_rule->set_num <= $this->m_nSetCount && $this->m_bLastGameOver))
		{
			$this->m_game_type = self::GAME_TYPE;	//游戏 1 四川血战到底 2 陕西麻将(陕北麻将)
			$this->m_room_state = ConstConfig::ROOM_STATE_OVER ;	//房间状态
			$this->m_room_id = 0;	//房间号
			$this->m_room_owner = 0;	//房主
			$this->m_room_players = array();	//玩家信息
			$this->m_start_time = 0;	//开始时间
			$this->m_end_time = time();	//结束时间

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

		$this->m_nChairDianPao = 255;
		$this->m_nCountHu = 0;
		$this->m_bChairHu_order = array();
		$this->m_nCountFlee = 0;
		$this->m_bTianRenHu = true; //天胡
		$this->m_nEndReason = 0;

		$this->m_sQiangGang = new Qiang_gang();
		$this->m_sGangPao = new Gang_pao();
		//$this->m_sFollowCard = new Follow_card();

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
		$this->m_hun_card = array(0,0);	//混牌
		$this->m_bLastGameOver = 0; //最终结束状态

		for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
		{
			$this->m_bChairHu[$i] = false;
			$this->m_nDiHu[$i] = 0;
			$this->m_wGangScore[$i] = array(0,0,0,0);
			$this->m_wHuScore[$i] = 0;
			$this->m_wSetScore[$i] = 0;
			$this->m_wSetLoseScore[$i] = 0;
			$this->m_wGFXYScore[$i] = 0;
			//$this->m_wFollowScore[$i] = 0;	//跟庄庄家分数结构
			$this->m_Score[$i] = new Score();
            $this->m_own_lazhuang[$i] = new La_zhuang();//开局时拉庄的分数
            $this->m_paozi_score[$i] = 0;               //最后拉庄的得分

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
			$this->m_is_ting_arr[$i] = 1;
			
			//$this->m_nHuList[$i] = 0;
            $this->m_gangkai_num[$i] = 0;
		}
	}
	
	//处理逃跑玩家
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if($this->m_room_state != ConstConfig::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfig::ROOM_STATE_OPEN )
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间已经不存来了'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}
			//有掉线用户
			if($this->handle_flee_play())
			{
				//有人断线，检测游戏结束投票
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if($this->m_room_state != ConstConfig::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfig::ROOM_STATE_OPEN )
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}
			if(!empty($params['uid']))
			{
				foreach ($this->m_room_players as $key => $room_user)
				{
					if($room_user['uid'] == $params['uid'])
					{
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
				$return_send['code'] = 2; 
				$return_send['text'] = '房间已经不存在了'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
				$return_send['code'] = 2; 
				$return_send['text'] = '房间已经不存在了'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_SET_OVER || $this->m_room_state == ConstConfig::ROOM_STATE_GAMEING )
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '此房间已经被占用'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}
			elseif ($this->m_room_state == ConstConfig::ROOM_STATE_OPEN  && $this->m_room_owner != $params['uid'])
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '此房间已经被占用'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}
			$this->clear();

			$this->m_rule = new RuleWenAn();
			if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3, 4)))
			{
				$params['rule']['player_count'] = 4;
			}
			$this->m_rule->game_type = $params['rule']['game_type'];
			$this->m_rule->player_count = $params['rule']['player_count'];
			$this->m_rule->set_num = $params['rule']['set_num'];			
			$this->m_rule->min_fan = $params['rule']['min_fan'];
			$this->m_rule->top_fan = $params['rule']['top_fan'];
            $this->m_rule->is_circle = $params['rule']['is_circle'];
            if(!empty($this->m_rule->is_circle))
            {
                $this->m_rule->set_num = $this->m_rule->is_circle * $this->m_rule->player_count;		//局等于  人*圈
            }
            else
            {
                $this->m_rule->set_num = $params['rule']['set_num'];
            }

            $this->m_rule->pay_type = $params['rule']['pay_type'];    //支付类型(大赢家付费,房主,AA)
            //本项目特有的玩法
            $this->m_rule->is_daizhuang = $params['rule']['is_daizhuang'];
            $this->m_rule->is_lazhuang = $params['rule']['is_lazhuang'];

			$this->InitData(true);
			
			$this->m_room_state = ConstConfig::ROOM_STATE_OPEN ;
			$this->m_room_id = $params['rid'];
			$this->m_room_owner = $params['uid'];
			$this->m_room_players = array();
			$this->m_start_time = $itime;
			$this->m_nSetCount = 0;
			$this->agent_uid = 0;
            $this->is_agent_payed = 0;
			if (!empty($params['opend_status']) && $this->m_rule->pay_type == 3) 
			{
				$this->agent_uid = $params['opend_status'];
			}
			
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}
			
			//性别兼容以前的
			if(empty($params['sex']))
			{
				$params['sex'] = 0;
			}

            if(empty($params['gps']))
            {
                $params['gps'] = [];
            }

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}
			else if(empty($this->m_room_players) && ($itime - $this->m_start_time) > Room::$room_timeout)
			{
				$this->m_room_state = ConstConfig::ROOM_STATE_OVER ;
				$this->clear();
				$return_send['code'] = 3; 
				$return_send['text'] = '没有此房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if( ($params['is_room_owner'] && $params['uid'] != $this->m_room_owner)
			|| ($params['is_room_owner'] && !empty($this->m_room_players[0]) && $params['uid']!=$this->m_room_players[0]['uid'])
			)
			{
				$return_send['code'] = 4; 
				$return_send['text'] = '房主错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
					$return_send['code'] = 5; 
					$return_send['text'] = '房间已满'; 
					$return_send['desc'] = __LINE__.__CLASS__; break 2;
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
					$return_send['code'] = 4; 
					$return_send['text'] = '房主错误'; 
					$return_send['desc'] = __LINE__.__CLASS__; break;
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
            $this->m_room_players[$add_key]['gps'] = $params['gps'];
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
				$return_send['code'] = 3; 
				$return_send['text'] = '用户不属于本房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}
			
			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
			{
				$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($u_key,true), Game_cmd::SCO_SINGLE_PLAYER , $params['uid']);
				$cmd->send($this->serv);
				unset($cmd);				
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
				$this->m_start_time = $itime;
				$this->on_start_game();
				$this->m_room_state = ConstConfig::ROOM_STATE_GAMEING ;
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if((ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
				$return_send['code'] = 3; 
				$return_send['text'] = '解散房间请求错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$this->handle_flee_play(true);	//更新断线用户
			$this->_cancle_game();

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//新增拉庄协议
    //下拉庄
    public function c_la_zhuang($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do {
            if( empty($params['rid'])
                || empty($params['uid'])
                || !isset($params['la_zhuang_num'])
                || !in_array($params['la_zhuang_num'], array(0, 1, 2, 3))
            )
            {
                $return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_LA_ZHUANG || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state)
            {
                $return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {
                    if ($this->m_own_lazhuang[$key]->recv)
                    {
                        $return_send['code'] = 4; $return_send['text'] = '您已经拉庄'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    }

                    $this->handle_la_zhuang($key, $params['la_zhuang_num']);
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
							$return_send['code'] = 6; 
							$return_send['text'] = '连续发送胡牌信息'; 
							$return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
					}
					
					if($this->m_sPlayer[$key]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
					{
						$return_send['code'] = 7; 
						$return_send['text'] = '胡牌错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					
					if($key != $this->m_chairCurrentPlayer)
					{
						$return_send['code'] = 4; 
						$return_send['text'] = '当前用户错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if($this->m_only_out_card[$key] == true)
					{
						$return_send['code'] = 6; 
						$return_send['text'] = '当前用户状态只能出牌'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if(!$this->HandleHuZiMo($key))	// 诈胡
					{
						$this->_clear_choose_buf($key);
						$return_send['code'] = 5; 
						$return_send['text'] = '诈胡'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					$this->_clear_choose_buf($key);	  //自摸不可能抢杠胡
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; 
				$return_send['text'] = '用户不属于本房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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

					if(4 != $this->_list_find($key,$params['gang_card'])
					&& !(($params['gang_card'] == $this->m_sPlayer[$key]->card_taken_now) && 3 == $this->_list_find($key,$params['gang_card']))
					)
					{
						$return_send['code'] = 5; 
						$return_send['text'] = '杠牌错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					
					$this->_clear_choose_buf($key);
					$this->HandleChooseAnGang($key, $params['gang_card']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; 
				$return_send['text'] = '用户不属于本房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
						$return_send['code'] = 5; 
						$return_send['text'] = '杠牌错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					
					$this->_clear_choose_buf($key);
					$this->HandleChooseWanGang($key, $params['gang_card']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; 
				$return_send['text'] = '用户不属于本房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
					if((empty($params['is_14']) && 0 == $this->_list_find($key,$params['out_card']))
					  )
					{
						$return_send['code'] = 5;
						$return_send['text'] = '出牌错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->HandleOutCard($key, $params['is_14'], $params['out_card'], $params['is_ting']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; 
				$return_send['text'] = '用户不属于本房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//取消杠
	public function c_cancle_gang($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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

					$this->m_sPlayer[$key]->state = ConstConfig::PLAYER_STATUS_THINK_OUTCARD ;
					$this->_clear_choose_buf($key);
					$is_act = true;
                    //$this->_set_record_game(ConstConfig::RECORD_PASS, $key);
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; 
				$return_send['text'] = '用户不属于本房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//荒庄
    public function c_huang_zhuang($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do {
            if( empty($params['rid'])
                || empty($params['uid'])
            )
            {
                $return_send['code'] = 1;
                $return_send['text'] = '参数错误';
                $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
            {
                $return_send['code'] = 2;
                $return_send['text'] = '房间状态错误';
                $return_send['desc'] = __LINE__.__CLASS__; break;
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
                            $return_send['code'] = 6;
                            $return_send['text'] = '连续发送胡牌信息';
                            $return_send['desc'] = __LINE__.__CLASS__; break 2;
                        }
                    }

                    if($this->m_sPlayer[$key]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
                    {
                        $return_send['code'] = 7;
                        $return_send['text'] = '胡牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    }

                    if($key != $this->m_chairCurrentPlayer)
                    {
                        $return_send['code'] = 4;
                        $return_send['text'] = '当前用户错误';
                        $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    }
                    if($this->m_only_out_card[$key] == true)
                    {
                        $return_send['code'] = 6;
                        $return_send['text'] = '当前用户状态只能出牌';
                        $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    }
                    $this->m_nEndReason = ConstConfig::END_REASON_NOCARD;
                    $this->HandleSetOver();
                    $is_act = true;
                }
            }
            if(!$is_act = true)
            {
                $return_send['code'] = 3;
                $return_send['text'] = '用户不属于本房间';
                $return_send['desc'] = __LINE__.__CLASS__; break;
            }

        }while(false);

        $this->serv->send($fd,  Room::tcp_encode(($return_send)));

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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if(!$this->_find_peng($key))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; 
						$return_send['text'] = '当前用户无碰'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if(empty($this->m_sOutedCard->card) 
						|| $this->m_sOutedCard->chair == $key 
						|| 2 > $this->_list_find($key,$this->m_sOutedCard->card)
					  )
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; 
						$return_send['text'] = '碰牌错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					
					$this->_clear_choose_buf($key);
					$this->HandleChooseResult($key, $params['act']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; 
				$return_send['text'] = '用户不属于本房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if(!$this->_find_eat($key,$params['num']))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; 
						$return_send['text'] = '当前用户无吃牌'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if(empty($this->m_sOutedCard->card) 
						|| $this->m_sOutedCard->chair == $key 
						|| $this->m_sOutedCard->chair != $this->_anti_clock($key,-1)
					  )
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; 
						$return_send['text'] = '吃牌错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					
					$this->_clear_choose_buf($key);
					$this->HandleChooseResult($key, $params['act'], $params['num']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; 
				$return_send['text'] = '用户不属于本房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
						$return_send['code'] = 4; 
						$return_send['text'] = '当前用户无直杠'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if(empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || 3 > $this->_list_find($key,$this->m_sOutedCard->card))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; 
						$return_send['text'] = '杠牌错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					
					$this->_clear_choose_buf($key);
					$this->HandleChooseResult($key, $params['act']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; 
				$return_send['text'] = '用户不属于本房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//胡  
	public function c_hu($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);

		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
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
						$return_send['code'] = 6; 
						$return_send['text'] = '连续发送胡牌信息'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					
					$params['type'] = 0;
					if( (empty($this->m_sOutedCard->card) && empty($this->m_sQiangGang->card))
					  || ($this->m_sOutedCard->card && $this->m_sOutedCard->chair == $key)
					  || ($this->m_sQiangGang->card && $this->m_sQiangGang->chair == $key)
					  )
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; 
						$return_send['text'] = '胡牌错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					if($this->m_sPlayer[$key]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; 
						$return_send['text'] = '胡牌错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
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
						$return_send['code'] = 5; 
						$return_send['text'] = '胡牌错误'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					$this->_list_insert($key, $temp_card);
					$this->m_HuCurt[$key]->card = $temp_card;
					if(!$this->judge_hu($key))					
					{
						$this->m_HuCurt[$key]->clear();
						$this->_list_delete($key, $temp_card);
						$this->HandleZhaHu($key);
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; 
						$return_send['text'] = '当前用户诈胡'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					$this->m_HuCurt[$key]->clear();
					$this->_list_delete($key, $temp_card);

					$this->_clear_choose_buf($key,false);
					$this->HandleChooseResult($key, $params['act']);
					$is_act = true;

					// if(empty($this->m_rule->is_yipao_duoxiang))
					// {
					// 	   //下家取消操作
					// 	   $next_chair = $key;
					// 	   for ($i=0; $i<$this->m_rule->player_count; $i++)
					// 	   {
					// 		   $c_act = "c_cancle_choice";
					// 		   $next_chair = $this->_anti_clock($next_chair);
					// 		   if($next_chair == $this->m_chairCurrentPlayer)
					// 		   {
					// 		        break;
					// 		   }
					// 		   if(!($this->m_bChooseBuf[$next_chair]))
					// 		   {
					// 			    continue;
					// 		   }
					// 		   $this->m_sPlayer[$next_chair]->state = ConstConfig::PLAYER_STATUS_WAITING;
					// 		   $c_act = "c_cancle_choice";

					// 		   $this->_clear_choose_buf($next_chair, false);
					// 		   $this->HandleChooseResult($next_chair, $c_act);
					// 	   }
					// }
					
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
						if( self::is_hu_give_up($temp_card, $this->m_nHuGiveUp[$last_chair]) || !$this->judge_hu($last_chair))
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
				$return_send['code'] = 3; 
				$return_send['text'] = '用户不属于本房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

    //取消选择（吃  碰  直杠  点炮胡）
	public function c_cancle_choice($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| !isset($params['type'])
			)
			{
				$return_send['code'] = 1; 
				$return_send['text'] = '参数错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}
			
			$params['act'] = 'c_cancle_choice';

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; 
				$return_send['text'] = '房间状态错误'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if(!($this->m_bChooseBuf[$key]))
					{
						$return_send['code'] = 4; 
						$return_send['text'] = '当前用户无需选择'; 
						$return_send['desc'] = __LINE__.__CLASS__; break 2;
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

						if($this->judge_hu($key))
						{
							$this->m_nHuGiveUp[$key] = $this->m_nHuGiveUp[$key] * 100 + $temp_card;
						}
						$this->m_HuCurt[$key]->clear();
						$this->_list_delete($key, $temp_card);
					}
                    if($params['type'] != 0)
                    {
                        $this->_set_record_game(ConstConfig::RECORD_PASS, $key);
                    }

					$this->_clear_choose_buf($key, false); //有可能取消的是抢杠胡，这是需要后面判断来补张
					$this->m_sPlayer[$key]->state = ConstConfig::PLAYER_STATUS_WAITING;
					$this->HandleChooseResult($key, $params['act']);
					$is_act = true;


				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; 
				$return_send['text'] = '用户不属于本房间'; 
				$return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

    //做牌
    public function c_make_card($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do {
            if(
            empty($params['all_card'])
            )
            {
                $return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }
            $array=explode(",",substr(substr($params['all_card'],1),0,-1));
            for($i=0;$i<count($array);$i++)
            {
                $array[$i] = intval($array[$i]);
            }

            $this->ALL_CARD = $array;

        }while(false);

        $this->serv->send($fd,  Room::tcp_encode(($return_send),false));
        return $return_send['code'];
    }

	//--------------------------------------------------------------------------

	//判断胡
	public function judge_hu($chair)
	{
        //$hu_type为数组
        //$response = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_WUKUI' => false);
        $hu_type = $this->judge_hu_type_fanhun($chair);

        if($hu_type['HU_TYPE'] == self::HU_TYPE_FENGDING_TYPE_INVALID)
        {
            return false;
        }

        //记录在全局数据
        $this->m_HuCurt[$chair]->method[0] = $hu_type['HU_TYPE'];
        $this->m_HuCurt[$chair]->count = 1;


        //抢杠杠开杠炮
        /*if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)	// 处理抢杠
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
        }*/
        if(!empty($this->m_rule->is_ganghua_fan) && $this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair)	//杠开
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGKAI);
        }
        // else if ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO && $this->m_sGangPao->mark && $this->m_sGangPao->chair != $chair)
        // {
        // 	//$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGPAO);
        // }
        //

        //素胡
        $fanhun_num = 0;
        foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
        {
            $fanhun_num  += $this->_list_find($chair,$fanhun_card);	//手牌翻混个数
        }
        if ($fanhun_num == 0)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_SUHU);
        }

        //一条龙
        if($hu_type['ATTACHED_HU_YITIAOLONG'])
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_YITIAOLONG);
        }
        //本混龙
        if($hu_type['ATTACHED_HU_BENHUNLONG'])
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_BENHUNLONG);
        }


        //混吊和混吊混
        if($hu_type['ATTACHED_HU_HUNDIAO'])
        {
            if (in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card))
            {
                $hu_type['ATTACHED_HU_HUNDIAOHUN'] = true;
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_HUNDIAOHUN);
            }
            else
            {
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_HUNDIAO);
            }

        }
        //捉五魁
        if(empty($hu_type['ATTACHED_HU_HUNDIAOHUN']))
        {
            if($hu_type['ATTACHED_HU_WUKUI'])
            {
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_WUKUI);
            }
        }

        $fan_sum = $this->judge_fan($chair);  //这个就是  一共多少分

        if($fan_sum < $this->m_rule->min_fan)
        {
            $this->m_HuCurt[$chair]->clear();
            return false;
        }
        return true;
	}

    public function judge_hu_type_fanhun($chair)
    {
        $yitiaolong = false;
        $score = 0;
        $result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
        //---------------------判断混子数-----------------------------------------
        $fanhun_num = 0;
        foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
        {
            $fanhun_num  += $this->_list_find($chair,$fanhun_card);	//手牌翻混个数
        }
        $pai_type = $this->_get_card_type($this->m_fan_hun_card);
        if ($fanhun_num == 0)
        {
            $result = $this->judge_hu_type($chair,$this->m_HuCurt[$chair]->card);
            return $result;
        }
        else
        {
            $temp_result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
            //十三幺牌型
            if(true)
            {


                $fanhun_arr = array(); //每个混子的数量
                //去掉所有混子
                foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
                {
                    $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                    $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                    $one_fanhun_card = $fanhun_card%16;       //翻混牌

                    $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                    $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                    $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] -= $one_fanhun_num;
                }



                $is_shisanyao = true;
                $is_luanyao = true;
                $luanyao_num = 0;
                $tmp_card_follow = 0;



                //倒牌(没有吃牌所有直接判断第一张牌)
                for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
                {
                    $is_shisanyao = false;
                    if (in_array($this->m_sStandCard[$chair]->first_card[$i], array(1,9,17,25,33,41,49,50,51,52,53,54,55)))
                    {
                        $luanyao_num +=3;
                    }
                    else
                    {
                        $luanyao_num +=255;
                    }
                }

                //手牌
                for($i = ConstConfig::PAI_TYPE_WAN ; $i <= ConstConfig::PAI_TYPE_TONG ; $i++)
                {
                    for($j=1; $j<=9; $j++)
                    {
                        if ($j == 1 || $j == 9)
                        {
                            if($this->m_sPlayer[$chair]->card[$i][$j] > 2)
                            {
                                $is_shisanyao = false;
                                $luanyao_num += $this->m_sPlayer[$chair]->card[$i][$j];
                            }
                            elseif($this->m_sPlayer[$chair]->card[$i][$j] == 2)
                            {
                                //两张牌有且只有一次
                                $tmp_card_follow += 1;
                                $luanyao_num += 2;
                                if($tmp_card_follow>1)  //有且只有一个对
                                {
                                    $is_shisanyao = false;
                                }
                            }
                            elseif($this->m_sPlayer[$chair]->card[$i][$j] == 1)
                            {
                                //十三幺符合不做处理
                                $luanyao_num += 1;
                            }
                        }
                        else
                        {
                            if ($this->m_sPlayer[$chair]->card[$i][$j]>0)
                            {
                                $is_shisanyao = false;
                                $luanyao_num +=255;
                                break 2;
                            }
                        }
                    }
                }
                for ($i = ConstConfig::PAI_TYPE_FENG ; $i <= ConstConfig::PAI_TYPE_FENG ; $i++)
                {
                    $luanyao_num += $this->m_sPlayer[$chair]->card[$i][0];
                    for($j=1; $j<=7; $j++)
                    {

                        if($this->m_sPlayer[$chair]->card[$i][$j] > 2)
                        {
                            $is_shisanyao = false;

                        }
                        elseif($this->m_sPlayer[$chair]->card[$i][$j] == 2)
                        {
                            if( 1 == $tmp_card_follow)  //有且只有一个对
                            {
                                $is_shisanyao = false;
                            }
                            else
                            {
                                $tmp_card_follow += 1;
                            }
                        }
                    }
                }

                //拿回所有混子
                foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
                {
                    $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                    $one_fanhun_card = $fanhun_card%16;       //翻混牌

                    $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                    $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
                }

                if ($luanyao_num + $fanhun_num == 14)
                {
                    if($is_shisanyao)
                    {
                        $temp_result['HU_TYPE'] = self::HU_TYPE_SHISANYAO;
                    }
                    else
                    {
                        $temp_result['HU_TYPE'] = self::HU_TYPE_LUANYAO;
                    }
                }
                if ($temp_result['HU_TYPE'] != self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    //计算番数
                    $temp_score =  self::$hu_type_arr[$temp_result['HU_TYPE']][1];
                    if ($temp_result['ATTACHED_HU_BENHUNLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_WUKUI'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                    }
                    if ($temp_result['ATTACHED_HU_HUNDIAO'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                    }
                    if ($temp_score > $score)
                    {
                        $score = $temp_score;
                        $result = $temp_result;
                    }
                }
            }

            //七对
            if(true)
            {
                $temp_result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
                $need_fanhun = 0;	//需要混子个数
                $hu_qidui = false;

                if($this->m_sStandCard[$chair]->num == 0)
                {
                    $fanhun_arr = array(); //每个混子的数量
                    //去掉所有混子
                    foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
                    {
                        $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                        $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                        $one_fanhun_card = $fanhun_card%16;       //翻混牌

                        $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                        $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                        $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] -= $one_fanhun_num;
                    }

                    for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
                    {
                        if(0 == $this->m_sPlayer[$chair]->card[$i][0])
                        {
                            continue;
                        }
                        for ($j=1; $j<=9; $j++)
                        {
                            if($this->m_sPlayer[$chair]->card[$i][$j] == 1 || $this->m_sPlayer[$chair]->card[$i][$j] == 3)
                            {
                                $need_fanhun +=1 ;
                                //$da8zhang_replace_fanhun[$i]+= 1;
                            }
                        }
                    }

                    //拿回所有混子
                    foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
                    {
                        $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                        $one_fanhun_card = $fanhun_card%16;       //翻混牌

                        $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                        $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
                    }

                    if($need_fanhun <= $fanhun_num)
                    {
                        $hu_qidui = true;
                    }

                    if($hu_qidui)
                    {
                        //吊五魁
                        $temp_result['ATTACHED_HU_WUKUI'] = $this->judge_qidui_wukui($chair);
                        //判断混吊单吊5万
                        $temp_result['ATTACHED_HU_HUNDIAO'] = $this->judge_qidui_hundiao($chair);

                        $temp_result['HU_TYPE'] = self::HU_TYPE_QIDUI;
                    }
                    if ($temp_result['HU_TYPE'] != self::HU_TYPE_FENGDING_TYPE_INVALID)
                    {
                        //计算番数
                        $temp_score =  self::$hu_type_arr[$temp_result['HU_TYPE']][1];
                        if ($temp_result['ATTACHED_HU_BENHUNLONG'])
                        {
                            $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                        }
                        if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                        {
                            $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                        }
                        if ($temp_result['ATTACHED_HU_WUKUI'])
                        {
                            $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                        }
                        if ($temp_result['ATTACHED_HU_HUNDIAO'])
                        {
                            $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                        }
                        if ($temp_score > $score)
                        {
                            $score = $temp_score;
                            $result = $temp_result;
                        }
                    }

                }
            }
            //32牌型
            if(true)
            {
                $temp_result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
                $score_five = 0;
                $result_four= $temp_result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
                $temp_score_five = 0;
                $fanhun_arr = array(); //每个混子的数量
                //去掉所有混子
                foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
                {
                    $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                    $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                    $one_fanhun_card = $fanhun_card%16;       //翻混牌

                    $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                    $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                    $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] -= $one_fanhun_num;
                }


                //32牌型
                $qing_arr = array();
                $bQing = false;
                $qingyise = false;
                $is_hu_data = false;
                $yitiaolong_tmp = false;
                $jiang_judge_arr = array(0=>2,1=>1,2=>0,3=>2,4=>1,5=>0,6=>2,7=>1,8=>0,9=>2,10=>1,11=>0,12=>2,13=>1,14=>0);
                $no_jiang_judge_arr = array(0=>0,1=>2,2=>1,3=>0,4=>2,5=>1,6=>0,7=>2,8=>1,9=>0,10=>2,11=>1,12=>0);
                //循环万到风
                for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
                {
                    /*var_dump(__LINE__);
                    var_dump('当前花色为将对'.$i);*/
                    if(0 == $this->m_sPlayer[$chair]->card[$i][0])
                    {
                        /*var_dump(__LINE__);
                        var_dump($i.'此门牌没有牌');*/
                        if ($i!=ConstConfig::PAI_TYPE_WAN)
                        {
                            continue;
                        }


                    }
                    /*var_dump(__LINE__);
                    var_dump($i.'此门牌有牌');*/

                    $qing_arr = array();
                    $bQing = false;
                    $qingyise = false;
                    $is_hu_data = false;
                    $yitiaolong_tmp = false;

                    $jiang_type = $i;	//假设将牌是某一门
                    $need_fanhun = 0;	//需要红中翻混个数
                    $replace_fanhun = array(0,0,0,0);
                    for($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
                    {
                        if(0 == $this->m_sPlayer[$chair]->card[$j][0])
                        {
                            continue;
                        }
                        $pai_num = $this->m_sPlayer[$chair]->card[$j][0];	//一门牌个数


                        //有将牌的那一门
                        if($jiang_type == $j)
                        {
                            if($jiang_judge_arr[$pai_num] > 0)
                            {
                                $need_fanhun += $jiang_judge_arr[$pai_num];
                                $replace_fanhun[$j] += $jiang_judge_arr[$pai_num];
                            }
                            else
                            {
                                //如果牌数量正合适，去判断是否胡牌数组里有
                                $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$j], 1)));
                                if($j == ConstConfig::PAI_TYPE_FENG)
                                {
                                    if(!isset(ConstConfig::$hu_data_feng[$key]) || (ConstConfig::$hu_data_feng[$key] & 1 )!= 1)
                                    {
                                        $need_fanhun += 3;
                                        $replace_fanhun[$j] += 3;
                                    }
                                }
                                else
                                {
                                    if(!isset(ConstConfig::$hu_data[$key]) || (ConstConfig::$hu_data[$key] & 1 )!= 1)
                                    {
                                        $need_fanhun += 3;
                                        $replace_fanhun[$j] += 3;
                                    }
                                }
                            }
                        }
                        else//没有将牌的门处理
                        {
                            if($no_jiang_judge_arr[$pai_num] > 0)
                            {
                                $need_fanhun += $no_jiang_judge_arr[$pai_num];
                                $replace_fanhun[$j] += $no_jiang_judge_arr[$pai_num];
                            }
                            else
                            {
                                //如果牌数量正合适，去判断是否胡牌数组里有
                                $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$j], 1)));
                                if($j == ConstConfig::PAI_TYPE_FENG)
                                {
                                    if(!isset(ConstConfig::$hu_data_feng[$key]) || (ConstConfig::$hu_data_feng[$key] & 1) != 1)
                                    {
                                        $need_fanhun += 3;
                                        $replace_fanhun[$j] += 3;
                                    }
                                }
                                else
                                {
                                    if(!isset(ConstConfig::$hu_data[$key]) || (ConstConfig::$hu_data[$key] & 1 )!= 1)
                                    {
                                        $need_fanhun += 3;
                                        $replace_fanhun[$j] += 3;
                                    }
                                }
                            }
                        }
                        if($need_fanhun > $fanhun_num)
                        {
                            /*var_dump(__LINE__);
                            var_dump('需要的混牌大于有的混牌');*/
                            break;
                        }
                    }

                    if($need_fanhun <= $fanhun_num)
                    {
                        $score_four = 0;
                        $result_four = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
                        $temp_score_four = 0 ;
                        $result_three = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);


                        $is_check_hu = false;

                        /*var_dump(__LINE__);
                        var_dump('需要的混牌小于等于有的混牌');*/

                        for ($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
                        {
                            //重置1 4情况的红中
                            /*if($need_fanhun == 1 && $fanhun_num == 4 || $need_fanhun == 1 && $fanhun_num == 7)
                            {
                                foreach ($replace_fanhun as $type => $num)
                                {
                                    if($num == 1)
                                    {
                                        $replace_fanhun[$type] = $fanhun_num;
                                    }
                                }
                            }*/

                            //重置0 3 情况
                            /*if($need_fanhun == 0 && $fanhun_num == 3 || $need_fanhun == 0 && $fanhun_num == 6)
                            {
                                foreach ( $replace_fanhun as $type => $num )
                                {
                                    if( $this->m_sPlayer[$chair]->card[$type][0] > 0 )
                                    {
                                        $replace_fanhun[$type] = $fanhun_num;
                                        break;
                                    }
                                }
                            }*/

                            if($fanhun_num == $need_fanhun && $is_check_hu)
                            {
                                continue;
                            }
                            $is_check_hu = true;

                            $tmp_replace_fanhun = $replace_fanhun;
                            $tmp_replace_fanhun[$j] += ($fanhun_num - $need_fanhun);
                            /*var_dump(__LINE__.'当前替换数组');
                            var_dump($tmp_replace_fanhun);*/


                            $score_three = 0;
                            $result_three = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
                            $temp_score_three = 0 ;
                            $result_two = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
                            //校验胡(每个花色需要的补牌)
                            foreach ($tmp_replace_fanhun as $type => $num)
                            {

                                //不需要补牌
                                /*if(0 == $num)
                                {
                                    if(ConstConfig::PAI_TYPE_FENG  != $type)
                                    {
                                        $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
                                        if(isset(ConstConfig::$hu_data[$key]) && (ConstConfig::$hu_data[$key] & 1) == 1)
                                        {
                                            if($this->m_rule->is_yitiaolong_fan && (ConstConfig::$hu_data[$key] & 256) == 256 && !$yitiaolong_tmp)  //判断一条龙
                                            {
                                                $yitiaolong_tmp = true;
                                            }
                                        }
                                    }
                                    continue;
                                }*/

                                /*var_dump(__LINE__);
                                var_dump('当前花色'.$type);*/

                                $score_two = 0;
                                $result_one = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
                                $temp_score_two = 0 ;
                                $result_two = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);

                                if(ConstConfig::PAI_TYPE_FENG  == $type)
                                {
                                    $is_hu_data = false;

                                    foreach (ConstConfig::$hu_data_insert_feng[$num] as $insert_arr)
                                    {
                                        $score_one = 0;
                                        $result_one = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
                                        $temp_score_one = 0 ;
                                        $temp_result_one = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);

                                        foreach ($insert_arr as $insert_item)
                                        {
                                            $this->m_sPlayer[$chair]->card[$type][$insert_item] += 1;
                                        }
                                        $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
                                        if(isset(ConstConfig::$hu_data_feng[$key]) && (ConstConfig::$hu_data_feng[$key] & 1) == 1)
                                        {
                                            $is_hu_data = true;
                                            /*var_dump(__LINE__);
                                            var_dump('可以胡牌');*/
                                            if ($type==$jiang_type)
                                            {
                                                if (in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card)&&$fanhun_num>1)
                                                {
                                                    if ($num>=2)
                                                    {
                                                        $temp_jiang_arr=array();
                                                        $is_insertcard = false;
                                                        $temp_insert_item = 0;
                                                        foreach ($insert_arr as $insert_item)
                                                        {
                                                            if($insert_item == $temp_insert_item)
                                                            {
                                                                $is_insertcard = true;
                                                                $temp_jiang_arr[] = $insert_item;
                                                            }
                                                            else
                                                            {
                                                                $temp_insert_item = $insert_item;
                                                            }
                                                        }
                                                        if ($is_insertcard)
                                                        {
                                                            foreach ($temp_jiang_arr as $jiang_dui)
                                                            {
                                                                /*var_dump(__LINE__);
                                                                var_dump($jiang_dui);*/
                                                                if($this->judge_32type($chair,array($jiang_dui,$jiang_dui),$type))
                                                                {
                                                                    $temp_result_one['ATTACHED_HU_HUNDIAO'] = true;
                                                                }
                                                            }

                                                        }
                                                    }
                                                }
                                                if(!in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card)&&$fanhun_num>0)
                                                {
                                                    $temp_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
                                                    $temp_index = $this->m_HuCurt[$chair]->card%16;
                                                    if ($temp_type==$type)
                                                    {
                                                        $is_insertcard = false;
                                                        foreach ($insert_arr as $insert_item)
                                                        {
                                                            if($insert_item ==$temp_index)
                                                            {
                                                                /*var_dump(__LINE__);
                                                                var_dump('插入了将牌');*/
                                                                $is_insertcard = true;
                                                            }
                                                        }
                                                        if ($is_insertcard)
                                                        {
                                                            $this->m_sPlayer[$chair]->card[$temp_type][$temp_index] -= 2;
                                                            $temp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
                                                            /*var_dump(__LINE__);
                                                            var_dump($temp_key);*/
                                                            if (isset(ConstConfig::$hu_data_feng[$temp_key]) && (ConstConfig::$hu_data_feng[$temp_key] & 1) == 1)
                                                            {
                                                                /*var_dump(__LINE__);
                                                                var_dump('混吊ok');*/
                                                                $temp_result_one['ATTACHED_HU_HUNDIAO'] = true;
                                                            }
                                                            $this->m_sPlayer[$chair]->card[$temp_type][$temp_index] += 2;

                                                        }

                                                    }


                                                }
                                            }
                                            foreach ($insert_arr as $insert_item)
                                            {
                                                $this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
                                            }
                                            //计算分数并赋值(平胡1分)
                                            $temp_score_one =  1;
                                            if ($temp_result_one['ATTACHED_HU_BENHUNLONG'])
                                            {
                                                $temp_score_one *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                                            }
                                            if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                                            {
                                                $temp_score_one *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                                            }
                                            if ($temp_result_one['ATTACHED_HU_WUKUI'])
                                            {
                                                $temp_score_one *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                                            }
                                            if ($temp_result_one['ATTACHED_HU_HUNDIAO'])
                                            {
                                                $temp_score_one *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                                            }
                                            if ($temp_score_one > $score_one)
                                            {
                                                $score_one = $temp_score_one;
                                                $result_one = $temp_result_one;
                                            }
                                            /*var_dump(__LINE__);
                                            var_dump($result_one);*/
                                        }
                                        else
                                        {
                                            foreach ($insert_arr as $insert_item)
                                            {
                                                $this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
                                            }
                                        }
                                        //计算分数并赋值(平胡1分)
                                        $temp_score_two =  1;
                                        if ($result_one['ATTACHED_HU_BENHUNLONG'])
                                        {
                                            $temp_score_two *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                                        }
                                        if ($result_one['ATTACHED_HU_YITIAOLONG'])
                                        {
                                            $temp_score_two *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                                        }
                                        if ($result_one['ATTACHED_HU_WUKUI'])
                                        {
                                            $temp_score_two *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                                        }
                                        if ($result_one['ATTACHED_HU_HUNDIAO'])
                                        {
                                            $temp_score_two *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                                        }
                                        if ($temp_score_two > $score_two)
                                        {
                                            $score_two = $temp_score_two;
                                            $result_two = $result_one;
                                        }
                                        /*var_dump(__LINE__);
                                        var_dump($result_two);*/
                                    }

                                    if(!$is_hu_data)
                                    {
                                        break;
                                    }

                                    if ($result_two['ATTACHED_HU_BENHUNLONG'])
                                    {
                                        $result_three['ATTACHED_HU_BENHUNLONG'] = true;
                                        $result_three['ATTACHED_HU_YITIAOLONG'] = false;
                                    }
                                    if($result_two['ATTACHED_HU_YITIAOLONG'])
                                    {
                                        $result_three['ATTACHED_HU_BENHUNLONG'] = false;
                                        $result_three['ATTACHED_HU_YITIAOLONG'] = true;
                                    }
                                    if ($result_two['ATTACHED_HU_WUKUI'])
                                    {
                                        $result_three['ATTACHED_HU_WUKUI'] = true;
                                    }
                                    if ($result_two['ATTACHED_HU_HUNDIAO'])
                                    {
                                        $result_three['ATTACHED_HU_HUNDIAO'] = true;
                                    }

                                    //计算分数并赋值(平胡1分)
                                    /*$temp_score_three =  1;
                                    if ($result_two['ATTACHED_HU_BENHUNLONG'])
                                    {
                                        $temp_score_three *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                                    }
                                    if ($result_two['ATTACHED_HU_YITIAOLONG'])
                                    {
                                        $temp_score_three *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                                    }
                                    if ($result_two['ATTACHED_HU_WUKUI'])
                                    {
                                        $temp_score_three *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                                    }
                                    if ($result_two['ATTACHED_HU_HUNDIAO'])
                                    {
                                        $temp_score_three *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                                    }
                                    if ($temp_score_three > $score_three)
                                    {
                                        $score_three = $temp_score_three;
                                        $result_three = $result_two;
                                    }*/
                                }
                                else//不是风牌
                                {
                                    $is_hu_data = false;

                                    foreach (ConstConfig::$hu_data_insert[$num] as $insert_arr)
                                    {
                                        $score_one = 0;
                                        $result_one = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
                                        $temp_score_one = 0 ;
                                        $temp_result_one = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
                                        //定义分数
                                        foreach ($insert_arr as $insert_item)
                                        {
                                            $this->m_sPlayer[$chair]->card[$type][$insert_item] += 1;
                                        }
                                        $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));

                                        if(isset(ConstConfig::$hu_data[$key]) && (ConstConfig::$hu_data[$key] & 1) == 1)
                                        {
                                            $is_hu_data = true;
                                            /*var_dump(__LINE__);
                                            var_dump('可以胡牌');*/
                                            if($type==ConstConfig::PAI_TYPE_WAN)
                                            {
                                                /*var_dump(__LINE__);
                                                var_dump($this->m_sPlayer[$chair]->card[$type]);*/
                                                $insert_5 = false;
                                                if (in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card))
                                                {
                                                    foreach ($insert_arr as $insert_item)
                                                    {
                                                        if($insert_item==5)
                                                        {
                                                            $insert_5 = true;
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    if($this->m_HuCurt[$chair]->card == 5)
                                                    {
                                                        $insert_5 = true;
                                                    }
                                                }
                                                if ($insert_5)
                                                {
                                                    //捉五魁
                                                    $temp_result_one['ATTACHED_HU_WUKUI'] = $this->judge_32_wukui($chair);
                                                }


                                                /*var_dump(__LINE__);
                                                var_dump('是否捉五魁'.$temp_result_one['ATTACHED_HU_WUKUI']);*/
                                                //吊五万
                                                if(!$temp_result_one['ATTACHED_HU_WUKUI'] && $type==$jiang_type)
                                                {
                                                    if ($insert_5)
                                                    {
                                                        $temp_result_one['ATTACHED_HU_WUKUI'] = $this->judge_32_diaowukui($chair);
                                                    }
                                                    /*var_dump(__LINE__);
                                                    var_dump('是否捉五魁'.$temp_result_one['ATTACHED_HU_WUKUI']);*/

                                                }
                                            }
                                            if ($type==$jiang_type)
                                            {
                                                if (in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card)&&$fanhun_num>1)
                                                {
                                                    if ($num>=2)
                                                    {
                                                        $temp_jiang_arr=array();
                                                        $is_insertcard = false;
                                                        $temp_insert_item = 0;
                                                        foreach ($insert_arr as $insert_item)
                                                        {
                                                            if($insert_item == $temp_insert_item)
                                                            {
                                                                $is_insertcard = true;
                                                                $temp_jiang_arr[] = $insert_item;
                                                            }
                                                            else
                                                            {
                                                                $temp_insert_item = $insert_item;
                                                            }
                                                        }
                                                        if ($is_insertcard)
                                                        {
                                                            foreach ($temp_jiang_arr as $jiang_dui)
                                                            {
                                                                /*var_dump(__LINE__);
                                                                var_dump($jiang_dui);*/
                                                                if($this->judge_32type($chair,array($jiang_dui,$jiang_dui),$type))
                                                                {
                                                                    $temp_result_one['ATTACHED_HU_HUNDIAO'] = true;
                                                                }
                                                            }

                                                        }
                                                    }
                                                }
                                                if(!in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card)&&$fanhun_num>0)
                                                {
                                                    $temp_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
                                                    $temp_index = $this->m_HuCurt[$chair]->card%16;
                                                    if ($temp_type==$type)
                                                    {
                                                        $is_insertcard = false;
                                                        foreach ($insert_arr as $insert_item)
                                                        {
                                                            if($insert_item ==$temp_index)
                                                            {
                                                                $is_insertcard = true;
                                                            }
                                                        }
                                                        if ($is_insertcard)
                                                        {
                                                            $this->m_sPlayer[$chair]->card[$temp_type][$temp_index] -= 2;
                                                            $temp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
                                                            if (isset(ConstConfig::$hu_data[$temp_key]) && (ConstConfig::$hu_data[$temp_key] & 1) == 1)
                                                            {
                                                                $temp_result_one['ATTACHED_HU_HUNDIAO'] = true;
                                                            }
                                                            $this->m_sPlayer[$chair]->card[$temp_type][$temp_index] += 2;

                                                        }

                                                    }


                                                }
                                            }


                                            foreach ($insert_arr as $insert_item)
                                            {
                                                $this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
                                            }


                                            if((ConstConfig::$hu_data[$key] & 256) == 256)  //判断一条龙
                                            {
                                                /*var_dump(__LINE__);
                                                var_dump('存在一条龙');*/
                                                if($pai_type==$type)
                                                {
                                                    $temp_result_one['ATTACHED_HU_BENHUNLONG']=true;
                                                }
                                                else
                                                {
                                                    $temp_result_one['ATTACHED_HU_YITIAOLONG']=true;
                                                }
                                            }
                                            //计算分数并赋值(平胡1分)
                                            $temp_score_one =  1;
                                            if ($temp_result_one['ATTACHED_HU_BENHUNLONG'])
                                            {
                                                $temp_score_one *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                                            }
                                            if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                                            {
                                                $temp_score_one *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                                            }
                                            if ($temp_result_one['ATTACHED_HU_WUKUI'])
                                            {
                                                $temp_score_one *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                                            }
                                            if ($temp_result_one['ATTACHED_HU_HUNDIAO'])
                                            {
                                                $temp_score_one *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                                            }
                                            if ($temp_score_one > $score_one)
                                            {
                                                $score_one = $temp_score_one;
                                                $result_one = $temp_result_one;
                                            }
                                            /*var_dump(__LINE__);
                                            var_dump($result_one);*/
                                        }
                                        else
                                        {
                                            foreach ($insert_arr as $insert_item)
                                            {
                                                $this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
                                            }
                                        }
                                        //计算分数并赋值(平胡1分)
                                        $temp_score_two =  1;
                                        if ($result_one['ATTACHED_HU_BENHUNLONG'])
                                        {
                                            $temp_score_two *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                                        }
                                        if ($result_one['ATTACHED_HU_YITIAOLONG'])
                                        {
                                            $temp_score_two *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                                        }
                                        if ($result_one['ATTACHED_HU_WUKUI'])
                                        {
                                            $temp_score_two *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                                        }
                                        if ($result_one['ATTACHED_HU_HUNDIAO'])
                                        {
                                            $temp_score_two *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                                        }
                                        if ($temp_score_two > $score_two)
                                        {
                                            $score_two = $temp_score_two;
                                            $result_two = $result_one;
                                        }

                                    }
                                    if(!$is_hu_data)
                                    {
                                        break;
                                    }


                                    if ($result_two['ATTACHED_HU_BENHUNLONG'])
                                    {
                                        $result_three['ATTACHED_HU_BENHUNLONG'] = true;
                                        $result_three['ATTACHED_HU_YITIAOLONG'] = false;
                                    }
                                    if($result_two['ATTACHED_HU_YITIAOLONG'])
                                    {
                                        $result_three['ATTACHED_HU_BENHUNLONG'] = false;
                                        $result_three['ATTACHED_HU_YITIAOLONG'] = true;
                                    }
                                    if ($result_two['ATTACHED_HU_WUKUI'])
                                    {
                                        $result_three['ATTACHED_HU_WUKUI'] = true;
                                    }
                                    if ($result_two['ATTACHED_HU_HUNDIAO'])
                                    {
                                        $result_three['ATTACHED_HU_HUNDIAO'] = true;
                                    }

                                    /*//计算分数并赋值(平胡1分)
                                    $temp_score_three =  1;
                                    if ($result_two['ATTACHED_HU_BENHUNLONG'])
                                    {
                                        $temp_score_three *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                                    }
                                    if ($result_two['ATTACHED_HU_YITIAOLONG'])
                                    {
                                        $temp_score_three *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                                    }
                                    if ($result_two['ATTACHED_HU_WUKUI'])
                                    {
                                        $temp_score_three *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                                    }
                                    if ($result_two['ATTACHED_HU_HUNDIAO'])
                                    {
                                        $temp_score_three *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                                    }
                                    if ($temp_score_three > $score_three)
                                    {
                                        if ($result_three['ATTACHED_HU_WUKUI'] && !$result_two['ATTACHED_HU_WUKUI'])
                                        {
                                            $score_three = $temp_score_three * 2;
                                            $result_three = $result_two;
                                            $result_three['ATTACHED_HU_WUKUI'] = true;
                                        }
                                        else
                                        {
                                            $score_three = $temp_score_three;
                                            $result_three = $result_two;
                                        }
                                    }*/

                                }
                                /*var_dump(__LINE__);
                                var_dump($result_three);*/
                            }

                            if($is_hu_data)
                            {
                                /*var_dump(__LINE__);
                                var_dump('4门牌都可以胡');*/
                                //倒牌
                                if($this->m_rule->is_qingyise_fan)
                                {
                                    for($k=0; $k<$this->m_sStandCard[$chair]->num; $k++)
                                    {
                                        $stand_pai_type = $this->_get_card_type( $this->m_sStandCard[$chair]->first_card[$k] );
                                        $qing_arr[] = $stand_pai_type;
                                    }

                                    $bQing = ( 1 == count(array_unique($qing_arr)) && ConstConfig::PAI_TYPE_FENG != $qing_arr[0]);
                                    if($bQing && !$qingyise)
                                    {
                                        $qingyise = true;
                                    }
                                }
                                if($this->m_rule->is_yitiaolong_fan && $yitiaolong_tmp && !$yitiaolong)
                                {
                                    $yitiaolong = $yitiaolong_tmp;
                                }
                                /*var_dump(__LINE__);
                                var_dump($result_three);*/
                                $result_three['HU_TYPE'] = self::HU_TYPE_PINGHU;
                                //计算分数并赋值(平胡1分)
                                $temp_score_four =  1;
                                if ($result_three['ATTACHED_HU_BENHUNLONG'])
                                {
                                    $temp_score_four *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                                }
                                if ($result_three['ATTACHED_HU_YITIAOLONG'])
                                {
                                    $temp_score_four *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                                }
                                if ($result_three['ATTACHED_HU_WUKUI'])
                                {
                                    $temp_score_four *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                                }
                                if ($result_three['ATTACHED_HU_HUNDIAO'])
                                {
                                    $temp_score_four *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                                }
                                if ($temp_score_four > $score_four)
                                {
                                    $score_four = $temp_score_four;
                                    $result_four = $result_three;
                                }
                                /*var_dump(__line__);
                                var_dump($result_four);*/


                            }
                            if ($result_four['HU_TYPE'] != self::HU_TYPE_FENGDING_TYPE_INVALID)
                            {
                                $temp_score_five =  1;
                                if ($result_four['ATTACHED_HU_BENHUNLONG'])
                                {
                                    $temp_score_five *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                                }
                                if ($result_four['ATTACHED_HU_YITIAOLONG'])
                                {
                                    $temp_score_five *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                                }
                                if ($result_four['ATTACHED_HU_WUKUI'])
                                {
                                    $temp_score_five *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                                }
                                if ($result_four['ATTACHED_HU_HUNDIAO'])
                                {
                                    $temp_score_five *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                                }
                                if ($temp_score_five > $score_five)
                                {
                                    $score_five = $temp_score_five;
                                    $temp_result = $result_four;
                                }
                            }
                        }

                        /*var_dump(__LINE__.'222222222222222222222222222222222222222222');
                        var_dump($temp_result);*/

                    }
                    else
                    {
                        /*var_dump(__LINE__);
                        var_dump('需要的混牌大于有的混牌');
                        var_dump('下一门牌的混牌大于有的混牌');*/
                        continue;
                    }
                }
                //拿回所有混子
                foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
                {
                    $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                    $one_fanhun_card = $fanhun_card%16;       //翻混牌

                    $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                    $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
                }



                if ($temp_result['HU_TYPE'] != self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    //计算番数
                    $temp_score =  self::$hu_type_arr[$temp_result['HU_TYPE']][1];
                    if ($temp_result['ATTACHED_HU_BENHUNLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_WUKUI'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                    }
                    if ($temp_result['ATTACHED_HU_HUNDIAO'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                    }
                    if ($temp_score > $score)
                    {
                        $score = $temp_score;
                        $result = $temp_result;
                    }
                }
            }
        }
        /*var_dump(__LINE__);
        var_dump($result);*/
        return $result;















        if ($fanhun_num == 1)
        {
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0]-=$one_fanhun_num;
            }
            if (in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card))
            {

                $result=$this->judge_hu_type_hucard_hun($chair);
            }
            else
            {

                $result=$this->judge_hu_type_fanhun_one($chair);
                //判断混吊
                //去掉胡的牌
                if ($this->judge_32type($chair,array($this->m_HuCurt[$chair]->card),$this->_get_card_type($this->m_HuCurt[$chair]->card)))
                {
                    $result['ATTACHED_HU_HUNDIAO'] = true;
                }
            }
            //恢复删除牌
            //拿回所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
            }
            return $result;
        }
        if ($fanhun_num == 2)
        {
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0]-=$one_fanhun_num;
            }

            $result = $this->judge_hu_type_fanhun_two($chair);
            //判断混吊
            //去掉胡的牌
            if (!$result['ATTACHED_HU_HUNDIAO'])
            {
                if ($this->judge_32type($chair,array(),ConstConfig::PAI_TYPE_WAN) && $this->judge_32type($chair,array(),ConstConfig::PAI_TYPE_TIAO) && $this->judge_32type($chair,array(),ConstConfig::PAI_TYPE_TONG) && $this->judge_32type($chair,array(),ConstConfig::PAI_TYPE_FENG))
                {
                    $result['ATTACHED_HU_HUNDIAO'] = true;
                }
            }
            //恢复删除牌
            //拿回所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
            }
            return $result;
        }
        if ($fanhun_num == 3)
        {
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0]-=$one_fanhun_num;
            }

            $result = $this->judge_hu_type_fanhun_three($chair);

            //恢复删除牌
            //拿回所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
            }
            return $result;
        }
        if ($fanhun_num == 4)
        {
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0]-=$one_fanhun_num;
            }

            $result = $this->judge_hu_type_fanhun_four($chair);

            //恢复删除牌
            //拿回所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
            }
            return $result;
        }
        if ($fanhun_num == 5)
        {
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0]-=$one_fanhun_num;
            }

            $result = $this->judge_hu_type_fanhun_five($chair);

            //恢复删除牌
            //拿回所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
            }
            return $result;
        }
        if ($fanhun_num == 6)
        {
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0]-=$one_fanhun_num;
            }

            $result = $this->judge_hu_type_fanhun_six($chair);

            //恢复删除牌
            //拿回所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
            }
            return $result;
        }
        if ($fanhun_num == 7)
        {
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0]-=$one_fanhun_num;
            }

            $result = $this->judge_hu_type_fanhun_seven($chair);

            //恢复删除牌
            //拿回所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
            }
            return $result;
        }
    }

    //判断类型判断 七个混子
    public function judge_hu_type_fanhun_seven($chair,$lastcard = 0)
    {
        //定义所有牌数组
        $allCard=array(1,2,3,4,5,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,49,50,51,52,53,54,55);
        $score = 0;
        $result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
        for ($i=$lastcard; $i <34 ; $i++)
        {
            if($this->_list_insert($chair, $allCard[$i]))
            {
                $temp_result = $this->judge_hu_type_fanhun_six($chair,$i);
                $this->_list_delete($chair, $allCard[$i]);
                if ($temp_result['HU_TYPE']!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    //计算番数
                    $temp_score =  self::$hu_type_arr[$temp_result['HU_TYPE']][1];
                    if ($temp_result['ATTACHED_HU_BENHUNLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_WUKUI'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                    }
                    if ($temp_result['ATTACHED_HU_HUNDIAO'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                    }
                    if ($temp_score > $score)
                    {
                        $score = $temp_score;
                        $result = $temp_result;
                    }
                }
            }
        }
        return $result;
    }

    //判断类型判断 六个混子
    public function judge_hu_type_fanhun_six($chair,$lastcard = 0)
    {
        //定义所有牌数组
        $allCard=array(1,2,3,4,5,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,49,50,51,52,53,54,55);
        $score = 0;
        $result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
        for ($i=$lastcard; $i <34 ; $i++)
        {
            if($this->_list_insert($chair, $allCard[$i]))
            {
                $temp_result = $this->judge_hu_type_fanhun_five($chair,$i);
                $this->_list_delete($chair, $allCard[$i]);
                if ($temp_result['HU_TYPE']!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    //计算番数
                    $temp_score =  self::$hu_type_arr[$temp_result['HU_TYPE']][1];
                    if ($temp_result['ATTACHED_HU_BENHUNLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_WUKUI'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                    }
                    if ($temp_result['ATTACHED_HU_HUNDIAO'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                    }
                    if ($temp_score > $score)
                    {
                        $score = $temp_score;
                        $result = $temp_result;
                    }
                }
            }
        }
        return $result;
    }

    //判断类型判断 五个混子
    public function judge_hu_type_fanhun_five($chair,$lastcard = 0)
    {
        //定义所有牌数组
        $allCard=array(1,2,3,4,5,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,49,50,51,52,53,54,55);
        $score = 0;
        $result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
        for ($i=$lastcard; $i <34 ; $i++)
        {
            if($this->_list_insert($chair, $allCard[$i]))
            {
                $temp_result = $this->judge_hu_type_fanhun_four($chair,$i);
                $this->_list_delete($chair, $allCard[$i]);
                if ($temp_result['HU_TYPE']!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    //计算番数
                    $temp_score =  self::$hu_type_arr[$temp_result['HU_TYPE']][1];
                    if ($temp_result['ATTACHED_HU_BENHUNLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_WUKUI'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                    }
                    if ($temp_result['ATTACHED_HU_HUNDIAO'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                    }
                    if ($temp_score > $score)
                    {
                        $score = $temp_score;
                        $result = $temp_result;
                    }
                }
            }
        }
        return $result;
    }

    //判断类型判断 四个混子
    public function judge_hu_type_fanhun_four($chair,$lastcard = 0)
    {
        //定义所有牌数组
        $allCard=array(1,2,3,4,5,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,49,50,51,52,53,54,55);
        $score = 0;
        $result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_WUKUI' => false);
        for ($i=$lastcard; $i <34 ; $i++)
        {
            if($this->_list_insert($chair, $allCard[$i]))
            {
                $temp_result = $this->judge_hu_type_fanhun_three($chair,$i);
                $this->_list_delete($chair, $allCard[$i]);
                if ($temp_result['HU_TYPE']!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    //计算番数
                    $temp_score =  self::$hu_type_arr[$temp_result['HU_TYPE']][1];
                    if ($temp_result['ATTACHED_HU_BENHUNLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_WUKUI'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                    }
                    if ($temp_result['ATTACHED_HU_HUNDIAO'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                    }
                    if ($temp_score > $score)
                    {
                        $score = $temp_score;
                        $result = $temp_result;
                    }
                }
            }
        }
        return $result;
    }

    //判断类型判断 三个混子
    public function judge_hu_type_fanhun_three($chair,$lastcard = 0)
    {
        //定义所有牌数组
        $allCard=array(1,2,3,4,5,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,49,50,51,52,53,54,55);
        $score = 0;
        $result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
        for ($i=$lastcard; $i <34 ; $i++)
        {
            if($this->_list_insert($chair, $allCard[$i]))
            {
                $temp_result = $this->judge_hu_type_fanhun_two($chair,$i);
                //判断混吊
                //去掉胡的牌
                if (!$temp_result['ATTACHED_HU_HUNDIAO'])
                {
                    if ($this->judge_32type($chair,array($this->m_HuCurt[$chair]->card),$this->_get_card_type($this->m_HuCurt[$chair]->card)))
                    {
                        $result['ATTACHED_HU_WUKUI'] = true;
                    }
                }
                $this->_list_delete($chair, $allCard[$i]);
                if ($temp_result['HU_TYPE']!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    //计算番数
                    $temp_score =  self::$hu_type_arr[$temp_result['HU_TYPE']][1];
                    if ($temp_result['ATTACHED_HU_BENHUNLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_WUKUI'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                    }
                    if ($temp_result['ATTACHED_HU_HUNDIAO'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                    }
                    if ($temp_score > $score)
                    {
                        $score = $temp_score;
                        $result = $temp_result;
                    }
                }
            }
        }
        return $result;
    }

    //判断类型判断 两个混子
    public function judge_hu_type_fanhun_two($chair,$lastcard = 0)
    {
        //定义所有牌数组
        $allCard=array(1,2,3,4,5,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,49,50,51,52,53,54,55);
        $score = 0;
        $result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
        for ($i=$lastcard; $i <34 ; $i++)
        {
            if($this->_list_insert($chair, $allCard[$i]))
            {
                if (in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card))
                {
                    $temp_result = $this->judge_hu_type_hucard_hun($chair);
                }
                else
                {
                    $temp_result = $this->judge_hu_type_fanhun_one($chair,$i);
                    //判断混吊
                    //去掉胡的牌
                    if ($this->judge_32type($chair,array($this->m_HuCurt[$chair]->card),$this->_get_card_type($this->m_HuCurt[$chair]->card)))
                    {
                        $temp_result['ATTACHED_HU_HUNDIAO'] = true;
                    }
                }
                $this->_list_delete($chair, $allCard[$i]);
                if ($temp_result['HU_TYPE']!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    //计算番数
                    $temp_score =  self::$hu_type_arr[$temp_result['HU_TYPE']][1];
                    if ($temp_result['ATTACHED_HU_BENHUNLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_WUKUI'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                    }
                    if ($temp_result['ATTACHED_HU_HUNDIAO'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_HUNDIAO][1];
                    }
                    if ($temp_score > $score)
                    {
                        $score = $temp_score;
                        $result = $temp_result;
                    }
                }
            }
        }
        return $result;
    }

    //判断类型判断 一个混子
    public function judge_hu_type_fanhun_one($chair,$lastcard = 0)
    {
        //定义所有牌数组
        $allCard=array(1,2,3,4,5,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,49,50,51,52,53,54,55);
        $score = 0;
        $result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
        for ($i=$lastcard; $i <34 ; $i++)
        {
            if($this->_list_insert($chair, $allCard[$i]))
            {
                $temp_result = $this->judge_hu_type32($chair,$this->m_HuCurt[$chair]->card);
                $this->_list_delete($chair, $allCard[$i]);
                if ($temp_result['HU_TYPE']!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    //计算番数
                    $temp_score =  self::$hu_type_arr[$temp_result['HU_TYPE']][1];
                    if ($temp_result['ATTACHED_HU_BENHUNLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_WUKUI'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                    }
                    if ($temp_score > $score)
                    {
                        $score = $temp_score;
                        $result = $temp_result;
                    }
                }
            }
        }
        return $result;
    }

    //判断类型判断 胡的牌是混牌
    public function judge_hu_type_hucard_hun($chair)
    {
        //定义所有牌数组
        $allCard=array(1,2,3,4,5,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,49,50,51,52,53,54,55);
        $score = 0;
        $result = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_WUKUI' => false);
        foreach ($allCard as $value)
        {
            if($this->_list_insert($chair, $value))
            {
                $temp_result = $this->judge_hu_type32($chair,$value);
                $this->_list_delete($chair, $value);
                if ($temp_result['HU_TYPE']!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    //计算番数
                    $temp_score =  self::$hu_type_arr[$temp_result['HU_TYPE']][1];
                    if ($temp_result['ATTACHED_HU_YITIAOLONG'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                    }
                    if ($temp_result['ATTACHED_HU_WUKUI'])
                    {
                        $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
                    }
                    if ($temp_score > $score)
                    {
                        $result = $temp_result;
                    }
                }
            }
        }
        return $result;
    }

    //判断类型判断 没有混子
    public function judge_hu_type($chair,$insertcard)
    {
        $temp_response = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
        $response = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);

        $jiang_arr = array();
        $qidui_arr = array();
        $shisanyao_arr = array();
        $luanyao_num = 0;

        $bType32 = false;
        $bQiDui = false;
        $bShiSanYao = false;    //13幺
        $bLuanYao = false;		//乱幺

        $is_yitiaolong = false;   //一条龙
        $is_zhuowukui = false;    //捉五魁


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
                $luanyao_num += $this->m_sPlayer[$chair]->card[$i][0];
            }
            if (ConstConfig::PAI_TYPE_FENG != $i)
            {
                $luanyao_num += $this->m_sPlayer[$chair]->card[$i][1];
                $luanyao_num += $this->m_sPlayer[$chair]->card[$i][9];
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
                    if ($i==$this->_get_card_type($this->m_fan_hun_card))
                    {
                        $temp_response['ATTACHED_HU_BENHUNLONG']=true;
                    }
                    else
                    {
                        $temp_response['ATTACHED_HU_YITIAOLONG']=true;
                    }

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
            if (in_array($this->m_sStandCard[$chair]->first_card[$i], array(1,9,17,25,33,41,49,50,51,52,53,54,55)))
            {
                $luanyao_num +=3;
            }
        }

        $bType32 = (32 == array_sum($jiang_arr));
        $bQiDui = !array_keys($qidui_arr, 0);
        $bPengPeng = !array_keys($pengpeng_arr, 0);
        $bShiSanYao = !array_keys($shisanyao_arr, 0);
        $bLuanYao = ($luanyao_num==14);

        //基本牌型的处理///////////////////////////////
        if(!$bType32 && !$bQiDui && !$bLuanYao)
        {
            $response['HU_TYPE'] = self::HU_TYPE_FENGDING_TYPE_INVALID;
            return $response;
        }
        $score = 0;
        if($bType32)
        {
            //捉五魁
            $temp_response['ATTACHED_HU_WUKUI'] = $this->_is_wukui($chair,$insertcard);

            //吊五万
            if(!$temp_response['ATTACHED_HU_WUKUI'])
            {
                $temp_response['ATTACHED_HU_WUKUI'] = $this->_is_diaowuwan($chair, $insertcard);
            }
            $temp_response['HU_TYPE'] = self::HU_TYPE_PINGHU;
            //计算分数是不是比最大
            $temp_score =  self::$hu_type_arr[$temp_response['HU_TYPE']][1];
            if ($temp_response['ATTACHED_HU_BENHUNLONG'])
            {
                $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
            }
            if ($temp_response['ATTACHED_HU_YITIAOLONG'])
            {
                $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
            }
            if ($temp_response['ATTACHED_HU_WUKUI'])
            {
                $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
            }
            if ($temp_score > $score)
            {
                $score = $temp_score;
                $response = $temp_response;
            }
        }


        if($bQiDui && $this->m_rule->is_qidui_fan)				//判断七对，可能同时是32牌型
        {
            //吊五万
            if(($insertcard == 5) && ($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_WAN][5] >= 2))
            {
                $temp_response['ATTACHED_HU_WUKUI']=true;
            }
            $temp_response['HU_TYPE'] = self::HU_TYPE_QIDUI ;
            //计算分数是不是比最大
            $temp_score =  self::$hu_type_arr[$temp_response['HU_TYPE']][1];
            if ($temp_response['ATTACHED_HU_WUKUI'])
            {
                $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
            }
            if ($temp_score > $score)
            {
                $score = $temp_score;
                $response = $temp_response;
            }
        }

        if($this->m_rule->is_feng && $bLuanYao)
        {
            if ($bShiSanYao)
            {
                $temp_response['HU_TYPE'] = self::HU_TYPE_SHISANYAO;
            }
            else
            {
                $temp_response['HU_TYPE'] = self::HU_TYPE_LUANYAO;
            }
            //计算分数是不是比最大
            $temp_score =  self::$hu_type_arr[$temp_response['HU_TYPE']][1];
            if ($temp_score > $score)
            {
                $score = $temp_score;
                $response = $temp_response;
            }
        }
        return $response;
    }

    //判断类型判断 只判断32牌型
    public function judge_hu_type32($chair,$insertcard)
    {
        $temp_response = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);
        $response = array('HU_TYPE' => self::HU_TYPE_FENGDING_TYPE_INVALID, 'ATTACHED_HU_YITIAOLONG' => false, 'ATTACHED_HU_BENHUNLONG' => false,'ATTACHED_HU_WUKUI' => false,'ATTACHED_HU_WUKUI'=>false,'ATTACHED_HU_HUNDIAO'=>false);

        $jiang_arr = array();
        $qidui_arr = array();
        $shisanyao_arr = array();
        $luanyao_num = 0;

        $bType32 = false;
        $bQiDui = false;
        $bShiSanYao = false;    //13幺
        $bLuanYao = false;		//乱幺

        $is_yitiaolong = false;   //一条龙
        $is_zhuowukui = false;    //捉五魁


        //手牌
        for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if(0 == $this->m_sPlayer[$chair]->card[$i][0])
            {
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
                $jiang_arr[] = 32; $jiang_arr[] = 32;
            }
            else
            {
                $hu_list_val = $tmp_hu_data[$key];
                //1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen
                if($this->m_rule->is_yitiaolong_fan && ($hu_list_val & 256) == 256)//一条龙
                {
                    if ($i==$this->_get_card_type($this->m_fan_hun_card))
                    {
                        $temp_response['ATTACHED_HU_BENHUNLONG']=true;
                    }
                    else
                    {
                        $temp_response['ATTACHED_HU_YITIAOLONG']=true;
                    }

                }
                if(($hu_list_val & 1) == 1)
                {
                    $jiang_arr[] = $hu_list_val & 32;
                }
                else
                {
                    //非32牌型设置
                    $jiang_arr[] = 32; $jiang_arr[] = 32;
                }
            }
        }


        $bType32 = (32 == array_sum($jiang_arr));

        //基本牌型的处理///////////////////////////////
        if(!$bType32)
        {
            $response['HU_TYPE'] = self::HU_TYPE_FENGDING_TYPE_INVALID;
            return $response;
        }
        $score = 0;
        if($bType32)
        {
            //捉五魁
            $temp_response['ATTACHED_HU_WUKUI'] = $this->_is_wukui($chair,$insertcard);

            //吊五万
            if(!$temp_response['ATTACHED_HU_WUKUI'])
            {
                $temp_response['ATTACHED_HU_WUKUI'] = $this->_is_diaowuwan($chair, $insertcard);
            }
            $temp_response['HU_TYPE'] = self::HU_TYPE_PINGHU;
            //计算分数是不是比最大
            $temp_score =  self::$hu_type_arr[$temp_response['HU_TYPE']][1];
            if ($temp_response['ATTACHED_HU_BENHUNLONG'])
            {
                $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_BENHUNLONG][1];
            }
            if ($temp_response['ATTACHED_HU_YITIAOLONG'])
            {
                $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
            }
            if ($temp_response['ATTACHED_HU_WUKUI'])
            {
                $temp_score *=  self::$attached_hu_arr[self::ATTACHED_HU_WUKUI][1];
            }
            if ($temp_score > $score)
            {
                $score = $temp_score;
                $response = $temp_response;
            }
        }
        return $response;
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
            $tmp_hu_desc .= self::$hu_type_arr[$hu_type][2];
        }

        for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
        {
            if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
            {
                if ($this->m_HuCurt[$chair]->method[$i]==self::ATTACHED_HU_GANGKAI)
                {
                    //连续杠取消
                    $fan_sum = $fan_sum * self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
                    //$fan_sum = $fan_sum * pow(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1],$this->m_gangkai_num[$chair]);
                }
                else
                {
                    $fan_sum = $fan_sum * self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
                }
                $tmp_hu_desc .= ' '.self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2];
            }
        }

        $this->m_bMaxFan[$chair] = false;
        if ($fan_sum > $this->m_rule->top_fan)
        {
            $fan_sum = $this->m_rule->top_fan;
            $this->m_bMaxFan[$chair] = true;
        }

        if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
        {
            $tmp_hu_desc = '自摸胡'.$tmp_hu_desc;
        }
        else
        {
            $tmp_hu_desc = '接炮胡'.$tmp_hu_desc;
        }
        $tmp_hu_desc .= ')';
        //if(!$this->m_hu_desc[$chair])
        //{
        $this->m_hu_desc[$chair] = $tmp_hu_desc;
        //}

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
        
		$this->_set_record_game(ConstConfig::RECORD_PENG, $chair, $temp_card, $this->m_sOutedCard->chair);
		
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

        $this->m_gangkai_num[$chair] +=1; //连续杠的次数

		$GangScore = 0;
		$nGangPao = 0;
		$this->m_wGFXYScore = [0,0,0,0];
		for ($i=0; $i<$this->m_rule->player_count; ++$i)
		{
			if ($i == $chair)
			{
				continue;
			}

			if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
			{
				$nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;

				$this->m_wGFXYScore[$i] = -$nGangScore;			//扣本次刮风下雨分
				$this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分

				$this->m_wGFXYScore[$chair] += $nGangScore;				//赢本次刮风下雨分
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

		//$this->m_sPlayer[$chair]->len -= 3;

		$this->m_bHaveGang = true;  //for 杠上花

        $this->m_gangkai_num[$chair] +=1; //连续杠的次数

		$nGangScore = 0;
		$nGangPao = 0;
		$this->m_wGFXYScore = [0,0,0,0];
		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
			if ($i == $chair)
			{
				continue;
			}

			if ($stand_count_after > 0)
			{
				$nGangScore = self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;

				$this->m_wGFXYScore[$i] = -$nGangScore;
				$this->m_wGangScore[$i][$i] -= $nGangScore;

				$this->m_wGFXYScore[$chair] += $nGangScore;
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
    public function HandleHuZiMo($chair)
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
            //总计自摸
            if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
            {
                $this->m_wTotalScore[$chair]->n_zimo += 1;
                $this->m_currentCmd = 'c_zimo_hu';
            }

            $this->m_chairSendCmd = $this->m_chairCurrentPlayer;

            $this->m_bChairHu[$chair] = true;
            $this->m_bChairHu_order[] = $chair;
            $this->m_nCountHu++;
            $this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;

            //去除胡牌者 card_taken_now  这个牌就只有在 m_HuCurt 有
            $this->m_sPlayer[$chair]->card_taken_now = 0;

            $tmp_lost_chair = 255;
            $this->ScoreOneHuCal($chair, $tmp_lost_chair);

            if(255 == $this->m_nChairBankerNext)	//下一局庄家
            {
                if(!empty($this->m_rule->is_daizhuang))
                {
                    if(!empty($this->m_rule->is_circle) && $this->m_nChairBanker != $chair)
                    {
                        $this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker,1);
                    }
                    else
                    {
                        $this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker,0);
                    }
                }
                else
                {
                    $this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker,1);
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
        $this->m_gangkai_num[$chair] = 0;           //连续杠的次数

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
		for ( $i=0; $i<$this->m_rule->player_count - 1; $i++)
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

			if($this->_find_peng($chair_next) 
			 ||	$this->_find_zhi_gang($chair_next) 
			 )
			{
				$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair_next), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair_next]['uid']);
			}
			else
			{
                $this->m_sPlayer[$chair_next]->state = ConstConfig::PLAYER_STATUS_WAITING;
                $tmp_arr[] = $chair_next;
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

	//竞争选择处理
	public function HandleChooseResult($chair, $nCmdID, $eat_num = null)
	{

		$this->handle_flee_play(true);
		
		//处理竞争
		$order_cmd = array('c_cancle_choice'=>0, 'c_eat'=>1, 'c_peng'=>2, 'c_zhigang'=>3, 'c_hu'=>4);
		if(empty($this->m_currentCmd) || ($order_cmd[$nCmdID] > $order_cmd[$this->m_currentCmd] && $order_cmd[$nCmdID] >= $order_cmd['c_cancle_choice']))	//吃, 碰, 杠竞争
		{
			$this->m_chairSendCmd	= $chair;
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
				$this->m_sOutedCard->card	= $this->m_sQiangGang->card;
				$this->m_currentCmd = 'c_hu';

				// 设置倒牌, 抢杠后杠牌变成刻子
				for ($i = 0; $i < $this->m_sStandCard[$this->m_sOutedCard->chair]->num; $i ++)
				{
					if ($this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
					&& $this->m_sStandCard[$this->m_sOutedCard->chair]->card[$i] == $this->m_sOutedCard->card)
					{
						$this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] = ConstConfig::DAO_PAI_TYPE_KE;
						break;
					}
				}

				//if ($this->m_game_type == self::GAME_TYPE)
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
				$m_wGFXYScore = [0,0,0,0];
                for ( $i=0; $i<$this->m_rule->player_count; ++$i)
				{
                    if ($i == $this->m_sQiangGang->chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
                    {
                        continue;
                    }
                    $nGangScore = ConstConfig::SCORE_BASE * self::M_WANGANG_SCORE;

                    $this->m_wGFXYScore[$i] = -$nGangScore;
                    $this->m_wGangScore[$i][$i] -= $nGangScore;

                    $this->m_wGFXYScore[$this->m_sQiangGang->chair] += $nGangScore;
                    $this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
                    $this->m_wGangScore[$this->m_sQiangGang->chair][$i] += $nGangScore;

                    $nGangPao += $nGangScore;
				}

				//弯杠 扣 点碰玩家分数
				/*for ($i = 0; $i < $this->m_sStandCard[$this->m_sQiangGang->chair]->num; $i ++)
				{
					if ($this->m_sStandCard[$this->m_sQiangGang->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
					&& $this->m_sStandCard[$this->m_sQiangGang->chair]->card[$i] == $this->m_sQiangGang->card)
					{
						$nGangScore = self::M_WANGANG_SCORE *ConstConfig::SCORE_BASE;

						$tmp_who_give_me = $this->m_sStandCard[$this->m_sQiangGang->chair]->who_give_me[$i];
						$this->m_wGFXYScore[$tmp_who_give_me] = -$nGangScore;
						$this->m_wGangScore[$tmp_who_give_me][$tmp_who_give_me] -= $nGangScore;

						$this->m_wGFXYScore[$this->m_sQiangGang->chair] += $nGangScore;
						$this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
						$this->m_wGangScore[$this->m_sQiangGang->chair][$tmp_who_give_me] += $nGangScore;
						break;
					}

				}*/


				$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
				$this->m_chairCurrentPlayer = $this->m_sQiangGang->chair;

				$this->m_bHaveGang = true;					//for 杠上花
                $this->m_gangkai_num[$this->m_sQiangGang->chair] +=1;           //连续杠的次数
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
					//CCLOG("end reason no card");
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
		else	// 不是抢杠拉
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

				if ($this->m_game_type == self::GAME_TYPE)
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
				$bHu = $this->judge_hu($hu_chair);
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
	
	//咋胡处理
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
			
			$data['m_wTotalScore'] = $this->m_wTotalScore;
			$data['m_ready'] = $this->m_ready;
			$data['is_cancle'] = $this->m_cancle;
			$data['m_cancle'] = $this->m_cancle;
			$data['m_cancle_first'] = $this->m_cancle_first;
            $data['m_own_lazhuang'] = $this->m_own_lazhuang;
            $data['m_own_paozi'] = $this->m_own_lazhuang;

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
					$data['m_sPlayer'][$i] = (object)null;
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
					$data['m_sPlayer'][$i] = (object)null;
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
		//拉庄的时候返回数据
		if($this->m_sysPhase == ConstConfig::SYSTEMPHASE_LA_ZHUANG)
        {
            return $data;
        }
		return true;
	}

	//每局结束处理
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
			$cmd = new Game_cmd($this->m_room_id, 's_game_over', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}
		
		$this->_set_game_and_checkout();
	}

	///////////////////////得分处理///////////////////////////
	//每局个人  +=赢的分  +=输的分  +=庄家 的分  一共4局 
	public function ScoreOneHuCal($chair, &$lost_chair)  
	{

        $fan_sum = $this->judge_fan($chair);  //这个就是  一共多少分
        $PerWinScore = $fan_sum;
        /*var_dump(__LINE__);
        var_dump($PerWinScore);*/
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

                $banker_fan = 1;
                if (!empty($this->m_rule->is_daizhuang))
                {
                    if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
                    {
                        $banker_fan = $banker_fan * 2;
                    }
                }

                $PerWinScore = ($PerWinScore == 0)? 1 : ($PerWinScore * $banker_fan);
                $wWinScore = 0;
                $wWinScore += $PerWinScore ;  //赢的分 加  庄家的分

                if (!empty($this->m_rule->is_lazhuang))
                {
                    if ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
                    {
                        $wWinPaoZi = ($this->m_own_lazhuang[$chair]->num + $this->m_own_lazhuang[$lost_chair]->num);
                        $this->m_paozi_score[$chair] += $wWinPaoZi;
                        $this->m_paozi_score[$lost_chair] -= $wWinPaoZi;
                    }
                }

                $this->m_wHuScore[$lost_chair] -= $wWinScore;
                $this->m_wHuScore[$chair] += $wWinScore;

                $this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
                $this->m_wSetScore[$chair] += $wWinScore;

                $this->m_HuCurt[$chair]->gain_chair[0]++;
                $this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $lost_chair;

                //恢复初始值
                $PerWinScore = $PerWinScore / $banker_fan;
                $banker_fan = 1;

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
            $this->m_Score[$i]->score = $this->m_wSetScore[$i]+ $this->m_wSetLoseScore[$i]+ $this->m_wGangScore[$i][$i]+ $this->m_paozi_score[$i];
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

		if ($this->m_game_type != self::GAME_TYPE)
		{
			echo("error m_game_type".__LINE__.__CLASS__);
			return false;
		}

		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$this->m_wGangScore[$i][$i] = 0; 
			//$this->m_wFollowScore[$i] = 0;
			//$this->m_Score[$i]->score = 0;
			$this->m_Score[$i]->set_count = $this->m_nSetCount;

			if ($this->m_Score[$i]->score>0)
			{
				$this->m_Score[$i]->win_count = 1;
			}
			else
			{
				$this->m_Score[$i]->lose_count = 1;
			}
			//写录像分数用
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
				//$this->m_hu_desc[$i] = '';
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

			if (!empty($this->m_rule->is_lazhuang))
            {
                if ($this->m_paozi_score[$i]>0)
                {
                    $this->m_hu_desc[$i] .= '拉庄+'.$this->m_paozi_score[$i].' ';
                }
                else
                {
                    $this->m_hu_desc[$i] .= '拉庄'.$this->m_paozi_score[$i].' ';
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
            if(!empty($this->ALL_CARD))
            {
                $this->m_nCardBuf = $this->ALL_CARD;
            }
        }
        else
        {
            $this->m_nCardBuf = ConstConfig::ALL_CARD_108;
            $this->m_nAllCardNum = ConstConfig::BASE_CARD_NUM;
            if(!empty($this->ALL_CARD))
            {
                $this->m_nCardBuf = $this->ALL_CARD;
            }
        }

        if(empty($this->ALL_CARD))
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

        //订 翻混牌
        $this->m_fan_hun_card = $this->m_nCardBuf[$this->m_nCountAllot++];
        $this->_get_fan_hun($this->m_fan_hun_card);
        $this->_set_record_game(ConstConfig::RECORD_FANHUN, $this->m_nChairBanker, $this->m_fan_hun_card,$this->m_nChairBanker,$this->m_hun_card[0]*100+$this->m_hun_card[1]);

		//整理排序
		//$this->_list_insert($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
		//$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->_find_14_card($this->m_nChairBanker);
	}

	//发牌
	public function DealCard($chair)
	{
		if ($this->m_game_type == self::GAME_TYPE && $this->m_bChairHu[$chair])	//未胡玩家发牌
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
				$this->m_sPlayer[$i]->seen_out_card = 1;		//如无人吃碰杠，则全部可见
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

	public function game_to_playing()
	{
		//状态设定
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD ;
		$this->m_chairCurrentPlayer = $this->m_nChairBanker;

		for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_WAITING;
        }
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


    //开始玩(修改)
    public function on_start_game()
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

        //拉庄
        if(!empty($this->m_rule->is_lazhuang))
        {
            $this->start_la_zhuang();
            return true;
        }
        //发牌
        $this->DealAllCardEx();

        $this->game_to_playing();

        return true;
    }

    //拉庄
    public function start_la_zhuang()
    {
        //设置牌局的状态
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_LA_ZHUANG;
        //庄家的拉庄信息
        $this->m_own_lazhuang[$this->m_nChairBanker]->recv = true;
        $this->m_own_lazhuang[$this->m_nChairBanker]->num = 0;
        //发送数据
        for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
        {
            $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_LA_ZHUANG;
            //发消息
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
        }
    }

	//新增handle拉庄
    //处理拉庄
    public function handle_la_zhuang($chair, $la_zhuang_num)
    {
        $this->m_own_lazhuang[$chair]->recv = true;
        $this->m_own_lazhuang[$chair]->num = $la_zhuang_num;

        $tmp_lazhuang_arr = [0, 0, 0, 0];
        for ($i = 0; $i<$this->m_rule->player_count; ++$i)
        {
            $tmp_lazhuang_arr[$i] = $this->m_own_lazhuang[$i]->num;
            if (!$this->m_own_lazhuang[$i]->recv)
            {
                break;
            }
        }

        //开始牌局
        if ($this->m_rule->player_count == $i)
        {
            $this->_set_record_game(ConstConfig::RECORD_PAOZI, $tmp_lazhuang_arr[0], $tmp_lazhuang_arr[1], $tmp_lazhuang_arr[2], $tmp_lazhuang_arr[3]);

            $this->DealAllCardEx();

            $this->game_to_playing();
            return true;
        }
    }

    //重写翻混方法
    public function _get_fan_hun($fan_hun_card)
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
        $this->m_hun_card[0] = $fan_hun_card;
        $this->m_hun_card[1] = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index
        return $this->m_hun_card;
    }


	/******/
	/*其他*/
	/******/

	//玩家j相对于玩家i的位置,如($i=3,$j=0),返回1(即下家)
	private function _chair_to($i, $j)
	{
		return ($j-$i+$this->m_rule->player_count)%$this->m_rule->player_count;
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
		$card_key = $card%16;
		/*if($this->m_sPlayer[$chair]->card[$card_type][$card_key] < 4)*/
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

	//牌index
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
        $all_hunzi = false;
        //去除混牌
        $fanhun_arr = array(); //每个混子的数量
        //去掉所有混子
        foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
        {
            $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
            $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
            $one_fanhun_card = $fanhun_card%16;       //翻混牌

            $fanhun_arr[$fanhun_key]=$one_fanhun_num;
            $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
            $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] -= $one_fanhun_num;
        }
        //找出14牌
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
            /*echo ("竟然没有牌aaaaaaaas".__LINE__ );
            return false;*/
            $all_hunzi = true;
        }
        if ($last_type>=0)
        {
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
        }
        //拿回所有混子
        foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
        {
            $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
            $one_fanhun_card = $fanhun_card%16;       //翻混牌

            $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
            $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
        }
        if ($all_hunzi)
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
                echo ("竟然没有牌aaaaaaaas".__LINE__ );
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
        }
        if(empty($fouteen_card))
        {
            echo ("第十四张牌为空".__LINE__ );
            return false;
        }
        return $fouteen_card;
	}

    //掷骰定庄家(修改根据rule定庄家)
    public function _on_table_status_to_playing()
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
		$cancle_count = 0;      //操作人数
		$yes_count = 0;         //yes人数
		$no_count = 0;          //no人数
		$is_cancle = 0;         //最终是否取消
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

		if($cancle_count >= 2)
		{
			if($yes_count >= $this->m_rule->player_count)
			{
				$this->m_room_state = ConstConfig::ROOM_STATE_OVER;
				$is_cancle = 1;
			}
			else if($no_count >= 1)
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
		//解散房间超过300秒，则游戏结束
        if(!empty($this->m_cancle_time) && ($this->m_cancle_time + Config::CANCLE_GAME_CLOCKER_NUM - time() <= Config::CANCLE_GAME_CLOCKER_LIMIT))
        {
            $this->m_room_state = ConstConfig::ROOM_STATE_OVER;
            $is_cancle = 1;
        }

		$cmd = new Game_cmd($this->m_room_id, 's_cancle_game', array('is_cancle'=>$is_cancle, 'm_cancle_first'=>$this->m_cancle_first, 'm_cancle'=>$this->m_cancle, 'cancle_time_start'=>$cancle_time_start), Game_cmd::SCO_ALL_PLAYER );
		$cmd->send($this->serv);
		unset($cmd);

		if($is_cancle == 1)
		{
			$is_log = false;
			if(($this->m_nSetCount > 1) || ($this->m_nChairBankerNext != 255 && $this->m_nSetCount == 1 && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext)))
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

	private function _deal_test_card()
	{
		//发测试牌
		for($i = 0; $i < $this->m_rule->player_count; $i++)
		{
			$power = 0;
			if($i == 0)
			{
				$power = 20;
			}
			if(defined("gf\\conf\\Config::WHITE_UID") && in_array($this->m_room_players[$i]['uid'], Config::WHITE_UID))
			{
				$power = 20;
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

    //记录战绩
    public function record_game_log($is_room_over = false, $is_log = false)
    {
        $itime = time();
        $uid_arr = array();
        $game_table_info = [];
        $agent_uid = empty($this->agent_uid) ? 0 : $this->agent_uid;

        foreach ($this->m_room_players as $key => $room_user)
        {
            if(!empty($room_user['uid']))
            {
                $uid_arr[] = $room_user['uid'];
            }
        }

        if( $is_room_over == 1)
        {
            $game_table_info['date'] = date('Y-m-d H:i:s', time());
            $game_table_info['display'] =  $this->m_rule->game_type['display'];
            $game_table_info['player_count'] =  $this->m_rule->player_count;
            $game_table_info['set_num'] =  $this->m_rule->set_num;
            $game_table_info['pay_type'] =  $this->m_rule->pay_type;
            if (!empty($agent_uid))
            {
                $game_table_info['is_circle'] =  empty($this->m_rule->is_circle) ? 0 : $this->m_rule->is_circle;
            }

            foreach ($this->m_room_players as $key => $room_user)
            {
                $game_table_info['play'][$key]['is_room_owner'] = $room_user['is_room_owner'];
                $game_table_info['play'][$key]['uid'] = $room_user['uid'];
                $game_table_info['play'][$key]['uname'] = $room_user['uname'];
                $game_table_info['play'][$key]['totalscore'] = $this->m_wTotalScore[$key]->n_score;
            }
        }

        //web set_game_log
        $tmp_game_info = $this->_set_game_info();
        if($tmp_game_info && $this->m_nSetCount != 255)	//非投票解散的牌局
        {
            BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'set_game_log', 'platform'=>'gfplay', 'rid'=>$this->m_room_id,'uid'=>$this->m_room_owner, 'uid_arr'=>implode(',', $uid_arr)
            , 'game_info'=>json_encode($tmp_game_info, JSON_UNESCAPED_UNICODE),'type'=>1, 'is_room_over'=>$is_room_over
            , 'game_type'=>$this->m_game_type, 'play_time'=>$itime - $this->m_start_time, 'game_table_info'=>json_encode($game_table_info, JSON_UNESCAPED_UNICODE), 'agent_uid' => $agent_uid
            ));
        }
        else
        {
            //game_info=255  表示集散房间不记录录像
            if($this->m_nSetCount == 255 && $is_log)
            {
                BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'set_game_log', 'platform'=>'gfplay', 'rid'=>$this->m_room_id,'uid'=>$this->m_room_owner, 'uid_arr'=>implode(',', $uid_arr)
                , 'game_info'=>255,'type'=>1, 'is_room_over'=>$is_room_over
                , 'game_type'=>$this->m_game_type,  'game_table_info'=>json_encode($game_table_info, JSON_UNESCAPED_UNICODE), 'agent_uid' => $agent_uid
                ));
            }
        }
    }

	//写录像
	private function _set_record_game($act, $param_1 = 0, $param_2 = 0, $param_3 = 0, $param_4 = 0)
	{
		$param_1_tmp = 0;
		$param_3_tmp = 0;
		if(in_array($act, [ConstConfig::RECORD_CHI,
                            ConstConfig::RECORD_PENG,
                            ConstConfig::RECORD_ZHIGANG,
                            ConstConfig::RECORD_ANGANG,
                            ConstConfig::RECORD_ZHUANGANG,
                            ConstConfig::RECORD_HU,
                            ConstConfig::RECORD_HU_QIANGGANG,
                            ConstConfig::RECORD_ZIMO,
                            ConstConfig::RECORD_DISCARD,
                            ConstConfig::RECORD_DRAW,
                            ConstConfig::RECORD_DEALER,
                            ConstConfig::RECORD_FANHUN,
                            ConstConfig::RECORD_PASS]))
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

	//写完录像 整理
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
    public function _set_game_and_checkout($is_log=false)
    {
        $itime = time();
        $is_room_over = 0;
        $currency_change_group = [];
        if( empty($this->m_rule)
            || ( $this->m_nSetCount != 255 && $this->m_rule->set_num <= $this->m_nSetCount && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext))
            || ( $this->m_nSetCount == 255 && $is_log )
        )
        {
            $is_room_over = 1;
        }

        //钻石结算
        $this->diamond_settlement($currency_change_group, $is_room_over);

        //总结算
        if (count($currency_change_group) == 1)
        {
            foreach ($currency_change_group as $uid => $single_currency_change)
            {
                if(count($single_currency_change) == 1)
                {
                    BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'checkout_open_room', 'platform'=>'gfplay', 'uid'=>$uid, 'currency'=>$single_currency_change[0]['currency'],'type'=>$single_currency_change[0]['type']));
                }
                else
                {
                    BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'checkout_open_room', 'platform'=>'gfplay', 'uid'=>'00001', 'currency'=>1,'type'=>1,'currency_change_group' => json_encode($currency_change_group, JSON_UNESCAPED_UNICODE)));
                }
            }
        }
        elseif (count($currency_change_group) > 1)
        {
            BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'checkout_open_room', 'platform'=>'gfplay', 'uid'=>'00001', 'currency'=>1,'type'=>1,'currency_change_group' => json_encode($currency_change_group, JSON_UNESCAPED_UNICODE)));
        }

        //录像和战绩
        $this->record_game_log($is_room_over, $is_log);
    }
    //钻石结算
    public function diamond_settlement(&$currency_change_group, $is_room_over)
    {
        $result = Room::$get_conf;
        if(empty($this->m_rule->is_circle) && !empty($result['data']['room_type']))
        {
            $currency_tmp = BaseFunction::need_currency($result['data']['room_type'],$this->m_game_type,$this->m_rule->set_num);
        }
        else if(!empty($result['data']['room_type_circle']))
        {
            $currency_tmp = BaseFunction::need_currency($result['data']['room_type_circle'],$this->m_game_type,($this->m_rule->set_num / $this->m_rule->player_count));
        }

        if (isset($this->m_rule->pay_type))
        {
            if ($this->m_rule->pay_type == 0)
            {
                if($this->m_nSetCount == 1 && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext))
                {
                    $currency = !empty($currency_tmp) ? (-$currency_tmp) : 0;
                    if (!empty($currency))
                    {
                        $currency_change_group[$this->m_room_owner][] = ['currency' => $currency, 'type' => 1];
                    }
                }
            }

            if ($this->m_rule->pay_type == 1)
            {
                if ($this->m_nSetCount == 1 && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext))
                {
                    $currency_all = !empty($currency_tmp) ? $currency_tmp : 0;
                    $currency = -(ceil($currency_all/$this->m_rule->player_count));
                    for($i = 0; $i < $this->m_rule->player_count; $i++)
                    {
                        $currency_change_group[$this->m_room_players[$i]['uid']][] = ['currency' => $currency, 'type' => 1];
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
                $currency = -(ceil($currency_all/$winner_count));
                foreach ($winner_arr as $item_user)
                {
                    $currency_change_group[$item_user][] = ['currency' => $currency, 'type' => 1];
                }
            }

            if ($this->m_rule->pay_type == 3)
            {
                if($this->m_nSetCount == 1 && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext))
                {
                    if (!empty($this->agent_uid))
                    {
                        $currency = !empty($currency_tmp) ? (-$currency_tmp) : 0;
                        if (!empty($currency))
                        {
                            $currency_change_group[$this->agent_uid][] = ['currency' => $currency, 'type' => 1];
                        }
                    }
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
                    if (!empty($currency))
                    {
                        $currency_change_group[$this->m_room_owner][] = ['currency' => $currency, 'type' => 1];
                    }
                }
            }
            else
            {
                if($is_room_over == 1)
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
                    $currency = -(ceil($currency_all/$winner_count));
                    foreach ($winner_arr as $item_user)
                    {
                        $currency_change_group[$item_user][] = ['currency' => $currency, 'type' => 1];
                    }
                }
            }
        }
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

    public function _is_wukui($chair,$insertcard)
    {
        $type = ConstConfig::PAI_TYPE_WAN;
        if(($insertcard != 5) || ($this->m_sPlayer[$chair]->card[$type][4] == 0 || $this->m_sPlayer[$chair]->card[$type][6] == 0))
        {
            return false;
        }
        return $this->judge_32type($chair,array(4,5,6),$type);
    }

    public function _is_diaowuwan($chair, $insertcard)
    {
        $is_diaowuwan = false;
        $bType32 = false;
        $type = ConstConfig::PAI_TYPE_WAN;


        if($insertcard != 5 || $this->m_sPlayer[$chair]->card[$type][5] <= 1)
        {
            return false;
        }

        $bType32=$this->judge_32type($chair,array(5,5),$type);

        if($bType32 && $this->_is_dandiao_wuwan($chair))
        {
            $is_diaowuwan = true;
        }
        return $is_diaowuwan;
    }

    //听牌判断
    public function _is_dandiao_wuwan($chair)
    {
        $is_dandiao_wuwan = true;
        $replace_card = array(1,2,3,4,6,7,8,9);

        $this->_list_delete($chair,5);
        foreach ($replace_card as $value)
        {
            $jiang_arr = array();
            $bType32 = false;
            $this->_list_insert($chair,$value);

            //手牌
            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG; $i++)
            {
                $tmp_hu_data = &ConstConfig::$hu_data;
                if(ConstConfig::PAI_TYPE_FENG == $i)
                {
                    $tmp_hu_data = &ConstConfig::$hu_data_feng;
                }
                $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

                if(!isset($tmp_hu_data[$key]))
                {
                    $jiang_arr[] = 32;
                    $jiang_arr[] = 32;
                }
                else
                {
                    $hu_list_val = $tmp_hu_data[$key];
                    //1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen

                    if(($hu_list_val & 1) == 1)
                    {
                        $jiang_arr[] = $hu_list_val & 32;
                    }
                    else
                    {
                        //非32牌型设置
                        $jiang_arr[] = 32; $jiang_arr[] = 32;
                    }
                }
            }
            $this->_list_delete($chair,$value);
            //记录根到全局数据
            if(32 == array_sum($jiang_arr) && ($this->m_sPlayer[$chair]->card[0][$value]>=1 && $this->judge_32type($chair,array($value),ConstConfig::PAI_TYPE_WAN)))
            {
                $is_danting_wuwan = false;
                break;
            }
        }
        $this->_list_insert($chair,5);
        return $is_dandiao_wuwan;
    }

    public function judge_32type($chair,$delcard_arr,$card_type)
    {
        $bType32 = false;
        $tmp_hu_data = &ConstConfig::$hu_data;
        if ($card_type == ConstConfig::PAI_TYPE_FENG)
        {
            $tmp_hu_data = &ConstConfig::$hu_data_feng;
        }
        foreach ($delcard_arr as $item=>$value)
        {
            $this->m_sPlayer[$chair]->card[$card_type][$value] -= 1;
        }
        //判断手牌是否满足32牌型
        $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$card_type], 1)));
        if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
        {
            $bType32 = true;
        }
        foreach ($delcard_arr as $item=>$value)
        {
            $this->m_sPlayer[$chair]->card[$card_type][$value] += 1;
        }
        return $bType32;
    }


    public function judge_qidui_wukui($chair)
    {
        if (in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card))
        {

            $need_fanhun = 0;	//需要混子个数
            $hu_qidui = false;

            //删除胡的那张牌
            $this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
            //把5插入
            $this->_list_insert($chair,5);

            $fanhun_num = 0;
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $fanhun_num  += $this->_list_find($chair,$fanhun_card);	//手牌翻混个数
            }
            if (in_array(5,$this->m_hun_card))
            {
                $fanhun_num -=1;
            }

            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] -= $one_fanhun_num;
            }

            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
            {
                if(0 == $this->m_sPlayer[$chair]->card[$i][0])
                {
                    continue;
                }
                for ($j=1; $j<=9; $j++)
                {
                    if($this->m_sPlayer[$chair]->card[$i][$j] == 1 || $this->m_sPlayer[$chair]->card[$i][$j] == 3)
                    {
                        $need_fanhun +=1 ;
                        //$da8zhang_replace_fanhun[$i]+= 1;
                    }
                }
            }

            //拿回所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
            }

            if($need_fanhun <= $fanhun_num)
            {
                $hu_qidui = true;
            }

            $this->_list_delete($chair,5);
            $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);

            return $hu_qidui;
        }
        else
        {
            if ($this->m_HuCurt[$chair]->card == 5)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public function judge_qidui_hundiao($chair)
    {
        //if (!in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card))
        if(true)
        {
            $need_fanhun = 0;	//需要混子个数
            $hu_qidui = false;

            //删除胡的那张牌
            $this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
            //删除一张混牌
            $fanhun_arr_one = array(); //每个混子的数量
            //去掉所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_num_one = $this->_list_find($chair, $fanhun_card);	   //手牌翻混个数
                $one_fanhun_type_one = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card_one = $fanhun_card%16;       //翻混牌
                if ($one_fanhun_num_one >0)
                {
                    $fanhun_arr_one[$fanhun_card] = 1;
                    $this->m_sPlayer[$chair]->card[$one_fanhun_type_one][$one_fanhun_card_one] -= 1 ;
                    $this->m_sPlayer[$chair]->card[$one_fanhun_type_one][0] -= 1;
                    break;
                }
            }


            $fanhun_num = 0;
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $fanhun_num  += $this->_list_find($chair,$fanhun_card);	//手牌翻混个数
            }

            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $fanhun_arr[$fanhun_key]=$one_fanhun_num;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] -= $one_fanhun_num;
            }

            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
            {
                if(0 == $this->m_sPlayer[$chair]->card[$i][0])
                {
                    continue;
                }
                for ($j=1; $j<=9; $j++)
                {
                    if($this->m_sPlayer[$chair]->card[$i][$j] == 1 || $this->m_sPlayer[$chair]->card[$i][$j] == 3)
                    {
                        $need_fanhun +=1 ;
                        //$da8zhang_replace_fanhun[$i]+= 1;
                    }
                }
            }

            //拿回所有混子
            foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
            {
                $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
                $one_fanhun_card = $fanhun_card%16;       //翻混牌

                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
            }

            if($need_fanhun <= $fanhun_num)
            {
                $hu_qidui = true;
            }

            $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
            foreach ($fanhun_arr_one as $fanhun_key=>$num)
            {
                $one_fanhun_type = $this->_get_card_type($fanhun_key);        //翻混牌类型
                $one_fanhun_card = $fanhun_key%16;       //翻混牌

                $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] += $num;
                $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $num;
            }
            return $hu_qidui;
        }
    }

    public function judge_32_wukui($chair)
    {
        $result = false;
        if ($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_WAN][4]>0  && $this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_WAN][6]>0)
        {
            $result =$this->judge_32type($chair,array(4,5,6),ConstConfig::PAI_TYPE_WAN);
        }
        return $result;
    }

    public function judge_32_diaowukui($chair)
    {
        $result = false;
        if ($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_WAN][5]>1)
        {
            $bType32=$this->judge_32type($chair,array(5,5),ConstConfig::PAI_TYPE_WAN);
            if($bType32 && $this->_is_dandiao_wuwan($chair))
            {
                $result = true;
            }
        }
        return $result;
    }

}
