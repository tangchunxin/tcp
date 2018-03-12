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

class GameFuJin extends BaseGame
{
	const GAME_TYPE = 160;


    //－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
    const HU_TYPE_PINGHU = 20;                  //平胡
    const HU_TYPE_BIAN = 21;                    //边
    const HU_TYPE_DUIDAO = 22;                  //对倒
    const HU_TYPE_DANDIAO = 23;                 //单吊
    const HU_TYPE_HUIDANDIAO = 24;              //会单吊
    const HU_TYPE_JIAHU = 25;                   //夹胡
    const HU_TYPE_HU19 = 26 ;                   //胡19
    const HU_TYPE_PIAOHU = 27;                  //飘胡

    const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   //错误

    //－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
    const ATTACHED_HU_SANJIAQING = 63;          //三家清

    //－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
    const M_ZHIGANG_SCORE = 1;                  //直杠  1分
    const M_ANGANG_SCORE =  2;                  //暗杠  2分
    const M_WANGANG_SCORE = 1;                  //弯杠  1分


    public static $hu_type_arr = array(
        self::HU_TYPE_PINGHU=>[self::HU_TYPE_PINGHU, 0, '胡']
    ,self::HU_TYPE_BIAN=>[self::HU_TYPE_BIAN, 1, '边']
    ,self::HU_TYPE_DUIDAO=>[self::HU_TYPE_DUIDAO, 1, '对倒']
    ,self::HU_TYPE_DANDIAO=>[self::HU_TYPE_DANDIAO, 2, '单吊']
    ,self::HU_TYPE_HUIDANDIAO=>[self::HU_TYPE_HUIDANDIAO, 2, '会单吊']
    ,self::HU_TYPE_JIAHU=>[self::HU_TYPE_JIAHU, 2, '夹胡']
    ,self::HU_TYPE_HU19=>[self::HU_TYPE_HU19, 2, '胡19']
    ,self::HU_TYPE_PIAOHU=>[self::HU_TYPE_PIAOHU, 4, '飘胡']

    );

    public static $attached_hu_arr = array(
        self::ATTACHED_HU_SANJIAQING=>[self::ATTACHED_HU_SANJIAQING, 4, '三家清']

    );
    //－－－－－－－－－－－－－钓鱼 －－－－－－－－－－－－－－－－－－－
    const YI_TIAO_YU = 2 ;
    const LIANG_TIAO_YU = 4 ;
    const SAN_TIAO_YU = 6 ;
    const SI_TIAO_YU = 8 ;
    const SHI_TIAO_YU = 20;

    public $m_hun_card = array();	        //混牌数组
    public $m_diao_yu;	                    //钓鱼结构
    public $m_diao_yu_score = array();	    //钓鱼得分
    public $m_hui_score = array();          //混分数组
    public $m_bKaiMen = array();            //开门数组

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
        $this->m_hun_card  = array();
        $this->m_diao_yu = new Diao_yu();
        for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
        {
            $this->m_diao_yu_score[$i] = 0;
            $this->m_hui_score[$i] = 0;
            $this->m_bKaiMen[$i] = false;
        }
	}

	public function _open_room_sub($params)
	{
        $this->m_rule = new RuleFuJin();

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
        $params['rule']['is_fanhun'] = !isset($params['rule']['is_fanhun'])? 1 : $params['rule']['is_fanhun'];;

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
		//$this->m_rule->allow_louhu = $params['rule']['allow_louhu'];
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

        $this->m_rule->is_fanhui = $params['rule']['is_fanhui'];
        $this->m_rule->is_fanhun = $params['rule']['is_fanhun'];
        $this->m_rule->is_piaohu = $params['rule']['is_piaohu'];
        $this->m_rule->is_xiadan = $params['rule']['is_xiadan'];
        $this->m_rule->is_diaoyu = $params['rule']['is_diaoyu'];



    }

    ///////////////////打牌前阶段////////////////////

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
        if(!empty($this->m_rule->is_fanhui))
        {
            $this->m_fan_hun_card = $this->m_nCardBuf[$this->m_nCountAllot++];
            $this->_get_fan_hun($this->m_fan_hun_card);
            $record_temp = 0;
            foreach ($this->m_hun_card as $item=>$value)
            {
                $record_temp=100*$record_temp+$value;
            }
            $this->_set_record_game(ConstConfig::RECORD_FANHUN, $this->m_nChairBanker, $this->m_fan_hun_card,$this->m_nChairBanker,$record_temp);

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

	//-------------------------------------------------------------------------

    //判断胡   ok
    public function judge_hu($chair, $is_fanhun = false)
    {
        //胡牌型
        $hu_type = $this->judge_hu_type_fanhun($chair,$is_fanhun);

        if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
        {
            $this->_log(__CLASS__,__LINE__,'不能胡牌!','');
            return false;
        }


        //记录在全局数据
        $this->m_HuCurt[$chair]->method[0] = $hu_type;
        $this->m_HuCurt[$chair]->count = 1;

        //三家清
        $sanjiaqing_arr=array();
        for($i = 0; $i < $this->m_rule->player_count; $i++)
        {
            if($i==$chair)
            {
                continue;
            }
            if ($this->m_bKaiMen[$i] == false)
            {
                $sanjiaqing_arr[]=1;
            }
        }
        if(count($sanjiaqing_arr) == 3)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_SANJIAQING);
        }

        return true;
    }

    //判断翻混  ok
    public function judge_hu_type_fanhun($chair,$is_fanhun = false)
    {
        //---------------------判断混子数以及红中赖子的个数-----------------------------------------
        $fanhun_num = $this->_get_fan_hun_num($chair);

        //---------------------判断去掉混子剩下的牌满足三色和19-----------------------------------------
        //12、13、14个会不能胡，不满足3色；
        $fanhun_arr = array(); //每个混子的数量
        //去掉所有混子
        $this->_del_hun_card($chair,$fanhun_arr);


        //判断胡牌条件
        //19中，三色。红中不是会的时候可以替代19
        $bBuquemen = $this->_is_buquemen($chair);
        $bHave1_9 = $this->_is_have19($chair);
        $bHavezhong = $this->_is_havezhong($chair);

        //拿回所有混子
        $this->_add_hun_card($chair,$fanhun_arr);

        if(!$bBuquemen || !$bHavezhong&&!$bHave1_9)
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }

        //判断飘胡
        if(!empty($this->m_rule->is_piaohu))
        {
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            $this->_del_hun_card($chair,$fanhun_arr);
            $is_pengpenghu = $this->_is_pengpeng($chair,$fanhun_num);
            //拿回所有混子
            $this->_add_hun_card($chair,$fanhun_arr);
            if ($is_pengpenghu)
            {
                return self::HU_TYPE_PIAOHU;
            }
        }

        if ( (14-$this->m_sStandCard[$chair]->num*3) ==$fanhun_num)
        {
            $all_hunzi = true;
        }
        else
        {
            $all_hunzi = false;
        }


        //----------------------------------------判断(已经满足三色和19)-------------------------------
        //9、10、11个会，直接组成32牌型，比如4个刻字+1副将牌；
        if ($fanhun_num>8)
        {
            return $this->judge_hun_type($chair);
        }



        if($fanhun_num==0)
        {
            return $this->judge_hu_type($chair,$this->m_HuCurt[$chair]->card,$bHavezhong);
        }

        if($fanhun_num == 1)
        {
            //删除混子牌
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            $this->_del_hun_card($chair,$fanhun_arr);
            if (in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card))
            {
                $result=$this->judge_hu_type_hucard_hun($chair,$bHavezhong);
            }
            else
            {
                $result=$this->judge_hu_type_fanhun_one($chair,$bHavezhong);
            }
            //拿回所有混子
            $this->_add_hun_card($chair,$fanhun_arr);
            return $result;
        }
        //如果只有一个混且为胡牌的
        if($fanhun_num == 2)
        {
            //删除混子牌
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            $this->_del_hun_card($chair,$fanhun_arr);
            $result=$this->judge_hu_type_fanhun_two($chair,$bHavezhong);
            //恢复删除牌
            //拿回所有混子
            $this->_add_hun_card($chair,$fanhun_arr);
            return $result;
        }
        //如果只有一个混且为胡牌的
        if($fanhun_num == 3)
        {
            //删除混子牌
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            $this->_del_hun_card($chair,$fanhun_arr);
            $result=$this->judge_hu_type_fanhun_three($chair,$bHavezhong);
            //恢复删除牌
            //拿回所有混子
            $this->_add_hun_card($chair,$fanhun_arr);
            return $result;
        }
        if($fanhun_num == 4)
        {
            //删除混子牌
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            $this->_del_hun_card($chair,$fanhun_arr);
            $result=$this->judge_hu_type_fanhun_four($chair,$bHavezhong);
            //恢复删除牌
            //拿回所有混子
            $this->_add_hun_card($chair,$fanhun_arr);
            return $result;
        }
        if($fanhun_num == 5)
        {
            //删除混子牌
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            $this->_del_hun_card($chair,$fanhun_arr);
            $result=$this->judge_hu_type_fanhun_five($chair,$bHavezhong,$all_hunzi);
            //恢复删除牌
            //拿回所有混子
            $this->_add_hun_card($chair,$fanhun_arr);
            return $result;
        }
        if($fanhun_num == 6)
        {
            //删除混子牌
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            $this->_del_hun_card($chair,$fanhun_arr);
            $result=$this->judge_hu_type_fanhun_six($chair,$bHavezhong);
            //恢复删除牌
            //拿回所有混子
            $this->_add_hun_card($chair,$fanhun_arr);
            return $result;
        }
        if($fanhun_num == 7)
        {
            //删除混子牌
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            $this->_del_hun_card($chair,$fanhun_arr);
            $result=$this->judge_hu_type_fanhun_seven($chair,$bHavezhong);
            //恢复删除牌
            //拿回所有混子
            $this->_add_hun_card($chair,$fanhun_arr);
            return $result;
        }
        if($fanhun_num == 8)
        {
            //删除混子牌
            $fanhun_arr = array(); //每个混子的数量
            //去掉所有混子
            $this->_del_hun_card($chair,$fanhun_arr);
            $result=$this->judge_hu_type_fanhun_eight($chair);
            //恢复删除牌
            //拿回所有混子
            $this->_add_hun_card($chair,$fanhun_arr);
            return $result;
        }
        return self::HU_TYPE_FENGDING_TYPE_INVALID;

    }

    public function judge_hu_type_fanhun_eight($chair)
    {
        //胡的那张牌的类型和键值
        $huCard_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
        $huCard_key = $this->m_HuCurt[$chair]->card % 16;
        $single_card_num = 0;
        //计算单牌
        if($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_FENG][5] == 1)
        {
            $single_card_num +=1;
        }
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
                        }
                    }
                }
            }
        }
        if($single_card_num >= 5)
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        if (!in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card) && $single_card_num == 4)
        {
            if ($huCard_key>=1 && $huCard_key<7 && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key+2]>=1)
            {
                return self::HU_TYPE_BIAN;
            }
            if ($huCard_key<=9 && $huCard_key>3 && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key-2]>=1)
            {
                return self::HU_TYPE_BIAN;
            }
            return $this->judge_hun_type($chair);
        }
        else
        {
            return $this->judge_hun_type($chair);
        }
    }
    /*public function judge_hu_type_fanhun_seven($chair)
    {
        //胡的那张牌的类型和键值
        $huCard_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
        $huCard_key = $this->m_HuCurt[$chair]->card % 16;
        $single_card_num = 0;
        //计算单牌
        if($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_FENG][5] == 1)
        {
            $single_card_num +=1;
        }
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
                        }
                    }
                }
            }
        }
        if($single_card_num >4)
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        if (!in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card) && $single_card_num == 4)
        {
            if ($huCard_key>=1 && $huCard_key<7 && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key+2]>=1 && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key+1]>=1)
            {
                return self::HU_TYPE_BIAN;
            }
            if ($huCard_key<=9 && $huCard_key>3 && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key-2]>=1 && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key-2])
            {
                return self::HU_TYPE_BIAN;
            }
            if ($this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key]==3)
            {
                return self::HU_TYPE_DUIDAO;
            }
            return $this->judge_hun_type($chair);
        }
        if (!in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card) && $single_card_num == 3)
        {
            if ($this->m_sPlayer[$chair]->card[$huCard_type][1]>=1 && $this->m_sPlayer[$chair]->card[$huCard_type][3]>=1 && $this->m_sPlayer[$chair]->card[$huCard_type][5]>=1 && $this->m_sPlayer[$chair]->card[$huCard_type][7]>=1)
            {
                if(in_array($huCard_key,array(1,3,5,7)))
                {
                    return self::HU_TYPE_BIAN;
                }
            }
            if ($this->m_sPlayer[$chair]->card[$huCard_type][2]>=1 && $this->m_sPlayer[$chair]->card[$huCard_type][4]>=1 && $this->m_sPlayer[$chair]->card[$huCard_type][6]>=1 && $this->m_sPlayer[$chair]->card[$huCard_type][8]>=1)
            {
                if(in_array($huCard_key,array(2,4,6,8)))
                {
                    return self::HU_TYPE_BIAN;
                }
            }
            if ($this->m_sPlayer[$chair]->card[$huCard_type][3]>=1 && $this->m_sPlayer[$chair]->card[$huCard_type][5]>=1 && $this->m_sPlayer[$chair]->card[$huCard_type][7]>=1 && $this->m_sPlayer[$chair]->card[$huCard_type][9]>=1)
            {
                if(in_array($huCard_key,array(3,5,7,9)))
                {
                    return self::HU_TYPE_BIAN;
                }
            }
            if ()
        }
        else
        {
            $this->judge_hun_type($chair);
        }
    }*/
    public function judge_hu_type_fanhun_seven($chair,$bHavezhong,$lastcard = 0)
    {
        //胡牌数组
        $hu_type=array();
        for ($i=$lastcard; $i <28 ; $i++)
        {
            $value=ConstConfig::ALL_CARD_28[$i];
            $Card_type = $this->_get_card_type($value);
            $Card_index = $value % 16;
            //不靠张的跳过
            $before_j = $Card_index - 1;
            $next_j = $Card_index+1;
            $nextnext_j = $Card_index+2;
            $before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$Card_type][$before_j] : 0 ;
            $next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$next_j] : 0 ;
            $nextnext_count = $nextnext_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$nextnext_j] : 0 ;
            if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$Card_type][$Card_index] && 0 == $next_count && $nextnext_count==0)
            {
                continue;
            }
            if($this->_list_insert($chair, $value))
            {

                $temp_hu_type=$this->judge_hu_type_fanhun_six($chair,$bHavezhong,$i);

                if ($temp_hu_type!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    $hu_type[]=$temp_hu_type;
                }
                $this->_list_delete($chair, ConstConfig::ALL_CARD_28[$i]);
                if (end($hu_type)==self::HU_TYPE_JIAHU||end($hu_type)==self::HU_TYPE_HU19||end($hu_type)==self::HU_TYPE_DANDIAO)
                {
                    break;
                }
            }
        }
        //返回
        if (empty($hu_type))
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        else
        {
            return max($hu_type);
        }
    }
    public function judge_hu_type_fanhun_six($chair,$bHavezhong,$lastcard = 0)
    {
        //胡牌数组
        $hu_type=array();
        for ($i=$lastcard; $i <28 ; $i++)
        {
            $value=ConstConfig::ALL_CARD_28[$i];
            $Card_type = $this->_get_card_type($value);
            $Card_index = $value % 16;
            //不靠张的跳过
            $before_j = $Card_index - 1;
            $next_j = $Card_index+1;
            $nextnext_j = $Card_index+2;
            $before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$Card_type][$before_j] : 0 ;
            $next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$next_j] : 0 ;
            $nextnext_count = $nextnext_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$nextnext_j] : 0 ;
            if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$Card_type][$Card_index] && 0 == $next_count && $nextnext_count==0)
            {
                continue;
            }
            if($this->_list_insert($chair, $value))
            {

                $temp_hu_type=$this->judge_hu_type_fanhun_five($chair,$bHavezhong,false,$i);

                if ($temp_hu_type!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    $hu_type[]=$temp_hu_type;
                }
                $this->_list_delete($chair, ConstConfig::ALL_CARD_28[$i]);
                if (end($hu_type)==self::HU_TYPE_JIAHU||end($hu_type)==self::HU_TYPE_HU19||end($hu_type)==self::HU_TYPE_DANDIAO)
                {
                    break;
                }
            }
        }
        //返回
        if (empty($hu_type))
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        else
        {
            return max($hu_type);
        }
    }
    public function judge_hu_type_fanhun_five($chair,$bHavezhong,$all_hunzi,$lastcard = 0)
    {
        //胡牌数组
        $hu_type=array();
        if ($all_hunzi)
        {
            for ($i=$lastcard; $i <28 ; $i++)
            {
                $value=ConstConfig::ALL_CARD_28[$i];
                $Card_type = $this->_get_card_type($value);
                $Card_index = $value % 16;
                if($this->_list_insert($chair, $value))
                {

                    $temp_hu_type=$this->judge_hu_type_fanhun_four($chair,$bHavezhong,$i);

                    if ($temp_hu_type!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                    {
                        $hu_type[]=$temp_hu_type;
                    }
                    $this->_list_delete($chair, ConstConfig::ALL_CARD_28[$i]);
                    if (end($hu_type)==self::HU_TYPE_JIAHU||end($hu_type)==self::HU_TYPE_HU19||end($hu_type)==self::HU_TYPE_DANDIAO)
                    {
                        break;
                    }
                }
            }
        }
        else
        {
            for ($i=$lastcard; $i <28 ; $i++)
            {
                $value=ConstConfig::ALL_CARD_28[$i];
                $Card_type = $this->_get_card_type($value);
                $Card_index = $value % 16;
                //不靠张的跳过
                $before_j = $Card_index - 1;
                $next_j = $Card_index+1;
                $nextnext_j = $Card_index+2;
                $before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$Card_type][$before_j] : 0 ;
                $next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$next_j] : 0 ;
                $nextnext_count = $nextnext_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$nextnext_j] : 0 ;
                if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$Card_type][$Card_index] && 0 == $next_count && $nextnext_count==0)
                {
                    continue;
                }
                if($this->_list_insert($chair, $value))
                {

                    $temp_hu_type=$this->judge_hu_type_fanhun_four($chair,$bHavezhong,$i);

                    if ($temp_hu_type!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                    {
                        $hu_type[]=$temp_hu_type;
                    }
                    $this->_list_delete($chair, ConstConfig::ALL_CARD_28[$i]);
                    if (end($hu_type)==self::HU_TYPE_JIAHU||end($hu_type)==self::HU_TYPE_HU19||end($hu_type)==self::HU_TYPE_DANDIAO)
                    {
                        break;
                    }
                }
            }
        }
        //返回
        if (empty($hu_type))
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        else
        {
            return max($hu_type);
        }
    }
    public function judge_hu_type_fanhun_four($chair,$bHavezhong,$lastcard = 0)
    {
        //胡牌数组
        $hu_type=array();
        for ($i=$lastcard; $i <28 ; $i++)
        {
            $value=ConstConfig::ALL_CARD_28[$i];
            $Card_type = $this->_get_card_type($value);
            $Card_index = $value % 16;
            //不靠张的跳过
            $before_j = $Card_index - 1;
            $next_j = $Card_index+1;
            $nextnext_j = $Card_index+2;
            $before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$Card_type][$before_j] : 0 ;
            $next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$next_j] : 0 ;
            $nextnext_count = $nextnext_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$nextnext_j] : 0 ;
            if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$Card_type][$Card_index] && 0 == $next_count && $nextnext_count==0)
            {
                continue;
            }
            if($this->_list_insert($chair, $value))
            {

                $temp_hu_type=$this->judge_hu_type_fanhun_three($chair,$bHavezhong,$i);

                if ($temp_hu_type!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    $hu_type[]=$temp_hu_type;
                }
                $this->_list_delete($chair, ConstConfig::ALL_CARD_28[$i]);
                if (end($hu_type)==self::HU_TYPE_JIAHU||end($hu_type)==self::HU_TYPE_HU19||end($hu_type)==self::HU_TYPE_DANDIAO)
                {
                    break;
                }
            }
        }
        //返回
        if (empty($hu_type))
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        else
        {
            return max($hu_type);
        }
    }
    public function judge_hu_type_fanhun_three($chair,$bHavezhong,$lastcard = 0)
    {
        //胡牌数组
        $hu_type=array();
        for ($i=$lastcard; $i <28 ; $i++)
        {
            $value=ConstConfig::ALL_CARD_28[$i];
            $Card_type = $this->_get_card_type($value);
            $Card_index = $value % 16;
            //不靠张的跳过
            $before_j = $Card_index - 1;
            $next_j = $Card_index+1;
            $nextnext_j = $Card_index+2;
            $before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$Card_type][$before_j] : 0 ;
            $next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$next_j] : 0 ;
            $nextnext_count = $nextnext_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$nextnext_j] : 0 ;
            if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$Card_type][$Card_index] && 0 == $next_count && $nextnext_count==0)
            {
                continue;
            }
            if($this->_list_insert($chair, $value))
            {

                $temp_hu_type=$this->judge_hu_type_fanhun_two($chair,$bHavezhong,$i);

                if ($temp_hu_type!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    $hu_type[]=$temp_hu_type;
                }
                $this->_list_delete($chair, ConstConfig::ALL_CARD_28[$i]);
                if (end($hu_type)==self::HU_TYPE_JIAHU||end($hu_type)==self::HU_TYPE_HU19||end($hu_type)==self::HU_TYPE_DANDIAO)
                {
                    break;
                }
            }
        }
        //返回
        if (empty($hu_type))
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        else
        {
            return max($hu_type);
        }
    }

    public function judge_hu_type_fanhun_two($chair,$bHavezhong,$lastcard = 0)
    {
        //胡牌数组
        $hu_type=array();
        for ($i=$lastcard; $i <28 ; $i++)
        {
            $value=ConstConfig::ALL_CARD_28[$i];
            $Card_type = $this->_get_card_type($value);
            $Card_index = $value % 16;
            //不靠张的跳过
            $before_j = $Card_index - 1;
            $next_j = $Card_index+1;
            $nextnext_j = $Card_index+2;
            $before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$Card_type][$before_j] : 0 ;
            $next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$next_j] : 0 ;
            $nextnext_count = $nextnext_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$nextnext_j] : 0 ;
            if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$Card_type][$Card_index] && 0 == $next_count && $nextnext_count==0)
            {
                continue;
            }
            if($this->_list_insert($chair, $value))
            {
                if (in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card))
                {
                    $temp_hu_type=$this->judge_hu_type_hucard_hun($chair,$bHavezhong);
                }
                else
                {
                    $temp_hu_type=$this->judge_hu_type_fanhun_one($chair,$bHavezhong,$i);
                }
                if ($temp_hu_type!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    $hu_type[]=$temp_hu_type;
                }
                $this->_list_delete($chair, ConstConfig::ALL_CARD_28[$i]);
                if (end($hu_type)==self::HU_TYPE_JIAHU||end($hu_type)==self::HU_TYPE_HU19||end($hu_type)==self::HU_TYPE_DANDIAO)
                {
                    break;
                }
            }
        }
        //返回
        if (empty($hu_type))
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        else
        {
            return max($hu_type);
        }
    }

    public function judge_hu_type_fanhun_one($chair,$bHavezhong,$lastcard = 0)
    {
        //胡牌数组
        $hu_type=array();
        for ($i=$lastcard; $i <28 ; $i++)
        {
            $value=ConstConfig::ALL_CARD_28[$i];
            $Card_type = $this->_get_card_type($value);
            $Card_index = $value % 16;
            //不靠张的跳过
            $before_j = $Card_index - 1;
            $next_j = $Card_index+1;
            $before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$Card_type][$before_j] : 0 ;
            $next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$next_j] : 0 ;
            if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$Card_type][$Card_index] && 0 == $next_count)
            {
                continue;
            }
            if($this->_list_insert($chair, $value))
            {
                $temp_hu_type=$this->judge_hu_type($chair,$this->m_HuCurt[$chair]->card,$bHavezhong);
                if ($temp_hu_type!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    $hu_type[]=$temp_hu_type;
                }
                $this->_list_delete($chair, ConstConfig::ALL_CARD_28[$i]);
                if (end($hu_type)==self::HU_TYPE_JIAHU||end($hu_type)==self::HU_TYPE_HU19||end($hu_type)==self::HU_TYPE_DANDIAO)
                {
                    break;
                }
            }
        }
        //返回
        if (empty($hu_type))
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        else
        {
            return max($hu_type);
        }
    }
    //胡牌为混子
    public function judge_hu_type_hucard_hun($chair,$bHavezhong)
    {
        //胡牌数组
        $hu_type=array();
        //定义所有牌数组
        $allCard=array(1,2,3,4,5,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,53);
        foreach ($allCard as $value)
        {
            $Card_type = $this->_get_card_type($value);
            $Card_index = $value % 16;
            //不靠张的跳过
            $before_j = $Card_index - 1;
            $next_j = $Card_index+1;
            $before_count = $before_j > 0 ? $this->m_sPlayer[$chair]->card[$Card_type][$before_j] : 0 ;
            $next_count = $next_j < 10 ? $this->m_sPlayer[$chair]->card[$Card_type][$next_j] : 0 ;
            if(0 == $before_count && 0 == $this->m_sPlayer[$chair]->card[$Card_type][$Card_index] && 0 == $next_count)
            {
                continue;
            }
            if($this->_list_insert($chair, $value))
            {
                $temp_hu_type=$this->judge_hu_type($chair,$value,$bHavezhong);
                if ($temp_hu_type!=self::HU_TYPE_FENGDING_TYPE_INVALID)
                {
                    $hu_type[]=$temp_hu_type;
                }
                $this->_list_delete($chair, $value);
                if (end($hu_type)==self::HU_TYPE_JIAHU||end($hu_type)==self::HU_TYPE_HU19||end($hu_type)==self::HU_TYPE_DANDIAO)
                {
                    break;
                }
            }
        }
        //返回
        if (empty($hu_type))
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        else
        {
            return max($hu_type);
        }
    }
    //胡牌类型判断  没有混的情况
    public function judge_hu_type($chair,$huCard,$bHavezhong)
    {
        $jiang_arr = array();

        $kezi_arr = array();
        $shunzi_arr = array();

        $bType32 = false;

        $bShunzi = false;
        $bKezi = false;

        $bJiahu = false;
        $bDandiao = false;
        $bDuidao = false;
        $bBian = false;
        $bHu19 = false;

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
        $bShunzi = array_sum($shunzi_arr);
        $bKezi = array_sum($kezi_arr);

        /////////////////////////////具体胡的处理/////////////////////////////////
        if (!$bType32)    //不是32牌型
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }
        //胡的那张牌的类型和键值
        $huCard_type = $this->_get_card_type($huCard);
        $huCard_key = $huCard % 16;

        if($bShunzi && ($bKezi || $bHavezhong))
        {
            //胡19
            if (!$bHavezhong)
            {
                $bHu19 = $this->_is_hu19($chair,$huCard_type,$huCard_key);
                if ($bHu19)
                {
                    return self::HU_TYPE_HU19;
                }
            }
            $bJiahu = $this->_is_jiahu($chair,$huCard_type,$huCard_key);
            if ($bJiahu)
            {
                return self::HU_TYPE_JIAHU;
            }
            //判断天胡时边为夹胡
            if($this->m_bTianRenHu)
            {
                return self::HU_TYPE_JIAHU;
            }
            $bDandiao = $this->_is_dandiao($chair,$huCard_type,$huCard_key,$jiang_type);
            if ($bDandiao)
            {
                return self::HU_TYPE_DANDIAO;
            }
            $bDuidao = $this->_is_duidao($chair,$huCard_type,$huCard_key);
            if ($bDuidao)
            {
                return self::HU_TYPE_DUIDAO;
            }
            return self::HU_TYPE_BIAN;
        }
        return self::HU_TYPE_FENGDING_TYPE_INVALID;
    }

	//------------------------------------- 命令处理函数 -----------------------------------
    //处理吃牌(开门字段)
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
        //开门字段
        $this->m_bKaiMen[$chair] = true;

        $this->_set_record_game(ConstConfig::RECORD_CHI, $chair, $temp_card, $this->m_sOutedCard->chair, $eat_num);

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
    //处理碰牌(开门字段)
    public function HandleChoosePeng($chair)
    {
        $temp_card = $this->m_sOutedCard->card;
        $card_type = $this->_get_card_type($temp_card);

        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
        {
            echo("error peng".__LINE__.__CLASS__);
            return false;
        }

        if(($this->_list_find($chair, $temp_card)) >= 2)
        {
            $this->_list_delete($chair, $temp_card);
            $this->_list_delete($chair, $temp_card);
        }
        else
        {
            echo "error handlechoosepeng".__LINE__.__CLASS__;
            return false;
        }

        // 设置倒牌
        $stand_count = $this->m_sStandCard[$chair]->num;
        $this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_KE;
        $this->m_sStandCard[$chair]->first_card[$stand_count] = $temp_card;
        $this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
        $this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
        $this->m_sStandCard[$chair]->num ++;
        //开门字段
        $this->m_bKaiMen[$chair] = true;

        // 找出第14张牌
        $car_14 = $this->_find_14_card($chair);
        if(!$car_14)
        {
            echo "error handlechoosepeng".__LINE__.__CLASS__;
            return false;
        }

        //置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
        $this->_deleteLastTableCard();

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
    //处理直杠(开门字段)
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
        //开门字段
        $this->m_bKaiMen[$chair] = true;

        //清除出牌信息
        $this->m_sOutedCard->clear();

        //收取杠分
        $bHaveGangScore = 0;
        $DoubleEgg = 1;
        $nGangScore = 0;
        $nGangPao = 0;
        if (!empty($this->m_rule->is_xiadan))
        {
            $bHaveGangScore = 1;
            if ($temp_card == 34)
            {
                $DoubleEgg = 2;
            }
        }

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
                    $nGangScore = self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE * 2 * $bHaveGangScore * $DoubleEgg;

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
                        $nGangScore = self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE * 2 * $bHaveGangScore * $DoubleEgg;

                        $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                        $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                        $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

                        $nGangPao += $nGangScore;
                    }
                    else
                    {
                        $nGangScore = self::M_ZHIGANG_SCORE * ConstConfig::SCORE_BASE * $bHaveGangScore * $DoubleEgg;

                        $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
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

        //收取杠分
        $bHaveGangScore = 0;
        $DoubleEgg = 1;
        $nGangScore = 0;
        $nGangPao = 0;
        if (!empty($this->m_rule->is_xiadan))
        {
            $bHaveGangScore = 1;
            if ($temp_card == 34)
            {
                $DoubleEgg = 2;
            }
        }

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
                    $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE * 2 * $bHaveGangScore * $DoubleEgg;

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
                        $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE * 2 * $bHaveGangScore * $DoubleEgg;

                        $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                        $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                        $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

                        $nGangPao += $nGangScore;
                    }
                    else
                    {
                        $nGangScore = self::M_ANGANG_SCORE * ConstConfig::SCORE_BASE * $bHaveGangScore * $DoubleEgg;

                        $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                        $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                        $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

                        $nGangPao += $nGangScore;
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

    //处理弯杠(弯杠不能被抢)
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

        //收取杠分
        $bHaveGangScore = 0;
        $DoubleEgg = 1;
        $nGangScore = 0;
        $nGangPao = 0;
        if (!empty($this->m_rule->is_xiadan))
        {
            $bHaveGangScore = 1;
            if ($temp_card == 34)
            {
                $DoubleEgg = 2;
            }
        }

        for ($i=0; $i<$this->m_rule->player_count; $i++)
        {
            if ($i == $chair)
            {
                continue;
            }

            if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
            {
                if($chair == $this->m_nChairBanker)//庄家弯杠
                {
                    $nGangScore = self::M_WANGANG_SCORE * ConstConfig::SCORE_BASE * 2 * $bHaveGangScore * $DoubleEgg;

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
                        $nGangScore = self::M_WANGANG_SCORE * ConstConfig::SCORE_BASE * 2 * $bHaveGangScore * $DoubleEgg;

                        $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                        $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                        $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

                        $nGangPao += $nGangScore;
                    }
                    else
                    {
                        $nGangScore = self::M_WANGANG_SCORE * ConstConfig::SCORE_BASE * $bHaveGangScore * $DoubleEgg;

                        $this->m_wGangScore[$i][$i] -= $nGangScore;		        //总刮风下雨分
                        $this->m_wGangScore[$chair][$chair] += $nGangScore;		//总刮风下雨分
                        $this->m_wGangScore[$chair][$i] += $nGangScore;			//赢对应玩家刮风下雨分

                        $nGangPao += $nGangScore;
                    }
                }
            }
        }

        //for 杠上花
        $this->m_bHaveGang = true;
        //杠炮
        $this->m_sGangPao->init_data(true, $temp_card, $chair, ConstConfig::DAO_PAI_TYPE_WANGANG, $nGangPao);
        //总计
        $this->m_wTotalScore[$chair]->n_zhigang_wangang += 1;
        //记录
        $this->_set_record_game(ConstConfig::RECORD_ZHUANGANG, $chair, $temp_card, $chair);

        //修改状态
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
        $this->m_chairCurrentPlayer = $chair;
        //暗杠需要记录入命令
        $this->m_chairSendCmd = $this->m_chairCurrentPlayer;
        $this->m_currentCmd = 'c_wan_gang';
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

    //处理出牌(没有加速出牌)
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

        //没有加速出牌

        for ( $i=0; $i<$this->m_rule->player_count - 1; $i++)
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
            $this->m_sPlayer[$chair_next]->seen_out_card = 0;
            $this->m_bChooseBuf[$chair_next] = 1;
            $this->m_sPlayer[$chair_next]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
            $bHaveCmd = 1;

            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair_next), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair_next]['uid']);
        }

        $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($chair), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$chair]['uid']);

        return true;

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

        //本项目没有抢杠
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
                $m_wGFXYScore = [0,0,0,0];
                for ( $i=0; $i<$this->m_rule->player_count; ++$i)
                {
                    if ($i == $this->m_sQiangGang->chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
                    {
                        continue;
                    }
                    $nGangScore = ConstConfig::SCORE_BASE * self::M_WANGANG_SCORE;

                    $this->m_wGFXYScore[$i] = -$nGangScore;
                    $this->m_wGangScore[$i][$i] -= $nGangScore;

                    $this->m_wGFXYScore[$this->m_sQiangGang->chair] += $nGangScore;
                    $this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
                    $this->m_wGangScore[$this->m_sQiangGang->chair][$i] += $nGangScore;

                    $nGangPao += $nGangScore;
                }

                //弯杠 扣 点碰玩家分数
                /*for ($i = 0; $i < $this->m_sStandCard[$this->m_sQiangGang->chair]->num; $i ++)
                {
                    if ($this->m_sStandCard[$this->m_sQiangGang->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                    && $this->m_sStandCard[$this->m_sQiangGang->chair]->card[$i] == $this->m_sQiangGang->card)
                    {
                        $nGangScore = self::M_WANGANG_SCORE *ConstConfig::SCORE_BASE;

                        $tmp_who_give_me = $this->m_sStandCard[$this->m_sQiangGang->chair]->who_give_me[$i];
                        $this->m_wGFXYScore[$tmp_who_give_me] = -$nGangScore;
                        $this->m_wGangScore[$tmp_who_give_me][$tmp_who_give_me] -= $nGangScore;

                        $this->m_wGFXYScore[$this->m_sQiangGang->chair] += $nGangScore;
                        $this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
                        $this->m_wGangScore[$this->m_sQiangGang->chair][$tmp_who_give_me] += $nGangScore;
                        break;
                    }

                }*/


                $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
                $this->m_chairCurrentPlayer = $this->m_sQiangGang->chair;

                $this->m_bHaveGang = true;					//for 杠上花
                $this->m_gangkai_num[$this->m_sQiangGang->chair] +=1;           //连续杠的次数
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
                    //CCLOG("end reason no card");
                    return;
                }

                //状态变化发消息
                $this->_send_act($this->m_currentCmd, $this->m_sQiangGang->chair, $this->m_sQiangGang->card);
                $this->handle_flee_play(true);	//更新断线用户
                for ($i=0; $i < $this->m_rule->player_count ; $i++)
                {
                    $cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
                    $cmd->send($this->serv);
                    unset($cmd);
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
                //$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
                $this->m_currentCmd = 'c_hu';

                //置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
                $this->_deleteLastTableCard();

                if ($this->m_game_type == self::GAME_TYPE)
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
                        $cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
                        $cmd->send($this->serv);
                        unset($cmd);
                    }

                    break;
            }

        }

        $this->m_nNumCmdHu = 0;
        $this->m_chairHu = array();
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
            //满手混时不能胡,按照荒庄算
            $fanhun_num = $this->_get_fan_hun_num($chair);

            if((13-$this->m_sStandCard[$chair]->num*3)==$fanhun_num && in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card))
            {
                //满手混按照没有牌计算
                $this->m_nEndReason = ConstConfig::END_REASON_NOCARD;
                $this->HandleSetOver();
            }
            else
            {
                echo("有人诈胡".__LINE__);
                $this->HandleZhaHu($chair);
                $this->m_HuCurt[$chair]->clear();
            }
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

            //发消息
            $this->_send_act($this->m_currentCmd, $chair);
            $this->HandleSetOver();

            return true;
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
        $data['m_diao_yu'] = $this->m_diao_yu;	//钓鱼
        $data['m_fan_hun_card'] = $this->m_fan_hun_card;
        $data['m_hun_card'] = $this->m_hun_card;
        $data['m_bKaiMen'] = $this->m_bKaiMen;                  //是否开门

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
                    //$data['m_nHuCan'] = $this->m_nHuCan[$i];
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
        $fan_sum = $this->judge_fan($chair);  //这个就是  一共多少分
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

                if ($this->m_game_type== 160 && $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
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

                // $banker_fan = 0;	//庄家分
                // if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
                // {
                // 	$banker_fan = 2;
                // }

                //庄家分数翻倍
                $banker_fan = 1;
                if($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)
                {
                    $banker_fan = $banker_fan * 2;
                }
                //数据门清判断
                if(!(in_array(self::ATTACHED_HU_SANJIAQING, $this->m_HuCurt[$chair]->method)))
                {
                    if($this->m_bKaiMen[$i] == false)
                    {
                        $banker_fan = $banker_fan * 2;
                        $this->m_hu_desc[$i] .= '门清 ';
                    }
                }
                //飘特一边大
                if(!empty($this->m_rule->is_piaoteyibianda) && (in_array(self::HU_TYPE_PIAOHU, $this->m_HuCurt[$chair]->method)))
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
                if($this->m_bKaiMen[$lost_chair] == false)
                {
                    $banker_fan = $banker_fan * 2;
                    $this->m_hu_desc[$lost_chair] .= '门清 ';
                }
            }
            //飘特一边大
            if(!empty($this->m_rule->is_piaoteyibianda) && (in_array(self::HU_TYPE_PIAOHU, $this->m_HuCurt[$chair]->method)))
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
                    if($this->m_bKaiMen[$i] == false)
                    {
                        $banker_fan = $banker_fan * 2;
                        $this->m_hu_desc[$i] .= '门清 ';
                    }
                }
                //飘特一边大
                if(!empty($this->m_rule->is_piaoteyibianda) && (in_array(self::HU_TYPE_PIAOHU, $this->m_HuCurt[$chair]->method)))
                {
                    $banker_fan = $banker_fan * 2;
                }
                //杠炮(流泪)
                /*if (in_array(self::ATTACHED_HU_GANGPAO, $this->m_HuCurt[$chair]->method))
                {
                    $banker_fan = $banker_fan * 0.5;
                }*/

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
            $this->_hui_score();
            $this->_diao_yu();
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
            $this->m_Score[$i]->score = $this->m_wSetScore[$i]+ $this->m_wSetLoseScore[$i]+ $this->m_wGangScore[$i][$i] +$this->m_hui_score[$i] + $this->m_diao_yu_score[$i]+$this->m_wFollowScore[$i]+ $this->m_paozi_score[$i];
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

    public function WriteScore()
    {
        for($i = 0; $i < $this->m_rule->player_count; $i++)
        {
            $this->m_wTotalScore[$i]->n_score += $this->m_Score[$i]->score;

            if($this->m_wSetScore[$i])
            {
                $this->m_hu_desc[$i] = $this->m_hu_desc[$i].'+'.($this->m_wSetScore[$i]).' ';
            }

            if($this->m_wSetLoseScore[$i])
            {
                $this->m_hu_desc[$i] .= '被胡'.$this->m_wSetLoseScore[$i].' ';
            }
            if($this->m_hui_score[$i]>0)
            {
                $this->m_hu_desc[$i] .= '会分+'.$this->m_hui_score[$i].' ';
            }
            if($this->m_hui_score[$i]<0)
            {
                $this->m_hu_desc[$i] .= '会分'.$this->m_hui_score[$i].' ';
            }

            if (!empty($this->m_rule->is_xiadan))
            {
                if($this->m_wGangScore[$i][$i]>0)
                {
                    $this->m_hu_desc[$i] .= '杠分+'.$this->m_wGangScore[$i][$i].' ';
                }
                else
                {
                    $this->m_hu_desc[$i] .= '杠分'.$this->m_wGangScore[$i][$i].' ';
                }
            }
            if(!empty($this->m_rule->is_diaoyu))
            {
                if ($this->m_diao_yu_score[$i] <> 0)
                {
                    if($this->m_diao_yu_score[$i] > 0)
                    {
                        $this->m_hu_desc[$i] .= abs(($this->m_diao_yu_score[$i])/2/($this->m_rule->player_count-1)).'条鱼+'.$this->m_diao_yu_score[$i].' ';
                    }
                    else
                    {
                        $this->m_hu_desc[$i] .= abs(($this->m_diao_yu_score[$i])/2).'条鱼'.$this->m_diao_yu_score[$i].' ';
                    }
                }

            }


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
    //翻混
    public function _get_fan_hun($fan_hun_card)
    {
        $temp_type = $this->_get_card_type($fan_hun_card);
        $temp_card_index = $fan_hun_card%16;

        if($this->m_rule->is_fanhui == 1)
        {
            if($temp_type == ConstConfig::PAI_TYPE_WAN || $temp_type == ConstConfig::PAI_TYPE_TIAO || $temp_type ==ConstConfig::PAI_TYPE_TONG )
            {
                $tmp_index_array = array(0,9,1,2,3,4,5,6,7,8);
                $this->m_hun_card[] = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index

                $tmp_index_array = array(0,2,3,4,5,6,7,8,9,1);
                $this->m_hun_card[] = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index

            }
            elseif($temp_type == ConstConfig::PAI_TYPE_FENG || $temp_type == ConstConfig::PAI_TYPE_DRAGON)
            {
                $feng_array = array(0,2,3,4,1,5,6,7);
                $this->m_hun_card[] =$this->_get_card_index($temp_type,$feng_array[$temp_card_index]);//风牌的翻混index
            }
            else
            {
                echo("混牌错误，出现未定义类型的牌".__LINE__);
                return false;
            }
        }
        if (!empty($this->m_rule->is_fanhui) && $this->m_rule->is_fanhui == 2)
        {

            if($temp_type == ConstConfig::PAI_TYPE_WAN || $temp_type == ConstConfig::PAI_TYPE_TIAO || $temp_type ==ConstConfig::PAI_TYPE_TONG )
            {
                $tmp_index_array = array(0,9,1,2,3,4,5,6,7,8);
                $this->m_hun_card[] = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index
                $tmp_index_array = array(0,1,2,3,4,5,6,7,8,9);
                $this->m_hun_card[] = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index
                $tmp_index_array = array(0,2,3,4,5,6,7,8,9,1);
                $this->m_hun_card[] = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index

            }
            elseif($temp_type == ConstConfig::PAI_TYPE_FENG || $temp_type == ConstConfig::PAI_TYPE_DRAGON)
            {
                $feng_array = array(0,2,3,4,1,5,6,7);
                $this->m_hun_card[] =$this->_get_card_index($temp_type,$feng_array[$temp_card_index]);//风牌的翻混index
            }
            else
            {
                echo("混牌错误，出现未定义类型的牌".__LINE__);
                return false;
            }
        }
        if (!empty($this->m_rule->is_fanhui) && $this->m_rule->is_fanhui == 3)
        {

            if($temp_type == ConstConfig::PAI_TYPE_WAN || $temp_type == ConstConfig::PAI_TYPE_TIAO || $temp_type ==ConstConfig::PAI_TYPE_TONG )
            {
                $tmp_index_array = array(0,9,1,2,3,4,5,6,7,8);
                $this->m_hun_card[] = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index
                $tmp_index_array = array(0,1,2,3,4,5,6,7,8,9);
                $this->m_hun_card[] = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index
                $tmp_index_array = array(0,2,3,4,5,6,7,8,9,1);
                $this->m_hun_card[] = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);  //翻混的index
                $this->m_hun_card[] = 53;//风牌的翻混index

            }
            elseif($temp_type == ConstConfig::PAI_TYPE_FENG || $temp_type == ConstConfig::PAI_TYPE_DRAGON)
            {
                $feng_array = array(0,2,3,4,1,5,6,7);
                $this->m_hun_card[] =$this->_get_card_index($temp_type,$feng_array[$temp_card_index]);//风牌的翻混index
            }
            else
            {
                echo("混牌错误，出现未定义类型的牌".__LINE__);
                return false;
            }
        }
    }

    //找出第14张牌  ok
    public function _find_14_card($chair)
    {
        $all_hunzi = false;
        //去除混牌
        $fanhun_arr = array(); //每个混子的数量
        $this->_del_hun_card($chair,$fanhun_arr);
        //找出14牌
        $last_type = ConstConfig::PAI_TYPE_DRAGON;
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
            $all_hunzi = true;
        }
        if ($last_type>=0)
        {
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
        }
        $this->_add_hun_card($chair,$fanhun_arr);
        if ($all_hunzi)
        {
            $last_type = ConstConfig::PAI_TYPE_DRAGON;
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
                echo ("竟然没有牌aaaaaaaas".__LINE__ );
                return false;
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
        }
        if(empty($fouteen_card))
        {
            echo ("第十四张牌为空".__LINE__ );
            return false;
        }

        return $fouteen_card;
    }
    //获取手牌中混牌的个数
    public function _get_fan_hun_num($chair)
    {
        $fanhun_num = 0;
        foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
        {
            $fanhun_num  += $this->_list_find($chair,$fanhun_card);	//手牌翻混个数
        }
        return $fanhun_num;
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
    //删除手牌中的混牌
    public function _del_hun_card($chair,&$fanhun_arr)
    {
        //去掉所有混子
        foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
        {
            $one_fanhun_num = $this->_list_find($chair, $fanhun_card);	//手牌翻混个数
            $one_fanhun_type = $this->_get_card_type($fanhun_card);     //翻混牌类型
            $one_fanhun_card = $fanhun_card%16;                         //翻混牌

            $fanhun_arr[$fanhun_key]=$one_fanhun_num;
            $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = 0;
            $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] -= $one_fanhun_num;
        }
    }
    //将删除的混牌加回手牌
    public function _add_hun_card($chair,$fanhun_arr)
    {
        foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
        {
            $one_fanhun_type = $this->_get_card_type($fanhun_card);        //翻混牌类型
            $one_fanhun_card = $fanhun_card%16;       //翻混牌

            $this->m_sPlayer[$chair]->card[$one_fanhun_type][$one_fanhun_card] = $fanhun_arr[$fanhun_key];
            $this->m_sPlayer[$chair]->card[$one_fanhun_type][0] += $fanhun_arr[$fanhun_key];
        }

    }
    //逻辑判断飘胡
    public function _is_pengpeng($chair,$fanhun_num)
    {
        $is_PENGPENG = false;
        //倒牌
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++)
        {
            //倒牌类型顺
            if (ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i])
            {
                return false;
            }
        }
        $need_fanhun = 0;
        //手牌
        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_FENG; $i++)
        {
            for ($j = 1;$j<=9;$j++)
            {
                switch ($this->m_sPlayer[$chair]->card[$i][$j])
                {
                    case 0:
                        $need_fanhun += 0;
                        break;
                    case 1:
                        $need_fanhun += 2;
                        break;
                    case 2:
                        $need_fanhun += 1;
                        break;
                    case 3:
                        $need_fanhun += 0;
                        break;
                    default:
                        return false;
                        break;
                }
            }
        }
        if ($need_fanhun<=($fanhun_num+1))
        {
            $is_PENGPENG = true;
        }
        return $is_PENGPENG;

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
            if ($this->_judge_32type($chair, array($huCard_key,$huCard_key + 1,$huCard_key + 2),$huCard_type)   )
            {
                $bBian = true;
            }

        }
        if ($huCard_type != ConstConfig::PAI_TYPE_FENG && $huCard_key > 2
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key - 1] > 0
            && $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key - 2] > 0
        )
        {
            if ($this->_judge_32type($chair, array($huCard_key,$huCard_key - 1,$huCard_key - 2),$huCard_type)   )
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
                //有红中可以胡19
                /*if (in_array($this->m_sStandCard[$chair]->first_card[$i], array(53)))
                {
                    $hu19_arr[] = 255;
                }*/
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
                if ($this->_is_dantingjiang($chair,$huCard_type,$huCard_key))
                {
                    $bDandiao = true;
                }
            }
        }
        return $bDandiao;
    }
    //单听将判断
    public function _is_dantingjiang($chair,$huCard_type,$huCard_key)
    {
        //如果牌刚好是2张红中
        if ($huCard_type==ConstConfig::PAI_TYPE_FENG)
        {
            //单吊符合
            return true;
        }
        $is_dandiao = true;
        $replace_card = array(1,2,3,4,5,6,7,8,9);
        //$huCard_key = $this->m_HuCurt[$chair]->card % 16;
        foreach ($replace_card as $key=>$value)
        {
            if ($value == $huCard_key)
            {
                unset($replace_card[$key]);
            }
        }
        //删除手牌中的胡的那一张牌
        $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key] -= 1;

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
        //删除手牌中的胡的那一张牌
        $this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key] += 1;
        return $is_dandiao;
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

    //多混子判断胡type
    public function judge_hun_type ($chair)
    {
        $fanhun_arr = array(); //每个混子的数量
        $this->_del_hun_card($chair,$fanhun_arr);
        $huCard_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);
        $huCard_key = $this->m_HuCurt[$chair]->card % 16;
        $bHu19=false;
        $bDandiao=false;
        //判断最后一张是不是混牌
        if(!in_array($this->m_HuCurt[$chair]->card,$this->m_hun_card))
        {
            $bHu19=$this->_is_hu19($chair,$huCard_type,$huCard_key);

            if ($this->m_sPlayer[$chair]->card[$huCard_type][$huCard_key] == 2 )
            {
                $bDandiao=true;
            }
        }
        //拿回所有混子
        $this->_add_hun_card($chair,$fanhun_arr);
        if ($bHu19)
        {
            return self::HU_TYPE_HU19;
        }
        elseif ($bDandiao)
        {
            return self::HU_TYPE_DANDIAO;
        }
        else
        {
            return self::HU_TYPE_JIAHU;
        }
    }

    private function _diao_yu()
    {
        $result = 0;
        $chair = 255;
        if (empty($this->m_rule->is_diaoyu))
        {
            return true;
        }
        if (empty($this->m_nCardBuf[$this->m_nCountAllot]))
        {
            return true;
        }
        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            if($this->m_HuCurt[$i]->state==ConstConfig::WIN_STATUS_ZI_MO||$this->m_HuCurt[$i]->state==ConstConfig::WIN_STATUS_CHI_PAO)
            {
                $chair=$i;
            }
        }
        $tmp_yu = $this->m_nCardBuf[$this->m_nCountAllot++];
        //只有红中混
        if(count($this->m_hun_card)==1)
        {
            if ($tmp_yu == 53)
            {
                $result = self::SHI_TIAO_YU;
            }
            else
            {
                $tmp_yu_index = $tmp_yu % 16;
                if (in_array($tmp_yu_index,array(7,8,9)))
                {
                    $result = self::SAN_TIAO_YU;
                }
                elseif (in_array($tmp_yu_index,array(4,5,6)))
                {
                    $result = self::LIANG_TIAO_YU;
                }
                elseif (in_array($tmp_yu_index,array(1,2,3)))
                {
                    $result = self::YI_TIAO_YU;
                }
            }
        }
        else
        {
            if (in_array($tmp_yu,$this->m_hun_card) || $tmp_yu == 53)
            {
                $result = self::SI_TIAO_YU;
            }
            else
            {
                $tmp_yu_index = $tmp_yu % 16;
                if (in_array($tmp_yu_index,array(7,8,9)))
                {
                    $result = self::SAN_TIAO_YU;
                }
                elseif (in_array($tmp_yu_index,array(4,5,6)))
                {
                    $result = self::LIANG_TIAO_YU;
                }
                elseif (in_array($tmp_yu_index,array(1,2,3)))
                {
                    $result = self::YI_TIAO_YU;
                }
            }
        }

        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            if ($i==$chair)
            {
                continue;
            }
            $this->m_diao_yu_score[$chair]+=$result;
            $this->m_diao_yu_score[$i]-=$result;
        }
        $this->m_diao_yu->index = $tmp_yu;
        $this->m_diao_yu->total = $this->m_diao_yu_score[$chair]/2/($this->m_rule->player_count-1);
        $this->_set_record_game(ConstConfig::RECORD_DIAOYU, $chair, $this->m_diao_yu->index, $chair, $this->m_diao_yu->total);
        return true;
    }

    private function _hui_score()
    {
        //会：胡牌时候，手里有一个会就加2分（如果翻出红中会，那么一个20分）
        //胡牌没有会的时候
        //如果为8个会															    8分
        //如果为11个会															10分
        //如果为15个会															20分

        //判断混牌数组中有多少个
        //一个的话就是红中
        //两个是8混
        //三个是11会
        //四个是15
        $chair=255;                         //胡的人
        $result=0;
        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            if($this->m_HuCurt[$i]->state==ConstConfig::WIN_STATUS_ZI_MO||$this->m_HuCurt[$i]->state==ConstConfig::WIN_STATUS_CHI_PAO)
            {
                $chair=$i;
            }
        }
        $fanhun_num = 0;
        $this->_list_insert($chair,$this->m_HuCurt[$chair]->card);
        foreach ($this->m_hun_card as $fanhun_key=>$fanhun_card)
        {
            $fanhun_num  += $this->_list_find($chair,$fanhun_card);	//手牌翻混个数
        }
        $this->_list_delete($chair,$this->m_HuCurt[$chair]->card);
        $a=count($this->m_hun_card);
        if ($a==1)
        {
            if($fanhun_num>0)
            {
                $result=$fanhun_num * 20;
            }
            else
            {
                $result=20;
            }
        }
        if($a==2)
        {
            if($fanhun_num>0)
            {
                $result=$fanhun_num * 2;
            }
            else
            {
                $result=8;
            }
        }
        if($a==3)
        {
            if($fanhun_num>0)
            {
                $result=$fanhun_num * 2;
            }
            else
            {
                $result=10;
            }
        }
        if($a==4)
        {
            if($fanhun_num>0)
            {
                $result=$fanhun_num * 2;
            }
            else
            {
                $result=20;
            }
        }
        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            if ($i==$chair)
            {
                continue;
            }
            $this->m_hui_score[$chair]+=$result;
            $this->m_hui_score[$i]-=$result;
        }
    }





    private function _log($class,$line,$title,$log)
    {
        $str = "类:$class 行号:$line\r\n";
        echo $str;
        var_dump($title);
        var_dump($log);
    }




}
