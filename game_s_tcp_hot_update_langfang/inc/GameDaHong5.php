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

class GameDaHong5
{
	const GAME_TYPE = 341;

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
	
	public $m_nCountAllot;								// 发到第几张牌
    public $m_nTableCards = array();        		   	// 玩家的桌面牌
    public $m_nNumTableCards = array();        			// 玩家桌面牌数量
	public $m_sysPhase;				                   	// 当前阶段状态
	public $m_chairCurrentPlayer;			           	// 当前出牌者
	public $m_nAllCardNum = ConstConfig::BASE_DAHONG5_CARD_NUM;	//牌总数	
	//public $m_bChooseBuf = array();			           	// 玩家的选择胡,吃,碰,杠命令 1 等待操作 0 无操作



	public $m_outed;									// 当前出牌
	public $m_play_outed = array();						// [$i][0]用户上次出的牌； [$i][1]用户上次状态： 0无操作 1过
	public $m_HistoryOuted = array(); 					// 每个玩家历史出牌记录
	public $m_run_order = array();						// 打完牌的顺序，[0]为最先跑玩家座位号
	
	public $m_base_score;								// 底分
	public $m_card_fan;									// 牌局番数(服务器用)	
	public $m_qipai;									// 弃牌的人(服务器用)	
	public $m_double5;									// 双红5的座位号
	public $m_only_qipai;								// 只能弃牌的标识位：0 无限制 1 只能选择弃牌和过
	
	public $m_liang_jiu_du = array(0,0);				// [0]牌局亮/揪/独状态: 0 初始 1 天宣 2 天揪 3 独 4 亮 5 揪; [1]执行亮/揪/独的玩家(服务器用)	
	public $m_chuai = array(0,0,0,0);					// 踹牌标识 0 没有踹过 1 已经踹过(服务器用)	
	public $m_team = array(0,0,0,0);					// 玩家阵营 0 普通阵营 1 红5阵营 2 独阵营
	public $m_bids = array(0,0,0,0);					// 玩家叫牌状态：0 初始值 1天宣 2天揪 3独 4亮 5揪 6踹 7弃 255过(客户端用)
	public $m_score_times = array();					// 每个玩家番数(客户端用)

	public $player_score = array(0,0,0,0);              //结算时积分
	public $player_cup = array(0,0,0,0);                //结算时奖杯
	public $agent_uid;                                  // 公会房代理的玩家id
    public $m_client_ip = array();                      // 用户ip

	///////////////////////方法/////////////////////////
	//构造方法
	public function __construct($serv)
	{
		$this->serv = $serv;
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_DAHONG5_SET_OVER ;
		$this->m_room_state = ConstConfig::ROOM_STATE_NULL ;
		$this->m_game_type = self::GAME_TYPE;
	}

	public function clear()
	{
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
			$this->m_game_type = self::GAME_TYPE;					//游戏类型，见http端协议
			$this->m_room_state = ConstConfig::ROOM_STATE_OVER ;	//房间状态
			$this->m_room_id = 0;									//房间号
			$this->m_room_owner = 0;								//房主
			$this->m_room_players = array();						//玩家信息
			$this->m_start_time = 0;								//开始时间
			$this->m_nSetCount = 0;									//当前局数
			$this->_on_table_status_to_playing();					//定庄家
			$this->m_sPlayer = array();
			$this->m_ready = array(0,0,0,0);
			for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
			{
				$this->m_wTotalScore[$i] = new TotalScore();
			}
		}
		$this->m_outed = new Outed_card_dahong5();
		
		if($this->m_nChairBankerNext != 255)
		{
			$this->m_nChairBanker = $this->m_nChairBankerNext;
		}
		$this->m_nChairBankerNext = 255;

		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_DAHONG5_SET_OVER ;
		$this->m_chairCurrentPlayer = 255;
		$this->m_end_time = '';
		$this->m_nCountAllot = 0;

		$this->m_cancle_first = 255;
		$this->m_cancle_time = 0;
		$this->m_record_game = array();

		$this->m_liang_jiu_du = array(0,0);
		$this->m_qipai = 255;
		$this->m_run_order = array(255);
		$this->m_double5 = 255;
		$this->m_card_fan = 1;
		$this->m_only_qipai = 0;
		$this->m_base_score = 1;
		
		$this->player_score = array(0,0,0,0);                                
		$this->player_cup = array(0,0,0,0);

		for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
		{
			$this->m_wHuScore[$i] = 0;
			$this->m_wSetScore[$i] = 0;
			$this->m_wSetLoseScore[$i] = 0;
			$this->m_Score[$i] = new Score();
			//$this->m_bChooseBuf[$i] = 0;

			$this->m_cancle[$i] = 0;
			$this->m_sPlayer[$i] = new Play_data_dahong5();
			$this->m_nTableCards[$i] = array();
			$this->m_nNumTableCards[$i] = 0;

			$this->m_score_times[$i] = 1;
			$this->m_team[$i] = 0;
			$this->m_chuai[$i] = 0;
			$this->m_bids[$i] = 0;
			$this->m_HistoryOuted[$i] = array();
			//$this->m_hu_desc[$i] = '';
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

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_SET_OVER || $this->m_room_state == ConstConfig::ROOM_STATE_GAMEING )
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}
			elseif ($this->m_room_state == ConstConfig::ROOM_STATE_OPEN  && $this->m_room_owner != $params['uid'])
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$this->m_rule = new RuleDaHong5();

			$params['rule']['player_count'] = 4;
			$params['rule']['top_fan'] = !isset($params['rule']['top_fan']) ? 255 : $params['rule']['top_fan'];
			$params['rule']['min_fan'] = !isset($params['rule']['min_fan']) ? 0 : $params['rule']['min_fan'];
			$params['rule']['pay_type'] = !isset($params['rule']['pay_type']) ? 0 : $params['rule']['pay_type'];
			$params['rule']['cancle_clocker'] = !isset($params['rule']['cancle_clocker']) ? 1 : $params['rule']['cancle_clocker'];

			$this->m_rule->game_type = $params['rule']['game_type'];
			$this->m_rule->player_count = $params['rule']['player_count'];
			$this->m_rule->set_num = $params['rule']['set_num'];
			$this->m_rule->top_fan = $params['rule']['top_fan'];
			$this->m_rule->min_fan = $params['rule']['min_fan'];
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

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
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

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
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
			if($this->m_nSetCount != 0 || $this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_SET_OVER)
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

    // 亮/揪/天宣/天揪
    public function c_liang_jiu($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do{
            if (empty($params['rid'])
                || empty($params['uid'])
                || empty($params['type'])
                || !in_array($params['type'], array(1, 2, 4, 5))  // 1 天宣 2 天揪 4 亮 5 揪
            )
            {
                $return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            if(($this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST && $this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_SECOND)
                || $this->m_room_state != ConstConfig::ROOM_STATE_GAMEING
            )
            {
                $return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            $is_act = false;
            foreach($this->m_room_players as $key => $room_user)
            {
                if ($room_user["uid"] == $params["uid"])
                {
					if (!empty($this->m_liang_jiu_du[0]))
					{
						$return_send['code'] = 4;
                        $return_send['text'] = '重复操作';
                        $return_send['desc'] = __LINE__ . __CLASS__;
                        break 2;
					}
					
					if ($params['type'] == 2)
					{
						if (!in_array('117',$this->m_sPlayer[$key]->card) && !in_array('125',$this->m_sPlayer[$key]->card))
						{
	                        $return_send['code'] = 4;
	                        $return_send['text'] = '天揪错误';
	                        $return_send['desc'] = __LINE__ . __CLASS__;
	                        break 2;
						}
					}
					
					if ($params['type'] == 1 || $params['type'] == 4)
					{
						if (!in_array('89',$this->m_sPlayer[$key]->card) && !in_array('91',$this->m_sPlayer[$key]->card))
						{
	                        $return_send['code'] = 4;
	                        $return_send['text'] = '宣亮错误';
	                        $return_send['desc'] = __LINE__ . __CLASS__;
	                        break 2;
						}
					}
				
                    $this->handle_liang_jiu($key, $params['type']);
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

    //处理亮/揪
    public function handle_liang_jiu($chair, $type)
    {
    	//操作类型 和 哪位玩家
        $this->m_liang_jiu_du[0] = $type;
        $this->m_liang_jiu_du[1] = $chair;
        $this->m_bids[$chair] = $type;

        //设置倍数
        if($this->m_liang_jiu_du[0] == 1 || $this->m_liang_jiu_du[0] == 2)
        {
			$this->m_card_fan *= 4;
        }
        elseif($this->m_liang_jiu_du[0] == 4|| $this->m_liang_jiu_du[0] == 5)
        {
			$this->m_card_fan *= 2;
        }

        for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
        {
            $this->m_score_times[$i] = $this->m_card_fan;
        }

        //划分同伙
        for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
        {
            if (in_array('89',$this->m_sPlayer[$i]->card) || in_array('91',$this->m_sPlayer[$i]->card))
            {
                //有红5的阵营
                $this->m_team[$i] = 1;
            }
        }

		$team_str = implode("|",$this->m_team);
		$this->_set_record_game(ConstConfig::RECORD_P_R5_R5, $team_str);
        
        //更改玩家状态
        $this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_DAHONG5_STATUS_WAITING;

        for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

        $next_key = $this->_anti_clock($chair);
		if ($next_key != $this->m_nChairBanker)
		{
			for ($i = 0; $i < $this->m_rule->player_count; ++$i)
			{
				if (($this->m_liang_jiu_du[0] == 2 && $this->m_team[$this->m_liang_jiu_du[1]] == 0) || $this->m_team[$next_key] != $this->m_team[$chair])
				{					
					$this->m_sPlayer[$next_key]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
					for ($i=0; $i < $this->m_rule->player_count ; $i++)
					{
						$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
					}
					return true;
				}
				else
				{
					$this->m_bids[$next_key] = 255;
					$next_key = $this->_anti_clock($next_key);
					//叫牌一圈，开始打牌
					if ($next_key == $this->m_nChairBanker)
					{
						$bids_str = implode('|',$this->m_bids);
						$this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);
						break;
					}
				}
			}
		}
		else
		{
			$bids_str = implode('|',$this->m_bids);
			$this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);
		}

		//下一个阶段
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST)
		{
			$this->DealSecond();
			for ($j = 0; $j < $this->m_rule->player_count; ++$j)
			{
				if (in_array('89',$this->m_sPlayer[$j]->card) && in_array('91',$this->m_sPlayer[$j]->card))
	            {
	            	$this->m_double5 = $j;
	            }
			}
		}

        //第二叫牌阶段有双红5可以再次选择是否弃牌
        if ($this->m_double5 != 255)
        {
            $this->m_sPlayer[$this->m_double5]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
            $this->m_only_qipai = 1;
            for ($i=0; $i < $this->m_rule->player_count ; $i++)
            {
                $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
            }
			return true;
        }
        
        $this->game_to_playing();
	
        return true;
    }

	//踹
	public function c_chuai($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if(($this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST && $this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_DU && $this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_SECOND)
			 || $this->m_room_state != ConstConfig::ROOM_STATE_GAMEING
			 )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if (empty($this->m_liang_jiu_du[0])
					|| array_keys($this->m_chuai, 1)
					|| ($this->m_liang_jiu_du[0] == 1 && $this->m_team[$key] == 1)
					|| ($this->m_liang_jiu_du[0] == 4 && $this->m_team[$key] == 1)
					|| ($this->m_liang_jiu_du[0] == 5 && $this->m_team[$key] == 0)
					|| ($this->m_liang_jiu_du[0] == 2 && $this->m_team[$this->m_liang_jiu_du[1]] == 1 && $this->m_team[$key] == 1)
					)
					{
						$return_send['code'] = 4; $return_send['text'] = '踹错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
					}

					$this->handle_chuai($key);
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

	//处理踹
	public function handle_chuai($chair)
	{
		$this->m_chuai[$chair] = 1;
		$this->m_bids[$chair] = 6;
		$this->m_card_fan *=2;
		$this->m_wTotalScore[$chair]->n_angang += 1;

		// $bids_str = implode('|',$this->m_bids);
		// $this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);

		
		for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
        {
            $this->m_score_times[$i] = $this->m_card_fan;
        }
		
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_DAHONG5_STATUS_WAITING;

		for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
        }

		//把踹后面玩家的叫牌状态置成“过”
		$next_key = $chair;
		for ($i = 0; $i < $this->m_rule->player_count; ++$i)
		{
			$next_key = $this->_anti_clock($next_key);
			if ($next_key != $this->m_nChairBanker)
            {
            	$this->m_bids[$next_key] = 255;
            }
            else
            {
            	break;
            }
		}

		$bids_str = implode('|',$this->m_bids);
	    $this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);

		// 只有第一次叫牌阶段和第二次叫牌阶段有双红5的人才能弃牌
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST || $this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_SECOND)
		{
			if($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST)
			{
				$this->DealSecond();
				for ($i = 0; $i < $this->m_rule->player_count; ++$i)
				{
					if (in_array('89',$this->m_sPlayer[$i]->card) && in_array('91',$this->m_sPlayer[$i]->card))
		            {
		            	$this->m_double5 = $i;
		            }
				}

			}

	        if ($this->m_double5 != 255)
	        {
	            $this->m_sPlayer[$this->m_double5]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
	            $this->m_only_qipai = 1;
	            for ($i=0; $i < $this->m_rule->player_count ; $i++)
	            {
	                $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
	            }
	            return true;
	        }
		}
		
        $this->game_to_playing();

		return true;
	}

	//独
	public function c_du($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if( $this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_DU
			|| $this->m_room_state != ConstConfig::ROOM_STATE_GAMEING
			 )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if(!empty($this->m_liang_jiu_du[0]))
					{
						$return_send['code'] = 4; $return_send['text'] = '独错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
					}

					$this->handle_du($key);
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

	//处理独
	public function handle_du($chair)
	{
		$this->m_liang_jiu_du[0] = 3;
		$this->m_liang_jiu_du[1] = $chair;
		$this->m_bids[$chair] = 3;
		
		// $bids_str = implode('|',$this->m_bids);
		// $this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);

		$this->m_card_fan *= 10;
		for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
        {
            $this->m_score_times[$i] = $this->m_card_fan;
        }
		
		$this->m_team[$chair] = 2; //team 置成独
		$this->m_wTotalScore[$chair]->n_zimo += 1;
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_DAHONG5_STATUS_WAITING;

		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		$next_key = $this->_anti_clock($chair);
		if($next_key != $this->m_nChairBanker)
		{
			$this->m_sPlayer[$next_key]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
			for ($i=0; $i < $this->m_rule->player_count ; $i++)
			{
				$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			}
			return true;
		}
		else
		{
			$bids_str = implode('|',$this->m_bids);
			$this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);
		}

		$this->game_to_playing();

		return true;
	}

	//过
	public function c_cancle_bids($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if(($this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST
				&& $this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_SECOND
				&& $this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_DU)
			|| $this->m_room_state != ConstConfig::ROOM_STATE_GAMEING
			 )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					$this->handle_cancle_bids($key);
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

	//处理过
	public function handle_cancle_bids($chair)
	{
		//双红5的人最后一次弃牌时选择过，则直接开始游戏
        if($this->m_only_qipai == 1)
        {
            $this->m_only_qipai = 0;
            $this->game_to_playing();
            return true;
        }

        $this->m_bids[$chair] = 255;

        // $bids_str = implode('|',$this->m_bids);
        // $this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);
        
        $this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_DAHONG5_STATUS_WAITING;
		
        for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		$next_key = $this->_anti_clock($chair);
		if ($next_key != $this->m_nChairBanker)
		{
			if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST && empty($this->m_liang_jiu_du[0]))
			{
				for ($i = 0; $i < $this->m_rule->player_count; ++$i)
				{
					if (in_array('89',$this->m_sPlayer[$next_key]->card)
				    || in_array('91',$this->m_sPlayer[$next_key]->card)
				    || in_array('117',$this->m_sPlayer[$next_key]->card)
				    || in_array('125',$this->m_sPlayer[$next_key]->card)
					)
					{
						$this->m_sPlayer[$next_key]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
						for ($i=0; $i < $this->m_rule->player_count ; $i++)
						{
							$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
						}
						return true;
					}
					else
					{
						$this->m_bids[$next_key] = 255;
						
						$next_key = $this->_anti_clock($next_key);
						if ($next_key == $this->m_nChairBanker)
						{
							$bids_str = implode('|',$this->m_bids);
        					$this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);
							
							for ($i=0; $i < $this->m_rule->player_count ; $i++)
							{
								$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
							}
							break;
						}
					}
				}
			}
			elseif($this->m_liang_jiu_du[0] == 1 || $this->m_liang_jiu_du[0] == 4 || $this->m_liang_jiu_du[0] == 5 
				|| ($this->m_liang_jiu_du[0] == 2 && $this->m_team[$this->m_liang_jiu_du[1]] == 1))
			{
                for ($i = 0; $i < $this->m_rule->player_count; ++$i)
                {
                    if($this->m_team[$next_key] != $this->m_team[$this->m_liang_jiu_du[1]])
                    {
                        $this->m_sPlayer[$next_key]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
                        for ($i=0; $i < $this->m_rule->player_count ; $i++)
						{
							$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
						}
						return true;
                    }
                    else
                    {
                    	$this->m_bids[$next_key] = 255;

            			// $bids_str = implode('|',$this->m_bids);
        				// $this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);

                    	$next_key = $this->_anti_clock($next_key);
                        if ($next_key == $this->m_nChairBanker)
                        {
                        	$bids_str = implode('|',$this->m_bids);
        					$this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);

                        	for ($i=0; $i < $this->m_rule->player_count ; $i++)
							{
								$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
							}
                        	break;
                        }
                    }
                }
			}
			else
            {
                $this->m_sPlayer[$next_key]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
                for ($i=0; $i < $this->m_rule->player_count ; $i++)
				{
					$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
				}
				return true;
            }	
		}
		else
		{
			$bids_str = implode('|',$this->m_bids);
			$this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);
		}

		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST)
		{
			if (empty($this->m_liang_jiu_du[0]))
			{
				for ($k = 0; $k < $this->m_rule->player_count; ++$k)
				{
					$this->m_bids[$k] = 0;
				}
				
			}
			
			$this->DealSecond();
			for ($j = 0; $j < $this->m_rule->player_count; ++$j)
			{
				if (in_array('89',$this->m_sPlayer[$j]->card) && in_array('91',$this->m_sPlayer[$j]->card))
	            {
	            	$this->m_double5 = $j;
	            }
			}
			//第一阶段弃牌条件
			if ($this->m_double5 != 255 && !empty($this->m_liang_jiu_du[0]) 
				//&& $this->m_chuai[$this->m_double5] != 1
			)
			{
				$this->m_sPlayer[$this->m_double5]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
	            $this->m_only_qipai = 1;
	            for ($i=0; $i < $this->m_rule->player_count ; $i++)
	            {
	                $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
	            }
	            return true;
			}
		}

		//第三阶段弃牌条件
        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_SECOND 
        	&& $this->m_double5 != 255
        )
        {
            $this->m_sPlayer[$this->m_double5]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
            $this->m_only_qipai = 1;

        	for ($i=0; $i < $this->m_rule->player_count ; $i++)
            {
                $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
            }
            return true;
        }

		if (empty($this->m_liang_jiu_du[0]))
		{
			if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST)
			{
				$this->m_sysPhase = ConstConfig::SYSTEMPHASE_DAHONG5_DU;
				$this->m_sPlayer[$this->m_nChairBanker]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
				for ($i=0; $i < $this->m_rule->player_count ; $i++)
				{
					$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
				}
			}
			elseif ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DU)
			{
				for ($k = 0; $k < $this->m_rule->player_count; ++$k)
				{
					$this->m_bids[$k] = 0;
				}
				// $bids_str = implode('|',$this->m_bids);
				// $this->_set_record_game(ConstConfig::RECORD_P_R5_BID, $this->m_nChairBanker, $bids_str);
				$this->m_sysPhase = ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_SECOND;
				$this->m_sPlayer[$this->m_nChairBanker]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
				for ($i=0; $i < $this->m_rule->player_count ; $i++)
				{
					$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
				}
			}
			elseif ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_SECOND)
			{
				$this->game_to_playing();
			}
		}
		else
		{
			$this->game_to_playing();
		}

		return true;
	}

	//弃牌
    public function c_give_up($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do {
            if( empty($params['rid'])
                || empty($params['uid'])
            )
            {
                $return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            if (($this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST && $this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_SECOND)
                || $this->m_room_state != ConstConfig::ROOM_STATE_GAMEING
            )
            {
                $return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {
                    if( $key != $this->m_double5
                        //|| $this->m_chuai[$key] == 1
                        //|| ($this->m_liang_jiu_du[0] == 4  && $this->m_liang_jiu_du[1] == $key)
                    )
                    {
                        $return_send['code'] = 4; $return_send['text'] = '弃牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
                    }
                    $this->handel_give_up($key);
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

    //处理弃牌
    public function handel_give_up($chair)
    {
        //将弃牌的人记录下来
        $this->m_bids[$chair] = 7;
        $this->m_qipai = $chair;
        $this->_set_record_game(ConstConfig::RECORD_P_R5_GIVEUP, $chair);


        //弃牌的次数
        $this->m_wTotalScore[$chair]->n_zhigang_wangang += 1;

        //牌局结束
        $this->HandleSetOver();
        return true;
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

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_PLAY_CARD)
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

	public function handle_out_card($chair, $out_card_arr)
	{
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
		//没有明确阵营，当出牌有红5时，标明阵营
		if (empty($this->m_liang_jiu_du[0]) && (in_array(89, $out_card_arr) || in_array(91, $out_card_arr)))
		{
			$this->m_team[$chair] = 1;
			$team_str = implode("|",$this->m_team);
            $this->_set_record_game(ConstConfig::RECORD_P_R5_R5, $team_str);
		}

   		//整理用户出的牌并校验信息
		$tmp_arr = ConstConfig::CARD_TEMP_DAHONG5;
		foreach($out_card_arr as $v)
		{
			$this->_list_insert_sub($tmp_arr, $v);
		}
		$tmp_out = new Outed_card_dahong5();
		$tmp_out->chair = $chair;
		$tmp_out->card = $out_card_arr;
		$tmp_out->pai_type = $this->_get_pai_type($tmp_arr, count($out_card_arr));
		$tmp_out->level = floor($out_card_arr[0] / 8);
		$tmp_out->len = count($out_card_arr);

		$first_out = 0;  // 首次出牌标志
		if(empty($this->m_outed->card) || $this->m_outed->chair == $chair)
		{
			//首出牌
			$first_out = 1;
		}
		else
		{
            if($tmp_out->pai_type != $this->m_outed->pai_type
			 || $tmp_out->len != $this->m_outed->len
			 || $tmp_out->level <= $this->m_outed->level)
			{
				return false;
			}
		}

		// 出牌录像
		$tmp_card_str = implode("|",$out_card_arr);
		$this->_set_record_game(ConstConfig::RECORD_P_DISCARD, $chair, $first_out, $tmp_card_str);
		
		$this->m_outed = $tmp_out;
		$this->m_play_outed[$chair] = [clone($tmp_out), 0];
		for ($i=0; $i < $this->m_rule->player_count; $i++)
		{
			$this->m_play_outed[$i][1] = 0;
		}

		$this->m_HistoryOuted[$chair][] = clone($tmp_out);
		
		foreach($out_card_arr as $v)
		{
			$this->_list_delete($chair, $v);
		}

		if($this->m_sPlayer[$chair]->len <= 0)
        {
            $this->m_run_order[] = $chair;
            $run_order_str = implode('|', array_slice($this->m_run_order, 1));
            $this->_set_record_game(ConstConfig::RECORD_P_R5_RUNORDER, $run_order_str);
        }
		
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_DAHONG5_STATUS_WAITING;
		$next = $chair;
		for ($i=0; $i < $this->m_rule->player_count; $i++)
		{
			$next = $this->_anti_clock($next);
	        if($this->m_sPlayer[$next]->len <= 0)
	        {
	        	continue;
	        }
	        $this->m_sPlayer[$next]->state = ConstConfig::PLAYER_DAHONG5_STATUS_THINK_OUTCARD;
	        $this->m_chairCurrentPlayer = $next;
	        break;
		}

		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		//没有牌啦
        if($this->m_sPlayer[$chair]->len <= 0)
        {
            if ($this->m_liang_jiu_du[0] == 3 || in_array('1',$this->m_chuai)   //有独或者踹
            	|| $chair == $this->m_double5   //跑的是双红5
            	|| ($this->m_double5 == 255 && isset($this->m_run_order[2]) && $this->m_run_order[2] == $chair && $this->m_team[$this->m_run_order[1]] == $this->m_team[$this->m_run_order[2]])   	//同一阵营的先跑
            	|| (isset($this->m_run_order[3]) && $this->m_run_order[3] == $chair)	//跑了三家
            )
            {
                //如果自己是思考打牌阶段，看不见自己出的牌，所以要置成等待
	            $this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_DAHONG5_STATUS_WAITING;

	            for ($i=0; $i < $this->m_rule->player_count ; $i++)
				{
					$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
				}
                $this->HandleSetOver();
            }
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

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_DAHONG5_PLAY_CARD)
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

                    $this->m_sPlayer[$key]->state = ConstConfig::PLAYER_DAHONG5_STATUS_THINK_OUTCARD ;
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

	public function handle_cancle_choice($chair)
	{
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_DAHONG5_STATUS_WAITING;
		$this->m_play_outed[$chair] = [(object)null, 1];
		$this->_set_record_game(ConstConfig::RECORD_P_DISCARD, $chair, 0, 255);

        //当前出牌者
        $next_key = $chair;
        $clear_out = false;
        for ($i = 0; $i < $this->m_rule->player_count; ++$i)
        {
            $next_key = $this->_anti_clock($next_key);
            if($this->m_sPlayer[$next_key]->len == 0)
            {
                if($next_key == $this->m_outed->chair)
                {
                    $clear_out = true;
                }
                continue;
            }
			
			$this->m_sPlayer[$next_key]->state = ConstConfig::PLAYER_DAHONG5_STATUS_THINK_OUTCARD;
            $this->m_chairCurrentPlayer = $next_key;

			for ($i=0; $i < $this->m_rule->player_count ; $i++)
			{
				$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, false), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			}
            
            
			if($this->m_outed->chair == $next_key || $clear_out == true)
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

            break;
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
			$data['m_nChairBanker'] = $this->m_nChairBanker;  //庄家
			$data['m_nSetCount'] = $this->m_nSetCount;
		}

		$data['m_sysPhase'] = $this->m_sysPhase;			// 当前的阶段
		$data['m_nCountAllot'] = $this->m_nCountAllot;		// 发到第几张
		$data['m_nAllCardNum'] = $this->m_nAllCardNum;		// 牌总数
		// $data['m_liang_jiu_du'] = $this->m_liang_jiu_du;    // 亮揪独
		// $data['m_chuai'] = $this->m_chuai;                  // 踹
		$data['m_bids'] = $this->m_bids;					// 叫牌状态
        $data['m_team'] = $this->m_team;					// 阵营
        $data['m_score_times'] = $this->m_score_times;		// 番数
        $data['m_base_score'] = $this->m_base_score;		// 底分
        $data['m_run_order'] = $this->m_run_order;          // 跑的顺序
        $data['m_double5'] = $this->m_double5;              // 双红五座位号
        $data['m_only_qipai'] = $this->m_only_qipai;		// 是否只能选择弃牌
        if(!empty($this->m_cancle_time))
		{
			$data['m_cancle_time'] = $this->m_cancle_time + Config::CANCLE_GAME_CLOCKER_NUM - time();
		}

		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
            if ($i == $chair)
            {
                $data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
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

		//第一次叫牌阶段
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST)
		{
			//$data['m_liang_jiu_du'] = $this->m_liang_jiu_du;    //亮揪独
			//$data['m_chuai'] = $this->m_chuai;                  //踹
			$data['m_bids'] = $this->m_bids;
	        $data['m_team'] = $this->m_team;                    // 阵营
	        $data['m_only_qipai'] = $this->m_only_qipai;		// 是否只能选择弃牌
			return $data;
		}

		//叫独阶段
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DU)
		{
			//$data['m_liang_jiu_du'] = $this->m_liang_jiu_du;    //亮揪独
			//$data['m_chuai'] = $this->m_chuai;                  //踹
			$data['m_bids'] = $this->m_bids;
	        $data['m_team'] = $this->m_team;                    // 阵营
	        $data['m_double5'] = $this->m_double5;				// 双红五座位号
			return $data;
		}
        
		//第二次叫牌阶段
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_SECOND)
		{
			//$data['m_liang_jiu_du'] = $this->m_liang_jiu_du;    //亮揪独
			//$data['m_chuai'] = $this->m_chuai;                  //踹
			$data['m_bids'] = $this->m_bids;
	        $data['m_team'] = $this->m_team;                    // 阵营
	        $data['m_double5'] = $this->m_double5;              // 双红五座位号
	        $data['m_only_qipai'] = $this->m_only_qipai;		// 是否只能选择弃牌
			return $data;
		}
        
        //出牌阶段
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_PLAY_CARD)
		{
            $data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer; 	// 当前出牌者
            $data['m_nNumTableCards'] = $this->m_nNumTableCards;        	// 玩家桌面牌数量
            $data['m_nTableCards'] = $this->m_nTableCards;    	// 玩家桌面牌
            $data['m_outed'] = $this->m_outed;                	// 刚出的牌
            $data['m_play_outed'] = $this->m_play_outed;
            $data['m_score_times'] = $this->m_score_times;		// 番数

			return $data;
		}

		//游戏结束阶段
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_SET_OVER)
		{
			$data['m_sPlayer'] = $this->m_sPlayer;			// 玩家数据
			$data['m_wTotalScore'] = $this->m_wTotalScore;
			$data['m_Score'] = $this->m_Score;				//分数

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

			//$data['m_hu_desc'] = $this->m_hu_desc;
			$data['m_end_time'] = $this->m_end_time;
			$data['player_score'] = $this->player_score;
			$data['player_cup'] = $this->player_cup;
			
			return $data;
		}
		return true;
	}

	//牌局结束处理
	public function HandleSetOver()
	{
		if($this->m_sysPhase == ConstConfig::SYSTEMPHASE_DAHONG5_SET_OVER)
		{
			return false;
		}
		// 补全阵营信息
		if (empty($this->m_liang_jiu_du[0]))
		{
			for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
	        {
	            if (in_array('89',$this->m_sPlayer[$i]->card) || in_array('91',$this->m_sPlayer[$i]->card))
	            {
	                $this->m_team[$i] = 1;
	            }
	        }
	        $team_str = implode("|",$this->m_team);
            $this->_set_record_game(ConstConfig::RECORD_P_R5_R5, $team_str);
		}
		
		$this->ScoreOneHuCal();

		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_DAHONG5_SET_OVER;

		$this->CalcHuScore(); //正常算分，此时无逃跑得失相等

		//下一局庄家
		if(255 == $this->m_nChairBankerNext)
		{
			if (isset($this->m_run_order[1]))
			{
				$this->m_nChairBankerNext = $this->m_run_order[1];
			}
			else
			{
				$this->m_nChairBankerNext = $this->m_nChairBanker;
			}
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

	//算分
	public function ScoreOneHuCal()
	{
		$this->m_wHuScore = [0,0,0,0];
		$tmp_score = $this->m_card_fan;
		
		//弃牌
		if ($this->m_qipai != 255)
		{
			$tmp_score = -$this->m_card_fan;
			for ($i = 0; $i < $this->m_rule->player_count; $i++)
			{
				if ($i == $this->m_qipai)
				{
					continue;
				}
				$this->m_wHuScore[$this->m_qipai] += $tmp_score;
				$this->m_wHuScore[$i] -= $tmp_score;
			}
		}
		//有独
		elseif ($this->m_liang_jiu_du[0] == 3)
		{
			if ($this->m_run_order[1] != $this->m_liang_jiu_du[1])
			{
				$tmp_score = -$this->m_card_fan;
			}

			for ($i = 0; $i < $this->m_rule->player_count; $i++)
			{
				if ($i == $this->m_liang_jiu_du[1])
				{
					continue;
				}
				$this->m_wHuScore[$this->m_liang_jiu_du[1]] += $tmp_score;
				$this->m_wHuScore[$i] -= $tmp_score;
			}
		}
		//有踹没独
		elseif (!empty(array_keys($this->m_chuai, 1))) 	
		{
			//玩家双红5
			if ($this->m_double5 != 255)
			{
				if ( $this->m_run_order[1] != $this->m_double5)
				{
					$tmp_score = -$this->m_card_fan;
				}
				for ($i = 0; $i < $this->m_rule->player_count; $i++)
				{
					if ($i == $this->m_double5)
					{
						continue;
					}
					$this->m_wHuScore[$this->m_double5] += $tmp_score * 3;
					$this->m_wHuScore[$i] -= $tmp_score * 3;
				}
			}
			//没有双红5
			else 	
			{
				for ($i = 0; $i < $this->m_rule->player_count; $i++)
				{
					if ($this->m_team[$i] == $this->m_team[$this->m_run_order[1]])
					{
						$this->m_wHuScore[$i] += $tmp_score * 2 * 2;
					}
					else
					{
						$this->m_wHuScore[$i] -= $tmp_score * 2 * 2;
					}
				}
			}
		}
		//没独没踹
		else 	
		{
			//双红5
			if ($this->m_double5 != 255)
			{
				if ($this->m_run_order[1] == $this->m_double5)
				{
					for ($i = 0; $i < $this->m_rule->player_count; $i++)
					{
						if ($i == $this->m_double5)
						{
							continue;
						}
						$this->m_wHuScore[$this->m_double5] += $tmp_score * 3;
						$this->m_wHuScore[$i] -= $tmp_score * 3;
					}
				}
				elseif ((isset($this->m_run_order[2]) && $this->m_run_order[2] == $this->m_double5) 
					|| (isset($this->m_run_order[3]) && $this->m_run_order[3] == $this->m_double5)
					)
				{
					//不用算分，平局
				}
				else
				{
					for ($i = 0; $i < $this->m_rule->player_count; $i++)
					{
						$tmp_score = -$this->m_card_fan;
						if ($i == $this->m_double5)
						{
							continue;
						}
						$this->m_wHuScore[$this->m_double5] += $tmp_score * 3;
						$this->m_wHuScore[$i] -= $tmp_score * 3;
					}
				}
			}
			//不是双红5
			else 	
			{
				//AABB型
				if ($this->m_team[$this->m_run_order[1]] == $this->m_team[$this->m_run_order[2]])
				{
					for ($i = 0; $i < $this->m_rule->player_count; $i++)
					{
						if ($this->m_team[$i] == $this->m_team[$this->m_run_order[1]])
						{
							$this->m_wHuScore[$i] += $tmp_score * 2 * 2;
						}
						else
						{
							$this->m_wHuScore[$i] -= $tmp_score * 2 * 2;
						}					
					}
				}
				//ABAB型
				elseif ($this->m_team[$this->m_run_order[1]] == $this->m_team[$this->m_run_order[3]])
				{
					for ($i = 0; $i < $this->m_rule->player_count; $i++)
					{
						if ($this->m_team[$i] == $this->m_team[$this->m_run_order[1]])
						{
							$this->m_wHuScore[$i] += $tmp_score * 2;
						}
						else
						{
							$this->m_wHuScore[$i] -= $tmp_score * 2;
						}					
					}
				}
				//ABBA型
				elseif ($this->m_team[$this->m_run_order[2]] == $this->m_team[$this->m_run_order[3]])
				{
					//不用算分，平局
				}
			}
		}

		for($i = 0; $i < $this->m_rule->player_count; $i++)
		{
			if($this->m_wHuScore[$i] > 0)
			{
				$this->m_wSetScore[$i] += $this->m_wHuScore[$i];
				$this->m_wTotalScore[$i]->n_jiepao += 1;
			}
			elseif ($this->m_wHuScore[$i] < 0)
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
			elseif ($this->m_Score[$i]->score < 0)
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
		$this->m_nCardBuf = ConstConfig::ALL_CARD_DAHONG5;
		$this->m_nAllCardNum = ConstConfig::BASE_DAHONG5_CARD_NUM;
		if(defined("gf\\conf\\Config::TEST_PAI") && Config::TEST_PAI)
		{
			$this->m_nCardBuf = ConstConfig::ALL_CARD_DAHONG5_TEST;
		}

		if(Config::WASHCARD)
		{
			shuffle($this->m_nCardBuf); shuffle($this->m_nCardBuf);
		}
	}

	//第一阶段发牌（三张）
	public function DealFirst()
	{
		$temp_card = 255;
		$this->WashCard();
		$tmp_card_arr = ['', '', '', ''];

		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$tmp_card_arr[$i] .= '3';
			for($k=0; $k<3; $k++)
			{
				$temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];	//从牌缓冲区里那张牌
				$this->_list_insert($i, $temp_card);
				$tmp_card_arr[$i] .= '|'.$temp_card;
			}
		}
		$this->_set_record_game(ConstConfig::RECORD_P_DEAL, $tmp_card_arr[0], $tmp_card_arr[1], $tmp_card_arr[2], $tmp_card_arr[3]);
	}
    //第二阶段发牌
	public function DealSecond()
	{
		$tmp_card_arr = ['', '', '', ''];

		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$tmp_card_arr[$i] .= '7';
			for($k=0; $k<7; $k++)
			{
				$temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];	//从牌缓冲区里那张牌
				$this->_list_insert($i, $temp_card);
				$tmp_card_arr[$i] .= '|'.$temp_card;
			}
		}
		$this->_set_record_game(ConstConfig::RECORD_P_DEAL, $tmp_card_arr[0], $tmp_card_arr[1], $tmp_card_arr[2], $tmp_card_arr[3]);
	}

	//开始打牌
	public function game_to_playing()
	{
		//补全阵营
		if ($this->m_liang_jiu_du[0] == 1 || $this->m_liang_jiu_du[0] == 2)
		{
			for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
	        {
	            if (in_array('89',$this->m_sPlayer[$i]->card) || in_array('91',$this->m_sPlayer[$i]->card))
	            {
	                $this->m_team[$i] = 1;
	            }
	        }
	        $team_str = implode("|",$this->m_team);
            $this->_set_record_game(ConstConfig::RECORD_P_R5_R5, $team_str);
		}
        //状态设定
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_DAHONG5_PLAY_CARD;

		//判断有红桃8的出牌者
        for($i = 0 ; $i < $this->m_rule->player_count; $i++ )
        {
            //11 代表的是红桃8
            if (in_array('11',$this->m_sPlayer[$i]->card))
            {
                $this->m_chairCurrentPlayer = $i;
                $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_DAHONG5_STATUS_THINK_OUTCARD;
            }
        }

		//状态变化发消息
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}
		$this->handle_flee_play(true);	//更新断线用户
	}

	//第一阶段叫牌
	public function start_bid()
	{
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_DAHONG5_DIBS_FIRST;
		for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
		{
			$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_DAHONG5_STATUS_WAITING;
		}

		$next_key = $this->m_nChairBanker;
		for ($i = 0; $i < $this->m_rule->player_count; ++$i)
		{
			if (in_array('89',$this->m_sPlayer[$next_key]->card)
		    || in_array('91',$this->m_sPlayer[$next_key]->card)
		    || in_array('117',$this->m_sPlayer[$next_key]->card)
		    || in_array('125',$this->m_sPlayer[$next_key]->card)
			)
			{
				$this->m_sPlayer[$next_key]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
				break;
			}
			else
			{
				$this->m_bids[$next_key] = 255;
				$next_key = $this->_anti_clock($next_key);
				if ($next_key == $this->m_nChairBanker)
				{
					sleep(2);
					for ($k = 0; $k < $this->m_rule->player_count; ++$k)
					{
						$this->m_bids[$k] = 0;
					}
					$this->DealSecond();
					for ($j = 0; $j < $this->m_rule->player_count; ++$j)
					{
						if (in_array('89',$this->m_sPlayer[$j]->card) && in_array('91',$this->m_sPlayer[$j]->card))
			            {
			            	$this->m_double5 = $j;
			            }
					}
		            
					$this->m_sysPhase = ConstConfig::SYSTEMPHASE_DAHONG5_DU;
					$this->m_sPlayer[$this->m_nChairBanker]->state = ConstConfig::PLAYER_DAHONG5_STATUS_DIBS;
				}
			}
		}

		for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
		{
			//发消息
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
		}
	}

	//开始游戏
	public function on_start_game()
	{
		$itime = time();
		//初始化数据，非首局的时候还要相关处理
		$this->InitData();
		$this->m_start_time = $itime;
		$this->m_nSetCount += 1;
		$this->m_room_state = ConstConfig::ROOM_STATE_GAMEING;

		$this->DealFirst();

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
        //统计单张，对子，三张，四张各有多少
		for($i=1; $i<=15; $i++)
		{
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
		if ($buffer_sum == 2 && $buffer_arr[14][0] == 1 && $buffer_arr[15][0] == 1)
		{
			$one-2;
			$pair++;
		}
        //判断牌的数量
		if($buffer_sum <= 0)
		{
			return ConstConfig::PAI_TYPE_DAHONG5_INVALID;
		}
		else if($buffer_sum <= 4)
		{
			if($one == 1)
				return ConstConfig::PAI_TYPE_DAHONG5_ONE;
			if($pair == 1)
				return ConstConfig::PAI_TYPE_DAHONG5_PAIR;
			if($triple == 1)
				return ConstConfig::PAI_TYPE_DAHONG5_TRIPLE;
			if($quartet == 1)
				return ConstConfig::PAI_TYPE_DAHONG5_QUADRUPLE;
			else
                return ConstConfig::PAI_TYPE_DAHONG5_INVALID;
    	}
    	return true;
	}

	private function _list_insert_sub(&$arr, $card)
	{
		$card_type = $this->_get_card_type($card);
		if($card_type == ConstConfig::PAI_TYPE_DAHONG5_INVALID)
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
		//$card_type = $this->_get_card_type($card);
		//$this->m_sPlayer[$chair]->card_index[$card_type] += 1;
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
		//$card_type = $this->_get_card_type($card);
		//$this->m_sPlayer[$chair]->card_index[$card_type] -= 1;
	}

	//返回牌的类型
	private function _get_card_type($card)
	{
		$type = floor($card / 8);
		if($type >0 && $type < 16)
		{
			return $type;
		}
		return ConstConfig::PAI_TYPE_DAHONG5_INVALID;
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

        $this->_send_cmd('s_cancle_game', array('is_cancle'=>$is_cancle, 'm_cancle_first'=>$this->m_cancle_first, 'm_cancle'=>$this->m_cancle, 'cancle_time_start'=>$cancle_time_start), Game_cmd::SCO_ALL_PLAYER );

        if($is_cancle == 1)
        {
            $is_log = false;
            if(($this->m_nSetCount > 1) || ($this->m_nChairBankerNext != 255 && $this->m_nSetCount == 1 && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext)))
            {
                $is_log = true;
            }
            $this->m_sysPhase = ConstConfig::SYSTEMPHASE_DAHONG5_SET_OVER;
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

	public function _set_record_game($act, $param_1 = 0, $param_2 = 0, $param_3 = 0, $param_4 = 0)
	{
		if ($act == ConstConfig::RECORD_P_DEAL)
		{
			$this->m_record_game[] = $act.'|'.$param_1.'|'.$param_2.'|'.$param_3.'|'.$param_4;
		}
		if ($act == ConstConfig::RECORD_P_DISCARD)
		{
			$this->m_record_game[] = $act.'|'.$param_1.'|'.$param_2.'|'.$param_3;
		}
		if ($act == ConstConfig::RECORD_P_R5_R5)
		{
			$this->m_record_game[] = $act.'|'.$param_1;
		}
		if ($act == ConstConfig::RECORD_P_R5_RUNORDER)
		{
			$this->m_record_game[] = $act.'|'.$param_1;
		}
		if ($act == ConstConfig::RECORD_P_R5_GIVEUP)
		{
			$this->m_record_game[] = $act.'|'.$param_1;
		}
		if ($act == ConstConfig::RECORD_P_R5_BID)
		{
			$this->m_record_game[] = $act.'|'.$param_1.'|'.$param_2;
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
        $currency_change_group = [];
        $is_room_over = 0;
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
                if ($this->m_nSetCount == 1 && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext)){
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
