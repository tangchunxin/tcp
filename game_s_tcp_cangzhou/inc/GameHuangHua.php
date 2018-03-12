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

class GameHuangHua extends BaseGame
{
	const GAME_TYPE = 263;

	//----- bian zuan za state -------
	const BZZ_NULL = 0;	//无
	const BZZ_BIAN = 1;	//边
	const BZZ_ZUAN = 2;	//钻
	const BZZ_ZA   = 3;	//砸

	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21;                  // 平胡
	const HU_TYPE_QIDUI = 22;                   // 七对
    const HU_TYPE_SHISANBUKAO = 23;           	// 十三不靠

	const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   // 错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_ZIMOFAN = 61;             // 自摸
	const ATTACHED_HU_GANGKAI = 62;             // 杠开
	const ATTACHED_HU_QIANGGANG = 63;           // 抢杠

	const ATTACHED_HU_QINGYISE = 64;            // 清一色
	const ATTACHED_HU_YITIAOLONG = 65;          // 一条龙
	const ATTACHED_HU_HUNYISE = 66;            	// 混一色
    const ATTACHED_HU_BIANZHANG = 67;           // 边张
    const ATTACHED_HU_KANZHANG = 68;            // 坎张
    const ATTACHED_HU_ZHONGFABAI = 69;          // 中发白
    const ATTACHED_HU_YIBIANGAO = 70;           // 一边高
    const ATTACHED_HU_MENQING = 71;             // 门清
    const ATTACHED_HU_ERWUBAJIANG = 72;         // 258将
    const ATTACHED_HU_QUEMEN = 73;              // 缺门
    const ATTACHED_HU_GUJAING = 74;             // 孤将
    const ATTACHED_HU_DANDIAO = 75;             // 单吊
    const ATTACHED_HU_DAUNYAOJIU = 76;          // 断幺九
    const ATTACHED_HU_SIGUIYI = 77;             // 四归一
    const ATTACHED_HU_GULIANLIU = 78;           // 孤连六
    const ATTACHED_HU_DAXIAOWU = 79;            // 大小五
    const ATTACHED_HU_GOUSHAN = 80;             // 够扇
    const ATTACHED_HU_TIANHU = 81;              // 天胡
    const ATTACHED_HU_DIHU = 82;                // 地胡

    const ATTACHED_HU_BIAN = 83;				//沧州边
	const ATTACHED_HU_ZUAN = 84;				//沧州钻
	const ATTACHED_HU_ZA = 85;					//沧州砸

    const ATTACHED_HU_KAWUKUI = 86;            // 卡五魁
    
	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 1;                 // 直杠分
	const M_ANGANG_SCORE = 2;                  // 暗杠分
	const M_WANGANG_SCORE = 1;                 // 弯杠分

	public static $hu_type_arr = array(
		self::HU_TYPE_PINGHU=>array(self::HU_TYPE_PINGHU, 1, '平胡')
		,self::HU_TYPE_QIDUI=>array(self::HU_TYPE_QIDUI, 3, '七对')
        ,self::HU_TYPE_SHISANBUKAO=>array(self::HU_TYPE_SHISANBUKAO, 4, '十三不靠')
	);

	public static $attached_hu_arr = array(
		self::ATTACHED_HU_ZIMOFAN=>array(self::ATTACHED_HU_ZIMOFAN, 0, '自摸')
		,self::ATTACHED_HU_GANGKAI=>array(self::ATTACHED_HU_GANGKAI, 0, '杠上花')
		,self::ATTACHED_HU_QIANGGANG=>array(self::ATTACHED_HU_QIANGGANG, 0, '抢杠')
        ,self::ATTACHED_HU_TIANHU=>array(self::ATTACHED_HU_TIANHU, 7, '天胡')
        ,self::ATTACHED_HU_DIHU=>array(self::ATTACHED_HU_DIHU, 1, '地胡')

		,self::ATTACHED_HU_QINGYISE=>array(self::ATTACHED_HU_QINGYISE, 2, '清一色')
		,self::ATTACHED_HU_HUNYISE=>array(self::ATTACHED_HU_HUNYISE, 2, '混一色')
		,self::ATTACHED_HU_YITIAOLONG=>array(self::ATTACHED_HU_YITIAOLONG, 2, '一条龙')
        ,self::ATTACHED_HU_BIANZHANG=>array(self::ATTACHED_HU_BIANZHANG, 1, '边张')
        ,self::ATTACHED_HU_KANZHANG=>array(self::ATTACHED_HU_KANZHANG, 1, '坎张')
        ,self::ATTACHED_HU_KAWUKUI=>array(self::ATTACHED_HU_KAWUKUI, 1, '卡五魁')
        ,self::ATTACHED_HU_ZHONGFABAI=>array(self::ATTACHED_HU_ZHONGFABAI, 1, '中发白')
        ,self::ATTACHED_HU_YIBIANGAO=>array(self::ATTACHED_HU_YIBIANGAO, 1, '一边高')
        ,self::ATTACHED_HU_MENQING=>array(self::ATTACHED_HU_MENQING, 1, '门清')
        ,self::ATTACHED_HU_ERWUBAJIANG=>array(self::ATTACHED_HU_ERWUBAJIANG, 1, '258将')
        ,self::ATTACHED_HU_QUEMEN=>array(self::ATTACHED_HU_QUEMEN, 1, '缺门')
        ,self::ATTACHED_HU_GUJAING=>array(self::ATTACHED_HU_GUJAING, 1, '孤将')
        ,self::ATTACHED_HU_DANDIAO=>array(self::ATTACHED_HU_DANDIAO, 1, '单吊')
        ,self::ATTACHED_HU_DAUNYAOJIU=>array(self::ATTACHED_HU_DAUNYAOJIU, 1, '断幺九')
        ,self::ATTACHED_HU_SIGUIYI=>array(self::ATTACHED_HU_SIGUIYI, 2, '四归一')
        ,self::ATTACHED_HU_GULIANLIU=>array(self::ATTACHED_HU_GULIANLIU, 1, '孤连六')
        ,self::ATTACHED_HU_DAXIAOWU=>array(self::ATTACHED_HU_DAXIAOWU, 1, '大小五')
        ,self::ATTACHED_HU_GOUSHAN=>array(self::ATTACHED_HU_GOUSHAN, 2, '够扇')

		,self::ATTACHED_HU_BIAN=>array(self::ATTACHED_HU_BIAN, 2, '边')
		,self::ATTACHED_HU_ZUAN=>array(self::ATTACHED_HU_ZUAN, 1, '钻')
		,self::ATTACHED_HU_ZA=>array(self::ATTACHED_HU_ZA, 1, '砸')
	);

	public $m_choice_za;			// 竞争选择砸 存储 
	public $m_bzz_state = array();	// 边钻砸状态
    public $GangNum = 0 ;           // 杠的数量-用于四杠荒庄

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
        $this->GangNum = 0 ;
		for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
		{
			$this->m_bzz_state[$i] = self::BZZ_NULL;
		}

		$this->player_score = array(0,0,0,0);
		$this->player_cup   = array(0,0,0,0);
	}

	public function _open_room_sub($params)
	{
        $this->m_rule = new RuleHuangHua();

        $params['rule']['game_type'] = isset($params['rule']['game_type']) ? $params['rule']['game_type']: 263;
        $params['rule']['player_count'] = isset($params['rule']['player_count']) ? $params['rule']['player_count']: 4;
        $params['rule']['set_num'] = isset($params['rule']['set_num']) ? $params['rule']['set_num']: 8;
        $params['rule']['top_fan'] = isset($params['rule']['top_fan']) ? $params['rule']['top_fan']: 255;
        $params['rule']['is_circle'] = isset($params['rule']['is_circle']) ? $params['rule']['is_circle']: 4;

        $params['rule']['is_feng'] = isset($params['rule']['is_feng']) ? $params['rule']['is_feng']: 1;
        $params['rule']['min_fan'] = isset($params['rule']['min_fan']) ? $params['rule']['min_fan']: 0;
        $params['rule']['is_yipao_duoxiang'] = isset($params['rule']['is_yipao_duoxiang']) ? $params['rule']['is_yipao_duoxiang']: 0;
        $params['rule']['is_chi'] = isset($params['rule']['is_chi']) ? $params['rule']['is_chi']: 0;
        $params['rule']['is_genzhuang'] = isset($params['rule']['is_genzhuang']) ? $params['rule']['is_genzhuang']: 0;

        $params['rule']['is_paozi'] = isset($params['rule']['is_paozi']) ? $params['rule']['is_paozi']: 0;
        $params['rule']['is_zhuang_fan'] = isset($params['rule']['is_zhuang_fan']) ? $params['rule']['is_zhuang_fan']: 0;
        $params['rule']['is_qingyise_fan'] = isset($params['rule']['is_qingyise_fan']) ? $params['rule']['is_qingyise_fan']: 1;
        $params['rule']['is_hunyise_fan'] = isset($params['rule']['is_hunyise_fan']) ? $params['rule']['is_hunyise_fan']: 1;
        $params['rule']['is_yitiaolong_fan'] = isset($params['rule']['is_yitiaolong_fan']) ? $params['rule']['is_yitiaolong_fan']: 1;

        $params['rule']['is_ganghua_fan'] = isset($params['rule']['is_ganghua_fan']) ? $params['rule']['is_ganghua_fan']: 1;
        $params['rule']['is_bianzhang_fan'] = isset($params['rule']['is_bianzhang_fan']) ? $params['rule']['is_bianzhang_fan']: 1;
        $params['rule']['is_kanzhang_fan'] = isset($params['rule']['is_kanzhang_fan']) ? $params['rule']['is_kanzhang_fan']: 1;
        $params['rule']['is_zhongfabai_fan'] = isset($params['rule']['is_zhongfabai_fan']) ? $params['rule']['is_zhongfabai_fan']: 1;
        $params['rule']['is_yibiangao_fan'] = isset($params['rule']['is_yibiangao_fan']) ? $params['rule']['is_yibiangao_fan']: 1;

        $params['rule']['is_menqing_fan'] = isset($params['rule']['is_menqing_fan']) ? $params['rule']['is_menqing_fan']: 1;
        $params['rule']['is_erwubajiang_fan'] = isset($params['rule']['is_erwubajiang_fan']) ? $params['rule']['is_erwubajiang_fan']: 1;
        $params['rule']['is_quemen_fan'] = isset($params['rule']['is_quemen_fan']) ? $params['rule']['is_quemen_fan']: 1;
        $params['rule']['is_gujiang_fan'] = isset($params['rule']['is_gujiang_fan']) ? $params['rule']['is_gujiang_fan']: 1;
        $params['rule']['is_dandiao_fan'] = isset($params['rule']['is_dandiao_fan']) ? $params['rule']['is_dandiao_fan']: 1;

        $params['rule']['is_duanyaojiu_fan'] = isset($params['rule']['is_duanyaojiu_fan']) ? $params['rule']['is_duanyaojiu_fan']: 1;

        $params['rule']['is_siguiyi_fan'] = isset($params['rule']['is_siguiyi_fan']) ? $params['rule']['is_siguiyi_fan']: 1;
        $params['rule']['is_gulianliu_fan'] = isset($params['rule']['is_gulianliu_fan']) ? $params['rule']['is_gulianliu_fan']: 1;
        $params['rule']['is_daxiaowu_fan'] = isset($params['rule']['is_daxiaowu_fan']) ? $params['rule']['is_daxiaowu_fan']: 1;
        $params['rule']['is_goushan_fan'] = isset($params['rule']['is_goushan_fan']) ? $params['rule']['is_goushan_fan']: 1;
        $params['rule']['is_tianhu_fan'] = isset($params['rule']['is_tianhu_fan']) ? $params['rule']['is_tianhu_fan']: 1;

        $params['rule']['is_dihu_fan'] = isset($params['rule']['is_dihu_fan']) ? $params['rule']['is_dihu_fan']: 1;
        $params['rule']['is_za'] = isset($params['rule']['is_za']) ? $params['rule']['is_za']: 1;
        $params['rule']['is_bian_zuan'] = isset($params['rule']['is_bian_zuan']) ? $params['rule']['is_bian_zuan']: 1;
        $params['rule']['is_zhongfabai_shun'] = isset($params['rule']['is_zhongfabai_shun']) ? $params['rule']['is_zhongfabai_shun']: 1;
        $params['rule']['is_wukui'] = isset($params['rule']['is_wukui']) ? $params['rule']['is_wukui']: 0;

        $params['rule']['is_diaowuwan'] = isset($params['rule']['is_diaowuwan']) ? $params['rule']['is_diaowuwan']: 0;
        $params['rule']['is_ziyise_fan'] = isset($params['rule']['is_ziyise_fan']) ? $params['rule']['is_ziyise_fan']: 0;
        $params['rule']['is_qidui_fan'] = isset($params['rule']['is_qidui_fan']) ? $params['rule']['is_qidui_fan']: 1;
        $params['rule']['is_shisanbukao_fan'] = isset($params['rule']['is_shisanbukao_fan']) ? $params['rule']['is_shisanbukao_fan']: 1;
        $params['rule']['is_pengpenghu_fan'] = isset($params['rule']['is_pengpenghu_fan']) ? $params['rule']['is_pengpenghu_fan']: 0;

        $params['rule']['is_wangang_1_lose'] = isset($params['rule']['is_wangang_1_lose']) ? $params['rule']['is_wangang_1_lose']: 1;
        $params['rule']['allow_louhu'] = isset($params['rule']['allow_louhu']) ? $params['rule']['allow_louhu']: 0;
        $params['rule']['qg_is_zimo'] = isset($params['rule']['qg_is_zimo']) ? $params['rule']['qg_is_zimo']: 1;
        $params['rule']['cancle_clocker'] = isset($params['rule']['cancle_clocker']) ? $params['rule']['cancle_clocker']: 1;
        $params['rule']['pay_type'] = isset($params['rule']['pay_type']) ? $params['rule']['pay_type']: 1;
        $params['rule']['is_dianpao_bao'] = isset($params['rule']['is_dianpao_bao']) ? $params['rule']['is_dianpao_bao']: 0;

        $params['rule']['score'] = isset($params['rule']['score']) ? $params['rule']['score'] : 0;
        $this->m_rule->score = $params['rule']['score'];
        $params['rule']['is_score_field'] = isset($params['rule']['is_score_field']) ?  $params['rule']['is_score_field']: 0;
        $this->m_rule->is_score_field = $params['rule']['is_score_field'];
        
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
        $this->m_rule->top_fan = $params['rule']['top_fan'];
        $this->m_rule->is_circle = $params['rule']['is_circle'];

        $this->m_rule->is_feng = $params['rule']['is_feng'];
        $this->m_rule->min_fan = $params['rule']['min_fan'];
        $this->m_rule->is_yipao_duoxiang = $params['rule']['is_yipao_duoxiang'];
        $this->m_rule->is_chi = $params['rule']['is_chi'];
        $this->m_rule->is_genzhuang = $params['rule']['is_genzhuang'];

        $this->m_rule->is_paozi = $params['rule']['is_paozi'];
        $this->m_rule->is_zhuang_fan = $params['rule']['is_zhuang_fan'];
        $this->m_rule->is_qingyise_fan = $params['rule']['is_qingyise_fan'];
        $this->m_rule->is_hunyise_fan = $params['rule']['is_hunyise_fan'];
        $this->m_rule->is_yitiaolong_fan = $params['rule']['is_yitiaolong_fan'];

        $this->m_rule->is_ganghua_fan = $params['rule']['is_ganghua_fan'];
        $this->m_rule->is_bianzhang_fan = $params['rule']['is_bianzhang_fan'];
        $this->m_rule->is_kanzhang_fan = $params['rule']['is_kanzhang_fan'];
        $this->m_rule->is_zhongfabai_fan = $params['rule']['is_zhongfabai_fan'];
        $this->m_rule->is_yibiangao_fan = $params['rule']['is_yibiangao_fan'];

        $this->m_rule->is_menqing_fan = $params['rule']['is_menqing_fan'];
        $this->m_rule->is_erwubajiang_fan = $params['rule']['is_erwubajiang_fan'];
        $this->m_rule->is_quemen_fan = $params['rule']['is_quemen_fan'];
        $this->m_rule->is_gujiang_fan = $params['rule']['is_gujiang_fan'];
        $this->m_rule->is_dandiao_fan = $params['rule']['is_dandiao_fan'];

        $this->m_rule->is_duanyaojiu_fan = $params['rule']['is_duanyaojiu_fan'];

        $this->m_rule->is_siguiyi_fan = $params['rule']['is_siguiyi_fan'];
        $this->m_rule->is_gulianliu_fan = $params['rule']['is_gulianliu_fan'];
        $this->m_rule->is_daxiaowu_fan = $params['rule']['is_daxiaowu_fan'];
        $this->m_rule->is_goushan_fan = $params['rule']['is_goushan_fan'];
        $this->m_rule->is_tianhu_fan = $params['rule']['is_tianhu_fan'];

        $this->m_rule->is_dihu_fan = $params['rule']['is_dihu_fan'];
        $this->m_rule->is_za = $params['rule']['is_za'];
        $this->m_rule->is_bian_zuan = $params['rule']['is_bian_zuan'];
        $this->m_rule->is_zhongfabai_shun = $params['rule']['is_zhongfabai_shun'];
        $this->m_rule->is_wukui = $params['rule']['is_wukui'];

        $this->m_rule->is_diaowuwan = $params['rule']['is_diaowuwan'];
        $this->m_rule->is_ziyise_fan = $params['rule']['is_ziyise_fan'];
        $this->m_rule->is_qidui_fan = $params['rule']['is_qidui_fan'];
        $this->m_rule->is_shisanbukao_fan = $params['rule']['is_shisanbukao_fan'];
        $this->m_rule->is_pengpenghu_fan = $params['rule']['is_pengpenghu_fan'];

        $this->m_rule->is_wangang_1_lose = $params['rule']['is_wangang_1_lose'];
        $this->m_rule->allow_louhu = $params['rule']['allow_louhu'];
        $this->m_rule->qg_is_zimo = $params['rule']['qg_is_zimo'];
        $this->m_rule->cancle_clocker = $params['rule']['cancle_clocker'];
        $this->m_rule->pay_type = $params['rule']['pay_type'];

        $this->m_rule->is_dianpao_bao = $params['rule']['is_dianpao_bao'];
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
                        if($tmp_card_type < ConstConfig::PAI_TYPE_FENG && ($tmp_card_index == 1 || $tmp_card_index == 9))
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
    
    //碰牌 -- 砸
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
                        //if($no_bzz_num >= 2)
                        //{
                        //    $this->c_cancle_choice($fd, $params);
                        //    $return_send['code'] = 6; $return_send['text'] = '砸错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                        //}
                    }
                    //else
                    //{
                    //    if($this->m_bzz_state[$key] != self::BZZ_NULL)
                    //    {
                    //        if($no_bzz_num >= 1)
                    //        {
                    //            $this->c_cancle_choice($fd, $params);
                    //            $return_send['code'] = 5; $return_send['text'] = '碰牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    //        }
                    //    }
                    //}

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

    //暗杠 -- 砸
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
                    //if(!empty($params['za']))
                    //{
                    //    if($this->m_bzz_state[$key] != self::BZZ_NULL && $this->m_bzz_state[$key] != self::BZZ_ZA)
                    //    {
                    //        $return_send['code'] = 6; $return_send['text'] = '砸杠错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    //    }
                    //
                    //    //if($no_bzz_num >= 2)
                    //    //{
                    //    //    $return_send['code'] = 6; $return_send['text'] = '砸杠错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    //    //}
                    //}
                    //else
                    //{
                    //    //if($this->m_bzz_state[$key] != self::BZZ_NULL)
                    //    //{
                    //    //    if($no_bzz_num >= 1)
                    //    //    {
                    //    //        $return_send['code'] = 5; $return_send['text'] = '杠牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    //    //    }
                    //    //}
                    //}
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

    //直杠 -- 砸
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
                    //if(!empty($params['za']))
                    //{
                    //    if($this->m_bzz_state[$key] != self::BZZ_NULL && $this->m_bzz_state[$key] != self::BZZ_ZA)
                    //    {
                    //        $this->c_cancle_choice($fd, $params);
                    //        $return_send['code'] = 6; $return_send['text'] = '砸杠错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    //    }
                    //    //if($no_bzz_num >= 2)
                    //    //{
                    //    //    $this->c_cancle_choice($fd, $params);
                    //    //    $return_send['code'] = 6; $return_send['text'] = '砸杠错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    //    //}
                    //}
                    //else
                    //{
                    //    if($this->m_bzz_state[$key] != self::BZZ_NULL)
                    //    {
                    //        if($no_bzz_num >= 1)
                    //        {
                    //            $this->c_cancle_choice($fd, $params);
                    //            $return_send['code'] = 5; $return_send['text'] = '杠牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    //        }
                    //    }
                    //}
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
                        if (($this->m_sStandCard[$key]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                            && $this->m_sStandCard[$key]->card[$i] == $params['gang_card'])
                            ||($this->m_sStandCard[$key]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                                && $this->m_sStandCard[$key]->card[$i] == $params['gang_card']))
                        {
                            $have_wan_gang = true;
                            break;
                        }
                    }
                    if(!$have_wan_gang)
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
    //--------------------------------------------------------------------------

	//判断胡  
	public function judge_hu($chair, $is_fanhun = false)
	{
		//胡牌型--附加番
        $is_bianzhang = false;
        $is_kanzhang = false;
        $is_kawukui = false;
        $is_zhongfabai = false;
        $is_yibiangao = false;
        $is_menqing = false;
        $is_258jiang = false;
        $is_quemen = false;
        $is_gujiang = false;
        $is_dandiao = false;
        $is_duanyaojiu = false;
        $is_siguiyi = false;
        $is_gulianliu = false;
        $is_daxiaowu = false;
        $is_goushan = false;
        $is_hunyise = false;
		$is_qingyise = false;
        $is_yitiaolong = false;
		$bzz_num = 0;

		$hu_type = $this->judge_hu_type($chair, $is_bianzhang, $is_kanzhang,$is_kawukui, $is_zhongfabai, $is_yibiangao, $is_menqing, $is_258jiang, $is_quemen, $is_gujiang, $is_dandiao,$is_duanyaojiu,$is_siguiyi,$is_gulianliu,$is_daxiaowu,$is_goushan,$is_hunyise,$is_qingyise,$is_yitiaolong,$bzz_num);

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
        if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == $qg_state)   // 处理抢杠
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
		}
		else if(!empty($this->m_rule->is_ganghua_fan) && $this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair)	//杠开
		{
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGKAI);
		}


		//天地胡处理
        if(!empty($this->m_rule->is_tianhu_fan) || !empty($this->m_rule->is_dihu_fan))
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

        //边张
        if($is_bianzhang && !empty($this->m_rule->is_bianzhang_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_BIANZHANG);
        }

        //坎张
        if($is_kanzhang && !empty($this->m_rule->is_kanzhang_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_KANZHANG);
        }

        //卡五魁
        if($is_kawukui && !empty($this->m_rule->is_kanzhang_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_KAWUKUI);
        }

        //中发白
        if($is_zhongfabai && !empty($this->m_rule->is_zhongfabai_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZHONGFABAI);
        }

        //一边高
        if($is_yibiangao && !empty($this->m_rule->is_yibiangao_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_YIBIANGAO);
        }

        //门清
        if($is_menqing && !empty($this->m_rule->is_menqing_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_MENQING);
        }

        //258将
        if($is_258jiang && !empty($this->m_rule->is_erwubajiang_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ERWUBAJIANG);
        }

        //缺门
        if($is_quemen && !empty($this->m_rule->is_quemen_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QUEMEN);
        }

        //孤将
        if($is_gujiang && !empty($this->m_rule->is_gujiang_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GUJAING);
        }

        //单吊
        if($is_dandiao && !empty($this->m_rule->is_dandiao_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_DANDIAO);
        }

        //断幺九
        if($is_duanyaojiu && !empty($this->m_rule->is_duanyaojiu_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_DAUNYAOJIU);
        }

        //四归一
        if($is_siguiyi && !empty($this->m_rule->is_siguiyi_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_SIGUIYI);
        }

        //大小五
        if($is_daxiaowu && !empty($this->m_rule->is_daxiaowu_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_DAXIAOWU);
        }

        //孤连六
        if($is_gulianliu && !empty($this->m_rule->is_gulianliu_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GULIANLIU);
        }

        //够扇
        if($is_goushan && !empty($this->m_rule->is_goushan_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GOUSHAN);
        }

        //混一色
        if($is_hunyise && !empty($this->m_rule->is_hunyise_fan))
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_HUNYISE);
        }

		//清一色
		if($is_qingyise && !empty($this->m_rule->is_qingyise_fan))
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QINGYISE);			
		}

		//一条龙
		if($is_yitiaolong && !empty($this->m_rule->is_yitiaolong_fan))
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_YITIAOLONG);			
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
		$hu_type = $this->m_HuCurt[$chair]->method[0];         // 平胡，七对，十三不靠
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
        $za_num = 0;
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
				elseif($this->m_HuCurt[$chair]->method[$i] == self::ATTACHED_HU_ZA)
                {
                    $za_num += 1;
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
        if($za_num == 3)
        {
            $tmp_hu_desc .='三砸 ';
        }
        if($za_num == 4)
        {
            $tmp_hu_desc .='四砸 ';
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
	public function judge_hu_type($chair, &$is_bianzhang, &$is_kanzhang, &$is_kawukui, &$is_zhongfabai, &$is_yibiangao, &$is_menqing, &$is_258jiang, &$is_quemen, &$is_gujiang, &$is_dandiao, &$is_duanyaojiu, &$is_siguiyi, &$is_gulianliu, &$is_daxiaowu, &$is_goushan, &$is_hunyise, &$is_qingyise, &$is_yitiaolong, &$bzz_num)
	{
        $qing_arr  = array();        //一色结果
        $qidui_arr = array();        //七对
        $jiang_arr = array();        //将
        $erwuba_arr = array();       //258将
        $yitiaolong_arr = array();   //一条龙

        $is_yitiaolong = false;      //一条龙
        $is_qingyise = false;        //清一色
        $is_hunyise  = false;        //混一色
        $is_siguiyi = false;         //四归一
        $is_goushan = false;         //够扇
        $is_gulianliu = false;       //孤连六
        $is_duanyaojiu = false;      //断幺九
        $is_menqing = false;         //门清
        $is_quemen = false;          //缺门

        $is_bianzhang = false;       //边张
        $is_kanzhang = false;        //坎张
        $is_kawukui = false;         //卡五魁
        $is_zhongfabai = false;      //中发白
        $is_yibiangao = false;      //一边高

        $is_258jiang = false;        //258将
        $is_dandiao = false;         //单吊
        $is_gujiang = false;         //孤将
        $is_daxiaowu = false;        //大小五

        $is_bukao = false;           //十三不靠
		$bzz_num = 0;                //边钻砸的数量

        /////////////////////////////////////胡牌类型的处理/////////////////////////////////////

		//倒牌
		for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
		{
			$stand_pai_type = $this->_get_card_type($this->m_sStandCard[$chair]->first_card[$i]);
			$stand_pai_key = $this->m_sStandCard[$chair]->first_card[$i] % 16;

            //七对数组中有 0 就不能胡 因为倒牌不可能有七对
			$qidui_arr[] = 0;

            //将倒牌的类型放入到$qing_arr数组中,判断是否为一色
			$qing_arr[] = $stand_pai_type;

            //对组合根进行判断，四归一
			if(ConstConfig::DAO_PAI_TYPE_KE == $this->m_sStandCard[$chair]->type[$i] && $this->m_sPlayer[$chair]->card[$stand_pai_type][$stand_pai_key] > 0
                || (ConstConfig::DAO_PAI_TYPE_ZA == $this->m_sStandCard[$chair]->type[$i] && $this->m_sPlayer[$chair]->card[$stand_pai_type][$stand_pai_key] > 0))
			{
                $is_siguiyi = true;    //手牌、倒牌 组合根，说明存在，满足四归一
			}

			//由于打牌规则导致 倒牌中边钻砸只能出现一种类型
			if(ConstConfig::DAO_PAI_TYPE_BIAN == $this->m_sStandCard[$chair]->type[$i]
				|| ConstConfig::DAO_PAI_TYPE_ZUAN == $this->m_sStandCard[$chair]->type[$i]
				|| ConstConfig::DAO_PAI_TYPE_ZA == $this->m_sStandCard[$chair]->type[$i])
			{
                $bzz_num++;
			}

            if($this->m_bzz_state[$chair] == self::BZZ_ZA)
            {
                //倒牌有杠++
                if (ConstConfig::DAO_PAI_TYPE_MINGGANG == $this->m_sStandCard[$chair]->type[$i]
                    || ConstConfig::DAO_PAI_TYPE_ANGANG == $this->m_sStandCard[$chair]->type[$i]
                    || ConstConfig::DAO_PAI_TYPE_WANGANG == $this->m_sStandCard[$chair]->type[$i]
                    || ConstConfig::DAO_PAI_TYPE_WANGANG_ZA == $this->m_sStandCard[$chair]->type[$i])
                {
                    $bzz_num++;
               }
            }

		}

		//手牌
		for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG; $i++)
		{
		    //此if代表 没有那个牌型，处理下一个牌型
			if(0 == $this->m_sPlayer[$chair]->card[$i][0])
			{
				continue;
			}

            //对tmp_hu_data类型进行判断
			$tmp_hu_data = &ConstConfig::$hu_data;
			if(ConstConfig::PAI_TYPE_FENG == $i)
			{
				$tmp_hu_data = &ConstConfig::$hu_data_feng;
				if(!empty($this->m_rule->is_zhongfabai_shun))
				{
					$tmp_hu_data = &ConstConfig::$hu_data_feng_shun;
				}
			}

			//组装key值
			$key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

			if(!isset($tmp_hu_data[$key]))
			{
				$jiang_arr[] = 32;
				$jiang_arr[] = 32;
				$qidui_arr[] = 0;
                $yitiaolong_arr[] = 0;
				$qing_arr[]  = $i;
                $erwuba_arr[]  = 0;
			}
			else
			{
                /* 1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对
                   128.十三幺  256.一条龙 512.硬将258 1024.有砍子 2048.有顺子 4096*$gen */
			    $hu_list_val = $tmp_hu_data[$key];
                //七对
                $qidui_arr[] = $hu_list_val & 64;
                //判断平胡
                if(($hu_list_val & 1) == 1)
                {
                    $jiang_arr[] = $hu_list_val & 32;
                }
                else
                {
                    //非32牌型设置
                    $jiang_arr[] = 32; $jiang_arr[] = 32;
                }

                //一条龙
                $yitiaolong_arr[] = $hu_list_val & 256;

				$qing_arr[] = $i;

				//判断边钻砸
				if($this->m_HuCurt[$chair]->card)
				{
					$hu_card_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
					$hu_card_index = $this->m_HuCurt[$chair]->card % 16;

                    if($this->m_bzz_state[$chair] == self::BZZ_ZA)
                    {
                        //判断手中的刻子数量
                        for ($j = 1; $j < 10; $j++) {
                            $tmp_card = $i * 16 + $j;
                            if ($this->m_sPlayer[$chair]->card[$i][$j] >= 3) {
                                $this->_list_delete($chair, $tmp_card);
                                $this->_list_delete($chair, $tmp_card);
                                $this->_list_delete($chair, $tmp_card);

                                $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

                                if (isset($tmp_hu_data[$key]) && ($tmp_hu_data[$key] & 1) == 1)
                                {
                                    $bzz_num++;
                                }

                                $this->_list_insert($chair, $tmp_card);
                                $this->_list_insert($chair, $tmp_card);
                                $this->_list_insert($chair, $tmp_card);
                            }
                        }
                    }

					//胡牌那门
					if($hu_card_type == $i)
					{
						$tmp_card_arr = $this->m_sPlayer[$chair]->card[$hu_card_type];
                        if($this->m_bzz_state[$chair] == self::BZZ_ZUAN)
						{
							if((($hu_card_index > 1 && $hu_card_index < 9 && $hu_card_type < ConstConfig::PAI_TYPE_FENG)
									 || ($hu_card_type == ConstConfig::PAI_TYPE_FENG && !empty($this->m_rule->is_zhongfabai_shun) && $hu_card_index == 6))
								 && $tmp_card_arr[$hu_card_index] > 0 && $tmp_card_arr[$hu_card_index + 1] > 0 && $tmp_card_arr[$hu_card_index - 1] > 0)
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

		$bType32 = (32 == array_sum($jiang_arr));               //平胡
		$bQiDui = !array_keys($qidui_arr, 0);        //七对
        $bYiTiaoLong = (256 == array_sum($yitiaolong_arr));     //一条龙


        //四归一2
        for($t=ConstConfig::PAI_TYPE_WAN ; $t<=ConstConfig::PAI_TYPE_FENG; $t++)
        {
            if($bQiDui)
            {
                break;
            }

            if($this->m_sPlayer[$chair]->card[$t][0] == 0 )
            {
                continue;
            }

            if($t != ConstConfig::PAI_TYPE_FENG)
            {
                for($k=1;$k<10;$k++)
                {
                    if($this->m_sPlayer[$chair]->card[$t][$k] == 4)
                    {
                        $is_siguiyi = true;
                        break;
                    }
                }
            }
            else
            {
                for($k=1;$k<8;$k++)
                {
                    if($this->m_sPlayer[$chair]->card[$t][$k] == 4)
                    {
                        $is_siguiyi = true;
                        break;
                    }
                }

            }

        }

        //十三不靠
        if($this->m_rule->is_shisanbukao_fan)
        {
            $this->bukao($chair,$is_bukao);
        }

        /////////////////////////////////////附加番型的处理/////////////////////////////////////

		//清混一色
		$this->_is_yise($qing_arr, $is_qingyise, $is_hunyise);

        //一条龙
        if($this->m_rule->is_yitiaolong_fan && $bYiTiaoLong)
        {
            $is_yitiaolong = true;
        }

        //258将
        if($this->m_rule->is_erwubajiang_fan)
        {
            $this->_is_erwuba($chair, $is_258jiang, $bQiDui);
        }

        //门清
        if(!empty($this->m_rule->is_menqing_fan))
        {
            $is_menqing = $this->_is_menqing($chair);
        }

        //大小五
        if(!empty($this->m_rule->is_daxiaowu_fan))
        {
            $this->_is_daxiaowu($chair, $is_daxiaowu);
        }

        //缺门
        if(!empty($this->m_rule->is_quemen_fan))
        {
            $is_quemen = $this->_is_quemen($chair);
        }

        //够扇
        if(!empty($this->m_rule->is_goushan_fan))
        {
            $this->_is_goushan($chair, $is_goushan);
        }

        //断幺九
        if(!empty($this->m_rule->is_duanyaojiu_fan))
        {
            $this->_is_duanyaojiu($chair, $is_duanyaojiu);
        }

        //孤将
        if(!empty($this->m_rule->is_gujiang_fan))
        {
            $this->_is_gujiang($chair, $is_gujiang, $bQiDui ,$is_bukao);
        }

        //孤连六
        if(!empty($this->m_rule->is_gulianliu_fan))
        {
            $this->_is_gulianliu($chair, $is_gulianliu);
        }

        //边张
        if(!empty($this->m_rule->is_bianzhang_fan) || !empty($this->m_rule->is_kanzhang_fan))
        {
            $this->_is_bianzhang($chair, $is_bianzhang, $is_kanzhang,$bQiDui, $is_bukao);
        }

        //坎张，
        if(!empty($this->m_rule->is_kanzhang_fan))
        {
            $this->_is_kanzhang($chair, $is_kanzhang ,$is_kawukui, $is_bianzhang, $bQiDui, $is_bukao);
        }

        //中发白
        if(!empty($this->m_rule->is_zhongfabai_fan))
        {
            $this->_is_zhongfabai($chair, $is_zhongfabai, $bQiDui ,$is_bukao);
        }

        //一边高
        if(!empty($this->m_rule->is_yibiangao_fan))
        {
            $this->_is_yibiangao($chair, $is_yibiangao, $bQiDui);
        }

        //单吊
        if(!empty($this->m_rule->is_dandiao_fan))
        {
            $this->_is_dandiao($chair, $is_dandiao,$is_kanzhang, $bQiDui, $is_bianzhang);
        }

		/////////////////////////////////////返回胡牌类型的基本牌型的处理/////////////////////////////////////

        //非平胡且非七对 错误！
        if(!$bType32 && !$bQiDui && !$is_bukao)
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID ;
        }

        //非边钻砸 错误！
        if($this->m_bzz_state[$chair] != self::BZZ_NULL && $bzz_num < 3)
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID ;
        }

        //判断七对
		if($bQiDui && $this->m_rule->is_qidui_fan)
		{

			return self::HU_TYPE_QIDUI ;				
		}

		//判断十三不靠
        if($is_bukao && $this->m_rule->is_shisanbukao_fan)
        {
            return self::HU_TYPE_SHISANBUKAO;
        }

        //满足32牌型，胡的为平胡
        if($bType32)
        {
            return self::HU_TYPE_PINGHU;
        }

        //啥都不是 错误！
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
		--$this->m_nNumTableCards[$this->m_sOutedCard->chair];
		if($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
		{
			unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
		}
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
        $this->GangNum += 1;

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
		if($za && $this->m_bzz_state[$chair] == self::BZZ_ZA)
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

		$this->m_sStandCard[$chair]->first_card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
		$this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
		$this->m_sStandCard[$chair]->num ++;
		$stand_count_after = $this->m_sStandCard[$chair]->num;

		$this->m_bHaveGang = true;  //for 杠上花

		$nGangScore = 0;
		$nGangPao = 0;

        $nGangScore =self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;
        $dian_gang = $this->m_sOutedCard->chair;
        $this->m_wGangScore[$dian_gang][$dian_gang] -= $nGangScore;
        $this->m_wGangScore[$chair][$chair] += $nGangScore;
        $this->m_wGangScore[$chair][$dian_gang] += $nGangScore;

        $this->_set_record_game(ConstConfig::RECORD_ZHIGANG, $chair, $temp_card, $this->m_sOutedCard->chair);

        $this->m_sGangPao->init_data(true, $temp_card, $chair,ConstConfig::DAO_PAI_TYPE_MINGGANG, $nGangPao);
        $this->m_wTotalScore[$chair]->n_zhigang_wangang += 1;
        $this->GangNum += 1;

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
		if($za && $this->m_bzz_state[$chair] == self::BZZ_ZA)
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
    public function HandleChooseWanGang($chair, $gang_card)
    {
        $temp_card = $gang_card;
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

        //扣牌
        if(!empty($this->m_rule->is_kou_card))
        {
            $tmp_times = 0;
            $kou_num = 12;
            if(!empty($this->m_rule->is_kou13))
            {
                $kou_num = 13;
            }
            for ($i=0; $i < $kou_num; $i++)
            {
                if($this->m_sPlayer[$chair]->kou_card[$i][0] == $temp_card && $this->m_sPlayer[$chair]->kou_card[$i][1] == 1)
                {
                    $this->m_sPlayer[$chair]->kou_card[$i][1] = 3;
                    $tmp_times++;
                }
                if($tmp_times >= 1)
                {
                    break;
                }
            }
            $this->m_sPlayer[$chair]->kou_card_display = $this->_set_kou_arr($chair, true);
        }

        // 设置倒牌
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i ++)
        {
            //砸-弯杠
            if($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                && $this->m_sStandCard[$chair]->card[$i] == $temp_card)
            {
                $this->m_sStandCard[$chair]->type[$i] = ConstConfig::DAO_PAI_TYPE_WANGANG_ZA;
                $za_num = 0;
                for ($j = 0; $j < $this->m_sStandCard[$chair]->num; $j++)
                {
                    if($this->m_sStandCard[$chair]->type[$j] == ConstConfig::DAO_PAI_TYPE_ZA)
                    {
                        $za_num ++;
                    }
                }

                if( $za_num == 0)
                {
                    $this->m_bzz_state[$chair] = self::BZZ_NULL;
                }
                break;
            }

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

				// 设置倒牌
				for ($i = 0; $i < $this->m_sStandCard[$this->m_sOutedCard->chair]->num; $i ++)
				{
					if ($this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
					&& $this->m_sStandCard[$this->m_sOutedCard->chair]->card[$i] == $this->m_sQiangGang->card)
					{
                        $this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] = ConstConfig::DAO_PAI_TYPE_KE;
						break;
					}

                    if ($this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA
                        && $this->m_sStandCard[$this->m_sOutedCard->chair]->card[$i] == $this->m_sQiangGang->card)
                    {
                        $this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] = ConstConfig::DAO_PAI_TYPE_ZA;
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
						if (($this->m_sStandCard[$this->m_sQiangGang->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG || $this->m_sStandCard[$this->m_sQiangGang->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
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
                for ($i = 0; $i < $this->m_sStandCard[$this->m_sQiangGang->chair]->num; $i ++)
                {
                   if ($this->m_sStandCard[$this->m_sQiangGang->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                      && $this->m_sStandCard[$this->m_sQiangGang->chair]->card[$i] == $this->m_sQiangGang->card)
                   {
                      $this->m_sGangPao->init_data(true, $this->m_sQiangGang->card, $this->m_sQiangGang->chair, ConstConfig::DAO_PAI_TYPE_WANGANG, $nGangPao);
                   
                      $this->_set_record_game(ConstConfig::RECORD_ZHUANGANG, $this->m_sQiangGang->chair, $this->m_sQiangGang->card, $this->m_sQiangGang->chair);
                   }
                   
                   if ($this->m_sStandCard[$this->m_sQiangGang->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA
                      && $this->m_sStandCard[$this->m_sQiangGang->chair]->card[$i] == $this->m_sQiangGang->card)
                   {
                      $this->m_sGangPao->init_data(true, $this->m_sQiangGang->card, $this->m_sQiangGang->chair, ConstConfig::DAO_PAI_TYPE_WANGANG_ZA, $nGangPao);
                   
                      $this->_set_record_game(ConstConfig::RECORD_WANGANG_ZA, $this->m_sQiangGang->chair, $this->m_sQiangGang->card, $this->m_sQiangGang->chair);
                   }
                }
                // $this->m_sGangPao->init_data(true, $this->m_sQiangGang->card, $this->m_sQiangGang->chair, ConstConfig::DAO_PAI_TYPE_WANGANG, $nGangPao);

                // $this->_set_record_game(ConstConfig::RECORD_ZHUANGANG, $this->m_sQiangGang->chair, $this->m_sQiangGang->card, $this->m_sQiangGang->chair);

				$this->m_wTotalScore[$this->m_sQiangGang->chair]->n_zhigang_wangang += 1;

                $this->GangNum += 1;

                //四杠荒庄
                if ($this->GangNum >=4)
                {
                    $this->m_nEndReason = ConstConfig::END_REASON_NOCARD;
                    $this->HandleSetOver();
                    return true;
                }

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

        //四杠荒庄
        if ($this->GangNum >=4)
        {
            $this->m_nEndReason = ConstConfig::END_REASON_NOCARD;
            $this->HandleSetOver();
            return true;
        }

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
			else if($card_index == 7 && $card_type == ConstConfig::PAI_TYPE_FENG)
			{
                $tmp_first_card = $this->_get_card_index($card_type, $card_index-2);
                $this->_list_delete($chair, $this->_get_card_index($card_type, $card_index-1));
                $this->_list_delete($chair, $tmp_first_card);
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

		//自摸
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

				$PerWinScore = ($PerWinScore == 0) ? 1 : $PerWinScore;
				$wWinScore = 2 * ConstConfig::SCORE_BASE * ($PerWinScore + $banker_fan);

				$wWinPaoZi = 2 * ($this->m_own_paozi[$chair]->num + $this->m_own_paozi[$i]->num);
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

					$PerWinScore = ($PerWinScore == 0) ? 1 : $PerWinScore;
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
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA
            )
            {
                $no_bzz_num++;
            }
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $bzz_num++;
            }
        }
    }

    //十三不靠
    /*public function bukao($chair, &$is_bukao)
    {
        $is_bukao = false;

        //不能有倒牌
        if ($this->m_sStandCard[$chair]->num>0)
        {
            return;
        }

        for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG; $i++)
        {
            //风-
            if (ConstConfig::PAI_TYPE_FENG == $i)
            {
                //风牌的个数小于5张的时候，
                if ($this->m_sPlayer[$chair]->card[$i][0] < 5)
                {

                    return;
                }
                
                for ($k = 1; $k < 8; $k++)
                {
                    //每一张牌大于1
                    if ($this->m_sPlayer[$chair]->card[$i][$k] > 1)
                    {
                        return;
                    }
                }
            }

            if($i == ConstConfig::PAI_TYPE_FENG)
            {
                continue;
            }

            //非风- 一个花色牌数大于3的时候，或者这个花色的数量等于0的时候
            if (($this->m_sPlayer[$chair]->card[$i][0] > 3 || $this->m_sPlayer[$chair]->card[$i][0] == 0)
                && $i != ConstConfig::PAI_TYPE_FENG)
            {
                return;
            }

            for ($j = 1; $j <= 3; $j++)
            {
                //147 258 369 组合中某一张牌打的个数大于1都不行
                if (!($this->m_sPlayer[$chair]->card[$i][1] == 1 && $this->m_sPlayer[$chair]->card[$i][4] == 1 && $this->m_sPlayer[$chair]->card[$i][7] == 1)
                && !($this->m_sPlayer[$chair]->card[$i][2] == 1 && $this->m_sPlayer[$chair]->card[$i][5] == 1 && $this->m_sPlayer[$chair]->card[$i][8] == 1)
                && !($this->m_sPlayer[$chair]->card[$i][3] == 1 && $this->m_sPlayer[$chair]->card[$i][6] == 1 && $this->m_sPlayer[$chair]->card[$i][9] == 1)
                )
                {
                    return;
                }
            }
        }
        $is_bukao = true;
    }*/

    public function bukao($chair, &$is_bukao)
    {
        $is_bukao = false;
        $arr=[];
        //不能有倒牌
        if ($this->m_sStandCard[$chair]->num>0)
        {
            return;
        }
        for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG; $i++)
        {
            //风-
            if (ConstConfig::PAI_TYPE_FENG == $i)
            {
                //风牌的个数小于5张的时候，
                if ($this->m_sPlayer[$chair]->card[$i][0] < 5)
                {

                    return;
                }
                
                for ($k = 1; $k < 8; $k++)
                {
                    //每一张牌大于1
                    if ($this->m_sPlayer[$chair]->card[$i][$k] > 1) {
                        return;
                    }
                }
            }

            if($i == ConstConfig::PAI_TYPE_FENG)
            {
                continue;
            }

            //非风- 一个花色牌数大于3的时候，或者这个花色的数量等于0的时候
            if (($this->m_sPlayer[$chair]->card[$i][0] > 3 || $this->m_sPlayer[$chair]->card[$i][0] == 0)
                && $i != ConstConfig::PAI_TYPE_FENG)
            {
                return;
            }

            //5张风牌
			$break = false;
            for ($j = 1; $j <= 3; $j++)
            {
                //147 258 369 组合中某一张牌打的个数大于1都不行
                if ($this->m_sPlayer[$chair]->card[$i][$j] == 1)
                {
 					$arr[] = $j;
 					$break = true;
                }
                if ($this->m_sPlayer[$chair]->card[$i][$j+3] == 1)
                {
 					$arr[] = $j+3;
 					$break = true;
                }
                if ($this->m_sPlayer[$chair]->card[$i][$j+6] == 1)
                {
 					$arr[] = $j+6;
 					$break = true;
                }
                if($break)
                {
                	break;
                }
            }

        }
        if(!empty($arr))
        {
            $num = count($arr);
            if(($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_FENG][0] == 5 && $num == 9)
                ||($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_FENG][0] == 6 && $num == 8)
                ||($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_FENG][0] == 7 && $num == 7)
            )
            {
                if ($num != count(array_unique($arr)))
                {   
                    return ;
                }
                else
                {
                    $is_bukao = true;
                }
            }
            else
            {
                return;
            }

        }
        else
        {
            return;
        }

    }

    //判断清一色，混一色 结果，通过引用返回
    public function _is_yise($qing_arr, &$is_qingyise, &$is_hunyise)
    {
        //清一色
        if(1 == count(array_unique($qing_arr)))
        {
            if(!empty($this->m_rule->is_qingyise_fan) && ConstConfig::PAI_TYPE_FENG != $qing_arr[0])
            {
                $is_qingyise = true;
            }
        }

        //混一色
        if(2 == count(array_unique($qing_arr)) && in_array(ConstConfig::PAI_TYPE_FENG, $qing_arr))
        {
            if(!empty($this->m_rule->is_hunyise_fan))
            {
                $is_hunyise = true;
            }
        }
    }

    //258将
    public function _is_erwuba($chair, &$erwuba, $bQiDui)
    {
        $erwuba = false;

        if($bQiDui)
        {
            return;
        }

        for($i=ConstConfig::PAI_TYPE_WAN; $i<ConstConfig::PAI_TYPE_FENG; $i++)
        {
            $tmp_hu_data = &ConstConfig::$hu_data;
            if(ConstConfig::PAI_TYPE_FENG == $i)
            {
                $tmp_hu_data = &ConstConfig::$hu_data_feng;
                if(!empty($this->m_rule->is_zhongfabai_shun))
                {
                    $tmp_hu_data = &ConstConfig::$hu_data_feng_shun;
                }
            }

            if ($this->m_sPlayer[$chair]->card[$i][2] > 1)
            {
                $card = $i * 16 + 2;
                $this->_list_delete($chair,$card);
                $this->_list_delete($chair,$card);

                $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

                $this->_list_insert($chair,$card);
                $this->_list_insert($chair,$card);

                if(isset($tmp_hu_data[$tmp_key]) && (($tmp_hu_data[$tmp_key] & 1) == 1))
                {
                    $erwuba = true;
                    break;
                }
            }elseif($this->m_sPlayer[$chair]->card[$i][5] > 1)
            {
                $card = $i * 16 + 5;
                $this->_list_delete($chair,$card);
                $this->_list_delete($chair,$card);

                $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

                $this->_list_insert($chair,$card);
                $this->_list_insert($chair,$card);

                if(isset($tmp_hu_data[$tmp_key]) && (($tmp_hu_data[$tmp_key] & 1) == 1))
                {
                    $erwuba = true;
                    break;
                }
            }elseif($this->m_sPlayer[$chair]->card[$i][8] > 1)
            {
                $card = $i * 16 + 8;
                $this->_list_delete($chair,$card);
                $this->_list_delete($chair,$card);

                $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

                $this->_list_insert($chair,$card);
                $this->_list_insert($chair,$card);

                if(isset($tmp_hu_data[$tmp_key]) && (($tmp_hu_data[$tmp_key] & 1) == 1))
                {
                    $erwuba = true;
                    break;
                }
            }
        }
    }

    //够扇
    public function _is_goushan($chair, &$is_goushan)
    {
        $is_goushan = false;
        //倒牌添加进手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
                )
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
            //砸刻杠
            if($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
            }
        }

        //判断是否够扇
        for($i=ConstConfig::PAI_TYPE_WAN; $i<ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if($this->m_sPlayer[$chair]->card[$i][0] >= 10)
            {
                $is_goushan = true;
                break;
            }
        }

        //还原手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
            //砸刻杠
            if($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
            }
        }
    }

    //一边高
    public function _is_yibiangao($chair, &$is_yibiangao, $bQiDui)
    {
        $is_yibiangao = false;
        if($bQiDui)
        {
            return;
        }

        //倒牌添加进手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
        }

        for($i=ConstConfig::PAI_TYPE_WAN; $i<ConstConfig::PAI_TYPE_FENG; $i++)
        {
            $tmp_hu_data = &ConstConfig::$hu_data;
            if(ConstConfig::PAI_TYPE_FENG == $i)
            {
                $tmp_hu_data = &ConstConfig::$hu_data_feng;
                if(!empty($this->m_rule->is_zhongfabai_shun))
                {
                    $tmp_hu_data = &ConstConfig::$hu_data_feng_shun;
                }
            }

            if($this->m_sPlayer[$chair]->card[$i][0] < 6)
            {
                continue;
            }

            for($j=1; $j<8; $j++)
            {
                $card = $i * 16 + $j;
                if($this->m_sPlayer[$chair]->card[$i][$j] > 1
                    && $this->m_sPlayer[$chair]->card[$i][$j+1] > 1
                    && $this->m_sPlayer[$chair]->card[$i][$j+2] > 1)
                {
                    $this->_list_delete($chair,$card);
                    $this->_list_delete($chair,$card);
                    $this->_list_delete($chair,$card+1);
                    $this->_list_delete($chair,$card+1);
                    $this->_list_delete($chair,$card+2);
                    $this->_list_delete($chair,$card+2);

                    $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

                    $this->_list_insert($chair,$card);
                    $this->_list_insert($chair,$card);
                    $this->_list_insert($chair,$card+1);
                    $this->_list_insert($chair,$card+1);
                    $this->_list_insert($chair,$card+2);
                    $this->_list_insert($chair,$card+2);

                    if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
                    {
                        $is_yibiangao = true;
                        break 2;
                    }
                }
                else
                {
                    continue;
                }
            }
        }

        //还原手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
        }
    }

    //孤连六
    public function _is_gulianliu($chair, &$is_gulianliu)
    {
        $is_gulianliu = false;
        //倒牌添加进手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //插入三张
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
                )
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
        }

        //判断能否孤连六
        for($i=ConstConfig::PAI_TYPE_WAN; $i<ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if($this->m_sPlayer[$chair]->card[$i][0] == 6)
            {
                for($j=1; $j<5; $j++)
                {
                    if($this->m_sPlayer[$chair]->card[$i][$j] == 1
                    && $this->m_sPlayer[$chair]->card[$i][$j+1] == 1
                        && $this->m_sPlayer[$chair]->card[$i][$j+2] == 1
                        && $this->m_sPlayer[$chair]->card[$i][$j+3] == 1
                        && $this->m_sPlayer[$chair]->card[$i][$j+4] == 1
                        && $this->m_sPlayer[$chair]->card[$i][$j+5] == 1)
                    {
                        $is_gulianliu = true;
                        break;
                    }
                }
            }

        }

        //还原手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //还原三张
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
               )
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
        }
    }

    //断幺九
    public function _is_duanyaojiu($chair, &$is_duanyaojiu)
    {
        $is_duanyaojiu = true;
        //倒牌添加进手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
            //砸刻杠
            if($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
            }
        }

        //判断是否断幺九
        for($i=ConstConfig::PAI_TYPE_WAN; $i<=ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if($i == ConstConfig::PAI_TYPE_FENG && $this->m_sPlayer[$chair]->card[$i][0] > 0)
            {
                $is_duanyaojiu = false;
            }

            if($this->m_sPlayer[$chair]->card[$i][1] != 0 || $this->m_sPlayer[$chair]->card[$i][9] != 0)
            {
                $is_duanyaojiu = false;
            }
        }

        //还原手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
            //砸刻
            if($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
            }
        }
    }

    //门清
    public function _is_menqing($chair)
    {
        $return = true;
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i ++)
        {
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_SHUN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
            {
                $return = false;
                break;
            }
        }
        return $return;
    }

    //判断缺门
    public function _is_quemen($chair, $fanhun_type= 0 , $fanhun_num = 0)
    {
        $quemen_arr = array();

        //倒牌添加进手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
            //砸刻杠
            if($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
            }
        }

        //缺门
        for($i=ConstConfig::PAI_TYPE_WAN; $i<ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if($this->m_sPlayer[$chair]->card[$i][0] > 0 )
            {
                $quemen_arr[$i] = 1;
            }
        }

        //还原手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
            //砸刻
            if($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
            }
        }

        if(array_sum($quemen_arr) <= 2 )
        {
            return $is_quemen = true;
        }
    }

    //边张
    public function _is_bianzhang($chair, &$is_bianzhang, $is_kanzhang ,$bQiDui, $is_bukao)
    {
        $is_bianzhang = false;
        $tmp_card = $this->m_HuCurt[$chair]->card % 16;
        $tmp_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);

        $tmp_hu_data = &ConstConfig::$hu_data;
        if(ConstConfig::PAI_TYPE_FENG == $tmp_type)
        {
            $tmp_hu_data = &ConstConfig::$hu_data_feng;
            if(!empty($this->m_rule->is_zhongfabai_shun))
            {
                $tmp_hu_data = &ConstConfig::$hu_data_feng_shun;
            }
        }

        if($bQiDui)
        {
            return;
        }

        if($is_kanzhang)
        {
            return;
        }


        if($tmp_card == 3 || $tmp_card == 7 )
        {
            if ($tmp_card == 3 && $this->m_sPlayer[$chair]->card[$tmp_type][2] != 0 &&
                $this->m_sPlayer[$chair]->card[$tmp_type][1] != 0)
            {
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card-1);
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card-2);
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card);

                $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$tmp_type], 1)));
                if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
                {
                    $is_bianzhang = true;
                }

                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card-1);
                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card-2);

            }elseif($tmp_card == 7 && $tmp_type == ConstConfig::PAI_TYPE_FENG && $this->m_sPlayer[$chair]->card[$tmp_type][6] != 0 && $this->m_sPlayer[$chair]->card[$tmp_type][5] != 0)
            {
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card-1);
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card-2);

                $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$tmp_type], 1)));

                if((isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1) || $is_bukao)
                {
                    $is_bianzhang = true;
                }

                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card-1);
                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card-2);

            }elseif($tmp_card == 7 && $tmp_type != ConstConfig::PAI_TYPE_FENG && $this->m_sPlayer[$chair]->card[$tmp_type][8] != 0 && $this->m_sPlayer[$chair]->card[$tmp_type][9] != 0)
            {
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card+1);
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card+2);

                $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$tmp_type], 1)));

                if((isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1))
                {
                    $is_bianzhang = true;
                }

                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card+1);
                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card+2);
            }
        }
    }

    //坎张
    public function _is_kanzhang($chair, &$is_kanzhang, &$is_kawukui, $is_bianzhang ,$bQiDui, $is_bukao)
    {
        $is_kanzhang = false;
        $is_kawukui  = false;

        $tmp_card = $this->m_HuCurt[$chair]->card % 16;
        $tmp_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);

        $tmp_hu_data = &ConstConfig::$hu_data;
        if(ConstConfig::PAI_TYPE_FENG == $tmp_type)
        {
            $tmp_hu_data = &ConstConfig::$hu_data_feng;
            if(!empty($this->m_rule->is_zhongfabai_shun))
            {
                $tmp_hu_data = &ConstConfig::$hu_data_feng_shun;
            }
        }

        if($bQiDui)
        {
            return;
        }

        if($is_bianzhang)
        {
            return;
        }

        if ($tmp_card != 1 && $tmp_card != 9)
        {
            //发财
            if($tmp_card == 6 && $tmp_type == ConstConfig::PAI_TYPE_FENG && $this->m_sPlayer[$chair]->card[$tmp_type][7] != 0 && $this->m_sPlayer[$chair]->card[$tmp_type][5] != 0)
            {
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card-1);
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card+1);

                $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$tmp_type], 1)));

                if((isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1) || $is_bukao)
                {
                    $is_kanzhang = true;
                }

                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card-1);
                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card+1);
            }

            //普通
            if ($this->m_sPlayer[$chair]->card[$tmp_type][$tmp_card-1] != 0 && $this->m_sPlayer[$chair]->card[$tmp_type][$tmp_card+1] != 0)
            {
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card-1);
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
                $this->_list_delete($chair,$this->m_HuCurt[$chair]->card+1);

                $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$tmp_type], 1)));
                if((isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1))
                {
                    if($tmp_card == 5 && $tmp_type == ConstConfig::PAI_TYPE_WAN)
                    {
                        $is_kawukui  = true;
                    }else{
                        $is_kanzhang = true;
                    }

                }

                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card-1);
                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
                $this->_list_insert($chair,$this->m_HuCurt[$chair]->card+1);
            }
        }
    }

    //中发白
    public function _is_zhongfabai($chair, &$is_zhongfabai, $bQiDui, $is_bukao)
    {
        $is_zhongfabai = false;
        $tmp_type = ConstConfig::PAI_TYPE_FENG;
        $tmp_card = $tmp_type * 16;
        $tmp_hu_data = &ConstConfig::$hu_data;
        if(ConstConfig::PAI_TYPE_FENG == $tmp_type)
        {
            $tmp_hu_data = &ConstConfig::$hu_data_feng;
            if(!empty($this->m_rule->is_zhongfabai_shun))
            {
                $tmp_hu_data = &ConstConfig::$hu_data_feng_shun;
            }
        }

        if($bQiDui)
        {
            return;
        }

        if($is_bukao)
        {
            return;
        }

        //倒牌添加进手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
        }

        if($this->m_sPlayer[$chair]->card[$tmp_type][5] != 0 && $this->m_sPlayer[$chair]->card[$tmp_type][6] != 0 && $this->m_sPlayer[$chair]->card[$tmp_type][7] != 0)
        {
            $this->_list_delete($chair,$tmp_card+5);
            $this->_list_delete($chair,$tmp_card+6);
            $this->_list_delete($chair,$tmp_card+7);

            $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$tmp_type], 1)));

            if((isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1))
            {
                $is_zhongfabai = true;
            }

            $this->_list_insert($chair,$tmp_card+5);
            $this->_list_insert($chair,$tmp_card+6);
            $this->_list_insert($chair,$tmp_card+7);
        }

        //还原手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
        }
    }

    //单吊
    public function _is_dandiao($chair,&$is_dandiao, $is_kanzhang, $bQiDui,$is_bianzhang)
    {
        $is_dandiao = false;

        if($bQiDui)
        {
            return;
        }
        //坎张成立就不算单吊
        if($is_kanzhang)
        {
            return;
        }

        //边张成立就不算单吊
        if($is_bianzhang)
        {
            return;
        }

        $card = $this->m_HuCurt[$chair]->card % 16;
        $card_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
        $tmp_hu_data = &ConstConfig::$hu_data;
        if(ConstConfig::PAI_TYPE_FENG == $card_type)
        {
            $tmp_hu_data = &ConstConfig::$hu_data_feng;
            if(!empty($this->m_rule->is_zhongfabai_shun))
            {
                $tmp_hu_data = &ConstConfig::$hu_data_feng_shun;
            }
        }

        if($this->m_sPlayer[$chair]->card[$card_type][$card] > 1)
        {
            $this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
            $this->_list_delete($chair,$this->m_HuCurt[$chair]->card);

            $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$card_type], 1)));

            $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
            $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);

            if(isset($tmp_hu_data[$key])
                && ((($tmp_hu_data[$key] & 1) == 1 && $this->_is_danting($chair)))
            )
            {
                    $is_dandiao = true;
            }
        }
    }

	//孤将
    public function _is_gujiang($chair, &$is_gujiang, $bQiDui,$is_bukao)
    {
        $is_gujiang = false;

        if($is_bukao)
        {
            return;
        }

        if($bQiDui)
        {
            return;
        }

        //倒牌添加进手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
            //砸刻杠
            if($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
            }
        }

        for($i=ConstConfig::PAI_TYPE_WAN ; $i<ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if($this->m_sPlayer[$chair]->card[$i][0] == 2)
            {
                $is_gujiang = true;
            }
        }

        //还原手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
            //砸刻杠
            if($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
            }
        }
    }

    //大小五
    public function _is_daxiaowu($chair, &$is_daxiaowu)
    {
        $is_daxiaowu = false;
        $xiao_arr = array(0,0,0);   //三个花色是否都满足
        $da_arr   = array(0,0,0);   //三个花色是否都满足

        //倒牌添加进手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_insert($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
            //砸刻杠
            if($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
            {
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_insert($chair, $this->m_sStandCard[$chair]->first_card[$i]);
            }
        }

        //判断是不是大小五
        for ($i = ConstConfig::PAI_TYPE_WAN; $i < ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if($this->m_sPlayer[$chair]->card[0][0] == 0
                && $this->m_sPlayer[$chair]->card[1][0] == 0
                && $this->m_sPlayer[$chair]->card[2][0] == 0)
            {
                $xiao_arr[$i] = 0;
                $da_arr[$i]   = 0;
                continue;
            }

            if ($this->m_sPlayer[$chair]->card[$i][0] == 0)
            {
                $xiao_arr[$i] = 1;
                $da_arr[$i] = 1;
                continue;
            }

            if ($this->m_sPlayer[$chair]->card[$i][0] > 0)
            {
                if (($this->m_sPlayer[$chair]->card[$i][1] == 0
                    && $this->m_sPlayer[$chair]->card[$i][2] == 0
                    && $this->m_sPlayer[$chair]->card[$i][3] == 0
                    && $this->m_sPlayer[$chair]->card[$i][4] == 0))
                {
                    $da_arr[$i] = 1;
                }

                if ($this->m_sPlayer[$chair]->card[$i][6] == 0
                    && $this->m_sPlayer[$chair]->card[$i][7] == 0
                    && $this->m_sPlayer[$chair]->card[$i][8] == 0
                    && $this->m_sPlayer[$chair]->card[$i][9] == 0)
                {
                    $xiao_arr[$i] = 1;
                }
            }
        }

        if ( array_sum($da_arr) == 3 || array_sum($xiao_arr) == 3)
        {
            $is_daxiaowu = true;
        }

        //还原手牌
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            //边钻
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_BIAN
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZUAN
            )
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 1));
                $this->_list_delete($chair, ($this->m_sStandCard[$chair]->first_card[$i] + 2));
            }
            //砸刻杠
            if($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ZA
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_ANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                || $this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG_ZA)
            {
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
                $this->_list_delete($chair, $this->m_sStandCard[$chair]->first_card[$i]);
            }
        }
    }

    //单听
    public function _is_danting($chair)
    {
        $return = true;
        $card_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
        $tmp_hu_data = &ConstConfig::$hu_data;
        $replace_card = array(1,2,3,4,5,6,7,8,9);

        if(ConstConfig::PAI_TYPE_FENG == $card_type)
        {
            $tmp_hu_data = &ConstConfig::$hu_data_feng;
            if(!empty($this->m_rule->is_zhongfabai_shun))
            {
                $tmp_hu_data = &ConstConfig::$hu_data_feng_shun;
            }
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

}
