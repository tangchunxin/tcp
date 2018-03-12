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
class GameMancheng extends BaseGame
{
    const GAME_TYPE = 126;
    //－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
    const HU_TYPE_PINGHU = 21;                  	// 平胡
    const HU_TYPE_QIDUI = 22;                   	// 七对
   	const HU_TYPE_SHISANYAO = 23;               	// 十三幺
   	const HU_TYPE_HAOHUA_QIDUI = 24;            	// 豪华七对
	const HU_TYPE_CHAOJI_QIDUI = 25;            	// 超级豪华七对
	const HU_TYPE_ZHUIZUN_QIDUI = 26;           	// 至尊豪华七对
    const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   	// 错误

    //－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
    //const ATTCHED_HU_ZHUOWUKUI = 61;            	// 捉五魁
    const ATTCHED_HU_KOUPAI = 62;               	// 扣牌
    const ATTCHED_KOUDAJIANG = 63;              	// 扣大将
    const ATTACHED_HU_YITIAOLONG = 64;          	// 一条龙
    const ATTACHED_HU_QIANGGANG = 65;           	// 抢杠
	const ATTACHED_HU_QINGYISE = 66;            	// 清一色
	const ATTACHED_HU_ZIYISE = 67;            		// 风一色

    //－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
    const M_ZHIGANG_SCORE = 1;                 		// 直杠 每家1分
    const M_ANGANG_SCORE = 2;                  		// 暗杠 每家2分
    const M_WANGANG_SCORE = 1;                 		// 弯杠 每家1分

    public $countGang;                     			// 记录杠的个数
    public $is_gang_genzhuang;						// 开杠算跟庄标识
    //public $isUpperChairDaBao = array(0, 0, 0, 0); // 一条龙是否上家大包

    public static $hu_type_arr = array(
        self::HU_TYPE_PINGHU=>array(self::HU_TYPE_PINGHU, 1, '平胡')
        ,self::HU_TYPE_QIDUI=>array(self::HU_TYPE_QIDUI, 2, '七对')
        ,self::HU_TYPE_SHISANYAO=>array(self::HU_TYPE_SHISANYAO, 10, '十三幺')
		,self::HU_TYPE_HAOHUA_QIDUI=>array(self::HU_TYPE_HAOHUA_QIDUI, 4, '豪华七对')
		,self::HU_TYPE_CHAOJI_QIDUI=>array(self::HU_TYPE_CHAOJI_QIDUI, 8, '超级豪华七对')
		,self::HU_TYPE_ZHUIZUN_QIDUI=>array(self::HU_TYPE_ZHUIZUN_QIDUI, 16, '至尊豪华七对')
    );

    public static $attached_hu_arr = array(
        //self::ATTCHED_HU_ZHUOWUKUI=>[self::ATTCHED_HU_ZHUOWUKUI, 3, '捉五魁']
        self::ATTCHED_HU_KOUPAI=>array(self::ATTCHED_HU_KOUPAI, 2, '扣牌')
        ,self::ATTCHED_KOUDAJIANG=>array(self::ATTCHED_KOUDAJIANG, 2, '扣大将')
        ,self::ATTACHED_HU_YITIAOLONG=>array(self::ATTACHED_HU_YITIAOLONG, 2, '一条龙')
        ,self::ATTACHED_HU_QIANGGANG=>array(self::ATTACHED_HU_QIANGGANG, 1, '抢杠')
        ,self::ATTACHED_HU_QINGYISE=>array(self::ATTACHED_HU_QINGYISE, 4, '清一色')
		,self::ATTACHED_HU_ZIYISE=>array(self::ATTACHED_HU_ZIYISE, 4, '字一色')
    );

    public function __construct($serv)
    {
        parent::__construct($serv);
        $this->m_game_type = self::GAME_TYPE;
    }

    public function InitDataSub()
    {
        $this->m_game_type = self::GAME_TYPE;	//游戏类型，见http端协议
        $this->countGang = 0;
        $this->is_gang_genzhuang = 0;
        //$this->isUpperChairDaBao = array(0, 0, 0, 0);
    }

    public function _open_room_sub($params)
    {
        $this->m_rule = new RuleMancheng();

        if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(2, 3, 4)))
        {
            $params['rule']['player_count'] = 4;
        }

        $params['rule']['min_fan'] = !isset($params['rule']['min_fan']) ? 0 : $params['rule']['min_fan'];
        $params['rule']['top_fan'] = !isset($params['rule']['top_fan']) ? 40 : $params['rule']['top_fan'];
        $params['rule']['is_circle'] = !isset($params['rule']['is_circle']) ? 1 : $params['rule']['is_circle'];
        $params['rule']['is_feng'] = !isset($params['rule']['is_feng']) ? 1 : $params['rule']['is_feng'];
        $params['rule']['is_chipai'] = !isset($params['rule']['is_chipai']) ? 0 : $params['rule']['is_chipai'];
        $params['rule']['is_yipao_duoxiang'] = !isset($params['rule']['is_yipao_duoxiang']) ? 0 : $params['rule']['is_yipao_duoxiang'];

        $params['rule']['is_genzhuang'] = $params['rule']['player_count'] == 4 ? 1 : 0;
		$params['rule']['is_zhuang_fan'] = !isset($params['rule']['is_zhuang_fan']) ? 1 : $params['rule']['is_zhuang_fan'];
		$params['rule']['is_qingyise_fan'] = !isset($params['rule']['is_qingyise_fan']) ? 1 : $params['rule']['is_qingyise_fan'];
		$params['rule']['is_ziyise_fan'] = !isset($params['rule']['is_ziyise_fan']) ? 1 : $params['rule']['is_ziyise_fan'];
        $params['rule']['is_yitiaolong_fan'] = !isset($params['rule']['is_yitiaolong_fan']) ? 1 : $params['rule']['is_yitiaolong_fan'];
        //$params['rule']['is_zhuowukui_fan'] = !isset($params['rule']['is_zhuowukui_fan']) ? 1 : $params['rule']['is_zhuowukui_fan'];
        
        $params['rule']['is_qidui_fan'] = !isset($params['rule']['is_qidui_fan']) ? 1 : $params['rule']['is_qidui_fan'];
        $params['rule']['is_kou_card'] = !isset($params['rule']['is_kou_card']) ? 1 : $params['rule']['is_kou_card'];
        $params['rule']['is_kou_dajiang'] = !isset($params['rule']['is_kou_dajiang']) ? 1 : $params['rule']['is_kou_dajiang'];
        $params['rule']['is_dianpao_bao'] = !isset($params['rule']['is_dianpao_bao']) ? 2 : $params['rule']['is_dianpao_bao'];
        $params['rule']['cancle_clocker'] = !isset($params['rule']['cancle_clocker']) ? 1 : $params['rule']['cancle_clocker'];
        $params['rule']['pay_type'] = !isset($params['rule']['pay_type']) ? 0 : $params['rule']['pay_type'];

        $params['rule']['qg_is_zimo'] = !isset($params['rule']['qg_is_zimo']) ? 1 : $params['rule']['qg_is_zimo'];
        $params['rule']['is_da8zhang'] = !isset($params['rule']['is_da8zhang']) ? 1 : $params['rule']['is_da8zhang'];
        $params['rule']['is_wangang_1_lose'] = !isset($params['rule']['is_wangang_1_lose']) ? 0 : $params['rule']['is_wangang_1_lose'];
		$params['rule']['is_za_hu_mian_gang'] = !isset($params['rule']['is_za_hu_mian_gang']) ? 1 : $params['rule']['is_za_hu_mian_gang'];
        $params['rule']['is_shisanyao_fan'] = !isset($params['rule']['is_shisanyao_fan']) ? 1 : $params['rule']['is_shisanyao_fan'];
        $params['rule']['is_kou13'] = !isset($params['rule']['is_kou13']) ? 1 : $params['rule']['is_kou13'];

        $this->m_rule->game_type = $params['rule']['game_type'];
        $this->m_rule->player_count = $params['rule']['player_count'];
        $this->m_rule->min_fan = $params['rule']['min_fan'];
        $this->m_rule->top_fan = $params['rule']['top_fan'];
        $this->m_rule->is_circle = $params['rule']['is_circle'];
        if(!empty($this->m_rule->is_circle))
        {
            $this->m_rule->set_num = $this->m_rule->is_circle * $this->m_rule->player_count;
        }
        else
        {
            $this->m_rule->set_num = $params['rule']['set_num'];
        }
        $this->m_rule->is_feng = $params['rule']['is_feng'];
        $this->m_rule->is_chipai = $params['rule']['is_chipai'];
        $this->m_rule->is_yipao_duoxiang = $params['rule']['is_yipao_duoxiang'];

        $this->m_rule->is_genzhuang = $params['rule']['is_genzhuang'];
		$this->m_rule->is_zhuang_fan = $params['rule']['is_zhuang_fan'];
		$this->m_rule->is_qingyise_fan = $params['rule']['is_qingyise_fan'];
		$this->m_rule->is_ziyise_fan = $params['rule']['is_ziyise_fan'];
        $this->m_rule->is_yitiaolong_fan = $params['rule']['is_yitiaolong_fan'];
        
        $this->m_rule->is_qidui_fan = $params['rule']['is_qidui_fan'];
        //$this->m_rule->is_zhuowukui_fan = $params['rule']['is_zhuowukui_fan'];
        $this->m_rule->is_kou_card = $params['rule']['is_kou_card'];
        $this->m_rule->is_kou_dajiang = $params['rule']['is_kou_dajiang'];
        $this->m_rule->is_dianpao_bao = $params['rule']['is_dianpao_bao'];
        $this->m_rule->cancle_clocker = $params['rule']['cancle_clocker'];
        $this->m_rule->pay_type = $params['rule']['pay_type'];

        $this->m_rule->qg_is_zimo = $params['rule']['qg_is_zimo'];
        $this->m_rule->is_da8zhang = $params['rule']['is_da8zhang'];
        $this->m_rule->is_wangang_1_lose = $params['rule']['is_wangang_1_lose'];
        $this->m_rule->is_za_hu_mian_gang = $params['rule']['is_za_hu_mian_gang'];  //默认规则 客户端可不传此 参数
        $this->m_rule->is_shisanyao_fan = $params['rule']['is_shisanyao_fan'];
        $this->m_rule->is_kou13 = $params['rule']['is_kou13'];
    }

    //扣牌
	public function c_kou_card($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| !isset($params['yes'])
			|| !in_array($params['yes'], array(1, 2))
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_KOU_CARD || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state)
			{
				$return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

			$is_act = false;
			foreach ($this->m_room_players as $key => $room_user)
			{
				if($room_user['uid'] == $params['uid'])
				{
					$this->handle_kou_card($key, $params['yes']);
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

	//处理扣牌
	public function handle_kou_card($chair, $yes)
	{
		$is_act = false;
		$act_n = 0;
		$send = true;
		for ($n=0; $n <= 3; $n++)
		{
			$tmp_start = $n * 4;
			for ($i = 0; $i<$this->m_rule->player_count; ++$i)
			{
				if($n == 3)
				{
					if($this->m_sPlayer[$i]->kou_card[$tmp_start][1] == 0)
					{
						if($chair == $i && !$is_act)
						{
							$this->m_sPlayer[$i]->kou_card[$tmp_start][1] = $yes;
							$is_act = true;
							$act_n = $n;
						}
						else
						{
							$send = false;
						}
					}
				}
				else
				{
					if($this->m_sPlayer[$i]->kou_card[$tmp_start + 3][1] == 0)
					{
						if($chair == $i && !$is_act)
						{
							$this->m_sPlayer[$i]->kou_card[$tmp_start][1] = $yes;
							$this->m_sPlayer[$i]->kou_card[$tmp_start + 1][1] = $yes;
							$this->m_sPlayer[$i]->kou_card[$tmp_start + 2][1] = $yes;
							$this->m_sPlayer[$i]->kou_card[$tmp_start + 3][1] = $yes;
							$is_act = true;
							$act_n = $n;
						}
						else
						{
							$send = false;
						}
					}
				}
				
			}

			for ($j = 0; $j<$this->m_rule->player_count; ++$j)
			{
				if($send && $n < 2 && $this->m_sPlayer[$j]->kou_card[$tmp_start + 3][1] == 2 && $this->m_sPlayer[$j]->kou_card[$tmp_start + 7][1] == 0)
				{
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 4][1] = 2;
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 5][1] = 2;
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 6][1] = 2;
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 7][1] = 2;
				}

				if($send && $n == 2 && $this->m_sPlayer[$j]->kou_card[$tmp_start + 3][1] == 2 && $this->m_sPlayer[$j]->kou_card[$tmp_start + 4][1] == 0)
				{
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 4][1] = 2;
				}
			}

			if(!$send)
			{
				break;
			}
			else
			{
				$act_n = $n;
			}

			if($n >= 3)
			{
				break;
			}
		}

		if($n > $act_n)
		{
			$this->start_kou_card();
			return true;
		}
		else if($send)
		{
			$this->game_to_playing();
			return true;
		}

		return true;
	}

	//判断胡
	public function judge_hu($chair, $is_fanhun = false)
	{
		//胡牌型
		$is_qingyise = false;
		$is_ziyise = false;
		$is_yitiaolong = false;
		//$is_wukui = false;
		$hu_type = $this->judge_hu_type($chair, $is_qingyise, $is_ziyise, $is_yitiaolong);

		if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
		{
			return false;
		}
		
		//记录在全局数据
		$this->m_HuCurt[$chair]->method[0] = $hu_type;
		$this->m_HuCurt[$chair]->count = 1;

		//抢杠
		if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)	// 处理抢杠
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
		}

		//清一色
		if($is_qingyise && !empty($this->m_rule->is_qingyise_fan))
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QINGYISE);			
		}

		//字一色
		if($is_ziyise && !empty($this->m_rule->is_ziyise_fan))
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZIYISE);			
		}

		//一条龙
		if($is_yitiaolong && $this->m_rule->is_yitiaolong_fan)
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_YITIAOLONG);			
		}

        //捉五魁
        // if($is_wukui && $this->m_rule->is_zhuowukui_fan)
        // {
        //     $this->m_HuCurt[$chair]->add_hu(self::ATTCHED_HU_ZHUOWUKUI);
        // }

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

		if(isset(self::$hu_type_arr[$hu_type]))
		{
			$fan_sum = self::$hu_type_arr[$hu_type][1];
			$tmp_hu_desc .= self::$hu_type_arr[$hu_type][2].' ';
		}

		for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
		{
			if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
			{
				$fan_sum *= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
				$tmp_hu_desc .= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
			}
		}

		///扣四
		if(!empty($this->m_rule->is_kou_card))
		{
			$tmp_times = 1;
			if(($this->m_sPlayer[$chair]->kou_card[12][1] == 1 || $this->m_sPlayer[$chair]->kou_card[12][1] == 3) && !empty($this->m_rule->is_kou13))
			{
				$tmp_times = 16;
				$tmp_hu_desc .= '扣 扣 扣 扣 ';
			}
			elseif($this->m_sPlayer[$chair]->kou_card[11][1] == 1 || $this->m_sPlayer[$chair]->kou_card[11][1] == 3)
			{
				$tmp_times = 8;
				$tmp_hu_desc .= '扣 扣 扣 ';
			}
			else if($this->m_sPlayer[$chair]->kou_card[7][1] == 1 || $this->m_sPlayer[$chair]->kou_card[7][1] == 3)
			{
				$tmp_times = 4;
				$tmp_hu_desc .= '扣 扣 ';
			}
			else if($this->m_sPlayer[$chair]->kou_card[3][1] == 1 || $this->m_sPlayer[$chair]->kou_card[3][1] == 3)
			{
				$tmp_times = 2;
				$tmp_hu_desc .= '扣 ';
			}
			else
			{
				$tmp_times = 1;
			}

			$dajiang_num_all = 0;

			if(!empty($this->m_rule->is_kou_dajiang) && $tmp_times >= 2)
			{
				//计算扣大将
				for ($i=0; $i < 3; $i++)
				{ 
					$tmp_dajiang = [0, 0, 0, 0, 0, 0, 0, 0];
					for ($j=0; $j < 4; $j++)
					{ 
						if($this->_get_card_type($this->m_sPlayer[$chair]->kou_card[$i * 4 + $j][0]) == ConstConfig::PAI_TYPE_FENG
							&& ($this->m_sPlayer[$chair]->kou_card[$i * 4 + $j][1] == 1 || $this->m_sPlayer[$chair]->kou_card[$i * 4 + $j][1] == 3)
						)
						{
							$tmp_dajiang[$this->m_sPlayer[$chair]->kou_card[$i * 4 + $j][0] % 16] += 1;
						}
					}
					$dajiang_num = array_count_values($tmp_dajiang);
					if(!empty($dajiang_num[1]))
					{
						$dajiang_num_all += $dajiang_num[1];
					}
				}

				if(!empty($this->m_rule->is_kou13) && $this->_get_card_type($this->m_sPlayer[$chair]->kou_card[12][0]) == ConstConfig::PAI_TYPE_FENG
							&& ($this->m_sPlayer[$chair]->kou_card[12][1] == 1 || $this->m_sPlayer[$chair]->kou_card[12][1] == 3)
						)
						{
							$dajiang_num_all += 1;
						}

				if($dajiang_num_all > 0)
				{
					$tmp_times *= pow(2, $dajiang_num_all);
					$tmp_hu_desc .= '扣大将×'.$dajiang_num_all.' ';					
				}
			}

			$fan_sum *= $tmp_times;
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
	public function judge_hu_type($chair, &$is_qingyise, &$is_ziyise, &$is_yitiaolong)
	{
		$jiang_arr = array();
		$qidui_arr = array();
		$qing_arr = array();
		$shisanyao_arr = array();
		$gen_arr = array();

		$bType32 = false;
		$bQiDui = false;

		$bShiSanYao = false;
		$is_qingyise = false;
		$is_ziyise = false;
		$is_yitiaolong = false;
        
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
				$qing_arr[] = $i;
				$shisanyao_arr[] = 0;
			}
			else
			{
				$hu_list_val = $tmp_hu_data[$key];
				//1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen
				if($this->m_rule->is_yitiaolong_fan && ($hu_list_val & 256) == 256)
				{
					$is_yitiaolong = true;
				}
				$qidui_arr[] = $hu_list_val & 64;
				$shisanyao_arr[] = $hu_list_val & 128;
				$gen_arr[] = intval($hu_list_val/4096);

				if(($hu_list_val & 1) == 1)
				{
				    //如果没有将牌咋办
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

			// if(ConstConfig::DAO_PAI_TYPE_KE == $this->m_sStandCard[$chair]->type[$i] && $this->m_sPlayer[$chair]->card[$stand_pai_type][$stand_pai_key] > 0)
			// {
			// 	//手牌，倒牌组合根
			// 	$gen_arr[] = 1;
			// }
		}

		$bType32 = (32 == array_sum($jiang_arr));
		$bQiDui = !array_keys($qidui_arr, 0);
		$bShiSanYao = !array_keys($shisanyao_arr, 0);

		/////////////////////////////附加 番型的处理/////////////////////////////////
		//一色结果
		$this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);

		//一条龙前面处理过
		//打八张判断
		if(!empty($this->m_rule->is_da8zhang))
		{
			$is_da8zhang = $this->_judge_da8zhang($chair,null,false,true);
		}

		if(!empty($this->m_rule->is_da8zhang) && !$is_da8zhang)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}

		//////////////////////基本牌型的处理///////////////////////////////
		if(!$bType32 && !$bQiDui && !$bShiSanYao)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}

		if($this->m_rule->is_shisanyao_fan && $this->m_rule->is_feng && $bShiSanYao)
		{
			return self::HU_TYPE_SHISANYAO;
		}

		if($bQiDui && $this->m_rule->is_qidui_fan )
		{
			if(array_sum($gen_arr) >= 3)
			{
				return self::HU_TYPE_ZHUIZUN_QIDUI;
			}
			elseif(array_sum($gen_arr) == 2)
			{
				return self::HU_TYPE_CHAOJI_QIDUI;
			}
			elseif(array_sum($gen_arr) == 1)				
			{
				return self::HU_TYPE_HAOHUA_QIDUI;
			}

			return self::HU_TYPE_QIDUI ;
		}

		if($bType32)
		{
			return self::HU_TYPE_PINGHU;
		}

		return self::HU_TYPE_FENGDING_TYPE_INVALID;
	}
	
	//------------------------------------- 命令处理函数 -----------------------------------
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

		//处理扣牌
        $this->_change_koucard($chair, $temp_card, 'angang');

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
			$nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;
			$this->m_wGangScore[$i][$i] -= $nGangScore;				//总刮风下雨分
			$this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
			$this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
			$nGangPao += $nGangScore;
		}

		$this->_set_record_game(ConstConfig::RECORD_ANGANG, $chair, $temp_card, $chair);

		$this->m_sGangPao->init_data(true, $gang_card, $chair, ConstConfig::DAO_PAI_TYPE_ANGANG, $nGangPao);

		$this->m_wTotalScore[$chair]->n_angang += 1;
		$this->countGang += 1;

		// 补发张牌给玩家
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
		$this->m_chairCurrentPlayer = $chair;
		if($this->countGang < 4)
		{
			$this->DealCard($chair);
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
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		//判断四杠荒庄
		$this->_is_4gang_huangzhuang();
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

		//处理扣牌
        $this->_change_koucard($chair, $temp_card, 'zhigang');
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
		
		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
			if ($i == $chair)
			{
				continue;
			}
			$nGangScore =self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;
			$this->m_wGangScore[$i][$i] -= $nGangScore;				//总刮风下雨分
			$this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
			$this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

			$nGangPao += $nGangScore;
		}

		$this->_set_record_game(ConstConfig::RECORD_ZHIGANG, $chair, $temp_card, $this->m_sOutedCard->chair);

		$this->m_sGangPao->init_data(true, $temp_card, $chair,ConstConfig::DAO_PAI_TYPE_MINGGANG, $nGangPao);

		$this->m_wTotalScore[$chair]->n_zhigang_wangang += 1;
		$this->countGang += 1;
		
		//开直杠跟庄处理
		if(!empty($this->m_rule->is_genzhuang) && $this->m_sFollowCard->status == ConstConfig::FOLLOW_STATUS && 0 == $this->m_sFollowCard->follow_card && $this->m_sOutedCard->chair == $this->m_nChairBanker)
		{
			$this->is_gang_genzhuang = 1;
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
		else
		{
			$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
		}

		// 补发张牌给玩家
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
		$this->m_chairCurrentPlayer = $chair;
		if($this->countGang < 4)
		{
			$this->DealCard($chair);
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
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		//判断四杠荒庄
		$this->_is_4gang_huangzhuang();
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
                $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair_next), Game_cmd::SCO_SINGLE_PLAYER,
                    $this->m_room_players[$chair_next]['uid']);
            }
            else
            {
                //判断是否有胡
                $this->_list_insert($chair_next, $this->m_sOutedCard->card);
                $this->m_HuCurt[$chair_next]->card = $this->m_sOutedCard->card;
                $tmp_c_hu_result = ( $this->m_is_ting_arr[$chair_next] && !(self::is_hu_give_up($this->m_sOutedCard->card, $this->m_nHuGiveUp[$chair_next])) && $this->judge_hu($chair_next));
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
			if ($this->m_sQiangGang->mark)
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

		if ($this->m_sQiangGang->mark)
		{
			$temp_card = $this->m_sQiangGang->card;
            $bHaveHu = false;
            $record_hu_chair = array();
            $this->_do_c_hu($temp_card, $this->m_sQiangGang->chair, $bHaveHu, $record_hu_chair);
            $this->m_sGangPao->clear();

            if ($bHaveHu)
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
                        $this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] = ConstConfig::DAO_PAI_TYPE_KE;
                        break;
                    }
                }

                $this->m_nEndReason = ConstConfig::END_REASON_HU;
                $this->HandleSetOver();
                return;
            }
            else    // 给杠的玩家补张
            {
                $GangScore = 0;
                $nGangPao = 0;
                if(!empty($this->m_rule->is_wangang_1_lose))
				{
					//弯杠 扣 点碰玩家分数
					for ($i = 0; $i < $this->m_sStandCard[$this->m_sQiangGang->chair]->num; $i ++)
					{
						if ($this->m_sStandCard[$this->m_sQiangGang->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
						&& $this->m_sStandCard[$this->m_sQiangGang->chair]->card[$i] == $this->m_sQiangGang->card)
						{
							$nGangScore = self::M_WANGANG_SCORE *ConstConfig::SCORE_BASE;

							$tmp_who_give_me = $this->m_sStandCard[$this->m_sQiangGang->chair]->who_give_me[$i];
							//$this->m_wGFXYScore[$tmp_who_give_me] = -$nGangScore;
							$this->m_wGangScore[$tmp_who_give_me][$tmp_who_give_me] -= $nGangScore;

							//$this->m_wGFXYScore[$this->m_sQiangGang->chair] += $nGangScore;
							$this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
							$this->m_wGangScore[$this->m_sQiangGang->chair][$tmp_who_give_me] += $nGangScore;

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
	                    $nGangScore = self::M_WANGANG_SCORE;

	                    $this->m_wGangScore[$i][$i] -= $nGangScore;
	                    $this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
	                    $this->m_wGangScore[$this->m_sQiangGang->chair][$i] += $nGangScore;

	                    $nGangPao += $nGangScore;
	                }
				}

                $this->m_sGangPao->init_data(true, $this->m_sQiangGang->card, $this->m_sQiangGang->chair, ConstConfig::DAO_PAI_TYPE_WANGANG, $nGangPao);

                $this->_set_record_game(ConstConfig::RECORD_ZHUANGANG, $this->m_sQiangGang->chair, $this->m_sQiangGang->card, $this->m_sQiangGang->chair);

                $this->m_wTotalScore[$this->m_sQiangGang->chair]->n_zhigang_wangang += 1;
                $this->countGang += 1;
                
                //更改状态
                $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
                $this->m_chairCurrentPlayer = $this->m_sQiangGang->chair;
                $this->m_bHaveGang = true; //for 杠上花

                //摸杠需要记录入命令
                $this->m_chairSendCmd = $this->m_chairCurrentPlayer;
                $this->m_currentCmd = 'c_wan_gang';

                if($this->countGang < 4)
				{
					$this->DealCard($this->m_chairCurrentPlayer);
				}

                if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
                {
                    return;
                }

                //状态变化发消息
                $this->_send_act($this->m_currentCmd, $this->m_sQiangGang->chair, $this->m_sQiangGang->card);
                $this->handle_flee_play(true);	//更新断线用户
                $this->m_sQiangGang->clear();

                for ($i=0; $i < $this->m_rule->player_count ; $i++)
                {
                    $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
                }

				//判断四杠荒庄
				$this->_is_4gang_huangzhuang();
                return;
            }

		}
		else
		{
			$bHaveHu = false;
			$record_hu_chair = array();
			$temp_card = $this->m_sOutedCard->card;

			$this->_do_c_hu($temp_card, $this->m_sOutedCard->chair, $bHaveHu, $record_hu_chair);

            $this->m_sQiangGang->clear();
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

		//扣四阶段
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_KOU_CARD)
		{
			for ($i=0; $i<$this->m_rule->player_count; $i++)
			{
				if($i == $chair)
				{
					$data['kou_card'][$i] = $this->_sub_kou_arr($i);
					$data['kou_card_display'][$i] = $this->m_sPlayer[$i]->kou_card_display;
				}
				else
				{
					$data['kou_card'][$i] = array();
					$data['kou_card_display'][$i] = $this->_set_kou_arr($i, false);
				}
			}
			return $data;
		}
		
		if($this->m_sysPhase == ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD || $this->m_sysPhase == ConstConfig::SYSTEMPHASE_CHOOSING)
		{
			$data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;								// 当前出牌者
			$data['m_nNumTableCards'] = $this->m_nNumTableCards;		// 玩家桌面牌数量
			$data['m_nTableCards'] = $this->m_nTableCards;	// 玩家桌面牌
			$data['m_sStandCard'] = $this->m_sStandCard;		// 玩家倒牌
			$data['m_sOutedCard'] = $this->m_sOutedCard;		//刚出的牌

			for ($i=0; $i<$this->m_rule->player_count; $i++)
			{
				if($i == $chair)
				{
					$data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
					$data['m_bChooseBuf'] = $this->m_bChooseBuf[$i];			 //命令缓冲
					$data['m_nHuGiveUp'] = $this->m_nHuGiveUp[$i];
					$data['m_only_out_card'] = $this->m_only_out_card[$i];	

					$data['kou_card'][$i] = $this->m_sPlayer[$i]->kou_card;
					$data['kou_card_display'][$i] = $this->m_sPlayer[$i]->kou_card_display;
				}
				else
				{
					$data['m_sPlayer'][$i] = (object)null;
					
					$data['kou_card'][$i] = array();
					$data['kou_card_display'][$i] = $this->_set_kou_arr($i, false);
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

			for ($i=0; $i<$this->m_rule->player_count; $i++)
			{
				$data['kou_card'][$i] = $this->m_sPlayer[$i]->kou_card;
				$data['kou_card_display'][$i] = $this->m_sPlayer[$i]->kou_card_display;
			}

			return $data;
		}
		return true;
	}

	///////////////////////得分处理///////////////////////////
	//每局个人  +=赢的分  +=输的分  +=庄家 的分
	public function ScoreOneHuCal($chair, &$lost_chair)  
	{
		$fan_sum = $this->judge_fan($chair);
		$PerWinScore = $fan_sum;	

		$wWinScore = 0;
		$this->m_wHuScore = [0,0,0,0];


		if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		{
			for($i = 0; $i < $this->m_rule->player_count; $i++)
			{
				if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
				{
					continue;
				}

				$banker_fan = 1;
				if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
				{
					$banker_fan = 2;
				}

				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
				$wWinScore = 0;
				$wWinScore += 2 * ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;  //自摸翻倍了

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
			//砸胡免杠
			if(!empty($this->m_rule->is_za_hu_mian_gang))
			{
				for($j = 0; $j < $this->m_rule->player_count; $j++)
				{
					$k = $lost_chair;
					//退回玩家k赢玩家j的杠分
					if ($k != $j && $this->m_wGangScore[$k][$j] > 0)
					{
						$this->m_wGangScore[$j][$j] += $this->m_wGangScore[$k][$j];
						$this->m_wGangScore[$k][$k] -= $this->m_wGangScore[$k][$j];
						$this->m_wGangScore[$k][$j] = 0;
					}
				}
			}

			//点炮大包
			if(!empty($this->m_rule->is_dianpao_bao) && $this->m_rule->is_dianpao_bao == 1)
			{
				$tmp_win_score = 0;
				for($i = 0; $i < $this->m_rule->player_count; $i++)
				{
					if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
					{
						continue;	//单用户测试需要关掉
					}

					$banker_fan = 1;
					if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
					{
						$banker_fan = 2;
					}

					$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
					$wWinScore = 0;
                    $dianpao = $lost_chair == $i ? 2 : 1;
					$wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $dianpao * $banker_fan;

					$tmp_win_score += $wWinScore;

					$this->m_HuCurt[$chair]->gain_chair[0]++;
					$this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $lost_chair;
				}

				$tmp_win_score = $this->_get_max_fan($tmp_win_score);

				$this->m_wHuScore[$lost_chair] -= $tmp_win_score;
				$this->m_wHuScore[$chair] += $tmp_win_score;

				$this->m_wSetLoseScore[$lost_chair] -= $tmp_win_score;
				$this->m_wSetScore[$chair] += $tmp_win_score;

			}
			else if(!empty($this->m_rule->is_dianpao_bao) && $this->m_rule->is_dianpao_bao == 2)
			{
				//点炮三家出
				for($i = 0; $i < $this->m_rule->player_count; $i++)
				{
					if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
					{
						continue;	//单用户测试需要关掉
					}

					$banker_fan = 1;
					if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
					{
						$banker_fan = 2;
					}
					
					$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;

                    $dianpao = $lost_chair == $i ? 2 : 1;
					$wWinScore = 0;
					$wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $dianpao * $banker_fan;

					$wWinScore = $this->_get_max_fan($wWinScore);

					$this->m_wHuScore[$i] -= $wWinScore;
					$this->m_wHuScore[$chair] += $wWinScore;

					$this->m_wSetLoseScore[$i] -= $wWinScore;
					$this->m_wSetScore[$chair] += $wWinScore;

					$this->m_HuCurt[$chair]->gain_chair[0]++;
					$this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $i;
				}
			}
			else if(empty($this->m_rule->is_dianpao_bao))
			{
				//点炮一家出
				$banker_fan = 1;
				if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair))
				{
					$banker_fan = 2;
				}

				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;	
				$wWinScore = 0;
				$wWinScore += 2 * ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;	//点炮加倍

				$wWinScore = $this->_get_max_fan($wWinScore);

				$this->m_wHuScore[$lost_chair] -= $wWinScore;
				$this->m_wHuScore[$chair] += $wWinScore;

				$this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
				$this->m_wSetScore[$chair] += $wWinScore;

				$this->m_HuCurt[$chair]->gain_chair[0] = 1;
				$this->m_HuCurt[$chair]->gain_chair[1]=$lost_chair;
			}

			return true;
		}

		echo("此人没有胡".__LINE__.__CLASS__);
		return false;
	}

	//批量发牌，扣牌13张
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

				//扣四
				if(!empty($this->m_rule->is_kou_card))
				{
					if($kou_num < 13)
					{
						$this->m_sPlayer[$i]->kou_card[] = [$temp_card, 0];
					}
					$kou_num++;
				}
			}
		}
	}

	//游戏开始，扣牌13张
	public function game_to_playing()
	{
		$tmp_card_arr = $this->m_deal_card_arr;
		for ($n=0; $n <= 3; $n++)
		{
			$this->_set_record_game(ConstConfig::RECORD_DRAW_ALL, intval($tmp_card_arr[$n][0]), intval($tmp_card_arr[$n][1]), intval($tmp_card_arr[$n][2]), intval($tmp_card_arr[$n][3]));

			//扣四
			if(!empty($this->m_rule->is_kou_card))
			{
				$record_arr = array('', '', '', '');
				$tmp_start = $n * 4;
				for ($i = 0; $i<$this->m_rule->player_count; ++$i)
				{
					
					if($n == 3)
					{
						if($this->m_sPlayer[$i]->kou_card[$tmp_start][1] == 1)
						{
							$record_arr[$i] = sprintf("%02d",$this->m_sPlayer[$i]->kou_card[$tmp_start][0]);
						}
					}
					elseif($this->m_sPlayer[$i]->kou_card[$tmp_start + 3][1] == 1)
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
	}

	public function _change_koucard($chair, $gang_card, $gang_type)
    {

        if(!empty($this->m_rule->is_kou_card))
        {
            $tmp_times = 0;
            $max_times = [
                'zhigang' => 3,
                'wangang' => 1,
                'angang'  => 4,
                'peng'    => 2
            ];

            $kou_num = 12;
			if(!empty($this->m_rule->is_kou13))
			{
				$kou_num = 13;
			}
			for ($i=0; $i < $kou_num; $i++)
            {
                if($this->m_sPlayer[$chair]->kou_card[$i][0] == $gang_card && $this->m_sPlayer[$chair]->kou_card[$i][1] == 1)
                {
                    $this->m_sPlayer[$chair]->kou_card[$i][1] = 3;
                    $tmp_times++;
                }
                if(isset($max_times[$gang_type]) && $tmp_times >= $max_times[$gang_type])
                {
                    break;
                }
            }
            $this->m_sPlayer[$chair]->kou_card_display = $this->_set_kou_arr($chair, true);
        }
    }

    //跟庄
	public function _genzhuang_do()
	{
		if( !empty($this->m_rule->is_genzhuang) && $this->m_sFollowCard->status == ConstConfig::FOLLOW_STATUS &&  4 == $this->m_rule->player_count )
		{
			if($this->m_sOutedCard->chair == $this->m_nChairBanker)
			{
				$this->is_gang_genzhuang = 0;
			}
			if($this->is_gang_genzhuang == 0)
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

	public function _judge_da8zhang($chair, $replace_fanhun , $is_fanhun = false, $rule_no_fanhun = false, $add_fanhun_num = 0)
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
				$da8zhang_fanhun_num = $this->_list_find($chair, $this->m_hun_card);	//手牌翻混个数
				$da8zhang_fanhun_type = $this->_get_card_type($this->m_hun_card);        //翻混牌类型		
			
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
					if(($this->m_sPlayer[$chair]->card[$k][0] + $tmp_stand_num - $da8zhang_fanhun_num + $replace_fanhun[$k]) + $add_fanhun_num >= 8 && $k == $da8zhang_fanhun_type)
					{
						$is_da8zhang = true;
						break;
					}
					elseif(($this->m_sPlayer[$chair]->card[$k][0] + $tmp_stand_num + $replace_fanhun[$k] + $add_fanhun_num) >= 8 && $k != $da8zhang_fanhun_type)
					{
						$is_da8zhang = true;
						break;
					}
				}
			}
		}

		return $is_da8zhang;										
	}

	//倒牌某门牌的个数，打八张用
	public function _stand_type_count($chair,$card_type)
	{
		$card_num = 0;

		if( $this->m_sStandCard[$chair]->num > 0)//有倒牌
		{
			for($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
			{
				if($this->_get_card_type($this->m_sStandCard[$chair]->card[$i]) == $card_type)
				{
					if(ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i] || ConstConfig::DAO_PAI_TYPE_KE == $this->m_sStandCard[$chair]->type[$i])
					{
						 $card_num += 3 ;
					}
					elseif(ConstConfig::DAO_PAI_TYPE_MINGGANG == $this->m_sStandCard[$chair]->type[$i] || ConstConfig::DAO_PAI_TYPE_ANGANG == $this->m_sStandCard[$chair]->type[$i] || ConstConfig::DAO_PAI_TYPE_WANGANG == $this->m_sStandCard[$chair]->type[$i])
					{
						$card_num += 3;
					}
				}
			}		
		}
		return $card_num;
	}

	//与BaseGame的不一样，此为扣13张牌的情况
	public function _sub_kou_arr($chair)
	{
		$tmp_kou_arr = $this->m_sPlayer[$chair]->kou_card;

		for ($i=0; $i <= 3; $i++)
		{ 
			if($i == 3)
			{
				if($this->m_sPlayer[$chair]->kou_card[$i*4][1] == 0)
				{
					if($this->m_sPlayer[$chair]->kou_card[$i*4 - 1][1] == 1)
					{
						return array_slice($this->m_sPlayer[$chair]->kou_card, 0, ($i*4+1));
					}
					else if($this->m_sPlayer[$chair]->kou_card[$i*4 - 1][1] == 2)
					{
						return array_slice($this->m_sPlayer[$chair]->kou_card, 0, ($i*4-4));
					}
				}
			}
			elseif($this->m_sPlayer[$chair]->kou_card[$i*4 + 3][1] == 0)
			{
				if(!isset($this->m_sPlayer[$chair]->kou_card[$i*4 - 1]) || $this->m_sPlayer[$chair]->kou_card[$i*4 - 1][1] == 1)
				{
					return array_slice($this->m_sPlayer[$chair]->kou_card, 0, ($i*4+4));
				}
				else if($this->m_sPlayer[$chair]->kou_card[$i*4 - 1][1] == 2)
				{
					return array_slice($this->m_sPlayer[$chair]->kou_card, 0, ($i*4));
				}
			}
		}
		return array_slice($this->m_sPlayer[$chair]->kou_card, 0, 13);
	}

	public function _is_4gang_huangzhuang()
	{
		if ($this->countGang >=4)
        {
            $this->m_nEndReason = ConstConfig::END_REASON_NOCARD;
            $this->HandleSetOver();
            return true;
        }
	}

}
