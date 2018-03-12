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
use gf\inc\BaseGame;

class GameGaobeidian extends BaseGame
{
	const GAME_TYPE = 128;
	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21;                  // 平胡
	const HU_TYPE_SHISANYAO = 22;               // 十三幺...
	const HU_TYPE_QIDUI = 23;                   // 七对
	const HU_TYPE_HAOHUA_QIDUI = 24;            // 豪华七对....
	const HU_TYPE_CHAOJI_QIDUI = 25;            // 超级豪华七对....
	const HU_TYPE_ZHUIZUN_QIDUI = 26;           // 至尊豪华七对....
	const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   // 错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_TIANHU = 61;              // 天胡 
	const ATTACHED_HU_DIHU = 62;                // 地胡 
	const ATTACHED_HU_ZIMOFAN = 63;             // 自摸     默认
	const ATTACHED_HU_GANGKAI = 64;             // 杠开
	const ATTACHED_HU_QIANGGANG = 65;           // 抢杠

	const ATTACHED_HU_QINGYISE = 66;            // 清一色
	const ATTACHED_HU_YITIAOLONG = 67;          // 一条龙
	const ATTACHED_HU_HAIDI = 68;               // 海底捞月 默认
	const ATTACHED_HU_DA8ZHANG = 69;            // 打八张   默认 
	const ATTACHED_HU_MENQING = 70;             // 门清
	const ATTACHED_HU_ZIYISE = 71;              // 风一色

	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 1;                 // 直杠 1分
	const M_ANGANG_SCORE = 1;                  // 暗杠 1分
	const M_WANGANG_SCORE = 1;                 // 弯杠 1分

	public static $hu_type_arr = array(
		self::HU_TYPE_PINGHU=>[self::HU_TYPE_PINGHU, 3, '平胡']
		,self::HU_TYPE_SHISANYAO=>[self::HU_TYPE_SHISANYAO, 30, '十三幺']
		,self::HU_TYPE_QIDUI=>[self::HU_TYPE_QIDUI, 6, '七对']
		,self::HU_TYPE_HAOHUA_QIDUI=>[self::HU_TYPE_HAOHUA_QIDUI, 12, '豪华七对']
		,self::HU_TYPE_CHAOJI_QIDUI=>[self::HU_TYPE_CHAOJI_QIDUI, 24, '超级豪华七对']
		,self::HU_TYPE_ZHUIZUN_QIDUI=>[self::HU_TYPE_ZHUIZUN_QIDUI, 48, '至尊豪华七对']
	);

	public static $attached_hu_arr = array(
		self::ATTACHED_HU_TIANHU=>[self::ATTACHED_HU_TIANHU, 8, '天胡']
		,self::ATTACHED_HU_DIHU=>[self::ATTACHED_HU_DIHU, 4, '地胡']
		,self::ATTACHED_HU_ZIMOFAN=>[self::ATTACHED_HU_ZIMOFAN, 0, '自摸']
		,self::ATTACHED_HU_GANGKAI=>[self::ATTACHED_HU_GANGKAI, 2, '杠上花']
		,self::ATTACHED_HU_QIANGGANG=>[self::ATTACHED_HU_QIANGGANG, 2, '抢杠']

		,self::ATTACHED_HU_QINGYISE=>[self::ATTACHED_HU_QINGYISE, 4, '清一色']
		,self::ATTACHED_HU_ZIYISE=>[self::ATTACHED_HU_ZIYISE, 4, '风一色']
		,self::ATTACHED_HU_YITIAOLONG=>[self::ATTACHED_HU_YITIAOLONG, 2, '一条龙']
		,self::ATTACHED_HU_HAIDI=>[self::ATTACHED_HU_HAIDI, 2, '海底捞月']	
		,self::ATTACHED_HU_MENQING=>[self::ATTACHED_HU_MENQING, 2, '门清']
		,self::ATTACHED_HU_DA8ZHANG=>[self::ATTACHED_HU_DA8ZHANG, 0, '打八张']
	);	

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
    }

	public function _open_room_sub($params)
    {
        $this->m_rule = new RuleGaobeidian();
		if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3, 4)))
		{
			$params['rule']['player_count'] = 4;
		}

		$params['rule']['min_fan'] = !isset($params['rule']['min_fan']) ? 0 : $params['rule']['min_fan'];
		$params['rule']['top_fan'] = !isset($params['rule']['top_fan']) ? 0 : $params['rule']['top_fan'];
		$params['rule']['is_circle'] = !isset($params['rule']['is_circle']) ? 0 : $params['rule']['is_circle'];
		//$params['rule']['set_num'] = 4;	//test
		$params['rule']['is_feng'] = !isset($params['rule']['is_feng']) ? 1 : $params['rule']['is_feng'];
		$params['rule']['is_yipao_duoxiang'] = !isset($params['rule']['is_yipao_duoxiang']) ? 0 : $params['rule']['is_yipao_duoxiang'];
		$params['rule']['is_chipai'] = !isset($params['rule']['is_chipai']) ? 0 : $params['rule']['is_chipai'];
		$params['rule']['is_paozi'] = !isset($params['rule']['is_paozi']) ? 0 : $params['rule']['is_paozi'];
		$params['rule']['is_qingyise_fan'] = !isset($params['rule']['is_qingyise_fan']) ? 1 : $params['rule']['is_qingyise_fan'];
		$params['rule']['is_ziyise_fan'] = !isset($params['rule']['is_ziyise_fan']) ? 1 : $params['rule']['is_ziyise_fan'];
		$params['rule']['is_yitiaolong_fan'] = !isset($params['rule']['is_yitiaolong_fan']) ? 1 : $params['rule']['is_yitiaolong_fan'];
		$params['rule']['is_shisanyao_fan'] = !isset($params['rule']['is_shisanyao_fan']) ? 1 : $params['rule']['is_shisanyao_fan'];
		$params['rule']['is_ganghua_fan'] = !isset($params['rule']['is_ganghua_fan']) ? 1 : $params['rule']['is_ganghua_fan'];
		$params['rule']['is_qidui_fan'] = !isset($params['rule']['is_qidui_fan']) ? 1 : $params['rule']['is_qidui_fan'];
		$params['rule']['is_tiandi_hu_fan'] = !isset($params['rule']['is_tiandi_hu_fan']) ? 1 : $params['rule']['is_tiandi_hu_fan'];

		$params['rule']['is_za_hu_mian_gang'] = !isset($params['rule']['is_za_hu_mian_gang']) ? 1 : $params['rule']['is_za_hu_mian_gang'];
		$params['rule']['is_dianpao_bao'] = !isset($params['rule']['is_dianpao_bao']) ? 0 : $params['rule']['is_dianpao_bao'];
		$params['rule']['is_kou_card'] = !isset($params['rule']['is_kou_card']) ? 0 : $params['rule']['is_kou_card'];
		$params['rule']['is_kou_dajiang'] = !isset($params['rule']['is_kou_dajiang']) ? 0 : $params['rule']['is_kou_dajiang'];
		$params['rule']['is_wangang_1_lose'] = !isset($params['rule']['is_wangang_1_lose']) ? 1 : $params['rule']['is_wangang_1_lose'];
		$params['rule']['is_menqing_fan'] = !isset($params['rule']['is_menqing_fan']) ? 0 : $params['rule']['is_menqing_fan'];
		//$params['rule']['is_zhuang_fan'] = !isset($params['rule']['is_zhuang_fan']) ? 1 : $params['rule']['is_zhuang_fan'];
		$params['rule']['cancle_clocker'] = !isset($params['rule']['cancle_clocker']) ? 1 : $params['rule']['cancle_clocker'];
		$params['rule']['pay_type'] = !isset($params['rule']['pay_type']) ? 0 : $params['rule']['pay_type'];

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
		$this->m_rule->is_yipao_duoxiang = $params['rule']['is_yipao_duoxiang'];
		$this->m_rule->is_fanhun = $params['rule']['is_fanhun'];
		$this->m_rule->is_chipai = $params['rule']['is_chipai'];
		$this->m_rule->is_genzhuang = $params['rule']['is_genzhuang'];
		$this->m_rule->is_da8zhang = $params['rule']['is_da8zhang'];
		$this->m_rule->is_paozi = $params['rule']['is_paozi'];
		$this->m_rule->is_za_hu_mian_gang = $params['rule']['is_za_hu_mian_gang'];  //默认规则 客户端可不传此 参数

		$this->m_rule->is_qingyise_fan = $params['rule']['is_qingyise_fan'];
		$this->m_rule->is_ziyise_fan = $params['rule']['is_ziyise_fan'];
		$this->m_rule->is_yitiaolong_fan = $params['rule']['is_yitiaolong_fan'];
		$this->m_rule->is_shisanyao_fan = $params['rule']['is_shisanyao_fan'];
		$this->m_rule->is_ganghua_fan = $params['rule']['is_ganghua_fan'];
		$this->m_rule->is_qidui_fan = $params['rule']['is_qidui_fan'];
		$this->m_rule->is_tiandi_hu_fan = $params['rule']['is_tiandi_hu_fan'];
		$this->m_rule->is_dianpao_bao = $params['rule']['is_dianpao_bao'];
		$this->m_rule->is_kou_card = $params['rule']['is_kou_card'];
		$this->m_rule->is_kou_dajiang = $params['rule']['is_kou_dajiang'];
		$this->m_rule->is_wangang_1_lose = $params['rule']['is_wangang_1_lose'];
		$this->m_rule->is_menqing_fan = $params['rule']['is_menqing_fan'];
		//$this->m_rule->is_zhuang_fan = $params['rule']['is_zhuang_fan'];
		$this->m_rule->cancle_clocker = $params['rule']['cancle_clocker'];
		$this->m_rule->pay_type = $params['rule']['pay_type'];
    }

	//扣四
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
					// if ($this->m_own_paozi[$key]->recv)
					// {
					// 	$return_send['code'] = 4; $return_send['text'] = '您扣牌结束了'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					// }
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

	public function handle_kou_card($chair, $yes)
	{
		$is_act = false;
		$act_n =	 0;
		$send = true;
		for ($n=0; $n <= 2; $n++)
		{
			$tmp_start = $n * 4;
			for ($i = 0; $i<$this->m_rule->player_count; ++$i)
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

			for ($j = 0; $j<$this->m_rule->player_count; ++$j)
			{
				if($send && $n < 2 && $this->m_sPlayer[$j]->kou_card[$tmp_start + 3][1] == 2 && $this->m_sPlayer[$j]->kou_card[$tmp_start + 7][1] == 0)
				{
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 4][1] = 2;
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 5][1] = 2;
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 6][1] = 2;
					$this->m_sPlayer[$j]->kou_card[$tmp_start + 7][1] = 2;
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

			if($n >= 2)
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
		$hu_type = $this->judge_hu_type_fanhun($chair, $is_qingyise, $is_yitiaolong, $is_ziyise, $is_fanhun);

		if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
		{
			return false;
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
					$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_TIANHU);
				}
				else
				{
					$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_DIHU);
				}
			}
			else if(0 == $this->m_nDiHu[$chair])
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_DIHU);
			}
		}

		//自摸加番
		// if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		// {
		// 	$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZIMOFAN);
		// }

		//抢杠杠开杠炮
		if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)	// 处理抢杠
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
		}
		else if(!empty($this->m_rule->is_ganghua_fan) && $this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair)	//杠开
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGKAI);
		}
		else if ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO && $this->m_sGangPao->mark && $this->m_sGangPao->chair != $chair)
		{
			//$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGPAO);
		}

		//清一色
		if($is_qingyise && $this->m_rule->is_qingyise_fan)
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

		//海底捞月
		if($this->m_nCountAllot >= $this->m_nAllCardNum - 5) //海底月
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_HAIDI);
		}

		//门清
		if(!empty($this->m_rule->is_menqing_fan) && $this->_is_menqing($chair) )
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_MENQING);
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

		if(isset(self::$hu_type_arr[$hu_type]))
		{
			$fan_sum = self::$hu_type_arr[$hu_type][1];
			$tmp_hu_desc .= self::$hu_type_arr[$hu_type][2].' ';
		}

		$attached_fan_sum = 0;
		for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
		{
			if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
			{
				$attached_fan_sum += self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
				$tmp_hu_desc .= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
			}
		}

		//保定附加番的时候不计平胡
		if($hu_type == self::HU_TYPE_PINGHU && !empty($attached_fan_sum))
		{
			$fan_sum = 3 * $attached_fan_sum;
		}
		else
		{
			$fan_sum += 3 * $attached_fan_sum;
		}
		
		//$fan_sum *= 3;

		//扣四
		if(!empty($this->m_rule->is_kou_card))
		{
			$tmp_times = 1;
			if($this->m_sPlayer[$chair]->kou_card[11][1] == 1 || $this->m_sPlayer[$chair]->kou_card[11][1] == 3)
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
				$bDajiang = false;
				for ($j=0; $j < 4; $j++)
				{ 
					if($this->_get_card_type($this->m_sPlayer[$chair]->kou_card[$j][0]) == ConstConfig::PAI_TYPE_FENG
						&& ($this->m_sPlayer[$chair]->kou_card[$j][1] == 1 || $this->m_sPlayer[$chair]->kou_card[$j][1] == 3)
					)
					{
						$bDajiang = true;
						break;
					}
				}

				if($bDajiang)
				{
					$tmp_times *= 2;
					$tmp_hu_desc .= '扣大将';					
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

	//判断翻混 
	public function judge_hu_type_fanhun($chair, &$is_qingyise, &$is_yitiaolong, &$is_ziyise, $is_fanhun = false)
	{
		$is_quemen = true;
		$fanhun_num = 0;
		$fanhun_type = 255;
		if($this->m_hun_card)
		{
			$fanhun_num = $this->_list_find($chair, $this->m_hun_card);	//手牌翻混个数
			$fanhun_type = $this->_get_card_type($this->m_hun_card);        //翻混牌类型
			$fanhun_card = $this->m_hun_card%16;       //翻混牌			
		}

		$fanhun_num = $is_fanhun ? $fanhun_num - 1 : $fanhun_num;	//打出的牌是否为翻混

		//判断缺门(考虑到翻混)
		$is_quemen = false;
		if(!empty($this->m_rule->is_quemen))
		{
			$is_quemen = $this->_is_quemen($chair, $fanhun_type, $fanhun_num);
		}

		if(0 == $this->m_rule->is_fanhun || 0 >= $fanhun_num)	//规则混子 或者 手牌无混中
		{
			return $this->judge_hu_type($chair, $is_qingyise, $is_yitiaolong, $is_ziyise, $is_quemen);
		}
		else
		{
			$return_type = self::HU_TYPE_FENGDING_TYPE_INVALID;	
			$is_da8zhang = false;
	
			//十三幺牌型，不计缺门
			if(!empty($this->m_rule->is_shisanyao_fan) && !empty($this->m_rule->is_feng))
			{
				$is_shisanyao = true;
				$tmp_card_follow = 0;

				$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;	//去掉翻混

				if($this->m_sStandCard[$chair]->num > 0)
				{
					$is_shisanyao = false;
				}
				else
				{
					for($i = ConstConfig::PAI_TYPE_WAN ; $i <= ConstConfig::PAI_TYPE_FENG ; $i++)
					{
						for($j=1; $j<=9; $j++)
						{
							if( $i == ConstConfig::PAI_TYPE_FENG || ( $i != ConstConfig::PAI_TYPE_FENG && ($j == 1 || $j == 9) ) )
							{
								if($this->m_sPlayer[$chair]->card[$i][$j] > 2)
								{
									$is_shisanyao = false; break 2;
								}
								elseif($this->m_sPlayer[$chair]->card[$i][$j] == 2)
								{
									if( 1 == $tmp_card_follow)  //有且只有一个对
									{
										$is_shisanyao = false; break 2;
									}
									else
									{
										$tmp_card_follow = 1;
									}
								}
								else if($this->m_sPlayer[$chair]->card[$i][$j] > 2)
								{
									$is_shisanyao = false; break 2;
								}
							}
							else if($this->m_sPlayer[$chair]->card[$i][$j] > 0)	//非13幺的牌
							{
								$is_shisanyao = false; break 2;
							}
						}
					}
				}
				//还原手牌中的翻混
				$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += $fanhun_num;	

				if($is_shisanyao)
				{
					$is_da8zhang = true;
					return self::HU_TYPE_SHISANYAO;
				}
			}

			if(!empty($this->m_rule->is_quemen) && !$is_quemen)
			{
				return self::HU_TYPE_FENGDING_TYPE_INVALID ;
			}

			//7对牌型
			if(!empty($this->m_rule->is_qidui_fan))
			{
				$need_fanhun = 0;	//需要混子个数	
				$qing_arr = array();
				$hu_qidui = false;
				$is_qingyise = false;
				$is_ziyise = false;
				$gen_count_num = 0;
				$da8zhang_replace_fanhun = array(0,0,0,0);
			 
				if($this->m_sStandCard[$chair]->num == 0)
				{	
					//去掉翻混
					$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;	

					for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
					{
						if(0 == $this->m_sPlayer[$chair]->card[$i][0] || (0 == ($this->m_sPlayer[$chair]->card[$i][0] - $fanhun_num) && $i == $fanhun_type && $this->m_sPlayer[$chair]->len > $fanhun_num))
						{
							continue;
						}
						else
						{
							$qing_arr[] = $i;
						}
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
						$gen_count_num += intval(($fanhun_num - $need_fanhun)/2);
					}

					//打八张
					if(!$is_da8zhang && !empty($this->m_rule->is_da8zhang))
					{
						$is_da8zhang = $this->_judge_da8zhang($chair, $da8zhang_replace_fanhun, $is_fanhun, false, ($fanhun_num - $need_fanhun));
						if(!$is_da8zhang)
						{
							$hu_qidui = false;
						}
					}

					if($hu_qidui)
					{
						$this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);

						if($gen_count_num >= 3)
						{
							return self::HU_TYPE_ZHUIZUN_QIDUI;
						}
						elseif($gen_count_num == 2)
						{
							return self::HU_TYPE_CHAOJI_QIDUI;
						}
						elseif($gen_count_num == 1)
						{
							return self::HU_TYPE_HAOHUA_QIDUI;						
						}
						else
						{
							return self::HU_TYPE_QIDUI;			
						}					
					}
				}
			}

			//32牌型
			$is_yitiaolong = false;
			$is_qingyise = false;
			$is_ziyise = false;

			//倒牌
			$qing_arr_stand = array();
			$pengpeng_arr_stand = array(1, 1, 1, 1);
			for($k=0; $k<$this->m_sStandCard[$chair]->num; $k++)
			{
				$tmp_stand_type = $this->_get_card_type( $this->m_sStandCard[$chair]->first_card[$k] );
				$qing_arr_stand[] = $tmp_stand_type;
				if(ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$k])
				{
					$pengpeng_arr_stand[$tmp_stand_type] = 0;
				}
			}
			$qing_arr = $qing_arr_stand;
			$pengpeng_arr = $pengpeng_arr_stand;

			$is_hu_data = false;
			$is_da8zhang = false;
			$yitiaolong_tmp = false;
			$max_hu = array(0=>-1);

			$jiang_judge_arr = array(0=>2,1=>1,2=>0,3=>2,4=>1,5=>0,6=>2,7=>1,8=>0,9=>2,10=>1,11=>0,12=>2,13=>1,14=>0);
			$no_jiang_judge_arr = array(0=>0,1=>2,2=>1,3=>0,4=>2,5=>1,6=>0,7=>2,8=>1,9=>0,10=>2,11=>1,12=>0);

			for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
			{
				if(0 == $this->m_sPlayer[$chair]->card[$i][0] || (0 == $this->m_sPlayer[$chair]->card[$i][0]-$fanhun_num && $i == $fanhun_type && $this->m_sPlayer[$chair]->len > $fanhun_num))
				{
					if(!in_array($i, $qing_arr_stand))
					{
						continue;
					}
				}

				$is_qingyise = false;
				$is_ziyise = false;
				$is_da8zhang = false;
				$yitiaolong_tmp = false;
				$qing_arr = $qing_arr_stand;
				$pengpeng_arr = $pengpeng_arr_stand;
				$is_hu_data = false;
				$jiang_type = $i;	//假设将牌是某一门
				$need_fanhun = 0;	//需要混个数
				$replace_fanhun = array(0,0,0,0);

				for($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
				{
					if(0 == $this->m_sPlayer[$chair]->card[$j][0] || ($j == $fanhun_type && 0 == $this->m_sPlayer[$chair]->card[$j][0]-$fanhun_num && $this->m_sPlayer[$chair]->len > $fanhun_num))
					{
						if(!in_array($j, $qing_arr_stand))
						{
							continue;
						}
					}

					$pai_num = $this->m_sPlayer[$chair]->card[$j][0];	//一门牌个数
					$pai_num = ($j == $fanhun_type) ? $pai_num - $fanhun_num : $pai_num;	//混牌的牌型个数得减去混牌个数

					if($pai_num > 0)
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
						if($j == $fanhun_type)
						{
							$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;	//去掉翻混
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

					if($replace_fanhun[$j] > 0)
					{
						$qing_arr[] = $j;
					}
				}

				if($need_fanhun <= $fanhun_num)
				{
					$is_check_hu = false;
					for($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
					{
						$yitiaolong_tmp = false;
						$is_da8zhang = false;
						$is_hu_data = false;
						$pengpeng_arr = $pengpeng_arr_stand;
						$max_type_hu_arr = array(0=>-1, 1=>$pengpeng_arr_stand, 0, 0);

						if(0 == $this->m_sPlayer[$chair]->card[$j][0] || ($this->m_sPlayer[$chair]->card[$j][0] == $fanhun_num && $j == $fanhun_type && $this->m_sPlayer[$chair]->len > $fanhun_num))
						{
							if(!in_array($j, $qing_arr_stand))
							{
								continue;
							}
						}
						if($fanhun_num == $need_fanhun && $is_check_hu)
						{
							continue;
						}
						$is_check_hu = true;

						$tmp_replace_fanhun = $replace_fanhun;
						$tmp_replace_fanhun[$j] += ($fanhun_num - $need_fanhun);
						
						if($tmp_replace_fanhun[$j] > 0)
						{
							$qing_arr[] = $j;
						}

						//打八张
						if(!$is_da8zhang && !empty($this->m_rule->is_da8zhang))
						{
							$is_da8zhang = $this->_judge_da8zhang($chair,$tmp_replace_fanhun,$is_fanhun, false, 0);
							if(!$is_da8zhang)
							{
								continue;
							}
						}

						//校验胡
						foreach ($tmp_replace_fanhun as $type => $num)
						{
							$type_len = $this->m_sPlayer[$chair]->card[$type][0] + $num;
							if($type == $fanhun_type)
							{
								$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = $is_fanhun ? 1 : 0;	//去掉翻混
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
							$tmp_is_big = false;
							$is_hu_data = false;
							$insert_pengpeng_arr = $max_type_hu_arr[1];
							$insert_yitiaolong = $max_type_hu_arr[2];
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
									$tmp_type_hu_num = 1;

									foreach ($insert_arr as $insert_item)
									{
										$this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
									}
									//碰碰胡和一条龙不会同时存在
									$tmp_pengpeng = $tmp_hu_data[$key] & 8;
									if($tmp_pengpeng && !array_keys($insert_pengpeng_arr, 0))
									{
										$tmp_type_hu_num += 1;
									}

									$tmp_type_yitiaolong = ($insert_yitiaolong || !empty($this->m_rule->is_yitiaolong_fan) && ($tmp_hu_data[$key] & 256) == 256);
									if($tmp_type_yitiaolong)
									{
										$tmp_type_hu_num += 2;
									}

									if($tmp_type_hu_num >= $max_type_hu_arr[0])
									{
										$tmp_is_big = true;
										$max_type_hu_arr[0] = $tmp_type_hu_num;
										if($insert_pengpeng_arr[$type])
										{
											$max_type_hu_arr[1][$type] = $tmp_pengpeng;
										}
										$max_type_hu_arr[2] = $tmp_type_yitiaolong;
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

							if($type == $fanhun_type)
							{
								$this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += $fanhun_num;
							}

							if($is_hu_data)
							{
								if(!$tmp_is_big)
								{
									if($insert_pengpeng_arr[$type])
									{
										$max_type_hu_arr[1][$type] = $tmp_pengpeng;
									}
								}
							}
							else
							{
								$max_type_hu_arr[0] = -1;
								break;
							}
						}

						if($max_type_hu_arr[0] > 0)
						{
							$tmp_max_hu = self::$hu_type_arr[self::HU_TYPE_PINGHU][1];
							if(!empty($this->m_rule->is_yitiaolong_fan) && $max_type_hu_arr[2])
							{
								$tmp_max_hu += self::$attached_hu_arr[self::ATTACHED_HU_YITIAOLONG][1];
							}
							if(!empty($this->m_rule->is_pengpenghu_fan) && !array_keys($max_type_hu_arr[1], 0))
							{
								$tmp_max_hu += self::$hu_type_arr[self::HU_TYPE_PENGPENGHU][1];
							}

							if(!empty($this->m_rule->is_qingyise_fan) || !empty($this->m_rule->is_ziyise_fan))
							{
								$tmp_is_qingyise = false;
								$tmp_is_ziyise = false;
								$this->_is_yise($qing_arr, $tmp_is_qingyise, $tmp_is_ziyise);

								if(!empty($this->m_rule->is_qingyise_fan) && $tmp_is_qingyise)
								{
									$tmp_max_hu += self::$attached_hu_arr[self::ATTACHED_HU_QINGYISE][1];
								}
								if(!empty($this->m_rule->is_ziyise_fan) && $tmp_is_ziyise)
								{
									$tmp_max_hu += self::$attached_hu_arr[self::ATTACHED_HU_ZIYISE][1];
								}
							}

							if($tmp_max_hu > $max_hu[0])
							{
								$max_hu[0] = $tmp_max_hu;
								$max_hu[2] = $max_type_hu_arr[2];
								$max_hu[1] = $max_type_hu_arr[1];
							}
						}

						if($max_hu[0] > self::$hu_type_arr[self::HU_TYPE_PINGHU][1] + self::$attached_hu_arr[self::ATTACHED_HU_QINGYISE][1])
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

			if($max_hu[0] >= 0)
			{
				$this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);

				if(!empty($this->m_rule->is_yitiaolong_fan) && $max_hu[2])
				{
					$is_yitiaolong = $max_hu[2];
				}
				if(!empty($this->m_rule->is_pengpenghu_fan) && !empty($max_hu[1]) && !array_keys($max_hu[1], 0))
				{
					return self::HU_TYPE_PENGPENGHU;
				}
				return self::HU_TYPE_PINGHU;
			}
			return $return_type;
		}
	}

	//胡牌类型判断  没有混的情况
	public function judge_hu_type($chair, &$is_qingyise, &$is_yitiaolong, &$is_ziyise, $is_quemen)
	{
		$jiang_arr = array();
		$qidui_arr = array();
		$qing_arr = array();
		$shisanyao_arr = array();
		$pengpeng_arr = array();
		$gen_arr = array();
		
		$bType32 = false;
		$bQiDui = false;
		$bPengPeng = false;  
		$bShiSanYao = false;    //13幺

		$is_qingyise = false;
		$is_ziyise = false;
		$is_yitiaolong = false;   //一条龙
		$is_da8zhang = false;

		//手牌
		for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG; $i++)
		{
			if(0 == $this->m_sPlayer[$chair]->card[$i][0])
			{
				$shisanyao_arr[] = 0;
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
				//return self::HU_TYPE_FENGDING_TYPE_INVALID ;
				$jiang_arr[] = 32; $jiang_arr[] = 32;
				$qidui_arr[] = 0;
				$qing_arr[] = $i;
				$pengpeng_arr[] = 0;
				$shisanyao_arr[] = 0;
			}
			else
			{
				$hu_list_val = $tmp_hu_data[$key];
				//1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen
				if($this->m_rule->is_yitiaolong_fan && ($hu_list_val & 256) == 256)//一条龙
				{
					$is_yitiaolong = true;
				}
				$pengpeng_arr[] = $hu_list_val & 8;
				$qidui_arr[] = $hu_list_val & 64;
				$shisanyao_arr[] = $hu_list_val & 128;
				$gen_arr[] = intval($hu_list_val/4096);

				if(($hu_list_val & 1) == 1)
				{
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

			if(ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i])
			{
				$pengpeng_arr[] = 0;
			}

			if(ConstConfig::DAO_PAI_TYPE_KE == $this->m_sStandCard[$chair]->type[$i] && $this->m_sPlayer[$chair]->card[$stand_pai_type][$stand_pai_key] > 0)
			{
				//手牌，倒牌组合根
				$gen_arr[] = 1;
			}
		}

		$bType32 = (32 == array_sum($jiang_arr));
		$bQiDui = !array_keys($qidui_arr, 0);
		$bPengPeng = !array_keys($pengpeng_arr, 0);
		$bShiSanYao = !array_keys($shisanyao_arr, 0);

		/////////////////////////////附加 番型的处理/////////////////////////////////
		//一色结果
		$this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);

		//一条龙前面处理过
		//
		//打八张判断
		if(!empty($this->m_rule->is_da8zhang))
		{
			$is_da8zhang = $this->_judge_da8zhang($chair,null,false,true);
		}

		//13幺
		if($this->m_rule->is_shisanyao_fan && $this->m_rule->is_feng && $bShiSanYao)
		{
			$is_da8zhang = true;  //13幺  免疫打八张
			$is_quemen = true;	//13幺，不计缺门
		}

		if(!empty($this->m_rule->is_da8zhang) && !$is_da8zhang)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}

		if(!empty($this->m_rule->is_quemen) && !$is_quemen)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}

		//基本牌型的处理///////////////////////////////
		if(!$bType32 && !$bQiDui && !$bShiSanYao)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}

		if($this->m_rule->is_shisanyao_fan && $this->m_rule->is_feng && $bShiSanYao)
		{
			return self::HU_TYPE_SHISANYAO;
		}

		if($bQiDui && $this->m_rule->is_qidui_fan )				//判断七对，可能同时是32牌型
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
			if(!empty($this->m_rule->is_pengpenghu_fan) && $bPengPeng)
			{
				return self::HU_TYPE_PENGPENGHU;
			}
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

		//扣牌
		if(!empty($this->m_rule->is_kou_card))
		{
			$tmp_times = 0;
			for ($i=0; $i < 12; $i++)
			{ 
				if($this->m_sPlayer[$chair]->kou_card[$i][0] == $temp_card && $this->m_sPlayer[$chair]->kou_card[$i][1] == 1)
				{
					$this->m_sPlayer[$chair]->kou_card[$i][1] = 3;
					$tmp_times++;
				}
				if($tmp_times >= 4)
				{
					break;
				}
			}
			$this->m_sPlayer[$chair]->kou_card_display = $this->_set_kou_arr($chair, true);
		}

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
		//$this->m_wGFXYScore = [0,0,0,0];
		for ($i=0; $i<$this->m_rule->player_count; ++$i)
		{
			if ($i == $chair)
			{
				continue;
			}

			if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
			{
				$nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;

				//$this->m_wGFXYScore[$i] = -$nGangScore;			//扣本次刮风下雨分
				$this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分

				//$this->m_wGFXYScore[$chair] += $nGangScore;				//赢本次刮风下雨分
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

		//扣牌
		if(!empty($this->m_rule->is_kou_card))
		{
			$tmp_times = 0;
			for ($i=0; $i < 12; $i++)
			{ 
				if($this->m_sPlayer[$chair]->kou_card[$i][0] == $temp_card && $this->m_sPlayer[$chair]->kou_card[$i][1] == 1)
				{
					$this->m_sPlayer[$chair]->kou_card[$i][1] = 3;
					$tmp_times++;
				}
				if($tmp_times >= 3)
				{
					break;
				}
			}
			$this->m_sPlayer[$chair]->kou_card_display = $this->_set_kou_arr($chair, true);
		}

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
		//$this->m_wGFXYScore = [0,0,0,0];
		for ($i=0; $i<$this->m_rule->player_count; $i++)
		{
			if ($i == $chair)
			{
				continue;
			}

			if ($stand_count_after > 0 && $i == $this->m_sStandCard[$chair]->who_give_me[$stand_count_after-1])
			{
				$nGangScore =self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;

				//$this->m_wGFXYScore[$i] = -$nGangScore;
				$this->m_wGangScore[$i][$i] -= $nGangScore;

				//$this->m_wGFXYScore[$chair] += $nGangScore;
				$this->m_wGangScore[$chair][$chair] += $nGangScore;

				$this->m_wGangScore[$chair][$i] += $nGangScore;

				$nGangPao += $nGangScore;
			}
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
			//CCLOG("end reason no card");
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
				//$m_wGFXYScore = [0,0,0,0];
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
						$nGangScore = self::M_WANGANG_SCORE *ConstConfig::SCORE_BASE;

						//$this->m_wGFXYScore[$i] = -$nGangScore;
						$this->m_wGangScore[$i][$i] -= $nGangScore;

						//$this->m_wGFXYScore[$this->m_sQiangGang->chair] += $nGangScore;
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
		$fan_sum = $this->judge_fan($chair);  //这个就是  一共多少分
		// if($fan_sum < $this->m_rule->min_fan)
		// {
		// 	$this->m_HuCurt[$chair]->clear();
		// 	return false;
		// }
		$PerWinScore = $fan_sum;	

		$wWinScore = 0;

		$this->m_wHuScore = [0,0,0,0];

		if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		{
			for($i = 0; $i < $this->m_rule->player_count; $i++)
			{
				if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
				{
					continue;	//单用户测试需要关掉
				}

				$banker_fan = 1;
				// if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
				// {
				// 	$banker_fan = 2;
				// }

				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
				$wWinScore = 0;
				$wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan ;  //自摸翻倍了

				//自摸下跑分翻倍
				$wWinPaoZi = 2*($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$i]->num);
				$this->m_paozi_score[$chair] += $wWinPaoZi;
				$this->m_paozi_score[$i] -= $wWinPaoZi;
				
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
					// if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
					// {
					// 	$banker_fan = 2;
					// }

					$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
					$wWinScore = 0;
					$wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;

					if($lost_chair == $i)
					{
						//$wWinScore *= 2;	//点炮加倍
						$wWinPaoZi = ($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$i]->num);
						$this->m_paozi_score[$chair] += $wWinPaoZi;
						$this->m_paozi_score[$lost_chair] -= $wWinPaoZi;
					}

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
					// if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
					// {
					// 	$banker_fan = 2;
					// }

					$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
					$wWinScore = 0;
					$wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;

					if($lost_chair == $i)
					{
						//$wWinScore *= 2;	//点炮加倍
						$wWinPaoZi = ($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$i]->num);
						$this->m_paozi_score[$chair] += $wWinPaoZi;
						$this->m_paozi_score[$lost_chair] -= $wWinPaoZi;
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
			else if(empty($this->m_rule->is_dianpao_bao))
			{
				$banker_fan = 1;
				// if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair))
				// {
				// 	$banker_fan = 2;
				// }

				//一家出
				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;	
				$wWinScore = 0;
				$wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;	//点炮加倍

				$wWinPaoZi = ($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$lost_chair]->num);
				$this->m_paozi_score[$chair] += $wWinPaoZi;
				$this->m_paozi_score[$lost_chair] -= $wWinPaoZi;

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

	////////////////////////////其他///////////////////////////
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

}
