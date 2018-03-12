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
	const HU_TYPE_SHISANYAO = 22;               // 十三幺...
	const HU_TYPE_QIDUI = 23;                   // 七对
	const HU_TYPE_HAOHUA_QIDUI = 24;            // 豪华七对....
	const HU_TYPE_CHAOJI_QIDUI = 25;            // 超级豪华七对....
	const HU_TYPE_ZHUIZUN_QIDUI = 26;           // 至尊豪华七对....
	const HU_TYPE_PENGPENGHU = 30;           // 碰碰胡....

	const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   // 错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_TIANHU = 61;              // 天胡 
	const ATTACHED_HU_DIHU = 62;                // 地胡 
	const ATTACHED_HU_ZIMOFAN = 63;             // 自摸
	const ATTACHED_HU_GANGKAI = 64;             // 杠开
	const ATTACHED_HU_QIANGGANG = 65;           // 抢杠

	const ATTACHED_HU_QINGYISE = 66;            // 清一色
	const ATTACHED_HU_YITIAOLONG = 67;          // 一条龙
	const ATTACHED_HU_HAIDI = 68;               // 海底捞月
	const ATTACHED_HU_DA8ZHANG = 69;            // 打八张
	const ATTACHED_HU_MENQING = 70;             // 门清
	const ATTACHED_HU_ZIYISE = 72;            // 字一色

	const ATTACHED_HU_BIAN = 80;		//沧州边
	const ATTACHED_HU_ZUAN = 81;		//沧州钻
	const ATTACHED_HU_ZA = 82;		//沧州砸
	const ATTACHED_HU_WUKUI = 83;	//捉五魁

	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 3;                 // 直杠分
	const M_ANGANG_SCORE = 2;                  // 暗杠分
	const M_WANGANG_SCORE = 1;                 // 弯杠分

	public static $hu_type_arr = array(
	self::HU_TYPE_PINGHU=>[self::HU_TYPE_PINGHU, 1, '平胡']  //平胡  不就倍分 算底分  作为低分2分
	,self::HU_TYPE_PENGPENGHU=>[self::HU_TYPE_PENGPENGHU, 2, '碰碰胡']
	,self::HU_TYPE_SHISANYAO=>[self::HU_TYPE_SHISANYAO, 10, '十三幺']
	,self::HU_TYPE_QIDUI=>[self::HU_TYPE_QIDUI, 2, '七对']
	,self::HU_TYPE_HAOHUA_QIDUI=>[self::HU_TYPE_HAOHUA_QIDUI, 4, '豪华七对']
	,self::HU_TYPE_CHAOJI_QIDUI=>[self::HU_TYPE_CHAOJI_QIDUI, 8, '超级豪华七对']
	,self::HU_TYPE_ZHUIZUN_QIDUI=>[self::HU_TYPE_ZHUIZUN_QIDUI, 16, '至尊豪华七对']

	);

	public static $attached_hu_arr = array(
	self::ATTACHED_HU_TIANHU=>[self::ATTACHED_HU_TIANHU, 0, '天胡']
	,self::ATTACHED_HU_DIHU=>[self::ATTACHED_HU_DIHU, 0, '地胡']
	,self::ATTACHED_HU_ZIMOFAN=>[self::ATTACHED_HU_ZIMOFAN, 0, '自摸']
	,self::ATTACHED_HU_GANGKAI=>[self::ATTACHED_HU_GANGKAI, 2, '杠上花']
	,self::ATTACHED_HU_QIANGGANG=>[self::ATTACHED_HU_QIANGGANG, 2, '抢杠']

	,self::ATTACHED_HU_QINGYISE=>[self::ATTACHED_HU_QINGYISE, 2, '清一色']
	,self::ATTACHED_HU_ZIYISE=>[self::ATTACHED_HU_ZIYISE, 2, '字一色']
	,self::ATTACHED_HU_YITIAOLONG=>[self::ATTACHED_HU_YITIAOLONG, 2, '一条龙']
	,self::ATTACHED_HU_HAIDI=>[self::ATTACHED_HU_HAIDI, 0, '海底捞月']	
	,self::ATTACHED_HU_MENQING=>[self::ATTACHED_HU_MENQING, 2, '门清']

	,self::ATTACHED_HU_DA8ZHANG=>[self::ATTACHED_HU_DA8ZHANG, 0, '打八张']
	,self::ATTACHED_HU_BIAN=>[self::ATTACHED_HU_BIAN, 2, '边']
	,self::ATTACHED_HU_ZUAN=>[self::ATTACHED_HU_ZUAN, 1, '钻']
	,self::ATTACHED_HU_ZA=>[self::ATTACHED_HU_ZA, 1, '砸']
	,self::ATTACHED_HU_WUKUI=>[self::ATTACHED_HU_WUKUI, 1, '捉五魁']
	);	


	public $m_choice_za;			// 竞争选择砸 存储 
	public $m_bzz_state = array();	//边钻砸状态
	public $m_cancle_time;			// 解散房间开始时间

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
		$this->m_cancle_time = 0;
		for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
		{
			$this->m_bzz_state[$i] = self::BZZ_NULL;
		}
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
		$params['rule']['is_fanhun'] = !isset($params['rule']['is_fanhun']) ? 0 : $params['rule']['is_fanhun'];
		$params['rule']['is_chipai'] = !isset($params['rule']['is_chipai']) ? 0 : $params['rule']['is_chipai'];
		$params['rule']['is_genzhuang'] = !isset($params['rule']['is_genzhuang']) ? 0 : $params['rule']['is_genzhuang'];
		$params['rule']['is_paozi'] = !isset($params['rule']['is_paozi']) ? 1 : $params['rule']['is_paozi'];
		$params['rule']['is_quemen'] = !isset($params['rule']['is_quemen']) ? 0 : $params['rule']['is_quemen'];
		$params['rule']['is_zhuang_fan'] = !isset($params['rule']['is_zhuang_fan']) ? 0 : $params['rule']['is_zhuang_fan'];
		$params['rule']['is_qingyise_fan'] = !isset($params['rule']['is_qingyise_fan']) ? 1 : $params['rule']['is_qingyise_fan'];
		$params['rule']['is_ziyise_fan'] = !isset($params['rule']['is_ziyise_fan']) ? 1 : $params['rule']['is_ziyise_fan'];
		$params['rule']['is_yitiaolong_fan'] = !isset($params['rule']['is_yitiaolong_fan']) ? 1 : $params['rule']['is_yitiaolong_fan'];
		$params['rule']['is_shisanyao_fan'] = !isset($params['rule']['is_shisanyao_fan']) ? 0 : $params['rule']['is_shisanyao_fan'];
		$params['rule']['is_ganghua_fan'] = !isset($params['rule']['is_ganghua_fan']) ? 1 : $params['rule']['is_ganghua_fan'];
		$params['rule']['is_qidui_fan'] = !isset($params['rule']['is_qidui_fan']) ? 1 : $params['rule']['is_qidui_fan'];
		$params['rule']['is_tiandi_hu_fan'] = !isset($params['rule']['is_tiandi_hu_fan']) ? 0 : $params['rule']['is_tiandi_hu_fan'];
		$params['rule']['is_menqing_fan'] = !isset($params['rule']['is_menqing_fan']) ? 0 : $params['rule']['is_menqing_fan'];
		$params['rule']['is_pengpenghu_fan'] = !isset($params['rule']['is_pengpenghu_fan']) ? 1 : $params['rule']['is_pengpenghu_fan'];
		$params['rule']['is_wangang_1_lose'] = !isset($params['rule']['is_wangang_1_lose']) ? 0 : $params['rule']['is_wangang_1_lose'];
		$params['rule']['is_dianpao_bao'] = !isset($params['rule']['is_dianpao_bao']) ? 0 : $params['rule']['is_dianpao_bao'];
		$params['rule']['is_wukui'] = !isset($params['rule']['is_wukui']) ? 1 : $params['rule']['is_wukui'];
		$params['rule']['is_zhongfabai_shun'] = !isset($params['rule']['is_zhongfabai_shun']) ? 1 : $params['rule']['is_zhongfabai_shun'];
		$params['rule']['is_bian_zuan'] = !isset($params['rule']['is_bian_zuan']) ? 1 : $params['rule']['is_bian_zuan'];
		$params['rule']['is_za'] = !isset($params['rule']['is_za']) ? 1 : $params['rule']['is_za'];

		$this->m_rule->game_type = $params['rule']['game_type'];
		$this->m_rule->player_count = $params['rule']['player_count'];
		$this->m_rule->set_num = $params['rule']['set_num'];
		$this->m_rule->min_fan = $params['rule']['min_fan'];
		$this->m_rule->top_fan = $params['rule']['top_fan'];

		$this->m_rule->is_feng = $params['rule']['is_feng'];
		$this->m_rule->is_yipao_duoxiang = $params['rule']['is_yipao_duoxiang'];
		$this->m_rule->is_fanhun = $params['rule']['is_fanhun'];
		$this->m_rule->is_chipai = $params['rule']['is_chipai'];
		$this->m_rule->is_genzhuang = $params['rule']['is_genzhuang'];
		$this->m_rule->is_paozi = $params['rule']['is_paozi'];
		$this->m_rule->is_quemen = $params['rule']['is_quemen'];
		$this->m_rule->is_zhuang_fan = $params['rule']['is_zhuang_fan'];

		$this->m_rule->is_qingyise_fan = $params['rule']['is_qingyise_fan'];
		$this->m_rule->is_ziyise_fan = $params['rule']['is_ziyise_fan'];
		$this->m_rule->is_yitiaolong_fan = $params['rule']['is_yitiaolong_fan'];
		$this->m_rule->is_shisanyao_fan = $params['rule']['is_shisanyao_fan'];
		$this->m_rule->is_ganghua_fan = $params['rule']['is_ganghua_fan'];
		$this->m_rule->is_qidui_fan = $params['rule']['is_qidui_fan'];
		$this->m_rule->is_tiandi_hu_fan = $params['rule']['is_tiandi_hu_fan'];
		$this->m_rule->is_pengpenghu_fan = $params['rule']['is_pengpenghu_fan'];
		$this->m_rule->is_menqing_fan = $params['rule']['is_menqing_fan'];

		$this->m_rule->is_wangang_1_lose = $params['rule']['is_wangang_1_lose'];
		$this->m_rule->is_dianpao_bao = $params['rule']['is_dianpao_bao'];
		$this->m_rule->is_wukui = $params['rule']['is_wukui'];
		$this->m_rule->is_zhongfabai_shun = $params['rule']['is_zhongfabai_shun'];
		$this->m_rule->is_bian_zuan = $params['rule']['is_bian_zuan'];
		$this->m_rule->is_za = $params['rule']['is_za'];
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
			$params['za'] = (!empty($params['za']) && !empty($this->m_rule->is_za)) ? $params['za'] : 0;

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

					$no_bzz_num = 0; $bzz_num = 0;
					$this->_get_num_stand($key, $no_bzz_num, $bzz_num);
					if(!empty($params['za']))
					{
						if($this->m_bzz_state[$key] != self::BZZ_NULL && $this->m_bzz_state[$key] != self::BZZ_ZA)
						{
							$return_send['code'] = 6; $return_send['text'] = '砸杠错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}

						if($no_bzz_num >= 2)
						{
							$return_send['code'] = 6; $return_send['text'] = '砸杠错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
					}
					else
					{
						if($this->m_bzz_state[$key] != self::BZZ_NULL)
						{
							if($no_bzz_num >= 1)
							{
								$return_send['code'] = 5; $return_send['text'] = '杠牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
							}
						}
					}
					$this->_clear_choose_buf($key);
					$this->HandleChooseAnGang($key, $params['gang_card'], $params['za']);
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

			if(!isset($params['is_ting']))
			{
				$params['is_ting'] = 1;
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

					$this->HandleOutCard($key, $params['is_14'], $params['out_card'], $params['is_ting']);
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

    //暗杠 
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
							|| ($tmp_card_type == ConstConfig::PAI_TYPE_FENG && ($tmp_card_index != 6 || empty($this->m_rule->is_zhongfabai_shun)))
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
						else if($tmp_card_type == ConstConfig::PAI_TYPE_FENG && $tmp_card_index == 7 && !empty($this->m_rule->is_zhongfabai_shun))
						{
							if(empty($this->m_sPlayer[$key]->card[$tmp_card_type][5]) || empty($this->m_sPlayer[$key]->card[$tmp_card_type][6]))
							{
								$tmp_is_bian = false;
							}
						}
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
			$params['za'] = (!empty($params['za']) && !empty($this->m_rule->is_za)) ? $params['za'] : 0;

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

					$no_bzz_num = 0; $bzz_num = 0;
					$this->_get_num_stand($key, $no_bzz_num, $bzz_num);
					if(!empty($params['za']))
					{
						if($this->m_bzz_state[$key] != self::BZZ_NULL && $this->m_bzz_state[$key] != self::BZZ_ZA)
						{
							$this->c_cancle_choice($fd, $params);
							$return_send['code'] = 6; $return_send['text'] = '砸错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
						if($no_bzz_num >= 2)
						{
							$this->c_cancle_choice($fd, $params);
							$return_send['code'] = 6; $return_send['text'] = '砸错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
					}
					else
					{
						if($this->m_bzz_state[$key] != self::BZZ_NULL)
						{
							if($no_bzz_num >= 1)
							{
								$this->c_cancle_choice($fd, $params);
								$return_send['code'] = 5; $return_send['text'] = '碰牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
							}
						}
					}

					$this->_clear_choose_buf($key);
					$this->HandleChooseResult($key, $params['act'], null, $params['za']);
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

					$no_bzz_num = 0; $bzz_num = 0;
					$this->_get_num_stand($key, $no_bzz_num, $bzz_num);
					if($this->m_bzz_state[$key] != self::BZZ_NULL)
					{
						if($no_bzz_num >= 1)
						{
							$return_send['code'] = 5; $return_send['text'] = '吃牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
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
			$params['za'] = (!empty($params['za']) && !empty($this->m_rule->is_za)) ? $params['za'] : 0;

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

					$no_bzz_num = 0; $bzz_num = 0;
					$this->_get_num_stand($key, $no_bzz_num, $bzz_num);
					if(!empty($params['za']))
					{
						if($this->m_bzz_state[$key] != self::BZZ_NULL && $this->m_bzz_state[$key] != self::BZZ_ZA)
						{
							$this->c_cancle_choice($fd, $params);
							$return_send['code'] = 6; $return_send['text'] = '砸杠错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
						if($no_bzz_num >= 2)
						{
							$this->c_cancle_choice($fd, $params);
							$return_send['code'] = 6; $return_send['text'] = '砸杠错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
						}
					}
					else
					{
						if($this->m_bzz_state[$key] != self::BZZ_NULL)
						{
							if($no_bzz_num >= 1)
							{
								$this->c_cancle_choice($fd, $params);
								$return_send['code'] = 5; $return_send['text'] = '杠牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
							}
						}
					}
					$this->_clear_choose_buf($key);
					$this->HandleChooseResult($key, $params['act'], null, $params['za']);
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
						if( BaseGame::is_hu_give_up($temp_card, $this->m_nHuGiveUp[$last_chair]) || !$this->judge_hu($last_chair,$is_fanhun))
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
		$hu_type = $this->judge_hu_type_fanhun($chair, $is_qingyise, $is_yitiaolong, $is_ziyise, $is_fanhun, $bzz_num, $is_wukui);

		if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
		{
			return false;
		}

		//记录在全局数据
		$this->m_HuCurt[$chair]->method[0] = $hu_type;
		$this->m_HuCurt[$chair]->count = 1;

		//天地胡处理
		if(!empty($this->m_rule->is_tiandi_hu_fan))
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
		if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZIMOFAN);
		}

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

		//海底捞月
		// if($this->m_nCountAllot >= $this->m_nAllCardNum - 5) //海底月
		// {
		// 	$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_HAIDI);
		// }

		//门清
		if(!empty($this->m_rule->is_menqing_fan) && $this->_is_menqing($chair))
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_MENQING);
		}

		//捉五魁
		if(!empty($this->m_rule->is_wukui) && $is_wukui)
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_WUKUI);
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

		$attached_fan_sum = 0;
		$attached_fan_times = 1;
		for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
		{
			if(($this->m_HuCurt[$chair]->method[$i] == self::ATTACHED_HU_GANGKAI || $this->m_HuCurt[$chair]->method[$i] == self::ATTACHED_HU_QIANGGANG)
				&& isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]])
			)
			{
				$attached_fan_times *= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
			}
			else if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
			{
				$attached_fan_sum += self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
			}

			$tmp_hu_desc .= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
		}

		$fan_sum = ($attached_fan_sum + $fan_sum) * $attached_fan_times;

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
	public function judge_hu_type_fanhun($chair, &$is_qingyise, &$is_yitiaolong, &$is_ziyise, $is_fanhun = false, &$bzz_num, &$is_wukui)
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
			return $this->judge_hu_type($chair, $is_qingyise, $is_yitiaolong, $is_ziyise, $is_quemen, $bzz_num, $is_wukui);
		}
		else
		{
			$return_type = self::HU_TYPE_FENGDING_TYPE_INVALID;	
	
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
	public function judge_hu_type($chair, &$is_qingyise, &$is_yitiaolong, &$is_ziyise, $is_quemen, &$bzz_num, &$is_wukui)
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

		$bzz_num = 0;
		$is_wukui = false;
		//倒牌
		for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
		{
			$stand_pai_type = $this->_get_card_type($this->m_sStandCard[$chair]->first_card[$i]);
			$stand_pai_key = $this->m_sStandCard[$chair]->first_card[$i] % 16;
			
			$qidui_arr[] = 0;
			$shisanyao_arr[] = 0;
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
				$shisanyao_arr[] = 0;
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

				//判断边钻砸,五魁
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

						$tmp_card_arr = $this->m_sPlayer[$chair]->card[$hu_card_type];
						if(!empty($this->m_rule->is_wukui) && $hu_card_type == ConstConfig::PAI_TYPE_WAN && $hu_card_index == 5
							&& $tmp_card_arr[$hu_card_index] > 0 && $tmp_card_arr[$hu_card_index + 1] > 0 && $tmp_card_arr[$hu_card_index - 1] > 0
						)
						{
							$tmp_card_arr[$hu_card_index] -= 1;
							$tmp_card_arr[$hu_card_index + 1] -= 1;
							$tmp_card_arr[$hu_card_index - 1] -= 1;
							$tmp_key = intval(implode('', array_slice($tmp_card_arr, 1)));
							if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
							{
								$is_wukui = true;
							}
						}
					}
				}
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

		if($this->m_bzz_state[$chair] != self::BZZ_NULL && $bzz_num < 3)
		{
			return self::HU_TYPE_FENGDING_TYPE_INVALID ;
		}
		//13幺
		if($this->m_rule->is_shisanyao_fan && $this->m_rule->is_feng && $bShiSanYao)
		{
			$is_quemen = true;	//13幺，不计缺门
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
		$car_14 = $this->_find_14_card($chair);
		if(!$car_14)
		{
			echo "error HandleChoosePeng".__LINE__.__CLASS__;
			return false;
		}

		//置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
		--$this->m_nNumTableCards[$this->m_sOutedCard->chair];
		if($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
		{
			unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
		}
		$this->m_sPlayer[$chair]->card_taken_now = $car_14;

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
		$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
		
		$this->m_eat_num = 0;
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

	//处理直杠 k2
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
			for ($i=0; $i<$this->m_rule->player_count; $i++)
			{
				if ($i == $chair)
				{
					continue;
				}
				if ($stand_count_after > 0 && $i == $this->m_sStandCard[$chair]->who_give_me[$stand_count_after-1])
				{
					$nGangScore =self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;

					$this->m_wGangScore[$i][$i] -= $nGangScore;
					$this->m_wGangScore[$chair][$chair] += $nGangScore;
					$this->m_wGangScore[$chair][$i] += $nGangScore;

					$nGangPao += $nGangScore;
				}
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
		$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄

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
			$this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
		}
		
		$this->m_chairSendCmd = 255;							// 当前发命令的玩家
		$this->m_currentCmd = 0;							// 当前的命令		
	}

	//处理自摸 
	public function HandleHuZiMo($chair)			//处理自摸
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
			//总计自摸
			if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
			{
				$this->m_wTotalScore[$chair]->n_zimo += 1;
				$this->m_currentCmd = 'c_zimo_hu';
			}

			$this->m_chairSendCmd = $this->m_chairCurrentPlayer;

			$this->m_bChairHu[$chair] = true;
			$this->m_bChairHu_order[] = $chair;
			$this->m_nCountHu++;
			$this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;

			//去除胡牌者 card_taken_now  这个牌就只有在 m_HuCurt 有
			$this->m_sPlayer[$chair]->card_taken_now = 0;

			$tmp_lost_chair = 255;
			$this->ScoreOneHuCal($chair, $tmp_lost_chair);

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

	//处理出牌 
	public function HandleOutCard($chair, $is_14 = false, $out_card = 0, $is_ting = 1)		
	{
		//一旦有人出牌，表示上一轮竞争已经结束, 可以清CMD
		$this->m_chairSendCmd = 255;							// 当前发命令的玩家
		$this->m_currentCmd = 0;							// 当前的命令
		$this->m_eat_num = 0;
		$this->m_choice_za = 0;

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
			if($card_index == 3 || ($card_index == 7 && $card_type == ConstConfig::PAI_TYPE_FENG))
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
		$car_14 = $this->_find_14_card($chair);
		if(!$car_14)
		{
			echo "error dddf".__LINE__.__CLASS__;
			return false;
		}

		$this->m_sPlayer[$chair]->card_taken_now = $car_14;


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

			return $data;
		}
		return true;
	}

	///////////////////////得分处理///////////////////////////
	//每局个人  +=赢的分  +=输的分  +=庄家 的分
	public function ScoreOneHuCal($chair, &$lost_chair)  
	{
		$fan_sum = $this->judge_fan($chair);  //这个就是  一共多少分
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

				if ($this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
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

				$banker_fan = 0;
				if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair))
				{
					$banker_fan = 1;
				}

				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
				$wWinScore = 2 * ConstConfig::SCORE_BASE * ($PerWinScore + $banker_fan);

				$wWinPaoZi = 2*($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$lost_chair]->num);
				$this->m_paozi_score[$chair] += $wWinPaoZi;
				$this->m_paozi_score[$lost_chair] -= $wWinPaoZi;
				
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
			//点炮大包
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
			else if(!empty($this->m_rule->is_dianpao_bao) && $this->m_rule->is_dianpao_bao == 2)
			{
				//点炮三家出
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
			else if(empty($this->m_rule->is_dianpao_bao))
			{
				$banker_fan = 0;
				if(!empty($this->m_rule->is_zhuang_fan) && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair))
				{
					$banker_fan = 1;
				}

				//一家出
				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;	
				$wWinScore = 0;
				$wWinScore +=  ConstConfig::SCORE_BASE * ($PerWinScore + $banker_fan);
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
			$this->m_Score[$i]->score = $this->m_wSetScore[$i]+ $this->m_wSetLoseScore[$i]+ $this->m_wGangScore[$i][$i] +$this->m_wFollowScore[$i]+ $this->m_paozi_score[$i];
			$this->m_Score[$i]->set_count = $this->m_nSetCount;
			if ($this->m_Score[$i]->score > 0)
			{
				$this->m_Score[$i]->win_count = 1;
			}
			else
			{
				$this->m_Score[$i]->lose_count = 1;
			}
			//录像信息用
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
			if(true)
			{
				//荒庄荒杠（包括跟庄分数）
				$this->m_wGangScore[$i][$i] = 0; 
				$this->m_wFollowScore[$i] = 0;
				$this->m_Score[$i]->score = 0;
			}
			else
			{
				$this->m_Score[$i]->score = $this->m_wSetScore[$i]+ $this->m_wSetLoseScore[$i]+ $this->m_wGangScore[$i][$i] +$this->m_wFollowScore[$i]+ $this->m_paozi_score[$i];
			}

			$this->m_Score[$i]->set_count = $this->m_nSetCount;

			if ($this->m_Score[$i]->score>0)
			{
				$this->m_Score[$i]->win_count = 1;
			}
			else
			{
				$this->m_Score[$i]->lose_count = 1;
			}

			//录像信息用
			$this->m_room_players[$i]['score'] = $this->m_Score[$i]->score;
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

			if(!empty($this->m_rule->is_paozi))
			{
				if($this->m_paozi_score[$i]>0)
				{
					$this->m_hu_desc[$i] .= '跑儿+'.$this->m_paozi_score[$i].' ';
				}
				else
				{
					$this->m_hu_desc[$i] .= '跑儿'.$this->m_paozi_score[$i].' ';
				}
			}

			if(!empty($this->m_rule->is_genzhuang))
			{
				if($this->m_wFollowScore[$i]>0)
				{
					$this->m_hu_desc[$i] .= '跟庄+'.$this->m_wFollowScore[$i].' ';
				}
				else
				{
					$this->m_hu_desc[$i] .= '跟庄'.$this->m_wFollowScore[$i].' ';
				}				
			}

			
		}
	}

	////////////////////////////其他///////////////////////////

	//倒牌某门牌的个数
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
						$card_num += 4;
					}
				}
			}		
		}
		return $card_num;
	}

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
