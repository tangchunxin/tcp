<?php
/**
 * @author tangchunxin
 * @final 20161025
 */

namespace gf\inc;

use gf\inc\ConstConfig;
use gf\conf\Config;
use gf\inc\Room;
use gf\inc\BaseFunction;
use gf\inc\Game_cmd;

class GameRunFast
{
	const GAME_TYPE = 351;

	public $serv;	                                   	// socket服务器对象

	public $m_ready = array(0,0,0);	               	    // 用户准备
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
	public $m_nHolCardNum;	//手牌个数
	public $m_nAllCardNum;	//牌总数

	public $m_sysPhase;				                   	// 当前阶段状态
	public $m_chairCurrentPlayer;			           	// 当前出牌者
	public $m_bChooseBuf = array();			           	// 玩家的选择胡,吃,碰,杠命令 1 等待操作 0 无操作

    public $m_nTableCards = array();        		   	// 玩家的桌面牌
    public $m_nNumTableCards = array();        			// 玩家桌面牌数量

	public $m_first_out=255;							//第一个出牌的座位号

    public $m_outed;									//当前出牌
	public $m_play_outed = array();						// [$i][0]用户上次出的牌； [$i][0]用户上次状态： 0无操作 1过
	public $m_HistoryOuted; 					        //结束牌局之前的最后一次出牌的记录
	public $m_hu_desc = array();		               	// 详细的出牌类型(火箭 炸弹 春天 反春天 明牌.......)

	//public $m_score_times = array();					//每个玩家倍数
	public $m_base_score;								//底分

	public $player_score = array(0,0,0);                //结算时积分
	public $player_cup = array(0,0,0);                  //结算时奖杯
	
	public $agent_uid;                                  // 公会房代理的玩家id
	public $m_compensation = 255;                       //包赔玩家
    public $m_client_ip = array();                      // 用户ip
	
	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_TYPE_SPRING = 70;              		// 春天
	const ATTACHED_TYPE_NOSPRING = 71;            		// 反春天
	const ATTACHED_TYPE_SHOW = 1;                		// 发牌后明牌
	const ATTACHED_TYPE_BEFORE_SHOW = 3;                // 发牌前明牌
	const ATTACHED_TYPE_DOUBLE = 11;              		// 加倍
	const ATTACHED_TYPE_SUPER_DOUBLE = 13;              // 超级加倍

	///////////////////////方法/////////////////////////
	//构造方法
	public function __construct($serv)
	{
		$this->serv = $serv;
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_RUNFAST_SET_OVER ;
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
			$this->m_game_type = self::GAME_TYPE;	                //游戏类型，见http端协议
			$this->m_room_state = ConstConfig::ROOM_STATE_OVER ;	//房间状态
			$this->m_room_id = 0;	                                //房间号
			$this->m_room_owner = 0;	                            //房主
			$this->m_room_players = array();	                    //玩家信息
			$this->m_start_time = 0;	                            //开始时间
			$this->m_nSetCount = 0;                                 //比赛局数
			//$this->_on_table_status_to_playing(); //选庄稼   跑得快应该  没有用 

			$this->m_sPlayer = array();                             //玩家手牌私有数据 Play_data
			$this->m_ready = array(0,0,0);                          //用户准备
			for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
			{
				$this->m_wTotalScore[$i] = new TotalScore();        //总结的分数
			}
		}

		$this->m_record_game = array();                             //录制脚本

		if($this->m_nChairBankerNext != 255)
		{
			$this->m_nChairBanker = $this->m_nChairBankerNext;      //庄家位
		}
		$this->m_nChairBankerNext = 255;                            //下一局庄家
		//$this->m_first_out = 255;
		$this->m_outed = new Outed_card_runfast();

		$this->m_nCountAllot = 0;
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_RUNFAST_SET_OVER ;
		$this->m_chairCurrentPlayer = 255;
		$this->m_end_time = '';

		$this->m_cancle_first = 255;
		$this->m_cancle_time = 0;

		$this->player_score = array(0,0,0);                                
		$this->player_cup = array(0,0,0);

		$this->m_base_score = 1;  //跑得快
		$this->m_compensation = 255;
		
		for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
		{
			$this->m_wHuScore[$i] = 0;
			$this->m_wSetScore[$i] = 0;
			$this->m_wSetLoseScore[$i] = 0;
			$this->m_Score[$i] = new Score_Runfast();
			$this->m_bChooseBuf[$i] = 0;

			$this->m_cancle[$i] = 0;
			$this->m_sPlayer[$i] = new Play_data_runfast();

			$this->m_nTableCards[$i] = array();
			$this->m_nNumTableCards[$i] = 0;

			$this->m_hu_desc[$i] = '';
			//$this->m_score_times[$i] = 1;
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

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_RUNFAST_SET_OVER || $this->m_room_state == ConstConfig::ROOM_STATE_GAMEING )
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}
			elseif ($this->m_room_state == ConstConfig::ROOM_STATE_OPEN  && $this->m_room_owner != $params['uid'])
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$this->clear();
			$this->m_rule = new RuleRunFast();
			
			$params['rule']['game_type'] = isset($params['rule']['game_type']) ? $params['rule']['game_type']: 351;
			$params['rule']['player_count'] = isset($params['rule']['player_count']) ? $params['rule']['player_count']: 3;
			$params['rule']['set_num'] = isset($params['rule']['set_num']) ? $params['rule']['set_num']: 12;
			$params['rule']['min_fan'] = isset($params['rule']['min_fan']) ? $params['rule']['min_fan']: 0;
			$params['rule']['top_fan'] = isset($params['rule']['top_fan']) ? $params['rule']['top_fan']: 255;
	
			$params['rule']['pay_type'] = isset($params['rule']['pay_type']) ? $params['rule']['pay_type']: 0;
			$params['rule']['is_score_field'] = isset($params['rule']['is_score_field']) ? $params['rule']['is_score_field']: 0;
			$params['rule']['spades_3'] = isset($params['rule']['spades_3']) ? $params['rule']['spades_3']: 0;
			$params['rule']['must_out_card'] = isset($params['rule']['must_out_card']) ? $params['rule']['must_out_card']: 0;
			$params['rule']['card_num'] = isset($params['rule']['card_num']) ? $params['rule']['card_num']: 0;
			$params['rule']['cancle_clocker'] = !isset($params['rule']['cancle_clocker']) ? 1 : $params['rule']['cancle_clocker'];
	
			if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3)))
			{
				$params['rule']['player_count'] = 3;
			}

			$params['rule']['score'] = !isset($params['rule']['score']) ? 0 : $params['rule']['score'];
			$this->m_rule->score = $params['rule']['score'];
	
			///////////////////////////////////////////////////
	
			$this->m_rule->game_type = $params['rule']['game_type'];
			$this->m_rule->player_count = $params['rule']['player_count'];
			$this->m_rule->set_num = $params['rule']['set_num'];
			$this->m_rule->min_fan = $params['rule']['min_fan'];
			$this->m_rule->top_fan = $params['rule']['top_fan'];
	
			$this->m_rule->pay_type = $params['rule']['pay_type'];
			$this->m_rule->is_score_field = $params['rule']['is_score_field'];
			$this->m_rule->spades_3 = $params['rule']['spades_3'];
			$this->m_rule->must_out_card = $params['rule']['must_out_card'];
			$this->m_rule->card_num = $params['rule']['card_num'];
			$this->m_rule->cancle_clocker = $params['rule']['cancle_clocker'];
			
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

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_RUNFAST_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
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

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_RUNFAST_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
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
			if($this->m_nSetCount != 0 || $this->m_sysPhase != ConstConfig::SYSTEMPHASE_RUNFAST_SET_OVER)
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

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_RUNFAST_PLAY_CARD)
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
		
		if($this->m_nSetCount == 1 && $this->m_rule->spades_3 == 1 && empty($this->m_HistoryOuted) && $chair == $this->m_nChairBanker)
		{
			if(!in_array(12, $out_card_arr))  //黑桃3必出
			{
				return false;
        	}
		}
   		//整理用户出的牌并校验信息
		$tmp_out = new Outed_card_runfast();
		$tmp_arr = ConstConfig::CARD_TEMP_RUNFAST;
		foreach($out_card_arr as $v)
		{
			$this->_list_insert_sub($tmp_arr, $v);
		}
		$tmp_out->pai_type = $this->_get_pai_type($tmp_arr, count($out_card_arr));

		if($tmp_out->pai_type == ConstConfig::PAI_TYPE_RUNFAST_INVALID)
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
			$tmp_out->is_first = 1;
		}
		else
		{
			//管牌 比大小
	        if($tmp_out->pai_type == ConstConfig::PAI_TYPE_RUNFAST_BOMB
			 && $this->m_outed->pai_type != ConstConfig::PAI_TYPE_RUNFAST_BOMB
			)
			{}
			else if($tmp_out->pai_type != $this->m_outed->pai_type
			 //|| $tmp_out->len != $this->m_outed->len
			 || $tmp_out->level <= $this->m_outed->level
			 )
			{
				return false;
			}
			else
			{}
		}

		$tmp_card_str = '';
		foreach($out_card_arr as $v)
		{
			$this->_list_delete($chair, $v);
			$tmp_card_str .= $v."|";
		}

		//出牌录像
		$this->_set_record_game(ConstConfig::RECORD_P_DISCARD, $chair, $first_outd_card, rtrim($tmp_card_str,"|"));
		//单手牌为0时候判断上家是否包赔
		if($this->m_sPlayer[$chair]->len <= 0 && $tmp_out->len == 1 )
		{
			if($this->_compensation($chair,$tmp_out)) //上家包赔
			{
				$this->m_compensation = $this->_anti_clock($chair, -1);
				$this->m_wTotalScore[$this->m_compensation]->n_angang += 1; //包赔次数
			}
		}
		$this->m_outed = $tmp_out;
		$this->m_play_outed[$chair] = [clone($tmp_out), 0];
		for ($i=0; $i < $this->m_rule->player_count; $i++)
		{ 
			$this->m_play_outed[$i][1] = 0;
		}
		
		$this->m_HistoryOuted = clone($tmp_out);

		$this->m_bChooseBuf[$chair] = 0;
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_RUNFAST_STATUS_WAITING;

		// if(defined("gf\\conf\\Config::AUTO_PASS") && Config::AUTO_PASS)
		// {
		// }
		// else
        if($this->m_sPlayer[$chair]->len > 0)
		{
			$next = $this->_anti_clock($chair, 1);
			$this->m_bChooseBuf[$next] = 1;
			$this->m_sPlayer[$next]->state = ConstConfig::PLAYER_RUNFAST_STATUS_THINK_OUTCARD;
			$this->m_chairCurrentPlayer = $next;
		}
		else
        {
            $this->m_chairCurrentPlayer = 255;
        }

		//$this->_get_score_times();

        for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
        }

		//出牌发送发送动作
        /*for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $this->_send_cmd('s_act', end($this->m_record_game), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
        }*/

		//$this->m_outed->clear();

		if($this->m_sPlayer[$chair]->len <= 0)				//没牌啦
		{
			
			if($this->m_outed->pai_type == ConstConfig::PAI_TYPE_RUNFAST_BOMB)
			{
				$this->ScoreBomb($chair);
				$this->m_wTotalScore[$this->m_outed->chair]->n_zhigang_wangang += 1; //炸弹		
			}
			$this->m_nChairBankerNext = $chair;  //赢家
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

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_RUNFAST_PLAY_CARD)
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

                    $this->m_sPlayer[$key]->state = ConstConfig::PLAYER_RUNFAST_STATUS_THINK_OUTCARD ;
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
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_RUNFAST_STATUS_WAITING;
		$next = $this->_anti_clock($chair, 1);
		$this->m_bChooseBuf[$next] = 1;
		$this->m_sPlayer[$next]->state = ConstConfig::PLAYER_RUNFAST_STATUS_THINK_OUTCARD;
		$this->m_chairCurrentPlayer = $next;

		$this->m_play_outed[$chair] = [(object)null, 1];

		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		//录像过
		$this->_set_record_game(ConstConfig::RECORD_P_DISCARD, $chair, 0,255);

        //不出发送动作
        /*for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $this->_send_cmd('s_act', end($this->m_record_game), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
        }*/

		if($this->m_outed->chair == $next)
		{
			//炸弹被两家都选择过了,,,结算此次炸弹的分数
			if($this->m_outed->pai_type == ConstConfig::PAI_TYPE_RUNFAST_BOMB)
			{
				$this->ScoreBomb($this->m_outed->chair);

				$this->m_wTotalScore[$this->m_outed->chair]->n_zhigang_wangang += 1; //炸弹
			
			}

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

			//$data['m_wTotalScore'] = $this->m_wTotalScore;
			$data['m_ready'] = $this->m_ready;
			$data['m_cancle'] = $this->m_cancle;
			$data['m_cancle_first'] = $this->m_cancle_first;

			$data['m_nChairBanker'] = $this->m_nChairBanker;  //庄家
			$data['m_nSetCount'] = $this->m_nSetCount;
		}
		//$data['m_Score'] = $this->m_Score;
		$data['m_wTotalScore'] = $this->m_wTotalScore;

		$data['m_sysPhase'] = $this->m_sysPhase;	// 当前的阶段
		$data['m_nCountAllot'] = $this->m_nCountAllot;	// 发到第几张
		$data['m_nAllCardNum'] = $this->m_nAllCardNum;	//牌总数
		
		//$data['m_score_times'] = $this->m_score_times;
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

			$data['m_sPlayer_len'][$i] = $this->m_sPlayer[$i]->len;
			$data['m_sPlayer_state'][$i] = $this->m_sPlayer[$i]->state;
			if($is_more && !empty($data['m_room_players'][$i]))
			{
				$data['m_room_players'][$i]['fd'] = 0;
			}
		}

		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_RUNFAST_PLAY_CARD)
		{
            $data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;                                // 当前出牌者
            $data['m_nNumTableCards'] = $this->m_nNumTableCards;        // 玩家桌面牌数量
            $data['m_nTableCards'] = $this->m_nTableCards;    // 玩家桌面牌
            $data['m_outed'] = $this->m_outed;        //刚出的牌
            $data['m_play_outed'] = $this->m_play_outed;

			return $data;
		}

		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_RUNFAST_SET_OVER)
		{
			$data['m_sPlayer'] = $this->m_sPlayer;			// 玩家数据
			//$data['m_wTotalScore'] = $this->m_wTotalScore;

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

    //根据当前状态获取所需的数据
    public function OnGetChairScene_TEST($chair, $is_more=false)
    {
        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_INVALID)
        {
            echo("sysPhase invalid,".__LINE__.__CLASS__."\n");
            return false;
        }

        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_RUNFAST_PLAY_CARD)
        {
            $data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;                                // 当前出牌者
            $data['m_nNumTableCards'] = $this->m_nNumTableCards;        // 玩家桌面牌数量
            $data['m_nTableCards'] = $this->m_nTableCards;              // 玩家桌面牌
            $data['m_outed'] = $this->m_outed;                          //刚出的牌
            $data['m_play_outed'] = $this->m_play_outed;

            return $data;
        }


        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_RUNFAST_SET_OVER)
        {
            $data['m_sPlayer'] = $this->m_sPlayer;			    // 玩家数据(刷新手牌)
            $data['m_Score'] = $this->m_Score;		            //单局分数
            if ($this->m_nSetCount >= $this->m_rule->set_num)   //总结算(牌都是局数的)
            {
                $data['m_wTotalScore'] = $this->m_wTotalScore;
            }
            $data['m_hu_desc'] = $this->m_hu_desc;              //胡牌描述
            $data['m_end_time'] = $this->m_end_time;            //结束时间
            $data['player_score'] = $this->player_score;        //扣除积分
            $data['player_cup'] = $this->player_cup;            //扣除的奖杯

            return $data;
        }
        return true;
    }

	public function HandleSetOver()
	{
		if($this->m_sysPhase == ConstConfig::SYSTEMPHASE_RUNFAST_SET_OVER)
		{
			return false;
		}

		$this->ScoreOneHuCal();

		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_RUNFAST_SET_OVER;

		$this->CalcHuScore(); //正常算分，此时无逃跑得失相等

		//下一局庄家
		// if(255 == $this->m_nChairBankerNext)
		// {
		// 	$this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker, 1);
		// 	//$this->m_nChairBankerNext = $this->m_nChairBanker;
		// }

		//准备状态
		$this->m_ready = array(0,0,0);

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
			$this->_send_cmd('s_game_over', $this->OnGetChairScene_TEST($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

	}

	//判断上家是否包赔
	private function _compensation($win_chair ,$tmp_out)
	{
		if($this->m_HistoryOuted->len >  1 && $this->m_HistoryOuted->chair != $win_chair)
		{
			return false;
		}
		
		$return = false;
		$tmp_chair = $this->_anti_clock($win_chair, -1);
		$tmp_chair_card_arr = $this->m_sPlayer[$tmp_chair]->card;

		//如果是上家出的牌,下家跑了
		$m_outed_card_arr = $this->m_HistoryOuted->card;
		$last_card = $m_outed_card_arr[0];
		$last_card_level = floor($last_card / 8);

		$buffer_arr = ConstConfig::CARD_TEMP_RUNFAST;
		
		foreach($tmp_chair_card_arr as $v)
		{
				$v_level =  floor($v / 8);			
				if($last_card_level < $v_level)
				{
					$return = true;//出的这个单 不是最大的 ,,,此时还不一定是包赔
				}
			
			$this->_list_insert_sub($buffer_arr, $v);
		}
		
		if($this->m_HistoryOuted->chair == $tmp_chair && $this->m_HistoryOuted->len == 1)
		{
			foreach($m_outed_card_arr as $v)//把打出去的单张也放回去
			{
				$this->_list_insert_sub($buffer_arr, $v);
			}
			$zero = 0; $one = 0; $pair = 0; $triple = 0; $quartet = 0;
			for($i=1; $i<=13; $i++)
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
			
			//首次出牌 
			if($this->m_HistoryOuted->is_first == 1)
			{
				if($pair || $triple || $quartet)
				{
					return true;  //手里面有炸弹没有出,,包赔
				}

				//有可能有是个连,这个连只需判断  -4 -3 -2 -1 A
				if($this->m_sPlayer[$tmp_chair]->len + 1 >= 5 && $last_card >= 41)
				{
					if($buffer_arr[$last_card_level-1][0] >= 1 
					&& $buffer_arr[$last_card_level-2][0] >= 1 
					&& $buffer_arr[$last_card_level-3][0] >= 1 
					&& $buffer_arr[$last_card_level-4][0] >= 1 				
					)
					{
						return true;
					}
				}
				return $return;
	
			}
			else
			{
				//跟牌
				if($quartet)
				{
					return true;  //手里面有炸弹没有出,,包赔
				}
				//跟牌  要跟最大的
				return $return;
			}

		} 
		//上上家出的牌,或自己出的牌
		else
		{
			$zero = 0; $one = 0; $pair = 0; $triple = 0; $quartet = 0;
			for($i=1; $i<=13; $i++)
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
			//判断我是不是真的不能管上
			return $this->_judge_big($tmp_chair,$buffer_arr,$one,$pair,$triple,$quartet);

		}
	}

	//每次炸弹实时算分
	public function ScoreBomb($chair)
	{
		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			
			if($i != $chair)
			{
				$this->m_Score[$chair]->bomb_count += 10;  //炸弹分
				$this->m_Score[$i]->bomb_count -= 10;
				
				$this->m_wTotalScore[$chair]->n_score += 10;//总分
				$this->m_wTotalScore[$i]->n_score -= 10;
			}

		}	
	}

	public function ScoreOneHuCal()
	{
		//$this->_get_score_times(true);
		$this->m_wHuScore = [0,0,0];

		$tmp_runfast_win = -1;
		if($this->m_sPlayer[$this->m_nChairBankerNext]->len <= 0)
		{
			$tmp_runfast_win = $this->m_nChairBankerNext;  //赢家
		}
		//判断是否包牌

		for($i = 0; $i < $this->m_rule->player_count; $i++)
		{
			$tmp_score = 0;  //如果剩余小于1张的牌
			//$this->m_Score[$i]->clear();
			if($this->m_sPlayer[$i]->len > 1)
			{
				$tmp_score = $this->m_base_score * $this->m_sPlayer[$i]->len;

				if($this->m_nHolCardNum == $this->m_sPlayer[$i]->len)//关门
				{
					$tmp_score = $tmp_score * 2;
					$this->m_wTotalScore[$i]->n_zimo += 1; //关门
					$this->m_Score[$i]->times = 1;
				}
			}
	
			$this->m_wHuScore[$tmp_runfast_win] += $tmp_score;
			if($this->m_compensation !=255)  //包赔
			{
				$this->m_wHuScore[$this->m_compensation] -= $tmp_score;
			}
			else
			{
				$this->m_wHuScore[$i] -= $tmp_score;
			}

		}
		for($i = 0; $i < $this->m_rule->player_count; $i++)
		{

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
		// for($i=0; $i<$this->m_rule->player_count; $i++)
		// {
		// 	$this->m_Score[$i]->clear();
		// }
		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
	
			$this->m_Score[$i]->score += ($this->m_wSetScore[$i] + $this->m_wSetLoseScore[$i] + $this->m_Score[$i]->bomb_count);//赢+输+炸弹
			$this->m_Score[$i]->set_count = $this->m_nSetCount;
			
			//该玩家是否包赔
			if($this->m_compensation == $i)
			{
				$this->m_Score[$i]->is_baopei = 1;
			}

			if ($this->m_Score[$i]->score > 0)
			{
				$this->m_Score[$i]->win_count = 1;
			}
			else
			{
				$this->m_Score[$i]->lose_count = 1;
			}

			$this->m_room_players[$i]['score'] = $this->m_Score[$i]->score ;
		}
	}

	//总分处理
	public function WriteScore()
	{
		for($i = 0; $i < $this->m_rule->player_count; $i++)
		{
			$this->m_wTotalScore[$i]->n_score += ($this->m_Score[$i]->score - $this->m_Score[$i]->bomb_count);  //总分已经包含过一次炸弹的分,,,在这里要减去
		}
	}

	//洗牌
	public function WashCard()
	{
		if($this->m_rule->card_num == 0)  //经典16张
		{
			$this->m_nCardBuf = ConstConfig::ALL_CARD_RUNFAST16;
			$this->m_nAllCardNum = ConstConfig::BASE_RUNFAST_CARD_NUM16;
			$this->m_nHolCardNum = ConstConfig::BASE_RUNFAST_HOLD_CARD_NUM16;
			if(defined("gf\\conf\\Config::TEST_PAI") && Config::TEST_PAI)
			{
				$this->m_nCardBuf = Config::ALL_CARD_RUNFAST16_TEST;
			}
		}
		else
		{
			$this->m_nCardBuf = ConstConfig::ALL_CARD_RUNFAST15;
			$this->m_nAllCardNum = ConstConfig::BASE_RUNFAST_CARD_NUM15;
			$this->m_nHolCardNum = ConstConfig::BASE_RUNFAST_HOLD_CARD_NUM15;
			if(defined("gf\\conf\\Config::TEST_PAI") && Config::TEST_PAI)
			{
				$this->m_nCardBuf = Config::ALL_CARD_RUNFAST15D_TEST;
			}
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
			$tmp_card_arr[$i] = $this->m_nHolCardNum;
			for($k=0; $k<$this->m_nHolCardNum; $k++)
			{
				$temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];	//从牌缓冲区里那张牌
				$this->_list_insert($i, $temp_card);
				//确定第一个出牌的玩家
				if($this->m_nSetCount == 1 && $temp_card == 12 )
				{
					$this->m_nChairBanker = $i;
				}
				$tmp_card_arr[$i] .= "|".$temp_card;
			}
		}
		//地主位置
		$this->_set_record_game(ConstConfig::RECORD_P_RF_BANKER,$this->m_nChairBanker );
		$this->_set_record_game(ConstConfig::RECORD_P_DEAL, $tmp_card_arr[0], $tmp_card_arr[1], $tmp_card_arr[2]);
	}

	public function game_to_playing()
	{
		//状态设定
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_RUNFAST_PLAY_CARD ;
		$this->m_chairCurrentPlayer = $this->m_nChairBanker;
		//$this->m_wTotalScore[$this->m_nChairBanker]->n_angang += 1;//第一出牌人次数

		//$this->_get_score_times();
		//状态变化发消息
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			if($i != $this->m_nChairBanker)
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_RUNFAST_STATUS_WAITING;
			}
			else
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_RUNFAST_STATUS_THINK_OUTCARD;
				$this->m_bChooseBuf[$i] = 1;
			}
		}
		
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		//动作的发送动作的数据
        /*for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $send_data = array('Act'=>ConstConfig::RECORD_P_DEAL,'Card'=>$this->m_sPlayer[$i]->card);
            $this->_send_cmd('s_act', $send_data, Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
        }
        for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $this->_send_cmd('s_act', array('Act'=>ConstConfig::RECORD_P_DEAL,'ChairBanker'=>$this->m_nChairBanker), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
        }*/
		$this->handle_flee_play(true);	//更新断线用户
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

    private function _send_act_test($data)
    {
        $this->_send_cmd('s_act', $data, Game_cmd::SCO_ALL_PLAYER);
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
		for($i=1; $i<=13; $i++)
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
			return ConstConfig::PAI_TYPE_RUNFAST_INVALID;
		}
		else if($buffer_sum <= 5)
		{//1~5
			if($one == 1 && !$pair && !$triple && !$quartet)
				return ConstConfig::PAI_TYPE_RUNFAST_ONE;//单
			if($pair == 1 && !$one && !$triple && !$quartet)
				return ConstConfig::PAI_TYPE_RUNFAST_PAIR;//对
			if($triple == 1 && !$one && !$pair && !$quartet)
				return ConstConfig::PAI_TYPE_RUNFAST_TRIPLE;//三张
			if(!$one && $pair == 1 && $triple == 1 && !$quartet)
				return ConstConfig::PAI_TYPE_RUNFAST_TRIPLE_PAIR;//三带二
			if($one == 2 && !$pair && $triple == 1 && !$quartet)
				return ConstConfig::PAI_TYPE_RUNFAST_TRIPLE_PAIR;//三带二单
			if($one == 1 && !$pair && !$triple && $quartet == 1)
				return ConstConfig::PAI_TYPE_RUNFAST_TRIPLE_PAIR;//三带二单
			if(!$one && !$pair && !$triple && $quartet == 1)
				return ConstConfig::PAI_TYPE_RUNFAST_BOMB;  //炸弹
			if(!$pair && !$triple && !$quartet && !$buffer_arr[13][0] && $buffer_sum == 5)
			{//顺子
      			if( $this->_is_continued(1, $one, $buffer_arr))
      				return ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT;//单顺
				else
					return ConstConfig::PAI_TYPE_RUNFAST_INVALID;
			}
			if(!$one && $pair && !$triple && !$quartet && !$buffer_arr[13][0] && $buffer_sum == 4)
			{//连对
      			if( $this->_is_continued(2, $pair, $buffer_arr))
      				return ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT_PAIR;
				else
					return ConstConfig::PAI_TYPE_RUNFAST_INVALID;
			}
    	}
		else if($buffer_sum >= 6)
		{
			//单顺
			if(!$pair && !$triple && !$quartet && !$buffer_arr[13][0])
			{
				if($this->_is_continued(1, $one, $buffer_arr))
					return ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT;
				else
					return ConstConfig::PAI_TYPE_RUNFAST_INVALID;
			}
			//连对
			if(!$one && !$triple && !$quartet )
			{
				if($this->_is_continued(2, $pair, $buffer_arr))
					return ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT_PAIR;
				else
					return ConstConfig::PAI_TYPE_RUNFAST_INVALID;
			}
			//飞机带翅膀
			if( ($triple+$quartet)>=2 )
            {
				//处理 特殊牌型   333444555666  8888 //  333444555666 8889 // 3334444555666 777
				$total = $this->_continued_max(3, 5, $buffer_arr);
				if($total == 2 && $buffer_sum <=$total*2+$total*3)
					return ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT_TRIPLE;
				if($total == 3 && $buffer_sum <=$total*2+$total*3)
					return ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT_TRIPLE;
				if($total == 4)
					return ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT_TRIPLE;
				if($total == 5)
					return ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT_TRIPLE;
				else
					return ConstConfig::PAI_TYPE_RUNFAST_INVALID;
            }

		    return ConstConfig::PAI_TYPE_RUNFAST_INVALID;
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
				$count_tmp[] = $count;
                if($count == $max)
                {
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
			case ConstConfig::PAI_TYPE_RUNFAST_ONE:  //单
				return $this->_max_index(1, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_RUNFAST_PAIR:   //对子
				return $this->_max_index(2, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT:  //单顺
				return $this->_max_index(1, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_RUNFAST_TRIPLE:  //三张
				return $this->_max_index(3, $buffer_arr);
				break;
			
			case ConstConfig::PAI_TYPE_RUNFAST_TRIPLE_PAIR:  //三带一对
				return $this->_max_index(3, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_RUNFAST_BOMB:   //炸弹
				return $this->_max_index(4, $buffer_arr);
				break;

			case ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT_PAIR:  //连对
				return $this->_max_index(2, $buffer_arr);
				break;
			case ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT_TRIPLE:   //飞机
				return $this->_max_index(3, $buffer_arr,true);
				break;

			default:
				return 0;
				break;
		}
	}

	//最大下标
	private function _max_index($count, $buffer_arr, $continued = false, $type = false)
	{
		for ($i=13; $i>=1; $i--)
		{
			 for ($i=13; $i>=1; $i--)
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
		if($card_type == ConstConfig::PAI_TYPE_RUNFAST_INVALID)
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
		if($card_type == ConstConfig::PAI_TYPE_RUNFAST_INVALID)
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
		return ConstConfig::PAI_TYPE_RUNFAST_INVALID;
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
            $this->m_sysPhase = ConstConfig::SYSTEMPHASE_RUNFAST_SET_OVER;
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
		if($act == ConstConfig::RECORD_P_RF_BANKER)			
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
	
	// //判断玩家手牌是否能管上
	private function _judge_big($chair,$buffer_arr,$one=0,$pair=0,$triple=0,$quartet=0)
	{
		//玩家手牌有4个K
		if($buffer_arr[11][0] >=4)
		{
			echo "管上  4K  ok";
			return true;
		}

	    if($quartet > 0)  //炸弹
		{
			if($this->m_HistoryOuted->pai_type == ConstConfig::PAI_TYPE_RUNFAST_BOMB)
			{
				for($i=$this->m_HistoryOuted->level+1; $i<=11; $i++)
				{
					if($buffer_arr[$i][0] == 4)
					{
						echo "管上  炸弹 OK";
						return true;
					}
				}
				return false;
			}
			else
			{
				echo "管上  炸弹 OK";
				return true;
			}
		}

		//////////////////以下不用考虑4张 炸弹的情况///////////////////////
		switch ($this->m_HistoryOuted->pai_type)
		{
		case ConstConfig::PAI_TYPE_RUNFAST_ONE:  //单张
			for($i=$this->m_HistoryOuted->level+1; $i<=13; $i++)
			{
				if($buffer_arr[$i][0] > 0)
				{
					echo "管上  单张 OK";
					return true;
				}
			}
			break;
		case ConstConfig::PAI_TYPE_RUNFAST_PAIR:  //对子
			if( $this->m_sPlayer[$chair]->len >= 2 && ($pair > 0 || $triple > 0) )
			{
				for($i=$this->m_HistoryOuted->level+1; $i<=13; $i++)
				{
					if($buffer_arr[$i][0] >= 2)
					{
						echo "管上 对子 OK";
						return true;
					}
				}
			}
			break;
		case ConstConfig::PAI_TYPE_RUNFAST_TRIPLE: //三张			
			if($this->m_sPlayer[$chair]->len >= 3 && $triple > 0)
			{
				for($i=$this->m_HistoryOuted->level+1; $i<=13; $i++)
				{
					if($buffer_arr[$i][0] >= 3)
					{
						echo "管上 三张 OK";
						return true;
					}
				}
			}
			break;
		case ConstConfig::PAI_TYPE_RUNFAST_TRIPLE_PAIR: //三张带2
			if($this->m_sPlayer[$chair]->len >= 5 &&  ($triple > 0 || $triple > 0 ))  //如果有炸弹 这条不会进来
			{
				for($i=$this->m_HistoryOuted->level+1; $i<=13; $i++)
				{
					if($buffer_arr[$i][0] >= 3)
					{
						echo "管上 三张带一对 OK";
						return true;
					}
				}
			}
			break;
		case ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT: //单连
			if(($this->m_sPlayer[$chair]->len - $buffer_arr[13][0]) >= $this->m_HistoryOuted->len)
			{
				$level_tmp = $this->m_HistoryOuted->level+1;
				for($i=$level_tmp; $i<=12; $i++)
				{
					$is_continued = $this->_is_continued_min(1, $this->m_HistoryOuted->len, $level_tmp, $buffer_arr);
					if($is_continued)
					{
						echo "管上 单连 OK";
						return true;
					}
					$level_tmp++;
				}
			}
			break;
		case ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT_PAIR: //双连
			if(($this->m_sPlayer[$chair]->len - $buffer_arr[13][0]) >= $this->m_HistoryOuted->len)
			{
				$level_tmp = $this->m_HistoryOuted->level+1;
				for($i=$this->m_HistoryOuted->level+1; $i<=12; $i++)
				{
					$is_continued = $this->_is_continued_min(2, $this->m_HistoryOuted->len/2, $level_tmp , $buffer_arr);
					if($is_continued)
					{
						echo "管上 双连 OK";
						return true;
					}
					$level_tmp++;
				}
			}
			break;
		case ConstConfig::PAI_TYPE_RUNFAST_STRAIGHT_TRIPLE: //飞机带对  //如果有炸弹 这里就不用走了
			if(($this->m_sPlayer[$chair]->len ) >= $this->m_HistoryOuted->len)
			{
				$level_tmp = $this->m_HistoryOuted->level+1;
				for($i=$level_tmp; $i<=12; $i++)
				{
					$is_continued = $this->_is_continued_min(3, $this->m_HistoryOuted->len/5, $level_tmp, $buffer_arr);

					if($is_continued )
					{
						
						echo "管上 飞机带对 OK";
						return true;
					}
					$level_tmp++;
				}
			}
			break;
		default:
			;
		}
		return false;
	}

	//给定最大的$level 和长度 $total, 反向判断是否连续
	private function _is_continued_min($num, $total, $level, $buffer_arr)
	{

		$count = 0;
		$flag = 0;//有值则标记为1
		$sig = 0;//从 有到无 改标记为1
		for($i = $level; $i > ($level-$total); $i--)
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
}
