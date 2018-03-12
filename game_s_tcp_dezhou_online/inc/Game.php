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

class Game
{
	public $serv;	//socket服务器对象

	public $m_ready = array(0,0,0,0);	//用户准备
	public $m_game_type;	//游戏 1 血战到底 2 陕西麻将 3河北承德麻将
	public $m_room_state;	//房间状态
	public $m_room_id;	//房间号
	public $m_room_owner;	//房主
	public $m_room_players = array();	//玩家信息
	public $m_rule;	//规则对象
	public $m_start_time;	//开始时间
	public $m_end_time;	//结束时间

	public $m_dice = array(0,0);	//两个骰子点数
	public $m_hu_desc = array();		// 详细的胡牌类型(七小对 天胡, 地胡, 碰碰胡.......)
	public $m_nSetCount;	//比赛局数
	public $m_wTotalScore;				// 总结的分数

	public $m_nChairDianPao;				// 点炮玩家椅子号
	public $m_nCountHu;		//胡牌玩家个数
	public $m_nCountFlee;	//逃跑玩家个数

	public $m_bChairHu = array();		// 血战已胡玩家
	public $m_bChairHu_order = array();		// 血战已胡玩家顺序
	public $m_only_out_card = array();		// 玩家只能出牌不能碰杠胡

	public $m_bTianRenHu;							///以判断地天人胡

	public $m_bNoCard;							///荒庄判断

	public $m_nDiHu = array();					// 判断地胡

	public $m_nEndReason;					// 游戏结束原因

	public $m_sQiangGang;			// 抢杠结构
	public $m_sGangPao;				// 杠炮结构
	public $m_sFollowCard;				// 跟庄结构
	public $m_bHaveGang;                   // 是否有杠开
	//public $m_own_paozi;	//用户炮子结构
	//public $m_paozi_score = array();	//炮子的输赢分


	//记分，以后处理
	public $m_wGangScore = array();			// 刮风下雨总分数
	public $m_wGFXYScore = array();				//刮风下雨临时分数
	public $m_wHuScore = array();					// 本剧胡整合分数
	public $m_wSetScore = array();				// 该局的胡分数
	public $m_wSetLoseScore = array();			// 该局的被胡分数
	public $m_Score = array();	//用户分数结构
	//public $m_wChairBanker = array();	//庄家分数结构  2分
	public $m_wFollowScore = array();	//跟庄庄家分数结构


	//数据区
	public $m_cancle = array();	//解散房间标志
	public $m_cancle_first;	//解散房间发起人

	public $m_nTableCards = array();		// 玩家的桌面牌
	public $m_nNumTableCards = array();	//玩家桌面牌数量
	public $m_sStandCard = array();			// 玩家倒牌 Stand_card
	public $m_sPlayer = array();				// 玩家手牌私有数据 Play_data
	public $m_nNumCheat = array();				// 玩家i诈胡次数

	//逃跑用户
	public $m_bFlee = array();

	//处理选择命令
	public $m_bChooseBuf = array();			// 玩家的选择胡,吃,碰,杠命令 1 等待操作 0 无操作
	public $m_nNumCmdHu;				// 胡命令的个数
	public $m_chairHu = array();				// 发出胡命令的玩家
	public $m_chairSendCmd;				// 当前发命令的玩家
	public $m_currentCmd;			// 当前的命令
	public $m_eat_num;			// 竞争选择吃法 存储 

	// 接收客户端数据
	//public $m_nJiang = array();				// 判断胡牌的将,不能胡时将为255;
	public $m_nHuGiveUp = array();			// 该轮放弃胡的番数,m_nHuGiveUp = [][0]: 个数

	// 与客户端无关
	public $m_nCardBuf = array();			// 牌的缓冲区
	public $m_HuCurt = array();	//胡牌信息

	public $m_bMaxFan = array();	//是否达到封顶番数

	public $m_nChairBanker;				// 庄家的位置，
	public $m_nChairBankerNext = 255;				// 下一局庄家的位置，
	public $m_nCountAllot;					// 发到第几张牌
	public $m_nAllCardNum = 108;
	public $m_sOutedCard;			// 刚打出的牌
	public $m_sysPhase;				// 当前阶段状态
	public $m_chairCurrentPlayer;			// 当前出牌者
	public $m_set_end_time;	//本局结束时间

	public $m_fan_hun_card;	//翻混牌
	public $m_hun_card;	//混牌

    public $m_bLastGameOver; //牌局是否最终结束
	//public $m_nHuList = array();			// 胡牌列表, m_nHuCList = [][0]: 可胡牌的个数

	/************************************************************************/
	/*                               函数区                                 */
	/************************************************************************/

	public function __construct($serv)
	{
		$this->serv = $serv;
		
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_SET_OVER ;
		$this->m_room_state = ConstConfig::ROOM_STATE_NULL ;
		$this->m_game_type = Config::GAME_TYPE;
	}
	
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

	//回调web   ok
	public function set_game_and_checkout($is_log=false)
	{
		//游戏记录
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
			|| ( $this->m_nSetCount != 255 && $this->m_rule->set_num <= $this->m_nSetCount )
			|| ( $this->m_nSetCount == 255 && $is_log )
			)
		{
			$is_room_over = 1;
		}
		BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'set_game_log', 'platform'=>'gfplay', 'rid'=>$this->m_room_id,'uid'=>$this->m_room_owner, 'uid_arr'=>implode(',', $uid_arr)
		, 'game_info'=>json_encode($this->OnGetChairScene(0, true)),'type'=>1, 'is_room_over'=>$is_room_over, 'game_type'=>$this->m_game_type, 'play_time'=>$itime - $this->m_start_time));
		//扣费或充值

		if(Config::IS_ROOM_OWNER_PAY)
		{
			if($this->m_nSetCount == 1 && !($this->m_bNoCard))
			{
				$currency = (!empty(Config::$set_num_arr[$this->m_rule->set_num])) ? -(Config::$set_num_arr[$this->m_rule->set_num]) : 0;
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
				$currency_all = (!empty(Config::$set_num_arr[$this->m_rule->set_num])) ? (Config::$set_num_arr[$this->m_rule->set_num]) : 0;
				$currency = -(intval($currency_all/$winner_count));
				foreach ($winner_arr as $item_user)
				{
					BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'checkout_open_room', 'platform'=>'gfplay', 'uid'=>$item_user, 'currency'=>$currency,'type'=>1));
				}
			}			
		}
	}
	
	//处理逃跑玩家  ok
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
				//				else
				//				{
				//					if(empty($this->m_room_players[$key]['is_room_owner']) && time() - $this->m_room_players[$key]['flee_time'] > 3600)
				//					{
				//						//非房主，断线超时，剩下的玩家根据牌局状态处理
				//						//unset($this->m_room_players[$key]);
				//					}
				//				}
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

	//心跳  ok
	public function c_tiao($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if($this->m_room_state != ConstConfig::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfig::ROOM_STATE_OPEN )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间已经不存来了'; $return_send['desc'] = __LINE__; break;
			}
			//有掉线用户
			if($this->handle_flee_play())
			{
				//有人断线，检测游戏结束投票
				$this->_cancle_game();
			}

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));
		return $return_send['code'];
	}
	
	//表情 文字	  ok
	public function c_chat($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['type'])
			|| !isset($params['content'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if($this->m_room_state != ConstConfig::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfig::ROOM_STATE_OPEN )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
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

	//开新的房间或者加入房间取房间状态（给http的server用）  ok
	public function c_get_room($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		$itime = time();
		do {

			if($this->m_room_state != ConstConfig::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfig::ROOM_STATE_OPEN )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}
			if($itime - $this->m_start_time > Room::$room_timeout)
			{
				//超时
				$this->m_room_state = ConstConfig::ROOM_STATE_NULL ;
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}
						
			foreach ($this->m_room_players as $key => $room_user)
			{
				if(!empty($params['uid']) && $room_user['uid'] == $params['uid'])
				{
					$return_send['code'] = 4; $return_send['text'] = '你有未结束的游戏'; $return_send['desc'] = __LINE__; break 2;
				}
				else if( $key == $this->m_rule->player_count - 1)
				{
					$return_send['code'] = 3; $return_send['text'] = '房间已满'; $return_send['desc'] = __LINE__; break 2;
				}
			}
			if(!empty($params['uid']) && count($this->m_room_players) == 0 && $this->m_room_owner == $params['uid'])
			{
				$return_send['code'] = 5; $return_send['text'] = '你有未结束的游戏'; $return_send['desc'] = __LINE__; break ;
			}
		}while(false);

		$this->serv->send($fd,  Room::tcp_encode($return_send, false));
		return $return_send['code'];
	}

	//掉线玩家重新回到游戏，取数据   ok
	public function c_get_game($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {

			if($this->m_room_state != ConstConfig::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfig::ROOM_STATE_OPEN )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间已经不存在了'; $return_send['desc'] = __LINE__; break;
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
				$return_send['code'] = 2; $return_send['text'] = '房间已经不存在了'; $return_send['desc'] = __LINE__; break;
			}
			
		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));
		return $return_send['code'];
	}

	//开房（给http的server用）   ok  ++
	public function c_open_room($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		$itime = time();
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['game_type'])
			|| empty($params['rule'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_SET_OVER || $this->m_room_state == ConstConfig::ROOM_STATE_GAMEING )
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__; break;
			}
			elseif ($this->m_room_state == ConstConfig::ROOM_STATE_OPEN  && $this->m_room_owner != $params['uid'])
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__; break;
			}
			$this->clear();

			$this->m_rule = new Rule();
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
			//$this->m_rule->is_yipao_duoxiang = $params['rule']['is_yipao_duoxiang'];
			$this->m_rule->is_chipai = $params['rule']['is_chipai'];
			$this->m_rule->is_258_jiang = $params['rule']['is_258_jiang'];
			$this->m_rule->is_zimo = $params['rule']['is_zimo'];

			$this->m_rule->is_qingyise_fan = $params['rule']['is_qingyise_fan'];
			$this->m_rule->is_yitiaolong_fan = $params['rule']['is_yitiaolong_fan'];
			$this->m_rule->is_ganghua_fan = $params['rule']['is_ganghua_fan'];
			$this->m_rule->is_qidui_fan = $params['rule']['is_qidui_fan'];
			//$this->m_rule->is_tiandi_hu_fan = $params['rule']['is_tiandi_hu_fan'];

			$this->m_rule->is_hunyise_fan = $params['rule']['is_hunyise_fan'];
			$this->m_rule->is_dadiaoche_fan = $params['rule']['is_dadiaoche_fan'];
	


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

	//加入房间，房主必须第一个加入房间   ok
	public function c_join_room($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__, "text"=>"");
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
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}
			
			//性别兼容以前的
			if(empty($params['sex']))
			{
				$params['sex'] = 0;
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}
			else if(empty($this->m_room_players) && ($itime - $this->m_start_time) > Room::$room_timeout)
			{
				$this->m_room_state = ConstConfig::ROOM_STATE_OVER ;
				$this->clear();
				$return_send['code'] = 3; $return_send['text'] = '没有此房间'; $return_send['desc'] = __LINE__; break;
			}

			if( ($params['is_room_owner'] && $params['uid'] != $this->m_room_owner)
			|| ($params['is_room_owner'] && !empty($this->m_room_players[0]) && $params['uid']!=$this->m_room_players[0]['uid'])
			)
			{
				$return_send['code'] = 4; $return_send['text'] = '房主错误'; $return_send['desc'] = __LINE__; break;
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
					$return_send['code'] = 5; $return_send['text'] = '房间已满'; $return_send['desc'] = __LINE__; break 2;
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
					$return_send['code'] = 4; $return_send['text'] = '房主错误'; $return_send['desc'] = __LINE__; break;
				}
				$this->m_room_players[$add_key]['is_room_owner'] = $params['is_room_owner'];
			}
			$this->m_room_players[$add_key]['fd'] = $fd;
			$this->m_room_players[$add_key]['uid'] = $params['uid'];
			$this->m_room_players[$add_key]['is_room_owner'] = $params['is_room_owner'];
			$this->m_room_players[$add_key]['ip'] = $params['ip'];
			$this->m_room_players[$add_key]['uname'] = $params['uname'];
			$this->m_room_players[$add_key]['head_pic'] = $params['head_pic'];
			$this->m_room_players[$add_key]['sex'] = $params['sex'];

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));
		if(0 == $return_send['code'])
		{
			$this->handle_flee_play(true);	//更新断线用户
			$cmd = new Game_cmd($this->m_room_id, 's_join_room', array('m_room_players'=>$this->m_room_players, 'm_ready'=>$this->m_ready), Game_cmd::SCO_ALL_PLAYER );
			$cmd->send($this->serv);
			unset($cmd);
			
			$this->c_ready($fd, $params);		
		}

		return $return_send['code'];
	}

	//准备开始   ok
	public function c_ready($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		$itime = time();
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
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
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}
			
			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_SET_OVER || (ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
			{
				$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($u_key,true), Game_cmd::SCO_SINGLE_PLAYER , $params['uid']);
				$cmd->send($this->serv);
				unset($cmd);				
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
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

	//解散房间   ok
	public function c_cancle_game($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['yes'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if((ConstConfig::ROOM_STATE_OPEN != $this->m_room_state && ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state))
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
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
				$return_send['code'] = 3; $return_send['text'] = '解散房间请求错误'; $return_send['desc'] = __LINE__; break;
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
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| !isset($params['pao_zi_num'])
			|| !in_array($params['pao_zi_num'], array(0, 1, 2, 3, 4 ))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_XIA_PAO || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if ($this->m_own_paozi[$key]->recv)
					{
						$return_send['code'] = 4; $return_send['text'] = '您已经下炮子了'; $return_send['desc'] = __LINE__; break 2;
					}

					$this->handle_pao_zi($key, $params['pao_zi_num']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	public function handle_pao_zi($chair, $pao_zi_num)
	{
		$this->m_own_paozi[$chair]->recv = true;
		$this->m_own_paozi[$chair]->num = $pao_zi_num;

		for ($i = 0; $i<$this->m_rule->player_count; ++$i)
		{
			if (!$this->m_own_paozi[$i]->recv)
			{
				break;
			}
		}
		if ($this->m_rule->player_count == $i)
		{
			$this->DealAllCardEx();
			$this->game_to_playing();
		}
	}

	//胡   ok
	public function c_zimo_hu($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
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
							$return_send['code'] = 6; $return_send['text'] = '连续发送胡牌信息'; $return_send['desc'] = __LINE__; break 2;
						}
					}
					if($this->m_sPlayer[$key]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
					{
						$return_send['code'] = 7; $return_send['text'] = '胡牌错误'; $return_send['desc'] = __LINE__; break 2;
					}
					
					if($key != $this->m_chairCurrentPlayer)
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户错误'; $return_send['desc'] = __LINE__; break 2;
					}
					if($this->m_only_out_card[$key] == true)
					{
						$return_send['code'] = 6; $return_send['text'] = '当前用户状态只能出牌'; $return_send['desc'] = __LINE__; break 2;
					}
					if(!$this->HandleHuZiMo($key))	// 诈胡
					{
						$this->ClearChooseBuf($key);
						$return_send['code'] = 5; $return_send['text'] = '诈胡'; $return_send['desc'] = __LINE__; break 2;
					}
					$this->ClearChooseBuf($key);	  //自摸不可能抢杠胡
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

    //暗杠  ok
	public function c_an_gang($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['gang_card'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if($key != $this->m_chairCurrentPlayer)
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户错误'; $return_send['desc'] = __LINE__; break 2;
					}

					if(4 != $this->list_find($key,$params['gang_card'])
					&& !(($params['gang_card'] == $this->m_sPlayer[$key]->card_taken_now) && 3 == $this->list_find($key,$params['gang_card']))
					)
					{
						$return_send['code'] = 5; $return_send['text'] = '杠牌错误'; $return_send['desc'] = __LINE__; break 2;
					}
					
					$this->ClearChooseBuf($key);
					$this->HandleChooseAnGang($key, $params['gang_card']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//弯杠  ok
	public function c_wan_gang($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['gang_card'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if($key != $this->m_chairCurrentPlayer)
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户错误'; $return_send['desc'] = __LINE__; break 2;
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
					if(!$have_wan_gang || ($params['gang_card'] != $this->m_sPlayer[$key]->card_taken_now && 0 == $this->list_find($key,$params['gang_card'])))
					{
						$return_send['code'] = 5; $return_send['text'] = '杠牌错误'; $return_send['desc'] = __LINE__; break 2;
					}
					
					$this->ClearChooseBuf($key);
					$this->HandleChooseWanGang($key, $params['gang_card']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));
		return $return_send['code'];
	}

	//出牌  
	public function c_out_card($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| (empty($params['is_14']) && empty($params['out_card']))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if($key != $this->m_chairCurrentPlayer)
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户错误'; $return_send['desc'] = __LINE__; break 2;
					}

					$this->ClearChooseBuf($key);
					if(empty($params['is_14']) && 0 == $this->list_find($key,$params['out_card']))
					{
						$return_send['code'] = 5; $return_send['text'] = '出牌错误'; $return_send['desc'] = __LINE__; break 2;
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
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//取消杠  ok
	public function c_cancle_gang($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if($key != $this->m_chairCurrentPlayer)
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户错误'; $return_send['desc'] = __LINE__; break 2;
					}

					$this->m_sPlayer[$key]->state = ConstConfig::PLAYER_STATUS_THINK_OUTCARD ;
					$this->ClearChooseBuf($key);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//碰牌  ok
	public function c_peng($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					$params['type'] = 0;
					if(!$this->find_peng($key))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; $return_send['text'] = '当前用户无碰'; $return_send['desc'] = __LINE__; break 2;
					}
					if(empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || 2 > $this->list_find($key,$this->m_sOutedCard->card))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '碰牌错误'; $return_send['desc'] = __LINE__; break 2;
					}
					
					$this->ClearChooseBuf($key);
					$this->HandleChooseResult($key, $params['act']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//吃牌	ok
	public function c_eat($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['num'])
			|| !in_array($params['num'],array(1,2,3))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					$params['type'] = 0;
					if(!$this->find_eat($key,$params['num']))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; $return_send['text'] = '当前用户无吃牌'; $return_send['desc'] = __LINE__; break 2;
					}
					if(empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || $this->m_sOutedCard->chair != $this->AntiClock($key,-1))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '吃牌错误'; $return_send['desc'] = __LINE__; break 2;
					}
					
					$this->ClearChooseBuf($key);
					$this->HandleChooseResult($key, $params['act'], $params['num']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//直杠  ok
	public function c_zhigang($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					$params['type'] = 0;
					if(!$this->find_zhi_gang($key))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; $return_send['text'] = '当前用户无直杠'; $return_send['desc'] = __LINE__; break 2;
					}
					if(empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || 3 > $this->list_find($key,$this->m_sOutedCard->card))
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '杠牌错误'; $return_send['desc'] = __LINE__; break 2;
					}
					
					$this->ClearChooseBuf($key);
					$this->HandleChooseResult($key, $params['act']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//胡 
	public function c_hu($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);

		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
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
							$return_send['code'] = 6; $return_send['text'] = '连续发送胡牌信息'; $return_send['desc'] = __LINE__; break 2;
						}
					}
					
					$params['type'] = 0;
					if( (empty($this->m_sOutedCard->card) && empty($this->m_sQiangGang->card))
					  || ($this->m_sOutedCard->card && $this->m_sOutedCard->chair == $key)
					  || ($this->m_sQiangGang->card && $this->m_sQiangGang->chair == $key)
					  )
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '胡牌错误'; $return_send['desc'] = __LINE__; break 2;
					}
					if($this->m_sPlayer[$key]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '胡牌错误'; $return_send['desc'] = __LINE__; break 2;
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
						$return_send['code'] = 5; $return_send['text'] = '胡牌错误'; $return_send['desc'] = __LINE__; break 2;
					}
					$this->list_insert($key, $temp_card);
					$this->m_HuCurt[$key]->card = $temp_card;
					if(!$this->judge_hu($key,$is_fanhun))
					{
						$this->m_HuCurt[$key]->clear();
						$this->list_delete($key, $temp_card);
						$this->HandleZhaHu($key);
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; $return_send['text'] = '当前用户诈胡'; $return_send['desc'] = __LINE__; break 2;
					}
					$this->m_HuCurt[$key]->clear();
					$this->list_delete($key, $temp_card);

					$this->ClearChooseBuf($key,false);
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

						$this->list_insert($last_chair, $temp_card);
						$this->m_HuCurt[$last_chair]->card = $temp_card;
						if( self::is_hu_give_up($temp_card, $this->m_nHuGiveUp[$last_chair]) || !$this->judge_hu($last_chair,$is_fanhun))
						{
							$this->m_sPlayer[$last_chair]->state = ConstConfig::PLAYER_STATUS_WAITING;
							$c_act = "c_cancle_choice";
						}
						$this->m_HuCurt[$last_chair]->clear();
						$this->list_delete($last_chair, $temp_card);

						$this->ClearChooseBuf($last_chair, false);
						$this->HandleChooseResult($last_chair, $c_act);
					}
					
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

    //取消选择   ok
	public function c_cancle_choice($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| !isset($params['type'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}
			
			$params['act'] = 'c_cancle_choice';

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if(!($this->m_bChooseBuf[$key]))
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户无需选择'; $return_send['desc'] = __LINE__; break 2;
					}

					if(4 == $params['type'])	//过手胡
					{
						$temp_card = $this->m_sOutedCard->card;
						if ($this->m_sQiangGang->mark )
						{
							$temp_card = $this->m_sQiangGang->card;
						}
						$this->list_insert($key, $temp_card);
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
						$this->list_delete($key, $temp_card);
					}
					$this->ClearChooseBuf($key, false); //有可能取消的是抢杠胡，这是需要后面判断来补张
					$this->m_sPlayer[$key]->state = ConstConfig::PLAYER_STATUS_WAITING;
					$this->HandleChooseResult($key, $params['act']);
					$is_act = true;
				}
			}
			if(!$is_act = true)
			{
				$return_send['code'] = 3; $return_send['text'] = '用户不属于本房间'; $return_send['desc'] = __LINE__; break;
			}

		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//--------------------------------------------------------------------------


	//判断胡   ok
	public function judge_hu($chair, $is_fanhun = false)
	{
		//胡牌型
		$is_qingyise = false;
		$is_yitiaolong = false;
		$is_da8zhang = false;
		$hu_type = $this->judge_hu_type($chair, $is_qingyise, $is_hunyise, $is_jiangyise, $is_yitiaolong ,$is_dadiaoche);

		if($hu_type == ConstConfig::HU_TYPE_FENGDING_TYPE_INVALID)
		{
		    echo '99999999999999999999999999999999';
		    var_dump($hu_type);
			return false;
		}

		if(!$is_da8zhang && !empty($this->m_rule->is_da8zhang)) 
		{
			echo "88888888888888888888".__LINE__;
			return false;			
		}

		//只能是自摸胡牌
		if($this->m_rule->is_zimo)
		{
			if($this->m_HuCurt[$chair]->state != ConstConfig::WIN_STATUS_ZI_MO)
			{
				return false;
			}
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
					$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_TIANHU);
				}
				else
				{
					$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_DIHU);
				}
			}
			else if(0 == $this->m_nDiHu[$chair])
			{
				$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_DIHU);
			}
		}

		
		//自摸加番
		// if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		// {
		// 	$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_ZIMOFAN);
		// }

		//抢杠杠开杠炮
		if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)	// 处理抢杠
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_QIANGGANG);
		}
		else if(!empty($this->m_rule->is_ganghua_fan) && $this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair)	//杠开
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_GANGKAI);
		}
		else if ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO && $this->m_sGangPao->mark && $this->m_sGangPao->chair != $chair)
		{
			//$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_GANGPAO);
		}

		//清一色
		if($is_qingyise && $this->m_rule->is_qingyise_fan)
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_QINGYISE);			
		}

		//一条龙
		if($is_yitiaolong && $this->m_rule->is_yitiaolong_fan)
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_YITIAOLONG);			
		}

		//混一色
		if($is_hunyise && $this->m_rule->is_hunyise_fan)
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_HUNYISE);	
		}

		//将一色
		if($is_jiangyise)
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_JIANGYISE);	
		}

		//大吊车
		if($is_dadiaoche && $this->m_rule->is_dadiaoche_fan)
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_DADIAOCHE);
		}
        //海底捞(城墙最后一张自摸)
        if($this->m_nCountAllot >= $this->m_nAllCardNum-1 && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
        {
            $this->m_HuCurt[$chair]->add_hu(ConstConfig::ATTACHED_HU_HAIDILAOYUE);
        }

		return true;			
	}

	//胡牌类型判断  没有混的情况
	public function judge_hu_type($chair, &$is_qingyise, &$is_hunyise,&$is_jiangyise ,&$is_yitiaolong,&$is_dadiaoche)
	{
		$jiang_arr = array(); 
		$qidui_arr = array();
		$qing_arr = array();
		$shisanyao_arr = array();
		$gen_arr = array();
		$pengpeng_arr = array();
		$jiangyise_arr = array();
		
		$bType32 = false;
		$bQiDui = false;
		$bQing = false;     
		$bHunYiSe = false;     
		$bPengPeng = false;
		$bJiangYiSe = false;

		$is_qingyise =  false;  //清一色
		$is_hunyise =  false;  //混一色
		$is_jiangyise =  false;  //将一色
		$is_yitiaolong = false;   //一条龙
		$is_da8zhang = false;   //打八张
		$is_dadiaoche = false;  //大吊车
		$is_258_jiang = false;

		//手牌
		if($this->m_rule->is_feng && $i = ConstConfig::PAI_TYPE_FENG)
		{
			if(0 < $this->m_sPlayer[$chair]->card[$i][0])
			{
				$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));
				if(!isset(ConstConfig::$hu_data_feng[$key]))
				{
					return ConstConfig::HU_TYPE_FENGDING_TYPE_INVALID ;
				}
				else
				{					
					$hu_list_val = ConstConfig::$hu_data_feng[$key];
										
					//七对判断
					$qidui_arr[] = $hu_list_val & 64;
					//对对胡
					$pengpeng_arr[] = $hu_list_val & 8;			
					//将一色
					$jiangyise_arr[] = 0;

					//32牌型判断
					if($hu_list_val & 1 == 1)
					{
						$jiang_arr[] = $hu_list_val & 32;
					}
					else 
					{
						//非32牌型设置
						$jiang_arr[] = 32;
						$jiang_arr[] = 32;
					}
					$qing_arr[] = $i;	
				}
			}
		}

		for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
		{

			if(0 == $this->m_sPlayer[$chair]->card[$i][0])
			{
				continue;
			}

			if(in_array($this->m_sPlayer[$chair]->card[$i][0], array(1, 7, 13)))
			{
				return ConstConfig::HU_TYPE_FENGDING_TYPE_INVALID ;
			}
			$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

			if(!isset(ConstConfig::$hu_data[$key]))
			{
				return ConstConfig::HU_TYPE_FENGDING_TYPE_INVALID ;
			}
			else
			{
				$hu_list_val = ConstConfig::$hu_data[$key];

				//一条龙
				if($this->m_rule->is_yitiaolong_fan && ($hu_list_val & 256) == 256)
				{
					$is_yitiaolong = true;
				}

				//将一色
				if(($hu_list_val & 4) == 4 )
				{
					$jiangyise_arr[] = 1;
				}
				else
				{
					$jiangyise_arr[] = 0;
				}

				//258做将牌
				if($this->m_rule->is_258_jiang && !$is_258_jiang && ($hu_list_val & 512) == 512 )
				{
					$is_258_jiang = true;
				}

				//七对
				$qidui_arr[] = $hu_list_val & 64;
				//对对胡
				$pengpeng_arr[] = $hu_list_val & 8;

				//32牌型
				if($hu_list_val & 1 == 1)
				{
					$jiang_arr[] = $hu_list_val & 32;
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
			$stand_pai_type = $this->get_card_type($this->m_sStandCard[$chair]->first_card[$i]);

			$qidui_arr[] = 0;
			$qing_arr[] = $stand_pai_type;

			//碰碰胡和将一色
			if(ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i])
			{
				$pengpeng_arr[] = 0;
				//有顺子则将一色不成立
				$jiangyise_arr[] = 0;
				continue ;
			}

			//将一色
			if(ConstConfig::PAI_TYPE_FENG == $stand_pai_type  )
			{
				$jiangyise_arr[] = 0;
			}
			elseif(!in_array($this->m_sStandCard[$chair]->card[$i]%16,array(2,5,8))  )
			{
				$jiangyise_arr[] = 0;
			}
		}

		//记录根到全局数据
		$bType32 = (32 == array_sum($jiang_arr));
		$bQiDui = !array_keys($qidui_arr, 0); 
		$bPengPeng = !array_keys($pengpeng_arr, 0);  
		$bJiangYiSe= !array_keys($jiangyise_arr, 0);  

		/////////////////////////////附加 番型的处理/////////////////////////////////
		//清一色结果
		if($this->m_rule->is_qingyise_fan)
		{
			$bQing = ( 1 == count(array_unique($qing_arr)) && ConstConfig::PAI_TYPE_FENG != $qing_arr[0]);
			if($bQing)
			{
				$is_qingyise = true;
			}
		}

		//混一色
		if($this->m_rule->is_hunyise_fan)
		{
            $bHunYiSe = (2 == count(array_unique($qing_arr)) && (in_array(ConstConfig::PAI_TYPE_FENG,$qing_arr)));
            if($bHunYiSe)
            {
                $is_hunyise = true;
            }
		}

		//将一色
		if($bJiangYiSe)
		{
			$is_jiangyise = true;
		}

		//大吊车
		if($this->m_rule->is_dadiaoche_fan && 4 == $this->m_sStandCard[$chair]->num)
		{
			$is_dadiaoche = true;
		}

		//一条龙处理结果$is_yitiaolong = true;
		//打八张判断
		if(!empty($this->m_rule->is_da8zhang))
		{
			$is_da8zhang = $this->_judge_da8zhang($chair,null,false,true);
		}

		///////////////////////基本牌型的处理///////////////////////////////

		//七对牌型和32牌型
		if(!$bType32 && !$bQiDui )	//不是32牌型也不是7对 也不是碰碰胡
		{
			return ConstConfig::HU_TYPE_FENGDING_TYPE_INVALID ;
		}
		elseif($bQiDui)				//判断七对，可能同时是32牌型
		{			
			if($this->m_rule->is_qidui_fan)
			{
				return ConstConfig::HU_TYPE_QIDUI ;			//七对							
			}
			else
			{
				return ConstConfig::HU_TYPE_PINGHU ;
			}
		}
		else
		{
			if(!$is_258_jiang && $this->m_rule->is_258_jiang)
			{
				return ConstConfig::HU_TYPE_FENGDING_TYPE_INVALID ;
			}

			if($bPengPeng)
			{
				return ConstConfig::HU_TYPE_PENGPENGHU;
			}
			return ConstConfig::HU_TYPE_PINGHU ;
		}

		return ConstConfig::HU_TYPE_FENGDING_TYPE_INVALID ;
	}
	
	//判断基本牌型+附加牌型+庄分
	public function judge_fan($chair)
	{
		$fan_sum = 0;
		$hu_type = $this->m_HuCurt[$chair]->method[0];
		if($hu_type == ConstConfig::HU_TYPE_FENGDING_TYPE_INVALID )
		{
			return 0;
		}
	
		$tmp_hu_desc = '';
		if(isset(ConstConfig::$hu_type_arr[$hu_type]))
		{
			$fan_sum = ConstConfig::$hu_type_arr[$hu_type][1];
			$tmp_hu_desc = ConstConfig::$hu_type_arr[$hu_type][2].' ';
		}

		$double_fan=1;//定义翻倍

		for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
		{
			if(isset(ConstConfig::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
			{
				if($this->m_HuCurt[$chair]->method[$i] == ConstConfig::ATTACHED_HU_HAIDILAOYUE)
				{
                    $double_fan = ConstConfig::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
					$tmp_hu_desc .= ConstConfig::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
				}
                else
                {
                    $fan_sum += ConstConfig::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
                    $tmp_hu_desc .= ConstConfig::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
                }

			}
		}

		$fan_sum = $fan_sum * $double_fan;

		$this->m_bMaxFan[$chair] = false;
		if ($fan_sum > $this->m_rule->top_fan)
		{
			$fan_sum = $this->m_rule->top_fan;
			$this->m_bMaxFan[$chair] = true;
		}
		
		if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		{
			$tmp_hu_desc = '自摸胡'.'('.$tmp_hu_desc;
		}
		else 
		{
			$tmp_hu_desc = '接炮胡'.'('.$tmp_hu_desc;
		}
		$tmp_hu_desc .= ') ';
		//if(!$this->m_hu_desc[$chair])
		//{
			$this->m_hu_desc[$chair] = $tmp_hu_desc;
		//}

		return $fan_sum;
	}

	//插入牌  ok
	public function list_insert($chair, $card)
	{
		$card_type = $this->get_card_type($card);
		if($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			echo("错误牌类型，list_insert".__LINE__);
			return false;
		}
		$card_key = $card%16;
		if($this->m_sPlayer[$chair]->card[$card_type][$card_key] < 4)
		{
			$this->m_sPlayer[$chair]->card[$card_type][$card_key] += 1;
			$this->m_sPlayer[$chair]->card[$card_type][0] += 1;
			$this->m_sPlayer[$chair]->len += 1;
			return true;
		}
		return false;
	}

	//删除牌  ok
	public function list_delete($chair, $card)
	{
		$card_type = $this->get_card_type($card);
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

	// 查找牌，返回个数  ok
	public function list_find($chair, $card)
	{
		$card_type = $this->get_card_type($card);
		if($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}
		$card_key = $card%16;
		return $this->m_sPlayer[$chair]->card[$card_type][$card_key];
	}

	//返回牌的类型  ok
	public function get_card_type($card)
	{
		if($card <= 9 && $card >= 1)	return ConstConfig::PAI_TYPE_WAN;
		if($card <= 25 && $card >= 17)	return ConstConfig::PAI_TYPE_TIAO;
		if($card <= 41 && $card >= 33)	return ConstConfig::PAI_TYPE_TONG;
		if($card <= 55 && $card >= 49)	return ConstConfig::PAI_TYPE_FENG;
		if($card <= 72 && $card >= 65)	return ConstConfig::PAI_TYPE_DRAGON;
		return ConstConfig::PAI_TYPE_PAI_TYPE_INVALID;
	}

	//  牌index  ok
	public function get_card_index($type, $key)
	{
		//四川麻将没有风牌和花牌
		if($type >=ConstConfig::PAI_TYPE_WAN  && $type <=ConstConfig::PAI_TYPE_DRAGON && $key >=1 && $key <=9)
		{
			return $type * 16 + $key;
		}
		return 0;
	}

	//取消选择buf
	public function ClearChooseBuf($chair, $ClearGang=true)
	{
		if($ClearGang)
		{
			$this->m_sQiangGang->clear();
		}
		$this->m_bChooseBuf[$chair] = 0;
	}

	//判断有没有吃
	public function find_eat($chair,$num)
	{
		if($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
		{
			return false;
		}
		
		$card_type = $this->get_card_type($this->m_sOutedCard->card);
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

		$card_count_first_tmp = $this->list_find($chair, $eat_card_first_tmp);
		$card_count_second_tmp = $this->list_find($chair, $eat_card_second_tmp);

		if (  $card_count_first_tmp >= 1 && $card_count_second_tmp >= 1 )
		{
			return true;
		}

		return false;
	}

	//判断有没有碰  ok 
	public function find_peng($chair)
	{
		if($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
		{
			return false;
		}
		
		$card_type = $this->get_card_type($this->m_sOutedCard->card);
		if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
		{
			return false;
		}

		$card_count = $this->list_find($chair, $this->m_sOutedCard->card);

		if (  $card_count == 2 || $card_count == 3 )
		{
			return true;
		}

		return false;
	}

	// 判断有没有别人打来的明杠  ok
	public function find_zhi_gang($chair)
	{
		if($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
		{
			return false;
		}
		
		$card_type = $this->get_card_type($this->m_sOutedCard->card);
		if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
		{
			return false;
		}

		$card_count = $this->list_find($chair, $this->m_sOutedCard->card);
		if($card_count == 3)
		{
			return true;
		}
		return false;
	}

	//找出第14张牌  ok
	public function find_14_card($chair)
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
				$fouteen_card = $this->get_card_index($last_type, $i);
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

	//------------------------------------- 命令处理函数 -----------------------------------

	//处理碰  ok
	public function HandleChoosePeng($chair)
	{
		$temp_card = $this->m_sOutedCard->card;
		$card_type = $this->get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			echo("uuuuuuuuuuuuuuuuuuuuuu".__LINE__);
			return false;
		}

		if(($this->list_find($chair, $temp_card)) >= 2)
		{
			$this->list_delete($chair, $temp_card);
			$this->list_delete($chair, $temp_card);
		}
		else
		{
			echo "error asdff".__LINE__;
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
		$car_14 = $this->find_14_card($chair);
		if(!$car_14)
		{
			echo "error dddf".__LINE__;
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

	//处理吃牌  ok
	public function HandleChooseEat($chair,$eat_num)
	{
		$temp_card = $this->m_sOutedCard->card;
		$card_type = $this->get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID || $card_type == ConstConfig::PAI_TYPE_FENG  || $card_type == ConstConfig::PAI_TYPE_DRAGON )
		{
			echo("uuuuuuuuuuuuuuuuuuuuuu".__LINE__);
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

		if($this->get_card_type($eat_card_first_tmp) != $card_type)
		{
			echo("uuuuuuuuuuuuuuuuuuuuuu".__LINE__);
			return false;
		}
		else
		{
			$this->list_delete($chair, $del_card_second_tmp);
			$this->list_delete($chair, $del_card_third_tmp);
		}
	
		// 设置倒牌
		$stand_count = $this->m_sStandCard[$chair]->num;
		$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_SHUN;
		$this->m_sStandCard[$chair]->first_card[$stand_count] = $eat_card_first_tmp;
		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
		$this->m_sStandCard[$chair]->num ++;

		// 找出第14张牌
		$car_14 = $this->find_14_card($chair);
		if(!$car_14)
		{
			echo "error dddf".__LINE__;
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

	//处理暗杠  ok
	public function HandleChooseAnGang($chair, $gang_card)
	{
		$temp_card = $gang_card;
		$card_type = $this->get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}

		$this->list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now);
		$this->m_sPlayer[$chair]->card_taken_now = 0;

		$this->list_delete($chair, $temp_card);
		$this->list_delete($chair, $temp_card);
		$this->list_delete($chair, $temp_card);
		$this->list_delete($chair, $temp_card);

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
		$this->m_wGFXYScore = [0,0,0,0];
		for ($i=0; $i<$this->m_rule->player_count; ++$i)
		{
			if ($i == $chair)
			{
				continue;
			}

			if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
			{
				$nGangScore = ConstConfig::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;

				$this->m_wGFXYScore[$i] = -$nGangScore;			//扣本次刮风下雨分
				$this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分

				$this->m_wGFXYScore[$chair] += $nGangScore;				//赢本次刮风下雨分
				$this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分

				$this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

				$nGangPao += $nGangScore;
			}
		}

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

	//处理直杠  ok
	public function HandleChooseZhiGang($chair)
	{
		$temp_card = $this->m_sOutedCard->card;
		$card_type = $this->get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}

		$this->list_delete($chair, $temp_card);
		$this->list_delete($chair, $temp_card);
		$this->list_delete($chair, $temp_card);

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

		$nGangScore = 0;
		$nGangPao = 0;
		$this->m_wGFXYScore = [0,0,0,0];
		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
			if ($i == $chair)
			{
				continue;
			}

			if ($stand_count_after > 0 && $i == $this->m_sStandCard[$chair]->who_give_me[$stand_count_after-1])
			{
				$nGangScore =ConstConfig::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;

				$this->m_wGFXYScore[$i] = -$nGangScore;
				$this->m_wGangScore[$i][$i] -= $nGangScore;

				$this->m_wGFXYScore[$chair] += $nGangScore;
				$this->m_wGangScore[$chair][$chair] += $nGangScore;

				$this->m_wGangScore[$chair][$i] += $nGangScore;

				$nGangPao += $nGangScore;
			}
		}

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

	//处理弯杠  ok
	public function HandleChooseWanGang($chair, $gane_card)
	{
		$temp_card = $gane_card;
		$card_type = $this->get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}		

		$card_type_taken_now = $this->get_card_type($this->m_sPlayer[$chair]->card_taken_now);
		if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type_taken_now)
		{
			echo("错误的牌类型".__LINE__);
			return false;
		}

		// 改变手持牌，弯杠牌是第14张牌
		if ($this->m_sPlayer[$chair]->card_taken_now == $temp_card)
		{
			$this->m_sPlayer[$chair]->card_taken_now = 0;
		}
		else //弯杠牌在手持牌中
		{
			$this->list_delete($chair, $temp_card);
			$this->list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now);
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
			$next_chair = $this->AntiClock($next_chair);

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

	//处理自摸  ok
	public function HandleHuZiMo($chair)			//处理自摸
	{
		$temp_card = $this->m_sPlayer[$chair]->card_taken_now;
		$card_type = $this->get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			echo("hu_zi_mo_error".__LINE__);
			return false;
		}

		$this->list_insert($chair, $temp_card);
		$this->m_HuCurt[$chair]->state = ConstConfig::WIN_STATUS_ZI_MO;
		$this->m_HuCurt[$chair]->card = $temp_card;

		$bHu = $this->judge_hu($chair);
		
		$this->list_delete($chair, $temp_card);

		if(!$bHu) //诈胡
		{
			echo("有人诈胡".__LINE__);
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

				if ($this->m_game_type == Config::GAME_TYPE)
				{
					$this->m_bChairHu[$chair] = true;
					$this->m_bChairHu_order[] = $chair;
					$this->m_nCountHu++;
					$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;

					//$this->list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now); //整理完毕
					
					//去除胡牌者 card_taken_now  这个牌就只有在 m_HuCurt 有
					$this->m_sPlayer[$chair]->card_taken_now = 0;

					if(255 == $this->m_nChairBankerNext)	//下一局庄家
					{
					    if ($chair == $this->m_nChairBanker)
					    {
                            $this->m_nChairBankerNext = $this->AntiClock($this->m_nChairBanker, 0);
                        }
						else
                        {
                            $this->m_nChairBankerNext = $this->AntiClock($this->m_nChairBanker, 1);
                        }


					}
					
					$this->m_nEndReason = ConstConfig::END_REASON_HU;
					$this->HandleSetOver();

					//发消息
					$this->_send_act($this->m_currentCmd, $chair);

					return true;
				}
			}
			else	//番数不够，判诈胡，一般进不来
			{
				echo("有人诈胡".__LINE__);
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

	//处理出牌  ok
	public function HandleOutCard($chair, $is_14 = false, $out_card = 0)		
	{
		//一旦有人出牌，表示上一轮竞争已经结束, 可以清CMD
		$this->m_chairSendCmd = 255;							// 当前发命令的玩家
		$this->m_currentCmd = 0;							// 当前的命令
		$this->m_eat_num = 0;

		// 更新桌面牌
		if($this->m_sOutedCard->card)
		{
			//echo("出牌没更新".__LINE__);
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
			if(!$this->list_delete($chair,$this->m_sOutedCard->card))
			{
				echo "出牌错误".__LINE__;
				return false;
			}

			$card_type = $this->get_card_type($this->m_sPlayer[$chair]->card_taken_now);
			if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
			{
				echo "出牌错误".__LINE__;
				return false;
			}
			$this->list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now); //整理完毕
			$this->m_sPlayer[$chair]->card_taken_now = 0;
		}

		$this->m_sPlayer[$chair]->seen_out_card = 1;
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

		$this->_send_act('c_out_card', $chair, $this->m_sOutedCard->card);

		$this->handle_flee_play(true);	//更新断线用户
		for ( $i=0; $i<$this->m_rule->player_count - 1; $i++)
		{
			$chair_next = $this->AntiClock($chair_next);
			if ($this->m_sPlayer[$chair_next]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU )
			{
				continue;
			}
			if ($chair_next == $chair)
			{
				continue;
			}
			$this->m_sPlayer[$chair_next]->seen_out_card = 0;
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
				$next_chair = $this->AntiClock($next_chair);
			} while ($this->m_bChairHu[$next_chair]);

			//			if ($next_chair == $chair)
			//			{
			//				echo("find unHu player error, chair".__LINE__);
			//				return false;
			//			}

			$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
			$this->m_chairCurrentPlayer = $next_chair;
			$this->m_sGangPao->clear();

			if(!$this->DealCard($next_chair))
			{
				echo(" return false in ".__LINE__);
				return false;
			}
			if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
			{
				echo("end reason no card".__LINE__);
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
			$this->m_nHuGiveUp[$this->m_chairSendCmd] = 0;
		}

		$temp_card=0;
		$card_type = ConstConfig::PAI_TYPE_PAI_TYPE_INVALID;

		//抉择后全部可见
		for($i = 0; $i< $this->m_rule->player_count; $i ++)
		{
			$this->m_sPlayer[$i]->seen_out_card = 1;
		}

		if ($this->m_sQiangGang->mark )	// 处理抢杠
		{
			$temp_card = $this->m_sQiangGang->card;
			$card_type = $this->get_card_type($temp_card);
			if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
			{
				echo("错误的牌类型，发生在-> 抢杠".__LINE__);
				return false;
			}

			$bHaveHu = false;

			//截胡
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
						$tmp_distance = $this->ChairTo($this->m_chairCurrentPlayer, $this->m_chairHu[$i]);
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
					if($this->m_sOutedCard->card == $this->m_hun_card)
					{
						$is_fanhun = true;
					}
					$this->list_insert($hu_chair, $temp_card);
					$this->m_HuCurt[$hu_chair]->state = ConstConfig::WIN_STATUS_CHI_PAO;   //抢杠算作吃炮
					$this->m_nChairDianPao = $this->m_sQiangGang->chair;
					$this->m_HuCurt[$hu_chair]->card = $temp_card;
					$bHu = $this->judge_hu($hu_chair,$is_fanhun);
					$this->list_delete($hu_chair, $temp_card);

					if(!$bHu)
					{
						echo("有人诈胡 at".__LINE__);
						$this->HandleZhaHu($hu_chair);
						$this->m_HuCurt[$hu_chair]->clear();
					}
					else
					{
						if($this->ScoreOneHuCal($hu_chair, $this->m_sQiangGang->chair))
						{
							$bHaveHu = true;

							if($this->m_HuCurt[$hu_chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
							{
								$this->m_wTotalScore[$hu_chair]->n_jiepao += 1;
								$this->m_wTotalScore[$this->m_nChairDianPao]->n_dianpao += 1;
							}
							if ($this->m_game_type == Config::GAME_TYPE)
							{
								$this->m_bChairHu[$hu_chair] = true;
								$this->m_bChairHu_order[] = $hu_chair;
								$this->m_nCountHu++;
								$this->m_sPlayer[$hu_chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;
							}

							$this->_send_act($this->m_currentCmd, $hu_chair);
							if(255 == $this->m_nChairBankerNext)	//下一局庄家
							{
							    if ($hu_chair == $this->m_nChairBanker)
                                {
                                    $this->m_nChairBankerNext = $hu_chair;
                                }else
                                {
                                    $this->m_nChairBankerNext = $this->AntiClock($this->m_nChairBanker, 1);
                                }

							}
						}
						else
						{
							//$bHaveHu = false;
							$this->HandleZhaHu($hu_chair);
							$this->m_HuCurt[$hu_chair]->clear();
						}
					}
				}
			}
			
			$this->m_sGangPao->clear();
			
			if($bHaveHu) //抢杠胡,处理原来的杠
			{
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

				if ($this->m_game_type == Config::GAME_TYPE)
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
					$nGangScore = ConstConfig::M_WANGANG_SCORE *ConstConfig::SCORE_BASE;

					$this->m_wGFXYScore[$i] = -$nGangScore;
					$this->m_wGangScore[$i][$i] -= $nGangScore;

					$this->m_wGFXYScore[$this->m_sQiangGang->chair] += $nGangScore;
					$this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
					$this->m_wGangScore[$this->m_sQiangGang->chair][$i] += $nGangScore;
					
					$nGangPao += $nGangScore;
				}

				$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
				$this->m_chairCurrentPlayer = $this->m_sQiangGang->chair;

				$this->m_bHaveGang = true;					//for 杠上花
				$this->m_sGangPao->init_data(true, $this->m_sQiangGang->card, $this->m_sQiangGang->chair, ConstConfig::DAO_PAI_TYPE_WANGANG, $nGangPao);

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

			$temp_card = $this->m_sOutedCard->card;
			$card_type = $this->get_card_type($temp_card);
			if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
			{
				echo("错误的牌类型，发生在".__LINE__);
				return false;
			}

			//截胡
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
						$tmp_distance = $this->ChairTo($this->m_chairCurrentPlayer, $this->m_chairHu[$i]);
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
					if($this->m_sOutedCard->card == $this->m_hun_card)
					{
						$is_fanhun = true;
					}					
					$this->list_insert($hu_chair, $temp_card);
					$this->m_HuCurt[$hu_chair]->state = ConstConfig::WIN_STATUS_CHI_PAO;   //抢杠算作吃炮
					$this->m_nChairDianPao = $this->m_sOutedCard->chair;
					$this->m_HuCurt[$hu_chair]->card = $temp_card;
					$bHu = $this->judge_hu($hu_chair,$is_fanhun);
					$this->list_delete($hu_chair, $temp_card);

					if(!$bHu)
					{
						echo("有人诈胡 at".__LINE__);
						$this->HandleZhaHu($hu_chair);
						$this->m_HuCurt[$hu_chair]->clear();
					}
					else
					{
						if($this->ScoreOneHuCal($hu_chair, $this->m_sOutedCard->chair))
						{
							$bHaveHu = true;

							if($this->m_HuCurt[$hu_chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
							{
								$this->m_wTotalScore[$hu_chair]->n_jiepao += 1;
								$this->m_wTotalScore[$this->m_nChairDianPao]->n_dianpao += 1;
							}
							if ($this->m_game_type == Config::GAME_TYPE)
							{
								$this->m_bChairHu[$hu_chair] = true;
								$this->m_bChairHu_order[] = $hu_chair;
								$this->m_nCountHu++;
								$this->m_sPlayer[$hu_chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;
							}

							$this->_send_act($this->m_currentCmd, $hu_chair);
                            if(255 == $this->m_nChairBankerNext)	//下一局庄家
                            {
                                if ($hu_chair == $this->m_nChairBanker)
                                {
                                    $this->m_nChairBankerNext = $hu_chair;
                                }else
                                {
                                    $this->m_nChairBankerNext = $this->AntiClock($this->m_nChairBanker, 1);
                                }

                            }
						}
						else
						{
							//$bHaveHu = false;
							$this->HandleZhaHu($hu_chair);
							$this->m_HuCurt[$hu_chair]->clear();
						}
					}
				}
			}
			
			$this->m_sGangPao->clear();
			
			if($bHaveHu)
			{
				//$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
				$this->m_currentCmd = 'c_hu';

				//置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
				--$this->m_nNumTableCards[$this->m_sOutedCard->chair];
				if($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
				{
					unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
				}

				if ($this->m_game_type == Config::GAME_TYPE)
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
				$next_chair = $this->AntiClock($next_chair);

				if ($next_chair == $this->m_chairCurrentPlayer)
				{
					echo("find unHu player error, chair".__LINE__);
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

	//--------------------------------------------------------------------


	public function HandleZhaHu($chair)
	{
		//以后另做处理，客户端诈胡等于作弊
		$this->m_nNumCheat[$chair]++;
		//$this->m_bChooseBuf[$chair] = 0; //clear the hu signal
	}

	public function game_to_playing()
	{
		//状态设定
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD ;
		$this->m_chairCurrentPlayer = $this->m_nChairBanker;

		$this->m_sPlayer[$this->m_nChairBanker]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
		$this->m_bChooseBuf[$this->m_nChairBanker] = 1;
		
		//处理庄家的14牌
		// if($this->m_sPlayer[$this->m_nChairBanker]->card_taken_now)
		// {
		// 	$this->list_insert($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
		// 	$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->find_14_card($this->m_nChairBanker);
		// }

		//状态变化发消息
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}
		$this->handle_flee_play(true);	//更新断线用户		
	}

	//掷骰定庄家
	public function on_table_status_to_playing()
	{
		if(Config::IS_ROOM_OWNER_PAY)
		{
			$this->m_nChairBanker = 0;			
		}
		else
		{
			$this->m_nChairBanker = mt_rand(0, ($this->m_rule->player_count-1));			
		}
		return;
	}

	//取得所有玩家数据
	public function OnGetChairScene($chair, $is_more=false)
	{
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_INVALID)
		{
			echo("sysPhase invalid,".__LINE__."\n");
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

			//$data['m_fan_hun_card'] = $this->m_fan_hun_card;		
			//$data['m_hun_card'] = $this->m_hun_card;		
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
		return true;
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
			echo 'error InitData'.__LINE__;
			return false;
		}
		if($is_open || ($this->m_rule->set_num <= $this->m_nSetCount && $this->m_bLastGameOver))
		{
			$this->m_game_type = Config::GAME_TYPE;	//游戏 1 四川血战到底 2 陕西麻将(陕北麻将)
			$this->m_room_state = ConstConfig::ROOM_STATE_OVER ;	//房间状态
			$this->m_room_id = 0;	//房间号
			$this->m_room_owner = 0;	//房主
			$this->m_room_players = array();	//玩家信息
			$this->m_start_time = 0;	//开始时间
			$this->m_end_time = time();	//结束时间

			$this->m_nSetCount = 0;
			$this->on_table_status_to_playing();
			
			$this->m_sPlayer = array();
			$this->m_ready = array(0,0,0,0);			
			for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
			{
				$this->m_wTotalScore[$i] = new TotalScore();
			}
		}

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
        $this->m_bNoCard = 0;   //  荒庄

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
			$this->m_wGFXYScore[$i] = 0;
			$this->m_wFollowScore[$i] = 0;	//跟庄庄家分数结构
			$this->m_Score[$i] = new Score();
			//$this->m_own_paozi[$i] = new Pao_zi();
			//$this->m_paozi_score[$i] = 0;


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
			
			//$this->m_nHuList[$i] = 0;
		}
	}

	public function DealCard($chair)
	{
		if ($this->m_game_type == Config::GAME_TYPE && $this->m_bChairHu[$chair])	//未胡玩家发牌
		{
			if ($this->m_nCountHu >= 1)
			{
				return false;
			}
			$this->m_chairCurrentPlayer = $this->AntiClock($chair);
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
			//echo("没牌啦".__LINE__);
			$this->m_nEndReason = ConstConfig::END_REASON_NOCARD;
            $this->m_bNoCard = 1;
			$this->HandleSetOver();
			return true;
		}

		$the_card = $this->m_nCardBuf[$this->m_nCountAllot];
		$this->m_nCountAllot++;

		$this->m_sPlayer[$chair]->card_taken_now = $the_card;

		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
		$this->m_bChooseBuf[$chair] = 1;

		$this->m_nHuGiveUp[$chair] = 0;	//重置过手胡

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
			echo(__LINE__."Unknow end reason: ".$this->m_nEndReason);
		}

		//下一局庄家
		if($this->m_nEndReason==ConstConfig::END_REASON_NOCARD && 255 == $this->m_nChairBankerNext)
		{
			$this->m_nChairBankerNext = $this->AntiClock($this->m_nChairBanker, 0);
		}

		//准备状态
		$this->m_ready = array(0,0,0,0);

		//本局结束时间
		$this->m_end_time = date('Y-m-d H:i:s', time());

		//写记录
		$this->WriteScore();

		$this->set_game_and_checkout();		
		
		//最后一局结束时候修改房间状态
		if(empty($this->m_rule) || $this->m_rule->set_num <= $this->m_nSetCount)
		{
		    if ($this->m_bNoCard == 0)
		    {
                $this->m_room_state = ConstConfig::ROOM_STATE_OVER;
                $this->m_bLastGameOver = 1;
            }
            else
            {
                //$this->m_bLastGameOver = 0;
                $this->m_room_state = ConstConfig::ROOM_STATE_OVER;
                $this->m_bLastGameOver = 1;

                //更新最后一局荒庄继续打牌需要将现有的代码注释,打开注释代码
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
	}

	///////////////////////得分处理///////////////////////////
	//每局个人  +=赢的分  +=输的分  +=庄家 的分  一共4局 
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

				if ($this->m_game_type== Config::GAME_TYPE && $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
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

				// $banker_fan = 0;	//庄家分
				// if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
				// {
				// 	$banker_fan = 2;
				// }

                //庄家分数翻倍
                $banker_fan = 1;
                if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
                {
                    $banker_fan = 2;
                }

				$PerWinScore = ($PerWinScore == 0)? 1 : ($PerWinScore * $banker_fan);
				$wWinScore = 0;
				$wWinScore += $PerWinScore ;  //赢的分 加  庄家的分

				
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
		// 吃炮者算分在此处！！
		else if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
		{
			$chairBaoPai = 255;
				
			/*$banker_fan = 0;	//庄家翻倍
			if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
			{
				$banker_fan = 2;
			}*/

            //庄家分数翻倍
            if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
            {
                $PerWinScore = 2 * $PerWinScore;
            }
			
			$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;	
			$wWinScore = 0;
			$wWinScore += $PerWinScore  ;

			$this->m_wHuScore[$lost_chair] -= $wWinScore;
			$this->m_wHuScore[$chair] += $wWinScore;

			$this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
			$this->m_wSetScore[$chair] += $wWinScore;

			$this->m_HuCurt[$chair]->gain_chair[0] = 1;
			$this->m_HuCurt[$chair]->gain_chair[1]=$lost_chair;


			return true;
		}

		echo("此人没有胡".__LINE__);
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
			$this->m_Score[$i]->score = $this->m_wSetScore[$i]+ $this->m_wSetLoseScore[$i]+ $this->m_wGangScore[$i][$i];
			$this->m_Score[$i]->set_count = $this->m_nSetCount;
			if ($this->m_Score[$i]->score > 0)
			{
				$this->m_Score[$i]->win_count = 1;
			}
			else
			{
				$this->m_Score[$i]->lose_count = 1;
			}
		}
	}

	///荒庄结算
	public function CalcNoCardScore()
	{
		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$this->m_Score[$i]->clear();
		}

		if ($this->m_game_type != Config::GAME_TYPE)
		{
			echo("error m_game_type".__LINE__);
			return false;
		}

		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$this->m_wGangScore[$i][$i] = 0; 
			//$this->m_wFollowScore[$i] = 0;
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
			
		}
	}

	//洗牌
	public function WashCard()
	{
		if($this->m_rule->is_feng)
		{
			$this->m_nCardBuf = ConstConfig::ALL_CARD_136;
			$this->m_nAllCardNum = 136;
		}
		else 
		{
			$this->m_nCardBuf = ConstConfig::ALL_CARD_108;
			$this->m_nAllCardNum = 108;
		}

		if(Config::WASHCARD)
		{
			shuffle($this->m_nCardBuf); shuffle($this->m_nCardBuf);	//为了测试 不洗牌
		}
	}

	//发牌
	public function DealAllCardEx()
	{
		$temp_card = 255;
		$this->WashCard();

		//给每人发13张牌
		for($i=0; $i<$this->m_rule->player_count ; $i++)
		{
			for($k=0; $k<ConstConfig::BASE_HOLD_CARD_NUM; $k++)
			{
				$temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];	//从牌缓冲区里那张牌
				$card_type = $this->get_card_type($temp_card);
				$card_key = $temp_card%16;
				if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID != $card_type)
				{
					$this->m_sPlayer[$i]->card[$card_type][0] ++;
					$this->m_sPlayer[$i]->card[$card_type][$card_key] += 1;
				}
				else
				{
					echo("发牌错误，出现未定义类型的牌".__LINE__);
				}
			}
			$this->m_sPlayer[$i]->len = 13;
		}

		//给庄家发第14张牌
		$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->m_nCardBuf[$this->m_nCountAllot++];
		$this->list_insert($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
		$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->find_14_card($this->m_nChairBanker);

		//订 翻混牌
		if(!empty($this->m_rule->is_fanhun))
		{
			$this->m_fan_hun_card = $this->m_nCardBuf[$this->m_nCountAllot++];
			$temp_type = $this->get_card_type($this->m_fan_hun_card);
			$temp_card_index = $this->m_fan_hun_card%16;

			if($temp_type == ConstConfig::PAI_TYPE_WAN || $temp_type == ConstConfig::PAI_TYPE_TIAO || $temp_type ==ConstConfig::PAI_TYPE_TONG )
			{
				$tmp_index_array = array(0,2,3,4,5,6,7,8,9,1);
				$this->m_hun_card = $this->get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index
			}
			elseif($temp_type == ConstConfig::PAI_TYPE_FENG || $temp_type == ConstConfig::PAI_TYPE_DRAGON)
			{
				$feng_array = array(0,2,3,4,1,6,7,5);
				$this->m_hun_card =$this->get_card_index($temp_type,$feng_array[$temp_card_index]);//风牌的翻混index
			}
			else
			{
				echo("混牌错误，出现未定义类型的牌".__LINE__);
				return false;
			}
		}

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
        $m_bNoCard_tmp = $this->m_bNoCard;

		//初始化数据，非首局的时候还要相关处理
		$this->InitData();
		$this->m_nSetCount += 1;		
				
		//下炮子
		/*if($this->m_rule->is_paozi)
		{
			$this->start_pao_zi();
			return true;
		}*/
		//处理荒庄局数不变
        if (!empty($m_bNoCard_tmp))
        {
            $this->m_nSetCount -= 1;
        }

        //发牌
		$this->DealAllCardEx();
		$this->game_to_playing();

		return true;
	}


	/******/
	/*其他*/
	/******/

	//玩家i相对于玩家j的位置,如(0,3),返回1(即下家)
	public function ChairTo($i, $j)
	{
		return ($j-$i+$this->m_rule->player_count)%$this->m_rule->player_count;
	}

	//返回chair逆时针转 n 的玩家
	public function AntiClock($chair, $n = 1)
	{
		return ($chair + $this->m_rule->player_count + $n)%$this->m_rule->player_count;
	}

	private function _send_act($cmd, $chair, $card=0)
	{
		$cmd = new Game_cmd($this->m_room_id, 's_act', array('cmd'=>$cmd, 'chair'=>$chair, 'card'=>$card), Game_cmd::SCO_ALL_PLAYER );
		$cmd->send($this->serv);
		unset($cmd);
	}
	
	private function _cancle_game()
	{
		$cancle_count = 0;
		$yes_count = 0;
		$is_cancle = 0;
		
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
			if($yes_count >= $this->m_rule->player_count - 1)
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

			$this->set_game_and_checkout($is_log);

			$this->clear();
		}

		return $is_cancle;
	}

	//倒牌某类牌型的个数
	private function _stand_type_count($chair,$card_type)
	{
		$card_num = 0;

		if( $this->m_sStandCard[$chair]->num > 0)//有倒牌
		{
			for($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
			{

				if($this->get_card_type($this->m_sStandCard[$chair]->card[$i])  == $card_type)
				{
					if(1 == $this->m_sStandCard[$chair]->type[$i] || 2 == $this->m_sStandCard[$chair]->type[$i])
					{
						 $card_num += 3 ;
					}
					elseif(3 == $this->m_sStandCard[$chair]->type[$i] || 4 == $this->m_sStandCard[$chair]->type[$i] || 5 == $this->m_sStandCard[$chair]->type[$i])
					{
						$card_num += 4;
					}
				}
			}			
		}
		return $card_num;
	}

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
				//状态变化发消息
				$this->_send_act('c_follow', 0 ,$this->m_sFollowCard->follow_card);
				$this->m_sFollowCard->clear();//更新跟庄标记 status=1,
			}
		}
	}

	private function _judge_da8zhang($chair, $replace_fanhun , $is_fanhun = false, $rule_no_fanhun = false)
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
				$da8zhang_fanhun_num = $this->list_find($chair, $this->m_hun_card);	//手牌翻混个数
				$da8zhang_fanhun_type = $this->get_card_type($this->m_hun_card);        //翻混牌类型		
			
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
					if(($this->m_sPlayer[$chair]->card[$k][0] + $tmp_stand_num - $da8zhang_fanhun_num + $replace_fanhun[$k]) >= 8 && $k == $da8zhang_fanhun_type)
					{
						$is_da8zhang = true;
						break;
					}
					elseif(($this->m_sPlayer[$chair]->card[$k][0] + $tmp_stand_num + $replace_fanhun[$k]) >= 8 && $k != $da8zhang_fanhun_type)
					{
						$is_da8zhang = true;
						break;
					}
				}
			}
		}

		return $is_da8zhang;										
	}


}
