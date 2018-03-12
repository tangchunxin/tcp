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

class GameCangZhou extends BaseGame
{
	const GAME_TYPE = 261;

	//----- bian zuan za state -------
	const BZZ_NULL = 0;	//无
	const BZZ_BIAN = 1;	//边
	const BZZ_ZUAN = 2;	//钻
	const BZZ_ZA = 3;	//砸

	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21;                  // 平胡
	const HU_TYPE_QIDUI = 22;                   // 七对
	const HU_TYPE_HAOHUA_QIDUI = 23;            // 豪华七对
	const HU_TYPE_PENGPENGHU = 24;           	// 碰碰胡

	const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   // 错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_ZIMOFAN = 61;             // 自摸
	const ATTACHED_HU_GANGKAI = 62;             // 杠开
	const ATTACHED_HU_QIANGGANG = 63;           // 抢杠

	const ATTACHED_HU_QINGYISE = 64;            // 清一色
	const ATTACHED_HU_YITIAOLONG = 65;          // 一条龙
	const ATTACHED_HU_ZIYISE = 66;            	// 字一色

	const ATTACHED_HU_BIAN = 67;				//沧州边
	const ATTACHED_HU_ZUAN = 68;				//沧州钻
	const ATTACHED_HU_ZA = 69;					//沧州砸
	const ATTACHED_HU_WUKUI = 70;				//捉五魁
	const ATTACHED_HU_DIAOWUWAN = 71;			//吊五万

	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 1;                 // 直杠分
	const M_ANGANG_SCORE = 2;                  // 暗杠分
	const M_WANGANG_SCORE = 1;                 // 弯杠分

	public static $hu_type_arr = array(
		self::HU_TYPE_PINGHU=>array(self::HU_TYPE_PINGHU, 1, '平胡')
		,self::HU_TYPE_QIDUI=>array(self::HU_TYPE_QIDUI, 3, '七对')
		,self::HU_TYPE_HAOHUA_QIDUI=>array(self::HU_TYPE_HAOHUA_QIDUI, 5, '豪华七对')
		,self::HU_TYPE_PENGPENGHU=>array(self::HU_TYPE_PENGPENGHU, 2, '碰碰胡')

	);

	public static $attached_hu_arr = array(
		self::ATTACHED_HU_ZIMOFAN=>array(self::ATTACHED_HU_ZIMOFAN, 0, '自摸')
		,self::ATTACHED_HU_GANGKAI=>array(self::ATTACHED_HU_GANGKAI, 0, '杠上花')
		,self::ATTACHED_HU_QIANGGANG=>array(self::ATTACHED_HU_QIANGGANG, 0, '抢杠')

		,self::ATTACHED_HU_QINGYISE=>array(self::ATTACHED_HU_QINGYISE, 2, '清一色')
		,self::ATTACHED_HU_ZIYISE=>array(self::ATTACHED_HU_ZIYISE, 2, '字一色')
		,self::ATTACHED_HU_YITIAOLONG=>array(self::ATTACHED_HU_YITIAOLONG, 2, '一条龙')

		,self::ATTACHED_HU_BIAN=>array(self::ATTACHED_HU_BIAN, 2, '边')
		,self::ATTACHED_HU_ZUAN=>array(self::ATTACHED_HU_ZUAN, 1, '钻')
		,self::ATTACHED_HU_ZA=>array(self::ATTACHED_HU_ZA, 1, '砸')
		
		,self::ATTACHED_HU_WUKUI=>array(self::ATTACHED_HU_WUKUI, 1, '捉五魁')
		,self::ATTACHED_HU_DIAOWUWAN=>array(self::ATTACHED_HU_DIAOWUWAN, 1, '吊五万')
	);	


	public $m_choice_za;			// 竞争选择砸 存储 
	public $m_bzz_state = array();	// 边钻砸状态
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
		$this->m_choice_za = 0;
		for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
		{
			$this->m_bzz_state[$i] = self::BZZ_NULL;
		}
		$this->player_score = array(0,0,0,0);
		$this->player_cup = array(0,0,0,0);
	}

	public function _open_room_sub($params)
	{
        $this->m_rule = new RuleCangZhou();

        if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3, 4)))
        {
            $params['rule']['player_count'] = 4;
        }

		$params['rule']['min_fan'] = !isset($params['rule']['min_fan']) ? 0 : $params['rule']['min_fan'];
		$params['rule']['top_fan'] = !isset($params['rule']['top_fan']) ? 0 : $params['rule']['top_fan'];
		$params['rule']['is_feng'] = !isset($params['rule']['is_feng']) ? 1 : $params['rule']['is_feng'];
		$params['rule']['is_yipao_duoxiang'] = !isset($params['rule']['is_yipao_duoxiang']) ? 0 : $params['rule']['is_yipao_duoxiang'];
		$params['rule']['is_chipai'] = !isset($params['rule']['is_chipai']) ? 0 : $params['rule']['is_chipai'];
		$params['rule']['is_genzhuang'] = !isset($params['rule']['is_genzhuang']) ? 0 : $params['rule']['is_genzhuang'];
		$params['rule']['is_paozi'] = !isset($params['rule']['is_paozi']) ? 0 : $params['rule']['is_paozi'];
		$params['rule']['is_zhuang_fan'] = !isset($params['rule']['is_zhuang_fan']) ? 1 : $params['rule']['is_zhuang_fan'];
		$params['rule']['is_qingyise_fan'] = !isset($params['rule']['is_qingyise_fan']) ? 1 : $params['rule']['is_qingyise_fan'];
		$params['rule']['is_ziyise_fan'] = !isset($params['rule']['is_ziyise_fan']) ? 1 : $params['rule']['is_ziyise_fan'];
		$params['rule']['is_yitiaolong_fan'] = !isset($params['rule']['is_yitiaolong_fan']) ? 1 : $params['rule']['is_yitiaolong_fan'];
		$params['rule']['is_ganghua_fan'] = !isset($params['rule']['is_ganghua_fan']) ? 1 : $params['rule']['is_ganghua_fan'];
		$params['rule']['is_qidui_fan'] = !isset($params['rule']['is_qidui_fan']) ? 1 : $params['rule']['is_qidui_fan'];
		$params['rule']['is_pengpenghu_fan'] = !isset($params['rule']['is_pengpenghu_fan']) ? 1 : $params['rule']['is_pengpenghu_fan'];
		$params['rule']['is_wangang_1_lose'] = !isset($params['rule']['is_wangang_1_lose']) ? 0 : $params['rule']['is_wangang_1_lose'];
		$params['rule']['is_dianpao_bao'] = !isset($params['rule']['is_dianpao_bao']) ? 0 : $params['rule']['is_dianpao_bao'];
		$params['rule']['is_wukui'] = !isset($params['rule']['is_wukui']) ? 1 : $params['rule']['is_wukui'];
		$params['rule']['is_diaowuwan'] = !isset($params['rule']['is_diaowuwan']) ? 1 : $params['rule']['is_diaowuwan'];
		$params['rule']['is_zhongfabai_shun'] = !isset($params['rule']['is_zhongfabai_shun']) ? 0 : $params['rule']['is_zhongfabai_shun'];
		$params['rule']['is_bian_zuan'] = !isset($params['rule']['is_bian_zuan']) ? 1 : $params['rule']['is_bian_zuan'];
		$params['rule']['is_za'] = !isset($params['rule']['is_za']) ? 0 : $params['rule']['is_za'];
		$params['rule']['pay_type'] = !isset($params['rule']['pay_type']) ? 0 : $params['rule']['pay_type'];
		$params['rule']['cancle_clocker'] = !isset($params['rule']['cancle_clocker']) ? 1 : $params['rule']['cancle_clocker'];
		$params['rule']['allow_louhu'] = !isset($params['rule']['allow_louhu']) ? 1 : $params['rule']['allow_louhu'];
		$params['rule']['qg_is_zimo'] = !isset($params['rule']['qg_is_zimo']) ? 1 : $params['rule']['qg_is_zimo'];
		if (!empty($params['rule']['is_score_field'])) 
		{
			$params['rule']['is_circle'] = !isset($params['rule']['is_circle']) ? 0 : $params['rule']['is_circle'];
		}
		else
		{
			$params['rule']['is_circle'] = !isset($params['rule']['is_circle']) ? 4 : $params['rule']['is_circle'];
		}
		
		$this->m_rule->game_type = $params['rule']['game_type'];
		$this->m_rule->player_count = $params['rule']['player_count'];
		$this->m_rule->min_fan = $params['rule']['min_fan'];
		$this->m_rule->top_fan = $params['rule']['top_fan'];
		$this->m_rule->is_circle = $params['rule']['is_circle'];
        if(!empty($this->m_rule->is_circle))
        {
            $this->m_rule->set_num = $this->m_rule->is_circle * $this->m_rule->player_count;		//局等于  人*圈
        }else
        {
            $this->m_rule->set_num = $params['rule']['set_num'];
        }

		$this->m_rule->is_feng = $params['rule']['is_feng'];
		$this->m_rule->is_yipao_duoxiang = $params['rule']['is_yipao_duoxiang'];
		$this->m_rule->is_chipai = $params['rule']['is_chipai'];
		$this->m_rule->is_genzhuang = $params['rule']['is_genzhuang'];
		$this->m_rule->is_paozi = $params['rule']['is_paozi'];
		$this->m_rule->is_zhuang_fan = $params['rule']['is_zhuang_fan'];

		$this->m_rule->is_qingyise_fan = $params['rule']['is_qingyise_fan'];
		$this->m_rule->is_ziyise_fan = $params['rule']['is_ziyise_fan'];
		$this->m_rule->is_yitiaolong_fan = $params['rule']['is_yitiaolong_fan'];
		$this->m_rule->is_ganghua_fan = $params['rule']['is_ganghua_fan'];
		$this->m_rule->is_qidui_fan = $params['rule']['is_qidui_fan'];
		$this->m_rule->is_pengpenghu_fan = $params['rule']['is_pengpenghu_fan'];

		$this->m_rule->is_wangang_1_lose = $params['rule']['is_wangang_1_lose'];
		$this->m_rule->is_dianpao_bao = $params['rule']['is_dianpao_bao'];
		$this->m_rule->is_wukui = $params['rule']['is_wukui'];
		$this->m_rule->is_diaowuwan = $params['rule']['is_diaowuwan'];
		
		$this->m_rule->is_zhongfabai_shun = $params['rule']['is_zhongfabai_shun'];
		$this->m_rule->is_bian_zuan = $params['rule']['is_bian_zuan'];
		$this->m_rule->is_za = $params['rule']['is_za'];
		$this->m_rule->pay_type = $params['rule']['pay_type'];
		
		$this->m_rule->cancle_clocker = $params['rule']['cancle_clocker'];
		$this->m_rule->allow_louhu = $params['rule']['allow_louhu'];
		$this->m_rule->qg_is_zimo = $params['rule']['qg_is_zimo'];

		// 积分新增
		$params['rule']['score'] = !isset($params['rule']['score']) ? 0 : $params['rule']['score'];
		$this->m_rule->score = $params['rule']['score'];
		$params['rule']['is_score_field'] = !isset($params['rule']['is_score_field']) ? 0 : $params['rule']['is_score_field'];
		$this->m_rule->is_score_field = $params['rule']['is_score_field'];

	}


	///////////////////出牌阶段//////////////////////

    //边钻
	public function c_bian_zuan($fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid'])
			|| empty($params['uid'])
			|| empty($params['num'])
			)
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}
			$params['num'] = (!empty($params['num']) && !empty($this->m_rule->is_bian_zuan)) ? $params['num'] : 0;

			if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD || empty($this->m_rule->is_bian_zuan))
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

					if(!($this->m_sPlayer[$key]->card_taken_now))
					{
						$return_send['code'] = 5; $return_send['text'] = '牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$no_bzz_num = 0; $bzz_num = 0;
					$this->_get_num_stand($key, $no_bzz_num, $bzz_num);
					if($no_bzz_num >= 2)
					{
						$return_send['code'] = 5; $return_send['text'] = '牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}
					
					$tmp_card_type = $this->_get_card_type($this->m_sPlayer[$key]->card_taken_now);
					$tmp_card_index = $this->m_sPlayer[$key]->card_taken_now % 16;
					if(($this->m_bzz_state[$key] == self::BZZ_NULL || $this->m_bzz_state[$key] == self::BZZ_ZUAN) && $params['num'] == self::BZZ_ZUAN)
					{
						//钻判定
						$tmp_is_zuan = true;
						if(($tmp_card_type < ConstConfig::PAI_TYPE_FENG && ($tmp_card_index == 1 || $tmp_card_index == 9))
							|| $tmp_card_type == ConstConfig::PAI_TYPE_FENG
						)
						{
							$tmp_is_zuan = false;
						}
						else if(empty($this->m_sPlayer[$key]->card[$tmp_card_type][$tmp_card_index-1]) || empty($this->m_sPlayer[$key]->card[$tmp_card_type][$tmp_card_index+1]))
						{
							$tmp_is_zuan = false;
						}

						if(!$tmp_is_zuan)
						{
							$return_send['code'] = 5; $return_send['text'] = '牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
					}
					else if(($this->m_bzz_state[$key] == self::BZZ_NULL || $this->m_bzz_state[$key] == self::BZZ_BIAN) && $params['num'] == self::BZZ_BIAN)
					{
						//边判定
						$tmp_is_bian = true;
						if($tmp_card_type < ConstConfig::PAI_TYPE_FENG && $tmp_card_index == 3)
						{
							if(empty($this->m_sPlayer[$key]->card[$tmp_card_type][1]) || empty($this->m_sPlayer[$key]->card[$tmp_card_type][2]))
							{
								$tmp_is_bian = false;
							}
						}
						else if($tmp_card_type < ConstConfig::PAI_TYPE_FENG && $tmp_card_index == 7)
						{
							if(empty($this->m_sPlayer[$key]->card[$tmp_card_type][8]) || empty($this->m_sPlayer[$key]->card[$tmp_card_type][9]))
							{
								$tmp_is_bian = false;
							}
						}
						// else if($tmp_card_type == ConstConfig::PAI_TYPE_FENG && $tmp_card_index == 7 && !empty($this->m_rule->is_zhongfabai_shun))
						// {
						// 	if(empty($this->m_sPlayer[$key]->card[$tmp_card_type][5]) || empty($this->m_sPlayer[$key]->card[$tmp_card_type][6]))
						// 	{
						// 		$tmp_is_bian = false;
						// 	}
						// }
						else
						{
							$tmp_is_bian = false;
						}

						if(!$tmp_is_bian)
						{
							$return_send['code'] = 5; $return_send['text'] = '牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
					}
					else
					{
						$return_send['code'] = 5; $return_send['text'] = '牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
					}

					$this->_clear_choose_buf($key);
					$this->handle_bian_zuan($key, $params['num']);
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
	/////////////选择阶段/////////////////

	//--------------------------------------------------------------------------

	//判断胡  
	public function judge_hu($chair, $is_fanhun = false)
	{
		//胡牌型
		$is_qingyise = false;
		$is_ziyise = false;
		$is_yitiaolong = false;
		$bzz_num = 0;
		$is_wukui = false;
		$is_diaowuwan = false;
		$hu_type = $this->judge_hu_type($chair, $is_qingyise, $is_yitiaolong, $is_ziyise, $bzz_num, $is_wukui, $is_diaowuwan);

		if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
		{
			return false;
		}

		//记录在全局数据
		$this->m_HuCurt[$chair]->method[0] = $hu_type;
		$this->m_HuCurt[$chair]->count = 1;

		//自摸加番
		// if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		// {
		// 	$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZIMOFAN);
		// }

		//抢杠杠开杠炮
		if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)	// 处理抢杠
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
		}
		else if(!empty($this->m_rule->is_ganghua_fan) && $this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair)	//杠开
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGKAI);
		}
		// else if ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO && $this->m_sGangPao->mark && $this->m_sGangPao->chair != $chair)
		// {
		// 	//$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGPAO);
		// }

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
		if(!empty($this->m_rule->is_wukui) && $is_wukui)
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_WUKUI);
		}

		//吊五万
		if(!empty($this->m_rule->is_diaowuwan) && $is_diaowuwan && !$is_wukui)
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_DIAOWUWAN);
		}

		//边钻
		if(!empty($this->m_rule->is_bian_zuan) && $bzz_num >= 3)
		{
			for($i=0; $i<$bzz_num; $i++)
			{
				if($this->m_bzz_state[$chair] == self::BZZ_BIAN)
				{
					$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_BIAN);
				}
				else if($this->m_bzz_state[$chair] == self::BZZ_ZUAN)
				{
					$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZUAN);
				}
			}
		}

		//砸
		if(!empty($this->m_rule->is_za) && $this->m_bzz_state[$chair] == self::BZZ_ZA && $bzz_num >= 3)
		{
			for($i=0; $i<$bzz_num; $i++)
			{
				$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZA);
			}
		}

		$fan_sum = $this->judge_fan($chair);  //这个就是  一共多少分
		if($fan_sum < $this->m_rule->min_fan)
		{
			$this->m_HuCurt[$chair]->clear();
			return false;
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

		$zuan_num = 0;
		$bian_num = 0;
		for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
		{
			if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
			{
				$fan_sum += self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
				if($this->m_HuCurt[$chair]->method[$i] == self::ATTACHED_HU_ZUAN)
				{
					$zuan_num += 1; 
				}
				elseif($this->m_HuCurt[$chair]->method[$i] == self::ATTACHED_HU_BIAN)
				{
					$bian_num += 1;
				}
				else
				{
					$tmp_hu_desc .= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
				}
			}
		}

		if($zuan_num == 3)
		{
			$tmp_hu_desc .='三钻 ';
		}
		if($zuan_num == 4)
		{
			$tmp_hu_desc .='四钻 ';
		}
		if($bian_num == 3)
		{
			$tmp_hu_desc .='三边 ';
		}
		if($bian_num == 4)
		{
			$tmp_hu_desc .='四边 ';
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
	public function judge_hu_type($chair, &$is_qingyise, &$is_yitiaolong, &$is_ziyise, &$bzz_num, &$is_wukui, &$is_diaowuwan)
	{
		$jiang_arr = array();
		$qidui_arr = array();
		$qing_arr = array();
		//$shisanyao_arr = array();
		$pengpeng_arr = array();
		$gen_arr = array();
		
		$bType32 = false;
		$bQiDui = false;
		$bPengPeng = false;  
		//$bShiSanYao = false;    //13幺

		$is_qingyise = false;
		$is_ziyise = false;
		$is_yitiaolong = false;   //一条龙

		$bzz_num = 0;
		$is_wukui = false;
		$is_diaowuwan = false;
		//倒牌
		for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
		{
			$stand_pai_type = $this->_get_card_type($this->m_sStandCard[$chair]->first_card[$i]);
			$stand_pai_key = $this->m_sStandCard[$chair]->first_card[$i] % 16;
			
			$qidui_arr[] = 0;
			//$shisanyao_arr[] = 0;
			$qing_arr[] = $stand_pai_type;

			if(ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i]
				|| ConstConfig::DAO_PAI_TYPE_BIAN == $this->m_sStandCard[$chair]->type[$i]
				|| ConstConfig::DAO_PAI_TYPE_ZUAN == $this->m_sStandCard[$chair]->type[$i]
			)
			{
				$pengpeng_arr[] = 0;
			}

			if( (ConstConfig::DAO_PAI_TYPE_KE == $this->m_sStandCard[$chair]->type[$i] && $this->m_sPlayer[$chair]->card[$stand_pai_type][$stand_pai_key] > 0)
				|| (ConstConfig::DAO_PAI_TYPE_ZA == $this->m_sStandCard[$chair]->type[$i] && $this->m_sPlayer[$chair]->card[$stand_pai_type][$stand_pai_key] > 0)
				|| (ConstConfig::DAO_PAI_TYPE_MINGGANG_ZA == $this->m_sStandCard[$chair]->type[$i])
				|| (ConstConfig::DAO_PAI_TYPE_ANGANG_ZA == $this->m_sStandCard[$chair]->type[$i])
			)
			{
				//手牌，倒牌组合根
				$gen_arr[] = 1;
			}

			//由于打牌规则导致 倒牌中边钻砸只能出现一种类型
			if(ConstConfig::DAO_PAI_TYPE_BIAN == $this->m_sStandCard[$chair]->type[$i]
				|| ConstConfig::DAO_PAI_TYPE_ZUAN == $this->m_sStandCard[$chair]->type[$i]
				|| ConstConfig::DAO_PAI_TYPE_ZA == $this->m_sStandCard[$chair]->type[$i]
				|| ConstConfig::DAO_PAI_TYPE_MINGGANG_ZA == $this->m_sStandCard[$chair]->type[$i]
				|| ConstConfig::DAO_PAI_TYPE_ANGANG_ZA == $this->m_sStandCard[$chair]->type[$i]
			)
			{
				$bzz_num++;
			}
		}

		//手牌
		for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG; $i++)
		{
			if(0 == $this->m_sPlayer[$chair]->card[$i][0])
			{
				//$shisanyao_arr[] = 0;
				continue;
			}

			$tmp_hu_data = &ConstConfig::$hu_data;
			if(ConstConfig::PAI_TYPE_FENG == $i)
			{
				$tmp_hu_data = &ConstConfig::$hu_data_feng;
				if(!empty($this->m_rule->is_zhongfabai_shun))
				{
					$tmp_hu_data = &ConstConfig::$hu_data_feng_shun;
				}
			}
			$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));
			if(!isset($tmp_hu_data[$key]))
			{
				//return self::HU_TYPE_FENGDING_TYPE_INVALID ;
				$jiang_arr[] = 32; $jiang_arr[] = 32;
				$qidui_arr[] = 0;
				$qing_arr[] = $i;
				$pengpeng_arr[] = 0;
				//$shisanyao_arr[] = 0;
			}
			else
			{
				$hu_list_val = $tmp_hu_data[$key];
				//1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen
				
				$pengpeng_arr[] = $hu_list_val & 8;
				$qidui_arr[] = $hu_list_val & 64;
				//$shisanyao_arr[] = $hu_list_val & 128;
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

				//判断边钻砸
				if($this->m_HuCurt[$chair]->card)
				{
					$hu_card_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
					$hu_card_index = $this->m_HuCurt[$chair]->card % 16;
					//胡牌那门
					if($hu_card_type == $i)
					{
						$tmp_card_arr = $this->m_sPlayer[$chair]->card[$hu_card_type];
						if($this->m_bzz_state[$chair] == self::BZZ_ZA)
						{
							if($tmp_card_arr[$hu_card_index] >=3)
							{
								$tmp_card_arr[$hu_card_index] -= 3;
								$tmp_key = intval(implode('', array_slice($tmp_card_arr, 1)));
								if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
								{
									$bzz_num++;
								}
							}
						}
						else if($this->m_bzz_state[$chair] == self::BZZ_ZUAN)
						{
							if(
								(($hu_card_index > 1 && $hu_card_index < 9 && $hu_card_type < ConstConfig::PAI_TYPE_FENG)
									 || ($hu_card_type == ConstConfig::PAI_TYPE_FENG && !empty($this->m_rule->is_zhongfabai_shun) && $hu_card_index == 6))
								 && $tmp_card_arr[$hu_card_index] > 0 && $tmp_card_arr[$hu_card_index + 1] > 0 && $tmp_card_arr[$hu_card_index - 1] > 0
							)
							{
								$tmp_card_arr[$hu_card_index] -= 1;
								$tmp_card_arr[$hu_card_index + 1] -= 1;
								$tmp_card_arr[$hu_card_index - 1] -= 1;
								$tmp_key = intval(implode('', array_slice($tmp_card_arr, 1)));
								if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
								{
									$bzz_num++;
								}
							}
						}
						else if($this->m_bzz_state[$chair] == self::BZZ_BIAN)
						{
							$tmp_index_1 = 0;
							$tmp_index_2 = 0;
							$tmp_index_3 = 0;
							if(
								($hu_card_index == 3 && $hu_card_type < ConstConfig::PAI_TYPE_FENG)
									 || ($hu_card_type == ConstConfig::PAI_TYPE_FENG && !empty($this->m_rule->is_zhongfabai_shun) && $hu_card_index == 7)
							)
							{
								$tmp_index_1 = $hu_card_index - 2;
								$tmp_index_2 = $hu_card_index - 1;
								$tmp_index_3 = $hu_card_index;
							}
							else if($hu_card_index == 7 && $hu_card_type < ConstConfig::PAI_TYPE_FENG)
							{
								$tmp_index_1 = $hu_card_index + 2;
								$tmp_index_2 = $hu_card_index + 1;
								$tmp_index_3 = $hu_card_index;
							}
							if($tmp_index_1 > 0 && $tmp_index_2 > 0 && $tmp_index_3 > 0
								&& $tmp_card_arr[$tmp_index_1] > 0 && $tmp_card_arr[$tmp_index_2] > 0 && $tmp_card_arr[$tmp_index_3] > 0 
								)
							{
								$tmp_card_arr[$tmp_index_1] -= 1;
								$tmp_card_arr[$tmp_index_2] -= 1;
								$tmp_card_arr[$tmp_index_3] -= 1;
								$tmp_key = intval(implode('', array_slice($tmp_card_arr, 1)));
								if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
								{
									$bzz_num++;
								}
							}
						}
					}
				}
			}
		}

		$bType32 = (32 == array_sum($jiang_arr));
		$bQiDui = !array_keys($qidui_arr, 0);
		$bPengPeng = !array_keys($pengpeng_arr, 0);
		//$bShiSanYao = !array_keys($shisanyao_arr, 0);

		/////////////////////////////附加 番型的处理/////////////////////////////////
		//一色结果
		$this->_is_yise($qing_arr, $is_qingyise, $is_ziyise);

		//一条龙
		if($this->m_rule->is_yitiaolong_fan)
		{
			$this->_is_yitiaolong($chair, $is_yitiaolong);
		}

		//捉五魁
		if(!empty($this->m_rule->is_wukui))
		{
			$this->_is_wukui($chair, $is_wukui, $bQiDui, $bzz_num);
		}

		//吊五万
		if(!empty($this->m_rule->is_diaowuwan))
		{
			$this->_is_diaowuwan($chair, $is_diaowuwan, $bQiDui);
		}

		if($this->m_bzz_state[$chair] != self::BZZ_NULL && $bzz_num < 3 && !$is_yitiaolong)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}
		//13幺
		// if($this->m_rule->is_shisanyao_fan && $this->m_rule->is_feng && $bShiSanYao)
		// {
		// 	$is_quemen = true;	//13幺，不计缺门
		// }

		// if(!empty($this->m_rule->is_quemen) && !$is_quemen)
		// {
		// 	return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		// }

		//基本牌型的处理///////////////////////////////
		if(!$bType32 && !$bQiDui && !$is_yitiaolong)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}

		// if($this->m_rule->is_shisanyao_fan && $this->m_rule->is_feng && $bShiSanYao)
		// {
		// 	return self::HU_TYPE_SHISANYAO;
		// }

		if($bQiDui && $this->m_rule->is_qidui_fan )				//判断七对，可能同时是32牌型
		{
			
			if(array_sum($gen_arr) >= 1)				
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
	//处理碰 
	public function HandleChoosePeng($chair, $za = 0)
	{
		$temp_card = $this->m_sOutedCard->card;
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
		if($za)
		{
			$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_ZA;
			$this->m_bzz_state[$chair] = self::BZZ_ZA;
		}
		$this->m_sStandCard[$chair]->first_card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
		$this->m_sStandCard[$chair]->num ++;

		// 找出第14张牌
		$card_14 = $this->_find_14_card($chair);
		if(!$card_14)
		{
			echo "error HandleChoosePeng".__LINE__.__CLASS__;
			return false;
		}

		//置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
		$this->_deleteLastTableCard();
	
		$this->m_sPlayer[$chair]->card_taken_now = $card_14;

		if($za)
		{
			$this->_set_record_game(ConstConfig::RECORD_PENG_ZA, $chair, $temp_card, $this->m_sOutedCard->chair);
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
		
		$this->m_choice_za = 0;
		$this->m_sGangPao->clear();
		$this->m_only_out_card[$chair] = true;

		if($za)
		{
			$this->m_currentCmd = 'c_peng_za';
		}
		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}

		return true;
	}

	//处理暗杠 
	public function HandleChooseAnGang($chair, $gang_card, $za = 0)
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
		if($za)
		{
			$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_ANGANG_ZA;
		}
		$this->m_sStandCard[$chair]->first_card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $chair;
		$this->m_sStandCard[$chair]->num ++;

		$this->m_bHaveGang = true;  //for 杠上花
		if($za)
		{
			$this->m_bHaveGang = false;
			$this->m_bzz_state[$chair] = self::BZZ_ZA;
		}

		$GangScore = 0;
		$nGangPao = 0;
		if(!$za)
		{
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
		}

		if($za)
		{
			$this->_set_record_game(ConstConfig::RECORD_ANGANG_ZA, $chair, $temp_card, $chair);
		}
		else
		{
			$this->_set_record_game(ConstConfig::RECORD_ANGANG, $chair, $temp_card, $chair);
		}
		
		if(!$za)
		{
			$this->m_sGangPao->init_data(true, $gang_card, $chair, ConstConfig::DAO_PAI_TYPE_ANGANG, $nGangPao);
			$this->m_wTotalScore[$chair]->n_angang += 1;
		}

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
		if($za)
		{
			$this->m_currentCmd = 'c_an_gang_za';
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

	//处理直杠
	public function HandleChooseZhiGang($chair, $za = 0)
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
		if($za)
		{
			$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_MINGGANG_ZA;
		}
		$this->m_sStandCard[$chair]->first_card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
		$this->m_sStandCard[$chair]->num ++;
		$stand_count_after = $this->m_sStandCard[$chair]->num;

		$this->m_bHaveGang = true;  //for 杠上花
		if($za)
		{
			$this->m_bHaveGang = false;
			$this->m_bzz_state[$chair] = self::BZZ_ZA;
		}

		$nGangScore = 0;
		$nGangPao = 0;
		if(!$za)
		{
			$nGangScore =self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;
			$dian_gang = $this->m_sOutedCard->chair;
			for ($i=0; $i<$this->m_rule->player_count; $i++)
			{
				if ($i == $chair)
				{
					continue;
				}
				if($this->m_rule->is_dianpao_bao == 1) //点炮直杠包杠
				{
					$this->m_wGangScore[$dian_gang][$dian_gang] -= $nGangScore;
					$this->m_wGangScore[$chair][$chair] += $nGangScore;
					$this->m_wGangScore[$chair][$dian_gang] += $nGangScore;
				}
				elseif($this->m_rule->is_dianpao_bao == 0)	//发胡直杠各家出
				{
					$this->m_wGangScore[$i][$i] -= $nGangScore;
					$this->m_wGangScore[$chair][$chair] += $nGangScore;
					$this->m_wGangScore[$chair][$i] += $nGangScore;
				}

				$nGangPao += $nGangScore;
			}
		}

		if($za)
		{
			$this->_set_record_game(ConstConfig::RECORD_ZHIGANG_ZA, $chair, $temp_card, $this->m_sOutedCard->chair);
		}
		else
		{
			$this->_set_record_game(ConstConfig::RECORD_ZHIGANG, $chair, $temp_card, $this->m_sOutedCard->chair);
		}

		if(!$za)
		{
			$this->m_sGangPao->init_data(true, $temp_card, $chair,ConstConfig::DAO_PAI_TYPE_MINGGANG, $nGangPao);
			$this->m_wTotalScore[$chair]->n_zhigang_wangang += 1;
		}
		// 补发张牌给玩家
		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
		$this->m_chairCurrentPlayer = $chair;
		if(!$this->DealCard($chair))
		{
			return;
		}

		//置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
		$this->_deleteLastTableCard();

		$this->m_sOutedCard->clear();

		if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
		{
			return;
		}
		$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
		//状态变化发消息
		if($za)
		{
			$this->m_currentCmd = 'c_zhigang_za';
		}
		$this->_send_act($this->m_currentCmd, $chair);
		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}
	}

	public function HandleChooseResult($chair, $nCmdID, $eat_num = null, $choice_za = 0)
	{
		$this->handle_flee_play(true);
		
		//处理竞争
		$order_cmd = array('c_cancle_choice'=>0, 'c_eat'=>1, 'c_peng'=>2, 'c_zhigang'=>3, 'c_hu'=>4);
		if(empty($this->m_currentCmd) || ($order_cmd[$nCmdID] > $order_cmd[$this->m_currentCmd] && $order_cmd[$nCmdID] >= $order_cmd['c_cancle_choice']))	//吃, 碰, 杠竞争
		{
			$this->m_chairSendCmd = $chair;
			$this->m_currentCmd	= $nCmdID;
			$this->m_eat_num = $eat_num; 
			$this->m_choice_za = $choice_za;
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
				$this->_deleteLastTableCard();

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
					$this->HandleChoosePeng($this->m_chairSendCmd, $this->m_choice_za);
					break;
				case 'c_zhigang':
					$this->HandleChooseZhiGang($this->m_chairSendCmd, $this->m_choice_za);
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

	public function handle_bian_zuan($chair, $num)
	{
		$temp_card = $this->m_sPlayer[$chair]->card_taken_now;
		$card_type = $this->_get_card_type($temp_card);
		$card_index = $temp_card % 16;

		if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID || ($num != self::BZZ_ZUAN && $num != self::BZZ_BIAN))
		{
			return false;
		}

		$this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now);
		$this->m_sPlayer[$chair]->card_taken_now = 0;

		$this->_list_delete($chair, $temp_card);
		$tmp_first_card = 0;
		if($num == self::BZZ_BIAN)
		{
			if($card_index == 3 && $card_type < ConstConfig::PAI_TYPE_FENG)
			{
				$tmp_first_card = $this->_get_card_index($card_type, $card_index-2);
				$this->_list_delete($chair, $this->_get_card_index($card_type, $card_index-1));
				$this->_list_delete($chair, $tmp_first_card);
			}
			else if($card_index == 7 && $card_type < ConstConfig::PAI_TYPE_FENG)
			{
				$tmp_first_card = $temp_card;
				$this->_list_delete($chair, $this->_get_card_index($card_type, $card_index+1));
				$this->_list_delete($chair, $this->_get_card_index($card_type, $card_index+2));
			}
			else
			{
				return false;
			}
		}
		else if($num == self::BZZ_ZUAN)
		{
			$tmp_first_card = $this->_get_card_index($card_type, $card_index-1);
			$this->_list_delete($chair, $tmp_first_card);
			$this->_list_delete($chair, $this->_get_card_index($card_type, $card_index+1));
		}
		else
		{
			return false;
		}

		// 设置倒牌
		$stand_count = $this->m_sStandCard[$chair]->num;
		if($num == self::BZZ_ZUAN)
		{
			$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_ZUAN;
			$this->m_currentCmd = 'c_zuan';
		}
		else if($num == self::BZZ_BIAN)
		{
			$this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_BIAN;
			$this->m_currentCmd = 'c_bian';
		}
		$this->m_sStandCard[$chair]->first_card[$stand_count] = $tmp_first_card;
		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $chair;
		$this->m_sStandCard[$chair]->num ++;

		$this->m_bzz_state[$chair] = $num;

		if($num == self::BZZ_BIAN)
		{
			$this->_set_record_game(ConstConfig::RECORD_BIAN, $chair, $temp_card, $chair);
		}
		else if($num == self::BZZ_ZUAN)
		{
			$this->_set_record_game(ConstConfig::RECORD_ZUAN, $chair, $temp_card, $chair);
		}

		// 找出第14张牌
		$card_14 = $this->_find_14_card($chair);
		if(!$card_14)
		{
			echo "error dddf".__LINE__.__CLASS__;
			return false;
		}

		$this->m_sPlayer[$chair]->card_taken_now = $card_14;


		$this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
		$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_THINK_OUTCARD;
		$this->m_chairCurrentPlayer = $chair;
		$this->m_only_out_card[$chair] = true;

		//暗杠需要记录入命令
		$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
		$this->m_sOutedCard->clear();

		$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
		//状态变化发消息
		$this->_send_act($this->m_currentCmd, $chair);

		$this->handle_flee_play(true);	//更新断线用户
		for ($i=0; $i < $this->m_rule->player_count ; $i++)
		{
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
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
		$data['m_bzz_state'] = $this->m_bzz_state;
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

	///////////////////////得分处理////////////////////////////////////////
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
				
				//抢杠胡要求开杠者包牌型分
				if($this->m_sQiangGang->mark && $this->m_rule->is_dianpao_bao == 1)
				{
					$lost_chair = $this->m_sQiangGang->chair;
				}
				else
				{
					$lost_chair = $i;
				}

				$banker_fan = 0;
				if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
				{
					$banker_fan = 1;
				}

				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
				$wWinScore = 2 * ConstConfig::SCORE_BASE * ($PerWinScore + $banker_fan);

				$wWinPaoZi = 2*($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$i]->num);
				$this->m_paozi_score[$chair] += $wWinPaoZi;
				$this->m_paozi_score[$i] -= $wWinPaoZi;
				
				$wWinScore = $this->_get_max_fan($wWinScore);
				
				$this->m_wHuScore[$lost_chair] -= $wWinScore;
				$this->m_wHuScore[$chair] += $wWinScore;

				$this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
				$this->m_wSetScore[$chair] += $wWinScore;

				$this->m_HuCurt[$chair]->gain_chair[0]++;
				$this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $lost_chair;
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

					$banker_fan = 0;
					if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
					{
						$banker_fan = 1;
					}

					$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
					$wWinScore = 0;
					$wWinScore += ConstConfig::SCORE_BASE * ($PerWinScore + $banker_fan);

					if($lost_chair == $i)
					{
						$wWinPaoZi = ($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$i]->num);
						$this->m_paozi_score[$chair] += $wWinPaoZi;
						$this->m_paozi_score[$lost_chair] -= $wWinPaoZi;
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

					$banker_fan = 0;
					if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i))
					{
						$banker_fan = 1;
					}

					$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
					$wWinScore = 0;
					$wWinScore += ConstConfig::SCORE_BASE * ($PerWinScore + $banker_fan);

					if($lost_chair == $i)
					{
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

			return true;
		}

		echo("此人没有胡".__LINE__.__CLASS__);
		return false;
	}

	////////////////////////////其他///////////////////////////
	public function _get_num_stand($chair, &$no_bzz_num, &$bzz_num)
	{
		$no_bzz_num = 0;
		$bzz_num = 0;
		for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i ++)
		{
			if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_SHUN
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
			  )
			{
				$no_bzz_num++;
			}
			if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG_ZA
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG_ZA
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
			 || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
			  )
			{
				$bzz_num++;
			}
		}
	}

    public function _is_yitiaolong($chair, &$is_yitiaolong)
    {
        $is_yitiaolong = false;
        //倒牌添加进手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN)
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
        }

        //判断是否一条龙
        for($i=ConstConfig::PAI_TYPE_WAN; $i<ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if(0 == $this->m_sPlayer[$chair]->card[$i][0])
            {
                continue;
            }

            $tmp_hu_data = &ConstConfig::$hu_data;
            $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

            if(isset($tmp_hu_data[$key]) && $this->m_rule->is_yitiaolong_fan && ($tmp_hu_data[$key] & 256) == 256)
            {
                $is_yitiaolong = true;
                break;
            }
        }

        //还原手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN)
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
        }
    }

    public function _is_wukui($chair, &$is_wukui, $bQiDui, $bzz_num)
	{
		$is_wukui = false;
		$bType32 = false;
		$type = ConstConfig::PAI_TYPE_WAN;
		$tmp_hu_data = &ConstConfig::$hu_data;
		
		if(($this->m_HuCurt[$chair]->card != 5) || ($this->m_sPlayer[$chair]->card[$type][4] == 0 || $this->m_sPlayer[$chair]->card[$type][5] == 0 || $this->m_sPlayer[$chair]->card[$type][6] == 0) || $bQiDui)
		{
			return;
		}
		
		$this->_list_delete($chair,4);
		$this->_list_delete($chair,5);
		$this->_list_delete($chair,6);

		//判断手牌是否满足32牌型
		$tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
		if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
		{
			$bType32 = true;
		}	
		$this->_list_insert($chair,4);
		$this->_list_insert($chair,5);
		$this->_list_insert($chair,6);

		if($bType32 && (($this->m_bzz_state[$chair] == self::BZZ_ZUAN && $bzz_num == 3) || $this->_is_danting_wuwan($chair)))
		{
			$is_wukui = true;
		}
	}

	public function _is_diaowuwan($chair, &$is_diaowuwan, $bQiDui)
	{
		$is_diaowuwan = false;
		$bType32 = false;
		$type = ConstConfig::PAI_TYPE_WAN;
		$tmp_hu_data = &ConstConfig::$hu_data;
		
		if($this->m_HuCurt[$chair]->card != 5 || $this->m_sPlayer[$chair]->card[$type][5] <= 1 || $bQiDui)
		{
			return;
		}
		
		$this->_list_delete($chair,5);
		$this->_list_delete($chair,5);

		//判断手牌是否满足32牌型
		$tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
		if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
		{
			$bType32 = true;
		}	
		$this->_list_insert($chair,5);
		$this->_list_insert($chair,5);

		if($bType32 && $this->_is_danting_wuwan($chair))
		{
			$is_diaowuwan = true;
		}
	}

	//听牌判断
	public function _is_danting_wuwan($chair)
	{
		$is_danting_wuwan = true;
		$replace_card = array(1,2,3,4,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,49,50,51,52,53,54,55);
		
		$this->_list_delete($chair,5);
		foreach ($replace_card as $value)
		{
			$jiang_arr = array();
			$bType32 = false;
			$this->_list_insert($chair,$value);

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
					if(!empty($this->m_rule->is_zhongfabai_shun))
					{
						$tmp_hu_data = &ConstConfig::$hu_data_feng_shun;
					}
				}
				$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));
				
				if(!isset($tmp_hu_data[$key]))
				{
					$jiang_arr[] = 32; 
					$jiang_arr[] = 32;
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

			$this->_list_delete($chair,$value);
			
			//记录根到全局数据
			if(32 == array_sum($jiang_arr))
			{
				$is_danting_wuwan = false;
				break;
			}
		}
		$this->_list_insert($chair,5);
		return $is_danting_wuwan;
	}

    private function _log($class,$line,$title,$log)
    {
        $str = "类:$class 行号:$line\r\n";
        echo $str;
        var_dump($title);
        var_dump($log);
    }
}
