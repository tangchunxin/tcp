<?php
/**
 * @author xuqiang76@163.com
 * @final 20170520
 */

namespace gf\inc;

use gf\inc\ConstConfig;
use gf\conf\Config;
use gf\inc\Room;
use gf\inc\BaseFunction;
use gf\inc\Game_cmd;
use gf\inc\BaseGame;

class GameXianxian extends BaseGame
{
	const GAME_TYPE = 264;
	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21;                  // 平胡
	const HU_TYPE_QIDUI = 22;                   // 七对
	const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   // 错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_GANGKAI = 61;             // 杠开
	const ATTACHED_HU_QIANGGANG = 62;           // 抢杠
	const ATTACHED_HU_MENQING = 63;				// 门清
	const ATTACHED_HU_YITIAOLONG = 64;          // 一条龙
	const ATTACHED_HU_BIANKADIAO = 65;			// 边卡吊
	const ATTACHED_HU_SUHU = 66;				// 素胡
	const ATTACHED_HU_SUHU_TEMP = 67;			// 素胡(计算分不显示素胡)

	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 1;					// 直杠分
	const M_ANGANG_SCORE = 2;					// 暗杠分
	const M_WANGANG_SCORE = 1;					// 弯杠分

	public static $hu_type_arr = array(
		self::HU_TYPE_PINGHU=>array(self::HU_TYPE_PINGHU, 1, '平胡')
		,self::HU_TYPE_QIDUI=>array(self::HU_TYPE_QIDUI, 2, '七对')
	);

	public static $attached_hu_arr = array(
		self::ATTACHED_HU_GANGKAI=>array(self::ATTACHED_HU_GANGKAI, 0, '杠上花')
		,self::ATTACHED_HU_QIANGGANG=>array(self::ATTACHED_HU_QIANGGANG, 0, '抢杠')
		,self::ATTACHED_HU_MENQING=>array(self::ATTACHED_HU_MENQING, 1, '门清')
		,self::ATTACHED_HU_YITIAOLONG=>array(self::ATTACHED_HU_YITIAOLONG, 2, '一条龙')
		,self::ATTACHED_HU_BIANKADIAO=>array(self::ATTACHED_HU_BIANKADIAO, 1, '边卡吊')
		,self::ATTACHED_HU_SUHU=>array(self::ATTACHED_HU_SUHU, 2, '素胡')
		,self::ATTACHED_HU_SUHU_TEMP=>array(self::ATTACHED_HU_SUHU_TEMP, 2, '')
	);

	public $m_choice_hou;						// 竞争选择后碰
	public $player_score = array(0,0,0,0);                              	
	public $player_cup = array(0,0,0,0);

	///////////////////////方法/////////////////////////
	//构造方法
	public function __construct($serv)
	{
		parent::__construct($serv);
		$this->m_game_type = self::GAME_TYPE;
	}

	public function InitDataSub()
	{
		$this->m_game_type = self::GAME_TYPE;	//游戏类型，见http端协议
		$this->m_choice_hou = 0;
		$this->player_score = array(0,0,0,0);
		$this->player_cup = array(0,0,0,0);
	}

	public function _open_room_sub($params)
	{
        $this->m_rule = new RuleXianXian();

        $params['rule']['game_type'] = isset($params['rule']['game_type']) ? $params['rule']['game_type']: 264;
        $params['rule']['player_count'] = isset($params['rule']['player_count']) ? $params['rule']['player_count']: 4;
        $params['rule']['set_num'] = isset($params['rule']['set_num']) ? $params['rule']['set_num']: 8;
        $params['rule']['min_fan'] = isset($params['rule']['min_fan']) ? $params['rule']['min_fan']: 0;
        $params['rule']['top_fan'] = isset($params['rule']['top_fan']) ? $params['rule']['top_fan']: 255;

        $params['rule']['is_circle'] = isset($params['rule']['is_circle']) ? $params['rule']['is_circle']: 1;
        
        $params['rule']['is_dianpaohu'] = isset($params['rule']['is_dianpaohu']) ? $params['rule']['is_dianpaohu']: 1;
        $params['rule']['is_feng'] = isset($params['rule']['is_feng']) ? $params['rule']['is_feng']: 1;
        $params['rule']['is_yipao_duoxiang'] = isset($params['rule']['is_yipao_duoxiang']) ? $params['rule']['is_yipao_duoxiang']: 0;
        $params['rule']['is_chi'] = isset($params['rule']['is_chi']) ? $params['rule']['is_chi']: 0;
        $params['rule']['is_yitiaolong_fan'] = isset($params['rule']['is_yitiaolong_fan']) ? $params['rule']['is_yitiaolong_fan']: 1;

        $params['rule']['is_ganghua_fan'] = isset($params['rule']['is_ganghua_fan']) ? $params['rule']['is_ganghua_fan']: 1;
        $params['rule']['is_qidui_fan'] = isset($params['rule']['is_qidui_fan']) ? $params['rule']['is_qidui_fan']: 1;
        $params['rule']['is_wangang_1_lose'] = isset($params['rule']['is_wangang_1_lose']) ? $params['rule']['is_wangang_1_lose']: 0;
        $params['rule']['is_dianpao_bao'] = isset($params['rule']['is_dianpao_bao']) ? $params['rule']['is_dianpao_bao']: 1;
        $params['rule']['pay_type'] = isset($params['rule']['pay_type']) ? $params['rule']['pay_type']: 1;

        $params['rule']['cancle_clocker'] = isset($params['rule']['cancle_clocker']) ? $params['rule']['cancle_clocker']: 1;
        $params['rule']['is_menqing_fan'] = isset($params['rule']['is_menqing_fan']) ? $params['rule']['is_menqing_fan']: 1;
        $params['rule']['is_biankadiao'] = isset($params['rule']['is_biankadiao']) ? $params['rule']['is_biankadiao']: 1;
        $params['rule']['is_suhu'] = isset($params['rule']['is_suhu']) ? $params['rule']['is_suhu']: 1;
        $params['rule']['is_fanhun'] = isset($params['rule']['is_fanhun']) ? $params['rule']['is_fanhun']: 1;
        $params['rule']['qg_is_zimo'] = isset($params['rule']['qg_is_zimo']) ? $params['rule']['qg_is_zimo']: 1;

        $params['rule']['is_score_field'] = isset($params['rule']['is_score_field']) ? $params['rule']['is_score_field']: 0;
        $params['rule']['score'] = isset($params['rule']['score']) ? $params['rule']['score']: 0;

        if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3, 4)))
        {
            $params['rule']['player_count'] = 4;
        }

        if(($params['rule']['is_circle']) && ($params['rule']['is_score_field'])) 
        {
            $params['rule']['is_circle'] = 0;
        }

        if(($params['rule']['is_circle']))
        {
            $params['rule']['set_num'] = $params['rule']['is_circle'] * $params['rule']['player_count'];
        }

        ///////////////////////////////////////////////////

        $this->m_rule->game_type = $params['rule']['game_type'];
        $this->m_rule->player_count = $params['rule']['player_count'];
        $this->m_rule->set_num = $params['rule']['set_num'];
        $this->m_rule->min_fan = $params['rule']['min_fan'];
        $this->m_rule->top_fan = $params['rule']['top_fan'];

        $this->m_rule->is_circle = $params['rule']['is_circle'];
        $this->m_rule->is_feng = $params['rule']['is_feng'];
        $this->m_rule->is_dianpaohu = $params['rule']['is_dianpaohu'];
        $this->m_rule->is_yipao_duoxiang = $params['rule']['is_yipao_duoxiang'];
        $this->m_rule->is_chi = $params['rule']['is_chi'];
        $this->m_rule->is_yitiaolong_fan = $params['rule']['is_yitiaolong_fan'];

        $this->m_rule->is_ganghua_fan = $params['rule']['is_ganghua_fan'];
        $this->m_rule->is_qidui_fan = $params['rule']['is_qidui_fan'];
        $this->m_rule->is_wangang_1_lose = $params['rule']['is_wangang_1_lose'];
        $this->m_rule->is_dianpao_bao = $params['rule']['is_dianpao_bao'];
        $this->m_rule->pay_type = $params['rule']['pay_type'];

        $this->m_rule->cancle_clocker = $params['rule']['cancle_clocker'];
        $this->m_rule->is_menqing_fan = $params['rule']['is_menqing_fan'];
        $this->m_rule->is_biankadiao = $params['rule']['is_biankadiao'];
        $this->m_rule->is_suhu = $params['rule']['is_suhu'];
        $this->m_rule->is_fanhun = $params['rule']['is_fanhun'];
        $this->m_rule->qg_is_zimo = $params['rule']['qg_is_zimo'];

        $this->m_rule->is_score_field = $params['rule']['is_score_field'];
        $this->m_rule->score = $params['rule']['score'];

	}

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

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING && !($this->m_sysPhase == ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD && isset($params['hou']) && $params['hou'] == 2))
            {
                $return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {
                	$params['type'] = 0;
                    
                    if (isset($params['hou']))
                    {
                    	$this->m_choice_hou = $params['hou'];
                    }
                    // 碰逻辑
                    if (!isset($params['hou']))
                    {
                    	if (!$this->_find_peng($key))
	                    {
	                        $this->c_cancle_choice($fd, $params);
	                        $return_send['code'] = 4; $return_send['text'] = '当前用户无碰'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
	                    }
	                    if (empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || 2 > $this->_list_find($key,$this->m_sOutedCard->card))
	                    {
	                        $this->c_cancle_choice($fd, $params);
	                        $return_send['code'] = 5; $return_send['text'] = '碰牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
	                    }

	                    $this->_clear_choose_buf($key);
	                    $this->HandleChooseResult($key, $params['act']);
                    }
                    // 明后逻辑 
                    elseif ($params['hou'] == 1)
                    {
                    	if (!$this->_find_hou($key, $params['hou']))
	                    {
	                        $this->c_cancle_choice($fd, $params);
	                        $return_send['code'] = 4; $return_send['text'] = '当前用户无后碰'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
	                    }
	                    if (empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || 2 > $this->_list_find($key,$this->m_sOutedCard->card))
	                    {
	                        $this->c_cancle_choice($fd, $params);
	                        $return_send['code'] = 5; $return_send['text'] = '后碰错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
	                    }

	                    $this->_clear_choose_buf($key);
	                    $this->HandleChooseResult($key, $params['act']);
                    }
                    // 暗后逻辑 
                    elseif ($params['hou'] == 2)
                    {
						if (!$this->_find_hou($key, $params['hou']))
	                    {
	                        $this->c_cancle_choice($fd, $params);
	                        $return_send['code'] = 4; $return_send['text'] = '当前用户无后碰'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
	                    }

	                    $this->_clear_choose_buf($key);
	                    $this->HandleChoosePeng($key);
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
                    $params['type'] = 0;
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
                    //新增
                    if (empty($this->m_rule->is_dianpaohu))
                    {
                        $return_send['code'] = 6; $return_send['text'] = '当前规则不能点炮'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
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

	//--------------------------------------------------------------------------

	//判断胡  
	public function judge_hu($chair, $is_fanhun = false)
	{
		//胡牌型
		$is_yitiaolong = false;
		$is_suhu = false;
		$is_biankadiao = false;
		$is_menqing = false;

		$hu_type = $this->judge_hu_type_fanhun($chair, $is_suhu, $is_yitiaolong, $is_biankadiao, $is_fanhun);

		if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
		{
			return false;
		}

		//记录在全局数据
		$this->m_HuCurt[$chair]->method[0] = $hu_type;
		$this->m_HuCurt[$chair]->count = 1;

		if (!empty($this->m_rule->qg_is_zimo))
		{
			$qg_state = ConstConfig::WIN_STATUS_ZI_MO;
		}
		else
		{
			$qg_state = ConstConfig::WIN_STATUS_CHI_PAO;
		}

		//抢杠杠开杠炮
		if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == $qg_state)	// 处理抢杠
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
		}
		else if(!empty($this->m_rule->is_ganghua_fan) && $this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair)	//杠开
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGKAI);
		}

		//一条龙
		if ($this->m_rule->is_yitiaolong_fan && $is_yitiaolong)
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_YITIAOLONG);			
		}

		//素胡
		if (!empty($this->m_rule->is_suhu) && $is_suhu)
		{
		    if (!empty($this->m_rule->is_fanhun))
            {
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_SUHU);
            }
            else
            {
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_SUHU_TEMP);
            }
		}
		
		//边卡吊
		if (!empty($this->m_rule->is_biankadiao) && $is_biankadiao)
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_BIANKADIAO);
		}

		//门清
		if(!empty($this->m_rule->is_menqing_fan) && $this->_is_menqing($chair))
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_MENQING);
		}

		return true;
	}
	
	//判断基本牌型+附加牌型+庄分
	public function judge_fan($chair)
	{
		$fan_sum = 0;
		$fan_multi = 1;
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

		for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
		{
			if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
			{
				if ($this->m_HuCurt[$chair]->method[$i] == self::ATTACHED_HU_SUHU || $this->m_HuCurt[$chair]->method[$i] == self::ATTACHED_HU_SUHU_TEMP)
				{
					$fan_multi *= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
				}
				else
				{
					$fan_sum += self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
				}

				$tmp_hu_desc .= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
			}
		}

		$fan_sum = $fan_sum * $fan_multi;
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
	public function judge_hu_type_fanhun($chair, &$is_suhu, &$is_yitiaolong, &$is_biankadiao, $is_fanhun = false)
	{
		$fanhun_num = 0;
		$fanhun_type = 255;
		if($this->m_hun_card)
		{
			$fanhun_num = $this->_list_find($chair, $this->m_hun_card);	//手牌翻混个数
			$fanhun_type = $this->_get_card_type($this->m_hun_card);        //翻混牌类型
			$fanhun_card = $this->m_hun_card%16;       //翻混牌			
		}

		$fanhun_num = $is_fanhun ? $fanhun_num - 1 : $fanhun_num;	//打出的牌是否为翻混

		if(0 == $this->m_rule->is_fanhun || 0 >= $fanhun_num)	//规则混子 或者 手牌无混中
		{
			$is_suhu  = true;
			return $this->judge_hu_type($chair, $is_yitiaolong, $is_biankadiao);
		}
		else
		{
			$return_type = self::HU_TYPE_FENGDING_TYPE_INVALID;	

			//7对牌型
			if(!empty($this->m_rule->is_qidui_fan))
			{
				$need_fanhun = 0;	//需要混子个数	
				$hu_qidui = false;
				$gen_count_num = 0;
				$da8zhang_replace_fanhun = array(0,0,0,0);
			 
				if($this->m_sStandCard[$chair]->num == 0)
				{	
					//去掉翻混
					$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;
					for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
					{
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
						//$gen_count_num += intval(($fanhun_num - $need_fanhun)/2);
					}

					if($hu_qidui)
					{
						return self::HU_TYPE_QIDUI;								
					}
				}
			}

			//32牌型
			$is_hu_data = false;
			$is_yitiaolong = false;
			$is_biankadiao = false;
			$max_hu = array('total'=>-1);
			$jiang_judge_arr = array(0=>2,1=>1,2=>0,3=>2,4=>1,5=>0,6=>2,7=>1,8=>0,9=>2,10=>1,11=>0,12=>2,13=>1,14=>0);
			$no_jiang_judge_arr = array(0=>0,1=>2,2=>1,3=>0,4=>2,5=>1,6=>0,7=>2,8=>1,9=>0,10=>2,11=>1,12=>0);

			//去掉翻混
			$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;
			
			// var_dump("手牌数据");
			// var_dump("changdu0:".$this->m_sPlayer[$chair]->len);
			// var_dump($this->m_sPlayer[$chair]->card);
			// $jishu = 0;
			
			for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
			{
				if(0 == $this->m_sPlayer[$chair]->card[$i][0] || (0 == $this->m_sPlayer[$chair]->card[$i][0]-$fanhun_num && $i == $fanhun_type && $this->m_sPlayer[$chair]->len > $fanhun_num))
				{
					continue;
				}
				
				$is_hu_data = false;
				$jiang_type = $i;	//假设将牌是某一门
				$need_fanhun = 0;	//需要混个数
				$replace_fanhun = array(0,0,0,0);

				for($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
				{
					$pai_num = $this->m_sPlayer[$chair]->card[$j][0];	//一门牌个数
					$pai_num = ($j == $fanhun_type) ? $pai_num - $fanhun_num : $pai_num;	//混牌的牌型个数得减去混牌个数
					if($pai_num == 0)
					{
						continue;
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
					}
				}
				
				// var_dump("changdu2:".$this->m_sPlayer[$chair]->len);
				// $jishu++;
				// var_dump("i循环第".$jishu."次，需要翻混数量".$need_fanhun." ");
				// var_dump($replace_fanhun);

				if($need_fanhun <= $fanhun_num)
				{
					$is_check_hu = false;
					for($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
					{
						if($fanhun_num == $need_fanhun && $is_check_hu)
						{
							continue;
						}
						$is_check_hu = true;

						$tmp_replace_fanhun = $replace_fanhun;
						$tmp_replace_fanhun[$j] += ($fanhun_num - $need_fanhun);
						$max_type_hu_arr = array('total'=>-1, 'yitiaolong' => 0, 'biankadiao' => 0);

						//校验胡
						foreach ($tmp_replace_fanhun as $type => $num)
						{
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

                            $is_hu_data = false; // 这一门牌的胡牌标志位
							$tmp_type_hu_num = 0;
                            $insert_yitiaolong = $max_type_hu_arr['yitiaolong'];
                            $insert_biankadiao = $max_type_hu_arr['biankadiao'];

							foreach ($tmp_hu_data_insert[$num] as $insert_arr)
							{
								$biankadiao_arr = array();
								foreach ($insert_arr as $insert_item)
								{
									$biankadiao_arr[] = $insert_item;
									$this->m_sPlayer[$chair]->card[$type][$insert_item] += 1;
								}

								$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
								if(isset($tmp_hu_data[$key]) && ($tmp_hu_data[$key] & 1) == 1)
								{
									$is_hu_data = true;
									$tmp_type_hu_num = 0;
									$tmp_type_yitiaolong = 0;
									$tmp_type_biankadiao = 0;

									// 一条龙加分
									if ($insert_yitiaolong || ($this->m_rule->is_yitiaolong_fan && ($tmp_hu_data[$key] & 256) == 256))
                                    {
                                        $tmp_type_yitiaolong = 1;
                                        $tmp_type_hu_num += self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                                    }

                                    // 边卡吊加分
                                    if($insert_biankadiao || ($this->m_rule->is_biankadiao && $this->_is_biankadiao($chair, true, $type, $biankadiao_arr)))
                                    {
                                        $tmp_type_biankadiao = 1;
                                        $tmp_type_hu_num += self::$attached_hu_arr[self::ATTACHED_HU_BIANKADIAO][1];
                                    }

									foreach ($insert_arr as $insert_item)
									{
										$this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
									}

									//算分
                                    if ($tmp_type_hu_num >= $max_type_hu_arr['total'])
                                    {
                                        $max_type_hu_arr['total'] = $tmp_type_hu_num;
                                        $max_type_hu_arr['yitiaolong'] = $tmp_type_yitiaolong;
                                        $max_type_hu_arr['biankadiao'] = $tmp_type_biankadiao;
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
						}

						if($max_type_hu_arr['total'] >= 0)
                        {
                            $tmp_max_hu = $max_type_hu_arr['total'];
                            if($tmp_max_hu > $max_hu['total'])
                            {
                                $max_hu['total'] = $tmp_max_hu;
                                $max_hu['yitiaolong'] = $max_type_hu_arr['yitiaolong'];
                                $max_hu['biankadiao'] = $max_type_hu_arr['biankadiao'];
                            }
                        }

                        if($max_hu['total'] >= self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1] + self::$attached_hu_arr[self::ATTACHED_HU_BIANKADIAO][1])
                        {
                            break 2;
                        }
					}
				}
				else
				{
					continue;
				}
			}

			//添加翻混
   			$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += $fanhun_num;

   			if($max_hu['total'] >= 0)
   			{
   				$is_yitiaolong = $max_hu['yitiaolong'];
   				$is_biankadiao =  $max_hu['biankadiao'];
   				return self::HU_TYPE_PINGHU;
   			}
			return $return_type;
		}
	}

	//胡牌类型判断  没有混的情况
	public function judge_hu_type($chair, &$is_yitiaolong, &$is_biankadiao)
	{
		$jiang_arr = array();
		$qidui_arr = array();
		
		$bType32 = false;
		$bQiDui = false;

		$is_yitiaolong = false;		//一条龙
		$is_biankadiao = false;		//边卡吊

		//倒牌
		for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
		{
			$qidui_arr[] = 0;
		}

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
				$qidui_arr[] = 0;
			}
			else
			{
				$hu_list_val = $tmp_hu_data[$key];
				//1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen
				if($this->m_rule->is_yitiaolong_fan && ($hu_list_val & 256) == 256)//一条龙
				{
					$is_yitiaolong = true;
				}
				$qidui_arr[] = $hu_list_val & 64;

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
		$bQiDui = !array_keys($qidui_arr, 0);

		//基本牌型的处理///////////////////////////////
		if(!$bType32 && !$bQiDui)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}

		if($bQiDui && $this->m_rule->is_qidui_fan )				//判断七对，可能同时是32牌型
		{
			return self::HU_TYPE_QIDUI ;				
		}

		if($bType32)
		{
			//边卡吊
			if($this->m_rule->is_biankadiao)
			{
				$temp = false;
				$this->_list_delete($chair, $this->m_HuCurt[$chair]->card);
                if ($this->_is_danting($chair))
                {
                    $temp = true;
                }
                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
                if ($temp)
                {
                    $is_biankadiao = $this->_is_biankadiao($chair);
                }

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
		if ($this->m_choice_hou == 2)
		{
			$temp_card = $this->m_sPlayer[$chair]->card_taken_now;
		}
		$card_type = $this->_get_card_type($temp_card);

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
		{
			echo("Error HandleChoosePeng ".__LINE__.__CLASS__);
			return false;
		}

		if(($this->_list_find($chair, $temp_card)) >= 2)
		{
			$this->_list_delete($chair, $temp_card);
			$this->_list_delete($chair, $temp_card);
		}
		else
		{
			echo "error HandleChoosePeng".__LINE__.__CLASS__;
			return false;
		}

		// 设置倒牌
		$stand_count = $this->m_sStandCard[$chair]->num;
		$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_KE;
		$this->m_sStandCard[$chair]->first_card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
		$this->m_sStandCard[$chair]->num ++;
		if($this->m_choice_hou == 1)
		{
			$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_MINGHOU;
		}
		if($this->m_choice_hou == 2)
		{
			$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_ANHOU;
			$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $chair;
		}

		// 后碰算分
		if (!empty($this->m_choice_hou))
		{
			for ($i=0; $i<$this->m_rule->player_count; ++$i)
			{
				if ($i == $chair)
				{
					continue;
				}
				if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
				{
					if ($this->m_choice_hou == 2)
					{
						$houpengScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;
					}
					elseif ($this->m_choice_hou == 1)
					{
						$houpengScore = self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;
					}
					$this->m_wGangScore[$i][$i] -= $houpengScore;
					$this->m_wGangScore[$chair][$chair] += $houpengScore;
					$this->m_wGangScore[$chair][$i] += $houpengScore;
				}
			}
		}
		
		// 找出第14张牌
		$card_14 = $this->_find_14_card($chair);
		if(!$card_14)
		{
			echo "error HandleChoosePeng".__LINE__.__CLASS__;
			return false;
		}

		//置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
		if ($this->m_choice_hou != 2)
		{
			--$this->m_nNumTableCards[$this->m_sOutedCard->chair];
			if($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
			{
				unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
			}
		}
		
		$this->m_sPlayer[$chair]->card_taken_now = $card_14;

		if($this->m_choice_hou == 2)
		{
			$this->_set_record_game(ConstConfig::RECORD_PENG_ANHOU, $chair, $temp_card, $chair);
		}
		elseif ($this->m_choice_hou == 1)
		{
			$this->_set_record_game(ConstConfig::RECORD_PENG_MINGHOU, $chair, $temp_card, $this->m_sOutedCard->chair);
		}
		else
		{
			$this->_set_record_game(ConstConfig::RECORD_PENG, $chair, $temp_card, $this->m_sOutedCard->chair);
		}
		$this->m_sOutedCard->clear();

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
		
		if(!empty($this->m_choice_hou))
		{
			$this->m_currentCmd = 'c_peng_hou';
		}
		$this->m_choice_hou = 0;
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

	/*//处理出牌 
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

			if($this->_find_peng($chair_next) 
			 ||	$this->_find_zhi_gang($chair_next) 
			 || (1 == $tmp_distance && !empty($this->m_rule->is_chipai) && ($this->_find_eat($chair_next,1) || $this->_find_eat($chair_next,2) || $this->_find_eat($chair_next,3)))
			 )
			{
				$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair_next), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair_next]['uid']);
			}
			else
			{
				// $is_fanhun = false;
				// if($this->m_sOutedCard->card == $this->m_hun_card)
				// {
				// 	$is_fanhun = true;
				// }
				// //判断是否有胡
				// $this->_list_insert($chair_next, $this->m_sOutedCard->card);
				// $this->m_HuCurt[$chair_next]->card = $this->m_sOutedCard->card;
				// $tmp_c_hu_result = ( $this->m_is_ting_arr[$chair_next] && !(self::is_hu_give_up($this->m_sOutedCard->card, $this->m_nHuGiveUp[$chair_next])) && $this->judge_hu($chair_next, $is_fanhun));
				// $this->m_HuCurt[$chair_next]->clear();
				// $this->_list_delete($chair_next, $this->m_sOutedCard->card);				
				// if($tmp_c_hu_result)
				// {
				// }
				// else
				// {
					$this->m_sPlayer[$chair_next]->state = ConstConfig::PLAYER_STATUS_WAITING;
					$tmp_arr[] = $chair_next;
				// }
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
	}*/

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

		for ($i=0; $i<$this->m_rule->player_count; ++$i)
		{
			if ($i == $chair)
			{
				continue;
			}

			if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
			{
				$nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;

				$this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分
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

		$nGangScore =self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;
		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
			if ($i == $chair)
			{
				continue;
			}
			$this->m_wGangScore[$i][$i] -= $nGangScore;
			$this->m_wGangScore[$chair][$chair] += $nGangScore;
			$this->m_wGangScore[$chair][$i] += $nGangScore;
			$nGangPao += $nGangScore;
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

	// 处理竞争选择
	public function HandleChooseResult($chair, $nCmdID, $eat_num = null)
	{
		$this->handle_flee_play(true);
		
		//处理竞争
		$order_cmd = array('c_cancle_choice'=>0, 'c_eat'=>1, 'c_peng'=>2, 'c_hou'=>2, 'c_zhigang'=>3, 'c_hu'=>4);
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
					&& $this->m_sStandCard[$this->m_sOutedCard->chair]->card[$i] == $this->m_sQiangGang->card)
					{
						$this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] = ConstConfig::DAO_PAI_TYPE_KE; 
						break;
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
				$nGangScore = self::M_WANGANG_SCORE *ConstConfig::SCORE_BASE;

				if(!empty($this->m_rule->is_wangang_1_lose))
				{
					for ($i = 0; $i < $this->m_sStandCard[$this->m_sQiangGang->chair]->num; $i ++)
					{
						if ($this->m_sStandCard[$this->m_sQiangGang->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG 
							&& $this->m_sStandCard[$this->m_sQiangGang->chair]->card[$i] == $this->m_sQiangGang->card)
						{
							$tmp_lose = $this->m_sStandCard[$this->m_sQiangGang->chair]->who_give_me[$i];

							$this->m_wGangScore[$tmp_lose][$tmp_lose] -= $nGangScore;
							$this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
							$this->m_wGangScore[$this->m_sQiangGang->chair][$tmp_lose] += $nGangScore;
							
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

						$this->m_wGangScore[$i][$i] -= $nGangScore;
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
					//$this->_genzhuang_do();
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
		//$data['m_bzz_state'] = $this->m_bzz_state;
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
			$data['player_score'] = $this->player_score;
			$data['player_cup'] = $this->player_cup;
			
			return $data;
		}
		return true;
	}

	//每局个人  +=赢的分  +=输的分  +=庄家 的分
	public function ScoreOneHuCal($chair, &$lost_chair)  
	{
		$fan_sum = $this->judge_fan($chair);  //这个就是  一共多少分
		$PerWinScore = $fan_sum;	

		$wWinScore = 0;
		$this->m_wHuScore = [0,0,0,0];

		if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		{
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
				
				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
				$wWinScore = 0;
				//庄家有庄家分
                if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i)
                {
                    for($j=1; $j<$this->m_HuCurt[$chair]->count; $j++)
                    {
                        if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$j]]))
                        {
                            if ($this->m_HuCurt[$chair]->method[$j] == self::ATTACHED_HU_SUHU || $this->m_HuCurt[$chair]->method[$j] == self::ATTACHED_HU_SUHU_TEMP)
                            {
                                $wWinScore += 2 * ConstConfig::SCORE_BASE * ($PerWinScore+2);
                            }
                        }
                    }
                    if ($wWinScore==0)
                    {
                        $wWinScore += 2 * ConstConfig::SCORE_BASE * ($PerWinScore+1);
                    }
                }
                else
                {
                    $wWinScore += 2 * ConstConfig::SCORE_BASE * $PerWinScore;
                }

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
			//点炮大包（点炮）
			if(!empty($this->m_rule->is_dianpao_bao) && $this->m_rule->is_dianpao_bao == 1)
			{
				for($i = 0; $i < $this->m_rule->player_count; $i++)
				{
					if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
					{
						continue;	//单用户测试需要关掉
					}

					$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
					$wWinScore = 0;
                    //庄家有庄家分
                    if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
                    {
                        for($j=1; $j<$this->m_HuCurt[$chair]->count; $j++)
                        {
                            if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$j]]))
                            {
                                if ($this->m_HuCurt[$chair]->method[$j] == self::ATTACHED_HU_SUHU || $this->m_HuCurt[$chair]->method[$j] == self::ATTACHED_HU_SUHU_TEMP)
                                {
                                    $wWinScore += 1 * ConstConfig::SCORE_BASE * ($PerWinScore+2);
                                }
                            }
                        }
                        if ($wWinScore==0)
                        {
                            $wWinScore += 1 * ConstConfig::SCORE_BASE * ($PerWinScore+1);
                        }
                    }
                    else
                    {
                        $wWinScore += 1 * ConstConfig::SCORE_BASE * $PerWinScore;
                    }

					$wWinScore = $this->_get_max_fan($wWinScore);

					$this->m_wHuScore[$lost_chair] -= $wWinScore;
					$this->m_wHuScore[$chair] += $wWinScore;

					$this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
					$this->m_wSetScore[$chair] += $wWinScore;

					$this->m_HuCurt[$chair]->gain_chair[0]++;
					$this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $lost_chair;
				}
			}
			else if(empty($this->m_rule->is_dianpao_bao) && $this->m_rule->is_dianpao_bao == 0)
			{
				//点炮三家出（发胡）
				for($i = 0; $i < $this->m_rule->player_count; $i++)
				{
					if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
					{
						continue;	//单用户测试需要关掉
					}

					$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
					$wWinScore = 0;
                    if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i)
                    {
                        for($j=1; $j<$this->m_HuCurt[$chair]->count; $j++)
                        {
                            if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$j]]))
                            {
                                if ($this->m_HuCurt[$chair]->method[$j] == self::ATTACHED_HU_SUHU || $this->m_HuCurt[$chair]->method[$j] == self::ATTACHED_HU_SUHU_TEMP)
                                {
                                    $wWinScore += 1 * ConstConfig::SCORE_BASE * ($PerWinScore+2);
                                }
                            }
                        }
                        if ($wWinScore==0)
                        {
                            $wWinScore += 1 * ConstConfig::SCORE_BASE * ($PerWinScore+1);
                        }
                    }
                    else
                    {
                        $wWinScore += 1 * ConstConfig::SCORE_BASE * $PerWinScore;
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

			return true;
		}

		echo("此人没有胡".__LINE__.__CLASS__);
		return false;
	}
	////////////////////////////其他//////////////////////////
	// 边卡吊
	public function _is_biankadiao($chair, $have_hun = false, $rep_type = 0, $bkd_arr = array())
	{
		$is_biankadiao = false;
		$tmp_hu_data = &ConstConfig::$hu_data;
		
		if ($this->m_HuCurt[$chair]->card != $this->m_hun_card)
		{
			$card_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
			$card_index = $this->m_HuCurt[$chair]->card % 16;
			if ($card_type == ConstConfig::PAI_TYPE_FENG)
			{
				$tmp_hu_data = &ConstConfig::$hu_data_feng;
			}



			//边
			if(($card_index == 3 || $card_index == 7) && $card_type != ConstConfig::PAI_TYPE_FENG)
			{
				if ($card_index == 3 && $this->m_sPlayer[$chair]->card[$card_type][2] != 0 && $this->m_sPlayer[$chair]->card[$card_type][1] != 0)
				{
					$this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
					$this->_list_delete($chair,$this->m_HuCurt[$chair]->card-1);
					$this->_list_delete($chair,$this->m_HuCurt[$chair]->card-2);
					
					$tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$card_type], 1)));
					
					$this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
					$this->_list_insert($chair,$this->m_HuCurt[$chair]->card-1);
					$this->_list_insert($chair,$this->m_HuCurt[$chair]->card-2);
					
					if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
					{
						$is_biankadiao = true;
					}
				}
				elseif ($card_index == 7 && $this->m_sPlayer[$chair]->card[$card_type][8] != 0 && $this->m_sPlayer[$chair]->card[$card_type][9] != 0)
				{
					$this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
					$this->_list_delete($chair,$this->m_HuCurt[$chair]->card+1);
					$this->_list_delete($chair,$this->m_HuCurt[$chair]->card+2);
					
					$tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$card_type], 1)));
					
					$this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
					$this->_list_insert($chair,$this->m_HuCurt[$chair]->card+1);
					$this->_list_insert($chair,$this->m_HuCurt[$chair]->card+2);
					
					if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
					{
						$is_biankadiao = true;
					}	
				}
			}
			
			// 卡
			if ($card_index != 1 && $card_index != 9 && $card_type != ConstConfig::PAI_TYPE_FENG)
			{
				if ($this->m_sPlayer[$chair]->card[$card_type][$card_index-1] != 0 && $this->m_sPlayer[$chair]->card[$card_type][$card_index+1] != 0)
				{
					$this->_list_delete($chair,$this->m_HuCurt[$chair]->card-1);
					$this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
					$this->_list_delete($chair,$this->m_HuCurt[$chair]->card+1);
					
					$tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$card_type], 1)));
					
					$this->_list_insert($chair,$this->m_HuCurt[$chair]->card-1);
					$this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
					$this->_list_insert($chair,$this->m_HuCurt[$chair]->card+1);
					
					if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
					{
						$is_biankadiao = true;
					}
				}
			}
			
			// 吊
			if ($this->m_sPlayer[$chair]->card[$card_type][$card_index] >= 2)
			{
				$this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
				$this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
				$tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$card_type], 1)));
				$this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
				$this->_list_insert($chair,$this->m_HuCurt[$chair]->card);

				if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
				{
					
					if ($have_hun || $this->_is_dandiao($chair))
					{
						$is_biankadiao = true;
					}
				}
			}
		}
		else
		{
			if ($rep_type == ConstConfig::PAI_TYPE_FENG)
			{
				$tmp_hu_data = &ConstConfig::$hu_data_feng;
			}

			foreach ($bkd_arr as $index) 
			{
				$rep_card = $this->_get_card_index($rep_type, $index);
				
				//边
				if(($index == 3 || $index == 7) && $rep_type != ConstConfig::PAI_TYPE_FENG)
				{
					if ($index == 3 && $this->m_sPlayer[$chair]->card[$rep_type][2] != 0 && $this->m_sPlayer[$chair]->card[$rep_type][1] != 0)
					{
						$this->_list_delete($chair,$rep_card);
						$this->_list_delete($chair,$rep_card-1);
						$this->_list_delete($chair,$rep_card-2);
						
						$tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$rep_type], 1)));
						
						$this->_list_insert($chair,$rep_card);
						$this->_list_insert($chair,$rep_card-1);
						$this->_list_insert($chair,$rep_card-2);
						
						if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
						{
							$is_biankadiao = true;
						}
					}
					elseif ($index == 7 && $this->m_sPlayer[$chair]->card[$rep_type][8] != 0 && $this->m_sPlayer[$chair]->card[$rep_type][9] != 0)
					{
						$this->_list_delete($chair,$rep_card);
						$this->_list_delete($chair,$rep_card+1);
						$this->_list_delete($chair,$rep_card+2);
						
						$tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$rep_type], 1)));
						
						$this->_list_insert($chair,$rep_card);
						$this->_list_insert($chair,$rep_card+1);
						$this->_list_insert($chair,$rep_card+2);
						
						if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
						{
							$is_biankadiao = true;
						}	
					}
				}
				
				// 卡
				if ($index != 1 && $index != 9 && $rep_type != ConstConfig::PAI_TYPE_FENG)
				{
					if ($this->m_sPlayer[$chair]->card[$rep_type][$index-1] != 0 && $this->m_sPlayer[$chair]->card[$rep_type][$index+1] != 0)
					{
						$this->_list_delete($chair,$rep_card-1);
						$this->_list_delete($chair,$rep_card);
						$this->_list_delete($chair,$rep_card+1);
						
						$tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$rep_type], 1)));
						
						$this->_list_insert($chair,$rep_card-1);
						$this->_list_insert($chair,$rep_card);
						$this->_list_insert($chair,$rep_card+1);
						
						if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
						{
							$is_biankadiao = true;
						}
					}
				}
				
				// 吊
				if ($this->m_sPlayer[$chair]->card[$rep_type][$index] >= 2)
				{
					$this->_list_delete($chair,$rep_card);
					$this->_list_delete($chair,$rep_card);
					$tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$rep_type], 1)));
					$this->_list_insert($chair,$rep_card);
					$this->_list_insert($chair,$rep_card);

					if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
					{
						
						if ($have_hun || $this->_is_dandiao($chair))
						{
							$is_biankadiao = true;
						}
					}
				}
			}
		}
		

		return $is_biankadiao;
	}

	// 单吊
	public function _is_dandiao($chair)
	{
		$return = true;
		$card_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
		$card_index = $this->m_HuCurt[$chair]->card % 16;
		$tmp_hu_data = &ConstConfig::$hu_data;
		$replace_card = array(1,2,3,4,5,6,7,8,9);

		if ($card_type == ConstConfig::PAI_TYPE_FENG)
		{
			$tmp_hu_data = &ConstConfig::$hu_data_feng;
			$replace_card = array(1,2,3,4,5,6,7);
		}
		
		$this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
		foreach ($replace_card as $value)
		{
			$tmp_card = $this->_get_card_index($card_type, $value);
			if ($tmp_card == $this->m_HuCurt[$chair]->card)
			{
				continue;
			}
			
			$this->_list_insert($chair,$tmp_card);
			$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$card_type], 1)));
			$this->_list_delete($chair,$tmp_card);
			if(isset($tmp_hu_data[$key]) && ($tmp_hu_data[$key] & 1) == 1)
			{
				$return = false;
			}
		}
		$this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
		return $return;
	}

	//判断有没有后碰
	public function _find_hou($chair, $hou)
	{
		if ($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
		{
			return false;
		}

		// if(!empty($this->m_rule->is_kou_card))
		// {
		// 	$ac = array_count_values($this->m_sPlayer[$chair]->kou_card_display);
		// 	$tmp_num = empty($ac[$this->m_sOutedCard->card]) ? 0 : $ac[$this->m_sOutedCard->card];
		// 	$tmp_kou_num = $tmp_num > 2 ? 2 : $tmp_num;
		// 	if($this->m_sPlayer[$chair]->len - 2 <= count($this->m_sPlayer[$chair]->kou_card_display) - $tmp_kou_num)
		// 	{
		// 		return false;
		// 	}
		// }
		if ($hou == 1)
		{
			$card = $this->m_sOutedCard->card;
		}
		elseif ($hou == 2)
		{
			$card = $this->m_sPlayer[$chair]->card_taken_now;
		}
		
		$card_type = $this->_get_card_type($card);
		if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
		{
			return false;
		}

		$list_card_count = $this->_list_find($chair, $card);
		$table_card_count = $this->_table_find($card);
		if ($hou == 2 && $list_card_count == 2 && $table_card_count == 1)
		{
			return true;
		}
		if ($hou == 1 && $list_card_count == 2 && $table_card_count == 2)
		{
			return true;
		}

		return false;
	}

	// 查找桌面牌，返回个数
	public function _table_find($card)
	{
		$count = 0;
		for ($i = 0; $i < $this->m_rule->player_count; $i++)
		{
			$tmp_arr = $this->m_nTableCards[$i];
			foreach ($tmp_arr as $value) 
			{
				if ($value == $card)
				{
					$count++;
				}
			}
		}
		return $count;
	}

	public function _is_danting($chair)
    {
        $n = 0;
        for ($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
        {
            if ($this->m_sPlayer[$chair]->card[$i][0] == 0)
            {
                continue;
            }
            for ($j=1; $j<=9; $j++)
            {
                //此门牌如果已经4张,则不插入
                if ($this->m_sPlayer[$chair]->card[$i][$j] == 4)
                {
                    continue;
                }
                //不靠张的牌不可能胡
                $before_j = $j - 1;
                $next_j = $j+1;
                $before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$i][$before_j] : 0 ;
                $next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$i][$next_j] : 0 ;
                if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$i][$j] && 0 == $next_count)
                {
                    continue;
                }

                $card = $this->_get_card_index($i, $j);
                if(!$this->_list_insert($chair, $card))
                {
                    continue;
                }


                if ($this->judge_32hu_type($chair))
                {
                    //$this->_log(__CLASS__,__LINE__,'牌可以胡',$card);
                    $n++;
                }
                $this->_list_delete($chair, $card);
            }
        }
        for ($i=ConstConfig::PAI_TYPE_FENG ; $i<=ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if ($this->m_sPlayer[$chair]->card[$i][0] == 0)
            {
                continue;
            }
            for ($j=1; $j<=7; $j++)
            {
                //此门牌如果已经4张,则不插入
                if ($this->m_sPlayer[$chair]->card[$i][$j] == 4)
                {
                    continue;
                }
                //不靠张的牌不可能胡
                $before_j = $j - 1;
                $next_j = $j+1;
                $before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$i][$before_j] : 0 ;
                $next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$i][$next_j] : 0 ;
                if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$i][$j] && 0 == $next_count)
                {
                    continue;
                }

                $card = $this->_get_card_index($i, $j);
                if(!$this->_list_insert($chair, $card))
                {
                    continue;
                }


                if ($this->judge_32hu_type($chair))
                {
                    //$this->_log(__CLASS__,__LINE__,'牌可以胡',$card);
                    $n++;
                }
                $this->_list_delete($chair, $card);
            }
        }
        //$this->_log(__CLASS__,__LINE__,'几张牌可以胡',$n);
        if ($n==1)
        {
            return true;
        }
        else
        {
            return false;
        }

    }

    public function judge_32hu_type($chair)
    {
        $jiang_arr = array();

        $bType32 = false;



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
            return self::HU_TYPE_FENGDING_TYPE_INVALID ;
        }

        if($bType32)
        {
            return self::HU_TYPE_PINGHU;
        }

        return self::HU_TYPE_FENGDING_TYPE_INVALID;
    }

    private function _log($class,$line,$title,$log)
    {
        $str = "类:$class 行号:$line\r\n";
        echo $str;
        var_dump($title);
        var_dump($log);
    }
}
