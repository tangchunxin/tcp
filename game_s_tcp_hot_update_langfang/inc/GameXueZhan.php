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

class GameXueZhan extends BaseGame
{
	const GAME_TYPE = 262;

	//-----------换三张方式--------------
	const HUAN_3_CLOCKWISE = 1;	//顺时针
	const HUAN_3_ANTICLOCKWISE  = 2;	//逆时针
	const HUAN_3_CROSS   = 3;	//交叉

	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_PINGHU = 21; //平胡 0番
	const HU_TYPE_PENGPENGHU = 22; //碰碰胡 1番
	const HU_TYPE_QINGYISE = 23; //清一色 2番
	const HU_TYPE_YAOJIU = 24; //幺九 3番
	const HU_TYPE_QIDUI = 25; //七对 2番

	const HU_TYPE_QING_PENG = 26; //清碰碰胡 3番
	const HU_TYPE_JIANG_PENG = 27; //将碰3番
	const HU_TYPE_LONG_QIDUI = 28; //龙七对 3番
	const HU_TYPE_QING_QIDUI = 29; //清七对 4番
	const HU_TYPE_QING_YAOJIU = 30; //清幺九 5番

	const HU_TYPE_QINGLONG_QIDUI = 31; //青龙七对 5番
	const HU_TYPE_JIANG_QIDUI = 32; //将七对 4番
	const HU_TYPE_FENGDING_TYPE_INVALID  = 0; //错误

	//－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_TIANHU = 61; //天胡 3番
	const ATTACHED_HU_DIHU = 62; //地胡 2番
	const ATTACHED_HU_RENHU = 63; //人和 2番
	const ATTACHED_HU_ZIMOFAN = 64; //自摸加番 1番
	const ATTACHED_HU_GANGKAI = 65; //杠开 1番

	const ATTACHED_HU_GANGPAO = 66; //杠炮 1番
	const ATTACHED_HU_QIANGGANG = 67; //抢杠 1番
	const ATTACHED_HU_GEN = 68; //根 1番
	const ATTACHED_HU_2GEN = 69; //2根 2番
	const ATTACHED_HU_3GEN = 70; //3根 3番
	const ATTACHED_HU_4GEN = 71; //4根 4番
	const ATTACHED_HU_GANG = 72; //杠 1番
	const ATTACHED_HU_JINGOU = 73; //金钩 1番

	const ATTACHED_HU_HAIDIHU =74; //海底胡 1番
	const ATTACHED_HU_HAIDIPAO = 75; //海底炮 1番
	const ATTACHED_HU_MENG_QING = 76; //门清 1番
	const ATTACHED_HU_ZHONGZHANG = 77; //中张 1番

	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 2;                 // 直杠分
	const M_ANGANG_SCORE = 2;                  // 暗杠分
	const M_WANGANG_SCORE = 1;                 // 弯杠分

	public static $hu_type_arr = array(
	self::HU_TYPE_PINGHU=>[self::HU_TYPE_PINGHU, 0, '平胡']
	,self::HU_TYPE_PENGPENGHU=>[self::HU_TYPE_PENGPENGHU, 1, '对对胡']
	,self::HU_TYPE_QINGYISE=>[self::HU_TYPE_QINGYISE, 2, '清一色']

	,self::HU_TYPE_QIDUI=>[self::HU_TYPE_QIDUI, 2, '七对']
	,self::HU_TYPE_YAOJIU=>[self::HU_TYPE_YAOJIU, 3, '全幺九']
	,self::HU_TYPE_QING_PENG=>[self::HU_TYPE_QING_PENG, 3, '清对']
	,self::HU_TYPE_JIANG_PENG=>[self::HU_TYPE_JIANG_PENG, 3, '将对']
	,self::HU_TYPE_LONG_QIDUI=>[self::HU_TYPE_LONG_QIDUI, 3, '龙七对']

	,self::HU_TYPE_QING_QIDUI=>[self::HU_TYPE_QING_QIDUI, 4, '清七对']
	,self::HU_TYPE_JIANG_QIDUI=>[self::HU_TYPE_JIANG_QIDUI, 4, '将七对']
	,self::HU_TYPE_QING_YAOJIU=>[self::HU_TYPE_QING_YAOJIU, 5, '清幺九']
	,self::HU_TYPE_QINGLONG_QIDUI=>[self::HU_TYPE_QINGLONG_QIDUI, 5, '清龙七对']

	);

	public static $attached_hu_arr = array(
	self::ATTACHED_HU_TIANHU=>[self::ATTACHED_HU_TIANHU, 1, '天胡']
	,self::ATTACHED_HU_DIHU=>[self::ATTACHED_HU_DIHU, 1, '地胡']
	,self::ATTACHED_HU_RENHU=>[self::ATTACHED_HU_RENHU, 1, '人胡']
	,self::ATTACHED_HU_ZIMOFAN=>[self::ATTACHED_HU_ZIMOFAN, 1, '自摸加番']
	,self::ATTACHED_HU_GANGKAI=>[self::ATTACHED_HU_GANGKAI, 1, '杠上花']

	,self::ATTACHED_HU_GANGPAO=>[self::ATTACHED_HU_GANGPAO, 1, '杠上炮']
	,self::ATTACHED_HU_QIANGGANG=>[self::ATTACHED_HU_QIANGGANG, 1, '抢杠']
	,self::ATTACHED_HU_GEN=>[self::ATTACHED_HU_GEN, 1, '根']
	,self::ATTACHED_HU_2GEN=>[self::ATTACHED_HU_2GEN, 2, '2根']
	,self::ATTACHED_HU_3GEN=>[self::ATTACHED_HU_3GEN, 3, '3根']
	,self::ATTACHED_HU_4GEN=>[self::ATTACHED_HU_4GEN, 4, '4根']
	,self::ATTACHED_HU_GANG=>[self::ATTACHED_HU_GANG, 1, '杠']
	,self::ATTACHED_HU_JINGOU=>[self::ATTACHED_HU_JINGOU, 1, '金钩']

	,self::ATTACHED_HU_HAIDIHU=>[self::ATTACHED_HU_HAIDIHU, 1, '海底捞月']
	,self::ATTACHED_HU_HAIDIPAO=>[self::ATTACHED_HU_HAIDIPAO, 1, '海底炮']
	,self::ATTACHED_HU_MENG_QING=>[self::ATTACHED_HU_MENG_QING, 1, '门清']
	,self::ATTACHED_HU_ZHONGZHANG=>[self::ATTACHED_HU_ZHONGZHANG, 1, '中张']
	
	);

	public $m_huan_3_type;                  //换三张的种类
    public $m_huan_3_arr = array();         //换三张牌的数组
    public $m_sDingQue = array();           //定缺数组
    public $m_wHuaZhuScore = array();		// 花猪分数
    public $m_wDaJiaoScore = array();		// 大叫分数
    public $m_nCountHuaZhu;	                //花猪个数
    public $m_nCountDajiao;	                //大叫个数
    public $m_nDajiaoFan = array();
    public $m_nHuList = array();            //胡牌列表
    public $m_bMaxFan = array();	        //是否达到封顶番数
    public $m_nHuGiveUpFan = array();       //过手胡的番数
    public $m_nHuCan = array();             //过手胡可以胡
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
		$this->m_huan_3_type = 0 ;
        $this->m_nCountHuaZhu = 0;
        $this->m_nCountDajiao = 0;
        for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
        {
            $this->m_huan_3_arr[$i] = new Huan_3();
            $this->m_sDingQue[$i] = new Ding_que();
            $this->m_wHuaZhuScore[$i] = 0;
            $this->m_wDaJiaoScore[$i] = 0;
            $this->m_nDajiaoFan[$i] = 0;
            $this->m_nHuList[$i] = 0;
            $this->m_bMaxFan[$i] = false;
            $this->m_nHuGiveUpFan[$i] = -1;
            $this->m_nHuCan[$i] = false;
        }
        $this->player_score = array(0,0,0,0);
        $this->player_cup = array(0,0,0,0);

	}

	public function _open_room_sub($params)
	{
        $this->m_rule = new RuleXueZhan();

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
		//$params['rule']['allow_louhu'] = !isset($params['rule']['allow_louhu']) ? 1 : $params['rule']['allow_louhu'];
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
		$this->m_rule->set_num = $params['rule']['set_num'];
		$this->m_rule->min_fan = $params['rule']['min_fan'];
		$this->m_rule->top_fan = $params['rule']['top_fan'];
		$this->m_rule->is_circle = $params['rule']['is_circle'];

		//$this->m_rule->is_feng = $params['rule']['is_feng'];
		$this->m_rule->is_yipao_duoxiang = $params['rule']['is_yipao_duoxiang'];
		//$this->m_rule->is_chipai = $params['rule']['is_chipai'];
		//$this->m_rule->is_genzhuang = $params['rule']['is_genzhuang'];
		//$this->m_rule->is_paozi = $params['rule']['is_paozi'];
		//$this->m_rule->is_zhuang_fan = $params['rule']['is_zhuang_fan'];

		$this->m_rule->is_qingyise_fan = $params['rule']['is_qingyise_fan'];
		//$this->m_rule->is_ziyise_fan = $params['rule']['is_ziyise_fan'];
		//$this->m_rule->is_yitiaolong_fan = $params['rule']['is_yitiaolong_fan'];
		$this->m_rule->is_ganghua_fan = $params['rule']['is_ganghua_fan'];
		$this->m_rule->is_qidui_fan = $params['rule']['is_qidui_fan'];
		$this->m_rule->is_pengpenghu_fan = $params['rule']['is_pengpenghu_fan'];

		//$this->m_rule->is_wangang_1_lose = $params['rule']['is_wangang_1_lose'];
		//$this->m_rule->is_dianpao_bao = $params['rule']['is_dianpao_bao'];
		//$this->m_rule->is_wukui = $params['rule']['is_wukui'];
		//$this->m_rule->is_diaowuwan = $params['rule']['is_diaowuwan'];
		
		//$this->m_rule->is_zhongfabai_shun = $params['rule']['is_zhongfabai_shun'];
		//$this->m_rule->is_bian_zuan = $params['rule']['is_bian_zuan'];
		//$this->m_rule->is_za = $params['rule']['is_za'];
		$this->m_rule->pay_type = $params['rule']['pay_type'];
		
		$this->m_rule->cancle_clocker = $params['rule']['cancle_clocker'];
		//$this->m_rule->allow_louhu = $params['rule']['allow_louhu'];
		//$this->m_rule->qg_is_zimo = $params['rule']['qg_is_zimo'];
		$this->m_rule->score = $params['rule']['score'];
		$this->m_rule->is_score_field = $params['rule']['is_score_field'];

        if(!empty($this->m_rule->is_circle))
        {
            $this->m_rule->set_num = $this->m_rule->is_circle * $this->m_rule->player_count;		//局等于  人*圈
        }
        else
        {
            $this->m_rule->set_num = $params['rule']['set_num'];
        }

        $this->m_rule->zimo_rule = $params['rule']['zimo_rule'];
        $this->m_rule->dian_gang_hua = $params['rule']['dian_gang_hua'];
        $this->m_rule->is_change_3 = $params['rule']['is_change_3'];
        $this->m_rule->is_yaojiu_jiangdui =  $params['rule']['is_yaojiu_jiangdui'];
        $this->m_rule->is_menqing_zhongzhang = $params['rule']['is_menqing_zhongzhang'];
        $this->m_rule->is_tiandi_hu = $params['rule']['is_tiandi_hu'];

        /*var_dump($this->m_rule);*/

    }

    ///////////////////打牌前阶段////////////////////
    //开始玩
    public function on_start_game()
    {
        //时间
        $itime = time();
        //局数设置减一
        if(!empty($this->m_rule->is_circle) && $this->m_nChairBankerNext == $this->m_nChairBanker)
        {
            $this->m_nSetCount -= 1;
        }
        //初始化数据，非首局的时候还要相关处理
        $this->InitData();
        //开始时间
        $this->m_start_time = $itime;
        //局数加一
        $this->m_nSetCount += 1;
        //房间状态
        $this->m_room_state = ConstConfig::ROOM_STATE_GAMEING;
        //录像记录
        $this->_set_record_game(ConstConfig::RECORD_DEALER, $this->m_nChairBanker, 0, 0, intval(implode('', $this->m_dice)));

        //发牌
        $this->DealAllCardEx();

        //换三张
        if(!empty($this->m_rule->is_change_3))
        {
            $this->start_huan_3();
            return true;
        }

        //定缺
        if(true)
        {
            $this->start_ding_que();
            return true;

        }
    }

    //批量发牌
    public function DealAllCardEx()
    {
        $temp_card = 255;
        $this->WashCard();

        //$this->_deal_test_card();

        //给每人发13张牌,整合成每个用户发一圈牌(4张)
        $this->m_deal_card_arr = array(['', '', '', ''], ['', '', '', ''], ['', '', '', ''], ['', '', '', '']);
        for($i=0; $i<$this->m_rule->player_count ; $i++)
        {
            for($k=0; $k<ConstConfig::BASE_HOLD_CARD_NUM; $k++)
            {
                $temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];	//从牌缓冲区里那张牌
                $this->_list_insert($i, $temp_card);
                $this->m_deal_card_arr[intval($k/4)][$i] .= sprintf("%02d",$temp_card);
            }
        }
        $tmp_card_arr = $this->m_deal_card_arr;
        for ($n=0; $n <= 3; $n++)
        {
            $this->_set_record_game(ConstConfig::RECORD_DRAW_ALL, intval($tmp_card_arr[$n][0]), intval($tmp_card_arr[$n][1]), intval($tmp_card_arr[$n][2]), intval($tmp_card_arr[$n][3]));
        }
        //给庄家发第14张牌
        $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->m_nCardBuf[$this->m_nCountAllot++];
        $this->_set_record_game(ConstConfig::RECORD_DRAW, $this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
    }

    //服务器发送换3张指令
    public function start_huan_3()
    {
        //系统状态
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_HUAN_3;
        //换牌方式
        $huan_type_arr = array(self::HUAN_3_CLOCKWISE , self::HUAN_3_ANTICLOCKWISE , self::HUAN_3_CROSS );
        //如果人数小于等于3人换牌的方式只有顺时针和逆时针
        if($this->m_rule->player_count <= 3)
        {
            $huan_type_arr = array(self::HUAN_3_CLOCKWISE , self::HUAN_3_ANTICLOCKWISE);
        }
        //打乱数组
        shuffle($huan_type_arr);
        //取出交换方式
        $this->m_huan_3_type = $huan_type_arr[0];
        //unset交换数组
        unset($huan_type_arr);
        //发送消息
        for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
        {
            $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_HUAN3ING;
            //发消息
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
        }
    }
    //客户端换3张
    public function c_huan_3($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
        do {
            //参数检查
            if( empty($params['rid'])
                || empty($params['uid'])
                || !isset($params['huan_card']) || !is_array($params['huan_card']) || 3 != count($params['huan_card'])
            )
            {
                $return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
            }
            //状态检查
            if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_HUAN_3 || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state)
            {
                $return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__; break;
            }
            //换3张牌类型
            $huan_card_type = ConstConfig::PAI_TYPE_PAI_TYPE_INVALID;
            //换3张牌类型
            $card_type_arr = array();
            //换3张牌key
            $card_key_arr = array();
            //换3张牌key
            $card_key_2_arr = array();

            foreach ($params['huan_card'] as $card_item)
            {
                $huan_card_type = $this->_get_card_type($card_item);
                $card_type_arr[] = $huan_card_type;
                $card_key_arr[] = $card_item % 16;
            }
            if(1 < count(array_unique($card_type_arr)) || $huan_card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
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
            //循环玩家
            foreach ($this->m_room_players as $key => $room_user)
            {
                //当前玩家
                if($room_user['uid'] == $params['uid'])
                {
                    //判断玩家是否换牌
                    if ($this->m_huan_3_arr[$key]->card_arr)
                    {
                        $return_send['code'] = 4; $return_send['text'] = '您已经指定换的牌了'; $return_send['desc'] = __LINE__; break 2;
                    }
                    //当前玩家是庄家
                    if($this->m_nChairBanker == $key)
                    {
                        //将第14张的牌插入
                        $tmp_card_taken_now = $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now;
                        $this->_list_insert($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
                        $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = 0;
                    }
                    foreach ($card_key_2_arr as $tmp_key => $tmp_val)
                    {
                        //手牌不够换的牌(检查客户端发送的换牌手牌中是否含有)
                        if(empty($this->m_sPlayer[$key]->card[$huan_card_type][$tmp_key]) || $this->m_sPlayer[$key]->card[$huan_card_type][$tmp_key] < $tmp_val)
                        {
                            $return_send['code'] = 5; $return_send['text'] = '手牌不够换的'; $return_send['desc'] = __LINE__; break 3;
                        }
                    }
                    //庄家删除牌
                    if($this->m_nChairBanker == $key)
                    {
                        $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $tmp_card_taken_now;
                        $this->_list_delete($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
                    }
                    //具体操作
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

    public function start_ding_que()
    {
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_DING_QUE;
        for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
        {
            $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_DINGQUEING;
            //发消息
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
        }
    }
    //定缺
    public function c_ding_que($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__);
        do {
            if( empty($params['rid'])
                || empty($params['uid'])
                || !isset($params['que_card_type'])
                || !in_array($params['que_card_type'], array(ConstConfig::PAI_TYPE_WAN ,ConstConfig::PAI_TYPE_TIAO ,ConstConfig::PAI_TYPE_TONG ))
            )
            {
                $return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__; break;
            }

            if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_DING_QUE || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state)
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

    //游戏开始
    public function game_to_playing()
    {
        //状态设定
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD ;
        $this->m_chairCurrentPlayer = $this->m_nChairBanker;

        //状态变化发消息
        for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
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

	//--------------------------------------------------------------------------

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

                    //过手胡处理
                    if(4 == $params['type'] && empty($this->m_rule->allow_louhu))
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
                            $this->m_nHuGiveUpFan[$key] = $this->judge_fan($key);
                        }
                        $this->m_HuCurt[$key]->clear();
                        $this->_list_delete($key, $temp_card);
                    }

                    if($params['type'] != 0)
                    {
                        $this->_set_record_game(ConstConfig::RECORD_PASS, $key);
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

    //点炮胡,玩家自己选择是否胡
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

    //判断胡
    public function judge_hu($chair)
    {
        //花猪判断拦截
        if($this->m_sPlayer[$chair]->card[$this->m_sDingQue[$chair]->card_type][0] != 0)
        {
            return false;
        }

        //胡牌型
        $is_menqing = false;
        $is_zhongzhang = false;
        $gen_num = 0;
        $hu_type = $this->judge_hu_type($chair, $is_menqing, $is_zhongzhang, $gen_num);

        if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
        {
            return false;
        }
        //记录在全局数据
        $this->m_HuCurt[$chair]->method[0] = $hu_type;
        $this->m_HuCurt[$chair]->count = 1;

        //门清中张
        if(!empty($this->m_rule->is_menqing_zhongzhang))
        {
            if($is_menqing)
            {
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_MENG_QING);
            }
            if($is_zhongzhang)
            {
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZHONGZHANG);
            }
        }

        //天地胡处理
        if(!empty($this->m_rule->is_tiandi_hu))
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
        //金钩
        if($this->m_sStandCard[$chair]->num == 4)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_JINGOU);
        }

        //自摸加番
        if(!empty($this->m_rule->zimo_rule) == 1 && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZIMOFAN);
        }
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
        //海底胡(海底捞月)
        if($this->m_nCountAllot >= ConstConfig::BASE_CARD_NUM && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_HAIDIHU);
        }
        //海底炮
        if($this->m_nCountAllot >= ConstConfig::BASE_CARD_NUM && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_HAIDIPAO);
        }

        //附加胡处理
        /*for ($i = 0; $i<$gen_num; ++$i)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GEN);
        }
        for ($i=0; $i<$this->m_sStandCard[$chair]->num; ++$i)
        {
            if ($this->m_sStandCard[$chair]->type[$i] != ConstConfig::DAO_PAI_TYPE_KE)
            {
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GEN);
            }
        }*/
        for ($i=0; $i<$this->m_sStandCard[$chair]->num; ++$i)
        {
            if ($this->m_sStandCard[$chair]->type[$i] != ConstConfig::DAO_PAI_TYPE_KE)
            {
                $gen_num++;
            }
        }
        switch ($gen_num) {
            case 1:
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GEN);
                break;
            case 2:
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_2GEN);
                break;
            case 3:
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_3GEN);
                break;
            case 4:
                $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_4GEN);
                break;
            default:
                break;
        }


        return true;
    }

	//判断几番
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
                $fan_sum += self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
                $tmp_hu_desc .= ' '.self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2];
            }
        }

        $this->m_bMaxFan[$chair] = false;
        if (empty($this->m_rule->top_fan))
        {
            ;
        }
        else
        {
            if ($fan_sum > $this->m_rule->top_fan)
            {
                $fan_sum = $this->m_rule->top_fan;
                $this->m_bMaxFan[$chair] = true;
            }
        }
        if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
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

    public function judge_hu_type($chair, &$is_menqing, &$is_zhongzhang, &$gen_num)
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
        for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG ; $i++)
        {
            if(0 == $this->m_sPlayer[$chair]->card[$i][0])
            {
                continue;
            }
            $tmp_hu_data = &ConstConfig::$hu_data;
            $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));
            if(!isset($tmp_hu_data[$key]))
            {
                return self::HU_TYPE_FENGDING_TYPE_INVALID ;
            }
            else
            {
                $hu_list_val = ConstConfig::$hu_data[$key];
                //1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen
                $gen_arr[] = intval($hu_list_val/4096);
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
                $qing_arr[] = $i;	//ConstConfig::PAI_TYPE_WAN	...
                if(!empty($this->m_rule->is_yaojiu_jiangdui))
                {
                    $is258_arr[] = $hu_list_val & 4;
                    $yaojiu_arr[] = $hu_list_val & 2;
                }
                if(!empty($this->m_rule->is_menqing_zhongzhang))
                {
                    $menqing_arr[] = 1;
                    $zhongzhang_arr[] = $hu_list_val & 16;
                }
            }
        }

        //倒牌(只有刻字,明杠,暗杠)
        for($i=0; $i<$this->m_sStandCard[$chair]->num; $i++)
        {
            $pai_type = $this->_get_card_type($this->m_sStandCard[$chair]->first_card[$i]);
            $pai_key = $this->m_sStandCard[$chair]->first_card[$i]%16;

            //有倒牌没七对
            $qidui_arr[] = 0;
            $qing_arr[] = $pai_type;
            if(!empty($this->m_rule->is_yaojiu_jiangdui))
            {
                $is258_arr[] = intval(in_array($pai_key, [2,5,8]));
                $yaojiu_arr[] = intval(in_array($pai_key, [1,9]));
            }
            if(!empty($this->m_rule->is_menqing_zhongzhang))
            {
                $zhongzhang_arr[] = intval(!in_array($pai_key, [1,9]));
                $menqing_arr[] = (ConstConfig::DAO_PAI_TYPE_ANGANG == $this->m_sStandCard[$chair]->type[$i])? 1 : 0;
            }
            if(ConstConfig::DAO_PAI_TYPE_KE  == $this->m_sStandCard[$chair]->type[$i] && $this->m_sPlayer[$chair]->card[$pai_type][$pai_key] > 0)
            {
                //手牌，倒牌组合根
                $gen_arr[] = 1;
            }
            /*if (ConstConfig::DAO_PAI_TYPE_MINGGANG  == $this->m_sStandCard[$chair]->type[$i])
            {
                $gen_arr[] = 1;
            }*/
        }

        //记录根到全局数据
        $gen_num = array_sum($gen_arr);
        $bType32 = (32 == array_sum($jiang_arr));
        $bQiDui = !array_keys($qidui_arr, 0);
        $bQing = ( 1 == count(array_unique($qing_arr)));
        $bPengPeng = !array_keys($pengpeng_arr, 0);
        if(!empty($this->m_rule->is_yaojiu_jiangdui))
        {
            $bYaoJiu = !array_keys($yaojiu_arr, 0);
            $b258 = !array_keys($is258_arr, 0);
        }

        if(!empty($this->m_rule->is_menqing_zhongzhang))
        {
            $bMengQing = !array_keys($menqing_arr, 0);
            $bZhongZhang = !array_keys($zhongzhang_arr, 0);
        }

        //门清中张是附加番
        if($bMengQing)
        {
            $is_menqing = $bMengQing;
            //return ConstConfig::HU_TYPE_MENG_QING ;				//门清
        }
        if($bZhongZhang)
        {
            $is_zhongzhang = $bZhongZhang;
            //return ConstConfig::HU_TYPE_ZHONGZHANG ;				//中张
        }

        //
        if(!$bType32 && !$bQiDui)	//不是32牌型也不是7对
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID ;
        }
        else if ($bQiDui)				                    //判断七对，可能同时是32牌型
        {
            if ($bQing && $gen_num)
            {
                $gen_num--;
                return self::HU_TYPE_QINGLONG_QIDUI ;	    //青龙七对
            }
            else if ($bQing)
            {
                return self::HU_TYPE_QING_QIDUI ;		    //青七对
            }
            if($b258 && $gen_num)
            {
                $gen_num--;
                return self::HU_TYPE_JIANG_QIDUI ;		    //将七对，因为缺门必然是龙七对
            }
            else if ($gen_num)
            {
                $gen_num--;
                return self::HU_TYPE_LONG_QIDUI ;		    //龙七对
            }
            else
            {
                return self::HU_TYPE_QIDUI ;			    //七对
            }
        }
        if($bYaoJiu && $bQing)
        {
            return self::HU_TYPE_QING_YAOJIU ;			    //清幺九
        }
        if ($bPengPeng && $b258)
        {
            return self::HU_TYPE_JIANG_PENG ;				//将碰
        }
        if($bQing && $bPengPeng)
        {
            return self::HU_TYPE_QING_PENG ;				//清碰
        }
        if ($bYaoJiu)
        {
            return self::HU_TYPE_YAOJIU ;					//幺九
        }
        if($bQing)
        {
            return self::HU_TYPE_QINGYISE ;				    //清一色
        }
        if($bPengPeng)
        {
            return self::HU_TYPE_PENGPENGHU ;				//碰碰胡
        }

        return self::HU_TYPE_PINGHU ;	                    //平胡
    }

	//------------------------------------- 命令处理函数 -----------------------------------
    //处理定缺
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
            $record_temp=array(0,0,0,0);
            for ($i = 0; $i<$this->m_rule->player_count; ++$i)
            {
                $record_temp[$i] = $this->m_sDingQue[$i]->card_type;
            }
            //记录
            $this->_set_record_game(ConstConfig::RECORD_DINGQUE, $record_temp[0], $record_temp[1], $record_temp[2], $record_temp[3]);
            //开始打牌
            $this->game_to_playing();
        }
    }
    //处理换3张
    public function handle_huan_3($chair, $huan_card)
    {
        //换3张的数组存到用户的
        $this->m_huan_3_arr[$chair]->card_arr = $huan_card;
        //判断所有用户已操作
        for ($i = 0; $i<$this->m_rule->player_count; ++$i)
        {
            if (!$this->m_huan_3_arr[$i]->card_arr)
            {
                break;
            }
        }
        //
        if ($this->m_rule->player_count == $i)
        {

            //发消息播放换3的动画
            //$this->_send_cmd('s_act', array('cmd'=>'c_huan_3', 'huan_3_type'=>$this->m_huan_3_type), Game_cmd::SCO_ALL_PLAYER);

            $this->_list_insert($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
            $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = 0;

            //换牌的具体操作
            for ($i = 0; $i<$this->m_rule->player_count; ++$i)
            {
                if($this->m_huan_3_type == self::HUAN_3_ANTICLOCKWISE)
                {
                    $target_player = $this->_anti_clock($i, 1);
                }
                else if ($this->m_huan_3_type == self::HUAN_3_CLOCKWISE)
                {
                    $target_player = $this->_anti_clock($i, -1);
                }
                else
                {
                    $this->m_huan_3_type = self::HUAN_3_CROSS;
                    $target_player = $this->_anti_clock($i, 2);
                }

                $this->m_huan_3_arr[$target_player]->get_card_arr = $this->m_huan_3_arr[$i]->card_arr;

                foreach ($this->m_huan_3_arr[$i]->card_arr as $card_item)
                {
                    $this->_list_delete($i, $card_item);
                    $this->_list_insert($target_player, $card_item);
                }
            }

            $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->_find_14_card($this->m_nChairBanker);


            //记录
            $record_temp=array(0,0,0,0,0);
            $huan_card_type = ConstConfig::PAI_TYPE_PAI_TYPE_INVALID;

            for ($i = 0; $i<$this->m_rule->player_count; ++$i)
            {
                $card_key_str = '';
                foreach ($this->m_huan_3_arr[$i]->card_arr as $card_item)
                {
                    $huan_card_type = $this->_get_card_type($card_item);
                    $card_key_str .= ($card_item % 16);
                }
                $record_temp[$i] = $huan_card_type.$card_key_str;
            }
            $record_temp[4] = $this->m_huan_3_type;
            //例:12|0123|1123|2123|0|1
            $this->m_record_game[] = ConstConfig::RECORD_HUAN3.'|'.$record_temp[0].'|'.$record_temp[1].'|'.$record_temp[2].'|'.$record_temp[3].'|'.$record_temp[4];
            //var_dump($this->m_record_game);

            unset($record_temp);
            unset($huan_card_type);
            unset($card_key_str);
            if (true)
            {
                $this->start_ding_que();
                return true;
            }
        }
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
        --$this->m_nNumTableCards[$this->m_sOutedCard->chair];
        if($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
        {
            unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
        }

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
        --$this->m_nNumTableCards[$this->m_sOutedCard->chair];
        if($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
        {
            unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
        }

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

        //清除出牌信息
        $this->m_sOutedCard->clear();

        ///收取杠分
        $nGangScore = 0;
        $nGangPao = 0;
        for ($i=0; $i<$this->m_rule->player_count; $i++)
        {
            if ($i == $chair)
            {
                continue;
            }

            if ($stand_count_after > 0 && $i == $this->m_sStandCard[$chair]->who_give_me[$stand_count_after-1])
            {
                $nGangScore = self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;


                $this->m_wGangScore[$i][$i] -= $nGangScore;
                $this->m_wGangScore[$chair][$chair] += $nGangScore;
                $this->m_wGangScore[$chair][$i] += $nGangScore;

                $nGangPao += $nGangScore;
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

                $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

                $nGangPao += $nGangScore;
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
            $tmp_c_hu_result = ( $this->m_is_ting_arr[$chair_next] && $this->judge_hu($chair_next) && ($this->m_nHuGiveUpFan[$chair_next] < $this->judge_fan($chair_next)));

            /*var_dump(__LINE__);
            var_dump($this->m_HuCurt[$chair_next]);
            var_dump($this->m_nHuGiveUpFan[$chair_next] < $this->judge_fan($chair_next));*/
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
            $this->m_nHuCan[$chair_next] = false;

            $tmp_distance = $this->_chair_to($chair, $chair_next);

            $this->m_bChooseBuf[$chair_next] = 1;
            $this->m_sPlayer[$chair_next]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
            $bHaveCmd = 1;

            //碰杠胡操作需要修改
            //判断是否有胡
            $this->_list_insert($chair_next, $this->m_sOutedCard->card);
            $this->m_HuCurt[$chair_next]->card = $this->m_sOutedCard->card;
            $this->m_HuCurt[$chair_next]->state = ConstConfig::WIN_STATUS_CHI_PAO;
            $tmp_c_hu_result = ( $this->m_is_ting_arr[$chair_next] && $this->judge_hu($chair_next) && ($this->m_nHuGiveUpFan[$chair_next] < $this->judge_fan($chair_next)));

            /*var_dump(__LINE__);
            var_dump($this->m_HuCurt[$chair_next]);
            var_dump($this->m_nHuGiveUpFan[$chair_next] < $this->judge_fan($chair_next));*/
            $this->m_HuCurt[$chair_next]->clear();
            $this->_list_delete($chair_next, $this->m_sOutedCard->card);
            if($tmp_c_hu_result)
            {
                //echo '111111111111111111111111';
                $this->m_nHuCan[$chair_next] = true;
            }
            else
            {

                //echo '22222222222222222222222222';
                if(!$this->_find_peng($chair_next)
                    &&	!$this->_find_zhi_gang($chair_next)
                    //&& (!empty($this->m_rule->is_chipai) && 1 == $tmp_distance && (!$this->_find_eat($chair_next,1) || !$this->_find_eat($chair_next,2) || !$this->_find_eat($chair_next,3)))
                )
                {
                    $this->m_sPlayer[$chair_next]->state = ConstConfig::PLAYER_STATUS_WAITING;
                    $tmp_arr[] = $chair_next;
                }
            }
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair_next), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair_next]['uid']);




            //////////////////////////////////////////////////////////////////////////
            if (false)
            {
                if($this->_find_peng($chair_next)
                    ||	$this->_find_zhi_gang($chair_next)
                    || (1 == $tmp_distance && !empty($this->m_rule->is_chipai) && ($this->_find_eat($chair_next,1) || $this->_find_eat($chair_next,2) || $this->_find_eat($chair_next,3)))
                )
                {
                    $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair_next), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair_next]['uid']);
                }
                else
                {
                    //判断是否有胡
                    $this->_list_insert($chair_next, $this->m_sOutedCard->card);
                    $this->m_HuCurt[$chair_next]->card = $this->m_sOutedCard->card;
                    $tmp_c_hu_result = ( $this->m_is_ting_arr[$chair_next] && $this->judge_hu($chair_next) && ($this->m_nHuGiveUpFan[$chair_next] < $this->judge_fan($chair_next)));
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
    //竞争选择
	public function HandleChooseResult($chair, $nCmdID, $eat_num = null)
	{
	    //更新玩家在线状态
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
                    $this->m_nHuGiveUpFan[$tmp_chair] = -1;
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

			//一炮多响和截胡判断
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


                //判断结束还是继续打牌
                if ($this->m_nCountHu >= $this->m_rule->player_count-1)
                {
                    $this->m_nEndReason = ConstConfig::END_REASON_HU;

                    $this->HandleSetOver();

                    return true;

                }
                else
                {
                    //给下家发牌
                    $next_chair = current($record_hu_chair);;
                    do
                    {
                        $next_chair = $this->_anti_clock($next_chair);
                    } while ($this->m_bChairHu[$next_chair]);

                    /*if ($next_chair == $this->m_chairCurrentPlayer)
                    {
                        echo ("find unHu player error, chair:". $chair."_".__LINE__);
                        return false;
                    }*/

                    $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD ;
                    $this->m_chairCurrentPlayer = $next_chair;

                    if(!($this->DealCard($next_chair)))
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
                        $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
                    }
                    return true;
                }
			}
			else // 给杠的玩家补张
			{

				$GangScore = 0;
				$nGangPao = 0;
				$nGangScore = self::M_WANGANG_SCORE *ConstConfig::SCORE_BASE;


				//弯杠多家出
				if(false)
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
            //处理胡牌(判断胡下一局庄家算分)
			$this->_do_c_hu($temp_card, $this->m_sOutedCard->chair, $bHaveHu, $record_hu_chair);
			
			$this->m_sGangPao->clear();
			
			if($bHaveHu)
			{
				if($record_hu_chair && is_array($record_hu_chair))
				{
                    $temp_order_hu = 0;
				    foreach ($record_hu_chair as $temp_order_hu_value)
                    {
                        $temp_order_hu =$temp_order_hu*10 + (pow(2, $temp_order_hu_value));
                    }
                    //记录胡牌信息
					$this->_set_record_game(ConstConfig::RECORD_HU, $record_hu_chair, $this->m_sOutedCard->card, $this->m_sOutedCard->chair,$temp_order_hu);
				}
				//$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
				//$this->m_currentCmd = 'c_hu';

				//置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
				--$this->m_nNumTableCards[$this->m_sOutedCard->chair];
				if($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
				{
					unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
				}

                //判断结束还是继续打牌
                if ($this->m_nCountHu >= $this->m_rule->player_count-1)
                {
                    $this->m_nEndReason = ConstConfig::END_REASON_HU;

                    $this->HandleSetOver();

                    return true;

                }
                else
                {
                    //给下家发牌
                    $next_chair = current($record_hu_chair);
                    do
                    {
                        $next_chair = $this->_anti_clock($next_chair);
                    } while ($this->m_bChairHu[$next_chair]);

                    /*if ($next_chair == $this->m_chairCurrentPlayer)
                    {
                        echo ("find unHu player error, chair:". $chair."_".__LINE__);
                        return false;
                    }*/

                    $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD ;
                    $this->m_chairCurrentPlayer = $next_chair;

                    if(!($this->DealCard($next_chair)))
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
                        $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
                    }
                    return true;
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
                    do
                    {
                        $next_chair = $this->_anti_clock($next_chair);
                    } while ($this->m_bChairHu[$next_chair]);

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

    //截胡和一炮多响
    public function _do_c_hu($temp_card, $dian_pao_chair, &$bHaveHu, &$record_hu_chair)
    {
        //牌类型
        $card_type = $this->_get_card_type($temp_card);
        if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
        {
            echo("错误的牌类型，发生在-> 抢杠".__LINE__.__CLASS__);
            return false;
        }

        //一炮多响
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
                $this->_list_insert($hu_chair, $temp_card);
                $this->m_HuCurt[$hu_chair]->state = ConstConfig::WIN_STATUS_CHI_PAO;

                $this->m_nChairDianPao = $dian_pao_chair;
                $this->m_HuCurt[$hu_chair]->card = $temp_card;
                $bHu = $this->judge_hu($hu_chair);
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

                    //下一局庄家
                    if(255 == $this->m_nChairBankerNext)
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

            $first_hu_chair = reset($tmp_hu_arr);
            //呼叫转移的处理
            if($this->m_sGangPao->mark)
            {
                if($this->m_sGangPao->chair != $first_hu_chair)
                {
                    /*var_dump($this->m_wGangScore);*/

                    //杠分回退

                    if ($this->m_sGangPao->type == ConstConfig::DAO_PAI_TYPE_MINGGANG)
                    {
                        $num = -1;
                        for($i=($this->m_sStandCard[$this->m_sGangPao->chair]->num -1); $i>=0 ; $i--)
                        {
                            if (ConstConfig::DAO_PAI_TYPE_MINGGANG  == $this->m_sStandCard[$this->m_sGangPao->chair]->type[$i])
                            {
                                $num =$i;
                                break;
                            }
                        }

                        $nGangScore = self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE;
                        if ($this->m_sStandCard[$this->m_sGangPao->chair]->who_give_me[$num] == $first_hu_chair)
                        {
                            $this->m_wGangScore[$this->m_sGangPao->chair][$this->m_sGangPao->chair] -= $nGangScore;
                            $this->m_wGangScore[$first_hu_chair][$first_hu_chair] += $nGangScore;
                            $this->m_wGangScore[$this->m_sGangPao->chair][$first_hu_chair] -= $nGangScore;
                        }
                        else
                        {
                            $this->m_wGangScore[$this->m_sGangPao->chair][$this->m_sGangPao->chair] -= $nGangScore;
                            $this->m_wGangScore[$this->m_sGangPao->chair][$this->m_sStandCard[$this->m_sGangPao->chair]->who_give_me[$num]] -= $nGangScore;
                            $this->m_wGangScore[$first_hu_chair][$first_hu_chair] += $nGangScore;
                            $this->m_wGangScore[$first_hu_chair][$this->m_sStandCard[$this->m_sGangPao->chair]->who_give_me[$num]] += $nGangScore;
                        }


                    }

                    if ($this->m_sGangPao->type == ConstConfig::DAO_PAI_TYPE_ANGANG)
                    {
                        for ($i=0; $i<$this->m_rule->player_count; ++$i)
                        {
                            if ($i == $this->m_sGangPao->chair || $i==$first_hu_chair)
                            {
                                continue;
                            }

                            if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU||in_array($i,$tmp_hu_arr))
                            {
                                $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;

                                $this->m_wGangScore[$this->m_sGangPao->chair][$this->m_sGangPao->chair] -= $nGangScore;
                                $this->m_wGangScore[$this->m_sGangPao->chair][$i] -= $nGangScore;
                                $this->m_wGangScore[$first_hu_chair][$first_hu_chair] += $nGangScore;
                                $this->m_wGangScore[$first_hu_chair][$i] += $nGangScore;

                            }
                        }
                        $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE;
                        $this->m_wGangScore[$first_hu_chair][$first_hu_chair] += $nGangScore;
                        $this->m_wGangScore[$this->m_sGangPao->chair][$this->m_sGangPao->chair] -= $nGangScore;
                        $this->m_wGangScore[$this->m_sGangPao->chair][$first_hu_chair] -= $nGangScore;

                    }
                    if ($this->m_sGangPao->type == ConstConfig::DAO_PAI_TYPE_WANGANG)
                    {
                        for ($i=0; $i<$this->m_rule->player_count; ++$i)
                        {
                            if ($i == $this->m_sGangPao->chair || $i==$first_hu_chair)
                            {
                                continue;
                            }

                            if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU || in_array($i,$tmp_hu_arr))
                            {
                                $nGangScore = self::M_WANGANG_SCORE * ConstConfig::SCORE_BASE;

                                $this->m_wGangScore[$this->m_sGangPao->chair][$this->m_sGangPao->chair] -= $nGangScore;
                                $this->m_wGangScore[$this->m_sGangPao->chair][$i] -= $nGangScore;
                                $this->m_wGangScore[$first_hu_chair][$first_hu_chair] += $nGangScore;
                                $this->m_wGangScore[$first_hu_chair][$i] += $nGangScore;

                            }
                        }
                        $nGangScore = self::M_WANGANG_SCORE * ConstConfig::SCORE_BASE;
                        $this->m_wGangScore[$first_hu_chair][$first_hu_chair] += $nGangScore;
                        $this->m_wGangScore[$this->m_sGangPao->chair][$this->m_sGangPao->chair] -= $nGangScore;
                        $this->m_wGangScore[$this->m_sGangPao->chair][$first_hu_chair] -= $nGangScore;

                    }
                    /*var_dump($this->m_wGangScore);*/
                }
            }

        }
    }

    //处理自摸胡
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

        //点杠花处理(直杠杠上开花算点炮)
        if(!empty($this->m_rule->dian_gang_hua) && $this->m_bHaveGang)
        {
            $stand_count = $this->m_sStandCard[$chair]->num;
            if(!empty($this->m_sStandCard[$chair]->type[$stand_count-1]) && $this->m_sStandCard[$chair]->type[$stand_count-1] == ConstConfig::DAO_PAI_TYPE_MINGGANG && $chair != $this->m_sStandCard[$chair]->who_give_me[$stand_count-1])
            {
                $this->m_HuCurt[$chair]->state = ConstConfig::WIN_STATUS_CHI_PAO;
                $this->m_nChairDianPao = $this->m_sStandCard[$chair]->who_give_me[$stand_count-1];
            }
            else
            {
                $this->m_HuCurt[$chair]->state = ConstConfig::WIN_STATUS_ZI_MO;
            }
        }
        else
        {
            $this->m_HuCurt[$chair]->state = ConstConfig::WIN_STATUS_ZI_MO;
        }

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
            if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
            {
                $this->m_wTotalScore[$chair]->n_jiepao += 1;
                $this->m_wTotalScore[$this->m_nChairDianPao]->n_dianpao += 1;
                $this->m_currentCmd = 'c_hu';
            }

            $this->m_chairSendCmd = $this->m_chairCurrentPlayer;

            $this->m_bChairHu[$chair] = true;
            $this->m_bChairHu_order[] = $chair;
            $this->m_nCountHu++;
            $this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;

            //去除胡牌者 card_taken_now  这个牌就只有在 m_HuCurt 有
            $this->m_sPlayer[$chair]->card_taken_now = 0;

            //算分
            $tmp_lost_chair = 255;
            if($this->m_nChairDianPao != 255 && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
            {
                $tmp_lost_chair = $this->m_nChairDianPao;
            }
            $this->ScoreOneHuCal($chair, $tmp_lost_chair);

            //下一局庄家
            if(255 == $this->m_nChairBankerNext)
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
            //写胡的录像
            $this->_set_record_game(ConstConfig::RECORD_ZIMO, $chair, $temp_card, $chair);

            //发消息播放胡的动画
            $this->_send_act($this->m_currentCmd, $chair);

            //判断结束还是继续打牌
            if ($this->m_nCountHu >= $this->m_rule->player_count-1)
            {
                $this->m_nEndReason = ConstConfig::END_REASON_HU;

                $this->HandleSetOver();

                return true;

            }
            else
            {
                //清除标记
                $this->m_sGangPao->clear();
                $this->m_bHaveGang = false;
                $this->m_sQiangGang->clear();

                $next_chair = $chair;
                do
                {
                    $next_chair = $this->_anti_clock($next_chair);
                } while ($this->m_bChairHu[$next_chair]);

                if ($next_chair == $chair)
                {
                    echo ("find unHu player error, chair:". $chair."_".__LINE__);
                    return false;
                }

                $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD ;
                $this->m_chairCurrentPlayer = $next_chair;

                if(!($this->DealCard($next_chair)))
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
                    $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
                }
                return true;
            }

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
			$data['m_sDingQue'] = $this->m_sDingQue;

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
                    $data['m_nHuCan'] = $this->m_nHuCan[$i];
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
        $fan_sum = $this->judge_fan($chair);
        $PerWinScore = 1<<$fan_sum;	//2的N次方
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

                $wWinScore = 0;
                $wWinScore += ConstConfig::SCORE_BASE * $PerWinScore;

                $this->m_wHuScore[$lost_chair] -= $wWinScore;
                $this->m_wHuScore[$chair] += $wWinScore;

                $this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
                $this->m_wSetScore[$chair] += $wWinScore;

                $this->m_HuCurt[$chair]->gain_chair[0]++;
                $this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]]=$lost_chair;

                if ($this->m_rule->zimo_rule == 0)
                {
                    $this->m_wHuScore[$lost_chair] -= ConstConfig::SCORE_BASE;
                    $this->m_wHuScore[$chair] += ConstConfig::SCORE_BASE;

                    $this->m_wSetLoseScore[$lost_chair] -= ConstConfig::SCORE_BASE;
                    $this->m_wSetScore[$chair] += ConstConfig::SCORE_BASE;
                }
            }
            return true;
        }

        // 吃炮者算分在此处！！
        else if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO)
        {
            $chairBaoPai = 255;
            /*//杠炮
            if($this->m_sGangPao->mark)
            {
                if($this->m_sGangPao->chair != $chair)
                {
                    //呼叫转移，如果是一炮多响就多次赔
                    $this->m_wGangScore[$chair][$chair] += $this->m_sGangPao->score;
                    $this->m_wGangScore[$lost_chair][$lost_chair] -= $this->m_sGangPao->score;

                    $this->m_wGangScore[$lost_chair][$chair] -= $this->m_sGangPao->score;
                }
            }*/
            $wWinScore = 0;
            $wWinScore +=ConstConfig::SCORE_BASE * $PerWinScore;

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

    //结束游戏
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
            $this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker, 1);
            //$this->m_nChairBankerNext = $this->m_nChairBanker;
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

    ///荒庄结算
    public function CalcNoCardScore()
    {
        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            $this->m_Score[$i]->clear();
        }

        //流局算分处理处理
        $this->ScoreNoCardBloodCal();

        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            $this->m_Score[$i]->score = $this->m_wSetScore[$i] + $this->m_wSetLoseScore[$i] + $this->m_wHuaZhuScore[$i] + $this->m_wDaJiaoScore[$i] + $this->m_wGangScore[$i][$i];
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
    //流局具体处理
    public function ScoreNoCardBloodCal()
    {
        for ($i=0; $i<$this->m_rule->player_count; ++$i)
        {
            if ($this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
            {
                if ($this->m_bFlee[$i])
                {
                    $this->m_nCountFlee++;
                }
            }
            //花猪
            else if ($this->m_sPlayer[$i]->card[$this->m_sDingQue[$i]->card_type][0] != 0)
            {
                $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_HUAZHU;
                $this->m_nCountHuaZhu++;

                $this->m_wTotalScore[$i]->n_huazhu += 1;
            }
            else
            {
                if($this->judge_da_jiao($i, $max_fan))
                {
                    /*var_dump(__LINE__);
                    var_dump('$i='.$i);
                    var_dump('最大番'.$max_fan);
                    var_dump($this->m_HuCurt[$i]);*/
                    $this->m_nDajiaoFan[$i] = $max_fan;
                    $this->m_HuCurt[$i]->state = ConstConfig::WIN_STATUS_HU_DA_JIAO;
                }
                else
                {
                    $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_DAJIAO;
                    $this->m_nCountDajiao++;

                    $this->m_wTotalScore[$i]->n_dajiao += 1;
                }
            }
        }

        /*if ($this->m_nCountHuaZhu == $this->m_rule->player_count)
        {
            for ($i=0; $i<$this->m_rule->player_count; ++$i)
            {
                $this->m_HuCurt[$i]->state = ConstConfig::WIN_STATUS_NOTHING ;
            }
            return;
        }*/

        for ($i=0; $i<$this->m_rule->player_count; ++$i)
        {
            if ($this->m_nCountHuaZhu>0)
            {
                //花猪赔所有非花猪人，包括已经胡牌的人，按最大番算
                if(empty($this->m_rule->top_fan))
                {
                    $top_times = 64;
                }
                else
                {
                    $top_times = 1<<($this->m_rule->top_fan);
                }

                if ($this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_HUAZHU)
                {
                    $this->m_wHuaZhuScore[$i] -= ConstConfig::SCORE_BASE*$top_times*($this->m_rule->player_count - $this->m_nCountHuaZhu - $this->m_nCountFlee);
                }
                else if(!$this->m_bFlee[$i])
                {
                    $this->m_wHuaZhuScore[$i] += ConstConfig::SCORE_BASE*$top_times*$this->m_nCountHuaZhu;
                }
            }

            if ($this->m_nCountDajiao!=0 && $this->m_nCountDajiao!=$this->m_rule->player_count - $this->m_nCountHuaZhu - $this->m_nCountHu)
            {
                //查大叫，只赔给赢大叫的人，已经胡的人不赔
                if ($this->m_HuCurt[$i]->state == ConstConfig::WIN_STATUS_HU_DA_JIAO)
                {
                    //$this->m_nNumFan[$i] = $this->m_nDajiaoFan[$i];
                    $lScore = ConstConfig::SCORE_BASE*(1<<($this->m_nDajiaoFan[$i]));
                    $this->m_wDaJiaoScore[$i] += $lScore*$this->m_nCountDajiao;

                    for ($j=0; $j<$this->m_rule->player_count; ++$j)
                    {
                        if ($this->m_sPlayer[$j]->state == ConstConfig::PLAYER_STATUS_DAJIAO)
                        {
                            $this->m_wDaJiaoScore[$j] -= $lScore;
                        }
                    }
                }
            }
            //花猪和大叫退回刮风下雨分
            if ($this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_DAJIAO || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_HUAZHU)
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

    //记录总分
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

            if ($this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_HUAZHU)
            {
                $this->m_hu_desc[$i] .= '花猪 ';
            }
            if ($this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_DAJIAO)
            {
                $this->m_hu_desc[$i] .= '大叫 ';
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

    /*public function judge_da_jiao($chair)
    {
        //定义所有牌数组
        $allCard=array(1,2,3,4,5,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,53);
        $fan = 0;
        foreach ($allCard as $key => $insertcard)
        {
            if($this->_list_insert($chair,$insertcard))
            {
                $this->m_HuCurt[$chair]->clear();
                if ($this->judge_hu($chair))
                {
                    $temp_fan =$this->judge_fan($chair);
                    if($temp_fan > $fan)
                    {
                        $fan = $temp_fan;
                    }
                }
                $this->_list_delete($chair, $insertcard);
            }
        }
        return $fan;

    }*/
	////////////////////////////其他///////////////////////////

    //洗牌
    public function WashCard()
    {
        if(!empty($this->m_rule->is_feng))
        {
            $this->m_nCardBuf = ConstConfig::ALL_CARD_136;
            $this->m_nAllCardNum = ConstConfig::BASE_CARD_NUM_FENG;
            if(defined("gf\\conf\\Config::TEST_PAI") && Config::TEST_PAI)
            {
                $this->m_nCardBuf = Config::ALL_CARD_136_TEST;
            }
        }
        else
        {
            $this->m_nCardBuf = ConstConfig::ALL_CARD_108;
            $this->m_nAllCardNum = ConstConfig::BASE_CARD_NUM;
            if(defined("gf\\conf\\Config::TEST_PAI") && Config::TEST_PAI)
            {
                $this->m_nCardBuf = Config::ALL_CARD_108_TEST;
            }
        }

        if(Config::WASHCARD)
        {
            shuffle($this->m_nCardBuf); shuffle($this->m_nCardBuf);	//为了测试 不洗牌
        }
    }

    //发牌
    public function DealCard($chair)
    {
        if ($this->m_bChairHu[$chair])	//未胡玩家发牌
        {
            if ($this->m_nCountHu >=$this->m_rule->player_count - 1)
            {
                return false;
            }
            $this->m_chairCurrentPlayer = $this->_anti_clock($chair);
            return $this->DealCard($this->m_chairCurrentPlayer);
        }

        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
            {
                $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_WAITING;
                $this->m_sPlayer[$i]->card_taken_now = 0;
            }
        }

        if(empty($this->m_nCardBuf[$this->m_nCountAllot]))				//没牌啦
        {
            //echo("没牌啦".__LINE__.__CLASS__);
            $this->m_nEndReason = ConstConfig::END_REASON_NOCARD;
            $this->HandleSetOver();
            return true;
        }

        $the_card = $this->m_nCardBuf[$this->m_nCountAllot];
        $this->m_nCountAllot++;

        $this->m_sPlayer[$chair]->card_taken_now = $the_card;

        $this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
        $this->m_bChooseBuf[$chair] = 1;

        $this->m_nHuGiveUpFan[$chair] = -1;	//重置过手胡
        $this->m_nHuGiveUp[$chair] = 0;	    //重置过手胡
        $this->m_nPengGiveUp[$chair] = 0;	//重置同圈过碰
        $this->_set_record_game(ConstConfig::RECORD_DRAW, $chair, $the_card, $chair);

        return true;
    }

    //找出第14张牌
    public function _find_14_card($chair)
    {
        if (true)
        {
            if ($this->m_sDingQue[$chair]->card_type != 255 && $this->m_sPlayer[$chair]->card[$this->m_sDingQue[$chair]->card_type][0]>0)
            {
                for($i=9; $i>0; $i--)
                {
                    if($this->m_sPlayer[$chair]->card[$this->m_sDingQue[$chair]->card_type][$i] > 0)
                    {
                        $fouteen_card = $this->_get_card_index($this->m_sDingQue[$chair]->card_type, $i);
                        $this->m_sPlayer[$chair]->card[$this->m_sDingQue[$chair]->card_type][$i] -= 1;
                        $this->m_sPlayer[$chair]->card[$this->m_sDingQue[$chair]->card_type][0] -= 1;
                        $this->m_sPlayer[$chair]->len -= 1;
                        return $fouteen_card;
                    }
                }
            }
        }
        $last_type = ConstConfig::PAI_TYPE_DRAGON;

        if(!empty($this->m_rule->is_kou_card))
        {
            foreach ($this->m_sPlayer[$chair]->kou_card_display as $value)
            {
                $this->_list_delete($chair, $value);
            }
        }

        if($this->m_hun_card != 0)
        {
            $hun_num = $this->_list_find($chair, $this->m_hun_card);
            $hun_type = $this->_get_card_type($this->m_hun_card);
            $hun_index = $this->m_hun_card % 16;
        }
        if(!empty($hun_num))
        {
            $this->m_sPlayer[$chair]->card[$hun_type][$hun_index] = 0;
            $this->m_sPlayer[$chair]->card[$hun_type][0] -= $hun_num;
            $this->m_sPlayer[$chair]->len -= $hun_num;
        }
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
            if(!empty($hun_num))
            {
                $this->m_sPlayer[$chair]->card[$hun_type][$hun_index] = $hun_num-1;
                $this->m_sPlayer[$chair]->card[$hun_type][0] += $hun_num-1;
                $this->m_sPlayer[$chair]->len += $hun_num-1;
                $fouteen_card = $this->m_hun_card;

                if(!empty($this->m_rule->is_kou_card))
                {
                    foreach ($this->m_sPlayer[$chair]->kou_card_display as $value)
                    {
                        $this->_list_insert($chair, $value);
                    }
                }

                return $fouteen_card;
            }
            else
            {
                echo ("竟然没有牌aaaaaaaas".__LINE__.__CLASS__ );
                return false;
            }
        }

        for($i=9; $i>0; $i--)
        {
            if($this->m_sPlayer[$chair]->card[$last_type][$i] > 0)
            {
                $fouteen_card = $this->_get_card_index($last_type, $i);
                $this->m_sPlayer[$chair]->card[$last_type][$i] -= 1;
                $this->m_sPlayer[$chair]->card[$last_type][0] -= 1;
                $this->m_sPlayer[$chair]->len -= 1;
                break;
            }
        }

        if(!empty($hun_num))
        {
            $this->m_sPlayer[$chair]->card[$hun_type][$hun_index] = $hun_num;
            $this->m_sPlayer[$chair]->card[$hun_type][0] += $hun_num;
            $this->m_sPlayer[$chair]->len += $hun_num;
        }

        if(!empty($this->m_rule->is_kou_card))
        {
            foreach ($this->m_sPlayer[$chair]->kou_card_display as $value)
            {
                $this->_list_insert($chair, $value);
            }
        }
        if(empty($fouteen_card))
        {
            return false;
        }

        return $fouteen_card;
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
            if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
            {
                echo("错误的牌类型，发生在JudgeDajiao1".__LINE__);
                return $is_hu;
            }
            $this->list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now); //整理完毕
            ///		m_sPlayer[$chair]->card_taken_now = 0;

            //此处效率低
            for ($i = ConstConfig::PAI_TYPE_WAN; $i<=ConstConfig::PAI_TYPE_TONG; ++$i)
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
        $card_type = ConstConfig::PAI_TYPE_PAI_TYPE_INVALID ;

        $this->m_nHuList[$chair] = [0];
        //查花猪
        if($this->m_sPlayer[$chair]->card[$this->m_sDingQue[$chair]->card_type][0] != 0)
        {
            return false;
        }
        for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_TONG; $i++)
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
                    /*var_dump($this->m_HuCurt[$chair]);*/
                }

                $this->m_HuCurt[$chair]->clear();

                $this->_list_delete($chair, $card);
            }
        }
        $this->m_nHuList[$chair][0] = $n;
    }
}
