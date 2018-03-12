<?php
/**
 * @author xuqiang76@163.com
 * @final 20161025
 */

namespace gf\inc;

use gf\inc\ConstConfigSouth;
use gf\conf\Config;
use gf\inc\Room;
use gf\inc\BaseFunction;
use gf\inc\Game_cmd;

class GameSouth
{
	public $serv;	//socket服务器对象

	public $m_ready = array(0,0,0,0);	//用户准备
	public $m_game_type;	//游戏 1 血战到底 2 陕西麻将
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
	public $m_nCountHuaZhu;	//花猪个数
	public $m_nCountDajiao;	//大叫个数
	public $m_bChairHu = array();		// 血战已胡玩家
	public $m_bChairHu_order = array();		// 血战已胡玩家顺序
	public $m_nGen = array();	//根数组
	public $m_only_out_card = array();		// 玩家只能出牌不能碰杠胡

	public $m_bTianRenHu;							///以判断地天人胡
	public $m_nDiHu = array();					// 判断地胡

	public $m_nEndReason;					// 游戏结束原因

	public $m_sQiangGang;			// 抢杠结构
	public $m_sGangPao;				// 杠炮结构
	public $m_bHaveGang;                   // 是否有杠开
	public $m_huan_3_type;	//换三张方式

	//记分，以后处理
	public $m_wHuaZhuScore = array();				// 花猪分数
	public $m_wDaJiaoScore = array();				// 大叫分数
	public $m_wGangScore = array();			// 刮风下雨总分数
	public $m_wGFXYScore = array();				//刮风下雨临时分数
	public $m_wHuScore = array();					// 本剧胡整合分数
	public $m_wSetScore = array();				// 该局的胡分数
	public $m_wSetLoseScore = array();			// 该局的被胡分数

	public $m_Score = array();	//用户分数结构

	//数据区
	public $m_cancle = array();	//解散房间标志
	public $m_cancle_first;	//解散房间发起人
	public $m_sDingQue = array();	// 定缺状态
	public $m_huan_3_arr = array();	//换三张数据

	public $m_nTableCards = array();		// 玩家的桌面牌
	public $m_nNumTableCards = array();	//玩家桌面牌数量
	public $m_sStandCard = array();			// 玩家倒牌 Stand_card
	public $m_sPlayer = array();				// 玩家手牌私有数据 Play_data
	public $m_nNumCheat = array();				// 玩家i诈胡次数

	//public $m_nNumFan = array();				// 赢家的番数
	//public $m_nCardLast;				// 海底牌

	//逃跑用户
	public $m_bFlee = array();
	public $m_nDajiaoFan = array();

	//处理命令
	//public $m_sEat;						// 存放吃牌信息 EAT_SUIT

	//处理命令
	//public $m_sCanGang;				// 存放可以杠的牌的信息 Gang_suit
	public $m_bChooseBuf = array();			// 玩家的选择胡,吃,碰,杠命令 1 等待操作 0 无操作

	public $m_nNumCmdHu;				// 胡命令的个数
	public $m_chairHu = array();				// 发出胡命令的玩家
	public $m_chairSendCmd;				// 当前发命令的玩家
	public $m_currentCmd;			// 当前的命令

	// 接收客户端数据
	//public $m_nJiang = array();				// 判断胡牌的将,不能胡时将为255;
	public $m_nHuList = array();			// 胡牌列表, m_nHuCList = [][0]: 可胡牌的个数
	public $m_nHuGiveUp = array();			// 该轮放弃胡的番数,m_nHuGiveUp = [][0]: 个数
	//public $m_nEatBuf;				// 客户端选择吃的三张牌

	// 与客户端无关
	public $m_nCardBuf = array();			// 牌的缓冲区
	public $m_HuCurt = array();	//胡牌信息

	public $m_bMaxFan = array();	//是否达到封顶番数
	public $m_bDingQue;                //定缺

	public $m_nChairBanker;				// 庄家的位置，
	public $m_nChairBankerNext = 255;				// 下一局庄家的位置，
	public $m_nCountAllot;					// 发到第几张牌
	public $m_sOutedCard;			// 刚打出的牌
	public $m_sysPhase;				// 当前阶段状态
	public $m_chairCurrentPlayer;			// 当前出牌者
	public $m_set_end_time;	//本局结束时间
    public $m_client_ip = array();                      // 用户ip

	/************************************************************************/
	/*                               函数区                                 */
	/************************************************************************/

	public function __construct($serv)
	{
		$this->serv = $serv;
		
		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_SET_OVER ;
		$this->m_room_state = ConstConfigSouth::ROOM_STATE_NULL ;
		$this->m_game_type = 1;
	}

	//回调web
	public function set_game_and_checkout($is_log=false)
	{
		//游戏记录
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
		, 'game_info'=>json_encode($this->OnGetChairScene(0, true)),'type'=>1, 'is_room_over'=>$is_room_over));
		//扣费或充值
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
//			$currency = -(floor($big_score/10.0));
//			if($currency < -4)
//			{
//				$currency = -4;
//			}
			$winner_count = 1;
			if($winner_arr)
			{
				$winner_count = count($winner_arr);
			}
			$currency_all = (!empty(Config::$set_num_arr_south[$this->m_rule->set_num])) ? -(Config::$set_num_arr_south[$this->m_rule->set_num]) : 0;
			$currency = intval($currency_all/$winner_count);
			foreach ($winner_arr as $item_user)
			{
				BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'checkout_open_room', 'platform'=>'gfplay', 'uid'=>$item_user, 'currency'=>$currency,'type'=>1));
			}
		}
//		if($this->m_nSetCount == 1)
//		{
//			$currency = (!empty(Config::$set_num_arr_south[$this->m_rule->set_num])) ? -(Config::$set_num_arr_south[$this->m_rule->set_num]) : 0;
//			BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'checkout_open_room', 'platform'=>'gfplay', 'uid'=>$this->m_room_owner, 'currency'=>$currency,'type'=>1));
//		}
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

	public function c_tiao($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if($this->m_room_state != ConstConfigSouth::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfigSouth::ROOM_STATE_OPEN )
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

			if($this->m_room_state != ConstConfigSouth::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfigSouth::ROOM_STATE_OPEN )
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

	//开新的房间或者加入房间取房间状态（给http的server用）
	public function c_get_room($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "room_owner"=>$this->m_room_owner, "desc"=>__LINE__);
		$itime = time();
		do {
            if(!empty($params['client_ip']))
            {
                $this->m_client_ip[$params['uid']] = $params['client_ip'];
            }

			if($this->m_room_state != ConstConfigSouth::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfigSouth::ROOM_STATE_OPEN )
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}
			if($itime - $this->m_start_time > 86400)
			{
				//超时
				$this->m_room_state = ConstConfigSouth::ROOM_STATE_NULL ;
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

	//掉线玩家重新回到游戏，取数据
	public function c_get_game($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {

			if($this->m_room_state != ConstConfigSouth::ROOM_STATE_GAMEING && $this->m_room_state != ConstConfigSouth::ROOM_STATE_OPEN )
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

	//开房（给http的server用）
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

			if($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_SET_OVER || $this->m_room_state == ConstConfigSouth::ROOM_STATE_GAMEING )
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__; break;
			}
			elseif ($this->m_room_state == ConstConfigSouth::ROOM_STATE_OPEN  && $this->m_room_owner != $params['uid'])
			{
				$return_send['code'] = 2; $return_send['text'] = '此房间已经被占用'; $return_send['desc'] = __LINE__; break;
			}
			$this->clear();
			if($this->m_game_type != $params['game_type'])
			{
				$return_send['code'] = 3; $return_send['text'] = '游戏类型不对'; $return_send['desc'] = __LINE__; break;
			}

			$this->m_rule = new RuleSouth();
			if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(2, 3, 4)))
			{
				$params['rule']['player_count'] = 4;
			}
			$this->m_rule->game_type = $params['rule']['game_type'];
			$this->m_rule->player_count = $params['rule']['player_count'];
			$this->m_rule->min_fan = $params['rule']['min_fan'];
			$this->m_rule->top_fan = $params['rule']['top_fan'];
			$this->m_rule->zimo_rule = $params['rule']['zimo_rule'];
			$this->m_rule->dian_gang_hua = $params['rule']['dian_gang_hua'];
			$this->m_rule->is_change_3 = $params['rule']['is_change_3'];
			$this->m_rule->is_yaojiu_jiangdui = $params['rule']['is_yaojiu_jiangdui'];
			$this->m_rule->is_menqing_zhongzhang = $params['rule']['is_menqing_zhongzhang'];
			$this->m_rule->is_tiandi_hu = $params['rule']['is_tiandi_hu'];
			$this->m_rule->set_num = $params['rule']['set_num'];
			
			$this->InitData(true);
			
			$this->m_room_state = ConstConfigSouth::ROOM_STATE_OPEN ;
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

			if($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_SET_OVER || (ConstConfigSouth::ROOM_STATE_OPEN != $this->m_room_state && ConstConfigSouth::ROOM_STATE_GAMEING != $this->m_room_state))
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}
			else if(empty($this->m_room_players) && ($itime - $this->m_start_time) > 3600)
			{
				$this->m_room_state = ConstConfigSouth::ROOM_STATE_OVER ;
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

	//准备开始
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
			
			if($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_SET_OVER || (ConstConfigSouth::ROOM_STATE_OPEN != $this->m_room_state && ConstConfigSouth::ROOM_STATE_GAMEING != $this->m_room_state))
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
				$this->m_room_state = ConstConfigSouth::ROOM_STATE_GAMEING ;
			}

		}while(false);

		$this->serv->send($fd,  Room::tcp_encode(($return_send)));

		return $return_send['code'];
	}

	//解散房间
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

			if((ConstConfigSouth::ROOM_STATE_OPEN != $this->m_room_state && ConstConfigSouth::ROOM_STATE_GAMEING != $this->m_room_state))
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

	//换3张
	public function c_huan_3($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| !isset($params['huan_card']) || !is_array($params['huan_card']) || 3 != count($params['huan_card'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_HUAN_3 || ConstConfigSouth::ROOM_STATE_GAMEING != $this->m_room_state)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$huan_card_type = ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID;
			$card_type_arr = array();
			$card_key_arr = array();
			$card_key_2_arr = array();
			foreach ($params['huan_card'] as $card_item)
			{
				$huan_card_type = $this->get_card_type($card_item);
				$card_type_arr[] = $huan_card_type;
				$card_key_arr[] = $card_item % 16;
			}
			if(1 < count(array_unique($card_type_arr)) || $huan_card_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID)
			{
				$return_send['code'] = 5; $return_send['text'] = '换牌类型错误'; $return_send['desc'] = __LINE__; break;
			}
			foreach ($card_key_arr as $card_key_item)
			{
				if($card_key_item <=0 || $card_key_item > 9)
				{
					//牌名错了
					$return_send['code'] = 5; $return_send['text'] = '换牌类型错误'; $return_send['desc'] = __LINE__; break 2;
				}
				if(empty($card_key_2_arr[$card_key_item]))
				{
					$card_key_2_arr[$card_key_item] = 1;
				}
				else
				{
					$card_key_2_arr[$card_key_item] += 1;
				}
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if ($this->m_huan_3_arr[$key]->card_arr)
					{
						$return_send['code'] = 4; $return_send['text'] = '您已经指定换的牌了'; $return_send['desc'] = __LINE__; break 2;
					}

					if($this->m_nChairBanker == $key)
					{
						$tmp_card_taken_now = $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now;
						$this->list_insert($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
						$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = 0;
					}
					foreach ($card_key_2_arr as $tmp_key => $tmp_val)
					{
						//手牌不够换的牌
						if(empty($this->m_sPlayer[$key]->card[$huan_card_type][$tmp_key]) || $this->m_sPlayer[$key]->card[$huan_card_type][$tmp_key] < $tmp_val)
						{
							$return_send['code'] = 5; $return_send['text'] = '手牌不够换的'; $return_send['desc'] = __LINE__; break 3;
						}
					}
					if($this->m_nChairBanker == $key)
					{
						$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $tmp_card_taken_now;
						$this->list_delete($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
					}

					$this->handle_huan_3($key, $params['huan_card']);
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

	//定缺
	public function c_ding_que($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| !isset($params['que_card_type'])
			|| !in_array($params['que_card_type'], array(ConstConfigSouth::PAI_TYPE_WAN ,ConstConfigSouth::PAI_TYPE_TIAO ,ConstConfigSouth::PAI_TYPE_TONG ))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}

			if($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_DING_QUE || ConstConfigSouth::ROOM_STATE_GAMEING != $this->m_room_state)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if ($this->m_sDingQue[$key]->recv)
					{
						$return_send['code'] = 4; $return_send['text'] = '您已经定缺了'; $return_send['desc'] = __LINE__; break 2;
					}

					$this->handle_ding_que($key, $params['que_card_type']);
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

	//胡
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

			if ($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD)
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
					
					if($this->m_sPlayer[$key]->state != ConstConfigSouth::PLAYER_STATUS_CHOOSING)
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

			if ($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD)
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
					
					if($this->m_sDingQue[$key]->card_type == $this->get_card_type($params['gang_card']))
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

			if ($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD)
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
						if ($this->m_sStandCard[$key]->type[$i] == ConstConfigSouth::DAO_PAI_TYPE_KE
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
					if($this->m_sDingQue[$key]->card_type == $this->get_card_type($params['gang_card']))
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

			if ($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD)
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
					if($tmp_card == 0
					 || ($this->m_sDingQue[$key]->recv
					 	&& ($this->m_sPlayer[$key]->card[$this->m_sDingQue[$key]->card_type][0] > 0 
					 		|| $this->m_sDingQue[$key]->card_type == $this->get_card_type($this->m_sPlayer[$key]->card_taken_now)
					 		)
					 	&& $this->m_sDingQue[$key]->card_type != $this->get_card_type($tmp_card)
					 	)
					 )
					{
						//有缺门不能出其他门
						$return_send['code'] = 5; $return_send['text'] = '出牌错误'; $return_send['desc'] = __LINE__; break 2;
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

			if ($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD)
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

					$this->m_sPlayer[$key]->state = ConstConfigSouth::PLAYER_STATUS_THINK_OUTCARD ;
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

			if ($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_CHOOSING)
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
					if($this->m_sDingQue[$key]->card_type == $this->get_card_type($this->m_sOutedCard->card))
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

			if ($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_CHOOSING)
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
					if($this->m_sDingQue[$key]->card_type == $this->get_card_type($this->m_sOutedCard->card))
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

			if ($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_CHOOSING)
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
					
					$params['type'] = 0;
					if( (empty($this->m_sOutedCard->card) && empty($this->m_sQiangGang->card))
					  || ($this->m_sOutedCard->card && $this->m_sOutedCard->chair == $key)
					  || ($this->m_sQiangGang->card && $this->m_sQiangGang->chair == $key)
					  )
					{
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 5; $return_send['text'] = '胡牌错误'; $return_send['desc'] = __LINE__; break 2;
					}
					if($this->m_sPlayer[$key]->state != ConstConfigSouth::PLAYER_STATUS_CHOOSING)
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
					if(!$this->judge_hu($key))
					{
						$this->m_HuCurt[$key]->clear();
						$this->list_delete($key, $temp_card);
						$this->HandleZhaHu($key);
						$this->c_cancle_choice($fd, $params);
						$return_send['code'] = 4; $return_send['text'] = '当前用户诈胡'; $return_send['desc'] = __LINE__; break 2;
					}
					$this->m_HuCurt[$key]->clear();
					$this->list_delete($key, $temp_card);

					$this->ClearChooseBuf($key, false);
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

			if ($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_CHOOSING)
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
						if($this->judge_hu($key))
						{
							$this->m_nHuGiveUp[$key] = $this->judge_fan($key);
						}
						$this->m_HuCurt[$key]->clear();
						$this->list_delete($key, $temp_card);
					}
					$this->ClearChooseBuf($key, false); //有可能取消的是抢杠胡，这是需要后面判断来补张
					$this->m_sPlayer[$key]->state = ConstConfigSouth::PLAYER_STATUS_WAITING;
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

	public function c_hu_give_up($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
			}
			
			$params['act'] = 'c_hu_give_up';

			if ($this->m_sysPhase != ConstConfigSouth::SYSTEMPHASE_CHOOSING)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
			}

			$is_act= false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					if(!($this->m_bChooseBuf[$key]))
					{
						$return_send['code'] = 4; $return_send['text'] = '当前用户无需选择'; $return_send['desc'] = __LINE__; break 2;
					}
					if($this->m_nHuGiveUp[$key] != 255)	//过手胡
					{
						$temp_card = $this->m_sOutedCard->card;
						if ($this->m_sQiangGang->mark )
						{
							$temp_card = $this->m_sQiangGang->card;
						}
						$this->list_insert($key, $temp_card);
						if(!$this->judge_hu($key) || $this->judge_fan($key) <= $this->m_nHuGiveUp[$key])
						{
							$this->m_HuCurt[$key]->clear();
							$this->list_delete($key, $temp_card);	
							$return_send['code'] = 3; $return_send['text'] = '当前用户不能胡牌'; $return_send['desc'] = __LINE__; break 2;
						}
						$this->m_HuCurt[$key]->clear();
						$this->list_delete($key, $temp_card);	
					}
					$is_act = true;
				}
			}
			if(!$is_act)
			{
				$return_send['code'] = 5; $return_send['text'] = '当前用户不在本房'; $return_send['desc'] = __LINE__; break 1;
			}
		}while(false);

		$this->serv->send($fd, Room::tcp_encode(($return_send)));
		return $return_send['code'];
	}

	
	//--------------------------------------------------------------------------
	//--------------------------------------------------------------------------

	//判断胡
	public function judge_hu($chair)
	{
		if ($this->judge_hua_zhu($chair))	//判断花猪
		{
			return false;
		}

		//胡牌型
		$is_menqing = false;
		$is_zhongzhang = false;
		$hu_type = $this->judge_hu_type($chair, $is_menqing, $is_zhongzhang);

		if($hu_type == ConstConfigSouth::HU_TYPE_FENGDING_TYPE_INVALID)
		{
			return false;
		}
		//记录在全局数据
		$this->m_HuCurt[$chair]->method[0] = $hu_type;
		$this->m_HuCurt[$chair]->count = 1;

		//门清中张
		if($this->m_rule->is_menqing_zhongzhang)
		{
			if($is_menqing)
			{
				$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_MENG_QING);
			}
			if($is_zhongzhang)
			{
				$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_ZHONGZHANG);
			}
		}

		//天地胡处理
		if($this->m_rule->is_tiandi_hu)
		{
			if($this->m_bTianRenHu)
			{
				if($chair == $this->m_nChairBanker)
				{
					$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_TIANHU);
				}
				else
				{
					$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_DIHU);
				}
			}
			else if(0 == $this->m_nDiHu[$chair])
			{
				$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_DIHU);
			}
		}

		if($this->m_sStandCard[$chair]->num == 4) //金钩
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_JINGOU);
		}

		//自摸加番
		if($this->m_rule->zimo_rule == 1 && $this->m_HuCurt[$chair]->state == ConstConfigSouth::WIN_STATUS_ZI_MO)
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_ZIMOFAN);
		}

		if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfigSouth::WIN_STATUS_CHI_PAO)	// 处理抢杠
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_QIANGGANG);
		}
		else if($this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair)	//杠开
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_GANGKAI);
		}
		else if ($this->m_HuCurt[$chair]->state == ConstConfigSouth::WIN_STATUS_CHI_PAO && $this->m_sGangPao->mark && $this->m_sGangPao->chair != $chair)
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_GANGPAO);
		}

		if($this->m_nCountAllot >= ConstConfigSouth::BASE_CARD_NUM && $this->m_HuCurt[$chair]->state == ConstConfigSouth::WIN_STATUS_ZI_MO) //海底胡
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_HAIDIHU);
		}
		if($this->m_nCountAllot >= ConstConfigSouth::BASE_CARD_NUM && $this->m_HuCurt[$chair]->state == ConstConfigSouth::WIN_STATUS_CHI_PAO) //海底炮
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_HAIDIHU);
		}

		//附加胡处理
		for ($i = 0; $i<$this->m_nGen[$chair]; ++$i)
		{
			$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_GEN);
		}
		for ($i=0; $i<$this->m_sStandCard[$chair]->num; ++$i)
		{
			if ($this->m_sStandCard[$chair]->type[$i] != ConstConfigSouth::DAO_PAI_TYPE_KE && $this->m_sStandCard[$chair]->type[$i] != ConstConfigSouth::DAO_PAI_TYPE_SHUN)
			{
				$this->m_HuCurt[$chair]->add_hu(ConstConfigSouth::ATTACHED_HU_GANG);
			}
		}

		return true;
	}

	public function judge_hu_type($chair, &$is_menqing, &$is_zhongzhang)
	{
		$gen_arr = array();
		$qidui_arr = array();
		$qing_arr = array();
		$yaojiu_arr = array();
		$is258_arr = array();
		$pengpeng_arr = array();
		$zhongzhang_arr = array();
		$menqing_arr = array();
		$jiang_arr = array();

		$bType32 = false;
		$bQiDui = false;
		$bQing = false;
		$bPengPeng = false;
		$bYaoJiu = false;
		$b258 = false;
		$bMengQing = false;
		$bZhongZhang = false;

		//手牌
		for($i=ConstConfigSouth::PAI_TYPE_WAN ; $i<=ConstConfigSouth::PAI_TYPE_TONG ; $i++)
		{
			if(0 == $this->m_sPlayer[$chair]->card[$i][0])
			{
				continue;
			}
			if(in_array($this->m_sPlayer[$chair]->card[$i][0], array(1, 7, 13)))
			{
				return ConstConfigSouth::HU_TYPE_FENGDING_TYPE_INVALID ;
			}
			$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));
			if(!isset(ConstConfigSouth::$hu_data[$key]))
			{
				return ConstConfigSouth::HU_TYPE_FENGDING_TYPE_INVALID ;
			}
			else
			{
				$hu_list_val = ConstConfigSouth::$hu_data[$key];
				//1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128*$gen
				$gen_arr[] = intval($hu_list_val/128);
				$qidui_arr[] = $hu_list_val & 64;
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
				$pengpeng_arr[] = $hu_list_val & 8;
				$qing_arr[] = $i;	//ConstConfigSouth::PAI_TYPE_WAN	...
				if($this->m_rule->is_yaojiu_jiangdui)
				{
					$is258_arr[] = $hu_list_val & 4;
					$yaojiu_arr[] = $hu_list_val & 2;
				}
				if($this->m_rule->is_menqing_zhongzhang)
				{
					$menqing_arr[] = 1;
					$zhongzhang_arr[] = $hu_list_val & 16;
				}
			}
		}

		//倒牌
		for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
		{
			$pai_type = $this->get_card_type($this->m_sStandCard[$chair]->first_card[$i]);
			if($pai_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID)
			{
				return ConstConfigSouth::HU_TYPE_FENGDING_TYPE_INVALID ;
			}
			$pai_key = $this->m_sStandCard[$chair]->first_card[$i]%16;

			$qidui_arr[] = 0;
			$qing_arr[] = $pai_type;
			if($this->m_rule->is_yaojiu_jiangdui)
			{
				$is258_arr[] = intval(in_array($pai_key, [2,5,8]));
				$yaojiu_arr[] = intval(in_array($pai_key, [1,9]));
			}
			if($this->m_rule->is_menqing_zhongzhang)
			{
				$zhongzhang_arr[] = intval(!in_array($pai_key, [1,9]));
				$menqing_arr[] = (ConstConfigSouth::DAO_PAI_TYPE_ANGANG == $this->m_sStandCard[$chair]->type[$i])? 1 : 0;
			}
			if(ConstConfigSouth::DAO_PAI_TYPE_KE  == $this->m_sStandCard[$chair]->type[$i] && $this->m_sPlayer[$chair]->card[$pai_type][$pai_key] > 0)
			{
				//手牌，倒牌组合根
				$gen_arr[] = 1;
			}
		}

		//记录根到全局数据
		$this->m_nGen[$chair] = array_sum($gen_arr);
		$bType32 = (32 == array_sum($jiang_arr));
		$bQiDui = !array_keys($qidui_arr, 0);
		$bQing = ( 1 == count(array_unique($qing_arr)));
		$bPengPeng = !array_keys($pengpeng_arr, 0);
		if($this->m_rule->is_yaojiu_jiangdui)
		{
			$bYaoJiu = !array_keys($yaojiu_arr, 0);
			$b258 = !array_keys($is258_arr, 0);
		}

		if($this->m_rule->is_menqing_zhongzhang)
		{
			$bMengQing = !array_keys($menqing_arr, 0);
			$bZhongZhang = !array_keys($zhongzhang_arr, 0);
		}

		//门清中张是附加番
		if($bMengQing)
		{
			$is_menqing = $bMengQing;
			//return ConstConfigSouth::HU_TYPE_MENG_QING ;				//门清
		}
		if($bZhongZhang)
		{
			$is_zhongzhang = $bZhongZhang;
			//return ConstConfigSouth::HU_TYPE_ZHONGZHANG ;				//中张
		}

		//
		if(!$bType32 && !$bQiDui)	//不是32牌型也不是7对
		{
			return ConstConfigSouth::HU_TYPE_FENGDING_TYPE_INVALID ;
		}
		else if ($bQiDui)				//判断七对，可能同时是32牌型
		{
			if ($bQing && $this->m_nGen[$chair])
			{
				$this->m_nGen[$chair]--;
				return ConstConfigSouth::HU_TYPE_QINGLONG_QIDUI ;	//青龙七对
			}
			else if ($bQing)
			{
				return ConstConfigSouth::HU_TYPE_QING_QIDUI ;		//青七对
			}
			if($b258 && $this->m_nGen[$chair])
			{
				$this->m_nGen[$chair]--;
				return ConstConfigSouth::HU_TYPE_JIANG_QIDUI ;		//将七对，因为缺门必然是龙七对
			}
			else if ($this->m_nGen[$chair])
			{
				$this->m_nGen[$chair]--;
				return ConstConfigSouth::HU_TYPE_LONG_QIDUI ;		//龙七对
			}
			else
			{
				return ConstConfigSouth::HU_TYPE_QIDUI ;			//七对
			}
		}
		if($bYaoJiu && $bQing)
		{
			return ConstConfigSouth::HU_TYPE_QING_YAOJIU ;			//清幺九
		}
		if ($bPengPeng && $b258)
		{
			return ConstConfigSouth::HU_TYPE_JIANG_PENG ;				//将碰
		}
		if($bQing && $bPengPeng)
		{
			return ConstConfigSouth::HU_TYPE_QING_PENG ;				//清碰
		}
		if ($bYaoJiu)
		{
			return ConstConfigSouth::HU_TYPE_YAOJIU ;					//幺九
		}
		if($bQing)
		{
			return ConstConfigSouth::HU_TYPE_QINGYISE ;				//清一色
		}
		if($bPengPeng)
		{
			return ConstConfigSouth::HU_TYPE_PENGPENGHU ;				//碰碰胡
		}

		return ConstConfigSouth::HU_TYPE_PINGHU ;	//平胡
	}

	public function judge_fan($chair)
	{
		$fan_sum = 0;
		$hu_type = $this->m_HuCurt[$chair]->method[0];
		if($hu_type == ConstConfigSouth::HU_TYPE_FENGDING_TYPE_INVALID )
		{
			return 0;
		}

		$tmp_hu_desc = '(';

		if(isset(ConstConfigSouth::$hu_type_arr[$hu_type]))
		{
			$fan_sum = ConstConfigSouth::$hu_type_arr[$hu_type][1];
			$tmp_hu_desc .= ConstConfigSouth::$hu_type_arr[$hu_type][2].' ';
		}

		for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
		{
			if(isset(ConstConfigSouth::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
			{
				$fan_sum += ConstConfigSouth::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
				$tmp_hu_desc .= ConstConfigSouth::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
			}
		}

		$this->m_bMaxFan[$chair] = false;
		if ($fan_sum > $this->m_rule->top_fan)
		{
			$fan_sum = $this->m_rule->top_fan;
			$this->m_bMaxFan[$chair] = true;
		}
		
		if($this->m_HuCurt[$chair]->state == ConstConfigSouth::WIN_STATUS_ZI_MO)
		{
			if($this->m_rule->zimo_rule == 0)
			{
				$tmp_hu_desc .= '自摸加底 ';
			}
			$tmp_hu_desc = '自摸胡'.$fan_sum.'番'.$tmp_hu_desc;
		}
		else 
		{
			$tmp_hu_desc = '接炮胡'.$fan_sum.'番'.$tmp_hu_desc;
		}
		$tmp_hu_desc .= ') ';
		//if(!$this->m_hu_desc[$chair])
		//{
			$this->m_hu_desc[$chair] = $tmp_hu_desc;
		//}

		return $fan_sum;
	}

	//插入牌
	public function list_insert($chair, $card)
	{
		$card_type = $this->get_card_type($card);
		if($card_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID)
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

	//删除牌
	public function list_delete($chair, $card)
	{
		$card_type = $this->get_card_type($card);
		if($card_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID)
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
	public function list_find($chair, $card)
	{
		$card_type = $this->get_card_type($card);
		if($card_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}
		$card_key = $card%16;
		return $this->m_sPlayer[$chair]->card[$card_type][$card_key];
	}

	//返回牌的类型
	public function get_card_type($card)
	{
		if($card <= 9 && $card >= 1)	return ConstConfigSouth::PAI_TYPE_WAN;
		if($card <= 25 && $card >= 17)	return ConstConfigSouth::PAI_TYPE_TIAO;
		if($card <= 41 && $card >= 33)	return ConstConfigSouth::PAI_TYPE_TONG;
		//if($card <= 55 && $card >= 49)	return ConstConfigSouth::PAI_TYPE_FENG;
		//		if($card <= 72 && $card >= 65)	return ConstConfigSouth::PAI_TYPE_DRAGON;
		return ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID;
	}

	public function get_card_index($type, $key)
	{
		//四川麻将没有风牌和花牌
		if($type >=ConstConfigSouth::PAI_TYPE_WAN  && $type <=ConstConfigSouth::PAI_TYPE_TONG && $key >=1 && $key <=9)
		{
			return $type * 16 + $key;
		}
		return 0;
	}

	public function ClearChooseBuf($chair, $ClearGang=true)
	{
		if($ClearGang)
		{
			$this->m_sQiangGang->clear();
		}
		$this->m_bChooseBuf[$chair] = 0;
	}

	//判断有没有吃

	//判断有没有碰
	public function find_peng($chair)
	{
		if($this->m_sPlayer[$chair]->state != ConstConfigSouth::PLAYER_STATUS_CHOOSING)
		{
			return false;
		}
		
		$card_type = $this->get_card_type($this->m_sOutedCard->card);
		if(ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
		{
			return false;
		}

		if ($this->m_bDingQue && $card_type == $this->m_sDingQue[$chair]->card_type)
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

	// 判断有没有别人打来的明杠
	public function find_zhi_gang($chair)
	{
		if($this->m_sPlayer[$chair]->state != ConstConfigSouth::PLAYER_STATUS_CHOOSING)
		{
			return false;
		}
		
		$card_type = $this->get_card_type($this->m_sOutedCard->card);
		if(ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
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

	//判断花猪
	public function judge_hua_zhu($chair)
	{
		if($this->m_bDingQue)
		{
			if ($this->m_sPlayer[$chair]->card[$this->m_sDingQue[$chair]->card_type][0] != 0)
			{
				return true;
			}
		}
		//		else
		//		{
		//			if (!$this->judge_que_yi_men($chair))
		//			{
		//				return true;
		//			}
		//			else
		//			{
		//				return false;
		//			}
		//		}
		return false;
	}

	//查大叫，效率低
	public function judge_da_jiao( $chair, &$nMaxFan )
	{
		$nMaxFan = 0;
		$is_hu = false;

		if ($this->m_sPlayer[$chair]->card_taken_now != 0)
		{
			echo("查大叫的时候一般进不来，因为都会打出最后一个牌qweqweqwe".__LINE__);
			$card_type = $this->get_card_type($this->m_sPlayer[$chair]->card_taken_now);
			if(ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
			{
				echo("错误的牌类型，发生在JudgeDajiao1".__LINE__);
				return $is_hu;
			}
			$this->list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now); //整理完毕
			///		m_sPlayer[$chair]->card_taken_now = 0;

			//此处效率低
			for ($i = ConstConfigSouth::PAI_TYPE_WAN; $i<=ConstConfigSouth::PAI_TYPE_TONG; ++$i)
			{
				if ($this->m_sPlayer[$chair]->card[$i][0] == 0)
				{
					continue;
				}
				for ($j = 1; $j <= 9; ++$j)
				{
					if ($this->m_sPlayer[$chair]->card[$i][$j] == 0)
					{
						continue;
					}

					$this->m_sPlayer[$chair]->card[$i][$j] -= 1;

					$tmp_fan = 0;
					$this->SetHuList($chair, $tmp_fan);
					$nHuCount = $this->m_nHuList[$chair][0];

					if ($nHuCount == 0 )
					{
						$this->m_sPlayer[$chair]->card[$i][$j] += 1;
						continue;
					}
					else
					{
						$nMaxFan = $tmp_fan>$nMaxFan ? $tmp_fan : $nMaxFan;
						$is_hu = true;
					}
					$this->m_sPlayer[$chair]->card[$i][$j] += 1;
				}
			}

			$this->list_delete($chair, $this->m_sPlayer[$chair]->card_taken_now);
		}
		else
		{
			$tmp_fan = 0;
			$this->SetHuList($chair, $tmp_fan);
			$nHuCount = $this->m_nHuList[$chair][0];
			if ($nHuCount == 0)
			{
				return $is_hu;
			}
			else
			{
				$nMaxFan = $tmp_fan>$nMaxFan? $tmp_fan: $nMaxFan;
				$is_hu = true;
			}
		}

		return $is_hu;
	}

	//置位胡牌列表，效率不高，少用
	public function SetHuList($chair, &$max_fan)
	{
		$n = 0;
		$card_type = ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID ;

		$this->m_nHuList[$chair] = [0];
		if($this->judge_hua_zhu($chair))
		{
			return false;
		}
		for($i=ConstConfigSouth::PAI_TYPE_WAN ; $i<=ConstConfigSouth::PAI_TYPE_TONG; $i++)
		{
			if ($this->m_sPlayer[$chair]->card[$i][0] == 0)
			{
				continue;
			}
			for ($j=1; $j<=9; $j++)
			{
				//不靠张的牌不可能胡
				$before_j = $j - 1;
				$next_j = $j+1;
				$before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$i][$before_j] : 0 ;
				$next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$i][$next_j] : 0 ;
				if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$i][$j] && 0 == $next_count)
				{
					continue;
				}

				$card = $this->get_card_index($i, $j);
				if(!$this->list_insert($chair, $card))
				{
					continue;
				}

				$bCanHu = FALSE;
				if ($this->judge_hu($chair))
				{
					$bCanHu = TRUE;
				}
				if($bCanHu)
				{
					$n++;
					$this->m_nHuList[$chair][$n] = $card;
					$tmp_fan = $this->judge_fan($chair);
					$max_fan = $max_fan >= $tmp_fan ? $max_fan : $tmp_fan;
				}

				$this->m_HuCurt[$chair]->clear();

				$this->list_delete($chair, $card);
			}
		}
		$this->m_nHuList[$chair][0] = $n;
	}


	//找出第14张牌
	public function find_14_card($chair)
	{
		//如果定缺门有牌
		if($this->m_bDingQue && $this->m_sDingQue[$chair]->recv && $this->m_sPlayer[$chair]->card[$this->m_sDingQue[$chair]->card_type][0] != 0)
		{
			$last_type = $this->m_sDingQue[$chair]->card_type;
		}
		else
		{
			$last_type = ConstConfigSouth::PAI_TYPE_DRAGON;
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

	//处理吃牌
	//	public function HandleChooseEat($chair, $data)
	//	{
	//		if(!is_array($data))
	//		{
	//			return false;
	//		}
	//		$temp_card = $this->m_sOutedCard->card;
	//		$card_type = $this->get_card_type($temp_card);
	//
	//		if ($card_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID || $card_type != $this->get_card_type($data[0]))
	//		{
	//			return false;
	//		}
	//		// 删掉其他两张牌
	//		for ($i = 0; $i <= 2; $i ++)
	//		{
	//			if ($data[$i] != $temp_card)
	//			{
	//				$this->list_delete($chair,$data[$i]);
	//			}
	//		}
	//		// 设置倒牌
	//		$stand_count = $this->m_sStandCard[$chair]->num;
	//		$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfigSouth::DAO_PAI_TYPE_SHUN;
	//		$this->m_sStandCard[$chair]->first_card[$stand_count] = $data[0];
	//		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
	//		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
	//		$this->m_sStandCard[$chair]->num ++;
	//
	//		// 找出第14张牌
	//		$car_14 = $this->find_14_card($chair);
	//		if(!$car_14)
	//		{
	//			return false;
	//		}
	//
	//		//$this->m_sPlayer[$chair]->len -= 3;
	//		$this->m_sOutedCard->clear();
	//		$this->m_sPlayer[$chair]->card_taken_now = $car_14;
	//
	//		for ($i = 0; $i < $this->m_rule->player_count ; $i ++)
	//		{
	//			$this->m_sPlayer[$i]->state = ConstConfigSouth::PLAYER_STATUS_WAITING;
	//		}
	//		// 改变状态
	//		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD;
	//		$this->m_sPlayer[$chair]->state = ConstConfigSouth::PLAYER_STATUS_THINK_OUTCARD;
	//		$this->m_chairCurrentPlayer = $chair;
	//
	//		//状态变化发消息
	//		for ($i=0; $i < $this->m_rule->player_count ; $i++)
	//		{
	//			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
	//			$cmd->send($this->serv);
	//			unset($cmd);
	//		}
	//
	//		return true;
	//	}
	//
	//处理碰
	public function HandleChoosePeng($chair)
	{
		$temp_card = $this->m_sOutedCard->card;
		$card_type = $this->get_card_type($temp_card);

		if ($card_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID)
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
		$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfigSouth::DAO_PAI_TYPE_KE;
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
			if($this->m_sPlayer[$i]->state != ConstConfigSouth::PLAYER_STATUS_BLOOD_HU)
			{
				$this->m_sPlayer[$i]->state = ConstConfigSouth::PLAYER_STATUS_WAITING;
			}
		}
		// 改变状态
		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD;
		$this->m_sPlayer[$chair]->state = ConstConfigSouth::PLAYER_STATUS_THINK_OUTCARD;
		$this->m_chairCurrentPlayer = $chair;
		
		$this->m_sGangPao->clear();
		$this->m_only_out_card[$chair] = true;

		//状态变化发消息
		$this->send_act($this->m_currentCmd, $chair);

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}

		return true;

	}

	public function HandleChooseAnGang($chair, $gang_card)
	{
		$temp_card = $gang_card;
		$card_type = $this->get_card_type($temp_card);

		if ($card_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID)
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
		$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfigSouth::DAO_PAI_TYPE_ANGANG;
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

			if ($this->m_sPlayer[$i]->state != ConstConfigSouth::PLAYER_STATUS_BLOOD_HU)
			{
				$nGangScore = 2 * ConstConfigSouth::SCORE_BASE;

				$this->m_wGFXYScore[$i] = -$nGangScore;			//扣本次刮风下雨分
				$this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分

				$this->m_wGFXYScore[$chair] += $nGangScore;				//赢本次刮风下雨分
				$this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分

				$this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

				$nGangPao += $nGangScore;

			}
		}

		$this->m_sGangPao->init_data(true, $gang_card, $chair, ConstConfigSouth::DAO_PAI_TYPE_ANGANG, $nGangPao);

		$this->m_wTotalScore[$chair]->n_angang += 1;

		// 补发张牌给玩家
		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD;
		$this->m_chairCurrentPlayer = $chair;
		if(!($this->DealCard($chair)))
		{
			return;
		}

		//暗杠需要记录入命令
		$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
		$this->m_currentCmd = 'c_an_gang';
		$this->m_sOutedCard->clear();
		if($this->m_nEndReason == ConstConfigSouth::END_REASON_NOCARD)
		{
			//CCLog("end reason no card");
			return;
		}

		//状态变化发消息
		$this->send_act($this->m_currentCmd, $chair);

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}

	}

	public function HandleChooseZhiGang($chair)
	{
		$temp_card = $this->m_sOutedCard->card;
		$card_type = $this->get_card_type($temp_card);

		if ($card_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}

		$this->list_delete($chair, $temp_card);
		$this->list_delete($chair, $temp_card);
		$this->list_delete($chair, $temp_card);

		// 设置倒牌
		$stand_count = $this->m_sStandCard[$chair]->num;
		$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfigSouth::DAO_PAI_TYPE_MINGGANG;
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
				$nGangScore = 2*ConstConfigSouth::SCORE_BASE;

				$this->m_wGFXYScore[$i] = -$nGangScore;
				$this->m_wGangScore[$i][$i] -= $nGangScore;

				$this->m_wGFXYScore[$chair] += $nGangScore;
				$this->m_wGangScore[$chair][$chair] += $nGangScore;

				$this->m_wGangScore[$chair][$i] += $nGangScore;

				$nGangPao += $nGangScore;
			}
		}

		$this->m_sGangPao->init_data(true, $temp_card, $chair,ConstConfigSouth::DAO_PAI_TYPE_MINGGANG, $nGangPao);

		$this->m_wTotalScore[$chair]->n_zhigang_wangang += 1;

		// 补发张牌给玩家
		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD;
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

		if($this->m_nEndReason == ConstConfigSouth::END_REASON_NOCARD)
		{
			//CCLOG("end reason no card");
			return;
		}

		//状态变化发消息
		$this->send_act($this->m_currentCmd, $chair);
		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}
	}

	public function HandleChooseWanGang($chair, $gane_card)
	{
		$temp_card = $gane_card;
		$card_type = $this->get_card_type($temp_card);

		if ($card_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID)
		{
			return false;
		}
		

		$card_type_taken_now = $this->get_card_type($this->m_sPlayer[$chair]->card_taken_now);
		if(ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID == $card_type_taken_now)
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
			if ($this->m_sStandCard[$chair]->type[$i] == ConstConfigSouth::DAO_PAI_TYPE_KE
			&& $this->m_sStandCard[$chair]->card[$i] == $temp_card)
			{
				$this->m_sStandCard[$chair]->type[$i] = ConstConfigSouth::DAO_PAI_TYPE_WANGANG;
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
		
		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_CHOOSING;

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$next_chair = $this->AntiClock($next_chair);

			if ($this->m_sPlayer[$next_chair]->state == ConstConfigSouth::PLAYER_STATUS_BLOOD_HU )
			{
				continue;
			}
			
			$this->m_bChooseBuf[$next_chair] = 1;
			$this->m_sPlayer[$next_chair]->state = ConstConfigSouth::PLAYER_STATUS_CHOOSING;
			
			if($next_chair == $chair)
			{
				$this->m_bChooseBuf[$next_chair] = 0;
				$this->m_sPlayer[$next_chair]->state = ConstConfigSouth::PLAYER_STATUS_WAITING;
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

	public function HandleHuZiMo($chair)			//处理自摸
	{
		$temp_card = $this->m_sPlayer[$chair]->card_taken_now;
		$card_type = $this->get_card_type($temp_card);

		if ($card_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID)
		{
			echo("hu_zi_mo_error".__LINE__);
			return false;
		}

		$this->list_insert($chair, $temp_card);
		if($this->m_rule->dian_gang_hua == 0 && $this->m_bHaveGang)	//点杠花处理
		{
			$stand_count = $this->m_sStandCard[$chair]->num;
			if(!empty($this->m_sStandCard[$chair]->type[$stand_count-1]) && $this->m_sStandCard[$chair]->type[$stand_count-1] == ConstConfigSouth::DAO_PAI_TYPE_MINGGANG && $chair != $this->m_sStandCard[$chair]->who_give_me[$stand_count-1])
			{
				$this->m_HuCurt[$chair]->state = ConstConfigSouth::WIN_STATUS_CHI_PAO;
				$this->m_nChairDianPao = $this->m_sStandCard[$chair]->who_give_me[$stand_count-1];
			}
			else
			{
				$this->m_HuCurt[$chair]->state = ConstConfigSouth::WIN_STATUS_ZI_MO;
			}
		}
		else
		{
			$this->m_HuCurt[$chair]->state = ConstConfigSouth::WIN_STATUS_ZI_MO;
		}

		$bHu = $this->judge_hu($chair);
		$this->m_HuCurt[$chair]->card = $temp_card;
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
			if($this->m_nChairDianPao != 255 && $this->m_HuCurt[$chair]->state == ConstConfigSouth::WIN_STATUS_CHI_PAO)
			{
				$tmp_lost_chair = $this->m_nChairDianPao;
			}
			if($this->ScoreOneHuCal($chair, $tmp_lost_chair))
			{
				//总计自摸
				if($this->m_HuCurt[$chair]->state == ConstConfigSouth::WIN_STATUS_ZI_MO)
				{
					$this->m_wTotalScore[$chair]->n_zimo += 1;
					$this->m_currentCmd = 'c_zimo_hu';
				}
				else if($this->m_HuCurt[$chair]->state == ConstConfigSouth::WIN_STATUS_CHI_PAO)
				{
					$this->m_wTotalScore[$chair]->n_jiepao += 1;
					$this->m_wTotalScore[$this->m_nChairDianPao]->n_dianpao += 1;
					$this->m_currentCmd = 'c_hu';
				}

				$this->m_chairSendCmd = $this->m_chairCurrentPlayer;

				if ($this->m_game_type == 1)
				{
					$this->m_bChairHu[$chair] = true;
					$this->m_bChairHu_order[] = $chair;
					$this->m_nCountHu++;
					$this->m_sPlayer[$chair]->state = ConstConfigSouth::PLAYER_STATUS_BLOOD_HU;

					///				$this->list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now); //整理完毕
					
					//去除胡牌者 card_taken_now  这个牌就只有在 m_HuCurt 有
					$this->m_sPlayer[$chair]->card_taken_now = 0;

					if(255 == $this->m_nChairBankerNext)	//下一局庄家
					{
						$this->m_nChairBankerNext = $chair;
					}
					
					if ($this->m_nCountHu >= $this->m_rule->player_count-1)		//三个玩家胡牌
					{
						$this->m_nEndReason = ConstConfigSouth::END_REASON_HU;
						$this->HandleSetOver();

						//发消息
						$this->send_act($this->m_currentCmd, $chair);

//						$this->handle_flee_play(true);	//更新断线用户
//						$cmd = new Game_cmd($this->m_room_id, 's_game_over', $this->OnGetChairScene($chair, true), Game_cmd::SCO_ALL_PLAYER );
//						$cmd->send($this->serv);
//						unset($cmd);
						
//						$this->set_game_and_checkout();

						return true;
					}
					else					//游戏继续
					{
						$next_chair = $chair;
						do
						{
							$next_chair = $this->AntiClock($next_chair);
						} while ($this->m_bChairHu[$next_chair]);

						if ($next_chair == $chair)
						{
							echo ("find unHu player error, chair:". $chair."_".__LINE__);
							return false;
						}

						$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD ;
						$this->m_chairCurrentPlayer = $next_chair;
						
						if($this->m_nEndReason == ConstConfigSouth::END_REASON_NOCARD)
						{
							//echo ("end reason no card".__LINE__);
							$this->HandleSetOver();

							//发消息
							$this->send_act($this->m_currentCmd, $chair);
//							$this->handle_flee_play(true);	//更新断线用户
//							$cmd = new Game_cmd($this->m_room_id, 's_game_over', $this->OnGetChairScene($chair, true), Game_cmd::SCO_ALL_PLAYER );
//							$cmd->send($this->serv);
//							unset($cmd);
							
//							$this->set_game_and_checkout();

							return true;
						}
						
						if(!$this->DealCard($next_chair))
						{
							echo ("DealCard".__LINE__);
							return false;
						}						

						//发消息
						$this->send_act($this->m_currentCmd, $chair);
						$this->handle_flee_play(true);	//更新断线用户
						for($i=0; $i<$this->m_rule->player_count ; $i++)
						{
							$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
							$cmd->send($this->serv);
							unset($cmd);
						}
						return true;
					}
				}
			}
			else	//番数不够，判诈胡，一般进不来
			{
				echo("有人诈胡".__LINE__);
				$this->HandleZhaHu($chair);
				$this->m_HuCurt[$chair]->clear();

				$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD;
				$this->m_chairCurrentPlayer = $chair;
				$this->m_sPlayer[$chair]->state = ConstConfigSouth::PLAYER_STATUS_THINK_OUTCARD ;

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

	public function HandleOutCard($chair, $is_14 = false, $out_card = 0)		//处理出牌
	{
		//一旦有人出牌，表示上一轮竞争已经结束, 可以清CMD
		$this->m_chairSendCmd = 255;							// 当前发命令的玩家
		$this->m_currentCmd = 0;							// 当前的命令

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
			if ($card_type == ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID)
			{
				echo "出牌错误".__LINE__;
				return false;
			}
			$this->list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now); //整理完毕
			$this->m_sPlayer[$chair]->card_taken_now = 0;
		}

		$this->m_sPlayer[$chair]->seen_out_card = 1;
		$this->m_sPlayer[$chair]->state = ConstConfigSouth::PLAYER_STATUS_WAITING ;

		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_CHOOSING ;
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

		$this->send_act('c_out_card', $chair, $this->m_sOutedCard->card);

		$this->handle_flee_play(true);	//更新断线用户
		for ( $i=0; $i<$this->m_rule->player_count - 1; $i++)
		{
			$chair_next = $this->AntiClock($chair_next);
			if ($this->m_sPlayer[$chair_next]->state == ConstConfigSouth::PLAYER_STATUS_BLOOD_HU )
			{
				continue;
			}
			if ($chair_next == $chair)
			{
				continue;
			}
			$this->m_sPlayer[$chair_next]->seen_out_card = 0;
			$this->m_bChooseBuf[$chair_next] = 1;
			$this->m_sPlayer[$chair_next]->state = ConstConfigSouth::PLAYER_STATUS_CHOOSING;
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

			$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD;
			$this->m_chairCurrentPlayer = $next_chair;
			$this->m_sGangPao->clear();

			if(!$this->DealCard($next_chair))
			{
				echo(" return false in ".__LINE__);
				return false;
			}
			if($this->m_nEndReason == ConstConfigSouth::END_REASON_NOCARD)
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

	public function HandleChooseResult($chair, $nCmdID)
	{
		$this->handle_flee_play(true);
		
		//处理竞争
		$order_cmd = array('c_cancle_choice'=>0, 'c_eat'=>1, 'c_peng'=>2, 'c_zhigang'=>3, 'c_hu'=>4);
		if(empty($this->m_currentCmd) || ($order_cmd[$nCmdID] > $order_cmd[$this->m_currentCmd] && $order_cmd[$nCmdID] >= $order_cmd['c_cancle_choice']))	//吃, 碰, 杠竞争
		{
			$this->m_chairSendCmd	= $chair;
			$this->m_currentCmd	= $nCmdID;
		}
		if($nCmdID == 'c_hu')
		{
			$this->m_chairHu[$this->m_nNumCmdHu ++] = $chair;
		}

		//等待大家都选了竞争 吃碰杠胡 再去执行
		$sum = 0;

		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			if($i == $this->m_chairCurrentPlayer || $this->m_sPlayer[$i]->state == ConstConfigSouth::PLAYER_STATUS_BLOOD_HU )
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
			$this->m_nHuGiveUp[$this->m_chairSendCmd] = 255;
		}

		$temp_card=0;
		$card_type = ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID;

		//抉择后全部可见
		for($i = 0; $i< $this->m_rule->player_count; $i ++)
		{
			$this->m_sPlayer[$i]->seen_out_card = 1;
		}

		if ($this->m_sQiangGang->mark )	// 处理抢杠
		{
			$temp_card = $this->m_sQiangGang->card;
			$card_type = $this->get_card_type($temp_card);
			if(ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
			{
				echo("错误的牌类型，发生在-> 抢杠".__LINE__);
				return false;
			}

			$bHaveHu = false;
			$tmp_hu_count = 0;
			if ($this->m_nNumCmdHu)
			{
				for ($i=0; $i<$this->m_nNumCmdHu; $i++)
				{
					$this->list_insert($this->m_chairHu[$i], $temp_card);
					$this->m_HuCurt[$this->m_chairHu[$i]]->state = ConstConfigSouth::WIN_STATUS_CHI_PAO;   //抢杠算作吃炮
					$this->m_nChairDianPao = $this->m_sQiangGang->chair;
					$bHu = $this->judge_hu($this->m_chairHu[$i]);
					$this->m_HuCurt[$this->m_chairHu[$i]]->card = $temp_card;
					$this->list_delete($this->m_chairHu[$i], $temp_card);

					if(!$bHu)
					{
						echo("有人诈胡 at".__LINE__);
						$this->HandleZhaHu($this->m_chairHu[$i]);
						$this->m_HuCurt[$this->m_chairHu[$i]]->clear();
					}
					else
					{
						if($this->ScoreOneHuCal($this->m_chairHu[$i], $this->m_sQiangGang->chair))
						{
							$bHaveHu = true;
							$tmp_hu_count++;

							if($tmp_hu_count > 1)
							{
								$this->m_HuCurt[$this->m_chairHu[$i]]->card_state = 0;
							}

							if($this->m_HuCurt[$this->m_chairHu[$i]]->state == ConstConfigSouth::WIN_STATUS_CHI_PAO)
							{
								$this->m_wTotalScore[$this->m_chairHu[$i]]->n_jiepao += 1;
								$this->m_wTotalScore[$this->m_nChairDianPao]->n_dianpao += 1;
							}

							if ($this->m_game_type == 1)		//血战场
							{
								$this->m_bChairHu[$this->m_chairHu[$i]] = true;
								$this->m_bChairHu_order[] = $this->m_chairHu[$i];
								$this->m_nCountHu++;
								$this->m_sPlayer[$this->m_chairHu[$i]]->state = ConstConfigSouth::PLAYER_STATUS_BLOOD_HU;
							}
							//$this->list_insert($this->m_chairHu[$i], $temp_card); //整理完毕
							//$this->m_sPlayer[$this->m_chairHu[$i]]->card_taken_now = $temp_card;

							$this->send_act($this->m_currentCmd, $this->m_chairHu[$i]);
							if(255 == $this->m_nChairBankerNext)	//下一局庄家
							{
								$this->m_nChairBankerNext = $this->m_chairHu[$i];
							}
						}
						else
						{
							//$bHaveHu = false;
							$this->HandleZhaHu($this->m_chairHu[$i]);
							$this->m_HuCurt[$this->m_chairHu[$i]]->clear();
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
					if ($this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] == ConstConfigSouth::DAO_PAI_TYPE_WANGANG
					&& $this->m_sStandCard[$this->m_sOutedCard->chair]->card[$i] == $this->m_sOutedCard->card)
					{
						$this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] = ConstConfigSouth::DAO_PAI_TYPE_KE;
						break;
					}
				}

				if ($this->m_game_type == 1)
				{
					//血战场
					if ($this->m_nCountHu>=$this->m_rule->player_count-1 )		//三个玩家胡牌
					{
						$this->m_nEndReason = ConstConfigSouth::END_REASON_HU;
						$this->HandleSetOver();

						//发消息
//						$this->handle_flee_play(true);	//更新断线用户
//						$cmd = new Game_cmd($this->m_room_id, 's_game_over', $this->OnGetChairScene($chair, true), Game_cmd::SCO_ALL_PLAYER );
//						$cmd->send($this->serv);
//						unset($cmd);
						
//						$this->set_game_and_checkout();

						return;
					}
					else					//游戏继续
					{
						//$next_chair = $this->m_sOutedCard->chair;
						$next_chair = $this->m_chairHu[$this->m_nNumCmdHu-1];
						do
						{
							$next_chair = $this->AntiClock($next_chair);
						} while ($this->m_bChairHu[$next_chair]);

//						if ($next_chair == $this->m_chairCurrentPlayer)
//						{
//							echo("find unHu player error, chair".__LINE__);
//							return;
//						}

						$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD;
						$this->m_chairCurrentPlayer = $next_chair;
						$this->m_sOutedCard->clear();

						if(!$this->DealCard($next_chair))
						{
							return;
						}
						if($this->m_nEndReason == ConstConfigSouth::END_REASON_NOCARD)
						{
							//CCLOG("end reason no card");
							return;
						}
						//状态变化发消息
						$this->handle_flee_play(true);	//更新断线用户
						for ($i=0; $i < $this->m_rule->player_count ; $i++)
						{
							$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
							$cmd->send($this->serv);
							unset($cmd);
						}

						return;
					}
				}
			}
			else // 给杠的玩家补张
			{

				$GangScore = 0;
				$nGangPao = 0;
				$m_wGFXYScore = [0,0,0,0];
				for ( $i=0; $i<$this->m_rule->player_count; ++$i)
				{
					if ($i == $this->m_sQiangGang->chair || $this->m_sPlayer[$i]->state == ConstConfigSouth::PLAYER_STATUS_BLOOD_HU)
					{
						continue;
					}
					$nGangScore = ConstConfigSouth::SCORE_BASE;

					$this->m_wGFXYScore[$i] = -$nGangScore;
					$this->m_wGangScore[$i][$i] -= $nGangScore;

					$this->m_wGFXYScore[$this->m_sQiangGang->chair] += $nGangScore;
					$this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
					$this->m_wGangScore[$this->m_sQiangGang->chair][$i] += $nGangScore;
					
					$nGangPao += $nGangScore;
				}

				$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD;
				$this->m_chairCurrentPlayer = $this->m_sQiangGang->chair;

				$this->m_bHaveGang = true;					//for 杠上花
				$this->m_sGangPao->init_data(true, $this->m_sQiangGang->card, $this->m_sQiangGang->chair, ConstConfigSouth::DAO_PAI_TYPE_WANGANG, $nGangPao);

				$this->m_wTotalScore[$this->m_sQiangGang->chair]->n_zhigang_wangang += 1;

				//摸杠需要记录入命令
				$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
				$this->m_currentCmd = 'c_wan_gang';

				if(!$this->DealCard($this->m_chairCurrentPlayer))
				{
					return;
				}

				if($this->m_nEndReason == ConstConfigSouth::END_REASON_NOCARD)
				{
					//CCLOG("end reason no card");
					return;
				}

				//状态变化发消息
				$this->send_act($this->m_currentCmd, $this->m_sQiangGang->chair, $this->m_sQiangGang->card);
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
			$tmp_hu_count = 0;

			$temp_card = $this->m_sOutedCard->card;
			$card_type = $this->get_card_type($temp_card);
			if(ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
			{
				echo("错误的牌类型，发生在".__LINE__);
				return false;
			}

			if ($this->m_nNumCmdHu)
			{
				for ($i=0; $i<$this->m_nNumCmdHu; $i++)
				{
					$this->list_insert($this->m_chairHu[$i], $temp_card);
					$this->m_HuCurt[$this->m_chairHu[$i]]->state = ConstConfigSouth::WIN_STATUS_CHI_PAO;
					$this->m_nChairDianPao = $this->m_sOutedCard->chair;
					$bHu  = $this->judge_hu($this->m_chairHu[$i]);
					$this->m_HuCurt[$this->m_chairHu[$i]]->card = $temp_card;
					$this->list_delete($this->m_chairHu[$i], $temp_card);

					if(!$bHu)
					{
						echo("有人诈胡 at ".__LINE__);
						$this->HandleZhaHu($this->m_chairHu[$i]);
						$this->m_HuCurt[$this->m_chairHu[$i]]->clear();
					}
					else
					{
						if($this->ScoreOneHuCal($this->m_chairHu[$i], $this->m_nChairDianPao))
						{
							$bHaveHu = true;
							$tmp_hu_count++;

							if($tmp_hu_count > 1)
							{
								$this->m_HuCurt[$this->m_chairHu[$i]]->card_state = 0;
							}

							if($this->m_HuCurt[$this->m_chairHu[$i]]->state == ConstConfigSouth::WIN_STATUS_CHI_PAO)
							{
								$this->m_wTotalScore[$this->m_chairHu[$i]]->n_jiepao += 1;
								$this->m_wTotalScore[$this->m_nChairDianPao]->n_dianpao += 1;
							}

							if ($this->m_game_type == 1)		//血战场
							{
								$this->m_bChairHu[$this->m_chairHu[$i]] = true;
								$this->m_bChairHu_order[] = $this->m_chairHu[$i];
								$this->m_nCountHu++;
								$this->m_sPlayer[$this->m_chairHu[$i]]->state = ConstConfigSouth::PLAYER_STATUS_BLOOD_HU;
							}
							//$this->list_insert($this->m_chairHu[$i] $temp_card); //整理完毕
							//$this->m_sPlayer[$this->m_chairHu[$i]]->card_taken_now = $temp_card;
							$this->send_act($this->m_currentCmd, $this->m_chairHu[$i]);
							if(255 == $this->m_nChairBankerNext)	//下一局庄家
							{
								$this->m_nChairBankerNext = $this->m_chairHu[$i];
							}
						}
						else
						{
							//$bHaveHu = false;
							$this->HandleZhaHu($this->m_chairHu[$i]);
							$this->m_HuCurt[$this->m_chairHu[$i]]->clear();
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

				if ($this->m_game_type == 1)
				{
					//血战场
					if ($this->m_nCountHu>=$this->m_rule->player_count - 1)		//三个玩家胡牌
					{
						$this->m_nEndReason = ConstConfigSouth::END_REASON_HU;
						$this->HandleSetOver();

						//发消息
//						$this->handle_flee_play(true);	//更新断线用户
//						$cmd = new Game_cmd($this->m_room_id, 's_game_over', $this->OnGetChairScene($chair, true), Game_cmd::SCO_ALL_PLAYER );
//						$cmd->send($this->serv);
//						unset($cmd);
						
//						$this->set_game_and_checkout();

						return;
					}
					else					//游戏继续
					{
						//$next_chair = $this->m_sOutedCard->chair;
						$next_chair = $this->m_chairHu[$this->m_nNumCmdHu-1];
						do
						{
							$next_chair = $this->AntiClock($next_chair);
						} while ($this->m_bChairHu[$next_chair]);

//						if ($next_chair == $this->m_chairCurrentPlayer)
//						{
//							echo("find unHu player error, chair:".__LINE__);
//							return false;
//						}

						$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD;
						$this->m_chairCurrentPlayer = $next_chair;
						$this->m_sOutedCard->clear();

						if(!$this->DealCard($next_chair))
						{
							return;
						}
						if($this->m_nEndReason == ConstConfigSouth::END_REASON_NOCARD)
						{
							echo("end reason no card".__LINE__);
							return;
						}

						//状态变化发消息
						$this->handle_flee_play(true);	//更新断线用户
						for ($i=0; $i < $this->m_rule->player_count ; $i++)
						{
							$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
							$cmd->send($this->serv);
							unset($cmd);
						}
						return;
					}
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
				case 'c_cancle_choice':	// 发牌给下家
				default:  //预防有人诈胡后,游戏得以继续
				$this->m_sGangPao->clear();
				
				$next_chair = $this->m_chairCurrentPlayer;
				$next_chair = $this->AntiClock($next_chair);

				if ($next_chair == $this->m_chairCurrentPlayer)
				{
					echo("find unHu player error, chair".__LINE__);
					return;
				}

				$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD;
				$this->m_chairCurrentPlayer = $next_chair;

				if(!$this->DealCard($next_chair))
				{
					return;
				}
				if($this->m_nEndReason == ConstConfigSouth::END_REASON_NOCARD)
				{
					echo("end reason no card");
					return;
				}

				//状态变化发消息
				$this->send_act($this->m_currentCmd, $chair);
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

	public function ScoreOneHuCal($chair, &$lost_chair)
	{
		$fan_sum = $this->judge_fan($chair);
		if($fan_sum < $this->m_rule->min_fan)
		{
			$this->m_HuCurt[$chair]->clear();
			return false;
		}
		//$this->m_nNumFan[$chair] = $fan_sum;
		$PerWinScore = 1<<$fan_sum;	//2的N次方
		$wWinScore = 0;

		$this->m_wHuScore = [0,0,0,0];

		if($this->m_HuCurt[$chair]->state == ConstConfigSouth::WIN_STATUS_ZI_MO)
		{
			$chairBaoPai = 255;

			for($i = 0; $i < $this->m_rule->player_count; $i++)
			{
				if($i == $chair)
				{
					continue;	//单用户测试需要关掉
				}

				if ($this->m_game_type==1 && $this->m_sPlayer[$i]->state == ConstConfigSouth::PLAYER_STATUS_BLOOD_HU)
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

				$wWinScore = 0;
				$wWinScore += ConstConfigSouth::SCORE_BASE * $PerWinScore;

				$this->m_wHuScore[$lost_chair] -= $wWinScore;
				$this->m_wHuScore[$chair] += $wWinScore;

				$this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
				$this->m_wSetScore[$chair] += $wWinScore;

				$this->m_HuCurt[$chair]->gain_chair[0]++;
				$this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]]=$lost_chair;

				if ($this->m_rule->zimo_rule == 0)
				{
					$this->m_wHuScore[$lost_chair] -= ConstConfigSouth::SCORE_BASE;
					$this->m_wHuScore[$chair] += ConstConfigSouth::SCORE_BASE;

					$this->m_wSetLoseScore[$lost_chair] -= ConstConfigSouth::SCORE_BASE;
					$this->m_wSetScore[$chair] += ConstConfigSouth::SCORE_BASE;
				}
			}
			return true;
		}

		// 吃炮者算分在此处！！
		else if($this->m_HuCurt[$chair]->state == ConstConfigSouth::WIN_STATUS_CHI_PAO)
		{
			$chairBaoPai = 255;
			//杠炮
			if($this->m_sGangPao->mark)
			{
				if($this->m_sGangPao->chair != $chair)
				{
					//呼叫转移，如果是一炮多响就多次赔
					$this->m_wGangScore[$chair][$chair] += $this->m_sGangPao->score;
					$this->m_wGangScore[$lost_chair][$lost_chair] -= $this->m_sGangPao->score;

					$this->m_wGangScore[$lost_chair][$chair] -= $this->m_sGangPao->score;
				}
			}
			$wWinScore = 0;
			$wWinScore +=ConstConfigSouth::SCORE_BASE * $PerWinScore;

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

	public function HandleZhaHu($chair)
	{
		//以后另做处理，客户端诈胡等于作弊

		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			if($i==$chair)
			{
				continue;
			}
//			$this->m_wSetLoseScore[$chair] -= ConstConfigSouth::SCORE_ZHA_HU;
//			$this->m_wSetScore[$i] += ConstConfigSouth::SCORE_ZHA_HU;
//
//			$this->m_wHuScore[$chair] -= ConstConfigSouth::SCORE_ZHA_HU;
//			$this->m_wHuScore[$i] += ConstConfigSouth::SCORE_ZHA_HU;
		}
		$this->m_nNumCheat[$chair]++;

		//char szZhahu[512];
		//sprintf(szZhahu,"系统：玩家 %s 因诈胡被扣除%d分，诈胡原因可能为番数不足.", szName, SCORE_ZHA_HU);
		//--m_pSite->SendSysMsgToClient(SCO_ALL_USER,0,szZhahu);

		$this->m_bChooseBuf[$chair] = 0; //clear the hu signal
	}


	public function handle_huan_3($chair, $huan_card)
	{
		$this->m_huan_3_arr[$chair]->card_arr = $huan_card;

		for ($i = 0; $i<$this->m_rule->player_count; ++$i)
		{
			if (!$this->m_huan_3_arr[$i]->card_arr)
			{
				break;
			}
		}
		if ($this->m_rule->player_count == $i)
		{
			$this->list_insert($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
			$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = 0;

			//换
			for ($i = 0; $i<$this->m_rule->player_count; ++$i)
			{
				if($this->m_huan_3_type == ConstConfigSouth::HUAN_3_ANTICLOCKWISE)
				{
					$target_player = $this->AntiClock($i, 1);
				}
				else if ($this->m_huan_3_type == ConstConfigSouth::HUAN_3_CLOCKWISE)
				{
					$target_player = $this->AntiClock($i, -1);
				}
				else
				{
					$this->m_huan_3_type = ConstConfigSouth::HUAN_3_CROSS;
					$target_player = $this->AntiClock($i, 2);
				}

				$this->m_huan_3_arr[$chair]->get_card_arr = $this->m_huan_3_arr[$i]->card_arr;

				foreach ($this->m_huan_3_arr[$i]->card_arr as $card_item)
				{
					$this->list_delete($i, $card_item);
					$this->list_insert($target_player, $card_item);
				}
			}

			$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->find_14_card($this->m_nChairBanker);

			if ($this->m_bDingQue)
			{
				$this->start_ding_que();
				return true;
			}
			$this->game_to_playing();
		}
	}

	public function handle_ding_que($chair, $card_type)
	{
		$this->m_sDingQue[$chair]->recv = true;
		$this->m_sDingQue[$chair]->card_type = $card_type;

		for ($i = 0; $i<$this->m_rule->player_count; ++$i)
		{
			if (!$this->m_sDingQue[$i]->recv)
			{
				break;
			}
		}
		if ($this->m_rule->player_count == $i)
		{
			//发信息 - 状态改变
			//			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($chair), Game_cmd::SCO_ALL_PLAYER);
			//			$cmd->send($this->serv);
			//			unset($cmd);

			$this->game_to_playing();
		}
	}

	public function game_to_playing()
	{
		//状态设定
		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD ;
		$this->m_sPlayer[$this->m_nChairBanker]->state = ConstConfigSouth::PLAYER_STATUS_THINK_OUTCARD ;

		$this->m_chairCurrentPlayer = $this->m_nChairBanker;

		//$this->m_sCanGang->clear();
		$this->m_sPlayer[$this->m_nChairBanker]->state = ConstConfigSouth::PLAYER_STATUS_CHOOSING;
		$this->m_bChooseBuf[$this->m_nChairBanker] = 1;
		
		//处理庄家的14牌
		if($this->m_sPlayer[$this->m_nChairBanker]->card_taken_now)
		{
			$this->list_insert($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
			$this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->find_14_card($this->m_nChairBanker);
		}

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
		$this->m_nChairBanker = mt_rand(0, ($this->m_rule->player_count-1));
		//$this->m_nChairBanker = 0;
		return;
	}

	//取得所有玩家数据
	public function OnGetChairScene($chair, $is_more=false)
	{
		if ($this->m_sysPhase == ConstConfigSouth::SYSTEMPHASE_INVALID)
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
			//兼容老客户端
			//unset($data['m_rule']->player_count);
			$data['m_sDingQue'] = $this->m_sDingQue;

			$data['m_dice'] = $this->m_dice;
			$data['m_Score'] = $this->m_Score;		//分数
			$data['m_wTotalScore'] = $this->m_wTotalScore;
			$data['m_ready'] = $this->m_ready;
			
			$data['is_cancle'] = $this->m_cancle;
			$data['m_cancle'] = $this->m_cancle;
			$data['m_cancle_first'] = $this->m_cancle_first;
		}
		
		$data['m_nChairBanker'] = $this->m_nChairBanker;  //庄家		
		$data['m_nSetCount'] = $this->m_nSetCount;		
		$data['m_sysPhase'] = $this->m_sysPhase;	// 当前的阶段
		$data['m_nCountAllot'] = $this->m_nCountAllot;									// 发到第几张
		$data['m_bHaveGang'] = $this->m_bHaveGang;
		$data['m_sQiangGang'] = $this->m_sQiangGang;
		$data['m_sGangPao'] = $this->m_sGangPao;
		$data['m_bTianRenHu'] = $this->m_bTianRenHu;
		$data['m_nDiHu'] = $this->m_nDiHu;
		$data['m_bChairHu'] = $this->m_bChairHu;
		$data['m_bChairHu_order'] = $this->m_bChairHu_order;
		$data['m_HuCurt'] = $this->m_HuCurt;		//胡牌详情
		
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
		
		if ($this->m_sysPhase == ConstConfigSouth::SYSTEMPHASE_HUAN_3 || $this->m_sysPhase == ConstConfigSouth::SYSTEMPHASE_DING_QUE)
		{
			$data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;								// 当前出牌者

			for ($i=0; $i<$this->m_rule->player_count; $i++)                                         // 玩家手持牌长度
			{
				$data['m_huan_3_type'] = $this->m_huan_3_type;
				
				if($i == $chair)
				{
					$data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
					$data['m_huan_3_arr'][$i] = $this->m_huan_3_arr[$i];
					$data['m_only_out_card'] = $this->m_only_out_card[$i];					
				}
				else
				{
					$data['m_sPlayer'][$i] = (object)null;
					$data['m_huan_3_arr'][$i] = (object)null;
				}
			}

			return $data;
		}

		if ($this->m_sysPhase == ConstConfigSouth::SYSTEMPHASE_THINKING_OUT_CARD)
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
					$data['m_huan_3_arr'][$i] = $this->m_huan_3_arr[$i];
					$data['m_bChooseBuf'] = $this->m_bChooseBuf[$i];			 //命令缓冲
					$data['m_nHuGiveUp'] = $this->m_nHuGiveUp[$i];
					$data['m_only_out_card'] = $this->m_only_out_card[$i];										
				}
				else
				{
					$data['m_sPlayer'][$i] = (object)null;
					$data['m_huan_3_arr'][$i] = (object)null;
				}
			}

			//			if($this->m_chairCurrentPlayer == $chair)
			//			{
			//				//只给当前用户
			//				//$data['m_sCanGang'] = $this->m_sCanGang;           //杠信息
			//			}

			//$data['m_chairSendCmd'] = $this->m_chairSendCmd;                  //发命令的玩家
			//$data['m_currentCmd'] = $this->m_currentCmd;

			return $data;
		}

		if ($this->m_sysPhase == ConstConfigSouth::SYSTEMPHASE_CHOOSING )
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
					$data['m_huan_3_arr'][$i] = $this->m_huan_3_arr[$i];
					$data['m_bChooseBuf'] = $this->m_bChooseBuf[$i];			 //命令缓冲
					$data['m_nHuGiveUp'] = $this->m_nHuGiveUp[$i];
					$data['m_only_out_card'] = $this->m_only_out_card[$i];										
				}
				else
				{
					$data['m_sPlayer'][$i] = (object)null;
					$data['m_huan_3_arr'][$i] = (object)null;
				}
			}

			return $data;
		}
		if ($this->m_sysPhase == ConstConfigSouth::SYSTEMPHASE_SET_OVER )
		{
			$data['m_nEndReason'] = $this->m_nEndReason;										//结束原因
			$data['m_nNumCheat'] = $this->m_nNumCheat;						// 玩家i诈胡次数
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
		if($is_open || $this->m_rule->set_num <= $this->m_nSetCount)
		{
			$this->m_game_type = 1;	//游戏 1 四川血战到底 2 陕西麻将
			$this->m_room_state = ConstConfigSouth::ROOM_STATE_OVER ;	//房间状态
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
		$this->m_nCountHuaZhu = 0;
		$this->m_nCountDajiao = 0;

		$this->m_bTianRenHu = true;
		$this->m_nEndReason = 0;

		$this->m_sQiangGang = new Qiang_gang();
		$this->m_sGangPao = new Gang_pao();
		$this->m_bHaveGang = false;
		//$this->m_nCardLast = 255;
		$this->m_huan_3_type = 0;
		//$this->m_sCanGang = new Gang_suit();
		$this->m_nNumCmdHu = 0;			// 胡命令的个数
		$this->m_chairSendCmd = 255;			// 当前发命令的玩家
		//$this->m_nEatBuf = new Eat_suit();
		$this->m_nCardBuf = array();

		$this->m_bDingQue = true;
		$this->m_nCountAllot = 0;			//还没发牌
		$this->m_sOutedCard = new Outed_card();

		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_SET_OVER ;
		$this->m_chairCurrentPlayer = 255;
		$this->m_currentCmd = 0;			// 当前的命令
		$this->m_end_time = '';
		
		$this->m_cancle_first = 255;

		for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
		{
			$this->m_bChairHu[$i] = false;
			$this->m_nGen[$i] = 0;
			$this->m_nDiHu[$i] = 0;
			$this->m_wHuaZhuScore[$i] = 0;
			$this->m_wDaJiaoScore[$i] = 0;

			$this->m_wGangScore[$i] = array(0,0,0,0);
			$this->m_wDaJiaoScore[$i] = 0;
			$this->m_wHuScore[$i] = 0;
			$this->m_wSetScore[$i] = 0;
			$this->m_wSetLoseScore[$i] = 0;
			$this->m_wGFXYScore[$i] = 0;
			$this->m_Score[$i] = new Score();

			$this->m_cancle[$i] = 0;
			$this->m_sDingQue[$i] = new Ding_que();
			$this->m_huan_3_arr[$i] = new Huan_3();
			$this->m_nTableCards[$i] = array();
			$this->m_sStandCard[$i] = new Stand_card();
			$this->m_sPlayer[$i] = new Play_data();
			$this->m_nNumCheat[$i] = 0;
			$this->m_nNumTableCards[$i] = 0;
			//$this->m_nNumFan[$i] = 0;

			$this->m_bFlee[$i] = 0;
			$this->m_nDajiaoFan[$i] = 0;

			//$this->m_sEat[$i] = 0;
			$this->m_bChooseBuf[$i] = 0;
			$this->m_chairHu[$i] = 0;
			//$this->m_nJiang =  0;
			$this->m_nHuList[$i] = 0;
			$this->m_nHuGiveUp[$i] = 255;
			$this->m_only_out_card[$i] = false;

			$this->m_bMaxFan[$i] = false;
			$this->m_HuCurt[$i] = new Hu_curt();
			$this->m_hu_desc[$i] = '';
		}
	}

	public function DealCard($chair)
	{
		if ($this->m_game_type == 1 && $this->m_bChairHu[$chair])	//血战场找未胡玩家发牌
		{
			if ($this->m_nCountHu >=$this->m_rule->player_count - 1)
			{
				return false;
			}
			$this->m_chairCurrentPlayer = $this->AntiClock($chair);
			return $this->DealCard($this->m_chairCurrentPlayer);
		}

		//$this->m_bTianRenHu = false; //判断天人胡标志
		//$this->m_nDiHu[$chair] = 1;

		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			if ($this->m_sPlayer[$i]->state != ConstConfigSouth::PLAYER_STATUS_BLOOD_HU)
			{
				$this->m_sPlayer[$i]->seen_out_card = 1;		//如无人吃碰杠，则全部可见
				$this->m_sPlayer[$i]->state = ConstConfigSouth::PLAYER_STATUS_WAITING;
				$this->m_sPlayer[$i]->card_taken_now = 0;
			}
		}
		
		if(empty($this->m_nCardBuf[$this->m_nCountAllot]))				//没牌啦
		{
			//echo("没牌啦".__LINE__);
			$this->m_nEndReason = ConstConfigSouth::END_REASON_NOCARD;
			$this->HandleSetOver();
			return true;
		}

		$the_card = $this->m_nCardBuf[$this->m_nCountAllot];
		$this->m_nCountAllot++;

		$this->m_sPlayer[$chair]->card_taken_now = $the_card;

		$this->m_sPlayer[$chair]->state = ConstConfigSouth::PLAYER_STATUS_CHOOSING;
		$this->m_bChooseBuf[$chair] = 1;

		$this->m_nHuGiveUp[$chair] = 255;	//重置过手胡

		return true;
	}

	public function HandleSetOver()
	{
		if($this->m_sysPhase == ConstConfigSouth::SYSTEMPHASE_SET_OVER)
		{
			return false;
		}
		
		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_SET_OVER;
		//m_sOutedCard->clear();

		//Close the expire timer
		//m_pSite ->KillTimer(TIME_ID_EXPIRE);

		if ($this->m_nEndReason == ConstConfigSouth::END_REASON_HU)
		{
			$this->CalcHuScore(); //正常算分，此时无逃跑得失相等
		}
		else if ($this->m_nEndReason==ConstConfigSouth::END_REASON_NOCARD)
		{
			$this->CalcNoCardScore();
		}
		else if($this->m_nEndReason == ConstConfigSouth::END_REASON_FLEE )		//逃跑结算游戏
		{
			//逃跑牌局等待，不结算
			//$this->CalcFleeScore();
		}
		else
		{
			echo(__LINE__."Unknow end reason: ".$this->m_nEndReason);
		}

		//下一局庄家
		if($this->m_nEndReason==ConstConfigSouth::END_REASON_NOCARD && 255 == $this->m_nChairBankerNext)
		{
			$this->m_nChairBankerNext = $this->AntiClock($this->m_nChairBanker, 1);
		}

		//准备状态
		$this->m_ready = array(0,0,0,0);

		//本局结束时间
		$this->m_end_time = date('Y-m-d H:i:s', time());

		//写记录
		$this->WriteScore();

		//状态变化发消息
		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$cmd = new Game_cmd($this->m_room_id, 's_game_over', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}
		
		$this->set_game_and_checkout();		
		
		//最后一局结束时候修改房间状态
		if(empty($this->m_rule) || $this->m_rule->set_num <= $this->m_nSetCount)
		{
			$this->m_room_state = ConstConfigSouth::ROOM_STATE_OVER;
		}
	}

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
			if ($this->m_wSetScore[$i]>0)
			{
				$this->m_Score[$i]->win_count = 1;
			}
			else if ($this->m_wSetLoseScore[$i]<0)
			{
				$this->m_Score[$i]->lose_count = 1;
			}
			else
			{
				$this->m_Score[$i]->lose_count = 1;
			}
		}
	}

	public function ScoreNoCardBloodCal()
	{
		for ($i=0; $i<$this->m_rule->player_count; ++$i)
		{
			if ($this->m_sPlayer[$i]->state == ConstConfigSouth::PLAYER_STATUS_BLOOD_HU)
			{
				///			nCountHu++;
				if ($this->m_bFlee[$i])
				{
					$this->m_nCountFlee++;
				}
			}
			else if ($this->judge_hua_zhu($i))
			{
				$this->m_sPlayer[$i]->state = ConstConfigSouth::PLAYER_STATUS_HUAZHU;
				$this->m_nCountHuaZhu++;

				$this->m_wTotalScore[$i]->n_huazhu += 1;
			}
			else
			{
				if($this->judge_da_jiao($i, $max_fan))
				{
					$this->m_nDajiaoFan[$i] = $max_fan;
					$this->m_HuCurt[$i]->state = ConstConfigSouth::WIN_STATUS_HU_DA_JIAO;
				}
				else
				{
					$this->m_sPlayer[$i]->state = ConstConfigSouth::PLAYER_STATUS_DAJIAO;
					$this->m_nCountDajiao++;

					$this->m_wTotalScore[$i]->n_dajiao += 1;
				}
			}
		}

		if ($this->m_nCountHuaZhu == $this->m_rule->player_count)
		{
			for ($i=0; $i<$this->m_rule->player_count; ++$i)
			{
				$this->m_HuCurt[$i]->state = ConstConfigSouth::WIN_STATUS_NOTHING ;
			}
			return;
		}

		for ($i=0; $i<$this->m_rule->player_count; ++$i)
		{
			if ($this->m_nCountHuaZhu>0)
			{
				//花猪赔所有非花猪人，包括已经胡牌的人，按最大番算
				$top_times = 1<<($this->m_rule->top_fan);
				if ($this->m_sPlayer[$i]->state == ConstConfigSouth::PLAYER_STATUS_HUAZHU)
				{
					$this->m_wHuaZhuScore[$i] -= ConstConfigSouth::SCORE_BASE*$top_times*($this->m_rule->player_count - $this->m_nCountHuaZhu - $this->m_nCountFlee);
				}
				else if(!$this->m_bFlee[$i])
				{
					$this->m_wHuaZhuScore[$i] += ConstConfigSouth::SCORE_BASE*$top_times*$this->m_nCountHuaZhu;
				}
			}

			if ($this->m_nCountDajiao!=0 && $this->m_nCountDajiao!=$this->m_rule->player_count - $this->m_nCountHuaZhu - $this->m_nCountHu)
			{
				//查大叫，只赔给赢大叫的人，已经胡的人不赔
				if ($this->m_HuCurt[$i]->state == ConstConfigSouth::WIN_STATUS_HU_DA_JIAO)
				{
					//$this->m_nNumFan[$i] = $this->m_nDajiaoFan[$i];
					$lScore = ConstConfigSouth::SCORE_BASE*(1<<($this->m_nDajiaoFan[$i]));
					$this->m_wDaJiaoScore[$i] += $lScore*$this->m_nCountDajiao;

					for ($j=0; $j<$this->m_rule->player_count; ++$j)
					{
						if ($this->m_sPlayer[$j]->state == ConstConfigSouth::PLAYER_STATUS_DAJIAO)
						{
							$this->m_wDaJiaoScore[$j] -= $lScore;
						}
					}
				}
			}

			if ($this->m_sPlayer[$i]->state == ConstConfigSouth::PLAYER_STATUS_DAJIAO || $this->m_sPlayer[$i]->state == ConstConfigSouth::PLAYER_STATUS_HUAZHU)		//花猪和大叫退回刮风下雨分
			{
				for ($j = 0; $j<$this->m_rule->player_count ; ++$j)
				{
					//退回刮风下雨,退税
					if ($j!=$i && $this->m_wGangScore[$i][$j] > 0)		//退回大叫玩家i赢玩家j的刮风下雨分
					{
						$this->m_wGangScore[$i][$i] -= $this->m_wGangScore[$i][$j];
						$this->m_wGangScore[$j][$j] += $this->m_wGangScore[$i][$j];

						$this->m_wGangScore[$i][$j] = 0;
					}
				}
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

		if ($this->m_game_type != 1)
		{
			echo("error m_game_type".__LINE__);
			return false;
		}
		else
		{
			$this->ScoreNoCardBloodCal();
		}

		for($i=0; $i<$this->m_rule->player_count; $i++)
		{
			$this->m_Score[$i]->score = $this->m_wSetScore[$i] + $this->m_wSetLoseScore[$i] + $this->m_wHuaZhuScore[$i]
			+ $this->m_wDaJiaoScore[$i] + $this->m_wGangScore[$i][$i];
			$this->m_Score[$i]->set_count = $this->m_nSetCount;

			if ($this->m_wSetScore[$i]>0)
			{
				$this->m_Score[$i]->win_count = 1;
			}
			else if ($this->m_wSetLoseScore<0)
			{
				$this->m_Score[$i]->lose_count = 1;
			}
			else
			{
				$this->m_Score[$i]->lose_count = 1;
			}
		}
	}

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

			if($this->m_wHuaZhuScore[$i] > 0)
			{
				$this->m_hu_desc[$i] .= '查花猪+'.$this->m_wHuaZhuScore[$i].' ';
			}
			else if($this->m_wHuaZhuScore[$i] < 0)
			{
				$this->m_hu_desc[$i] .= '被查花猪'.$this->m_wHuaZhuScore[$i].' ';
			}

			if($this->m_wDaJiaoScore[$i] > 0)
			{
				$this->m_hu_desc[$i] .= '查大叫+'.$this->m_wDaJiaoScore[$i].' ';
			}
			else if($this->m_wDaJiaoScore[$i] < 0)
			{
				$this->m_hu_desc[$i] .= '被查大叫'.$this->m_wDaJiaoScore[$i].' ';
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
		$this->m_nCardBuf = ConstConfigSouth::ALL_CARD;

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
			for($k=0; $k<ConstConfigSouth::BASE_HOLD_CARD_NUM; $k++)
			{
				$temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];	//从牌缓冲区里那张牌
				$card_type = $this->get_card_type($temp_card);
				$card_key = $temp_card%16;
				if(ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID != $card_type)
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
	}

	public function start_huan_3()
	{
		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_HUAN_3;
		$huan_type_arr = array(ConstConfigSouth::HUAN_3_CLOCKWISE , ConstConfigSouth::HUAN_3_ANTICLOCKWISE , ConstConfigSouth::HUAN_3_CROSS );
		if($this->m_rule->player_count <= 3)
		{
			$huan_type_arr = array(ConstConfigSouth::HUAN_3_CLOCKWISE , ConstConfigSouth::HUAN_3_ANTICLOCKWISE);
		}
		shuffle($huan_type_arr);
		$this->m_huan_3_type = $huan_type_arr[0];
		unset($huan_type_arr);
		for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
		{
			$this->m_sPlayer[$i]->state = ConstConfigSouth::PLAYER_STATUS_HUAN3ING;
			//发消息
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}
	}

	public function start_ding_que()
	{
		$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_DING_QUE;
		for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
		{
			$this->m_sPlayer[$i]->state = ConstConfigSouth::PLAYER_STATUS_DINGQUEING;
			//发消息
			$cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
			$cmd->send($this->serv);
			unset($cmd);
		}
	}

	//开始玩
	public function on_start_game()			//游戏开始
	{
		//初始化数据，非首局的时候还要相关处理
		$this->InitData();
		$this->m_nSetCount += 1;		
		
		//发牌
		$this->DealAllCardEx();

		//换3张
		if($this->m_rule->is_change_3)
		{
			$this->start_huan_3();
			return true;
		}

		//定缺
		if ($this->m_bDingQue)
		{
			$this->start_ding_que();
			return true;
		}

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

	private function send_act($cmd, $chair, $card=0)
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
				//空位子和短线用户都算同意结束牌局
				$cancle_count++;
				if( (!empty($this->m_cancle[$i]) && $this->m_cancle[$i] == 1) || empty($this->m_room_players[$i]) || !empty($this->m_room_players[$i]['flee_time']))
				{
					$yes_count++;
				}
			}
			if($this->m_room_state != ConstConfigSouth::ROOM_STATE_GAMEING && !empty($this->m_room_players[$i]['uid']) && $this->m_room_owner == $this->m_room_players[$i]['uid'] && $this->m_cancle[$i] == 1)
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
				$this->m_room_state = ConstConfigSouth::ROOM_STATE_OVER;
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
			$this->m_sysPhase = ConstConfigSouth::SYSTEMPHASE_SET_OVER;
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

	//判断有没有暗杠
	//	public function find_an_gang($chair)
	//	{
	//		$type = 255;
	//		$mark = false;
	//
	//		for ($type = ConstConfigSouth::PAI_TYPE_WAN; $type <= ConstConfigSouth::PAI_TYPE_FENG; $type++)
	//		{
	//			if($this->m_sPlayer[$chair]->card[$type][0] < 4)
	//			{
	//				continue;
	//			}
	//			for ($i = 1; $i <= 9; $i ++)
	//			{
	//				if ( $this->m_sPlayer[$chair]->card[$type][$i] == 4 )
	//				{
	//					$this->m_sCanGang->type[$this->m_sCanGang->num] = ConstConfigSouth::DAO_PAI_TYPE_ANGANG;
	//					$this->m_sCanGang->card[$this->m_sCanGang->num] = $this->get_card_index($type, $i);
	//					$mark = true;
	//					$this->m_sCanGang->num++;
	//				}
	//			}
	//		}
	//		return $mark;
	//	}

	// 判断有没有自己摸的明杠
	//	public function find_wan_gang($chair)
	//	{
	//		$mark = false;
	//		$temp_card = 255;
	//		$card_type = ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID ;
	//
	//		if ( $this->m_sStandCard[$chair]->num == 0 )
	//		{
	//			return false;
	//		}
	//
	//		for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
	//		{
	//			// 找到类型为刻的倒牌
	//			if ( $this->m_sStandCard[$chair]->type[$i] == ConstConfigSouth::DAO_PAI_TYPE_KE )
	//			{
	//				$temp_card = $this->m_sStandCard[$chair]->first_card[$i];
	//				$card_type = $this->get_card_type($temp_card);
	//
	//				if(ConstConfigSouth::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
	//				{
	//					return false;
	//				}
	//
	//				if ( $this->list_find($chair, $temp_card) )
	//				{
	//					$this->m_sCanGang->type[$this->m_sCanGang->num] = ConstConfigSouth::DAO_PAI_TYPE_WANGANG;
	//					$this->m_sCanGang->card[$this->m_sCanGang->num] = $temp_card;
	//					$this->m_sCanGang->num++;
	//					$mark = true;
	//				}
	//			}
	//		}
	//		return $mark;
	//	}

}
