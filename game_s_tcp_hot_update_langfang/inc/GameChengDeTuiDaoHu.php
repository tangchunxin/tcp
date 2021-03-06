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


class GameChengDeTuiDaoHu extends BaseGame
{
    const GAME_TYPE = 242;

	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21;                  // 平胡
	const HU_TYPE_QIDUI = 22;                   // 七对
	const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   // 错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_GANGKAI = 61;             // 杠上开花

	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 1;                 // 直杠 1分
	const M_ANGANG_SCORE = 2;                  // 暗杠 2分
	const M_WANGANG_SCORE = 1;                 // 弯杠 1分

	public static $hu_type_arr = array(
	    self::HU_TYPE_PINGHU => [self::HU_TYPE_PINGHU, 2, '平胡'],
        self::HU_TYPE_QIDUI => [self::HU_TYPE_QIDUI, 4, '七对']
	);

	public static $attached_hu_arr = array(
        self::ATTACHED_HU_GANGKAI => [self::ATTACHED_HU_GANGKAI, 2, '杠上开花'],
	);

    public $robot = array(0,0,0,0);

    public function __construct($serv)
    {
        parent::__construct($serv);
        $this->m_game_type = self::GAME_TYPE;
    }

    public function InitDataSub()
    {
        $this->m_game_type = self::GAME_TYPE;	//游戏类型，见http端协议
    }

    //玩家在线状态
    public function handle_flee_play($is_force = false)
    {
        $is_flee = false;
        foreach ($this->m_room_players as $key => $room_user)
        {
            if($this->robot[$key]==0 && (!$room_user['fd'] || !($this->serv->connection_info($room_user['fd']))))
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

    public function _open_room_sub($params)
    {
        $this->m_rule = new RuleChengDeTuiDaoHu();

        if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3, 4)))
        {
            $params['rule']['player_count'] = 4;
        }

        $params['rule']['min_fan'] = !isset($params['rule']['min_fan']) ? 0 : $params['rule']['min_fan'];
        $params['rule']['top_fan'] = !isset($params['rule']['top_fan']) ? 255 : $params['rule']['top_fan'];
        $params['rule']['is_feng'] = !isset($params['rule']['is_feng']) ? 0 : $params['rule']['is_feng'];

        $this->m_rule->game_type = $params['rule']['game_type'];
        $this->m_rule->player_count = $params['rule']['player_count'];
        $this->m_rule->set_num = $params['rule']['set_num'];
        $this->m_rule->min_fan = $params['rule']['min_fan'];
        $this->m_rule->top_fan = $params['rule']['top_fan'];

        $this->m_rule->is_feng = $params['rule']['is_feng'];
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
            $this->m_room_players[$add_key]['gps'] = $params['gps'];
            $this->m_room_players[$add_key]['score'] = 0;	//录像信息用
            if(!empty($params['robot']))
            {
                $this->robot[$add_key] = 1;
            }
            $this->_log(__CLASS__,__LINE__,'add_key',$add_key);


        }while(false);

        $this->serv->send($fd,  Room::tcp_encode(($return_send)));
        if(0 == $return_send['code'])
        {
            $this->handle_flee_play(true);	//更新断线用户
            $this->_send_cmd('s_join_room', array('m_room_players'=>$this->m_room_players, 'm_ready'=>$this->m_ready), Game_cmd::SCO_ALL_PLAYER );
            if(!empty($params['robot']))
            {
                $this->robot[$add_key] = 1;
                $this->c_ready($fd, $params);
            }
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
                if ($this->robot[$key] ==1 )
                {
                    $this->m_ready[$key] = 1;
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
                        if($this->_is_clocker() && !empty($this->m_rule->cancle_clocker))
                        {
                            $this->m_cancle_time = time();
                        }
                    }
                }
                if ($this->robot[$key]==1)
                {
                    $this->m_cancle[$key] = 1;
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

	//判断胡
	public function judge_hu($chair, $is_fanhun = false)
	{
		//胡牌型
        $hu_type = $this->judge_hu_type($chair);

		if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
		{
			return false;
		}


		//记录在全局数据
		$this->m_HuCurt[$chair]->method[0] = $hu_type;
		$this->m_HuCurt[$chair]->count = 1;

        //抢杠,杠开,杠炮
        if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
        {
            //$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
        }
        else if($this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGKAI);
        }
        else if ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO && $this->m_sGangPao->mark && $this->m_sGangPao->chair != $chair)
        {
            //$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGPAO);
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

        //基本牌型
        if (isset(self::$hu_type_arr[$hu_type])) {
            $fan_sum = self::$hu_type_arr[$hu_type][1];
            $tmp_hu_desc .= self::$hu_type_arr[$hu_type][2] . ' ';
        }

        //附加番
        for ($i = 1; $i < $this->m_HuCurt[$chair]->count; $i++) {
            if (isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]])) {
                $fan_sum *= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
                $tmp_hu_desc .= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2] . ' ';
            }
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

	//胡牌类型判断  没有混的情况
    public function judge_hu_type($chair)
    {
        $jiang_arr = array();
        $qidui_arr = array();

        $bType32 = false;
        $bQiDui = false;

        //手牌
        if ((!empty($this->m_rule->is_feng)) && ($i = ConstConfig::PAI_TYPE_FENG))
        {
            if (0 < $this->m_sPlayer[$chair]->card[$i][0])
            {
                $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));
                if (!isset(ConstConfig::$hu_data_feng[$key]))
                {
                    return self::HU_TYPE_FENGDING_TYPE_INVALID;
                }
                else
                {
                    $hu_list_val = ConstConfig::$hu_data_feng[$key];

                    $qidui_arr[] = $hu_list_val & 64;

                    if (($hu_list_val & 1) == 1)
                    {
                        $jiang_arr[] = $hu_list_val & 32;
                    }
                    else
                    {
                        //非32牌型设置
                        $jiang_arr[] = 32;
                        $jiang_arr[] = 32;
                    }
                }
            }
        }

        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
        {
            if (0 == $this->m_sPlayer[$chair]->card[$i][0])
            {
                continue;
            }
            if (in_array($this->m_sPlayer[$chair]->card[$i][0], array(1, 7, 13)))
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }
            $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

            if (!isset(ConstConfig::$hu_data[$key]))
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }
            else
            {
                $hu_list_val = ConstConfig::$hu_data[$key];

                $qidui_arr[] = $hu_list_val & 64;

                if (($hu_list_val & 1) == 1)
                {
                    $jiang_arr[] = $hu_list_val & 32;
                }
                else
                {
                    //非32牌型设置
                    $jiang_arr[] = 32;
                    $jiang_arr[] = 32;
                }
            }
        }

        //倒牌
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
        {
            $qidui_arr[] = 0;
        }

        //记录根到全局数据
        $bType32 = (32 == array_sum($jiang_arr));
        $bQiDui = !array_keys($qidui_arr, 0);

        ///////////////////////基本牌型的处理///////////////////////////////

        //不是32牌型也不是7对
        if (!$bType32 && !$bQiDui)
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        else if ($bQiDui)
        {
            //七对
            return self::HU_TYPE_QIDUI;
        }
        else
        {
            return self::HU_TYPE_PINGHU;
        }
    }

    public function judge_ting($chair)
    {
        //定义所有牌数组
        $allCard=array(1,2,3,4,5,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41);
        $temp_HuList = array();
        foreach ($allCard as $value)
        {
            if($this->_list_insert($chair, $value))
            {

                $temp_hu_type=$this->judge_hu_type($chair,$value);
                if ($temp_hu_type!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    $temp_HuList[$value]=$temp_hu_type;
                }
                $this->_list_delete($chair, $value);
            }
        }
        return $temp_HuList;
    }

    /*//游戏开始
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

            }
            if ($i != $this->m_nChairBanker)
            {
                $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
            }
        }
        //判断是否能胡
        $is_fanhun = false;
        if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now == $this->m_hun_card)
        {
            $is_fanhun = true;
        }
        //判断是否有胡
        $this->_list_insert($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
        $this->m_HuCurt[$this->m_chairCurrentPlayer]->card = $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now;
        $tmp_c_hu_result = $this->judge_hu($this->m_chairCurrentPlayer, $is_fanhun);
        $this->m_HuCurt[$this->m_chairCurrentPlayer]->clear();
        $this->_list_delete($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
        //能胡
        if($tmp_c_hu_result)
        {
            $this->_clear_choose_buf($this->m_chairCurrentPlayer);
            $this->HandleHuZiMo($this->m_chairCurrentPlayer);
            return;
        }
        //杠
        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
        {
            for ($j = 1; $j <= 9; $j++)
            {
                if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]>3)
                {
                    $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                    $this->HandleChooseAnGang($this->m_chairCurrentPlayer, $this->_get_card_index($i,$j));
                    return;
                }
                if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j] == 3 )
                {
                    if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now == $this->_get_card_index($i,$j))
                    {
                        $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                        $this->HandleChooseAnGang($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                        return;
                    }
                }
            }
        }
        //出牌
        $out_card_array = array();
        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
        {
            for ($j = 1; $j <= 9; $j++)
            {
                if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]>0)
                {
                    for ($k = 1; $k <= $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]; $k++)
                    {
                        $out_card_array[] = $this->_get_card_index($i,$j);
                    }
                }
            }
        }
        $out_card_array[] = $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now;
        shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
        if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
        {
            $is_14=true;
        }
        else
        {
            $is_14=false;
        }
        $out_card = $out_card_array[0];
        //出牌
        $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);

    }*/

    //游戏开始
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
                //$this->_list_insert($i, $this->m_sPlayer[$i]->card_taken_now);
                //$this->m_sPlayer[$i]->card_taken_now = $this->_find_14_card($i);
            }

            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
        }
        $this->handle_flee_play(true);	//更新断线用户

        if ($this->robot[$this->m_nChairBanker]==1)
        {
            //判断是否能胡
            $is_fanhun = false;
            if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now == $this->m_hun_card)
            {
                $is_fanhun = true;
            }
            //判断是否有胡
            $this->_list_insert($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
            $this->m_HuCurt[$this->m_chairCurrentPlayer]->card = $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now;
            $tmp_c_hu_result = $this->judge_hu($this->m_chairCurrentPlayer, $is_fanhun);
            $this->m_HuCurt[$this->m_chairCurrentPlayer]->clear();
            $this->_list_delete($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
            //能胡
            if($tmp_c_hu_result)
            {
                $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                $this->HandleHuZiMo($this->m_chairCurrentPlayer);
                return;
            }
            //杠
            for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
            {
                for ($j = 1; $j <= 9; $j++)
                {
                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]>3)
                    {
                        $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                        $this->HandleChooseAnGang($this->m_chairCurrentPlayer, $this->_get_card_index($i,$j));
                        return;
                    }
                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j] == 3 )
                    {
                        if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now == $this->_get_card_index($i,$j))
                        {
                            $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                            $this->HandleChooseAnGang($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                            return;
                        }
                    }
                }
            }
            //出牌
            $out_card_array = array();
            for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
            {
                for ($j = 1; $j <= 9; $j++)
                {
                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]>0)
                    {
                        for ($k = 1; $k <= $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]; $k++)
                        {
                            $out_card_array[] = $this->_get_card_index($i,$j);
                        }
                    }
                }
            }
            $out_card_array[] = $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now;
            shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
            if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
            {
                $is_14=true;
            }
            else
            {
                $is_14=false;
            }
            $out_card = $out_card_array[0];
            //出牌
            $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);

        }

    }

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
		//$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
		
		$this->m_sGangPao->clear();
		$this->m_only_out_card[$chair] = true;

		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
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
		//$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
		
		$this->m_eat_num = 0;
		$this->m_sGangPao->clear();
		$this->m_only_out_card[$chair] = true;

		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
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

		$this->_set_record_game(ConstConfig::RECORD_ANGANG, $chair, $temp_card, $chair);

        $nGangPao = self::M_ANGANG_SCORE;
		$this->m_sGangPao->init_data(true, $gang_card, $chair, ConstConfig::DAO_PAI_TYPE_ANGANG, $nGangPao);

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

		//$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
		}
        if($this->robot[$chair]==1)
        {
            //判断是否能胡
            $is_fanhun = false;
            if($this->m_sPlayer[$chair]->card_taken_now == $this->m_hun_card)
            {
                $is_fanhun = true;
            }
            //判断是否有胡
            $this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now);
            $this->m_HuCurt[$chair]->card = $this->m_sPlayer[$chair]->card_taken_now;
            $tmp_c_hu_result = $this->judge_hu($chair, $is_fanhun);
            $this->m_HuCurt[$chair]->clear();
            $this->_list_delete($chair, $this->m_sPlayer[$chair]->card_taken_now);
            //能胡
            if($tmp_c_hu_result)
            {
                $this->_log(__CLASS__,__LINE__,'能胡','');
                $this->_clear_choose_buf($chair);
                $this->HandleHuZiMo($chair);
                return;
            }
            //杠
            for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
            {
                for ($j = 1; $j <= 9; $j++)
                {
                    if ($this->m_sPlayer[$chair]->card[$i][$j]>3)
                    {
                        $this->_log(__CLASS__,__LINE__,'能暗杠','');
                        $this->_clear_choose_buf($chair);
                        $this->HandleChooseAnGang($chair, $this->_get_card_index($i,$j));
                        return;
                    }
                    if ($this->m_sPlayer[$chair]->card[$i][$j] == 3 )
                    {
                        if($this->m_sPlayer[$chair]->card_taken_now == $this->_get_card_index($i,$j))
                        {
                            $this->_log(__CLASS__,__LINE__,'能暗杠','');
                            $this->_clear_choose_buf($chair);
                            $this->HandleChooseAnGang($chair, $this->m_sPlayer[$chair]->card_taken_now);
                            return;
                        }
                    }
                }
            }
            for ($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
            {
                if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE)
                {
                    if ($this->m_sStandCard[$chair]->first_card[$i] == $this->m_sPlayer[$chair]->card_taken_now)
                    {
                        $this->_log(__CLASS__,__LINE__,'能弯杠','');
                        $this->_clear_choose_buf($chair);
                        $this->HandleChooseWanGang($chair, $this->m_sPlayer[$chair]->card_taken_now);
                        return;
                    }
                }
            }
            //出牌(计算单牌)
            $single_card_num = 0;
            $out_card_array = array();
            $this->_list_insert($chair,$this->m_sPlayer[$chair]->card_taken_now);
            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
            {
                if (0 == $this->m_sPlayer[$chair]->card[$i][0])
                {
                    continue;
                }
                for ($j=1;$j<=9;$j++)
                {
                    if ($j==1)
                    {
                        if($this->m_sPlayer[$chair]->card[$i][1] == 1)
                        {
                            if ($this->m_sPlayer[$chair]->card[$i][2]==0 && $this->m_sPlayer[$chair]->card[$i][3]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==2)
                    {
                        if($this->m_sPlayer[$chair]->card[$i][2] == 1)
                        {
                            if ($this->m_sPlayer[$chair]->card[$i][1]==0 && $this->m_sPlayer[$chair]->card[$i][3]==0 && $this->m_sPlayer[$chair]->card[$i][4]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j>=3 && $j<=7)
                    {
                        if($this->m_sPlayer[$chair]->card[$i][$j] == 1)
                        {
                            if ($this->m_sPlayer[$chair]->card[$i][$j-2]==0 && $this->m_sPlayer[$chair]->card[$i][$j-1]==0 && $this->m_sPlayer[$chair]->card[$i][$j+1]==0 && $this->m_sPlayer[$chair]->card[$i][$j+2]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==8)
                    {
                        if ($this->m_sPlayer[$chair]->card[$i][8] == 1)
                        {
                            if ($this->m_sPlayer[$chair]->card[$i][6]==0 && $this->m_sPlayer[$chair]->card[$i][7]==0 && $this->m_sPlayer[$chair]->card[$i][9]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==9)
                    {
                        if($this->m_sPlayer[$chair]->card[$i][9] == 1)
                        {
                            if ($this->m_sPlayer[$chair]->card[$i][8]==0 && $this->m_sPlayer[$chair]->card[$i][7]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                }
            }
            $this->_list_delete($chair,$this->m_sPlayer[$chair]->card_taken_now);
            if ($single_card_num > 0)
            {
                shuffle($out_card_array); shuffle($out_card_array); //随机出牌
                if ($out_card_array[0]==$this->m_sPlayer[$chair]->card_taken_now)
                {
                    $is_14=true;
                }
                else
                {
                    $is_14=false;
                }
                $out_card = $out_card_array[0];
                $this->_log(__CLASS__,__LINE__,'有单牌','');
                //出牌
                $this->HandleOutCard($chair, $is_14, $out_card);
                return;
            }

            //出牌(随机出牌)
            $out_card_array = array();
            for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
            {
                for ($j = 1; $j <= 9; $j++)
                {
                    if ($this->m_sPlayer[$chair]->card[$i][$j]>0)
                    {
                        for ($k = 1; $k <= $this->m_sPlayer[$chair]->card[$i][$j]; $k++)
                        {
                            $out_card_array[] = $this->_get_card_index($i,$j);
                        }
                    }
                }
            }
            $out_card_array[] = $this->m_sPlayer[$chair]->card_taken_now;
            $num = array();
            $this->_list_insert($chair,$this->m_sPlayer[$chair]->card_taken_now);
            foreach ($out_card_array as $del_index)
            {
                $this->_list_delete($chair,$del_index);
                //判断听牌
                $temp_HuList = $this->judge_ting($chair);
                $this->_log(__CLASS__,__LINE__,'听牌',$temp_HuList);

                if(!empty($temp_HuList))
                {
                    foreach ($temp_HuList as $hu_index => $value1)
                    {
                        $num[$del_index] += $this->_list_find($chair,$hu_index);
                        for($i=0; $i<$this->m_rule->player_count; $i++)
                        {
                            foreach ($this->m_nTableCards[$i] as $table_index )
                            {
                                if($table_index==$hu_index)
                                {
                                    $num[$del_index] +=1;
                                }
                            }
                        }
                    }
                    $num[$del_index] = count($temp_HuList) * 4 - $num[$del_index];
                }
                $this->_list_insert($chair,$del_index);
            }
            $this->_list_delete($chair, $this->m_sPlayer[$chair]->card_taken_now);
            $this->_log(__CLASS__,__LINE__,'$num',$num);
            if (!empty($num) && max($num)!=0)
            {
                $card_key = array_search(max($num),$num);
                if ($card_key==$this->m_sPlayer[$chair]->card_taken_now)
                {
                    $is_14=true;
                }
                else
                {
                    $is_14=false;
                }
                $out_card = $card_key;
                $this->_log(__CLASS__,__LINE__,'出听牌','');
                //出牌
                $this->HandleOutCard($chair, $is_14, $out_card);
                return;
            }

            //出牌(计算顺子前和后)
            $single_card_num = 0;
            $out_card_array = array();
            $this->_list_insert($chair,$this->m_sPlayer[$chair]->card_taken_now);
            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
            {
                if (0 == $this->m_sPlayer[$chair]->card[$i][0])
                {
                    continue;
                }
                for ($j=1;$j<=9;$j++)
                {
                    if ($j==1)
                    {
                        if($this->m_sPlayer[$chair]->card[$i][1] == 1)
                        {
                            if ($this->m_sPlayer[$chair]->card[$i][2] == 0 && $this->m_sPlayer[$chair]->card[$i][3] > 0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==2)
                    {
                        if($this->m_sPlayer[$chair]->card[$i][2] == 1)
                        {
                            if ($this->m_sPlayer[$chair]->card[$i][1]==0 && $this->m_sPlayer[$chair]->card[$i][3]==0 && $this->m_sPlayer[$chair]->card[$i][4] > 0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j>=3 && $j<=7)
                    {
                        if($this->m_sPlayer[$chair]->card[$i][$j] == 1)
                        {
                            if ($this->m_sPlayer[$chair]->card[$i][$j-2] > 0 && $this->m_sPlayer[$chair]->card[$i][$j-1]==0 && $this->m_sPlayer[$chair]->card[$i][$j+1]==0 && $this->m_sPlayer[$chair]->card[$i][$j+2]>0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==8)
                    {
                        if ($this->m_sPlayer[$chair]->card[$i][8] == 1)
                        {
                            if ($this->m_sPlayer[$chair]->card[$i][6]>0 && $this->m_sPlayer[$chair]->card[$i][7]==0 && $this->m_sPlayer[$chair]->card[$i][9]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==9)
                    {
                        if($this->m_sPlayer[$chair]->card[$i][9] == 1)
                        {
                            if ($this->m_sPlayer[$chair]->card[$i][8]==0 && $this->m_sPlayer[$chair]->card[$i][7]>0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                }
            }
            $this->_list_delete($chair,$this->m_sPlayer[$chair]->card_taken_now);
            if ($single_card_num > 0)
            {
                shuffle($out_card_array); shuffle($out_card_array); //随机出牌
                if ($out_card_array[0]==$this->m_sPlayer[$chair]->card_taken_now)
                {
                    $is_14=true;
                }
                else
                {
                    $is_14=false;
                }
                $out_card = $out_card_array[0];
                $this->_log(__CLASS__,__LINE__,'顺子前后','');
                //出牌
                $this->HandleOutCard($chair, $is_14, $out_card);
                return;
            }
            //删除单张
            $single_card_num = 0;
            $out_card_array = array();
            $this->_list_insert($chair,$this->m_sPlayer[$chair]->card_taken_now);
            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
            {
                if (0 == $this->m_sPlayer[$chair]->card[$i][0])
                {
                    continue;
                }
                for ($j=1;$j<=9;$j++)
                {
                    if($this->m_sPlayer[$chair]->card[$i][$j] == 1)
                    {
                        $single_card_num +=1;
                        $out_card_array[] = $this->_get_card_index($i,$j);
                    }
                }
            }
            $this->_list_delete($chair,$this->m_sPlayer[$chair]->card_taken_now);
            if ($single_card_num > 0)
            {
                shuffle($out_card_array); shuffle($out_card_array); //随机出牌
                if ($out_card_array[0]==$this->m_sPlayer[$chair]->card_taken_now)
                {
                    $is_14=true;
                }
                else
                {
                    $is_14=false;
                }
                $out_card = $out_card_array[0];
                $this->_log(__CLASS__,__LINE__,'单张','');
                //出牌
                $this->HandleOutCard($chair, $is_14, $out_card);
                return;
            }

            //出牌(随机出牌)
            $out_card_array = array();
            for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
            {
                for ($j = 1; $j <= 9; $j++)
                {
                    if ($this->m_sPlayer[$chair]->card[$i][$j]>0)
                    {
                        for ($k = 1; $k <= $this->m_sPlayer[$chair]->card[$i][$j]; $k++)
                        {
                            $out_card_array[] = $this->_get_card_index($i,$j);
                        }
                    }
                }
            }
            $out_card_array[] = $this->m_sPlayer[$chair]->card_taken_now;
            shuffle($out_card_array); shuffle($out_card_array); //随机出牌
            if ($out_card_array[0]==$this->m_sPlayer[$chair]->card_taken_now)
            {
                $is_14=true;
            }
            else
            {
                $is_14=false;
            }
            $out_card = $out_card_array[0];
            $this->_log(__CLASS__,__LINE__,'随机出牌','');
            //出牌
            $this->HandleOutCard($chair, $is_14, $out_card);

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

		$this->_set_record_game(ConstConfig::RECORD_ZHIGANG, $chair, $temp_card, $this->m_sOutedCard->chair);

        $nGangPao = self::M_ZHIGANG_SCORE;
		$this->m_sGangPao->init_data(true, $temp_card, $chair,ConstConfig::DAO_PAI_TYPE_MINGGANG, $nGangPao);

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
		//$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);
		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
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
		//$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄

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
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
		}
		
		$this->m_chairSendCmd = 255;							// 当前发命令的玩家
		$this->m_currentCmd = 0;							    // 当前的命令

        //弯杠自动出牌
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

				//if ($this->m_game_type == self::GAME_TYPE)
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
					$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
				}
			}
		}
	}

	//处理出牌 
	public function HandleOutCard($chair, $is_14 = false, $out_card = 0,$is_ting = 1)
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

            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair_next), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$chair_next]['uid']);
        }

        $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$chair]['uid']);

        $chair_next = $chair;
        for ( $i=0; $i < $this->m_rule->player_count - 1; $i++)
        {
            $chair_next = $this->_anti_clock($chair_next);
            if($this->robot[$chair_next]==1)
            {
                //判断是否能胡
                $is_fanhun = false;
                if($this->m_sPlayer[$chair_next]->card_taken_now == $this->m_hun_card)
                {
                    $is_fanhun = true;
                }
                //判断是否有胡
                $this->_list_insert($chair_next, $this->m_sOutedCard->card);
                $this->m_HuCurt[$chair_next]->card = $this->m_sOutedCard->card;
                $tmp_c_hu_result = $this->judge_hu($chair_next, $is_fanhun);
                $this->m_HuCurt[$chair_next]->clear();
                $this->_list_delete($chair_next, $this->m_sOutedCard->card);
                //能胡
                if($tmp_c_hu_result)
                {
                    $this->_log(__CLASS__,__LINE__,'能胡','');
                    $this->_clear_choose_buf($chair_next);
                    $this->HandleChooseResult($chair_next,'c_hu');
                }
                //杠
                for ($j = ConstConfig::PAI_TYPE_WAN; $j <= ConstConfig::PAI_TYPE_TONG; $j++)
                {
                    for ($k = 1; $k <= 9; $k++)
                    {
                        if ($this->m_sPlayer[$chair_next]->card[$j][$k] == 3 )
                        {
                            if($this->m_sOutedCard->card == $this->_get_card_index($j,$k))
                            {
                                $this->_log(__CLASS__,__LINE__,'能直杠','');
                                $this->_clear_choose_buf($chair_next);
                                $this->HandleChooseResult($chair_next,'c_zhigang');
                            }
                        }
                    }
                }
                for ($j = ConstConfig::PAI_TYPE_WAN; $j <= ConstConfig::PAI_TYPE_TONG; $j++)
                {
                    for ($k = 1; $k <= 9; $k++)
                    {
                        if ($this->m_sPlayer[$chair_next]->card[$j][$k] == 2 )
                        {
                            if($this->m_sOutedCard->card == $this->_get_card_index($j,$k))
                            {
                                $this->_log(__CLASS__,__LINE__,'能碰','');
                                $this->_clear_choose_buf($chair_next);
                                $this->HandleChooseResult($chair_next,'c_peng');
                            }
                        }
                    }
                }
                $this->_log(__CLASS__,__LINE__,'没有操作','');
                //没有操作
                $this->_clear_choose_buf($chair_next, false);
                $this->HandleChooseResult($chair_next, 'c_cancle_choice');

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

				//if ($this->m_game_type == self::GAME_TYPE)
				{
					$this->m_nEndReason = ConstConfig::END_REASON_HU;
					$this->HandleSetOver();
					return;
				}
			}
			else // 给杠的玩家补张
			{
				$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
				$this->m_chairCurrentPlayer = $this->m_sQiangGang->chair;

				$this->m_bHaveGang = true; //for 杠上花

                $nGangPao = self::M_WANGANG_SCORE;
				$this->m_sGangPao->init_data(true, $this->m_sQiangGang->card, $this->m_sQiangGang->chair, ConstConfig::DAO_PAI_TYPE_WANGANG, $nGangPao);
			
				$this->_set_record_game(ConstConfig::RECORD_ZHUANGANG, $this->m_sQiangGang->chair, $this->m_sQiangGang->card, $this->m_sQiangGang->chair);

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
					$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
				}
				
				$this->m_sQiangGang->clear();

                if($this->robot[$this->m_chairCurrentPlayer]==1)
                {
                    //判断是否能胡
                    $is_fanhun = false;
                    if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now == $this->m_hun_card)
                    {
                        $is_fanhun = true;
                    }
                    //判断是否有胡
                    $this->_list_insert($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                    $this->m_HuCurt[$this->m_chairCurrentPlayer]->card = $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now;
                    $tmp_c_hu_result = $this->judge_hu($this->m_chairCurrentPlayer, $is_fanhun);
                    $this->m_HuCurt[$this->m_chairCurrentPlayer]->clear();
                    $this->_list_delete($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                    //能胡
                    if($tmp_c_hu_result)
                    {
                        $this->_log(__CLASS__,__LINE__,'能胡','');
                        $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                        $this->HandleHuZiMo($this->m_chairCurrentPlayer);
                        return;
                    }
                    //杠
                    for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
                    {
                        for ($j = 1; $j <= 9; $j++)
                        {
                            if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]>3)
                            {
                                $this->_log(__CLASS__,__LINE__,'能暗杠','');
                                $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                                $this->HandleChooseAnGang($this->m_chairCurrentPlayer, $this->_get_card_index($i,$j));
                                return;
                            }
                            if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j] == 3 )
                            {
                                if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now == $this->_get_card_index($i,$j))
                                {
                                    $this->_log(__CLASS__,__LINE__,'能暗杠','');
                                    $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                                    $this->HandleChooseAnGang($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                                    return;
                                }
                            }
                        }
                    }
                    for ($i=0; $i<$this->m_sStandCard[$this->m_chairCurrentPlayer]->num; $i++)
                    {
                        if ($this->m_sStandCard[$this->m_chairCurrentPlayer]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE)
                        {
                            if ($this->m_sStandCard[$this->m_chairCurrentPlayer]->first_card[$i] == $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                            {
                                $this->_log(__CLASS__,__LINE__,'能弯杠','');
                                $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                                $this->HandleChooseWanGang($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                                return;
                            }
                        }
                    }
                    //出牌(计算单牌)
                    $single_card_num = 0;
                    $out_card_array = array();
                    $this->_list_insert($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                    for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
                    {
                        if (0 == $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][0])
                        {
                            continue;
                        }
                        for ($j=1;$j<=9;$j++)
                        {
                            if ($j==1)
                            {
                                if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][1] == 1)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][2]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][3]==0)
                                    {
                                        $single_card_num +=1;
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                            if ($j==2)
                            {
                                if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][2] == 1)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][3]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][4]==0)
                                    {
                                        $single_card_num +=1;
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                            if ($j>=3 && $j<=7)
                            {
                                if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j] == 1)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j-2]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j-1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j+1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j+2]==0)
                                    {
                                        $single_card_num +=1;
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                            if ($j==8)
                            {
                                if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][8] == 1)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][6]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][7]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][9]==0)
                                    {
                                        $single_card_num +=1;
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                            if ($j==9)
                            {
                                if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][9] == 1)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][8]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][7]==0)
                                    {
                                        $single_card_num +=1;
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                        }
                    }
                    $this->_list_delete($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                    if ($single_card_num > 0)
                    {
                        shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
                        if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                        {
                            $is_14=true;
                        }
                        else
                        {
                            $is_14=false;
                        }
                        $out_card = $out_card_array[0];
                        $this->_log(__CLASS__,__LINE__,'有单牌','');
                        //出牌
                        $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);
                        return;
                    }

                    //出牌(随机出牌)
                    $out_card_array = array();
                    for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
                    {
                        for ($j = 1; $j <= 9; $j++)
                        {
                            if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]>0)
                            {
                                for ($k = 1; $k <= $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]; $k++)
                                {
                                    $out_card_array[] = $this->_get_card_index($i,$j);
                                }
                            }
                        }
                    }
                    $out_card_array[] = $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now;
                    $num = array();
                    $this->_list_insert($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                    foreach ($out_card_array as $del_index)
                    {
                        $this->_list_delete($this->m_chairCurrentPlayer,$del_index);
                        //判断听牌
                        $temp_HuList = $this->judge_ting($this->m_chairCurrentPlayer);
                        $this->_log(__CLASS__,__LINE__,'听牌',$temp_HuList);

                        if(!empty($temp_HuList))
                        {
                            foreach ($temp_HuList as $hu_index => $value1)
                            {
                                $num[$del_index] += $this->_list_find($this->m_chairCurrentPlayer,$hu_index);
                                for($i=0; $i<$this->m_rule->player_count; $i++)
                                {
                                    foreach ($this->m_nTableCards[$i] as $table_index )
                                    {
                                        if($table_index==$hu_index)
                                        {
                                            $num[$del_index] +=1;
                                        }
                                    }
                                }
                            }
                            $num[$del_index] = count($temp_HuList) * 4 - $num[$del_index];
                        }
                        $this->_list_insert($this->m_chairCurrentPlayer,$del_index);
                    }
                    $this->_list_delete($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                    $this->_log(__CLASS__,__LINE__,'$num',$num);
                    if (!empty($num) && max($num)!=0)
                    {
                        $card_key = array_search(max($num),$num);
                        if ($card_key==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                        {
                            $is_14=true;
                        }
                        else
                        {
                            $is_14=false;
                        }
                        $out_card = $card_key;
                        $this->_log(__CLASS__,__LINE__,'出听牌','');
                        //出牌
                        $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);
                        return;
                    }

                    //出牌(计算顺子前和后)
                    $single_card_num = 0;
                    $out_card_array = array();
                    $this->_list_insert($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                    for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
                    {
                        if (0 == $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][0])
                        {
                            continue;
                        }
                        for ($j=1;$j<=9;$j++)
                        {
                            if ($j==1)
                            {
                                if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][1] == 1)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][2] == 0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][3] > 0)
                                    {
                                        $single_card_num +=1;
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                            if ($j==2)
                            {
                                if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][2] == 1)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][3]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][4] > 0)
                                    {
                                        $single_card_num +=1;
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                            if ($j>=3 && $j<=7)
                            {
                                if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j] == 1)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j-2] > 0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j-1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j+1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j+2]>0)
                                    {
                                        $single_card_num +=1;
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                            if ($j==8)
                            {
                                if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][8] == 1)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][6]>0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][7]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][9]==0)
                                    {
                                        $single_card_num +=1;
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                            if ($j==9)
                            {
                                if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][9] == 1)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][8]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][7]>0)
                                    {
                                        $single_card_num +=1;
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                        }
                    }
                    $this->_list_delete($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                    if ($single_card_num > 0)
                    {
                        shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
                        if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                        {
                            $is_14=true;
                        }
                        else
                        {
                            $is_14=false;
                        }
                        $out_card = $out_card_array[0];
                        $this->_log(__CLASS__,__LINE__,'顺子前后','');
                        //出牌
                        $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);
                        return;
                    }
                    //删除单张
                    $single_card_num = 0;
                    $out_card_array = array();
                    $this->_list_insert($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                    for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
                    {
                        if (0 == $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][0])
                        {
                            continue;
                        }
                        for ($j=1;$j<=9;$j++)
                        {
                            if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j] == 1)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    $this->_list_delete($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                    if ($single_card_num > 0)
                    {
                        shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
                        if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                        {
                            $is_14=true;
                        }
                        else
                        {
                            $is_14=false;
                        }
                        $out_card = $out_card_array[0];
                        $this->_log(__CLASS__,__LINE__,'单张','');
                        //出牌
                        $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);
                        return;
                    }

                    //出牌(随机出牌)
                    $out_card_array = array();
                    for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
                    {
                        for ($j = 1; $j <= 9; $j++)
                        {
                            if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]>0)
                            {
                                for ($k = 1; $k <= $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]; $k++)
                                {
                                    $out_card_array[] = $this->_get_card_index($i,$j);
                                }
                            }
                        }
                    }
                    $out_card_array[] = $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now;
                    shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
                    if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                    {
                        $is_14=true;
                    }
                    else
                    {
                        $is_14=false;
                    }
                    $out_card = $out_card_array[0];
                    $this->_log(__CLASS__,__LINE__,'随机出牌','');
                    //出牌
                    $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);

                }

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

				//if ($this->m_game_type == self::GAME_TYPE)
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
						$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
					}
                    if($this->robot[$this->m_chairCurrentPlayer]==1)
                    {
                        //判断是否能胡
                        $is_fanhun = false;
                        if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now == $this->m_hun_card)
                        {
                            $is_fanhun = true;
                        }
                        //判断是否有胡
                        $this->_list_insert($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                        $this->m_HuCurt[$this->m_chairCurrentPlayer]->card = $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now;
                        $tmp_c_hu_result = $this->judge_hu($this->m_chairCurrentPlayer, $is_fanhun);
                        $this->m_HuCurt[$this->m_chairCurrentPlayer]->clear();
                        $this->_list_delete($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                        //能胡
                        if($tmp_c_hu_result)
                        {
                            $this->_log(__CLASS__,__LINE__,'能胡','');
                            $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                            $this->HandleHuZiMo($this->m_chairCurrentPlayer);
                            return;
                        }
                        //杠
                        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
                        {
                            for ($j = 1; $j <= 9; $j++)
                            {
                                if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]>3)
                                {
                                    $this->_log(__CLASS__,__LINE__,'能暗杠','');
                                    $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                                    $this->HandleChooseAnGang($this->m_chairCurrentPlayer, $this->_get_card_index($i,$j));
                                    return;
                                }
                                if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j] == 3 )
                                {
                                    if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now == $this->_get_card_index($i,$j))
                                    {
                                        $this->_log(__CLASS__,__LINE__,'能暗杠','');
                                        $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                                        $this->HandleChooseAnGang($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                                        return;
                                    }
                                }
                            }
                        }
                        for ($i=0; $i<$this->m_sStandCard[$this->m_chairCurrentPlayer]->num; $i++)
                        {
                            if ($this->m_sStandCard[$this->m_chairCurrentPlayer]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE)
                            {
                                if ($this->m_sStandCard[$this->m_chairCurrentPlayer]->first_card[$i] == $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                                {
                                    $this->_log(__CLASS__,__LINE__,'能弯杠','');
                                    $this->_clear_choose_buf($this->m_chairCurrentPlayer);
                                    $this->HandleChooseWanGang($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                                    return;
                                }
                            }
                        }
                        //出牌(计算单牌)
                        $single_card_num = 0;
                        $out_card_array = array();
                        $this->_list_insert($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                        for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
                        {
                            if (0 == $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][0])
                            {
                                continue;
                            }
                            for ($j=1;$j<=9;$j++)
                            {
                                if ($j==1)
                                {
                                    if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][1] == 1)
                                    {
                                        if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][2]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][3]==0)
                                        {
                                            $single_card_num +=1;
                                            $out_card_array[] = $this->_get_card_index($i,$j);
                                        }
                                    }
                                }
                                if ($j==2)
                                {
                                    if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][2] == 1)
                                    {
                                        if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][3]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][4]==0)
                                        {
                                            $single_card_num +=1;
                                            $out_card_array[] = $this->_get_card_index($i,$j);
                                        }
                                    }
                                }
                                if ($j>=3 && $j<=7)
                                {
                                    if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j] == 1)
                                    {
                                        if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j-2]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j-1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j+1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j+2]==0)
                                        {
                                            $single_card_num +=1;
                                            $out_card_array[] = $this->_get_card_index($i,$j);
                                        }
                                    }
                                }
                                if ($j==8)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][8] == 1)
                                    {
                                        if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][6]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][7]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][9]==0)
                                        {
                                            $single_card_num +=1;
                                            $out_card_array[] = $this->_get_card_index($i,$j);
                                        }
                                    }
                                }
                                if ($j==9)
                                {
                                    if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][9] == 1)
                                    {
                                        if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][8]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][7]==0)
                                        {
                                            $single_card_num +=1;
                                            $out_card_array[] = $this->_get_card_index($i,$j);
                                        }
                                    }
                                }
                            }
                        }
                        $this->_list_delete($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                        if ($single_card_num > 0)
                        {
                            shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
                            if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                            {
                                $is_14=true;
                            }
                            else
                            {
                                $is_14=false;
                            }
                            $out_card = $out_card_array[0];
                            $this->_log(__CLASS__,__LINE__,'有单牌','');
                            //出牌
                            $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);
                            return;
                        }

                        //出牌(随机出牌)
                        $out_card_array = array();
                        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
                        {
                            for ($j = 1; $j <= 9; $j++)
                            {
                                if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]>0)
                                {
                                    for ($k = 1; $k <= $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]; $k++)
                                    {
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                        }
                        $out_card_array[] = $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now;
                        $num = array();
                        $this->_list_insert($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                        foreach ($out_card_array as $del_index)
                        {
                            $this->_list_delete($this->m_chairCurrentPlayer,$del_index);
                            //判断听牌
                            $temp_HuList = $this->judge_ting($this->m_chairCurrentPlayer);
                            $this->_log(__CLASS__,__LINE__,'听牌',$temp_HuList);

                            if(!empty($temp_HuList))
                            {
                                foreach ($temp_HuList as $hu_index => $value1)
                                {
                                    $num[$del_index] += $this->_list_find($this->m_chairCurrentPlayer,$hu_index);
                                    for($i=0; $i<$this->m_rule->player_count; $i++)
                                    {
                                        foreach ($this->m_nTableCards[$i] as $table_index )
                                        {
                                            if($table_index==$hu_index)
                                            {
                                                $num[$del_index] +=1;
                                            }
                                        }
                                    }
                                }
                                $num[$del_index] = count($temp_HuList) * 4 - $num[$del_index];
                            }
                            $this->_list_insert($this->m_chairCurrentPlayer,$del_index);
                        }
                        $this->_list_delete($this->m_chairCurrentPlayer, $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                        $this->_log(__CLASS__,__LINE__,'$num',$num);
                        if (!empty($num) && max($num)!=0)
                        {
                            $card_key = array_search(max($num),$num);
                            if ($card_key==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                            {
                                $is_14=true;
                            }
                            else
                            {
                                $is_14=false;
                            }
                            $out_card = $card_key;
                            $this->_log(__CLASS__,__LINE__,'出听牌','');
                            //出牌
                            $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);
                            return;
                        }

                        //出牌(计算顺子前和后)
                        $single_card_num = 0;
                        $out_card_array = array();
                        $this->_list_insert($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                        for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
                        {
                            if (0 == $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][0])
                            {
                                continue;
                            }
                            for ($j=1;$j<=9;$j++)
                            {
                                if ($j==1)
                                {
                                    if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][1] == 1)
                                    {
                                        if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][2] == 0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][3] > 0)
                                        {
                                            $single_card_num +=1;
                                            $out_card_array[] = $this->_get_card_index($i,$j);
                                        }
                                    }
                                }
                                if ($j==2)
                                {
                                    if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][2] == 1)
                                    {
                                        if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][3]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][4] > 0)
                                        {
                                            $single_card_num +=1;
                                            $out_card_array[] = $this->_get_card_index($i,$j);
                                        }
                                    }
                                }
                                if ($j>=3 && $j<=7)
                                {
                                    if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j] == 1)
                                    {
                                        if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j-2] > 0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j-1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j+1]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j+2]>0)
                                        {
                                            $single_card_num +=1;
                                            $out_card_array[] = $this->_get_card_index($i,$j);
                                        }
                                    }
                                }
                                if ($j==8)
                                {
                                    if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][8] == 1)
                                    {
                                        if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][6]>0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][7]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][9]==0)
                                        {
                                            $single_card_num +=1;
                                            $out_card_array[] = $this->_get_card_index($i,$j);
                                        }
                                    }
                                }
                                if ($j==9)
                                {
                                    if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][9] == 1)
                                    {
                                        if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][8]==0 && $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][7]>0)
                                        {
                                            $single_card_num +=1;
                                            $out_card_array[] = $this->_get_card_index($i,$j);
                                        }
                                    }
                                }
                            }
                        }
                        $this->_list_delete($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                        if ($single_card_num > 0)
                        {
                            shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
                            if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                            {
                                $is_14=true;
                            }
                            else
                            {
                                $is_14=false;
                            }
                            $out_card = $out_card_array[0];
                            $this->_log(__CLASS__,__LINE__,'顺子前后','');
                            //出牌
                            $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);
                            return;
                        }
                        //删除单张
                        $single_card_num = 0;
                        $out_card_array = array();
                        $this->_list_insert($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                        for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
                        {
                            if (0 == $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][0])
                            {
                                continue;
                            }
                            for ($j=1;$j<=9;$j++)
                            {
                                if($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j] == 1)
                                {
                                    $single_card_num +=1;
                                    $out_card_array[] = $this->_get_card_index($i,$j);
                                }
                            }
                        }
                        $this->_list_delete($this->m_chairCurrentPlayer,$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now);
                        if ($single_card_num > 0)
                        {
                            shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
                            if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                            {
                                $is_14=true;
                            }
                            else
                            {
                                $is_14=false;
                            }
                            $out_card = $out_card_array[0];
                            $this->_log(__CLASS__,__LINE__,'单张','');
                            //出牌
                            $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);
                            return;
                        }

                        //出牌(随机出牌)
                        $out_card_array = array();
                        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
                        {
                            for ($j = 1; $j <= 9; $j++)
                            {
                                if ($this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]>0)
                                {
                                    for ($k = 1; $k <= $this->m_sPlayer[$this->m_chairCurrentPlayer]->card[$i][$j]; $k++)
                                    {
                                        $out_card_array[] = $this->_get_card_index($i,$j);
                                    }
                                }
                            }
                        }
                        $out_card_array[] = $this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now;
                        shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
                        if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairCurrentPlayer]->card_taken_now)
                        {
                            $is_14=true;
                        }
                        else
                        {
                            $is_14=false;
                        }
                        $out_card = $out_card_array[0];
                        $this->_log(__CLASS__,__LINE__,'随机出牌','');
                        //出牌
                        $this->HandleOutCard($this->m_chairCurrentPlayer, $is_14, $out_card);

                    }
					break;
			}
		}

		$this->m_nNumCmdHu = 0;
		$this->m_chairHu = array();
        if($this->robot[$this->m_chairSendCmd]==1)
        {
            //出牌(计算单牌)
            $single_card_num = 0;
            $out_card_array = array();
            $this->_list_insert($this->m_chairSendCmd,$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now);
            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
            {
                if (0 == $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][0])
                {
                    continue;
                }
                for ($j=1;$j<=9;$j++)
                {
                    if ($j==1)
                    {
                        if($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][1] == 1)
                        {
                            if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][2]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][3]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==2)
                    {
                        if($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][2] == 1)
                        {
                            if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][1]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][3]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][4]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j>=3 && $j<=7)
                    {
                        if($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j] == 1)
                        {
                            if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j-2]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j-1]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j+1]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j+2]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==8)
                    {
                        if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][8] == 1)
                        {
                            if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][6]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][7]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][9]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==9)
                    {
                        if($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][9] == 1)
                        {
                            if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][8]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][7]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                }
            }
            $this->_list_delete($this->m_chairSendCmd,$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now);
            if ($single_card_num > 0)
            {
                shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
                if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now)
                {
                    $is_14=true;
                }
                else
                {
                    $is_14=false;
                }
                $out_card = $out_card_array[0];
                $this->_log(__CLASS__,__LINE__,'有单牌','');
                //出牌
                $this->HandleOutCard($this->m_chairSendCmd, $is_14, $out_card);
                return;
            }

            //出牌(随机出牌)
            $out_card_array = array();
            for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
            {
                for ($j = 1; $j <= 9; $j++)
                {
                    if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j]>0)
                    {
                        for ($k = 1; $k <= $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j]; $k++)
                        {
                            $out_card_array[] = $this->_get_card_index($i,$j);
                        }
                    }
                }
            }
            $out_card_array[] = $this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now;
            $num = array();
            $this->_list_insert($this->m_chairSendCmd,$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now);
            foreach ($out_card_array as $del_index)
            {
                $this->_list_delete($this->m_chairSendCmd,$del_index);
                //判断听牌
                $temp_HuList = $this->judge_ting($this->m_chairSendCmd);
                $this->_log(__CLASS__,__LINE__,'听牌',$temp_HuList);

                if(!empty($temp_HuList))
                {
                    foreach ($temp_HuList as $hu_index => $value1)
                    {
                        $num[$del_index] += $this->_list_find($this->m_chairSendCmd,$hu_index);
                        for($i=0; $i<$this->m_rule->player_count; $i++)
                        {
                            foreach ($this->m_nTableCards[$i] as $table_index )
                            {
                                if($table_index==$hu_index)
                                {
                                    $num[$del_index] +=1;
                                }
                            }
                        }
                    }
                    $num[$del_index] = count($temp_HuList) * 4 - $num[$del_index];
                }
                $this->_list_insert($this->m_chairSendCmd,$del_index);
            }
            $this->_list_delete($this->m_chairSendCmd, $this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now);
            $this->_log(__CLASS__,__LINE__,'$num',$num);
            if (!empty($num) && max($num)!=0)
            {
                $card_key = array_search(max($num),$num);
                if ($card_key==$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now)
                {
                    $is_14=true;
                }
                else
                {
                    $is_14=false;
                }
                $out_card = $card_key;
                $this->_log(__CLASS__,__LINE__,'出听牌','');
                //出牌
                $this->HandleOutCard($this->m_chairSendCmd, $is_14, $out_card);
                return;
            }

            //出牌(计算顺子前和后)
            $single_card_num = 0;
            $out_card_array = array();
            $this->_list_insert($this->m_chairSendCmd,$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now);
            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
            {
                if (0 == $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][0])
                {
                    continue;
                }
                for ($j=1;$j<=9;$j++)
                {
                    if ($j==1)
                    {
                        if($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][1] == 1)
                        {
                            if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][2] == 0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][3] > 0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==2)
                    {
                        if($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][2] == 1)
                        {
                            if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][1]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][3]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][4] > 0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j>=3 && $j<=7)
                    {
                        if($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j] == 1)
                        {
                            if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j-2] > 0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j-1]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j+1]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j+2]>0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==8)
                    {
                        if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][8] == 1)
                        {
                            if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][6]>0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][7]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][9]==0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                    if ($j==9)
                    {
                        if($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][9] == 1)
                        {
                            if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][8]==0 && $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][7]>0)
                            {
                                $single_card_num +=1;
                                $out_card_array[] = $this->_get_card_index($i,$j);
                            }
                        }
                    }
                }
            }
            $this->_list_delete($this->m_chairSendCmd,$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now);
            if ($single_card_num > 0)
            {
                shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
                if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now)
                {
                    $is_14=true;
                }
                else
                {
                    $is_14=false;
                }
                $out_card = $out_card_array[0];
                $this->_log(__CLASS__,__LINE__,'顺子前后','');
                //出牌
                $this->HandleOutCard($this->m_chairSendCmd, $is_14, $out_card);
                return;
            }
            //删除单张
            $single_card_num = 0;
            $out_card_array = array();
            $this->_list_insert($this->m_chairSendCmd,$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now);
            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
            {
                if (0 == $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][0])
                {
                    continue;
                }
                for ($j=1;$j<=9;$j++)
                {
                    if($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j] == 1)
                    {
                        $single_card_num +=1;
                        $out_card_array[] = $this->_get_card_index($i,$j);
                    }
                }
            }
            $this->_list_delete($this->m_chairSendCmd,$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now);
            if ($single_card_num > 0)
            {
                shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
                if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now)
                {
                    $is_14=true;
                }
                else
                {
                    $is_14=false;
                }
                $out_card = $out_card_array[0];
                $this->_log(__CLASS__,__LINE__,'单张','');
                //出牌
                $this->HandleOutCard($this->m_chairSendCmd, $is_14, $out_card);
                return;
            }

            //出牌(随机出牌)
            $out_card_array = array();
            for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
            {
                for ($j = 1; $j <= 9; $j++)
                {
                    if ($this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j]>0)
                    {
                        for ($k = 1; $k <= $this->m_sPlayer[$this->m_chairSendCmd]->card[$i][$j]; $k++)
                        {
                            $out_card_array[] = $this->_get_card_index($i,$j);
                        }
                    }
                }
            }
            $out_card_array[] = $this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now;
            shuffle($out_card_array); shuffle($out_card_array);	//随机出牌
            if ($out_card_array[0]==$this->m_sPlayer[$this->m_chairSendCmd]->card_taken_now)
            {
                $is_14=true;
            }
            else
            {
                $is_14=false;
            }
            $out_card = $out_card_array[0];
            $this->_log(__CLASS__,__LINE__,'随机出牌','');
            //出牌
            $this->HandleOutCard($this->m_chairSendCmd, $is_14, $out_card);

        }


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

        //杠分处理
        $nGangScore = 0;
        for ($j = 0; $j < $this->m_sStandCard[$chair]->num; $j++)
        {
            if ($this->m_sStandCard[$chair]->type[$j] == ConstConfig::DAO_PAI_TYPE_ANGANG)
            {
                $nGangScore += self::M_ANGANG_SCORE;
                $this->m_wTotalScore[$chair]->n_angang += 1;
            }
            if ($this->m_sStandCard[$chair]->type[$j] == ConstConfig::DAO_PAI_TYPE_MINGGANG)
            {
                $nGangScore += self::M_ZHIGANG_SCORE;
                $this->m_wTotalScore[$chair]->n_zhigang_wangang += 1;
            }
            if ($this->m_sStandCard[$chair]->type[$j] == ConstConfig::DAO_PAI_TYPE_WANGANG)
            {
                $nGangScore += self::M_WANGANG_SCORE;
                $this->m_wTotalScore[$chair]->n_zhigang_wangang += 1;
            }
        }

        if (in_array(self::ATTACHED_HU_GANGKAI, $this->m_HuCurt[$chair]->method))
        {
            $nGangScore = 2 * $nGangScore;
        }

		$this->m_wHuScore = [0,0,0,0];

		if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		{
			for($i = 0; $i < $this->m_rule->player_count; $i++)
			{
				if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
				{
					continue;	//单用户测试需要关掉
				}

				 $banker_fan = 1;	//庄家分
				 if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i)
				 {
				 	$banker_fan = 2;
				 }

				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
				$wWinScore = 0;
				$wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;  //赢的分 加  庄家的分
				
				$this->m_wHuScore[$i] -= $wWinScore;
				$this->m_wHuScore[$chair] += $wWinScore;

				$this->m_wSetLoseScore[$i] -= $wWinScore;
				$this->m_wSetScore[$chair] += $wWinScore;

				$this->m_HuCurt[$chair]->gain_chair[0]++;
				$this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $i;

                //杠分处理
                $this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分
                $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
			}

			return true;
		}
		else if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
		{
            $banker_fan = 1;	//庄家分
            if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
            {
                $banker_fan = 2;
            }

            $PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
            $wWinScore = 0;
            $wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;

            $this->m_wHuScore[$lost_chair] -= $wWinScore;
            $this->m_wHuScore[$chair] += $wWinScore;

            $this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
            $this->m_wSetScore[$chair] += $wWinScore;

            $this->m_HuCurt[$chair]->gain_chair[0] = 1;
            $this->m_HuCurt[$chair]->gain_chair[1]=$lost_chair;

            //杠分处理
            $this->m_wGangScore[$lost_chair][$lost_chair] -= $nGangScore;		//总刮风下雨分
            $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
            $this->m_wGangScore[$chair][$lost_chair] += $nGangScore;			//赢对应玩家刮风下雨分
			return true;
		}
		else
        {
            echo("此人没有胡".__LINE__.__CLASS__);
            return false;
        }
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
            $this->m_Score[$i]->score = $this->m_wSetScore[$i]+ $this->m_wSetLoseScore[$i] + $this->m_wGangScore[$i][$i];
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
			elseif($this->m_wGangScore[$i][$i]<0)
			{
				$this->m_hu_desc[$i] .= '杠分'.$this->m_wGangScore[$i][$i].' ';
			}
		}
	}

    //录像
    public function _set_record_game($act, $param_1 = 0, $param_2 = 0, $param_3 = 0, $param_4 = 0)
    {
        $param_1_tmp = 0;
        $param_3_tmp = 0;
        if(in_array($act, [
            ConstConfig::RECORD_CHI,
            ConstConfig::RECORD_PENG,
            ConstConfig::RECORD_ZHIGANG,
            ConstConfig::RECORD_ANGANG,
            ConstConfig::RECORD_ZHUANGANG,
            ConstConfig::RECORD_HU,
            ConstConfig::RECORD_ZIMO,
            ConstConfig::RECORD_DISCARD,
            ConstConfig::RECORD_DRAW,
            ConstConfig::RECORD_DEALER,
            ConstConfig::RECORD_FANHUN,
            ConstConfig::RECORD_HU_QIANGGANG
        ]))
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

    private function _log($class,$line,$title,$log)
    {
        $str = "类:$class 行号:$line\r\n";
        echo $str;
        var_dump($title);
        var_dump($log);
    }


}


