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

class GameLandLord
{
	const GAME_TYPE = 331;

	public $serv;	                                   	// socket服务器对象

	public $m_ready = array(0,0,0,0);	               	// 用户准备
	public $m_game_type;	                           	// 游戏type
	public $m_room_state;	                           	// 房间状态
	public $m_room_id;	                               	// 房间号
	public $m_room_owner;	                           	// 房主
	public $m_room_players = array();	               	// 玩家信息
	public $m_rule;	                                   	// 规则对象
	public $m_start_time;	                           	// 开始时间
	public $m_end_time;	                               	// 结束时间
	public $m_record_game;							   	// 录制脚本

	public $m_nSetCount;	                           	// 比赛局数
	public $m_wTotalScore;				               	// 总结的分数

	//记分，以后处理
	public $m_wHuScore = array();					   	// 本剧胡整合分数
	public $m_wSetScore = array();				       	// 该局的胡分数
	public $m_wSetLoseScore = array();			       	// 该局的被胡分数
	public $m_Score = array();	                       	// 用户分数结构
	
	public $m_cancle = array();	                       	// 解散房间标志
	public $m_cancle_first;	                           	// 解散房间发起人
	public $m_cancle_time;								// 解散房间开始时间

	public $m_sPlayer = array();				       	// 玩家手牌私有数据 Play_data

	public $m_nCardBuf = array();			           	// 牌的缓冲区
	public $m_nChairBanker;				               	// 庄家的位置，
	public $m_nChairBankerNext = 255;				   	// 下一局庄家的位置，
	public $m_nCountAllot;					           	// 发到第几张牌
	public $m_nAllCardNum = ConstConfig::BASE_LANDLORD_CARD_NUM;	//牌总数
	public $m_sysPhase;				                   	// 当前阶段状态
	public $m_chairCurrentPlayer;			           	// 当前出牌者
	public $m_bChooseBuf = array();			           	// 玩家的选择胡,吃,碰,杠命令 1 等待操作 0 无操作

    public $m_nTableCards = array();        		   	// 玩家的桌面牌
    public $m_nNumTableCards = array();        			// 玩家桌面牌数量

	public $m_show = array();							//[]每个玩家明牌： 0未操作， 1明牌； 2不明牌
	public $m_double = array();							//[]每玩家加倍： 0未操作， 1加倍； 2不加倍
	public $m_bid = array();							//[]每玩家叫牌： 0未操作，1分，2分，3分，255不叫
	public $m_landlord;									//地主座位号
	public $m_nLastCards = array();						//3张底牌
	public $m_nResBid = 0;								//重新发牌次数

	public $m_outed;									//当前出牌
	public $m_play_outed = array();						// [$i][0]用户上次出的牌； [$i][0]用户上次状态： 0无操作 1过
	public $m_HistoryOuted = array(); 					//每个玩家历史出牌记录
	public $m_hu_desc = array();		               	// 详细的出牌类型(火箭 炸弹 春天 反春天 明牌.......)

	public $m_score_times = array();					//每个玩家倍数
	public $m_base_score;								//底分

	public $player_score = array(0,0,0,0);              //结算时积分
	public $player_cup = array(0,0,0,0);                //结算时奖杯
	
	public $agent_uid;                                  // 公会房代理的玩家id
    public $m_client_ip = array();                      // 用户ip
	
	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_TYPE_SPRING = 70;              		// 春天
	const ATTACHED_TYPE_NOSPRING = 71;            		// 反春天
	const ATTACHED_TYPE_SHOW = 1;                		// 发牌后明牌
	const ATTACHED_TYPE_BEFORE_SHOW = 3;                // 发牌前明牌
	const ATTACHED_TYPE_DOUBLE = 11;              		// 加倍
	const ATTACHED_TYPE_SUPER_DOUBLE = 13;              // 超级加倍

	public static $pai_type_arr = array(
	ConstConfig::PAI_TYPE_LANDLORD_JOKER_BOMB=>[ConstConfig::PAI_TYPE_LANDLORD_JOKER_BOMB, 1, '火箭']
	,ConstConfig::PAI_TYPE_LANDLORD_BOMB=>[ConstConfig::PAI_TYPE_LANDLORD_BOMB, 1, '炸弹']

	);

	public static $attached_type_arr = array(
	self::ATTACHED_TYPE_SPRING=>[self::ATTACHED_TYPE_SPRING, 2, '春天']
	,self::ATTACHED_TYPE_NOSPRING=>[self::ATTACHED_TYPE_NOSPRING, 2, '春天']
	,self::ATTACHED_TYPE_SHOW=>[self::ATTACHED_TYPE_SHOW, 2, '明牌']
	,self::ATTACHED_TYPE_BEFORE_SHOW=>[self::ATTACHED_TYPE_BEFORE_SHOW, 4, '明牌']
	,self::ATTACHED_TYPE_DOUBLE=>[self::ATTACHED_TYPE_DOUBLE, 2, '加倍']
	,self::ATTACHED_TYPE_SUPER_DOUBLE=>[self::ATTACHED_TYPE_SUPER_DOUBLE, 4, '超级加倍']

	);

	///////////////////////方法/////////////////////////
	//构造方法
	public function __construct($serv)
	{
		$this->serv = $serv;
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_LANDLORD_SET_OVER ;
		$this->m_room_state = ConstConfig::ROOM_STATE_NULL ;
		$this->m_game_type = self::GAME_TYPE;
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
		if($is_open || $this->m_rule->set_num <= $this->m_nSetCount)
		{
			$this->m_game_type = self::GAME_TYPE;	//游戏类型，见http端协议
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
		$this->m_landlord = 255;
		$this->m_outed = new Outed_card_landlord();

		$this->m_nCountAllot = 0;
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_LANDLORD_SET_OVER ;
		$this->m_chairCurrentPlayer = 255;
		$this->m_end_time = '';

		$this->m_cancle_first = 255;
		$this->m_cancle_time = 0;
		$this->m_nLastCards = [];     //底牌
		$this->player_score = array(0,0,0,0);                                
		$this->player_cup = array(0,0,0,0);

		if(defined("gf\\conf\\Config::LAND_LORD_BASE") && Config::LAND_LORD_BASE)
		{
			$this->m_base_score = Config::LAND_LORD_BASE;
		}
		else
		{
			$this->m_base_score = 1;
		}

		for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
		{
			$this->m_wHuScore[$i] = 0;
			$this->m_wSetScore[$i] = 0;
			$this->m_wSetLoseScore[$i] = 0;
			$this->m_Score[$i] = new Score();
			$this->m_bChooseBuf[$i] = 0;

			$this->m_cancle[$i] = 0;
			$this->m_sPlayer[$i] = new Play_data_landlord();

			$this->m_nTableCards[$i] = array();
			$this->m_nNumTableCards[$i] = 0;

			$this->m_bid[$i] = 0;
			$this->m_show[$i] = 0;
			$this->m_double[$i] = 0;
			$this->m_HistoryOuted[$i] = array();
			$this->m_hu_desc[$i] = '';
			$this->m_score_times[$i] = 1;
			$this->m_play_outed[$i] = [(object)null, 0];

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
				$return_send['code'] = 2; $return_send['text'] = '房间已经不存在了'; $return_send['desc'] = __LINE__.__CLASS__; break;
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

						$this->_send_cmd('s_get_game', $this->OnGetChairScene($key, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$key]['uid']);

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

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_LANDLORD_SET_OVER || $this->m_room_state == ConstConfig::ROOM_STATE_GAMEING )
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}
			elseif ($this->m_room_state == ConstConfig::ROOM_STATE_OPEN  && $this->m_room_owner != $params['uid'])
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$this->clear();
			$this->m_rule = new RuleLandLord();
			if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3)))
			{
				$params['rule']['player_count'] = 3;
			}

			$params['rule']['is_show'] = !isset($params['rule']['is_show']) ? 1 : $params['rule']['is_show'];
			$params['rule']['is_double'] = !isset($params['rule']['is_double']) ? 1 : $params['rule']['is_double'];
			$params['rule']['is_fanhun'] = !isset($params['rule']['is_fanhun']) ? 0 : $params['rule']['is_fanhun'];
			$params['rule']['top_fan'] = !isset($params['rule']['top_fan']) ? 0 : $params['rule']['top_fan'];
			$params['rule']['min_fan'] = !isset($params['rule']['min_fan']) ? 0 : $params['rule']['min_fan'];
			$params['rule']['pay_type'] = !isset($params['rule']['pay_type']) ? 0 : $params['rule']['pay_type'];
			$params['rule']['cancle_clocker'] = !isset($params['rule']['cancle_clocker']) ? 1 : $params['rule']['cancle_clocker'];

			$this->m_rule->game_type = $params['rule']['game_type'];
			$this->m_rule->player_count = $params['rule']['player_count'];
			$this->m_rule->set_num = $params['rule']['set_num'];
			$this->m_rule->top_fan = $params['rule']['top_fan'];
			$this->m_rule->min_fan = $params['rule']['min_fan'];

			$this->m_rule->is_show = $params['rule']['is_show'];
			$this->m_rule->is_double = $params['rule']['is_double'];
			$this->m_rule->is_fanhun = $params['rule']['is_fanhun'];
			$this->m_rule->pay_type = $params['rule']['pay_type'];
			$this->m_rule->cancle_clocker = $params['rule']['cancle_clocker'];

			// 积分新增
			$params['rule']['score'] = !isset($params['rule']['score']) ? 0 : $params['rule']['score'];
			$this->m_rule->score = $params['rule']['score'];
			$params['rule']['is_score_field'] = !isset($params['rule']['is_score_field']) ? 0 : $params['rule']['is_score_field'];
			$this->m_rule->is_score_field = $params['rule']['is_score_field'];
			
			$this->InitData(true);

			$this->m_room_state = ConstConfig::ROOM_STATE_OPEN ;
			$this->m_room_id = $params['rid'];
			$this->m_room_owner = $params['uid'];
			$this->m_room_players = array();
			$this->m_start_time = $itime;
			$this->m_nSetCount = 0;
			$this->agent_uid = 0;
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
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			//兼容
			if(empty($params['sex']))
			{
				$params['sex'] = 0;
			}
			if(empty($params['gps']))
			{
				$params['gps'] = [];
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_LANDLORD_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
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
			$this->m_room_players[$add_key]['gps'] = $params['gps'];
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

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_LANDLORD_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
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

	//离开房间  
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
			if($this->m_nSetCount != 0 || $this->m_sysPhase != ConstConfig::SYSTEMPHASE_LANDLORD_SET_OVER)
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
						if($this->_is_clocker() && !empty($this->m_rule->cancle_clocker))
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

	//声明明牌
	public function c_show($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['type'])
			|| !in_array($params['type'], array(1, 2))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if(($this->m_sysPhase != ConstConfig::SYSTEMPHASE_LANDLORD_SHOW_BRFORE && $this->m_sysPhase != ConstConfig::SYSTEMPHASE_LANDLORD_SHOW_AFTER)
			 || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state
			 )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if ($this->m_show[$key] != 0)
					{
						$return_send['code'] = 4; $return_send['text'] = '您已经明牌了'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->handle_show($key, $params['type']);
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

	private function handle_show($chair, $type)
	{
		if(empty($this->m_show[$chair]))
		{
			$this->m_show[$chair] = $type;
		}

		$this->_get_score_times();	

		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		if(!array_keys($this->m_show, 0))
		{
			//$this->_set_record_game(ConstConfig::RECORD_PAOZI, $tmp_paozi_arr[0], $tmp_paozi_arr[1], $tmp_paozi_arr[2], $tmp_paozi_arr[3]);

			if($this->m_sysPhase == ConstConfig::SYSTEMPHASE_LANDLORD_SHOW_BRFORE)
			{
				$this->DealAllCardEx();
			}


			$this->start_bid();
			return true;
		}
	}

	//声明加倍
	public function c_double($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['type'])
			|| !in_array($params['type'], array(1, 2))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if(($this->m_sysPhase != ConstConfig::SYSTEMPHASE_LANDLORD_DOUBLE)
			 || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state
			 )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if ($this->m_double[$key] != 0)
					{
						$return_send['code'] = 4; $return_send['text'] = '您已经加倍了'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					$this->handle_double($key, $params['type']);
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

	private function handle_double($chair, $type)
	{
		if(empty($this->m_double[$chair]))
		{
			$this->m_double[$chair] = $type;
		}

		$this->_get_score_times();	

		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}
		
		if(!array_keys($this->m_double, 0))
		{
			//加倍
			$this->_set_record_game(ConstConfig::RECORD_P_DDZ_DOUBLE, $this->m_double[0], $this->m_double[1], $this->m_double[2] );

			$this->game_to_playing();
			return true;
		}
	}

	//叫牌/抢地主
	public function c_bid($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| !isset($params['num'])
			|| !in_array($params['num'], array(1, 2, 3, 255))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_LANDLORD_DIBS || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if($this->m_sPlayer[$key]->state != ConstConfig::PLAYER_LANDLORD_STATUS_DIBS)
					{
						$return_send['code'] = 4; $return_send['text'] = '您现在不能叫牌'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					//3人斗地主叫牌校验
					if($key != $this->m_nChairBanker)
					{
						$last_key = $this->_anti_clock($key, -1);
						if($this->m_bid[$last_key] == 0 || ($this->m_bid[$last_key] != 255 && $this->m_bid[$last_key] >= $params['num']))
						{
							$return_send['code'] = 4; $return_send['text'] = '叫牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
						else if($this->m_bid[$last_key] == 255 && $last_key != $this->m_nChairBanker)
						{
							$last_2_key = $this->_anti_clock($key, -2);
							if($this->m_bid[$last_2_key] == 0 || ($this->m_bid[$last_2_key] != 255 && $this->m_bid[$last_2_key] >= $params['num']))
							{
								$return_send['code'] = 4; $return_send['text'] = '叫牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
							}
						}
					}

					if ($this->m_bid[$key] != 0)
					{
						$return_send['code'] = 4; $return_send['text'] = '您已经叫牌了'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->handle_bid($key, $params['num']);
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

	private function handle_bid($chair, $num)
	{
		$this->m_bid[$chair] = $num;
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_LANDLORD_STATUS_WAITING;

		//3人斗地主叫3分校验
		$next_key = $this->_anti_clock($chair, 1);
		if($this->m_bid[$next_key] == 0 && $next_key != $this->m_nChairBanker)
		{
			$this->m_sPlayer[$next_key]->state = ConstConfig::PLAYER_LANDLORD_STATUS_DIBS;
			if($num == 3)
			{
				$this->m_bid[$next_key] = 255;
				$this->m_sPlayer[$next_key]->state = ConstConfig::PLAYER_LANDLORD_STATUS_WAITING;
				$next_2_key = $this->_anti_clock($chair, 2);
				if($this->m_bid[$next_2_key] == 0 && $next_2_key != $this->m_nChairBanker)
				{
					$this->m_sPlayer[$next_2_key]->state = ConstConfig::PLAYER_LANDLORD_STATUS_WAITING;
					$this->m_bid[$next_2_key] = 255;
				}
			}
		}

		//$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene(0, false), Game_cmd::SCO_ALL_PLAYER);
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		$this->_get_score_times();
		if(!array_keys($this->m_bid, 0))
		{
			if(array_sum($this->m_bid) == 255 * $this->m_rule->player_count )
			{
				$this->m_nSetCount -= 1;
				$this->m_nResBid += 1;  //重新发牌次数
				$this->on_start_game();
			}
			else
			{
				$max_bid = 0;
				$this->m_nResBid = 0; //重置 重新发牌次数
				$i = $this->m_nChairBanker;
				do
				{
					if($this->m_bid[$i] > $max_bid && $this->m_bid[$i] != 255)
					{
						$this->m_landlord = $i;

					}
					$i = $this->_anti_clock($i, 1);
				}while ( $i != $this->m_nChairBanker);

				
				//底牌处理
				for($k=0; $k<ConstConfig::BASE_LANDLORD_LEFT_CARD_NUM; $k++)
				{
					$temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];	//从牌缓冲区里那张牌
					$this->_list_insert($this->m_landlord, $temp_card);
					$this->m_nLastCards[] = $temp_card;
					$tmp_card_arr[$k] = $temp_card;
				}
				
				//地主叫分
				$this->_set_record_game(ConstConfig::RECORD_P_DDZ_BID,$this->m_bid[0], $this->m_bid[1], $this->m_bid[2]);				

				//地主位置
				$this->_set_record_game(ConstConfig::RECORD_P_DDZ_LANDLORD,$this->m_landlord );

				//底牌
				$this->_set_record_game(ConstConfig::RECORD_P_DDZ_UNDERCARD, $tmp_card_arr[0], $tmp_card_arr[1], $tmp_card_arr[2]);

				if(!empty($this->m_rule->is_double))
				{
					$this->start_double();
					return true;
				}

				$this->game_to_playing();
			}
		}
	}

	//三圈都没有人叫地主 后 默认地主
	private function res_bid()
	{
		$this->m_landlord = $this->m_nChairBanker;

		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			if($i == $this->m_landlord )
			{
				$this->m_bid[$i] = 1;
			}
			else
			{
				$this->m_bid[$i] = 255;
			}
		}

		$this->m_nResBid = 0; //重置

		//底牌处理
		for($k=0; $k<ConstConfig::BASE_LANDLORD_LEFT_CARD_NUM; $k++)
		{
			$temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];	//从牌缓冲区里那张牌
			$this->_list_insert($this->m_landlord, $temp_card);
			$this->m_nLastCards[] = $temp_card;
			$tmp_card_arr[$i] = $temp_card;
		}

		//地主叫分
		$this->_set_record_game(ConstConfig::RECORD_P_DDZ_BID,$this->m_bid[0], $this->m_bid[1], $this->m_bid[2]);				
		
		//地主位置
		$this->_set_record_game(ConstConfig::RECORD_P_DDZ_LANDLORD,$this->m_landlord );

		//底牌
		$this->_set_record_game(ConstConfig::RECORD_P_DDZ_UNDERCARD, intval($tmp_card_arr[0]), intval($tmp_card_arr[1]), intval($tmp_card_arr[2]));

		if(!empty($this->m_rule->is_double))
		{
			$this->start_double();
			return true;
		}

		$this->game_to_playing();
	}

	//出牌
	public function c_out_card($fd, $params)
	{
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do {
            if( empty($params['rid'])
                || empty($params['uid'])
                || empty($params['out_card'])
            )
            {
                $return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_LANDLORD_PLAY_CARD)
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


                    if(!($this->handle_out_card($key, $params['out_card'])))
                    {
                    	$return_send['code'] = 5; $return_send['text'] = '出牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    }

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

	private function handle_out_card($chair, $out_card_arr)
	{
		$is_current = true;
		//$out_card_arr = explode(",", $out_card);
		//校验用户出的牌
		if(empty($out_card_arr))
		{
			return false;
		}
		foreach($out_card_arr as $v)
		{
			if(!in_array($v, $this->m_sPlayer[$chair]->card))
			{
				return false;
        	}
		}

   		//整理用户出的牌并校验信息
		$tmp_out = new Outed_card_landlord();
		$tmp_arr = ConstConfig::CARD_TEMP_LANDLORD;
		foreach($out_card_arr as $v)
		{
			$this->_list_insert_sub($tmp_arr, $v);
		}
		$tmp_out->pai_type = $this->_get_pai_type($tmp_arr, count($out_card_arr));

		if($tmp_out->pai_type == ConstConfig::PAI_TYPE_LANDLORD_INVALID)
		{
			return false;
		}
		$tmp_out->level = $this->_level_buffer($tmp_out->pai_type,$tmp_arr);
		$tmp_out->chair = $chair;
		$tmp_out->card = $out_card_arr;
		$tmp_out->len = count($out_card_arr);

		$first_outd_card = 0;
		if(empty($this->m_outed->card) || $this->m_outed->chair == $chair)
		{
			//首出牌			
			$first_outd_card = 1;
		}
		else
		{
			//管牌 比大小
			if($tmp_out->pai_type == ConstConfig::PAI_TYPE_LANDLORD_JOKER_BOMB)
			{}
			else if($tmp_out->pai_type == ConstConfig::PAI_TYPE_LANDLORD_BOMB
			 && $this->m_outed->pai_type != ConstConfig::PAI_TYPE_LANDLORD_BOMB
			 && $this->m_outed->pai_type != ConstConfig::PAI_TYPE_LANDLORD_JOKER_BOMB
			)
			{}
			else if($tmp_out->pai_type != $this->m_outed->pai_type
			 || $tmp_out->len != $this->m_outed->len
			 || $tmp_out->level <= $this->m_outed->level
			 )
			{
				return false;
			}
			else
			{}
		}

		if($tmp_out->pai_type == ConstConfig::PAI_TYPE_LANDLORD_JOKER_BOMB
			|| $tmp_out->pai_type == ConstConfig::PAI_TYPE_LANDLORD_BOMB
			)
		{
			$this->m_wTotalScore[$chair]->n_zhigang_wangang += 1; //炸弹
		}

		$tmp_card_str = '';
		foreach($out_card_arr as $v)
		{
			$this->_list_delete($chair, $v);
			$tmp_card_str .= $v."|";
		}

		//出牌录像
		$this->_set_record_game(ConstConfig::RECORD_P_DISCARD, $chair, $first_outd_card, rtrim($tmp_card_str,"|"));

		$this->m_outed = $tmp_out;
		$this->m_play_outed[$chair] = [clone($tmp_out), 0];
		for ($i=0; $i < $this->m_rule->player_count; $i++)
		{ 
			$this->m_play_outed[$i][1] = 0;
		}
		
		$this->m_HistoryOuted[$chair][] = clone($tmp_out);

		$this->m_bChooseBuf[$chair] = 0;
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_LANDLORD_STATUS_WAITING;

		// if(defined("gf\\conf\\Config::AUTO_PASS") && Config::AUTO_PASS)
		// {
		// }
		// else
		{
			$next = $this->_anti_clock($chair, 1);
			$this->m_bChooseBuf[$next] = 1;
			$this->m_sPlayer[$next]->state = ConstConfig::PLAYER_LANDLORD_STATUS_THINK_OUTCARD;
			$this->m_chairCurrentPlayer = $next;
		}

		$this->_get_score_times();
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}
		//$this->m_outed->clear();

		if($this->m_sPlayer[$chair]->len <= 0)				//没牌啦
		{
			$this->HandleSetOver();
			return true;
		}

		return true;
	}

	//放弃出牌
	public function c_cancle_choice($fd, $params)
	{
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do {
            if( empty($params['rid'])
                || empty($params['uid'])
            )
            {
                $return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_LANDLORD_PLAY_CARD)
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

                    $this->m_sPlayer[$key]->state = ConstConfig::PLAYER_LANDLORD_STATUS_THINK_OUTCARD ;
                    $this->handle_cancle_choice($key);
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

	private function handle_cancle_choice($chair)
	{
		$this->m_bChooseBuf[$chair] = 0;
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_LANDLORD_STATUS_WAITING;
		$next = $this->_anti_clock($chair, 1);
		$this->m_bChooseBuf[$next] = 1;
		$this->m_sPlayer[$next]->state = ConstConfig::PLAYER_LANDLORD_STATUS_THINK_OUTCARD;
		$this->m_chairCurrentPlayer = $next;

		$this->m_play_outed[$chair] = [(object)null, 1];

		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		//录像过
		$this->_set_record_game(ConstConfig::RECORD_P_DISCARD, $chair, 0,255);

		if($this->m_outed->chair == $next)
		{
			$this->m_outed->clear();
			for ($i=0; $i < $this->m_rule->player_count; $i++)
			{ 
				$this->m_play_outed[$i] = [(object)null, 0];
			}
			for ($i=0; $i < $this->m_rule->player_count ; $i++)
			{
				$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			}
		}
	}

	//--------------------------------------------------------------------

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
			$data['m_room_players'] = $this->m_room_players;
			$data['m_rule'] = clone $this->m_rule;
			$data['m_Score'] = $this->m_Score;		//分数

			$data['m_wTotalScore'] = $this->m_wTotalScore;
			$data['m_ready'] = $this->m_ready;
			$data['m_cancle'] = $this->m_cancle;
			$data['m_cancle_first'] = $this->m_cancle_first;
			$data['m_show'] = $this->m_show;	//[]每个玩家明牌： 0未操作， 1明牌； 2不明牌
			$data['m_double'] = $this->m_double;	//[]每玩家加倍： 0未操作， 1加倍； 2不加倍
			$data['m_bid'] = $this->m_bid;	//[]每玩家叫牌： 0未操作，1分，2分，3分，255不叫
			$data['m_nChairBanker'] = $this->m_nChairBanker;  //庄家
			$data['m_nSetCount'] = $this->m_nSetCount;
		}

		$data['m_sysPhase'] = $this->m_sysPhase;	// 当前的阶段
		$data['m_landlord'] = $this->m_landlord;	//地主
		$data['m_nCountAllot'] = $this->m_nCountAllot;	// 发到第几张
		$data['m_nAllCardNum'] = $this->m_nAllCardNum;	//牌总数
		$data['m_nLastCards'] = $this->m_nLastCards;	//底牌
		$data['m_score_times'] = $this->m_score_times;
		if(!empty($this->m_cancle_time))
		{
			$data['m_cancle_time'] = $this->m_cancle_time + Config::CANCLE_GAME_CLOCKER_NUM - time(); 
		}

		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
            if ($i == $chair)
            {
                $data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
                $data['m_bChooseBuf'] = $this->m_bChooseBuf[$i];             //命令缓冲
            }
            else
            {
                $data['m_sPlayer'][$i] = (object)null;
            }

            if($this->m_show[$i] == 1)
            {
            	$data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
            }

			$data['m_sPlayer_len'][$i] = $this->m_sPlayer[$i]->len;
			$data['m_sPlayer_state'][$i] = $this->m_sPlayer[$i]->state;
			if($is_more && !empty($data['m_room_players'][$i]))
			{
				$data['m_room_players'][$i]['fd'] = 0;
			}
		}

		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_LANDLORD_SHOW_BRFORE)
		{
			$data['m_show'] = $this->m_show;	//[]每个玩家明牌： 0未操作， 1明牌； 2不明牌
			return $data;
		}

		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_LANDLORD_SHOW_AFTER)
		{
			$data['m_show'] = $this->m_show;	//[]每个玩家明牌： 0未操作， 1明牌； 2不明牌
			return $data;
		}

		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_LANDLORD_DIBS)
		{
			$data['m_bid'] = $this->m_bid;	//[]每玩家叫牌： 0未操作，1分，2分，3分，255不叫
			return $data;
		}

		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_LANDLORD_DOUBLE)
		{
			$data['m_double'] = $this->m_double;	//[]每玩家加倍： 0未操作， 1加倍； 2不加倍
			return $data;
		}

		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_LANDLORD_PLAY_CARD)
		{
            $data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;                                // 当前出牌者
            $data['m_nNumTableCards'] = $this->m_nNumTableCards;        // 玩家桌面牌数量
            $data['m_nTableCards'] = $this->m_nTableCards;    // 玩家桌面牌
            $data['m_outed'] = $this->m_outed;        //刚出的牌
            $data['m_play_outed'] = $this->m_play_outed;

			return $data;
		}

		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_LANDLORD_SET_OVER)
		{
			$data['m_sPlayer'] = $this->m_sPlayer;			// 玩家数据
			$data['m_wTotalScore'] = $this->m_wTotalScore;

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
			$data['player_score'] = $this->player_score;
			$data['player_cup'] = $this->player_cup;

			return $data;
		}
		return true;
	}

	public function HandleSetOver()
	{
		if($this->m_sysPhase == ConstConfig::SYSTEMPHASE_LANDLORD_SET_OVER)
		{
			return false;
		}

		$this->ScoreOneHuCal();

		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_LANDLORD_SET_OVER;

		$this->CalcHuScore(); //正常算分，此时无逃跑得失相等

		//下一局庄家
		if(255 == $this->m_nChairBankerNext)
		{
			$this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker, 1);
			//$this->m_nChairBankerNext = $this->m_nChairBanker;
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
		}
		//状态变化发消息
		$this->handle_flee_play(true);	//更新断线用户
		$this->_set_game_and_checkout();
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_game_over', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

	}


	public function ScoreOneHuCal()
	{
		$this->_get_score_times(true);
		$this->m_wHuScore = [0,0,0,0];

		$tmp_lord_win = -1;
		if($this->m_sPlayer[$this->m_landlord]->len <= 0)
		{
			$tmp_lord_win = 1;
		}

		for($i = 0; $i < $this->m_rule->player_count; $i++)
		{
			$tmp_score = $this->m_base_score * $this->m_score_times[$i];
			if($i == $this->m_landlord)
			{
				$this->m_wHuScore[$i] += $tmp_lord_win*$tmp_score;
			}
			else
			{
				$this->m_wHuScore[$i] -= $tmp_lord_win*$tmp_score;
			}

			if($this->m_wHuScore[$i] > 0)
			{
				$this->m_wSetScore[$i] += $this->m_wHuScore[$i];
				$this->m_wTotalScore[$i]->n_jiepao += 1;
			}
			else
			{
				$this->m_wSetLoseScore[$i] += $this->m_wHuScore[$i];
				$this->m_wTotalScore[$i]->n_dianpao += 1;
			}
		}
		return true;
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
			$this->m_Score[$i]->score = $this->m_wSetScore[$i] + $this->m_wSetLoseScore[$i];
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

	//总分处理
	public function WriteScore()
	{
		for($i = 0; $i < $this->m_rule->player_count; $i++)
		{
			$this->m_wTotalScore[$i]->n_score += $this->m_Score[$i]->score;
		}
	}

	//洗牌
	public function WashCard()
	{
		$this->m_nCardBuf = ConstConfig::ALL_CARD_LANDLORD;
		$this->m_nAllCardNum = ConstConfig::BASE_LANDLORD_CARD_NUM;
		if(defined("gf\\conf\\Config::TEST_PAI") && Config::TEST_PAI)
		{
			$this->m_nCardBuf = Config::ALL_CARD_LANDLORD_TEST;
		}

		if(Config::WASHCARD)
		{
			shuffle($this->m_nCardBuf); shuffle($this->m_nCardBuf);
		}
	}

	//批量发牌
	public function DealAllCardEx()
	{
		$temp_card = 255;
		$this->WashCard();

		//$this->_deal_test_card();
		$tmp_card_arr = ['', '', ''];
		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$tmp_card_arr[$i] = ConstConfig::BASE_LANDLORD_HOLD_CARD_NUM;
			for($k=0; $k<ConstConfig::BASE_LANDLORD_HOLD_CARD_NUM; $k++)
			{
				$temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];	//从牌缓冲区里那张牌
				$this->_list_insert($i, $temp_card);
				$tmp_card_arr[$i] .= "|".$temp_card;
			}
		}
		$this->_set_record_game(ConstConfig::RECORD_P_DEAL, $tmp_card_arr[0], $tmp_card_arr[1], $tmp_card_arr[2]);
	}

	public function game_to_playing()
	{
		//状态设定
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_LANDLORD_PLAY_CARD ;
		$this->m_chairCurrentPlayer = $this->m_landlord;
		$this->m_wTotalScore[$this->m_landlord]->n_angang += 1;//地主次数

		$this->_get_score_times();
		//状态变化发消息
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			if($i != $this->m_landlord)
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_LANDLORD_STATUS_WAITING;
			}
			else
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_LANDLORD_STATUS_THINK_OUTCARD;
				$this->m_bChooseBuf[$i] = 1;
			}
		}
		
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}
		$this->handle_flee_play(true);	//更新断线用户
	}

	public function start_show($systemphase_landlord)
	{
		$this->m_sysPhase = $systemphase_landlord;
		for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
		{
			$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_LANDLORD_STATUS_THINK_SHOW;
			//发消息
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
		}
	}

	public function start_bid()
	{
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_LANDLORD_DIBS;
		for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
		{
			$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_LANDLORD_STATUS_WAITING;
			if($i == $this->m_nChairBanker)
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_LANDLORD_STATUS_DIBS;
			}
		}
		for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
		{
			//发消息
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
		}
	}

	public function start_double()
	{
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_LANDLORD_DOUBLE;
		for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
		{
			$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_LANDLORD_STATUS_THINK_DOUBLE;
		}
		$this->_get_score_times();

		for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
		{
			//发消息
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
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

		if(!empty($this->m_rule->is_show) && $this->m_rule->is_show == 2)
		{
			$this->start_show(ConstConfig::SYSTEMPHASE_LANDLORD_SHOW_BRFORE);
			return true;
		}

		//发牌
		$this->DealAllCardEx();

		if(!empty($this->m_rule->is_show) && $this->m_rule->is_show == 1)
		{
			$this->start_show(ConstConfig::SYSTEMPHASE_LANDLORD_SHOW_AFTER);
			return true;
		}

		//叫牌/抢地主
		if(!empty(Config::RES_BID) && $this->m_nResBid == Config::RES_BID)
		{
			$this->res_bid();
			return true;
		}

		$this->start_bid();

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

	//获取牌型
	private function _get_pai_type($buffer_arr, $buffer_sum)
	{
		$zero = 0; $one = 0; $pair = 0; $triple = 0; $quartet = 0;
		for($i=1; $i<=15; $i++)
		{	//统计单张，对子，三同，四同各有多少
			if($buffer_arr[$i][0] == 1)
				$one++;
			else if($buffer_arr[$i][0] == 2)
				$pair++;
			else if($buffer_arr[$i][0] == 3)
				$triple++;
			else if($buffer_arr[$i][0] == 4)
				$quartet++;
	    	else
	    		$zero++;
		}

		//printf("单%d 对%d 三%d 四%d 零%d,sum%d===\n",one,pair,triple,quartet,zero,buffer_sum);
		if($buffer_sum <= 0)
		{
			return ConstConfig::PAI_TYPE_LANDLORD_INVALID;
		}
		else if($buffer_sum <= 5)
		{//1~5
			if($one == 1 && !$pair && !$triple && !$quartet)
				return ConstConfig::PAI_TYPE_LANDLORD_ONE;
			if($pair == 1 && !$one && !$triple && !$quartet)
				return ConstConfig::PAI_TYPE_LANDLORD_PAIR;
			if($one == 2 && $buffer_arr[14][0] && $buffer_arr[15][0])
				return ConstConfig::PAI_TYPE_LANDLORD_JOKER_BOMB;
			if($triple == 1 && !$one && !$pair && !$quartet)
				return ConstConfig::PAI_TYPE_LANDLORD_TRIPLE;
			if($one ==1 && !$pair && $triple == 1 && !$quartet )
				return ConstConfig::PAI_TYPE_LANDLORD_TRIPLE_ONE;
			if(!$one && $pair == 1 && $triple == 1 && !$quartet)
				return ConstConfig::PAI_TYPE_LANDLORD_TRIPLE_PAIR;
			if(!$one && !$pair && !$triple && $quartet == 1)
				return ConstConfig::PAI_TYPE_LANDLORD_BOMB;
			if(!$pair && !$triple && !$quartet && (!$buffer_arr[13][0] && !$buffer_arr[14][0] && !$buffer_arr[15][0]) && $buffer_sum >= 5)
			{//顺子
      			if( $this->_is_continued(1, $one, $buffer_arr))
      				return ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT;
				else
					return ConstConfig::PAI_TYPE_LANDLORD_INVALID;
			}
    	}
		else if($buffer_sum >= 6)
		{
			if(($one == 2) && (!$pair) && (!$triple) && ($quartet == 1) )
				return ConstConfig::PAI_TYPE_LANDLORD_QUARTET_TWO;
			if((!$one) && ($pair == 1) && (!$triple) && ($quartet == 1) )
				return ConstConfig::PAI_TYPE_LANDLORD_QUARTET_TWO;
			if((!$one) && ($pair == 2) && (!$triple) && ($quartet == 1) )
				return ConstConfig::PAI_TYPE_LANDLORD_QUARTET_TWO_PAIR;
			if((!$one) && (!$pair) && (!$triple) && ($quartet == 2) )
				return ConstConfig::PAI_TYPE_LANDLORD_QUARTET_TWO_PAIR;
		    if(!$one && !$pair && !$quartet)
		    {
				if($this->_is_continued(3, $triple, $buffer_arr))
					return ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE;
				else
				{
					//处理 特殊牌型   333444555 777
					if($buffer_sum == 12 || $buffer_sum == 16)
					{
						$total = $this->_continued_max(3, $buffer_sum/4, $buffer_arr);
						if($total >= $buffer_sum/4)
						{
							return ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE_ONE;
						}
					}

					return ConstConfig::PAI_TYPE_LANDLORD_INVALID;
				}
		    }
		    if(!$one && !$triple && !$quartet)
		    {
		    	if($this->_is_continued(2, $pair, $buffer_arr))
		    		return ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT_PAIR;
		    	else
					return ConstConfig::PAI_TYPE_LANDLORD_INVALID;
		    }
		    if($buffer_sum == (3+1) * ($triple + $quartet))
		    {
				if($this->_is_continued(3, ($triple + $quartet), $buffer_arr))
					return ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE_ONE;
				else
					return ConstConfig::PAI_TYPE_LANDLORD_INVALID;
		    }
		    if(($buffer_sum == (3+2) * $triple) && ($triple == $pair || $triple == $quartet*2 + $pair))
		    {
		    	if($this->_is_continued(3, $triple, $buffer_arr))
		    		return ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE_PAIR;
		    	else
		    		return ConstConfig::PAI_TYPE_LANDLORD_INVALID;
		    }
		    if(!$pair && !$triple && !$quartet &&(!$buffer_arr[13][0] && !$buffer_arr[14][0] && !$buffer_arr[15][0]))
		    {
				if($this->_is_continued(1, $one, $buffer_arr))
					return ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT;
				else
					return ConstConfig::PAI_TYPE_LANDLORD_INVALID;
		    }

			if( ($triple+$quartet)>=3 )
            {
                //处理 特殊牌型   333444555666  8888 //  333444555666 8889 // 3334444555666 777
				if($buffer_sum == 12 || $buffer_sum == 16)
				{
					$total = $this->_continued_max(3, $buffer_sum/4, $buffer_arr);
					if($total >= $buffer_sum/4)
					{
						return ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE_ONE;
					}
				}
            }

			if($buffer_sum == 20 && ($triple + $quartet) >= 5 )
            {
                $total = $this->_continued_max(3, 5, $buffer_arr);
                if($total >= 5)
                {
                    return ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE_ONE;
                }
            }

		    return ConstConfig::PAI_TYPE_LANDLORD_INVALID;
		}
	}

    //判断是否连续
	private function _is_continued($num, $total, $buffer_arr)
	{
		$count = 0;
		$flag = 0;//有值则标记为1
		$sig = 0;//从 有到无 改标记为1
		for($i=1; $i<=15; $i++)
		{
		    if($buffer_arr[$i][0] >= $num)
		    {
		    	if($sig)
		    	{
					return 0;//非连续
				}
				$count++;
				if($count == $total)
				{
					return 1;//连续
				}
				$flag=1;
			}
			else
			{
				if($flag)
				{
					$sig = 1;
				}
			}
		}
		return 0;
	}

	//判断 >= $num  的连续个数
	private function _continued_max($num, $max, $buffer_arr)
    {
        $count = 0;
        $count_tmp = [0];    //赋值[0]  保护max()函数
        for($i=1; $i<=12; $i++)
        {
            if($buffer_arr[$i][0] >= $num)
            {
                $count++;
                if($count == $max)
                {
                   $count_tmp[] = $count;
                   break;
                }
            }
            else
            {
                $count_tmp[] = $count;
                $count = 0;
            }
        }

        return max($count_tmp);
    }

	//获取牌型类等级
	private function _level_buffer($type, $buffer_arr)
	{
		switch($type)
		{
			case ConstConfig::PAI_TYPE_LANDLORD_ONE:
				return $this->_max_index(1, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_LANDLORD_PAIR:
				return $this->_max_index(2, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_LANDLORD_JOKER_BOMB:
				return $this->_max_index(1, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_LANDLORD_TRIPLE:
				return $this->_max_index(3, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_LANDLORD_TRIPLE_ONE:
				return $this->_max_index(3, $buffer_arr);
				break;

			case ConstConfig::PAI_TYPE_LANDLORD_TRIPLE_PAIR:
				return $this->_max_index(3, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_LANDLORD_BOMB:
				return $this->_max_index(4, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_LANDLORD_QUARTET_TWO:
				return $this->_max_index(4, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_LANDLORD_QUARTET_TWO_PAIR:
				return $this->_max_index(4, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT:
				return $this->_max_index(1, $buffer_arr);
				break;

			case ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT_PAIR:
				return $this->_max_index(2, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE:
				return $this->_max_index(3, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE_ONE:
				return $this->_max_index(3, $buffer_arr ,true);
				break;
			case ConstConfig::PAI_TYPE_LANDLORD_STRAIGHT_TRIPLE_PAIR:
				return $this->_max_index(3, $buffer_arr, true, true);
				break;
			default:
				return 0;
				break;
		}
	}

	//最大下标
	private function _max_index($count, $buffer_arr, $continued = false, $type = false)
	{
		for ($i=15; $i>=1; $i--)
		{
			 for ($i=15; $i>=1; $i--)
			{
				if( (!$type && $buffer_arr[$i][0] >= $count) || ($type && $buffer_arr[$i][0] == $count) )   //type为true时处理 333444555 666677 飞机带对
				{
					if($continued && $i < 13  && $buffer_arr[$i-1][0] >= $count )  // continued为true是  处理 333444555 888 飞机带单
					{
						return $i;
					}

					if(!$continued)
					{
					return $i;
					}
				}
			}
		}
		return 0;
	}

	private function _list_insert_sub(&$arr, $card)
	{
		$card_type = $this->_get_card_type($card);
		if($card_type == ConstConfig::PAI_TYPE_LANDLORD_INVALID)
		{
			echo("错误牌类型，_list_insert_sub".__LINE__.__CLASS__);
			return false;
		}
		$card_key = $card % 8;
		$arr[$card_type][$card_key] += 1;
		$arr[$card_type][0] += 1;

		return true;
	}

	private function _list_delete_sub(&$arr, $card)
	{
		$card_type = $this->_get_card_type($card);
		if($card_type == ConstConfig::PAI_TYPE_LANDLORD_INVALID)
		{
			return false;
		}
		$card_key = $card % 8;
		if($arr[$card_type][$card_key] > 0)
		{
			$arr[$card_type][$card_key] -= 1;
			$arr[$card_type][0] -= 1;
			return true;
		}
		return false;
	}

	//插入牌
	private function _list_insert($chair, $card)
	{
		$this->m_sPlayer[$chair]->card[] = $card;
		$this->m_sPlayer[$chair]->len += 1;
	}

	//删除牌
	private function _list_delete($chair, $card)
	{
		$key = array_search($card, $this->m_sPlayer[$chair]->card);
		if(isset($key))
		{
			array_splice($this->m_sPlayer[$chair]->card, $key, 1);
		}
		$this->m_sPlayer[$chair]->len -= 1;
	}

	//返回牌的类型
	private function _get_card_type($card)
	{
		$type = floor($card / 8);
		if($type >0 && $type < 16)
		{
			return $type;
		}
		return ConstConfig::PAI_TYPE_LANDLORD_INVALID;
	}

	//  牌index
	private function _get_card_index($type, $key)
	{
		if($type > 0  && $type < 16 && $key >=1 && $key <= 5)
		{
			return $type * 8 + $key;
		}
		return 0;
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
        	echo "error ".__CLASS__.__LINE__;
        }

        return;
	}

    public function _cancle_game()
    {
        $cancle_count = 0;
        $yes_count = 0;
        $no_count = 0;
        $is_cancle = 0;
        if($this->_is_clocker() && !empty($this->m_rule->cancle_clocker))
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
        
        if(!empty($this->m_cancle_time) && ($this->m_cancle_time + Config::CANCLE_GAME_CLOCKER_NUM - time() <= Config::CANCLE_GAME_CLOCKER_LIMIT))
        {
            $this->m_room_state = ConstConfig::ROOM_STATE_OVER;
            $is_cancle = 1;
        }

        $this->_send_cmd('s_cancle_game', array('is_cancle'=>$is_cancle, 'm_cancle_first'=>$this->m_cancle_first, 'm_cancle'=>$this->m_cancle, 'cancle_time_start'=>$cancle_time_start), Game_cmd::SCO_ALL_PLAYER );

        if($is_cancle == 1)
        {
            $is_log = false;
            if(($this->m_nSetCount > 1) || ($this->m_nChairBankerNext != 255 && $this->m_nSetCount == 1 && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext)))
            {
                $is_log = true;
            }
            $this->m_sysPhase = ConstConfig::SYSTEMPHASE_LANDLORD_SET_OVER;
            $this->m_nSetCount = 255;	//用于解散结束牌局判定
            $this->m_ready = array(0,0,0,0);
            $this->m_end_time = date('Y-m-d H:i:s', time());
            //发送结束结算
            $this->_send_cmd('s_game_over', $this->OnGetChairScene($this->m_cancle_first, true), Game_cmd::SCO_ALL_PLAYER );

            $this->_set_game_and_checkout($is_log);

            $this->clear();
        }

        return $is_cancle;
    }

    public function _is_clocker()
    {
        if(defined("gf\\conf\\Config::CANCLE_GAME_CLOCKER_NUM") && defined("gf\\conf\\Config::CANCLE_GAME_CLOCKER_LIMIT"))
        {
            return true;
        }
        else
        {
            return false;
        }
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

	public function _set_record_game($act, $param_1 = 0, $param_2 = 0, $param_3 = 0)
	{
		if($act == ConstConfig::RECORD_P_DDZ_LANDLORD)			
		{
			$this->m_record_game[] = $act.'|'.$param_1;
		}
		else
		{
			$this->m_record_game[] = $act.'|'.$param_1.'|'.$param_2.'|'.$param_3;
		}
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
		$pai = ConstConfig::PAI_TYPE_TONG;
		$key = mt_rand(1,9);
		$change_arr[] = $this->_get_card_index($pai, $key);
		$change_arr[] = $this->_get_card_index($pai, $key);

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
			$offset = $chair * ConstConfig::BASE_HOLD_CARD_NUM_TUIDABING;
			for($m = 0; $m < ConstConfig::BASE_HOLD_CARD_NUM_TUIDABING; $m++)
			{
				$tmp = $this->m_nCardBuf[$m];
				$this->m_nCardBuf[$m] = $this->m_nCardBuf[$m + $offset];
				$this->m_nCardBuf[$m + $offset] = $tmp;
			}
		}
	}


	private function _get_score_times($is_end = false)
	{
		//is have lord?
		if($this->m_landlord == 255)
		{
			$tmp_bid = 1;
			$i = $this->m_nChairBanker;
			do
			{
				if($this->m_bid[$i] > $tmp_bid && $this->m_bid[$i] != 255)
				{
					$tmp_bid = $this->m_bid[$i];
				}
				$i = $this->_anti_clock($i, 1);
			}while ( $i != $this->m_nChairBanker);

			for($i=0; $i < $this->m_rule->player_count ;$i++)
			{
				//明牌加倍
				if(!empty($this->m_show[$i]) && $this->m_show[$i] == 1)
				{
					$this->m_score_times[$i] = $tmp_bid * self::$attached_type_arr[self::ATTACHED_TYPE_SHOW][1];
				}
				else if(!empty($this->m_show[$i]) && $this->m_show[$i] == 3)
				{
					$this->m_score_times[$i] = $tmp_bid * self::$attached_type_arr[self::ATTACHED_TYPE_BEFORE_SHOW][1];
				}			
			}
		}
		else
		{
			$tmp_bid = $this->m_bid[$this->m_landlord];
			$tmp_bid = empty($tmp_bid)? 1 : $tmp_bid;

			$num_JokerBomb_tmp = 0;  //火箭次数
			$num_Bomb_tmp = 0;    //炸弹次数
			$tmp_times = [1, 1, 1, 1]; //临时变量，每个农民的明牌和加倍的倍数
			$num_Spring_tmp = 0;      //春天临时变量

			for($i=0; $i < $this->m_rule->player_count ;$i++)
			{
				foreach($this->m_HistoryOuted[$i] as $v)
				{
					if($v->pai_type == ConstConfig::PAI_TYPE_LANDLORD_JOKER_BOMB)
					{
						$num_JokerBomb_tmp++;
					}

					if($v->pai_type == ConstConfig::PAI_TYPE_LANDLORD_BOMB )
					{
						$num_Bomb_tmp++;
					}
				}

				$tmp_i_times = 1;
				if(!empty($this->m_show[$i]) && $this->m_show[$i] == 1)
				{
					$tmp_i_times *= self::$attached_type_arr[self::ATTACHED_TYPE_SHOW][1];
				}
				else if(!empty($this->m_show[$i]) && $this->m_show[$i] == 3)
				{
					$tmp_i_times *= self::$attached_type_arr[self::ATTACHED_TYPE_BEFORE_SHOW][1];
				}
				if(!empty($this->m_double[$i]) && $this->m_double[$i] == 1)
				{
					$tmp_i_times *= self::$attached_type_arr[self::ATTACHED_TYPE_DOUBLE][1];
				}
				else if(!empty($this->m_double[$i]) && $this->m_double[$i] == 3)
				{
					$tmp_i_times *= self::$attached_type_arr[self::ATTACHED_TYPE_SUPER_DOUBLE][1];
				}
				$tmp_times[$i] *= $tmp_i_times;
					
				//农民是否出过牌
				if($i != $this->m_landlord && count($this->m_HistoryOuted[$i]) > 0)
				{
					$num_Spring_tmp += 1;
				}
			}

			//春天加倍
			$tmp_spring_times = 1;
			if($is_end)
			{
				if(count($this->m_HistoryOuted[$this->m_landlord]) > 0 && $num_Spring_tmp == 0)
				{
					$tmp_spring_times = self::$attached_type_arr[self::ATTACHED_TYPE_SPRING][1];
					$this->m_wTotalScore[$this->m_landlord]->n_zimo += 1;
				}
				else if(1 == count($this->m_HistoryOuted[$this->m_landlord]) && $num_Spring_tmp > 0 )
				{
					$tmp_spring_times = self::$attached_type_arr[self::ATTACHED_TYPE_NOSPRING][1];

					for($i = 0; $i < $this->m_rule->player_count; $i++)
					{
						if($i == $this->m_landlord)
						{
							continue;
						}
						$this->m_wTotalScore[$i]->n_zimo += 1;
					}
				}
			}

			for($i=0; $i < $this->m_rule->player_count ;$i++)
			{
				if($i == $this->m_landlord)
				{
					continue;
				}
				$tmp_times[$i] *= $tmp_times[$this->m_landlord];
				$this->m_score_times[$i] = $tmp_times[$i];
				$this->m_score_times[$i] *= self::$pai_type_arr[ConstConfig::PAI_TYPE_LANDLORD_JOKER_BOMB][1] << $num_JokerBomb_tmp;
				$this->m_score_times[$i] *= self::$pai_type_arr[ConstConfig::PAI_TYPE_LANDLORD_BOMB][1] << $num_Bomb_tmp;
				$this->m_score_times[$i] *= $tmp_spring_times;
				$this->m_score_times[$i] *= $tmp_bid;
				if (!empty($this->m_rule->top_fan) && $this->m_score_times[$i] >= $this->m_rule->top_fan/2)
				{
					$this->m_score_times[$i] = $this->m_rule->top_fan/2;
				}
			}

			$this->m_score_times[$this->m_landlord] = 0;
			for($i=0; $i < $this->m_rule->player_count ;$i++)
			{
				if($i != $this->m_landlord)
				{
					$this->m_score_times[$this->m_landlord] += $this->m_score_times[$i];
				}
			}

			for($i=0; $i < $this->m_rule->player_count ;$i++)
			{
				$this->m_hu_desc[$i] = $this->m_base_score.",".($this->m_base_score * $this->m_score_times[$i]);
			}
		}
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

    	//积分场新增
    	if (isset($this->m_rule->is_score_field)) 
    	{
    		//计算总分判断是否流局或平局
	        $is_not_draw = 0;
	        for($i = 0; $i < $this->m_rule->player_count; $i++)
			{
				if (!empty($this->m_wTotalScore[$i]->n_score)) 
				{
	        		$is_not_draw = 1;break;
				}
			}
	        
	        //全部打完,结算积分和奖杯
	        if ($this->m_nSetCount != 255 && $this->m_rule->set_num <= $this->m_nSetCount && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext) && isset($this->m_rule->is_score_field)) 
	        {
	        	if (empty($this->m_rule->is_score_field)) 
	        	{
	        		for($i = 0; $i < $this->m_rule->player_count; $i++)
			        {
		            	$this->player_score[$i] = 1;
		                $currency_change_group[$this->m_room_players[$i]['uid']][] = ['currency' => 1, 'type' => 32];
			        }
	        	}

	        	if (!empty($is_not_draw))
	        	{
	        		$this->integral_settlement($currency_change_group);
	        	}
	        }
    	}
        
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

    //积分结算
    public function integral_settlement(&$currency_change_group)
    {
        $big_score = 0;
        $small_score = 0;
        $draw_score = 0;
        $winner_arr	= array();
        $loser_arr	= array();
        $drawer_arr = array();
        $http_conf = Room::$get_conf;
        $http_conf = $http_conf['data'];

        for($i = 0; $i < $this->m_rule->player_count; $i++)
        {
            if($this->m_wTotalScore[$i]->n_score > $big_score)
            {
                $big_score = $this->m_wTotalScore[$i]->n_score;
                $winner_arr	= array();
                $winner_arr[$i] = $this->m_room_players[$i]['uid'];
            }
            else if($this->m_wTotalScore[$i]->n_score == $big_score && !empty($this->m_room_players[$i]))
            {
                $winner_arr[$i] = $this->m_room_players[$i]['uid'];
            }

            if($this->m_wTotalScore[$i]->n_score < $small_score)
            {
                $small_score = $this->m_wTotalScore[$i]->n_score;
                $loser_arr	= array();
                $loser_arr[$i] = $this->m_room_players[$i]['uid'];
            }
            else if($this->m_wTotalScore[$i]->n_score == $small_score && !empty($this->m_room_players[$i]))
            {
                $loser_arr[$i] = $this->m_room_players[$i]['uid'];
            }
        }

        $winner_count = empty($winner_arr) ? 0: count($winner_arr);
        $loser_count = empty($loser_arr) ? 0 : count($loser_arr);
        
        if ($this->m_rule->player_count > ($winner_count + $loser_count)) 
        {
        	for($i = 0; $i < $this->m_rule->player_count; $i++)
            {
            	if (!in_array($this->m_room_players[$i]['uid'], $winner_arr) && !in_array($this->m_room_players[$i]['uid'], $loser_arr)) 
            	{
            		if (empty($drawer_arr)) 
        			{
        				$draw_score = $this->m_wTotalScore[$i]->n_score;
        				$drawer_arr[$i] = $this->m_room_players[$i]['uid'];
        			}
        			else
        			{
        				if ($this->m_wTotalScore[$i]->n_score == $draw_score) 
        				{
        					$drawer_arr[$i] = $this->m_room_players[$i]['uid'];
        				}
        				else
        				{
        					if ($this->m_wTotalScore[$i]->n_score > $draw_score) 
        					{
        						$loser_arr= array_merge($loser_arr, $drawer_arr);
        						$drawer_arr = [];
        						$drawer_arr[$i] = $this->m_wTotalScore[$i]->n_score;
        					}
        					else
        					{
								$loser_arr[$i] = $this->m_room_players[$i]['uid'];
        					}
        				}
        			}
            	}
            }
        }
        
        $drawer_count = empty($drawer_arr) ? 0 : count($drawer_arr);

        //积分场送奖杯
    	$cup_present_tmp = $http_conf['cup_present'][$this->m_rule->score];
    	if ($this->m_rule->player_count == 4) 
    	{
    		$cup_present = $cup_present_tmp;
    	}
    	else
    	{
    		$cup_present = [$cup_present_tmp[0], $cup_present_tmp[2], $cup_present_tmp[3]];
    	}

        if ($this->m_rule->is_score_field == 0) 
        {
        	//普通场大赢家送奖杯
        	foreach ($winner_arr as $key => $item_user)
            {	
            	$this->player_cup[$key] = 1;
                $currency_change_group[$item_user][] = ['currency' => 1, 'type' => 41];
            }
        }
        elseif (!empty($this->m_rule->is_score_field) && !empty($this->m_rule->score)) 
        {
            //平家
            if (!empty($drawer_count))
            {	
            	$drawer_total_cup = 0;
            	for ($i=$winner_count; $i < $winner_count + $drawer_count; $i++) 
	        	{ 
	        		$drawer_total_cup += $cup_present[$i];
	        	}
	        	$drawer_cup = ceil($drawer_total_cup/$drawer_count);
	        	foreach ($drawer_arr as $key => $item_user)
	            {
	            	$this->player_score[$key] = 0;
            		$this->player_cup[$key] = $drawer_cup;
                	$currency_change_group[$item_user][] = ['currency' => $drawer_cup, 'type' => 41];
	            }
            }
            //输家
            if (!empty($loser_count)) 
            {
            	$loser_total_cup = 0;
            	for ($i= ($this->m_rule->player_count-1); $i > ($this->m_rule->player_count -1 - $loser_count); $i--) 
	        	{ 
	        		$loser_total_cup += $cup_present[$i];
	        	}
	            $loser_cup = ceil($loser_total_cup/$loser_count);
	            $loser_score = - $this->m_rule->score;
	        	foreach ($loser_arr as $key => $item_user)
	            {
	            	$this->player_score[$key] = $loser_score;
            		$this->player_cup[$key] = $loser_cup;
                	$currency_change_group[$item_user][] = ['currency' => $loser_score, 'type' => 31];
                	$currency_change_group[$item_user][] = ['currency' => $loser_cup, 'type' => 41];
	            }
            }

            //大赢家
        	if (!empty($winner_count)) 
        	{
        		$winner_total_cup = 0;
        		for ($i=0; $i < $winner_count; $i++) 
	        	{ 
	        		$winner_total_cup += $cup_present[$i];
	        	}
	        	$winner_cup = ceil($winner_total_cup/$winner_count);
	        	$winner_total_score = $this->m_rule->score * $loser_count - $this->m_rule->player_count * $this->m_rule->score * $http_conf['score_decuct_percent'];
	        	$winner_score = ceil($winner_total_score/$winner_count);
	        	
	        	foreach ($winner_arr as $key => $item_user)
	            {
	            	$this->player_score[$key] = $winner_score;
            		$this->player_cup[$key] = $winner_cup;
                	$currency_change_group[$item_user][] = ['currency' => $winner_score, 'type' => 31];
                	$currency_change_group[$item_user][] = ['currency' => $winner_cup, 'type' => 41];
	            }
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
			$game_table_info['date'] = date('Y-m-d H:i:s', $itime);
			$game_table_info['display'] =  $this->m_rule->game_type['display'];
			$game_table_info['player_count'] =  $this->m_rule->player_count;
			$game_table_info['set_num'] =  $this->m_rule->set_num;
			$game_table_info['pay_type'] =  $this->m_rule->pay_type;
			if (!empty($agent_uid)) 
			{
				$game_table_info['is_circle'] =  empty($this->m_rule->is_circle) ? 0 : $this->m_rule->is_circle;
			}
			if (isset($this->m_rule->is_score_field)) 
			{
				$game_table_info['is_score_field'] =  $this->m_rule->is_score_field;
				$game_table_info['score'] =  isset($this->m_rule->score) ? $this->m_rule->score : 0;
			}
			
			foreach ($this->m_room_players as $key => $room_user)
			{
				$game_table_info['play'][$key]['is_room_owner'] = $room_user['is_room_owner'];				
				$game_table_info['play'][$key]['uid'] = $room_user['uid'];				
				$game_table_info['play'][$key]['uname'] = $room_user['uname'];				
				$game_table_info['play'][$key]['totalscore'] = $this->m_wTotalScore[$key]->n_score;
				if (isset($this->m_rule->is_score_field) && isset($this->player_score)) 
				{
					$game_table_info['play'][$key]['integral'] = $this->player_score[$key];			
				}			
			}
		}

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
			//game_info=255 表示集散房间不记录录像
			if($this->m_nSetCount == 255 && $is_log)
			{
				BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'set_game_log', 'platform'=>'gfplay', 'rid'=>$this->m_room_id,'uid'=>$this->m_room_owner, 'uid_arr'=>implode(',', $uid_arr)
				, 'game_info'=>255,'type'=>1, 'is_room_over'=>$is_room_over
				, 'game_type'=>$this->m_game_type,  'game_table_info'=>json_encode($game_table_info, JSON_UNESCAPED_UNICODE), 'agent_uid' => $agent_uid
				));
			}
		}
    }

    //钻石结算
    public function diamond_settlement(&$currency_change_group, $is_room_over)
    {
        $http_conf = Room::$get_conf;
        $http_conf = $http_conf['data'];
    	$currency_tmp = 0;

    	if (!empty($this->m_rule->is_score_field) && !empty($http_conf['room_type_score'])) 
        {
        	$currency_tmp = BaseFunction::need_currency($http_conf['room_type_score'],$this->m_game_type,$this->m_rule->set_num);
        }
        else if(empty($this->m_rule->is_circle) && !empty($http_conf['room_type']))
        {
            $currency_tmp = BaseFunction::need_currency($http_conf['room_type'],$this->m_game_type,$this->m_rule->set_num);
        }
        else if(!empty($this->m_rule->is_circle) && !empty($http_conf['room_type_circle']))
        {
            $currency_tmp = BaseFunction::need_currency($http_conf['room_type_circle'],$this->m_game_type,($this->m_rule->set_num / $this->m_rule->player_count));
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
}
