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


class GameHengShui extends BaseGame
{
    const GAME_TYPE = 281;

	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21;                  // 平胡
    const HU_TYPE_QIDUI = 22;                   // 七对
    const HU_TYPE_HUNGANG = 23;               // 混杠
	const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   // 错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_QIANGGANG = 61;           // 抢杠
    const ATTACHED_HU_HUNDIAO = 62;             // 混儿吊
    const ATTACHED_HU_QINGYISE = 63; 			// 清一色
    const ATTACHED_HU_YITIAOLONG = 64; 			// 一条龙

	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 3;                 // 直杠 3分
	const M_ANGANG_SCORE = 2;                  // 暗杠 2分
	const M_WANGANG_SCORE = 1;                 // 弯杠 1分
    const M_HUNGANG_SCORE = 10;                // 混杠 10分

	public static $hu_type_arr = array(
	    self::HU_TYPE_PINGHU => [self::HU_TYPE_PINGHU, 2, '平胡']
        ,self::HU_TYPE_QIDUI => [self::HU_TYPE_QIDUI, 4, '七对']
       ,self::HU_TYPE_HUNGANG => [self::HU_TYPE_HUNGANG, 5, '混杠胡']

	);

	public static $attached_hu_arr = array(
        self::ATTACHED_HU_QIANGGANG => [self::ATTACHED_HU_QIANGGANG, 1, '抢杠'],
        self::ATTACHED_HU_HUNDIAO => [self::ATTACHED_HU_HUNDIAO, 2, '混儿吊'],
        self::ATTACHED_HU_QINGYISE=>[self::ATTACHED_HU_QINGYISE, 2, '清一色'],
        self::ATTACHED_HU_YITIAOLONG=>[self::ATTACHED_HU_YITIAOLONG, 2, '一条龙']
	);

    // 混杠分数
    public $m_wHunGangScore = array();
    public $m_cancle_time;				// 解散房间开始时间

    public function __construct($serv)
    {
        parent::__construct($serv);
        $this->m_game_type = self::GAME_TYPE;
    }

    public function InitDataSub()
    {
        $this->m_wHunGangScore = array(0,0,0,0);
        $this->m_game_type = self::GAME_TYPE;	//游戏类型，见http端协议
        $this->m_cancle_time = 0;
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


    public function _open_room_sub($params)
    {
        $this->m_rule = new RuleHengShui();

        if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3, 4)))
        {
            $params['rule']['player_count'] = 4;
        }

        $params['rule']['min_fan'] = !isset($params['rule']['min_fan']) ? 0 : $params['rule']['min_fan'];
        $params['rule']['top_fan'] = !isset($params['rule']['top_fan']) ? 255 : $params['rule']['top_fan'];
        $params['rule']['is_circle'] = !isset($params['rule']['is_circle']) ? 1 : $params['rule']['is_circle'];
        $params['rule']['is_feng'] = !isset($params['rule']['is_feng']) ? 1 : $params['rule']['is_feng'];
        $params['rule']['is_fanhun'] = !isset($params['rule']['is_fanhun']) ? 1 : $params['rule']['is_fanhun'];
        $params['rule']['is_dianpao_bao'] = !isset($params['rule']['is_dianpao_bao']) ? 0 : $params['rule']['is_dianpao_bao'];
        $params['rule']['pay_type'] = !isset($params['rule']['pay_type']) ? 0 : $params['rule']['pay_type'];

        //默认项
        $params['rule']['is_chipai'] = !isset($params['rule']['is_chipai']) ? 0 : $params['rule']['is_chipai'];
        $params['rule']['is_qingyise_fan'] = !isset($params['rule']['is_qingyise_fan']) ? 1 : $params['rule']['is_qingyise_fan'];
        $params['rule']['is_yitiaolong_fan'] = !isset($params['rule']['is_yitiaolong_fan']) ? 1 : $params['rule']['is_yitiaolong_fan'];
        $params['rule']['is_ziyise_fan'] = !isset($params['rule']['is_ziyise_fan']) ? 1 : $params['rule']['is_ziyise_fan'];

        $this->m_rule->game_type = $params['rule']['game_type'];
        $this->m_rule->player_count = $params['rule']['player_count'];
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
        $this->m_rule->is_feng = $params['rule']['is_feng'];
        $this->m_rule->is_fanhun = $params['rule']['is_fanhun'];
        $this->m_rule->is_dianpao_bao = $params['rule']['is_dianpao_bao'];
        $this->m_rule->pay_type = $params['rule']['pay_type'];

        //默认项
        $this->m_rule->is_chipai = $params['rule']['is_chipai'];
        $this->m_rule->is_yitiaolong_fan = $params['rule']['is_yitiaolong_fan'];
        $this->m_rule->is_qingyise_fan = $params['rule']['is_qingyise_fan'];
        $this->m_rule->is_ziyise_fan = $params['rule']['is_ziyise_fan'];
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

	//判断胡  
	public function judge_hu($chair, $is_fanhun = false)
	{
		//胡牌型
        $is_hunyou = false;
        $is_qingyise = false;
        $is_yitiaolong = false;
        $is_ziyise =false;

		if (empty($this->m_rule->is_fanhun))
        {
            $hu_type = $this->judge_hu_type($chair, $is_ziyise, $is_qingyise, $is_yitiaolong);
        }
        else
        {
            $hu_type = $this->judge_hu_type_fanhun($chair, $is_hunyou, $is_ziyise, $is_qingyise, $is_yitiaolong, $is_fanhun);
        }

		if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		{
			if($this->m_hun_card)
			{
				$hungagn_hu_card = $this->m_hun_card;//
				$hungagn_hu_type = $this->_get_card_type($hungagn_hu_card);
				if($this->m_sPlayer[$chair]->card[$hungagn_hu_type][$hungagn_hu_card%16] == 4)
				{
					$hu_type = self::HU_TYPE_HUNGANG;
					//记录在全局数据
					$this->m_HuCurt[$chair]->method[0] = $hu_type;
					$this->m_HuCurt[$chair]->count = 1;

				}
			}

			if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
			{
				var_dump(__LINE__);
				return false;
			}
        }
        else
        {
            if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
            {
                var_dump(__LINE__);
                return false;
            }

            //记录在全局数据
            $this->m_HuCurt[$chair]->method[0] = $hu_type;
            $this->m_HuCurt[$chair]->count = 1;
    
            //抢杠胡
            if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
            {
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
            }
    
            //混儿吊
            if($is_hunyou)
            {
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_HUNDIAO);
            }
    
            //清一色
            if($is_qingyise || $is_ziyise)
            {
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QINGYISE);
            }
    
            //一条龙
            if($is_yitiaolong)
            {
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_YITIAOLONG);
            }

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

		//验证混杠
		if($hu_type != self::HU_TYPE_HUNGANG && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO && $fan_sum <= self::$hu_type_arr[self::HU_TYPE_HUNGANG][1])
		{			
            
            if($this->m_hun_card )
            {  
                $this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now);

                $hungagn_hu_card = $this->m_hun_card;//
                $hungagn_hu_type = $this->_get_card_type($hungagn_hu_card);

                if($this->m_sPlayer[$chair]->card[$hungagn_hu_type][$hungagn_hu_card%16] == 4)
                { 
                    //重置混杠
                    $fan_sum = 0;
                    $hu_type = self::HU_TYPE_HUNGANG;
                    $this->m_HuCurt[$chair]->method = array(0 => $hu_type);//充值胡牌
                    $this->m_HuCurt[$chair]->count = 1;//充值胡牌个数

                    $tmp_hu_desc = '(';
                    if(isset(self::$hu_type_arr[$hu_type]))
                    {
                        $fan_sum = self::$hu_type_arr[$hu_type][1];
                        $tmp_hu_desc .= self::$hu_type_arr[$hu_type][2].' ';
                    }
                }
                $this->_list_delete($chair, $this->m_sPlayer[$chair]->card_taken_now);
				
			}
		}

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
    public function judge_hu_type_fanhun($chair, &$is_hunyou,&$is_ziyise, &$is_qingyise, &$is_yitiaolong, $is_fanhun = false)
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

        //手牌无混中
        if($fanhun_num <= 0)
        {
            return $this->judge_hu_type($chair, $is_ziyise, $is_qingyise, $is_yitiaolong);
        }
        else
        {
            $return_type = self::HU_TYPE_FENGDING_TYPE_INVALID;

            //7对牌型
            $need_fanhun = 0;
            $hu_qidui = false;
            $qing_arr = array();
            $is_qingyise = false;
            $is_ziyise = false;

            if($this->m_sStandCard[$chair]->num == 0)
            {
                //去掉翻混
                $this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;
                $this->m_sPlayer[$chair]->card[$fanhun_type][0] -= $fanhun_num;

                for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
                {
                    //当不带风牌时
                    if (empty($this->m_rule->is_feng) && $i == ConstConfig::PAI_TYPE_FENG)
                    {
                        continue;
                    }

                    if(0 == $this->m_sPlayer[$chair]->card[$i][0])
                    {
                        continue;
                    }

                    $qing_arr[] = $i;
                    for ($j=1; $j<=9; $j++)
                    {
                        if($this->m_sPlayer[$chair]->card[$i][$j] == 1 || $this->m_sPlayer[$chair]->card[$i][$j] == 3)
                        {
                            $need_fanhun +=1 ;
                        }
                    }
                }

                $this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += $fanhun_num;
                $this->m_sPlayer[$chair]->card[$fanhun_type][0] += $fanhun_num;

                if($need_fanhun <= $fanhun_num)
                {
                    $hu_qidui = true;
                }

                if($hu_qidui)
                {
                    //清一色
                    $this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);
                    //七对也有混悠
                    if($this->_is_qidui_hunyou($chair , $is_fanhun))
                    {
                        $is_hunyou = true;
                    }

                    return self::HU_TYPE_QIDUI;
                }
            }

            //32牌型
            $is_hu_data = false;
            $qing_arr = array();
            $is_qingyise = false;
            $is_yitiaolong = false;
            $is_ziyise = false;
            $max_hu = array(0=>-1);

            if( $this->_is_hunyou($chair, $is_fanhun))
            {
                $is_hunyou = true;
            }

            //倒牌
            $qing_arr_stand = array();
            for($k=0; $k<$this->m_sStandCard[$chair]->num; $k++)
            {
                $tmp_stand_type = $this->_get_card_type( $this->m_sStandCard[$chair]->first_card[$k]);
                $qing_arr_stand[] = $tmp_stand_type;
            }
            $qing_arr = $qing_arr_stand;

            $jiang_judge_arr = array(0=>2,1=>1,2=>0,3=>2,4=>1,5=>0,6=>2,7=>1,8=>0,9=>2,10=>1,11=>0,12=>2,13=>1,14=>0);
            $no_jiang_judge_arr = array(0=>0,1=>2,2=>1,3=>0,4=>2,5=>1,6=>0,7=>2,8=>1,9=>0,10=>2,11=>1,12=>0);

            //去掉翻混
            $this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;
            $this->m_sPlayer[$chair]->card[$fanhun_type][0] -= $fanhun_num;

            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
            {
                if (empty($this->m_rule->is_feng) && $i == ConstConfig::PAI_TYPE_FENG)
                {
                    continue;
                }

                if(0 == $this->m_sPlayer[$chair]->card[$i][0] && $i != $fanhun_type)
                {
                    continue;
                }

                $is_qingyise = false;
                $is_ziyise = false;
                $qing_arr = $qing_arr_stand;

                $is_hu_data = false;
                $jiang_type = $i;
                $need_fanhun = 0;
                $replace_fanhun = array(0,0,0,0);

                //每门牌需要的混个数
                for($j=ConstConfig::PAI_TYPE_WAN; $j<=ConstConfig::PAI_TYPE_FENG; $j++)
                {
                    if (empty($this->m_rule->is_feng) && $j == ConstConfig::PAI_TYPE_FENG)
                    {
                        continue;
                    }
                    //一门牌个数
                    $pai_num = $this->m_sPlayer[$chair]->card[$j][0];

                    if($pai_num == 0)
                    {
                        continue;
                    }
                    elseif ($pai_num > 0)
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

                if($need_fanhun <= $fanhun_num)
                {
                    $is_check_hu = false;
                    for($j=ConstConfig::PAI_TYPE_WAN; $j<=ConstConfig::PAI_TYPE_FENG; $j++)
                    {
                        $is_hu_data = false;
                        $max_type_hu_arr = array(0=>-1,0);

                        if (empty($this->m_rule->is_feng) && $j == ConstConfig::PAI_TYPE_FENG)
                        {
                            continue;
                        }

                        if($fanhun_num == $need_fanhun && $is_check_hu)
                        {
                            continue;
                        }
                        $is_check_hu = true;

                        $tmp_replace_fanhun = $replace_fanhun;
                        $tmp_replace_fanhun[$j] += ($fanhun_num - $need_fanhun);

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

                            $tmp_type_hu_num = 0;
                            $is_hu_data = false;
                            $insert_yitiaolong = $max_type_hu_arr[1];

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

                                    foreach ($insert_arr as $insert_item)
                                    {
                                        $this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
                                    }

                                    //平胡
                                    $tmp_type_hu_num = self::$hu_type_arr[self::HU_TYPE_PINGHU][1];

                                    //一条龙
                                    $tmp_type_yitiaolong = ($insert_yitiaolong || !empty($this->m_rule->is_yitiaolong_fan) && ($tmp_hu_data[$key] & 256) == 256);
                                    if($tmp_type_yitiaolong)
                                    {
                                        $tmp_type_hu_num *= self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                                    }

                                    if($tmp_type_hu_num >= $max_type_hu_arr[0])
                                    {
                                        //总分
                                        $max_type_hu_arr[0] = $tmp_type_hu_num;
                                        $max_type_hu_arr[1] = $tmp_type_yitiaolong;
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

                            if (!$is_hu_data)
                            {
                                $max_type_hu_arr[0] = -1;
                                break;
                            }
                        }

                        if($max_type_hu_arr[0] > 0)
                        {
                            $tmp_max_hu = self::$hu_type_arr[self::HU_TYPE_PINGHU][1];

                            if(!empty($this->m_rule->is_yitiaolong_fan) && $max_type_hu_arr[1])
                            {
                                $tmp_max_hu += self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
                            }

                            if(!empty($this->m_rule->is_qingyise_fan))
                            {
                                $tmp_is_qingyise = false;
                                $tmp_is_ziyise = false;
                                $this->_is_yise($qing_arr, $tmp_is_qingyise, $tmp_is_ziyise);

                                if(!empty($this->m_rule->is_qingyise_fan) && $tmp_is_qingyise)
                                {
                                    $tmp_max_hu += self::$attached_hu_arr[self::ATTACHED_HU_QINGYISE][1];
                                }
                                elseif (!empty($this->m_rule->is_ziyise_fan) && $tmp_is_ziyise)
                                {
                                    $tmp_max_hu += self::$attached_hu_arr[self::ATTACHED_HU_QINGYISE][1];
                                }
                            }

                            if($tmp_max_hu > $max_hu[0])
                            {
                                $max_hu[0] = $tmp_max_hu;
                                $max_hu[1] = $max_type_hu_arr[1];
                            }
                        }

                        if($max_hu[0] >= self::$hu_type_arr[self::HU_TYPE_PINGHU][1] + self::$attached_hu_arr[self::ATTACHED_HU_QINGYISE][1]
                            + self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1])
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

            //添加翻混
            $this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += $fanhun_num;
            $this->m_sPlayer[$chair]->card[$fanhun_type][0] += $fanhun_num;

            if($max_hu[0] >= 0)
            {
                $this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);

                if(!empty($this->m_rule->is_yitiaolong_fan) && $max_hu[1])
                {
                    $is_yitiaolong = $max_hu[1];
                }

                return self::HU_TYPE_PINGHU;
            }
            return $return_type;
        }
    }

	//胡牌类型判断  没有混的情况
    public function judge_hu_type($chair,&$is_ziyise, &$is_qingyise, &$is_yitiaolong)
    {
        $jiang_arr = array();
        $qidui_arr = array();
        $qing_arr = array();
        $is_qingyise = false;
        $is_yitiaolong = false;
        $is_ziyise =false;
        $bType32 = false;
        $bQiDui = false;

        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if (empty($this->m_rule->is_feng) && $i == ConstConfig::PAI_TYPE_FENG)
            {
                continue;
            }

            if (0 == $this->m_sPlayer[$chair]->card[$i][0])
            {
                continue;
            }
            if (in_array($this->m_sPlayer[$chair]->card[$i][0], array(1, 7, 13)))
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }

            $tmp_hu_data = &ConstConfig::$hu_data;
            if ($i == ConstConfig::PAI_TYPE_FENG)
            {
                $tmp_hu_data = &ConstConfig::$hu_data_feng;
            }

            $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));
            if (!isset($tmp_hu_data[$key]))
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }
            else
            {
                $hu_list_val = $tmp_hu_data[$key];

                if (!empty($this->m_rule->is_yitiaolong_fan) && ($hu_list_val & 256) == 256)
                {
                    $is_yitiaolong = true;
                }

                $qidui_arr[] = $hu_list_val & 64;

                $qing_arr[] = $i;

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
            $stand_pai_type = $this->_get_card_type($this->m_sStandCard[$chair]->first_card[$i]);
            $qing_arr[] = $stand_pai_type;
        }

        //记录根到全局数据
        $bType32 = (32 == array_sum($jiang_arr));
        $bQiDui = !array_keys($qidui_arr, 0);

        /////////////////////////////附加 番型的处理/////////////////////////////////
        //一色结果
        $this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);

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
	
	//------------------------------------- 命令处理函数 -----------------------------------
	//处理碰 
	private function HandleChoosePeng($chair)
	{
		$temp_card = $this->m_sOutedCard->card;

        $this->_list_delete($chair, $temp_card);
        $this->_list_delete($chair, $temp_card);

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
			return false;
		}
        $this->m_sPlayer[$chair]->card_taken_now = $car_14;

        //删除出牌者最后一张桌面牌
        $this->_deleteLastTableCard($this->m_sOutedCard->chair, $this->m_sOutedCard->card);

		//录像
		$this->_set_record_game(ConstConfig::RECORD_PENG, $chair, $temp_card, $this->m_sOutedCard->chair);

		//更改状态
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
		for ($i = 0; $i < $this->m_rule->player_count ; $i ++)
		{
            if($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
            {
                $this->m_sPlayer[$i]->state = ($i == $chair) ? ConstConfig::PLAYER_STATUS_THINK_OUTCARD : ConstConfig::PLAYER_STATUS_WAITING;
            }
		}

		$this->m_chairCurrentPlayer = $chair;
		//$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
        $this->m_sOutedCard->clear();
        $this->m_sGangPao->clear();
		$this->m_only_out_card[$chair] = true;

		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);
        $this->handle_flee_play(true);
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
			return false;
		}
        $this->m_sPlayer[$chair]->card_taken_now = $car_14;

		//删除出牌者最后一张桌面牌
        $this->_deleteLastTableCard($this->m_sOutedCard->chair, $this->m_sOutedCard->card);

        // 改变状态
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
        for ($i = 0; $i < $this->m_rule->player_count ; $i ++)
		{
			if($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
			{
                $this->m_sPlayer[$i]->state = ($i == $chair) ? ConstConfig::PLAYER_STATUS_THINK_OUTCARD : ConstConfig::PLAYER_STATUS_WAITING;
			}
		}

		$this->m_chairCurrentPlayer = $chair;
		//$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
        $this->m_sOutedCard->clear();
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

        //混儿杠
        $GangScore = 0;
        $nGangPao = 0;

        if ($this->m_rule->is_fanhun == 1 && $temp_card == $this->m_hun_card)
        {
            for ($i=0; $i<$this->m_rule->player_count; $i++)
            {
                if ($i == $chair)
                {
                    continue;
                }

                if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
                {
                    $nGangScore = self::M_HUNGANG_SCORE * ConstConfig::SCORE_BASE;

                    $this->m_wHunGangScore[$i] -= $nGangScore;
                    $this->m_wHunGangScore[$chair] += $nGangScore;

                    $nGangPao += $nGangScore;
                }
            }
        }
        else
        {
            for ($i=0; $i<$this->m_rule->player_count; $i++)
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

		//$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
		}
	}

	//处理直杠 
	private function HandleChooseZhiGang($chair)
	{
		$temp_card = $this->m_sOutedCard->card;

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

		$nGangPao = 0;

		//分数结算
        if ($this->m_rule->is_fanhun == 1 && $temp_card == $this->m_hun_card)
        {
            for ($i=0; $i<$this->m_rule->player_count; $i++)
            {
                if ($i == $chair)
                {
                    continue;
                }

                if ($stand_count_after > 0 && $i == $this->m_sStandCard[$chair]->who_give_me[$stand_count_after-1])
                {
                    $nGangScore = self::M_HUNGANG_SCORE * ConstConfig::SCORE_BASE;

                    $this->m_wHunGangScore[$i] -= $nGangScore;
                    $this->m_wHunGangScore[$chair] += $nGangScore;

                    $nGangPao += $nGangScore;
                }
            }
        }
        else
        {
            for ($i=0; $i<$this->m_rule->player_count; $i++)
            {
                if ($i == $chair)
                {
                    continue;
                }

                if ($stand_count_after > 0 && $i == $this->m_sStandCard[$chair]->who_give_me[$stand_count_after-1])
                {
                    $nGangScore = self::M_ZHIGANG_SCORE;

                    $this->m_wGangScore[$i][$i] -= $nGangScore;

                    $this->m_wGangScore[$chair][$chair] += $nGangScore;

                    $this->m_wGangScore[$chair][$i] += $nGangScore;

                    $nGangPao += $nGangScore;
                }
            }
        }

        //录像
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

		//删除最后一张桌面牌
        $this->_deleteLastTableCard($this->m_sOutedCard->chair, $this->m_sOutedCard->card);

		$this->m_sOutedCard->clear();

		if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
		{
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
					$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
				}
			}
		}
	}

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

            if($this->_find_peng($chair_next) || $this->_find_zhi_gang($chair_next))
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

                //弯杠 赢3家
                for ($i = 0; $i < $this->m_rule->player_count; ++$i) {
                    if ($i == $this->m_sQiangGang->chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU) {
                        continue;
                    }
                    $nGangScore = self::M_WANGANG_SCORE * ConstConfig::SCORE_BASE;

                    $this->m_wGangScore[$i][$i] -= $nGangScore;

                    $this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
                    $this->m_wGangScore[$this->m_sQiangGang->chair][$i] += $nGangScore;

                    $nGangPao += $nGangScore;
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
					$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
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
						$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
					}
					break;
			}
		}

		$this->m_nNumCmdHu = 0;
		$this->m_chairHu = array();
	}

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
				$wWinScore += 2 * ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;  //赢的分 加  庄家的分

				$this->m_wHuScore[$i] -= $wWinScore;
				$this->m_wHuScore[$chair] += $wWinScore;

				$this->m_wSetLoseScore[$i] -= $wWinScore;
				$this->m_wSetScore[$chair] += $wWinScore;

				$this->m_HuCurt[$chair]->gain_chair[0]++;
				$this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $i;
			}
			return true;
		}
		else if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
		{
		    //点炮大包
            if($this->m_rule->is_dianpao_bao == 1)
            {
                for($i = 0; $i < $this->m_rule->player_count; $i++)
                {
                    if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
                    {
                        continue;	//单用户测试需要关掉
                    }

                    $banker_fan = 1;
                    if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i)
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

                    $this->m_HuCurt[$chair]->gain_chair[0]++;
                    $this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $lost_chair;
                }
            }
            elseif ($this->m_rule->is_dianpao_bao == 2)
            {
                for($i = 0; $i < $this->m_rule->player_count; $i++)
                {
                    if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
                    {
                        continue;	//单用户测试需要关掉
                    }

                    $banker_fan = 1;
                    if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i)
                    {
                        $banker_fan = 2;
                    }

                    $PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
                    $wWinScore = 0;
                    $wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;

                    $this->m_wHuScore[$i] -= $wWinScore;
                    $this->m_wHuScore[$chair] += $wWinScore;

                    $this->m_wSetLoseScore[$i] -= $wWinScore;
                    $this->m_wSetScore[$chair] += $wWinScore;

                    $this->m_HuCurt[$chair]->gain_chair[0]++;
                    $this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $i;
                }
            }
            elseif ($this->m_rule->is_dianpao_bao == 0)
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
            }

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
			$this->m_Score[$i]->score = $this->m_wSetScore[$i]+ $this->m_wSetLoseScore[$i]+ $this->m_wGangScore[$i][$i] + $this->m_wHunGangScore[$i];
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
            $this->m_wHunGangScore[$i] = 0;
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

			if (!empty($this->m_rule->is_fanhun) && $this->m_wHunGangScore[$i] != 0)
			{
                if($this->m_wHunGangScore[$i]>0)
                {
                    $this->m_hu_desc[$i] .= '混杠+'.$this->m_wHunGangScore[$i].' ';
                }
                else
                {
                    $this->m_hu_desc[$i] .= '混杠'.$this->m_wHunGangScore[$i].' ';
                }
            }
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
        $this->game_to_playing();

        return true;
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

    //掷骰定庄家
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

    //七对混儿吊
    private function _is_qidui_hunyou($chair,$is_fanhun = false)
    {
        $temp_card = $this->m_HuCurt[$chair]->card;

        if($this->m_hun_card)
        {
            $hun_type = $this->_get_card_type($this->m_hun_card);        //翻混牌类型
            $hun_index= $this->m_hun_card % 16;
        }

        if($temp_card == $this->m_hun_card)  //抓来的牌是混 ,那就两张混子
        {
            //如果抓来的是个混,那手牌也需要又一个混
            if($this->m_sPlayer[$chair]->card[$hun_type][$hun_index] < 2)
            {
                return false;
            }
        }

        $need_fanhun = 0;	//需要混子个数
        $hu_hunyou = false;

        //去掉混牌和自摸的牌
        $this->_list_delete($chair,$temp_card);
        $this->_list_delete($chair,$this->m_hun_card);

        $hun_num_last = $this->m_sPlayer[$chair]->card[$hun_type][$hun_index];
        $this->m_sPlayer[$chair]->card[$hun_type][$hun_index] = 0;

        for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
        {
            if(0 == $this->m_sPlayer[$chair]->card[$i][0] || (0 == ($this->m_sPlayer[$chair]->card[$i][0] - $hun_num_last) && $i == $hun_type ))
            {
                continue;
            }
            for ($j=1; $j<=9; $j++)
            {
                if($this->m_sPlayer[$chair]->card[$i][$j] == 1 || $this->m_sPlayer[$chair]->card[$i][$j] == 3)
                {
                    $need_fanhun +=1 ;
                }
            }
        }
        $this->m_sPlayer[$chair]->card[$hun_type][$hun_index] = $hun_num_last;

        $this->_list_insert($chair,$temp_card);
        $this->_list_insert($chair,$this->m_hun_card);

        if($need_fanhun <= $hun_num_last)
        {
            $hu_hunyou = true;
        }

        return $hu_hunyou;
    }

    //32混儿吊
    private function _is_hunyou($chair, $is_fanhun)
    {
        $return = false;
        $temp_card = $this->m_HuCurt[$chair]->card;

        if($this->m_hun_card)
        {
            $hun_type = $this->_get_card_type($this->m_hun_card);
            $hun_index = $this->m_hun_card % 16;
        }

        if($temp_card == $this->m_hun_card)  //抓来的牌是混 ,那就两张混子
        {
            //如果抓来的是个混,那手牌也需要又一个混
            if($this->m_sPlayer[$chair]->card[$hun_type][$hun_index] < 2)
            {
                return false;
            }
        }

        //去掉混牌和胡的牌
        $this->_list_delete($chair,$temp_card);
        $this->_list_delete($chair,$this->m_hun_card);

        //判断能不能胡 并且没有将牌
        if($this->judge32TypeHunYou($chair,$is_fanhun))
        {
            $return = true;
        }

        //还原手牌
        $this->_list_insert($chair,$temp_card);
        $this->_list_insert($chair,$this->m_hun_card);

        return $return;
    }

    private function judge32TypeHunYou($chair, $is_fanhun = false)
    {
        $fanhun_num = 0;
        $fanhun_type = 255;
        if($this->m_hun_card)
        {
            $fanhun_num = $this->_list_find($chair, $this->m_hun_card);	//手牌翻混个数
            $fanhun_type = $this->_get_card_type($this->m_hun_card);        //翻混牌类型
            $fanhun_card = $this->m_hun_card%16;       //翻混牌
        }

        $max_hu = array(0=>-1);
        $no_jiang_judge_arr = array(0=>0,1=>2,2=>1,3=>0,4=>2,5=>1,6=>0,7=>2,8=>1,9=>0,10=>2,11=>1,12=>0);

        $need_fanhun = 0;	//需要混个数
        $replace_fanhun = array(0,0,0,0);

        for($j=ConstConfig::PAI_TYPE_WAN; $j<=ConstConfig::PAI_TYPE_FENG; $j++)
        {
            if (empty($this->m_rule->is_feng) && $j == ConstConfig::PAI_TYPE_FENG)
            {
                continue;
            }

            if(0 == $this->m_sPlayer[$chair]->card[$j][0] || ($j == $fanhun_type && 0 == $this->m_sPlayer[$chair]->card[$j][0]-$fanhun_num))
            {
               continue;
            }

            $pai_num = $this->m_sPlayer[$chair]->card[$j][0];	//一门牌个数
            $pai_num = ($j == $fanhun_type) ? $pai_num - $fanhun_num : $pai_num;	//混牌的牌型个数得减去混牌个数

            $tmp_judge_arr = $no_jiang_judge_arr;
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
                    $this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 0;	//去掉翻混
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
        }

        if($need_fanhun <= $fanhun_num)
        {
            $is_check_hu = false;
            for($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
            {
                if (empty($this->m_rule->is_feng) && $j == ConstConfig::PAI_TYPE_FENG)
                {
                    continue;
                }

                if($fanhun_num == $need_fanhun && $is_check_hu)
                {
                    continue;
                }

                $is_check_hu = true;
                $max_type_hu_arr = array(0=>-1);
                $tmp_replace_fanhun = $replace_fanhun;
                $tmp_replace_fanhun[$j] += ($fanhun_num - $need_fanhun);

                //校验胡
                foreach ($tmp_replace_fanhun as $type => $num)
                {
                    $type_len = $this->m_sPlayer[$chair]->card[$type][0] + $num;
                    if($type == $fanhun_type)
                    {
                        $this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 0;	//去掉翻混
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
                    $is_hu_data = false;
                    foreach ($tmp_hu_data_insert[$num] as $insert_arr)
                    {
                        foreach ($insert_arr as $insert_item)
                        {
                            $this->m_sPlayer[$chair]->card[$type][$insert_item] += 1;
                        }
                        $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));

                        if(isset($tmp_hu_data[$key]) && ($tmp_hu_data[$key] & 1) == 1)
                        {
                            $is_have_jiang = $tmp_hu_data[$key] & 32;
                            if (!$is_have_jiang)
                            {
                                $is_hu_data = true;
                                $tmp_type_hu_num = 1;
                            }
                            foreach ($insert_arr as $insert_item)
                            {
                                $this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
                            }

                            if($tmp_type_hu_num >= $max_type_hu_arr[0])
                            {
                                $max_type_hu_arr[0] = $tmp_type_hu_num;
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

                    if(!$is_hu_data)
                    {
                        $max_type_hu_arr[0] = -1;
                        break;
                    }
                }

                if($max_type_hu_arr[0] > 0)
                {
                    $tmp_max_hu = self::$hu_type_arr[self::HU_TYPE_PINGHU][1];
                    if($tmp_max_hu > $max_hu[0])
                    {
                        $max_hu[0] = $tmp_max_hu;
                    }
                }
            }
        }

        if($max_hu[0] >= 0)
        {
            return true;
        }

        return false;
    }

    //碰牌
    public function c_peng($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do {

            if($this->_isEmpty($params,['rid', 'uid']))
            {
                $this->_deBugs(ConstConfig::PARAMETER_ERROR, $return_send, __LINE__.__CLASS__);break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
            {
                $this->_deBugs(ConstConfig::ROOM_PHASE_ERROR, $return_send, __LINE__.__CLASS__);break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {
                    if(!$this->_find_peng($key))
                    {
                        $this->c_cancle_choice($fd, $params);
                        $this->_deBugs(ConstConfig::NOT_HAVE_PENG, $return_send, __LINE__.__CLASS__);break 2;
                    }

                    $this->_clear_choose_buf($key);
                    $this->HandleChooseResult($key, $params['act']);
                    $is_act = true;
                }
            }

            if(!$is_act = true)
            {
                $this->_deBugs(ConstConfig::NOT_BELONG_THIS_ROOM, $return_send, __LINE__.__CLASS__);break;
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
            if($this->_isEmpty($params, ['rid', 'uid', 'num']) || !in_array($params['num'],array(1,2,3)))
            {
                $this->_deBugs(ConstConfig::PARAMETER_ERROR, $return_send, __LINE__.__CLASS__);break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
            {
                $this->_deBugs(ConstConfig::ROOM_PHASE_ERROR, $return_send, __LINE__.__CLASS__);break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {
                    if(!$this->_find_eat($key,$params['num']))
                    {
                        $this->c_cancle_choice($fd, $params);
                        $this->_deBugs(ConstConfig::NOT_HAVE_EAT, $return_send, __LINE__.__CLASS__);break 2;
                    }

                    $this->_clear_choose_buf($key);
                    $this->HandleChooseResult($key, $params['act'], $params['num']);
                    $is_act = true;
                }
            }
            if(!$is_act = true)
            {
                $this->_deBugs(ConstConfig::NOT_BELONG_THIS_ROOM, $return_send, __LINE__.__CLASS__);break;
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
            if($this->_isEmpty($params, ['rid', 'uid']))
            {
                $this->_deBugs(ConstConfig::PARAMETER_ERROR, $return_send, __LINE__.__CLASS__);break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING)
            {
                $this->_deBugs(ConstConfig::ROOM_PHASE_ERROR, $return_send, __LINE__.__CLASS__);break;
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
                        $this->_deBugs(ConstConfig::NOT_HAVE_ZHIGANG, $return_send, __LINE__.__CLASS__);break 2;
                    }

                    $this->_clear_choose_buf($key);
                    $this->HandleChooseResult($key, $params['act']);
                    $is_act = true;
                }
            }
            if(!$is_act = true)
            {
                $this->_deBugs(ConstConfig::NOT_BELONG_THIS_ROOM, $return_send, __LINE__.__CLASS__);break;
            }

        }while(false);

        $this->serv->send($fd, Room::tcp_encode(($return_send)));

        return $return_send['code'];
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

        if ($this->m_sOutedCard->chair == $chair)
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

        if ($this->m_sOutedCard->chair != $this->_anti_clock($chair,-1))
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

        if ($this->m_sOutedCard->chair == $chair)
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

    private function _isEmpty($params, $keys)
    {
        foreach ($keys as $key)
        {
            if (empty($params[$key]))
            {
                return true;
            }
        }
        return false;
    }

    private function _deBugs($type, &$return_send, $line)
    {
        $error = ConstConfig::$error;
        if (isset($error[$type]))
        {
            $return_send['code'] = $type;
            $return_send['text'] = $error[$type];
            $return_send['desc'] = $line;
        }
    }

    private function _deleteLastTableCard($chair, $card)
    {
        $this->m_nNumTableCards[$chair] = $this->m_nNumTableCards[$chair] -1;
        if($this->m_nTableCards[$chair][$this->m_nNumTableCards[$chair]] == $card)
        {
            unset($this->m_nTableCards[$chair][$this->m_nNumTableCards[$chair]]);
        }
    }

    public function _cancle_game()
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

        $this->_send_cmd('s_cancle_game', array('is_cancle'=>$is_cancle, 'm_cancle_first'=>$this->m_cancle_first, 'm_cancle'=>$this->m_cancle, 'cancle_time_start'=>$cancle_time_start), Game_cmd::SCO_ALL_PLAYER );

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


