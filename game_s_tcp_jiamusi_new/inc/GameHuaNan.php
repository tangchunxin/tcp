<?php
/**
 * @author xuqiang76@163.com
 * @final 20171031
 */

namespace gf\inc;

use gf\inc\ConstConfig;
use gf\conf\Config;
use gf\inc\Room;
use gf\inc\BaseFunction;
use gf\inc\Game_cmd;
use gf\inc\BaseGame;

class GameHuaNan extends BaseGame
{
	const GAME_TYPE = 161;

    //－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
    const HU_TYPE_BIAN = 21;                    //边
    const HU_TYPE_DUIDAO = 22;                  //对倒
    const HU_TYPE_DANDIAO = 23;                 //单吊
    const HU_TYPE_JIAHU = 24;                   //夹胡
    const HU_TYPE_HU19 = 25;                    //胡19
    const HU_TYPE_PIAOHU = 26;                  //飘胡
    const HU_TYPE_SHOUBAYI = 27;                //手把一
    const HU_TYPE_PIAOHUSHOUBAYI = 28;          //飘胡手把一
    const HU_TYPE_TEHU = 29;                    //特胡
    const HU_TYPE_QIXIAODUI = 30;               //七小对
    const HU_TYPE_HAOHUAQIXIAODUI = 31;         //豪华七小对

    const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   //错误

    //－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
    const ATTACHED_HU_ZIMOFAN = 61;             //自摸   默认
    const ATTACHED_HU_GANGKAI = 62;             //杠开
    const ATTACHED_HU_GANGPAO = 63;             //杠炮(流泪)
    const ATTACHED_HU_QIANGGANG =64;            //抢杠胡

    const ATTACHED_HU_SANJIAQING = 65;          //三家清

    //－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
    const M_ZHIGANG_SCORE = 2;                  //直杠2分
    const M_ANGANG_SCORE = 4;                   //暗杠  4分
    const M_WANGANG_SCORE = 2;                  //弯杠  2分

    public static $hu_type_arr = array(
        self::HU_TYPE_BIAN=>[self::HU_TYPE_BIAN, 1, '边']
    ,self::HU_TYPE_DUIDAO=>[self::HU_TYPE_DUIDAO, 1, '对倒']
    ,self::HU_TYPE_DANDIAO=>[self::HU_TYPE_DANDIAO, 2, '单吊']
    ,self::HU_TYPE_JIAHU=>[self::HU_TYPE_JIAHU, 2, '夹胡']
    ,self::HU_TYPE_HU19=>[self::HU_TYPE_HU19, 2, '胡19']
    ,self::HU_TYPE_PIAOHU=>[self::HU_TYPE_PIAOHU, 4, '飘胡']
    ,self::HU_TYPE_SHOUBAYI=>[self::HU_TYPE_SHOUBAYI, 4, '手把一']
    ,self::HU_TYPE_PIAOHUSHOUBAYI=>[self::HU_TYPE_PIAOHUSHOUBAYI, 8, '飘胡手把一']
    ,self::HU_TYPE_TEHU=>[self::HU_TYPE_TEHU, 8, '特胡']
    ,self::HU_TYPE_QIXIAODUI=>[self::HU_TYPE_QIXIAODUI, 8, '七小对']
    ,self::HU_TYPE_HAOHUAQIXIAODUI=>[self::HU_TYPE_HAOHUAQIXIAODUI, 16, '豪华七小对']

    );

    public static $attached_hu_arr = array(
        self::ATTACHED_HU_ZIMOFAN=>[self::ATTACHED_HU_ZIMOFAN, 2,'']//自摸
    ,self::ATTACHED_HU_GANGKAI=>[self::ATTACHED_HU_GANGKAI, 2, '杠上花']
    ,self::ATTACHED_HU_GANGPAO=>[self::ATTACHED_HU_GANGPAO, 2, '流泪']//抢杠
    ,self::ATTACHED_HU_SANJIAQING=>[self::ATTACHED_HU_SANJIAQING, 4, '三家清']
    ,self::ATTACHED_HU_QIANGGANG=>[self::ATTACHED_HU_QIANGGANG,1,'抢杠']
    );

    public $m_bKaiMen = array();                //开门数组
    public $m_she_te;	                        //设特结构
    public $m_double_card = array();            //第一次手里只剩两张牌标记位


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

        $this->m_she_te = new She_te();         //设特结构
        for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
        {
            $this->m_bKaiMen[$i] = false;
            $this->m_double_card[$i] = false;
        }

	}

	public function _open_room_sub($params)
	{
        $this->m_rule = new RuleHuaNan();

		$params['rule']['min_fan'] = !isset($params['rule']['min_fan']) ? 0 : $params['rule']['min_fan'];
		$params['rule']['top_fan'] = !isset($params['rule']['top_fan']) ? 0 : $params['rule']['top_fan'];
		//$params['rule']['is_feng'] = !isset($params['rule']['is_feng']) ? 1 : $params['rule']['is_feng'];
		$params['rule']['is_yipao_duoxiang'] = !isset($params['rule']['is_yipao_duoxiang']) ? 1 : $params['rule']['is_yipao_duoxiang'];
		//$params['rule']['is_chipai'] = !isset($params['rule']['is_chipai']) ? 0 : $params['rule']['is_chipai'];
		//$params['rule']['is_genzhuang'] = !isset($params['rule']['is_genzhuang']) ? 0 : $params['rule']['is_genzhuang'];
		//$params['rule']['is_paozi'] = !isset($params['rule']['is_paozi']) ? 0 : $params['rule']['is_paozi'];
		//$params['rule']['is_zhuang_fan'] = !isset($params['rule']['is_zhuang_fan']) ? 1 : $params['rule']['is_zhuang_fan'];
		$params['rule']['is_qingyise_fan'] = !isset($params['rule']['is_qingyise_fan']) ? 1 : $params['rule']['is_qingyise_fan'];
		//$params['rule']['is_ziyise_fan'] = !isset($params['rule']['is_ziyise_fan']) ? 1 : $params['rule']['is_ziyise_fan'];
		//$params['rule']['is_yitiaolong_fan'] = !isset($params['rule']['is_yitiaolong_fan']) ? 1 : $params['rule']['is_yitiaolong_fan'];
		$params['rule']['is_ganghua_fan'] = !isset($params['rule']['is_ganghua_fan']) ? 1 : $params['rule']['is_ganghua_fan'];
		$params['rule']['is_qidui_fan'] = !isset($params['rule']['is_qidui_fan']) ? 1 : $params['rule']['is_qidui_fan'];
		$params['rule']['is_pengpenghu_fan'] = !isset($params['rule']['is_pengpenghu_fan']) ? 1 : $params['rule']['is_pengpenghu_fan'];
		//$params['rule']['is_wangang_1_lose'] = !isset($params['rule']['is_wangang_1_lose']) ? 0 : $params['rule']['is_wangang_1_lose'];
		//$params['rule']['is_dianpao_bao'] = !isset($params['rule']['is_dianpao_bao']) ? 0 : $params['rule']['is_dianpao_bao'];
		//$params['rule']['is_wukui'] = !isset($params['rule']['is_wukui']) ? 1 : $params['rule']['is_wukui'];
		//$params['rule']['is_diaowuwan'] = !isset($params['rule']['is_diaowuwan']) ? 1 : $params['rule']['is_diaowuwan'];
		//$params['rule']['is_zhongfabai_shun'] = !isset($params['rule']['is_zhongfabai_shun']) ? 0 : $params['rule']['is_zhongfabai_shun'];
		//$params['rule']['is_bian_zuan'] = !isset($params['rule']['is_bian_zuan']) ? 1 : $params['rule']['is_bian_zuan'];
		//$params['rule']['is_za'] = !isset($params['rule']['is_za']) ? 0 : $params['rule']['is_za'];
		$params['rule']['pay_type'] = !isset($params['rule']['pay_type']) ? 0 : $params['rule']['pay_type'];
		$params['rule']['cancle_clocker'] = !isset($params['rule']['cancle_clocker']) ? 1 : $params['rule']['cancle_clocker'];
		$params['rule']['allow_louhu'] = !isset($params['rule']['allow_louhu']) ? 1 : $params['rule']['allow_louhu'];
		//$params['rule']['qg_is_zimo'] = !isset($params['rule']['qg_is_zimo']) ? 1 : $params['rule']['qg_is_zimo'];
		$params['rule']['score'] = !isset($params['rule']['score']) ? 0 : $params['rule']['score'];
		$params['rule']['is_score_field'] = !isset($params['rule']['is_score_field']) ? 0 : $params['rule']['is_score_field'];

		$params['rule']['zimo_rule'] = !isset($params['rule']['zimo_rule'])? 1 : $params['rule']['zimo_rule'];
		$params['rule']['dian_gang_hua'] = !isset($params['rule']['dian_gang_hua'])? 1 : $params['rule']['dian_gang_hua'];
		$params['rule']['is_change_3'] = !isset($params['rule']['is_change_3'])? 1 : $params['rule']['is_change_3'];
		$params['rule']['is_yaojiu_jiangdui'] = !isset($params['rule']['is_yaojiu_jiangdui'])? 1 : $params['rule']['is_yaojiu_jiangdui'];
		$params['rule']['is_menqing_zhongzhang'] = !isset($params['rule']['is_menqing_zhongzhang'])? 1 : $params['rule']['is_menqing_zhongzhang'];
		$params['rule']['is_tiandi_hu'] = !isset($params['rule']['is_tiandi_hu'])? 1 : $params['rule']['is_tiandi_hu'];


        if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3, 4)))
        {
            $params['rule']['player_count'] = 4;
        }

		if (!empty($params['rule']['is_score_field'])) 
		{
			$params['rule']['is_circle'] = !isset($params['rule']['is_circle']) ? 0 : $params['rule']['is_circle'];
		}
		else
		{
			$params['rule']['is_circle'] = !isset($params['rule']['is_circle']) ? 0 : $params['rule']['is_circle'];
		}

		//////////////////////////////
		
		$this->m_rule->game_type = $params['rule']['game_type'];
		$this->m_rule->player_count = $params['rule']['player_count'];
		//$this->m_rule->set_num = $params['rule']['set_num'];
		$this->m_rule->min_fan = $params['rule']['min_fan'];
		$this->m_rule->top_fan = $params['rule']['top_fan'];
		$this->m_rule->is_circle = $params['rule']['is_circle'];

		//$this->m_rule->is_feng = $params['rule']['is_feng'];
		//$this->m_rule->is_yipao_duoxiang = $params['rule']['is_yipao_duoxiang'];
		//$this->m_rule->is_chipai = $params['rule']['is_chipai'];
		//$this->m_rule->is_genzhuang = $params['rule']['is_genzhuang'];
		//$this->m_rule->is_paozi = $params['rule']['is_paozi'];
		//$this->m_rule->is_zhuang_fan = $params['rule']['is_zhuang_fan'];

		//$this->m_rule->is_qingyise_fan = $params['rule']['is_qingyise_fan'];
		//$this->m_rule->is_ziyise_fan = $params['rule']['is_ziyise_fan'];
		//$this->m_rule->is_yitiaolong_fan = $params['rule']['is_yitiaolong_fan'];
		//$this->m_rule->is_ganghua_fan = $params['rule']['is_ganghua_fan'];
		//$this->m_rule->is_qidui_fan = $params['rule']['is_qidui_fan'];
		//$this->m_rule->is_pengpenghu_fan = $params['rule']['is_pengpenghu_fan'];

		//$this->m_rule->is_wangang_1_lose = $params['rule']['is_wangang_1_lose'];
		//$this->m_rule->is_dianpao_bao = $params['rule']['is_dianpao_bao'];
		//$this->m_rule->is_wukui = $params['rule']['is_wukui'];
		//$this->m_rule->is_diaowuwan = $params['rule']['is_diaowuwan'];
		
		//$this->m_rule->is_zhongfabai_shun = $params['rule']['is_zhongfabai_shun'];
		//$this->m_rule->is_bian_zuan = $params['rule']['is_bian_zuan'];
		//$this->m_rule->is_za = $params['rule']['is_za'];
		$this->m_rule->pay_type = $params['rule']['pay_type'];
		
		$this->m_rule->cancle_clocker = $params['rule']['cancle_clocker'];
		$this->m_rule->allow_louhu = $params['rule']['allow_louhu'];
		//$this->m_rule->qg_is_zimo = $params['rule']['qg_is_zimo'];
		//$this->m_rule->score = $params['rule']['score'];
		//$this->m_rule->is_score_field = $params['rule']['is_score_field'];

        if(!empty($this->m_rule->is_circle))
        {
            $this->m_rule->set_num = $this->m_rule->is_circle * $this->m_rule->player_count;		//局等于  人*圈
        }
        else
        {
            $this->m_rule->set_num = $params['rule']['set_num'];
        }

        $this->m_rule->is_qixiaodui = $params['rule']['is_qixiaodui'];
        $this->m_rule->is_piaoteyibianda = $params['rule']['is_piaoteyibianda'];

        /*var_dump($this->m_rule);*/

    }

    ///////////////////打牌前阶段////////////////////
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
                    //修改过判断条件
                    if( (empty($this->m_sOutedCard->card) && empty($this->m_sQiangGang->card))
                        || ($this->m_sOutedCard->card && $this->m_sOutedCard->chair == $key && !$this->m_she_te->mark)
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
    public function judge_hu($chair)
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

        //处理抢杠胡
        if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
        }
        //处理扛上开花
        else if($this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGKAI);
        }
        //杠上开炮
        else if ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO && $this->m_sGangPao->mark && $this->m_sGangPao->chair != $chair)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGPAO);
        }
        //三家清
        $sanjiaqing_arr=array();
        for($i = 0; $i < $this->m_rule->player_count; $i++)
        {
            if($i==$chair)
            {
                continue;
            }
            if (!empty($this->m_rule->is_qixiaodui))
            {
                if (!$this->m_bKaiMen[$i])
                {
                    if (!$this->_is_ting_qidui($i))
                    {
                        $sanjiaqing_arr[]=1;
                    }
                }
            }
            else
            {
                if (!$this->m_bKaiMen[$i])
                {
                    $sanjiaqing_arr[]=1;
                }
            }
        }
        if(count($sanjiaqing_arr) == 3)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_SANJIAQING);
        }

        return true;
    }

    public function judge_hu_type($chair)
    {
        //三色判断
        if (!$this->_is_buquemen($chair))
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }

        $bHavezhong = $this->_is_havezhong($chair);
        $bHave1_9 = $this->_is_have19($chair);
        //19判断
        if (!$bHavezhong&&!$bHave1_9)
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }

        $jiang_arr = array();
        $qidui_arr = array();
        $pengpeng_arr = array();
        $gen_arr = array();

        $kezi_arr = array();
        $shunzi_arr = array();

        $bType32 = false;
        $bQiDui = false;
        $bPengPeng = false;

        $bShunzi = false;
        $bKezi = false;

        $bBian = false;
        $bDuidao = false;
        $bJiahu = false;
        $bDandiao = false;
        $bShoubayi = false;
        $bHu19 = false;
        $bTehu = false;
        $bHaohuaqixiaodui = false;


        $jiang_type=255;

        //1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen
        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if (0 == $this->m_sPlayer[$chair]->card[$i][0])
            {
                continue;
            }
            if (in_array($this->m_sPlayer[$chair]->card[$i][0], array(1, 7, 13)))
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }
            $tmp_hu_data = &ConstConfig::$hu_data;
            if ($i==ConstConfig::PAI_TYPE_FENG)
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
                //七对
                $qidui_arr[] = $hu_list_val & 64;
                //根判断
                $gen_arr[] = intval($hu_list_val / 4096);
                //对对胡
                $pengpeng_arr[] = $hu_list_val & 8;
                //刻字
                $kezi_arr[] = $hu_list_val & 1024;
                //顺子
                $shunzi_arr[] = $hu_list_val & 2048;
                //32牌型
                if ($hu_list_val & 1 == 1)
                {
                    $jiang_arr[] = $hu_list_val & 32;
                    if ($hu_list_val & 32)
                    {
                        $jiang_type=$i;
                    }
                }
                else
                {
                    //非32牌型设置
                    $jiang_arr[] = 32;$jiang_arr[] = 32;
                }
            }
        }
        //倒牌
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
        {
            //七对
            $qidui_arr[] = 0;
            //倒牌类型顺
            if (ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i])
            {
                //顺子
                $shunzi_arr[] = 1;
                //碰碰胡
                $pengpeng_arr[] = 0;
            }
            else
            {
                //刻字
                $kezi_arr[] = 1;
            }
        }

        //判断
        $bType32 = (32 == array_sum($jiang_arr));
        $bQiDui = !array_keys($qidui_arr, 0);
        $bPengPeng = !array_keys($pengpeng_arr, 0);
        $bShunzi = array_sum($shunzi_arr);
        $bKezi = array_sum($kezi_arr);


        /////////////////////////////附加 番型的处理/////////////////////////////////
        if (!$bType32 && !$bQiDui)    //不是32牌型也不是7对 也不是碰碰胡
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        if (!$bType32 && $bQiDui && empty($this->m_rule->is_qixiaodui))
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        //胡的那张牌的类型和键值
        $huCard_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
        $huCard_key = $this->m_HuCurt[$chair]->card % 16;
        //豪华七对判断
        if (!empty($this->m_rule->is_qixiaodui) && $bQiDui)
        {
            if ($this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key] == 4)
            {
                return self::HU_TYPE_HAOHUAQIXIAODUI;
            }
            if ($this->m_bTianRenHu)
            {
                //有根就符合豪华七对
                if (array_sum($gen_arr) >= 1)
                {
                    return self::HU_TYPE_HAOHUAQIXIAODUI;
                }
            }
            return self::HU_TYPE_QIXIAODUI;
        }

        if ($bType32)
        {
            //判断是否开门
            if (!$this->m_bKaiMen[$chair])
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }

            //手把一
            if ($this->m_sStandCard[$chair]->num == 4)//手把一判断
            {
                $bShoubayi = true;
            }

            if ($bPengPeng)
            {
                if ($bShoubayi)
                {
                    return self::HU_TYPE_PIAOHUSHOUBAYI; //飘胡手把一
                }
                else
                {
                    return self::HU_TYPE_PIAOHU; //飘胡
                }
            }

            //判断平胡(有顺有刻或者红中加顺)
            if (!$bShunzi || (!$bKezi && !$bHavezhong))
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }


            //$bBian = $this->_is_bian($chair,$huCard_type,$huCard_key);
            $bDuidao = $this->_is_duidao($chair,$huCard_type,$huCard_key);
            $bJiahu = $this->_is_jiahu($chair,$huCard_type,$huCard_key);
            $bHu19 = $this->_is_hu19($chair,$huCard_type,$huCard_key);
            $bTehu = $this->_is_tehu($chair,$huCard_type,$huCard_key,$bDuidao,$bJiahu,$bHu19,$bHavezhong);
            $bDandiao = $this->_is_dandiao($chair,$huCard_type,$huCard_key,$jiang_type);
        }
        ///////////////////////基本牌型的处理///////////////////////////////

        if($bTehu)
        {
            return self::HU_TYPE_TEHU; //特胡
        }
        if($bShoubayi)
        {
            return self::HU_TYPE_SHOUBAYI;//手把一
        }
        if(!$bHavezhong  && $bHu19)
        {
            return self::HU_TYPE_HU19;//胡19
        }
        if($bJiahu)
        {
            return self::HU_TYPE_JIAHU;//夹胡
        }
        if($bDandiao)
        {
            return self::HU_TYPE_DANDIAO;//单吊
        }
        if($bDuidao)
        {
            return self::HU_TYPE_DUIDAO;//对到
        }
        else
        {
            return self::HU_TYPE_BIAN;//边
        }

    }


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
            $tmp_hu_desc .= self::$hu_type_arr[$hu_type][2];
        }

        for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
        {
            if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
            {
                $fan_sum = $fan_sum * self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
                $tmp_hu_desc .= ' '.self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2];
            }
        }

        $this->m_bMaxFan[$chair] = false;

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

	//------------------------------------- 命令处理函数 -----------------------------------
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
        //第一次出现两张牌
        if ($this->m_sPlayer[$chair]->len == 1)
        {
            $this->m_double_card[$chair]=true;
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
                || (1 == $tmp_distance  && ($this->_find_eat($chair_next,1) || $this->_find_eat($chair_next,2) || $this->_find_eat($chair_next,3)))
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
    //处理吃牌
    public function HandleChooseEat($chair,$eat_num)
    {
        $temp_card = $this->m_sOutedCard->card;
        $card_type = $this->_get_card_type($temp_card);

        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID || $card_type == ConstConfig::PAI_TYPE_FENG  || $card_type == ConstConfig::PAI_TYPE_DRAGON )
        {
            echo("eat error".__LINE__.__CLASS__);
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
            echo("eat error".__LINE__.__CLASS__);
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

        //判断是否开过门
        if(!$this->m_bKaiMen[$chair])
        {
            // 查看倒牌有暗杠需要算分
            for ($j = 0; $j < $this->m_sStandCard[$chair]->num; $j ++)
            {
                if ($this->m_sStandCard[$chair]->type[$j] == ConstConfig::DAO_PAI_TYPE_ANGANG)
                {
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
                            if($chair == $this->m_nChairBanker)//庄家暗杠
                            {
                                $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                                $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                                $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                                $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
                                $nGangPao += $nGangScore;
                            }
                            else
                            {
                                if ($i == $this->m_nChairBanker)
                                {
                                    //庄家扣分翻倍
                                    $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                                    $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                                    $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                                    $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
                                    $nGangPao += $nGangScore;
                                }
                                else
                                {
                                    $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;

                                    $this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分
                                    $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                                    $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
                                    $nGangPao += $nGangScore;
                                }
                            }
                        }
                    }
                }
            }
            $this->m_bKaiMen[$chair] = true;
        }
        // 找出第14张牌
        $card_14 = $this->_find_14_card($chair);
        if(!$card_14)
        {
            echo "error dddf".__LINE__.__CLASS__;
            return false;
        }

        //置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
        $this->_deleteLastTableCard();

        $this->m_sOutedCard->clear();

        $this->m_sPlayer[$chair]->card_taken_now = $card_14;

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

    //处理碰牌
    public function HandleChoosePeng($chair)
    {
        //获取出牌的信息
        $temp_card = $this->m_sOutedCard->card;
        $card_type = $this->_get_card_type($temp_card);
        $temp_chair = $this->m_sOutedCard->chair;

        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
        {
            echo("error peng".__LINE__.__CLASS__);
            return false;
        }

        //置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
        $this->_deleteLastTableCard();

        //删除手中的牌
        $this->_list_delete($chair, $temp_card);
        $this->_list_delete($chair, $temp_card);


        // 设置倒牌
        $stand_count = $this->m_sStandCard[$chair]->num;
        $this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_KE;
        $this->m_sStandCard[$chair]->first_card[$stand_count] = $temp_card;
        $this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
        $this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
        $this->m_sStandCard[$chair]->num ++;

        //判断是否开过门
        if(!$this->m_bKaiMen[$chair])
        {
            // 查看倒牌有暗杠需要算分
            for ($j = 0; $j < $this->m_sStandCard[$chair]->num; $j ++)
            {
                if ($this->m_sStandCard[$chair]->type[$j] == ConstConfig::DAO_PAI_TYPE_ANGANG)
                {
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
                            if($chair == $this->m_nChairBanker)//庄家暗杠
                            {
                                $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                                $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                                $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                                $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
                                $nGangPao += $nGangScore;
                            }
                            else
                            {
                                if ($i == $this->m_nChairBanker)
                                {
                                    //庄家扣分翻倍
                                    $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                                    $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                                    $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                                    $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
                                    $nGangPao += $nGangScore;
                                }
                                else
                                {
                                    $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;

                                    $this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分
                                    $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                                    $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
                                    $nGangPao += $nGangScore;
                                }
                            }
                        }
                    }
                }
            }
            $this->m_bKaiMen[$chair] = true;
        }

        // 找出第14张牌
        $car_14 = $this->_find_14_card($chair);
        if(!$car_14)
        {
            echo "error handlechoosepeng".__LINE__.__CLASS__;
            return false;
        }
        //第14张牌
        $this->m_sPlayer[$chair]->card_taken_now = $car_14;
        //清空出牌
        $this->m_sOutedCard->clear();

        //写记录
        $this->_set_record_game(ConstConfig::RECORD_PENG, $chair, $temp_card, $temp_chair);


        //设置状态
        for ($i = 0; $i < $this->m_rule->player_count ; $i ++)
        {
            if($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
            {
                $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_WAITING;
            }
        }
        //改变状态
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
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
        }

        return true;
    }
    //处理直杠
    public function HandleChooseZhiGang($chair)
    {
        $temp_card = $this->m_sOutedCard->card;
        $card_type = $this->_get_card_type($temp_card);
        $temp_chair = $this->m_sOutedCard->chair;

        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
        {
            return false;
        }

        //置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）(杠的桌面牌拿走)
        $this->_deleteLastTableCard();

        //删除手中的3张牌
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

        //判断是否开过门
        if(!$this->m_bKaiMen[$chair])
        {
            // 查看倒牌有暗杠需要算分
            for ($j = 0; $j < $this->m_sStandCard[$chair]->num; $j ++)
            {
                if ($this->m_sStandCard[$chair]->type[$j] == ConstConfig::DAO_PAI_TYPE_ANGANG)
                {
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
                            if($chair == $this->m_nChairBanker)//庄家暗杠
                            {
                                $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                                $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                                $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                                $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
                                $nGangPao += $nGangScore;
                            }
                            else
                            {
                                if ($i == $this->m_nChairBanker)
                                {
                                    //庄家扣分翻倍
                                    $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                                    $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                                    $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                                    $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
                                    $nGangPao += $nGangScore;
                                }
                                else
                                {
                                    $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;

                                    $this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分
                                    $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                                    $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
                                    $nGangPao += $nGangScore;
                                }
                            }
                        }
                    }
                }
            }
            $this->m_bKaiMen[$chair] = true;
        }

        //判断特胡
        $this->_list_insert($this->m_sOutedCard->chair,$temp_card);
        /*var_dump(__LINE__);
        var_dump($this->judge_she_te($this->m_sOutedCard->chair,$temp_card));*/

        if ($this->_judge_she_te($this->m_sOutedCard->chair,$temp_card))
        {
            $this->m_she_te->mark = true;
            $this->m_she_te->gang_chair = $chair;
            $this->m_she_te->she_te_chair = $this->m_sOutedCard->chair;
        }
        $this->_list_delete($this->m_sOutedCard->chair,$temp_card);

        if ($this->m_she_te->mark)
        {
            //记录
            $this->_set_record_game(ConstConfig::RECORD_ZHIGANG, $chair, $temp_card, $temp_chair);

            // 初始化杠结构
            $this->m_sQiangGang->init_data(true, $temp_card, $chair); //处理抢杠

            /*$this->m_sOutedCard->clear();*/

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
                if ($next_chair == $this->m_sOutedCard->chair)
                {
                    $this->m_bChooseBuf[$this->m_sOutedCard->chair] = 1;
                    $this->m_sPlayer[$this->m_sOutedCard->chair]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
                }
                else
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
            $this->m_currentCmd = 0;							    // 当前的命令
        }
        else
        {
            $nGangScore = 0;
            $nGangPao = 0;
            for ($i=0; $i<$this->m_rule->player_count; $i++)
            {
                if ($i == $chair)
                {
                    continue;
                }

                if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
                {
                    if($chair == $this->m_nChairBanker)//庄家直杠
                    {
                        $nGangScore = self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                        $this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分
                        $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                        $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

                        $nGangPao += $nGangScore;
                    }
                    else
                    {
                        if ($i == $this->m_nChairBanker)
                        {
                            //庄家扣分翻倍
                            $nGangScore = self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                            $this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分
                            $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                            $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

                            $nGangPao += $nGangScore;
                        }
                        else
                        {
                            $nGangScore = self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;

                            $this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分
                            $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                            $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

                            $nGangPao += $nGangScore;
                        }
                    }
                }
            }
            //for 杠上花
            $this->m_bHaveGang = true;
            //杠上炮初始化
            $this->m_sGangPao->init_data(true, $temp_card, $chair,ConstConfig::DAO_PAI_TYPE_MINGGANG, $nGangPao);
            //总计
            $this->m_wTotalScore[$chair]->n_zhigang_wangang += 1;
            //记录
            $this->_set_record_game(ConstConfig::RECORD_ZHIGANG, $chair, $temp_card, $temp_chair);
            //清除出牌信息
            $this->m_sOutedCard->clear();
            //播放动画命令发送
            $this->_send_act($this->m_currentCmd, $chair);

            // 补发张牌给玩家
            $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
            $this->m_chairCurrentPlayer = $chair;
            if(!$this->DealCard($chair))
            {
                return;
            }

            if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
            {
                //CCLOG("end reason no card");
                return;
            }

            //状态变化发消息
            $this->handle_flee_play(true);	//更新断线用户
            for ($i=0; $i < $this->m_rule->player_count ; $i++)
            {
                $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
            }
        }

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
        //第14张牌插入手牌
        $this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now);
        $this->m_sPlayer[$chair]->card_taken_now = 0;
        //删除手牌中的4张
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

        $GangScore = 0;
        $nGangPao = 0;
        //判断是否开过门
        if($this->m_bKaiMen[$chair])
        {
            for ($i=0; $i<$this->m_rule->player_count; ++$i)
            {
                if ($i == $chair)
                {
                    continue;
                }
                if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
                {
                    if($chair == $this->m_nChairBanker)//庄家暗杠
                    {
                        $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                        $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                        $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                        $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分
                        $nGangPao += $nGangScore;
                    }
                    else
                    {
                        if ($i == $this->m_nChairBanker)
                        {
                            //庄家扣分翻倍
                            $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                            $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                            $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                            $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

                            $nGangPao += $nGangScore;
                        }
                        else
                        {
                            $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;

                            $this->m_wGangScore[$i][$i] -= $nGangScore;		//总刮风下雨分
                            $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                            $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

                            $nGangPao += $nGangScore;
                        }
                    }
                }
            }
        }
        //for 杠上花
        $this->m_bHaveGang = true;
        //杠炮
        $this->m_sGangPao->init_data(true, $gang_card, $chair, ConstConfig::DAO_PAI_TYPE_ANGANG, $nGangPao);
        //总计
        $this->m_wTotalScore[$chair]->n_angang += 1;
        //记录
        $this->_set_record_game(ConstConfig::RECORD_ANGANG, $chair, $temp_card, $chair);

        //修改状态
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
        $this->m_chairCurrentPlayer = $chair;
        //暗杠需要记录入命令
        $this->m_chairSendCmd = $this->m_chairCurrentPlayer;
        $this->m_currentCmd = 'c_an_gang';
        $this->m_sOutedCard->clear();

        //状态变化发消息
        $this->_send_act($this->m_currentCmd, $chair);

        // 补发张牌给玩家

        if(!($this->DealCard($chair)))
        {
            return;
        }

        if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
        {
            //CCLog("end reason no card");
            return;
        }

        $this->handle_flee_play(true);	//更新断线用户
        for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
        }
    }
    /*//处理弯杠
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
        $chair_next = $chair;

        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_CHOOSING;
        $this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄

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
            $this->m_nHuCan[$chair_next] = false;

            $tmp_distance = $this->_chair_to($chair, $chair_next);

            $this->m_bChooseBuf[$chair_next] = 1;
            $this->m_sPlayer[$chair_next]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
            $bHaveCmd = 1;

            //碰杠胡操作需要修改
            //判断是否有胡
            $this->_list_insert($chair_next, $temp_card);
            $this->m_HuCurt[$chair_next]->card = $temp_card;
            $this->m_HuCurt[$chair_next]->state = ConstConfig::WIN_STATUS_CHI_PAO;
            $tmp_c_hu_result = ( $this->m_is_ting_arr[$chair_next] && $this->judge_hu($chair_next) && ($this->m_nHuGiveUp[$chair_next] < $this->judge_fan($chair_next)));

            $this->m_HuCurt[$chair_next]->clear();
            $this->_list_delete($chair_next, $temp_card);
            if($tmp_c_hu_result)
            {
                $this->m_nHuCan[$chair_next] = true;
            }
            else
            {
                $this->m_sPlayer[$chair_next]->state = ConstConfig::PLAYER_STATUS_WAITING;
                $tmp_arr[] = $chair_next;
            }
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair_next), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair_next]['uid']);
        }

        $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair]['uid']);

        $this->m_chairSendCmd = 255;							// 当前发命令的玩家
        $this->m_currentCmd = 0;							    // 当前的命令

        foreach ($tmp_arr as $val_next_chair)
        {
            $tmp_c_act = "c_cancle_choice";
            $this->_clear_choose_buf($val_next_chair, false);
            $this->m_sPlayer[$val_next_chair]->state = ConstConfig::PLAYER_STATUS_WAITING;
            $this->HandleChooseResult($val_next_chair, $tmp_c_act);
        }


    }*/
    //竞争选择处理
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
                if ($this->m_she_te->mark)
                {
                    if($record_hu_chair && is_array($record_hu_chair))
                    {
                        $this->_set_record_game(ConstConfig::RECORD_TEHU_QIANGGANG, $record_hu_chair, $this->m_sQiangGang->card, $this->m_sQiangGang->chair);
                    }
                }
                else
                {
                    if($record_hu_chair && is_array($record_hu_chair))
                    {
                        $this->_set_record_game(ConstConfig::RECORD_HU_QIANGGANG, $record_hu_chair, $this->m_sQiangGang->card, $this->m_sQiangGang->chair);
                    }
                }
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
                for ($i=0; $i < $this->m_rule->player_count ; $i++)
                {
                    $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
                }

                $this->m_nEndReason = ConstConfig::END_REASON_HU;
                $this->HandleSetOver();
                return;

            }
            else // 给杠的玩家补张
            {

                $GangScore = 0;
                $nGangPao = 0;
                for ( $i=0; $i<$this->m_rule->player_count; ++$i)
                {
                    if ($i == $this->m_sQiangGang->chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
                    {
                        continue;
                    }
                    if($this->m_sQiangGang->chair == $this->m_nChairBanker)//庄家弯杠
                    {
                        $nGangScore = self::M_WANGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                        $this->m_wGangScore[$i][$i] -= $nGangScore;
                        $this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
                        $this->m_wGangScore[$this->m_sQiangGang->chair][$i] += $nGangScore;

                        $nGangPao += $nGangScore;
                    }
                    else
                    {
                        if ($i == $this->m_nChairBanker)
                        {
                            //庄家扣分翻倍
                            $nGangScore = self::M_WANGANG_SCORE * ConstConfig::SCORE_BASE * 2;

                            $this->m_wGangScore[$i][$i] -= $nGangScore;
                            $this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
                            $this->m_wGangScore[$this->m_sQiangGang->chair][$i] += $nGangScore;

                            $nGangPao += $nGangScore;
                        }
                        else
                        {
                            $nGangScore = self::M_WANGANG_SCORE *ConstConfig::SCORE_BASE;

                            $this->m_wGangScore[$i][$i] -= $nGangScore;
                            $this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
                            $this->m_wGangScore[$this->m_sQiangGang->chair][$i] += $nGangScore;

                            $nGangPao += $nGangScore;
                        }
                    }
                }



                $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
                $this->m_chairCurrentPlayer = $this->m_sQiangGang->chair;

                $this->m_bHaveGang = true;					//for 杠上花
                //$this->m_gangkai_num[$this->m_sQiangGang->chair] +=1;           //连续杠的次数
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
        else	// 不是抢杠拉
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
                $this->m_currentCmd = 'c_hu';

                //置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
                $this->_deleteLastTableCard();

                $this->m_nEndReason = ConstConfig::END_REASON_HU;
                $this->HandleSetOver();
                return;

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

                default:  //预防有人诈胡后,游戏得以继续
                    $this->m_sGangPao->clear();
                    if ($this->m_she_te->mark)
                    {
                        $this->m_she_te->clear();
                    }

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
			//$data['m_own_paozi'] = $this->m_own_paozi;
			
			$data['m_wTotalScore'] = $this->m_wTotalScore;
			$data['m_ready'] = $this->m_ready;
			$data['is_cancle'] = $this->m_cancle;
			$data['m_cancle'] = $this->m_cancle;
			$data['m_cancle_first'] = $this->m_cancle_first;
			//$data['m_sDingQue'] = $this->m_sDingQue;

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
        $data['m_bKaiMen'] = $this->m_bKaiMen;                  //是否开门
        $data['m_double_card'] = $this->m_double_card[$chair];  //第一次两张牌

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

        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_HUAN_3 || $this->m_sysPhase == ConstConfig::SYSTEMPHASE_DING_QUE)
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

        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_HUAN_3 || $this->m_sysPhase == ConstConfig::SYSTEMPHASE_DING_QUE)
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
			//$data['player_score'] = $this->player_score;
			return $data;
		}
        return true;
	}

	///////////////////////得分处理////////////////////////////////////////
	//每局个人  +=赢的分  +=输的分  +=庄家 的分
    public function ScoreOneHuCal($chair, &$lost_chair)
    {
        $fan_sum = $this->judge_fan($chair);
        $PerWinScore = $fan_sum;
        $wWinScore = 0;

        $this->m_wHuScore = [0,0,0,0];

        //自摸胡
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

                //庄家分数翻倍
                $banker_fan = 1;
                if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
                {
                    $banker_fan = $banker_fan * 2;
                }
                //数据门清判断
                if(!(in_array(self::ATTACHED_HU_SANJIAQING, $this->m_HuCurt[$chair]->method)))
                {
                    if (!empty($this->m_rule->is_qixiaodui))
                    {
                        if ($this->m_sStandCard[$i]->num == 0)
                        {
                            if (!$this->_is_ting_qidui($i))
                            {
                                $banker_fan = $banker_fan * 2;
                                $this->m_hu_desc[$i] .= '门清 ';
                            }
                        }
                        else
                        {
                            if ((!in_array(ConstConfig::DAO_PAI_TYPE_SHUN,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_KE,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_MINGGANG,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_WANGANG,$this->m_sStandCard[$i]->type)))
                            {
                                $banker_fan = $banker_fan * 2;
                                $this->m_hu_desc[$i] .= '门清 ';
                            }
                        }
                    }
                    else
                    {
                        if($this->m_sStandCard[$i]->num == 0)
                        {
                            $banker_fan = $banker_fan * 2;
                            $this->m_hu_desc[$i] .= '门清 ';
                        }
                        else
                        {
                            if ((!in_array(ConstConfig::DAO_PAI_TYPE_SHUN,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_KE,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_MINGGANG,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_WANGANG,$this->m_sStandCard[$i]->type)))
                            {
                                $banker_fan = $banker_fan * 2;
                                $this->m_hu_desc[$i] .= '门清 ';
                            }
                        }
                    }
                }
                //飘特一边大
                if($this->m_rule->is_piaoteyibianda && (in_array(self::HU_TYPE_PIAOHU, $this->m_HuCurt[$chair]->method)))
                {
                    $banker_fan = $banker_fan * 2;
                }
                //手把一
                if($this->m_rule->is_piaoteyibianda && (in_array(self::HU_TYPE_SHOUBAYI, $this->m_HuCurt[$chair]->method)))
                {
                    $banker_fan = $banker_fan * 2;
                }
                //飘胡手把一
                if($this->m_rule->is_piaoteyibianda && (in_array(self::HU_TYPE_PIAOHUSHOUBAYI, $this->m_HuCurt[$chair]->method)))
                {
                    $banker_fan = $banker_fan * 2;
                }
                //自摸翻倍
                $banker_fan = $banker_fan * 2;

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
            $banker_fan = 1;//翻倍分数


            //庄家分数翻倍
            if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
            {
                $banker_fan = $banker_fan * 2;
            }
            //数据门清判断
            if(!(in_array(self::ATTACHED_HU_SANJIAQING, $this->m_HuCurt[$chair]->method)))
            {
                if (!empty($this->m_rule->is_qixiaodui))
                {
                    if ($this->m_sStandCard[$lost_chair]->num == 0)
                    {
                        if (!$this->_is_ting_qidui($lost_chair))
                        {
                            $banker_fan = $banker_fan * 2;
                            $this->m_hu_desc[$lost_chair] .= '门清 ';
                        }
                    }
                    else
                    {
                        if ((!in_array(ConstConfig::DAO_PAI_TYPE_SHUN,$this->m_sStandCard[$lost_chair]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_KE,$this->m_sStandCard[$lost_chair]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_MINGGANG,$this->m_sStandCard[$lost_chair]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_WANGANG,$this->m_sStandCard[$lost_chair]->type)))
                        {
                            $banker_fan = $banker_fan * 2;
                            $this->m_hu_desc[$lost_chair] .= '门清 ';
                        }
                    }
                }
                else
                {
                    if($this->m_sStandCard[$lost_chair]->num == 0)
                    {
                        $banker_fan = $banker_fan * 2;
                        $this->m_hu_desc[$lost_chair] .= '门清 ';
                    }
                    else
                    {
                        if ((!in_array(ConstConfig::DAO_PAI_TYPE_SHUN,$this->m_sStandCard[$lost_chair]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_KE,$this->m_sStandCard[$lost_chair]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_MINGGANG,$this->m_sStandCard[$lost_chair]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_WANGANG,$this->m_sStandCard[$lost_chair]->type)))
                        {
                            $banker_fan = $banker_fan * 2;
                            $this->m_hu_desc[$lost_chair] .= '门清 ';
                        }
                    }
                }
            }
            //飘特一边大
            if($this->m_rule->is_piaoteyibianda && (in_array(self::HU_TYPE_PIAOHU, $this->m_HuCurt[$chair]->method)))
            {
                $banker_fan = $banker_fan * 2;
            }
            //手把一
            if($this->m_rule->is_piaoteyibianda && (in_array(self::HU_TYPE_SHOUBAYI, $this->m_HuCurt[$chair]->method)))
            {
                $banker_fan = $banker_fan * 2;
            }
            //飘胡手把一
            if($this->m_rule->is_piaoteyibianda && (in_array(self::HU_TYPE_PIAOHUSHOUBAYI, $this->m_HuCurt[$chair]->method)))
            {
                $banker_fan = $banker_fan * 2;
            }
            //点炮翻倍
            $banker_fan = $banker_fan * 2;
            $this->m_hu_desc[$lost_chair] .= '点炮 ';

            $PerWinScore = ($PerWinScore == 0)? 1 : ($PerWinScore * $banker_fan);
            $wWinScore = 0;
            $wWinScore += $PerWinScore  ;

            $this->m_wHuScore[$lost_chair] -= $wWinScore;
            $this->m_wHuScore[$chair] += $wWinScore;

            $this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
            $this->m_wSetScore[$chair] += $wWinScore;

            $this->m_HuCurt[$chair]->gain_chair[0] = 1;
            $this->m_HuCurt[$chair]->gain_chair[1]=$lost_chair;

            //恢复初始值
            $PerWinScore = $PerWinScore / $banker_fan;
            $banker_fan = 1;

            for($i = 0; $i < $this->m_rule->player_count; $i++)
            {
                if( $i == $chair || $i == $lost_chair)
                {
                    continue;
                }
                //庄家翻倍
                if($i==$this->m_nChairBanker || $this->m_nChairBanker == $chair)
                {
                    $banker_fan = $banker_fan * 2;
                }
                //数据门清判断
                if(!(in_array(self::ATTACHED_HU_SANJIAQING, $this->m_HuCurt[$chair]->method)))
                {
                    if (!empty($this->m_rule->is_qixiaodui))
                    {
                        if ($this->m_sStandCard[$i]->num == 0)
                        {
                            if (!$this->_is_ting_qidui($i))
                            {
                                $banker_fan = $banker_fan * 2;
                                $this->m_hu_desc[$i] .= '门清 ';
                            }
                        }
                        else
                        {
                            if ((!in_array(ConstConfig::DAO_PAI_TYPE_SHUN,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_KE,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_MINGGANG,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_WANGANG,$this->m_sStandCard[$i]->type)))
                            {
                                $banker_fan = $banker_fan * 2;
                                $this->m_hu_desc[$i] .= '门清 ';
                            }
                        }
                    }
                    else
                    {
                        if($this->m_sStandCard[$i]->num == 0)
                        {
                            $banker_fan = $banker_fan * 2;
                            $this->m_hu_desc[$i] .= '门清 ';
                        }
                        else
                        {
                            if ((!in_array(ConstConfig::DAO_PAI_TYPE_SHUN,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_KE,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_MINGGANG,$this->m_sStandCard[$i]->type))&&(!in_array(ConstConfig::DAO_PAI_TYPE_WANGANG,$this->m_sStandCard[$i]->type)))
                            {
                                $banker_fan = $banker_fan * 2;
                                $this->m_hu_desc[$i] .= '门清 ';
                            }
                        }
                    }
                }
                //飘特一边大
                if($this->m_rule->is_piaoteyibianda && (in_array(self::HU_TYPE_PIAOHU, $this->m_HuCurt[$chair]->method)))
                {
                    $banker_fan = $banker_fan * 2;
                }
                //手把一
                if($this->m_rule->is_piaoteyibianda && (in_array(self::HU_TYPE_SHOUBAYI, $this->m_HuCurt[$chair]->method)))
                {
                    $banker_fan = $banker_fan * 2;
                }
                //飘胡手把一
                if($this->m_rule->is_piaoteyibianda && (in_array(self::HU_TYPE_PIAOHUSHOUBAYI, $this->m_HuCurt[$chair]->method)))
                {
                    $banker_fan = $banker_fan * 2;
                }
                //杠炮(流泪)
                if (in_array(self::ATTACHED_HU_GANGPAO, $this->m_HuCurt[$chair]->method))
                {
                    $banker_fan = $banker_fan * 0.5;
                }

                $PerWinScore = ($PerWinScore == 0)? 1 : ($PerWinScore * $banker_fan);
                $wWinScore = 0;
                $wWinScore += $PerWinScore  ;

                $this->m_wHuScore[$i] -= $wWinScore;
                $this->m_wHuScore[$chair] += $wWinScore;

                $this->m_wSetLoseScore[$i] -= $wWinScore;
                $this->m_wSetScore[$chair] += $wWinScore;

                $this->m_HuCurt[$chair]->gain_chair[0]++;
                $this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $i;

                //恢复初始值
                $PerWinScore = $PerWinScore / $banker_fan;
                $banker_fan = 1;

            }
            return true;
        }

        echo("此人没有胡".__LINE__);
        return false;
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
                /*$this->m_hu_desc[$i] = ''*/;
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

    ///荒庄结算
    public function CalcNoCardScore()
    {
        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            $this->m_Score[$i]->clear();
        }

        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            $this->m_Score[$i]->score = $this->m_wGangScore[$i][$i];
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

	////////////////////////////其他///////////////////////////

    //洗牌
    public function WashCard()
    {

        //只有万条筒加红中
        $this->m_nCardBuf = ConstConfig::ALL_CARD_112;
        $this->m_nAllCardNum = ConstConfig::BASE_CARD_NUM_HONG_ZHONG;
        if(!empty($this->ALL_CARD))
        {
            $this->m_nCardBuf = $this->ALL_CARD;
        }

        if(empty($this->ALL_CARD))
        {
            shuffle($this->m_nCardBuf); shuffle($this->m_nCardBuf);	//为了测试 不洗牌
        }
    }


    //简版判断能不能胡
    public function _judge_hu_simple($chair)
    {
        //三色判断
        if (!$this->_is_buquemen($chair))
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }

        $bHavezhong = $this->_is_havezhong($chair);
        $bHave1_9 = $this->_is_have19($chair);
        //19判断
        if (!$bHavezhong&&!$bHave1_9)
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }

        $jiang_arr = array();
        $qidui_arr = array();
        $pengpeng_arr = array();

        $kezi_arr = array();
        $shunzi_arr = array();

        $bType32 = false;
        $bQiDui = false;
        $bPengPeng = false;

        $bShunzi = false;
        $bKezi = false;


        //1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen
        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if (0 == $this->m_sPlayer[$chair]->card[$i][0])
            {
                continue;
            }
            if (in_array($this->m_sPlayer[$chair]->card[$i][0], array(1, 7, 13)))
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }
            $tmp_hu_data = &ConstConfig::$hu_data;
            if ($i==ConstConfig::PAI_TYPE_FENG)
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
                //七对
                $qidui_arr[] = $hu_list_val & 64;
                //对对胡
                $pengpeng_arr[] = $hu_list_val & 8;
                //刻字
                $kezi_arr[] = $hu_list_val & 1024;
                //顺子
                $shunzi_arr[] = $hu_list_val & 2048;
                //32牌型
                if ($hu_list_val & 1 == 1)
                {
                    $jiang_arr[] = $hu_list_val & 32;
                }
                else
                {
                    //非32牌型设置
                    $jiang_arr[] = 32;$jiang_arr[] = 32;
                }
            }
        }
        //倒牌
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
        {
            //七对
            $qidui_arr[] = 0;
            //倒牌类型顺
            if (ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i])
            {
                //顺子
                $shunzi_arr[] = 1;
                //碰碰胡
                $pengpeng_arr[] = 0;
            }
            else
            {
                //刻字
                $kezi_arr[] = 1;
            }
        }

        //判断
        $bType32 = (32 == array_sum($jiang_arr));
        $bQiDui = !array_keys($qidui_arr, 0);
        $bPengPeng = !array_keys($pengpeng_arr, 0);
        $bShunzi = array_sum($shunzi_arr);
        $bKezi = array_sum($kezi_arr);

        /////////////////////////////附加 番型的处理/////////////////////////////////
        if (!$bType32 && !$bQiDui)    //不是32牌型也不是7对 也不是碰碰胡
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        //七对判断
        if (!empty($this->m_rule->is_qixiaodui) && $bQiDui)
        {
            return self::HU_TYPE_QIXIAODUI;
        }

        if ($bType32)
        {
            //判断是否开门
            if (!$this->m_bKaiMen[$chair])
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }
            if ($bPengPeng)
            {
                return self::HU_TYPE_PIAOHU; //飘胡
            }

            //判断平胡(有顺有刻或者红中加顺)
            if ($bShunzi && ($bKezi || $bHavezhong))
            {
                return true;
            }
            else
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }

        }
    }
    //判断是否满足32牌型
    public function _judge_32type($chair,$delcard_arr,$card_type)
    {
        $bType32 = false;
        $tmp_hu_data = &ConstConfig::$hu_data;
        if ($card_type == ConstConfig::PAI_TYPE_FENG)
        {
            $tmp_hu_data = &ConstConfig::$hu_data_feng;
        }
        foreach ($delcard_arr as $item=>$value)
        {
            $this->m_sPlayer[$chair]->card[$card_type][$value] -= 1;
        }
        //判断手牌是否满足32牌型
        $tmp_key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$card_type], 1)));
        if(isset($tmp_hu_data[$tmp_key]) && ($tmp_hu_data[$tmp_key] & 1) == 1)
        {
            $bType32 = true;
        }
        foreach ($delcard_arr as $item=>$value)
        {
            $this->m_sPlayer[$chair]->card[$card_type][$value] += 1;
        }
        return $bType32;
    }
    //单听将判断
    public function _is_dantingjiang($chair,$huCard_type)
    {
        //如果牌刚好是2张红中
        if ($huCard_type==ConstConfig::PAI_TYPE_FENG)
        {
            //单吊符合
            return true;
        }
        $is_dandiao = true;
        $replace_card = array(1,2,3,4,5,6,7,8,9);
        $huCard_key = $this->m_HuCurt[$chair]->card % 16;
        foreach ($replace_card as $key=>$value)
        {
            if ($value == $huCard_key)
            {
                unset($replace_card[$key]);
            }
        }
        //删除手牌中的胡的那一张牌
        $this->_list_delete($chair,$this->m_HuCurt[$chair]->card);

        foreach ($replace_card as $value)
        {
            //不靠张的跳过
            $before_j = $value - 1;
            $next_j = $value+1;
            $before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$huCard_type][$before_j] : 0 ;
            $next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$huCard_type][$next_j] : 0 ;
            if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$huCard_type][$value] && 0 == $next_count)
            {
                continue;
            }

            $jiang_arr = array();
            $this->m_sPlayer[$chair]->card[$huCard_type][$value] += 1;

            //手牌
            $i=$huCard_type;
            {
                $tmp_hu_data = &ConstConfig::$hu_data;
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
            $this->m_sPlayer[$chair]->card[$huCard_type][$value] -= 1;
            //记录根到全局数据
            if(32 == array_sum($jiang_arr)
                && $this->m_sPlayer[$chair]->card[$huCard_type][$value] == 1
                && $this->_judge_32type($chair,array($value),$huCard_type)
            )
            {
                $is_dandiao = false;
                break;
            }
        }
        $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
        return $is_dandiao;
    }
    //是否听七小对(修改过逻辑)
    public function _is_ting_qidui($chair)
    {
        //七对没有倒牌
        if ($this->m_sStandCard[$chair]->num > 0)
        {
            return false;
        }
        //三色判断
        if (!$this->_is_buquemen($chair))
        {
            return false;
        }
        //19中判断
        if (!$this->_is_have19($chair)&&!$this->_is_havezhong($chair))
        {
            return false;
        }
        $qidui_num = 0;
        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if ($this->m_sPlayer[$chair]->card[$i][0]>0)
            {
                for ($j = 1; $j <= 9; $j++)
                {
                    $qidui_num += $this->m_sPlayer[$chair]->card[$i][$j] % 2;
                }
            }
        }
        if($qidui_num == 1)
        {
            return true;
        }
        else
        {
            return false;
        }

    }
    //判断设特
    public function _judge_she_te($chair,$huCard)
    {
        //三色判断
        if (!$this->_is_buquemen($chair))
        {

            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }

        $bHavezhong = $this->_is_havezhong($chair);
        $bHave1_9 = $this->_is_have19($chair);
        //19判断
        if (!$bHavezhong&&!$bHave1_9)
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }

        $jiang_arr = array();
        $pengpeng_arr = array();

        $kezi_arr = array();
        $shunzi_arr = array();

        $bType32 = false;
        $bPengPeng = false;

        $bShunzi = false;
        $bKezi = false;

        $bDuidao = false;
        $bJiahu = false;
        $bDandiao = false;
        $bHu19 = false;
        $bTehu = false;


        //1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen
        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if (0 == $this->m_sPlayer[$chair]->card[$i][0])
            {
                continue;
            }
            if (in_array($this->m_sPlayer[$chair]->card[$i][0], array(1, 7, 13)))
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }
            $tmp_hu_data = &ConstConfig::$hu_data;
            if ($i==ConstConfig::PAI_TYPE_FENG)
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
                //刻字
                $kezi_arr[] = $hu_list_val & 1024;
                //顺子
                $shunzi_arr[] = $hu_list_val & 2048;
                //32牌型
                if ($hu_list_val & 1 == 1)
                {
                    $jiang_arr[] = $hu_list_val & 32;
                }
                else
                {
                    //非32牌型设置
                    $jiang_arr[] = 32;$jiang_arr[] = 32;
                }
            }
        }
        //倒牌
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
        {
            //倒牌类型顺
            if (ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i])
            {
                //顺子
                $shunzi_arr[] = 1;
            }
            else
            {
                //刻字
                $kezi_arr[] = 1;
            }
        }

        //判断
        $bType32 = (32 == array_sum($jiang_arr));
        $bShunzi = array_sum($shunzi_arr);
        $bKezi = array_sum($kezi_arr);


        /////////////////////////////附加 番型的处理/////////////////////////////////
        if (!$bType32)
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        //胡的那张牌的类型和键值
        $huCard_type = $this->_get_card_type($huCard);
        $huCard_key = $huCard % 16;
        if ($bType32)
        {
            //判断是否开门
            if (!$this->m_bKaiMen[$chair])
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }
            //判断平胡(有顺有刻或者红中加顺)
            if (!$bShunzi || (!$bKezi && !$bHavezhong))
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }


            $bJiahu = $this->_is_jiahu($chair,$huCard_type,$huCard_key);
            $bHu19 = $this->_is_hu19($chair,$huCard_type,$huCard_key);
            $bTehu = $this->_is_shete($chair,$huCard,$bJiahu,$bHu19,$bHavezhong);

        }
        ///////////////////////基本牌型的处理///////////////////////////////

        if($bTehu)
        {
            return self::HU_TYPE_TEHU; //特胡
        }
        else
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
    }
    //特胡判断(牌为倒牌)
    public function _is_shete($chair,$huCard,$bJiahu,$bHu19,$bHavezhong)
    {
        $bTehu = false;
        for ($i = 0; $i < $this->m_rule->player_count; $i++)
        {
            for ($j = 0; $j < $this->m_sStandCard[$i]->num; $j++)
            {
                if (ConstConfig::DAO_PAI_TYPE_MINGGANG == $this->m_sStandCard[$i]->type[$j])
                {
                    if ($this->m_sStandCard[$i]->first_card[$j] == $huCard)
                    {
                        if ($bJiahu||($bHu19&&!$bHavezhong))
                        {
                            $bTehu = true;
                        }
                    }
                }
            }
        }
        return $bTehu;
    }
    //判断是否不缺门
    public function _is_buquemen($chair)
    {
        $bBuquemen = false;
        $buquemen_arr = array();
        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
        {
            if ($this->m_sPlayer[$chair]->card[$i][0] > 0)
            {
                $buquemen_arr[] = $i;
            }
        }
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
        {
            $buquemen_arr[] = $this->_get_card_type($this->m_sStandCard[$chair]->first_card[$i]);
        }
        $bBuquemen = (in_array(ConstConfig::PAI_TYPE_WAN,$buquemen_arr)&&in_array(ConstConfig::PAI_TYPE_TIAO,$buquemen_arr)&&in_array(ConstConfig::PAI_TYPE_TONG,$buquemen_arr));

        return $bBuquemen;
    }
    //判断是否有19
    public function _is_have19($chair)
    {
        $bHave1_9 = false;
        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
        {
            if ($this->m_sPlayer[$chair]->card[$i][1] > 0||$this->m_sPlayer[$chair]->card[$i][9] > 0)
            {
                $bHave1_9 = true;
            }
        }

        //倒牌
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
        {
            //倒牌类型顺
            if (ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i])
            {
                //判断是否有19
                if (in_array($this->m_sStandCard[$chair]->first_card[$i] % 16, array(1, 7)))
                {
                    $bHave1_9 = true;
                }
            }
            else
            {
                //判断是否有19红中
                if (in_array($this->m_sStandCard[$chair]->first_card[$i], array(1,9,17,25,33,41)))
                {
                    $bHave1_9 = true;
                }
            }
        }
        return $bHave1_9;
    }
    //判断是否有中
    public function _is_havezhong($chair)
    {
        $bHavezhong = false;
        for ($i = ConstConfig::PAI_TYPE_FENG; $i <= ConstConfig::PAI_TYPE_FENG; $i++)
        {
            if ($this->m_sPlayer[$chair]->card[$i][5] > 0)
            {
                $bHavezhong = true;
            }
        }
        //倒牌
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
        {
            //倒牌类型顺
            if (ConstConfig::DAO_PAI_TYPE_SHUN != $this->m_sStandCard[$chair]->type[$i])
            {
                //判断是否有19红
                if (in_array($this->m_sStandCard[$chair]->first_card[$i],array(53)))
                {
                    $bHavezhong = true;
                }
            }
        }
        return $bHavezhong;
    }
    //判断边
    public function _is_bian($chair,$huCard_type,$huCard_key)
    {
        $bBian = false;
        //边
        if (($huCard_type != ConstConfig::PAI_TYPE_FENG && $huCard_key < 8
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key + 1] > 0
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key + 2] > 0))
        {
            if ($this->_judge_32type($chair, array($huCard_key, $huCard_key + 1, $huCard_key + 2),$huCard_type)   )
            {
                $bBian = true;
            }

        }
        if ($huCard_type != ConstConfig::PAI_TYPE_FENG && $huCard_key > 2
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key - 1] > 0
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key - 2] > 0
        )
        {
            if ($this->_judge_32type($chair, array($huCard_key, $huCard_key - 1, $huCard_key - 2),$huCard_type)   )
            {
                $bBian = true;
            }

        }
        return $bBian;
    }
    //判断对倒
    public function _is_duidao($chair,$huCard_type,$huCard_key)
    {
        $bDuidao = false;
        //对倒
        if ($this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key] >= 3)
        {
            if ($this->_judge_32type($chair, array($huCard_key,$huCard_key,$huCard_key),$huCard_type))
            {
                $bDuidao = true;
            }
        }
        return $bDuidao;
    }
    //判断夹胡(12胡3,89胡7算夹胡)
    public function _is_jiahu($chair,$huCard_type,$huCard_key)
    {
        $bJiahu = false;
        if ($huCard_type != ConstConfig::PAI_TYPE_FENG
            && $huCard_key > 1
            && $huCard_key < 9
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key + 1] > 0
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key - 1] > 0
        )
        {
            if ($this->_judge_32type($chair, array($huCard_key,$huCard_key + 1,$huCard_key - 1),$huCard_type))
            {
                $bJiahu = true;
            }

        }
        //如果牌型为1,2胡3或者8,9胡7，这个要显示夹，2番
        if ($huCard_type != ConstConfig::PAI_TYPE_FENG
            && $huCard_key == 3
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key - 1] > 0
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key - 2] > 0
        )
        {
            if ($this->_judge_32type($chair, array(1,2,3),$huCard_type))
            {
                $bJiahu = true;
            }
        }
        if ($huCard_type != ConstConfig::PAI_TYPE_FENG
            && $huCard_key == 7
                && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key + 1] > 0
                && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key + 2] > 0
        )
        {
            if ($this->_judge_32type($chair, array(7,8,9),$huCard_type))
            {
                $bJiahu = true;
            }
        }
        return $bJiahu;
    }
    //判断是否只有胡的那张牌是1或者9
    public function _is_hu19($chair,$huCard_type,$huCard_key)
    {
        $bHu19 = false;
        $hu19_arr = array();
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
        {
            //倒牌类型顺
            if (ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i])
            {
                //判断是否有19红
                if (in_array($this->m_sStandCard[$chair]->first_card[$i] % 16, array(1, 7)))
                {
                    $hu19_arr[] = 255;
                }
            }
            else
            {
                //判断是否有19红中
                if (in_array($this->m_sStandCard[$chair]->first_card[$i] % 16, array(1, 9)))
                {
                    $hu19_arr[] = 255;
                }
            }
        }
        //胡19
        if($huCard_type != ConstConfig::PAI_TYPE_FENG
            && $huCard_key == 1
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key] == 1
        )
        {
            $hu19_arr[] = $huCard_type;
            if ($this->m_sPlayer[$chair]->card[$huCard_type][9] >= 1)
            {
                $hu19_arr[] = $huCard_type;
            }
            for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
            {
                if ($i == $huCard_type)
                {
                    continue;
                }
                if ($this->m_sPlayer[$chair]->card[$i][1] >= 1 || $this->m_sPlayer[$chair]->card[$i][9] >= 1)
                {
                    $hu19_arr[] = $i;
                }
            }
        }
        //胡19
        if($huCard_type != ConstConfig::PAI_TYPE_FENG
            && $huCard_key == 9
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key] == 1
        )
        {
            $hu19_arr[] = $huCard_type;
            if ($this->m_sPlayer[$chair]->card[$huCard_type][1] >= 1)
            {
                $hu19_arr[] = $huCard_type;
            }
            for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++)
            {
                if ($i == $huCard_type)
                {
                    continue;
                }
                if ($this->m_sPlayer[$chair]->card[$i][1] >= 1 || $this->m_sPlayer[$chair]->card[$i][9] >= 1)
                {
                    $hu19_arr[] = $i;
                }
            }
        }
        $bHu19 = (count($hu19_arr) == 1 && current($hu19_arr) == $huCard_type);
        return $bHu19;
    }
    //判断特胡(设特修改)
    public function _is_tehu($chair,$huCard_type,$huCard_key,$bDuidao,$bJiahu,$bHu19,$bHavezhong)
    {
        $bTehu = false;
        $bHupai4zhang = false;
        //特胡判断(牌为倒牌)
        for ($i = 0; $i < $this->m_rule->player_count; $i++)
        {
            for ($j = 0; $j < $this->m_sStandCard[$i]->num; $j++)
            {
                if (ConstConfig::DAO_PAI_TYPE_KE == $this->m_sStandCard[$i]->type[$j])
                {
                    if ($this->m_sStandCard[$i]->first_card[$j] == $this->m_HuCurt[$chair]->card)
                    {
                        if ($bJiahu||($bHu19&&!$bHavezhong))
                        {
                            $bTehu = true;
                        }
                    }
                }
            }
        }
        //手牌四张
        if ($this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key] == 4)
        {
            $bHupai4zhang = true;
        }
        if ($bHupai4zhang)
        {
            if ($bJiahu && $bDuidao)
            {
                $bTehu = true;
            }
        }
        if($this->m_sQiangGang->mark)
        {
            if($this->m_HuCurt[$chair]->card==$this->m_sQiangGang->card)
            {
                if ($bJiahu||($bHu19&&!$bHavezhong))
                {
                    $bTehu = true;
                    //将设特的直杠修改回刻字
                    if ($this->m_she_te->mark)
                    {
                        for ($i = 0; $i < $this->m_sStandCard[$this->m_she_te->gang_chair]->num; $i ++)
                        {
                            if ($this->m_sStandCard[$this->m_she_te->gang_chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_MINGGANG
                                && $this->m_sStandCard[$this->m_she_te->gang_chair]->card[$i] == $this->m_sOutedCard->card)
                            {
                                $this->m_sStandCard[$this->m_she_te->gang_chair]->type[$i] = ConstConfig::DAO_PAI_TYPE_KE;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $bTehu;
    }
    //判断单吊
    public function _is_dandiao($chair,$huCard_type,$huCard_key,$jiang_type)
    {
        $bDandiao = false;
        //单吊
        if ($jiang_type==$huCard_type
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key] == 2
        )
        {
            if ($this->_judge_32type($chair, array($huCard_key,$huCard_key),$huCard_type))
            {
                //是不是只听一张将牌
                if ($this->_is_dantingjiang($chair,$huCard_type))
                {
                    $bDandiao = true;
                }
            }
        }
        return $bDandiao;
    }

    private function _log($class,$line,$title,$log)
    {
        /*$str = "类:$class 行号:$line\r\n";
        echo $str;
        var_dump($title);
        var_dump($log);*/
    }

}
