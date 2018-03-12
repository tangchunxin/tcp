<?php
/**
 * @author xuqiang76@163.com
 * @final 20170616
 */

namespace gf\inc;

use gf\inc\ConstConfig;
use gf\conf\Config;
use gf\inc\Room;
use gf\inc\BaseFunction;
use gf\inc\Game_cmd;

class BaseGame
{
	public $serv;	                                   // socket服务器对象

	public $m_ready = array(0,0,0,0);	               // 用户准备
	public $m_game_type;	                           // 游戏编号
	public $m_room_state;	                           // 房间状态
	public $m_room_id;	                               // 房间号
	public $m_room_owner;	                           // 房主
	public $m_room_players = array();	               // 玩家信息
	public $m_rule;	                                   // 规则对象
	public $m_start_time;	                           // 开始时间
	public $m_end_time;	                               // 结束时间
	public $m_record_game;				               // 录制脚本

	public $m_dice = array(0,0);	                   // 两个骰子点数
	public $m_hu_desc = array();		               // 详细的胡牌类型(七小对 天胡, 地胡, 碰碰胡.......)
	public $m_nSetCount;	                           // 比赛局数
	public $m_wTotalScore;				               // 总结的分数

	public $m_nChairDianPao;				           // 点炮玩家椅子号
	public $m_nCountHu;		                           // 胡牌玩家个数
	public $m_nCountFlee;	                           // 逃跑玩家个数

	public $m_bChairHu = array();		               // 已胡玩家
	public $m_bChairHu_order = array();		           // 已胡玩家顺序
	public $m_only_out_card = array();		           // 玩家吃碰以后不能胡的状态，理论上只能杠

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
	public $m_wHuScore = array();					   // 本局胡整合分数
	public $m_wSetScore = array();				       // 该局的胡分数
	public $m_wSetLoseScore = array();			       // 该局的被胡分数
	public $m_Score = array();	                       // 用户分数结构
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

	public $m_nChairBanker;				                // 庄家的位置，
	public $m_nChairBankerNext = 255;				    // 下一局庄家的位置，
	public $m_nCountAllot;					            // 发到第几张牌
	public $m_nAllCardNum = ConstConfig::BASE_CARD_NUM;
	public $m_sOutedCard;			                    // 刚打出的牌
	public $m_sysPhase;				                    // 当前阶段状态
	public $m_chairCurrentPlayer;			            // 当前出牌者

	public $m_deal_card_arr = array();					//每墩发牌数组

	public $m_bLastGameOver; // 打牌  牌局是否最终结束
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
	}

	public function clear()
	{
		$this->InitData();
	}

	//留给子类重载
	public function InitDataSub()
	{}

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
		}

		$this->InitDataSub();
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

			$this->_send_cmd('s_flee', array('flee_time'=>$flee_time), Game_cmd::SCO_ALL_PLAYER );
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
						//$this->_send_cmd('s_chat', array("type"=>$params['type'], "content"=>$params['content']), Game_cmd::SCO_ALL_PLAYER_EXCEPT , $params['uid']);
						$this->_send_cmd('s_chat', array("type"=>$params['type'], "content"=>$params['content'], "chair"=>$key, "uid"=>$params['uid']), Game_cmd::SCO_ALL_PLAYER );
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

	//留给子类重载
	public function _open_room_sub($params)
	{}

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

			$this->_open_room_sub($params);

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
			$this->m_room_players[$add_key]['score'] = 0;	//录像信息用

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));
		if(0 == $return_send['code'])
		{
			$this->handle_flee_play(true);	//更新断线用户
			$this->_send_cmd('s_join_room', array('m_room_players'=>$this->m_room_players, 'm_ready'=>$this->m_ready), Game_cmd::SCO_ALL_PLAYER );

			$this->c_ready($fd, $params);
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
				$this->_send_cmd('s_ready', array('base_player_count'=>$this->m_rule->player_count, 'm_room_players'=>$this->m_room_players, 'm_ready'=>$this->m_ready, 'm_nSetCount'=>$this->m_nSetCount, 'm_wTotalScore'=>$this->m_wTotalScore), Game_cmd::SCO_ALL_PLAYER );
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
                    if(empty($params['is_14']) && 0 == $this->_list_find($key,$params['out_card']))
                    {
                        $return_send['code'] = 5; $return_send['text'] = '出牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
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

                    $this->HandleOutCard($key, $params['is_14'], $params['out_card']);
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
			$this->game_to_playing();
		}
	}

	//--------------------------------------------------------------------

	public function _do_c_hu($temp_card, $dian_pao_chair, &$bHaveHu, &$record_hu_chair)
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
					$bHaveHu = true;
					$record_hu_chair[] = $hu_chair;
					if($this->m_HuCurt[$hu_chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
					{
						$this->m_wTotalScore[$hu_chair]->n_jiepao += 1;
						$this->m_wTotalScore[$this->m_nChairDianPao]->n_dianpao += 1;
					}

					$this->m_bChairHu[$hu_chair] = true;
					$this->m_bChairHu_order[] = $hu_chair;
					$this->m_nCountHu++;
					$this->m_sPlayer[$hu_chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;

					if(255 == $this->m_nChairBankerNext || $hu_chair == $this->m_nChairBanker)	//下一局庄家
					{
						$this->m_nChairBankerNext = $hu_chair;
					}
					$this->_send_act($this->m_currentCmd, $hu_chair);
				}
			}

			//多人胡牌状态，最后算分，防止一炮多响点炮三家出之类的bug
			foreach ($tmp_hu_arr as $hu_chair)
			{
				$this->ScoreOneHuCal($hu_chair, $dian_pao_chair);
			}
		}
	}

	public function HandleZhaHu($chair)
	{
		//以后另做处理，客户端诈胡等于作弊
		$this->m_nNumCheat[$chair]++;
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
		if(empty($this->m_rule) || $this->m_rule->set_num <= $this->m_nSetCount)
		{
			$this->m_room_state = ConstConfig::ROOM_STATE_OVER;
			$this->m_bLastGameOver = 1;
		}

		//状态变化发消息
		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_game_over', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		$this->_set_game_and_checkout();

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
			}
		}
	}

	public function game_to_playing()
	{
		$tmp_card_arr = $this->m_deal_card_arr;
		for ($n=0; $n <= 3; $n++)
		{
			$this->_set_record_game(ConstConfig::RECORD_DRAW_ALL, intval($tmp_card_arr[$n][0]), intval($tmp_card_arr[$n][1]), intval($tmp_card_arr[$n][2]), intval($tmp_card_arr[$n][3]));
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
			if($i != $this->m_nChairBanker)
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_WAITING;
			}
			else
			{
				$this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
				$this->m_bChooseBuf[$i] = 1;

				//整理排序
				//$this->_list_insert($i, $this->m_sPlayer[$i]->card_taken_now);
				//$this->m_sPlayer[$i]->card_taken_now = $this->_find_14_card($i);
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
		if(!empty($this->m_rule->is_paozi))
		{
			$this->start_pao_zi();
			return true;
		}
		//发牌
		$this->DealAllCardEx();
		$this->game_to_playing();

		return true;
	}

	////////////////////////////其他///////////////////////////
	//玩家i相对于玩家j的位置,如(0,3),返回1(即下家)
	public function _chair_to($i, $j)
	{
		$tmp_chair = ($j - $i + $this->m_rule->player_count) % ($this->m_rule->player_count);
		return $tmp_chair;
	}

	//返回chair逆时针转 n 的玩家
	public function _anti_clock($chair, $n = 1)
	{
		$tmp_chair = ($chair + $this->m_rule->player_count + $n) % ($this->m_rule->player_count);
		return $tmp_chair;
	}

	public function _send_act($cmd, $chair, $card=0)
	{
		$this->_send_cmd('s_act', array('cmd'=>$cmd, 'chair'=>$chair, 'card'=>$card), Game_cmd::SCO_ALL_PLAYER);
	}

	//向客户端发送后台处理数据
	public function _send_cmd($act, $data, $scope = Game_cmd::SCO_ALL_PLAYER, $uid = 0)
    {
        $cmd = new Game_cmd($this->m_room_id, $act, $data, $scope, $uid);
        $cmd->send($this->serv);
        unset($cmd);
    }

	//插入牌
	public function _list_insert($chair, $card)
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
	public function _list_delete($chair, $card)
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
	public function _list_find($chair, $card)
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
	public function _get_card_type($card)
	{
		if($card <= 9 && $card >= 1)	return ConstConfig::PAI_TYPE_WAN;
		if($card <= 25 && $card >= 17)	return ConstConfig::PAI_TYPE_TIAO;
		if($card <= 41 && $card >= 33)	return ConstConfig::PAI_TYPE_TONG;
		if($card <= 55 && $card >= 49)	return ConstConfig::PAI_TYPE_FENG;
		if($card <= 72 && $card >= 65)	return ConstConfig::PAI_TYPE_DRAGON;
		return ConstConfig::PAI_TYPE_PAI_TYPE_INVALID;
	}

	//牌index
	public function _get_card_index($type, $key)
	{
		//四川麻将没有风牌和花牌
		if($type >=ConstConfig::PAI_TYPE_WAN  && $type <=ConstConfig::PAI_TYPE_DRAGON && $key >=1 && $key <=9)
		{
			return $type * 16 + $key;
		}
		return 0;
	}

	//取消选择buf
	public function _clear_choose_buf($chair, $ClearGang=true)
	{
		if($ClearGang)
		{
			$this->m_sQiangGang->clear();
		}
		$this->m_bChooseBuf[$chair] = 0;
	}

	//判断有没有吃
	public function _find_eat($chair,$num)
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
	public function _find_peng($chair)
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
	public function _find_zhi_gang($chair)
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
	public function _find_14_card($chair)
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
	public function _on_table_status_to_playing()
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

	public function _cancle_game()
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

		$this->_send_cmd('s_cancle_game', array('is_cancle'=>$is_cancle, 'm_cancle_first'=>$this->m_cancle_first, 'm_cancle'=>$this->m_cancle), Game_cmd::SCO_ALL_PLAYER );

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
			$this->_send_cmd('s_game_over', $this->OnGetChairScene($this->m_cancle_first, true), Game_cmd::SCO_ALL_PLAYER );

			$this->_set_game_and_checkout($is_log);

			$this->clear();
		}

		return $is_cancle;
	}

	//跟庄
	public function _genzhuang_do()
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

	public function _deal_test_card()
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

	public function _change_pai($chair)
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

		$this->m_hun_card = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index
		return $this->m_hun_card;
	}

	public function _set_record_game($act, $param_1 = 0, $param_2 = 0, $param_3 = 0, $param_4 = 0)
	{
		$param_1_tmp = 0;
		$param_3_tmp = 0;
		if(in_array($act, [ConstConfig::RECORD_CHI, ConstConfig::RECORD_PENG, ConstConfig::RECORD_ZHIGANG
			, ConstConfig::RECORD_ANGANG, ConstConfig::RECORD_ZHUANGANG, ConstConfig::RECORD_HU
			, ConstConfig::RECORD_ZIMO, ConstConfig::RECORD_DISCARD, ConstConfig::RECORD_DRAW
			, ConstConfig::RECORD_DEALER, ConstConfig::RECORD_FANHUN, ConstConfig::RECORD_PENG_ZA
			, ConstConfig::RECORD_ZHIGANG_ZA, ConstConfig::RECORD_ANGANG_ZA, ConstConfig::RECORD_BIAN
			, ConstConfig::RECORD_ZUAN, ConstConfig::RECORD_HU_QIANGGANG, ConstConfig::RECORD_YIKOUXIANG
			]
			))
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

	public function _set_game_info()
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

	public function _is_quemen($chair, $fanhun_type, $fanhun_num)
	{
		$is_quemen = false;
		//判断缺门(考虑到翻混)
		if(!empty($this->m_rule->is_quemen))
		{
			$is_quemen = true;
			$sum = 0;
			$stand_type_arr = array(0, 0, 0);

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
	public function _is_yise($qing_arr, &$is_qingyise, &$is_ziyise)
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

	public function _is_menqing($chair)
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

	public function _get_max_fan($fan_sum)
	{
		$return_fan = $fan_sum;
		if (!empty($this->m_rule->top_fan) && $fan_sum > $this->m_rule->top_fan)
		{
			$return_fan = $this->m_rule->top_fan;
		}
		return $return_fan;
	}

}