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

class GameTianJin extends BaseGame
{
	const GAME_TYPE = 441;

	//－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
	const HU_TYPE_HUNDIAO = 21;                       //混钓
	const HU_TYPE_ZHUOWUKUI = 22;                     //捉五魁
	const HU_TYPE_SHUANGHUN_ZHUOWUKUI = 23;           //双混捉五魁
	const HU_TYPE_YITIAOLONG = 24;                    //一条龙
	const HU_TYPE_BENHUNLONG = 25;                    //本混龙
    const HU_TYPE_HUNDIAO_YITIAOLONG = 26;            //混钓一条龙
    const HU_TYPE_HUNDIAO_BENHUNLONG = 27;            //混钓本混龙
    const HU_TYPE_ZHUOWU_YITIAOLONG = 28;             //捉五一条龙
    const HU_TYPE_ZHUOWU_BENHUNLONG = 29;             //捉五本混龙
    const HU_TYPE_SHUANGHUN_ZHUOWU_YITIAOLONG = 30;   //双混捉五一条龙
    const HU_TYPE_SHUANGHUN_ZHUOWU_BENHUNLONG = 31;   //双混捉五本混龙
    const HU_TYPE_YINGANGHU = 32;                     //银杠胡
    const HU_TYPE_JINGANGHU = 33;                     //金杠胡
    const HU_TYPE_SUHU = 34;                          //素胡
    const HU_TYPE_PINGHU = 35;                        //平胡
                                   
	const HU_TYPE_FENGDING_TYPE_INVALID  = 0;   // 错误

	//－－－－－－－－－－－－－加倍牌型－－－－－－－－－－－－－－－－－－－
	const ATTACHED_HU_SUHU = 61;                //素胡
	const ATTACHED_HU_TIANHU = 62;              //天胡
	const ATTACHED_HU_GANGKAI = 63;             //杠上开花
	const ATTACHED_HU_KOUTING = 64;             //扣听

	//－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
	const M_ZHIGANG_SCORE = 1;                 // 直杠分
	const M_ANGANG_SCORE = 2;                  // 暗杠分
	const M_WANGANG_SCORE = 1;                 // 弯杠分
    const M_YINGANG_SCORE = 6;                 // 银杠分
    const M_JINGANG_SCORE = 8;                 // 金杠分

	public static $hu_type_arr = array(
		self::HU_TYPE_HUNDIAO=>array(self::HU_TYPE_HUNDIAO, 2, '混钓'),
		self::HU_TYPE_ZHUOWUKUI=>array(self::HU_TYPE_ZHUOWUKUI, 3, '捉五魁'),
		self::HU_TYPE_SHUANGHUN_ZHUOWUKUI=>array(self::HU_TYPE_SHUANGHUN_ZHUOWUKUI, 6, '双混捉五魁'),
		self::HU_TYPE_YITIAOLONG=>array(self::HU_TYPE_YITIAOLONG, 4, '一条龙'),
        self::HU_TYPE_BENHUNLONG=>array(self::HU_TYPE_BENHUNLONG, 8, '本混龙'),

        self::HU_TYPE_HUNDIAO_YITIAOLONG=>array(self::HU_TYPE_HUNDIAO_YITIAOLONG, 6, '混钓一条龙'),
        self::HU_TYPE_HUNDIAO_BENHUNLONG=>array(self::HU_TYPE_HUNDIAO_BENHUNLONG, 10, '混钓本混龙'),
        self::HU_TYPE_ZHUOWU_YITIAOLONG=>array(self::HU_TYPE_ZHUOWU_YITIAOLONG, 7, '捉五一条龙'),
        self::HU_TYPE_ZHUOWU_BENHUNLONG=>array(self::HU_TYPE_ZHUOWU_BENHUNLONG, 11, '捉五本混龙'),
        self::HU_TYPE_SHUANGHUN_ZHUOWU_YITIAOLONG=>array(self::HU_TYPE_SHUANGHUN_ZHUOWU_YITIAOLONG, 10, '双混捉五一条龙'),
        self::HU_TYPE_SHUANGHUN_ZHUOWU_BENHUNLONG=>array(self::HU_TYPE_SHUANGHUN_ZHUOWU_BENHUNLONG, 14, '双混捉五本混龙'),

        self::HU_TYPE_YINGANGHU=>array(self::HU_TYPE_YINGANGHU, 6, '银杠胡'),
		self::HU_TYPE_JINGANGHU=>array(self::HU_TYPE_JINGANGHU, 8, '金杠胡'),
        self::HU_TYPE_SUHU=>array(self::HU_TYPE_SUHU, 2, '素胡'),
        self::HU_TYPE_PINGHU=>array(self::HU_TYPE_PINGHU, 1, '平胡'),//素胡扣听后平胡或者平胡杠上开花
	);

	public static $attached_hu_arr = array(
		self::ATTACHED_HU_SUHU=>array(self::ATTACHED_HU_SUHU, 2, '素胡'),
		self::ATTACHED_HU_TIANHU=>array(self::ATTACHED_HU_TIANHU, 4, '天胡'),
		self::ATTACHED_HU_GANGKAI=>array(self::ATTACHED_HU_GANGKAI, 2, '杠上开花'),
		self::ATTACHED_HU_KOUTING=>array(self::ATTACHED_HU_KOUTING, 4, '扣听'),
	);

    public $contendBankerScore;             // 争庄
	public $m_kou_ting = array(0,0,0,0);    // 扣听结构
    public $m_hun_card = array();           // 混牌

	///////////////////////初始化/////////////////////////
	public function __construct($serv)
	{
		parent::__construct($serv);
		$this->m_game_type = self::GAME_TYPE;
	}

	public function InitDataSub()
	{
		$this->m_game_type = self::GAME_TYPE;
        $this->m_hun_card = array();
        $this->m_kou_ting = array(0,0,0,0);
		for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
		{
			$this->contendBankerScore[$i] = new ContendBanker();
		}                                     	
	}

	public function _open_room_sub($params)
	{
        $this->m_rule = new RuleTianJin();

        if(empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'], array(1, 2, 3, 4)))
        {
            $params['rule']['player_count'] = 4;
        }

		$params['rule']['min_fan'] = !isset($params['rule']['min_fan']) ? 0 : $params['rule']['min_fan'];
		$params['rule']['top_fan'] = !isset($params['rule']['top_fan']) ? 0 : $params['rule']['top_fan'];
		$params['rule']['is_circle'] = !isset($params['rule']['is_circle']) ? 4 : $params['rule']['is_circle'];
        $params['rule']['is_contend_banker'] = !isset($params['rule']['is_contend_banker']) ? 0 : $params['rule']['is_contend_banker'];
        $params['rule']['is_kou_ting'] = !isset($params['rule']['is_kou_ting']) ? 1 : $params['rule']['is_kou_ting'];
        $params['rule']['pay_type'] = !isset($params['rule']['pay_type']) ? 0 : $params['rule']['pay_type'];

		//默认项
        $params['rule']['is_feng'] = !isset($params['rule']['is_feng']) ? 1 : $params['rule']['is_feng'];
		$params['rule']['is_chipai'] = !isset($params['rule']['is_chipai']) ? 0 : $params['rule']['is_chipai'];
        $params['rule']['cancle_clocker'] = !isset($params['rule']['cancle_clocker']) ? 1 : $params['rule']['cancle_clocker'];
        $params['rule']['is_fanhun'] = !isset($params['rule']['is_fanhun']) ? 1 : $params['rule']['is_fanhun'];
		
		$this->m_rule->game_type = $params['rule']['game_type'];
		$this->m_rule->player_count = $params['rule']['player_count'];
		$this->m_rule->min_fan = $params['rule']['min_fan'];
		$this->m_rule->top_fan = $params['rule']['top_fan'];
		$this->m_rule->is_circle = $params['rule']['is_circle'];
		$this->m_rule->set_num = $this->m_rule->is_circle * $this->m_rule->player_count;
        $this->m_rule->is_contend_banker = $params['rule']['is_contend_banker'];
        $this->m_rule->is_kou_ting = $params['rule']['is_kou_ting'];
        $this->m_rule->pay_type = $params['rule']['pay_type'];

        //默认项
        $this->m_rule->is_chipai = $params['rule']['is_chipai'];
		$this->m_rule->is_feng = $params['rule']['is_feng'];
        $this->m_rule->cancle_clocker = $params['rule']['cancle_clocker'];
        $this->m_rule->is_fanhun = $params['rule']['is_fanhun'];

	}

    ///////////////////出牌阶段//////////////////////
    //自摸胡
    public function c_zimo_hu($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do {
            $error_code = $this->_validateParameterAndPhase($params, [
                'rid' => 'require',
                'uid' => 'require'],
                ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD
            );

            if ($error_code != 0)
            {
                $this->_deBugs($error_code, $return_send, __LINE__.__CLASS__);break;
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
                        $this->_deBugs(ConstConfig::FREQUENT_REQUEST, $return_send, __LINE__.__CLASS__);break 2;
                    }

                    if($this->m_sPlayer[$key]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
                    {
                        $this->_deBugs(ConstConfig::HU_ERROR, $return_send, __LINE__.__CLASS__);break 2;
                    }

                    if($key != $this->m_chairCurrentPlayer)
                    {
                        $this->_deBugs(ConstConfig::CURRENT_USER_ERROR, $return_send, __LINE__.__CLASS__);break 2;
                    }

                    if($this->m_only_out_card[$key] == true)
                    {
                        $this->_deBugs(ConstConfig::CAN_ONLY_OUT_CARD, $return_send, __LINE__.__CLASS__);break 2;
                    }

                    if(!$this->HandleHuZiMo($key))
                    {
                        $this->_clear_choose_buf($key);
                        $this->_deBugs(ConstConfig::FRAUD, $return_send, __LINE__.__CLASS__);break 2;
                    }

                    $this->_clear_choose_buf($key);   //自摸不可能抢杠胡
                    $is_act = true;
                }
            }

            if(!$is_act = true)
            {
                $this->_deBugs(ConstConfig::NOT_BELONG_THIS_ROOM, $return_send, __LINE__.__CLASS__);break;
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
            $error_code = $this->_validateParameterAndPhase($params, [
                'rid' => 'require',
                'uid' => 'require',
                'gang_card' => 'require'],
                ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD
            );

            if ($error_code != 0)
            {
                $this->_deBugs($error_code, $return_send, __LINE__.__CLASS__);break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {
                    $error_code = $this->_find_an_gang($key, $params['gang_card']);
                    if ($error_code != 0) 
                    {
                        $this->_deBugs($error_code, $return_send, __LINE__.__CLASS__);break;
                    }

                    if(empty($this->m_nCardBuf[$this->m_nCountAllot]))
                    {
                        $this->_deBugs(ConstConfig::CAN_ONLY_OUT_CARD, $return_send, __LINE__.__CLASS__);break;
                    }

                    $this->_clear_choose_buf($key);
                    $this->HandleChooseAnGang($key, $params['gang_card']);
                    $is_act = true;
                }
            }
            if(!$is_act = true)
            {
                $this->_deBugs(ConstConfig::NOT_BELONG_THIS_ROOM, $return_send, __LINE__.__CLASS__);break;
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
            $error_code = $this->_validateParameterAndPhase($params, [
                'rid' => 'require',
                'uid' => 'require',
                'gang_card' => 'require'],
                ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD
            );

            if ($error_code != 0) 
            {
                $this->_deBugs($error_code, $return_send, __LINE__.__CLASS__);break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {   
                    $error_code = $this->_find_wan_gang($key, $params['gang_card']);
                    if ($error_code != 0) 
                    {
                        $this->_deBugs($error_code, $return_send, __LINE__.__CLASS__);break;
                    }

                    if(empty($this->m_nCardBuf[$this->m_nCountAllot]))
                    {
                        $this->_deBugs(ConstConfig::CAN_ONLY_OUT_CARD, $return_send, __LINE__.__CLASS__);break;
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

    /////////////竞争选择阶段/////////////////
    //碰牌
    public function c_peng($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do {
            $error_code = $this->_validateParameterAndPhase($params, [
                'rid' => 'require',
                'uid' => 'require'],
                ConstConfig::SYSTEMPHASE_CHOOSING
            );

            if ($error_code != 0)
            {
                $this->_deBugs($error_code, $return_send, __LINE__.__CLASS__);break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {
                    $error_code = $this->_find_peng($key);
                    if ($error_code != 0) 
                    {
                        $this->c_cancle_choice($fd, $params);
                        $this->_deBugs($error_code, $return_send, __LINE__.__CLASS__);break;
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

    //直杠
    public function c_zhigang($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do {
            $error_code = $this->_validateParameterAndPhase($params, [
                'rid' => 'require',
                'uid' => 'require'],
                ConstConfig::SYSTEMPHASE_CHOOSING
            );

            if ($error_code != 0) 
            {
                $this->_deBugs($error_code, $return_send, __LINE__.__CLASS__);break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {
                    $params['type'] = 0;
                    $error_code = $this->_find_zhi_gang($key);
                    if ($error_code != 0) 
                    {
                        $this->c_cancle_choice($fd, $params);
                        $this->_deBugs($error_code, $return_send, __LINE__.__CLASS__);break;
                    }

                    if(empty($this->m_nCardBuf[$this->m_nCountAllot]))
                    {
                        $this->_deBugs(ConstConfig::CAN_ONLY_OUT_CARD, $return_send, __LINE__.__CLASS__);break;
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
                    if(empty($this->m_rule->is_kou_card) && empty($params['is_14']) && 0 == $this->_list_find($key,$params['out_card']))
                    {
                        $return_send['code'] = 5; $return_send['text'] = '出牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    }
                    if(!empty($this->m_rule->is_kou_card) && empty($params['is_14']) )
                    {
                        $ac = array_count_values($this->m_sPlayer[$key]->kou_card_display);
                        if(!empty($ac[$params['out_card']]) && $this->_list_find($key,$params['out_card']) <= $ac[$params['out_card']])
                        {
                            $return_send['code'] = 5; $return_send['text'] = '出牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                        }
                        else if(empty($ac[$params['out_card']]) && $this->_list_find($key,$params['out_card']) <= 0)
                        {
                            $return_send['code'] = 5; $return_send['text'] = '出牌错误'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                        }
                    }

                    //不能打混牌
                    if (in_array($params['out_card'], $this->m_hun_card)) 
                    {
                        $return_send['code'] = 6; $return_send['text'] = '不能打混牌'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
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

    //扣听
    public function c_kou_ting($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        
        do {
            if( empty($params['rid'])
            || empty($params['uid'])
            || !isset($params['is_kou_ting'])
            )
            {
                $return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }
            
            if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_KOU_TING || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state)
            {
                $return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            $is_act = false;

            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {
                    $this->handle_kou_ting($key, $params['is_kou_ting']);
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

    //坐庄拉庄
    public function c_contendBanker($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);

        do {
            if( empty($params['rid'])
            || empty($params['uid'])
            || !isset($params['is_contend_banker'])
            )
            {
                $return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CONTEND_BANKER || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state)
            {
                $return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {
                    if ($this->contendBankerScore[$key]->is_received == 1 && $this->m_nChairBanker != $key)
                    {
                        $return_send['code'] = 4; $return_send['text'] = '您已经拉过庄了'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    }

                    if ($this->contendBankerScore[$key]->is_received == 2 && $this->m_nChairBanker == $key)
                    {
                        $return_send['code'] = 4; $return_send['text'] = '您已经坐过庄了'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    }

                    $this->handle_contend_banker($key, $params['is_contend_banker']);
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

    //////////////////胡牌结果判断/////////////////////
	//判断胡  
	public function judge_hu($chair, $is_fanhun = false)
	{
		//胡牌型
        $is_su_hu = false;
		$hu_type = $this->judge_hu_type_fanhun($chair, $is_fanhun, $is_su_hu);

		if($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID)
		{
			return false;
		}

		//记录在全局数据
		$this->m_HuCurt[$chair]->method[0] = $hu_type;
		$this->m_HuCurt[$chair]->count = 1;

        //素胡
        if ($is_su_hu) 
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_SUHU);
        }

		//杠开
		if($this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair)
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_GANGKAI);
		}

        //天胡
        if($this->m_bTianRenHu && $chair == $this->m_nChairBanker)
        {
            $this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_TIANHU);
        }

		return true;
	}

    //胡牌类型判断  没有混的情况
    public function judge_hu_type($chair, &$is_su_hu)
    {
        //1.牌型32胡 2.幺九 4.是258 8.碰碰胡牌型 16.中张 32.有将牌 64.可做七对 128.十三幺 256.一条龙 512.硬将258 1024.有砍子  2048.有顺子 4096*$gen

        $jiang_arr = array();
        $gen_arr = array();
        $bType32 = false;
        $bQiDui = false;
        $is_yitiaolong = false;   //一条龙
        $is_zhuowukui = false;    //捉五魁
        $is_su_hu = false;

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
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }
            else
            {
                $hu_list_val = $tmp_hu_data[$key];
                
                if(($hu_list_val & 1) == 1)
                {
                    $jiang_arr[] = $hu_list_val & 32;

                    //一条龙
                    if(($hu_list_val & 256) == 256)
                    {
                        $is_yitiaolong = true;
                    }
                }
                else
                {
                    //非32牌型设置
                    $jiang_arr[] = 32; $jiang_arr[] = 32;
                }
            }
        }

        $bType32 = (32 == array_sum($jiang_arr));
        
        if($bType32)
        {   
            if ($this->m_HuCurt[$chair]->card == 5 && empty($this->m_bTianRenHu)) 
            {
                $is_zhuowukui = $this->_is_danting_wuwan($chair);
            }

            //庄家天胡捉五
            if ($this->m_bTianRenHu && $chair == $this->m_nChairBanker) 
            {
                $is_zhuowukui = $this->_is_tianhu_zhuowu($chair);
            }
            
            if($is_yitiaolong && $is_zhuowukui)
            {   
                $is_su_hu = true;
                return self::HU_TYPE_ZHUOWU_YITIAOLONG;
            }
            else
            {
                if ($is_yitiaolong) 
                {
                    $is_su_hu = true;
                    return self::HU_TYPE_YITIAOLONG;
                }
                if ($is_zhuowukui) 
                {
                    $is_su_hu = true;
                    return self::HU_TYPE_ZHUOWUKUI;
                }
            }
            
            return self::HU_TYPE_SUHU;
        }
    
        return self::HU_TYPE_FENGDING_TYPE_INVALID;
    }

	//判断翻混 
    public function judge_hu_type_fanhun($chair, $is_fanhun = false, &$is_su_hu)
    {
        $fanhun_num = 0;
        $fanhun_type = 255;
        if(!empty($this->m_hun_card))
        {
            $hun_num = $this->_list_find($chair, $this->m_hun_card[1]);        
            $fanhun_num = $this->_list_find($chair, $this->m_hun_card[0]);	
            $total_hun_num = $hun_num + $fanhun_num;
            $fanhun_type = $this->_get_card_type($this->m_hun_card[0]);
            $hun_card = $this->m_hun_card[1] % 16;
            $fanhun_card = $this->m_hun_card[0] % 16;
        }

        //$fanhun_num = $is_fanhun ? $fanhun_num - 1 : $fanhun_num;	//打出的牌是否为翻混
        //手牌无混中
        if($total_hun_num <= 0)
        {
            return $this->judge_hu_type($chair, $is_su_hu);
        }
        else
        {   
            $return_type = self::HU_TYPE_FENGDING_TYPE_INVALID;

            //金杠胡和银杠胡
            if ($hun_num == 4 && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO) 
            {
                $is_jin_gang_hu = self::$hu_type_arr[self::HU_TYPE_JINGANGHU][1];
            }
            if ($fanhun_num == 3 && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO) 
            {
                $is_yin_gang_hu = self::$hu_type_arr[self::HU_TYPE_YINGANGHU][1];
            }

            //32牌型
            $is_hu_data = false;
            $max_hu = array('total'=>-1);

            $jiang_judge_arr = array(0=>2,1=>1,2=>0,3=>2,4=>1,5=>0,6=>2,7=>1,8=>0,9=>2,10=>1,11=>0,12=>2,13=>1,14=>0);
            $no_jiang_judge_arr = array(0=>0,1=>2,2=>1,3=>0,4=>2,5=>1,6=>0,7=>2,8=>1,9=>0,10=>2,11=>1,12=>0);

            //去掉翻混
            $this->m_sPlayer[$chair]->card[$fanhun_type][$hun_card] = 0;
            $this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 0;

            $this->m_sPlayer[$chair]->card[$fanhun_type][0] -= ($total_hun_num);

            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
            {
                if(0 == $this->m_sPlayer[$chair]->card[$i][0] && $i != $fanhun_type)
                {
                    continue;
                }

                $is_hu_data = false;
                $jiang_type = $i;
                $need_fanhun = 0;
                $replace_fanhun = array(0,0,0,0);

                //假设$i为将门计算每门牌需要的混个数
                for($j=ConstConfig::PAI_TYPE_WAN; $j<=ConstConfig::PAI_TYPE_FENG; $j++)
                {
                    //一门牌个数
                    $pai_num = $this->m_sPlayer[$chair]->card[$j][0];

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

                //假设$i为将门,计算现在的混是否满足胡牌牌型
                if($need_fanhun <= $total_hun_num)
                {
                    $is_check_hu = false;
                    for($j=ConstConfig::PAI_TYPE_WAN; $j<=ConstConfig::PAI_TYPE_FENG; $j++)
                    {
                        $is_hu_data = false;
                        $max_type_hu_arr = array('total'=>-1, 'benhunlong' => 0, 'yitiaolong' => 0, 'shuanghun_zhuowu' => 0, 'zhuowukui' => 0, 'hundiao' => 0);

                        if($total_hun_num == $need_fanhun && $is_check_hu)
                        {
                            continue;
                        }
                        $is_check_hu = true;

                        $tmp_replace_fanhun = $replace_fanhun;
                        $tmp_replace_fanhun[$j] += ($total_hun_num - $need_fanhun);

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
                            $insert_yitiaolong = $max_type_hu_arr['yitiaolong'];
                            $insert_benhunlong = $max_type_hu_arr['benhunlong'];
                            $insert_zhuowukui = $max_type_hu_arr['zhuowukui'];
                            $insert_shuanghun_zhuowu = $max_type_hu_arr['shuanghun_zhuowu'];
                            $insert_hundiao = $max_type_hu_arr['hundiao'];

                            //这门牌需要$num个混
                            foreach ($tmp_hu_data_insert[$num] as $insert_arr)
                            {
                                $hun_diao_result = false; //必须有    
                                $wukui_result = false;    //必须有 

                                //把牌插进手牌中
                                foreach ($insert_arr as $insert_item)
                                {
                                    $this->m_sPlayer[$chair]->card[$type][$insert_item] += 1;
                                }

                                $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
                                
                                //满足32牌型的条件下判断胡牌牌型
                                if(isset($tmp_hu_data[$key]) && ($tmp_hu_data[$key] & 1) == 1)
                                {
                                    $is_hu_data = true;
                                    $tmp_type_hu_num = 0;

                                    //捉五魁判断
                                    if ($type == ConstConfig::PAI_TYPE_WAN) 
                                    {
                                        if ($this->m_bTianRenHu) 
                                        {   
                                            if ($chair == $this->m_nChairBanker) 
                                            {
                                                $wukui_result = $this->_is_zhuo_wu_hun($chair,$insert_arr,true);
                                            }
                                        }
                                        else
                                        {   
                                            if ($this->m_HuCurt[$chair]->card == 5 || (in_array($this->m_HuCurt[$chair]->card, $this->m_hun_card) && in_array(5, $insert_arr))) 
                                            {
                                                $wukui_result = $this->_is_zhuo_wu_hun($chair,$insert_arr,false);
                                            }
                                        }
                                    }

                                    //捉五魁加分
                                    $tmp_type_zhuowukui = ($insert_zhuowukui || $wukui_result == self::HU_TYPE_ZHUOWUKUI);
                                    if ($tmp_type_zhuowukui)
                                    {
                                        $tmp_type_hu_num += self::$hu_type_arr[self::HU_TYPE_ZHUOWUKUI][1];
                                    }

                                    //双混捉五加分
                                    $tmp_type_shuanghun_zhuowu = ($insert_shuanghun_zhuowu || $wukui_result == self::HU_TYPE_SHUANGHUN_ZHUOWUKUI);
                                    if ($tmp_type_shuanghun_zhuowu)
                                    {
                                        $tmp_type_hu_num += self::$hu_type_arr[self::HU_TYPE_SHUANGHUN_ZHUOWUKUI][1];
                                    }
                                    
                                    //混钓(混钓和捉五不能同时存在)
                                    if ($type == $jiang_type && empty($tmp_type_zhuowukui) && empty($tmp_type_shuanghun_zhuowu)) 
                                    {
                                        if ($this->m_bTianRenHu) 
                                        {
                                            $hun_diao_result = $this->_is_tianhu_hunyou($chair, $insert_arr, $jiang_type);
                                        }
                                        else
                                        {
                                            $hu_card = $this->m_HuCurt[$chair]->card % 16;
                                            $hu_card_type = $this->_get_card_type($this->m_HuCurt[$chair]->card);

                                            if (in_array($this->m_HuCurt[$chair]->card, $this->m_hun_card) && $total_hun_num >= 2)
                                            {
                                                $to_judge_hundiao = true;
                                                $is_hu_card_hun = true;
                                            }
                                            elseif (!in_array($this->m_HuCurt[$chair]->card, $this->m_hun_card) && $hu_card_type == $jiang_type && in_array($hu_card, $insert_arr)) 
                                            {
                                                $to_judge_hundiao = true;
                                                $is_hu_card_hun = false;
                                            }
                                            else
                                            {
                                                $to_judge_hundiao = false;
                                            }

                                            if ($to_judge_hundiao) 
                                            {
                                                //混钓判断
                                                $hun_diao_result = $this->_is_hunyou($chair, $insert_arr, $jiang_type, $is_hu_card_hun);
                                            }
                                        }
                                    }
                                    
                                    //混钓加分
                                    $tmp_type_hundiao = ($insert_hundiao || $hun_diao_result == self::HU_TYPE_HUNDIAO);

                                    if ($tmp_type_hundiao) 
                                    {   
                                        $tmp_type_hu_num += self::$hu_type_arr[self::HU_TYPE_HUNDIAO][1];
                                    }
                                    
                                    //一条龙判断加分
                                    $tmp_type_yitiaolong = ($insert_yitiaolong || ($tmp_hu_data[$key] & 256) == 256 && $type != $fanhun_type);

                                    if($tmp_type_yitiaolong)
                                    {
                                        $tmp_type_hu_num += self::$hu_type_arr[self::HU_TYPE_YITIAOLONG][1];
                                    }

                                    //本混龙判断加分
                                    $tmp_type_benhunlong = ($insert_benhunlong || (($tmp_hu_data[$key] & 256) == 256 && $type == $fanhun_type));
                                    if ($tmp_type_benhunlong) 
                                    {
                                        $tmp_type_hu_num += self::$hu_type_arr[self::HU_TYPE_BENHUNLONG][1];
                                    }

                                    //素胡扣听平胡
                                    if ($this->m_kou_ting[$chair] && $total_hun_num == 1 && in_array($this->m_HuCurt[$chair]->card, $this->m_hun_card) && $tmp_type_hu_num == 0) 
                                    {
                                        $tmp_type_hu_num += self::$hu_type_arr[self::HU_TYPE_PINGHU][1];
                                    }

                                    //平胡杠上开花
                                    if ($this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair && $tmp_type_hu_num == 0) 
                                    {
                                        $tmp_type_hu_num += self::$hu_type_arr[self::HU_TYPE_PINGHU][1];
                                    }

                                    foreach ($insert_arr as $insert_item)
                                    {
                                        $this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
                                    }

                                    //算分
                                    if($tmp_type_hu_num >= $max_type_hu_arr['total'])
                                    {
                                        $max_type_hu_arr['total'] = $tmp_type_hu_num;
                                        $max_type_hu_arr['yitiaolong'] = $tmp_type_yitiaolong;
                                        $max_type_hu_arr['benhunlong'] = $tmp_type_benhunlong;
                                        $max_type_hu_arr['zhuowukui'] = $tmp_type_zhuowukui;
                                        $max_type_hu_arr['shuanghun_zhuowu'] = $tmp_type_shuanghun_zhuowu;
                                        $max_type_hu_arr['hundiao'] = $tmp_type_hundiao;
                                    }
                                    
                                    // if($tmp_type_hu_num >= 14)
                                    // {
                                    //     break;
                                    // }
                                }
                                else
                                {
                                    foreach ($insert_arr as $insert_item)
                                    {
                                        $this->m_sPlayer[$chair]->card[$type][$insert_item] -= 1;
                                    }
                                }
                            }

                            // if(!$is_hu_data)
                            // {
                            //     $max_type_hu_arr['total'] = -1;
                            //     break;
                            // }
                        }
                    
                        if($max_type_hu_arr['total'] > 0)
                        {
                            $tmp_max_hu = $max_type_hu_arr['total'];
                            if($tmp_max_hu > $max_hu['total'])
                            {

                                $max_hu['total'] = $tmp_max_hu;
                                $max_hu['yitiaolong'] = $max_type_hu_arr['yitiaolong'];
                                $max_hu['benhunlong'] = $max_type_hu_arr['benhunlong'];
                                $max_hu['zhuowukui'] = $max_type_hu_arr['zhuowukui'];
                                $max_hu['shuanghun_zhuowu'] = $max_type_hu_arr['shuanghun_zhuowu'];
                                $max_hu['hundiao'] = $max_type_hu_arr['hundiao'];
                            }
                        }

                        if($max_hu['total'] >= self::$hu_type_arr[self::HU_TYPE_SHUANGHUN_ZHUOWU_BENHUNLONG][1])
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
            $this->m_sPlayer[$chair]->card[$fanhun_type][$hun_card] += $hun_num;
            $this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += $fanhun_num;
            $this->m_sPlayer[$chair]->card[$fanhun_type][0] += $total_hun_num;

            //金杠比较
            if (isset($is_jin_gang_hu))
            {
                if ($is_jin_gang_hu > $max_hu['total']) 
                {
                    return self::HU_TYPE_JINGANGHU;
                }
            }

            //银杠比较
            if (isset($is_yin_gang_hu)) 
            {
                if ($is_yin_gang_hu > $max_hu['total'] ) 
                {
                    return self::HU_TYPE_YINGANGHU;
                }
            }
            
            //混吊混的情况，使混吊和卡五魁同时存在
            if($max_hu['total'] >= 0)
            {   
                if ($max_hu['total'] == 1) 
                {
                    return self::HU_TYPE_PINGHU;
                }

                if ($max_hu['hundiao'])
                {
                    if ($max_hu['yitiaolong']) 
                    {
                        if($max_hu['zhuowukui'])
                        {
                            return self::HU_TYPE_ZHUOWU_YITIAOLONG;
                        }

                        if ($max_hu['shuanghun_zhuowu']) {
                            return self::HU_TYPE_SHUANGHUN_ZHUOWU_YITIAOLONG;
                        }

                        return self::HU_TYPE_HUNDIAO_YITIAOLONG;
                    }

                    if ($max_hu['benhunlong']) 
                    {
                        if($max_hu['zhuowukui'])
                        {
                            return self::HU_TYPE_ZHUOWU_BENHUNLONG;
                        }

                        if ($max_hu['shuanghun_zhuowu']) {
                            return self::HU_TYPE_SHUANGHUN_ZHUOWU_BENHUNLONG;
                        }

                        return self::HU_TYPE_HUNDIAO_BENHUNLONG;
                    }

                    if($max_hu['zhuowukui'])
                    {
                        return self::HU_TYPE_ZHUOWUKUI;
                    }

                    if($max_hu['shuanghun_zhuowu'])
                    {
                        return self::HU_TYPE_SHUANGHUN_ZHUOWUKUI;
                    }

                    return self::HU_TYPE_HUNDIAO;
                }
                else
                {
                    if ($max_hu['yitiaolong'])
                    {
                        if($max_hu['zhuowukui'])
                        {
                            return self::HU_TYPE_ZHUOWU_YITIAOLONG;
                        }

                        if ($max_hu['shuanghun_zhuowu']) {
                            return self::HU_TYPE_SHUANGHUN_ZHUOWU_YITIAOLONG;
                        }

                        return self::HU_TYPE_YITIAOLONG;
                    }

                    if ($max_hu['benhunlong']) 
                    {
                        if($max_hu['zhuowukui'])
                        {
                            return self::HU_TYPE_ZHUOWU_BENHUNLONG;
                        }

                        if ($max_hu['shuanghun_zhuowu']) {
                            return self::HU_TYPE_SHUANGHUN_ZHUOWU_BENHUNLONG;
                        }

                        return self::HU_TYPE_BENHUNLONG;
                    }
                    
                    if($max_hu['zhuowukui'])
                    {
                        return self::HU_TYPE_ZHUOWUKUI;
                    }

                    if($max_hu['shuanghun_zhuowu'])
                    {
                        return self::HU_TYPE_SHUANGHUN_ZHUOWUKUI;
                    }
                }
            }

            return $return_type;
        }
    }

    //------------------------------------- 命令处理函数 -----------------------------------
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

            //算分
            $tmp_lost_chair = 255;
            $this->ScoreOneHuCal($chair, $tmp_lost_chair);

            if(255 == $this->m_nChairBankerNext)    //下一局庄家
            {
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

            //银杠胡
            $stand_count = $this->m_sStandCard[$chair]->num;
            $stand_type = 0;
            if ($stand_count > 0) 
            {
                $stand_type = $this->m_sStandCard[$chair]->type[$stand_count-1];
            }
            if ($this->m_bHaveGang && $this->m_sGangPao->mark && $this->m_sGangPao->chair == $chair && $stand_type == ConstConfig::DAO_PAI_TYPE_YINGANG)
            {

                $this->_set_record_game(ConstConfig::RECORD_ZIMO, $chair, $temp_card, $chair, 1);
            }
            else
            {
                $this->_set_record_game(ConstConfig::RECORD_ZIMO, $chair, $temp_card, $chair);
            }

            $this->HandleSetOver();

            //发消息
            $this->_send_act($this->m_currentCmd, $chair);

            return true;
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
        else if($this->m_nEndReason == ConstConfig::END_REASON_FLEE )       //逃跑结算游戏
        {
            //逃跑牌局等待，不结算
        }
        else
        {
            echo(__LINE__.__CLASS__."Unknow end reason: ".$this->m_nEndReason);
        }

        //下一局庄家
        if($this->m_nEndReason==ConstConfig::END_REASON_NOCARD)
        {
            for ($i=0; $i < $this->m_rule->player_count; $i++) 
            { 
                if ($i == $this->m_nChairBanker) 
                {
                    continue;
                }

                $stand_type_group = $this->m_sStandCard[$i]->type;
                foreach ($stand_type_group as $stand_type) 
                {
                    if (in_array($stand_type, [ConstConfig::DAO_PAI_TYPE_MINGGANG, ConstConfig::DAO_PAI_TYPE_ANGANG, ConstConfig::DAO_PAI_TYPE_WANGANG, ConstConfig::DAO_PAI_TYPE_YINGANG, ConstConfig::DAO_PAI_TYPE_JINGANG])) 
                    {
                        $this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker,1);
                        break 2;
                    }
                }
            }
            
            if (255 == $this->m_nChairBankerNext) 
            {
                $this->m_nChairBankerNext = $this->m_nChairBanker;
            }
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
        $this->handle_flee_play(true);  //更新断线用户
        for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $this->_send_cmd('s_game_over', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
        }

        $this->_set_game_and_checkout();
    }

    //处理暗杠 
    public function HandleChooseAnGang($chair, $gang_card)
    {
        $temp_card = $gang_card;

        $card_taken_now = $this->m_sPlayer[$chair]->card_taken_now;
        $this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now);
        $this->m_sPlayer[$chair]->card_taken_now = 0;

        if ($temp_card == $this->m_fan_hun_card) 
        {
            $this->_list_delete_multiple_times($chair, $temp_card, 3);
        }
        else
        {
            $this->_list_delete_multiple_times($chair, $temp_card, 4);
        }

        //设置倒牌
        $down_card_type = ConstConfig::DAO_PAI_TYPE_ANGANG;

        if ($temp_card == $this->m_hun_card[0]) {
            $down_card_type = ConstConfig::DAO_PAI_TYPE_YINGANG;
        }
        if ($temp_card == $this->m_hun_card[1]) {
            $down_card_type = ConstConfig::DAO_PAI_TYPE_JINGANG;
        }

        $stand_count = $this->m_sStandCard[$chair]->num;
        $this->_set_down_card($chair, $down_card_type, $temp_card, $temp_card, $chair);
        $this->m_bHaveGang = true;
        $this->m_bTianRenHu = false;

        //混儿杠
        $GangScore = 0;
        $nGangPao = 0;

        switch ($gang_card) {
            case $this->m_hun_card[0]:
                $this->_set_record_game(ConstConfig::RECORD_ANGANG, $chair, $temp_card, $chair, 1);
                $nGangScore = self::M_YINGANG_SCORE;
                break;

            case $this->m_hun_card[1]:
                $this->_set_record_game(ConstConfig::RECORD_ANGANG, $chair, $temp_card, $chair, 2);
                $nGangScore = self::M_JINGANG_SCORE;
                break;

            default:
                $this->_set_record_game(ConstConfig::RECORD_ANGANG, $chair, $temp_card, $chair, 0);
                $nGangScore = self::M_ANGANG_SCORE;
                break;
        }

        for ($i=0; $i<$this->m_rule->player_count; $i++)
        {
            if ($i == $chair)
            {
                continue;
            }

            if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
            {
                $this->m_wGangScore[$i][$i] -= $nGangScore;     //总刮风下雨分
                $this->m_wGangScore[$chair][$chair] += $nGangScore;     //总刮风下雨分
                $this->m_wGangScore[$chair][$i] += $nGangScore;         //赢对应玩家刮风下雨分

                $nGangPao += $nGangScore;
            }
        }

        $this->m_sGangPao->init_data(true, $gang_card, $chair, ConstConfig::DAO_PAI_TYPE_ANGANG, $nGangPao);
        $this->m_wTotalScore[$chair]->n_angang += 1;

        // 补发张牌给玩家
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
        $this->m_chairCurrentPlayer = $chair;
        if ($this->m_sStandCard[$chair]->type[$stand_count] == ConstConfig::DAO_PAI_TYPE_YINGANG) 
        {
            if (!empty($this->m_kou_ting[$chair])) 
            {
                $this->_list_delete($chair, $card_taken_now);
                $car_14 = $card_taken_now;
            }
            else
            {
                //找出第14张牌
                $car_14 = $this->_find_14_card($chair);
                if(!$car_14)
                {
                    return false;
                }
            }
            $this->m_sPlayer[$chair]->card_taken_now = $car_14;
        }
        else
        {
            if(!($this->DealCard($chair)))
            {
                return;
            }
        }

        //暗杠需要记录入命令
        $this->m_chairSendCmd = $this->m_chairCurrentPlayer;
        $this->m_currentCmd = 'c_an_gang';
        $this->m_sOutedCard->clear();
        if($this->m_nEndReason == ConstConfig::END_REASON_NOCARD)
        {
            return;
        }

        //状态变化发消息
        $this->_send_act($this->m_currentCmd, $chair);

        $this->handle_flee_play(true);  //更新断线用户
        for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
        }
    }

    //处理碰 
    public function HandleChoosePeng($chair)
    {
        $temp_card = $this->m_sOutedCard->card;

        //从手牌中删除
        $this->_list_delete_multiple_times($chair, $temp_card, 2);

        //设置倒牌
        $this->_set_down_card($chair, ConstConfig::DAO_PAI_TYPE_KE, $temp_card, $temp_card, $this->m_sOutedCard->chair);

        //找出第14张牌
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

        //更改系统和玩家状态
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
        for ($i = 0; $i < $this->m_rule->player_count ; $i ++)
        {
            if($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU)
            {
                $this->m_sPlayer[$i]->state = ($i == $chair) ? ConstConfig::PLAYER_STATUS_THINK_OUTCARD : ConstConfig::PLAYER_STATUS_WAITING;
            }
        }

        $this->m_chairCurrentPlayer = $chair;
        $this->m_sOutedCard->clear();
        $this->m_sGangPao->clear();
        $this->m_only_out_card[$chair] = true;
        //$this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄

        //状态变化发消息(用于放特效)
        $this->_send_act($this->m_currentCmd, $chair);
        $this->handle_flee_play(true);
        for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
        }

        return true;
    }

    public function HandleChooseZhiGang($chair)
    {
        $temp_card = $this->m_sOutedCard->card;

        $this->_list_delete_multiple_times($chair, $temp_card, 3);

        // 设置倒牌
        $this->_set_down_card($chair, ConstConfig::DAO_PAI_TYPE_MINGGANG, $temp_card, $temp_card, $this->m_sOutedCard->chair);
        
        $stand_count_after = $this->m_sStandCard[$chair]->num;

        $this->m_bHaveGang = true;  //for 杠上花

        $nGangPao = 0;

        //分数结算,每人一分
        $nGangScore = self::M_ZHIGANG_SCORE;
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

        //状态变化发消息
        $this->_send_act($this->m_currentCmd, $chair);
        $this->handle_flee_play(true);  //更新断线用户
        for ($i=0; $i < $this->m_rule->player_count ; $i++)
        {
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
        }
    }

    //处理竞争选择
    public function HandleChooseResult($chair, $nCmdID, $eat_num = null)
    {
        $this->handle_flee_play(true);
        
        //处理竞争
        $order_cmd = array('c_cancle_choice'=>0, 'c_eat'=>1, 'c_peng'=>2, 'c_zhigang'=>3, 'c_hu'=>4);
        if(empty($this->m_currentCmd) || ($order_cmd[$nCmdID] > $order_cmd[$this->m_currentCmd] && $order_cmd[$nCmdID] >= $order_cmd['c_cancle_choice']))   //吃, 碰, 杠竞争
        {
            $this->m_chairSendCmd = $chair;
            $this->m_currentCmd = $nCmdID;
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

        if ($this->m_sQiangGang->mark ) // 处理抢杠
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

                    $nGangScore = self::M_WANGANG_SCORE;

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
                $this->handle_flee_play(true);  //更新断线用户
                for ($i=0; $i < $this->m_rule->player_count ; $i++)
                {
                    $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
                }
                
                $this->m_sQiangGang->clear();               

                return;
            }
        }
        else    // 不是抢杠
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
                case 'c_cancle_choice': // 发牌给下家
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
                    $this->handle_flee_play(true);  //更新断线用户
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

    //处理扣听
    private function handle_kou_ting($chair, $is_kou_ting)
    {
        $this->m_bChooseBuf[$chair] = 0;
        $this->m_kou_ting[$chair] = $is_kou_ting;

        if (!empty($is_kou_ting)) 
        {
            $this->_set_record_game(ConstConfig::RECORD_KOU_TING, $chair);
        }
        
        $sum = 0;
        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            $sum += $this->m_bChooseBuf[$i];
        }

        if($sum == 0)
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
                }

                $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
            }
        }
    }

    //处理拉庄
    private function handle_contend_banker($chair, $is_contend_banker)
    {
        $continue = false;

        $this->contendBankerScore[$chair]->is_received += 1;
        $this->contendBankerScore[$chair]->is_contend_banker[$this->contendBankerScore[$chair]->is_received] = $is_contend_banker;

        $this->_send_cmd('s_contendBanker', array('contendBankerScore' => $this->contendBankerScore), Game_cmd::SCO_ALL_PLAYER);

        if ($chair == $this->m_nChairBanker && $this->contendBankerScore[$chair]->is_received == 2) 
        {
            $continue = true;
        }
        else
        {
            $tmp_contend_banker = array();

            for ($i = 0; $i<$this->m_room_players; ++$i)
            {
                if (empty($this->contendBankerScore[$i]->is_received))
                {
                    break;
                }

                $tmp_contend_banker[$i] = $this->contendBankerScore[$i]->is_contend_banker[1];
            }
            
            if ($i == $this->m_rule->player_count) 
            {
                if (empty($tmp_contend_banker[$this->m_nChairBanker])) 
                {
                    $continue = true;
                }

                if (!empty($tmp_contend_banker[$this->m_nChairBanker]) && array_sum($tmp_contend_banker) == $tmp_contend_banker[$this->m_nChairBanker]) 
                {
                    $continue = true;
                }
            }
        }

        //开始牌局
        if ($continue)
        {
            $param_1 = array_sum($this->contendBankerScore[0]->is_contend_banker);
            $param_2 = array_sum($this->contendBankerScore[1]->is_contend_banker);
            $param_3 = isset($this->contendBankerScore[2]) ? array_sum($this->contendBankerScore[2]->is_contend_banker) : 0;
            $param_4 = isset($this->contendBankerScore[3]) ? array_sum($this->contendBankerScore[3]->is_contend_banker) : 0;

            $this->_set_record_game(ConstConfig::RECORD_CONTEND_BANKER, $param_1, $param_2, $param_3, $param_4);

            $this->DealAllCardEx();

            $this->game_to_playing();

            return true;
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
			$data['m_own_paozi'] = $this->m_own_paozi;
			
			$data['m_wTotalScore'] = $this->m_wTotalScore;
			$data['m_ready'] = $this->m_ready;
			$data['is_cancle'] = $this->m_cancle;
			$data['m_cancle'] = $this->m_cancle;
			$data['m_cancle_first'] = $this->m_cancle_first;

			$data['m_fan_hun_card'] = $this->m_fan_hun_card;		
			$data['m_hun_card'] = $this->m_hun_card;		
            $data['m_contend_banker_score'] = $this->contendBankerScore;
		}
		
        $data['m_kou_ting'] = $this->m_kou_ting;   
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
        $data['m_bLastGameOver'] = $this->m_bLastGameOver;      //胡牌最终结束
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

        //拉庄阶段
		if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_CONTEND_BANKER)
        {
            $data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;                                // 当前出牌者
            return $data;
        }

        //扣听阶段
        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_KOU_TING)
        {
            $data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;                                // 当前出牌者
            $data['m_nNumTableCards'] = $this->m_nNumTableCards;        // 玩家桌面牌数量
            $data['m_nTableCards'] = $this->m_nTableCards;  // 玩家桌面牌
            $data['m_sStandCard'] = $this->m_sStandCard;        // 玩家倒牌
            $data['m_sOutedCard'] = $this->m_sOutedCard;        //刚出的牌

            for ($i=0; $i<$this->m_rule->player_count; $i++)                                         // 玩家手持牌长度
            {
                if($i == $chair)
                {
                    $data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
                    $data['m_bChooseBuf'] = $this->m_bChooseBuf[$i];             //命令缓冲
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

    //每局牌局最终  分  赢的分-输的分
    public function CalcHuScore() 
    {
        $cash = 0;
        //  Score_Struct score[PLAYER_COUNT];
        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            $this->m_Score[$i]->clear();
        }
        for($i=0; $i<$this->m_rule->player_count; $i++)
        {
            $this->m_Score[$i]->score = $this->m_wSetScore[$i]+ $this->m_wSetLoseScore[$i]+ $this->m_wGangScore[$i][$i];
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
            //荒庄荒杠（包括跟庄分数）
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

    //开始拉庄
    public function start_contend_banker()
    {
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_CONTEND_BANKER;
        for ($i = 0; $i < $this->m_rule->player_count ; ++$i)
        {
            $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_CONTEND_BANKER;
            //发消息
            $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
        }
    }

    //开始玩
    public function on_start_game()
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

        //拉庄
        if(!empty($this->m_rule->is_contend_banker))
        {
            $this->start_contend_banker();
            return true;
        }

        //发牌
        $this->DealAllCardEx();
        
        $this->game_to_playing();

        return true;
    }

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

        //订翻混牌
        if(!empty($this->m_rule->is_fanhun))
        {
            $this->m_fan_hun_card = $this->m_nCardBuf[$this->m_nCountAllot++];
            $this->_get_fan_hun($this->m_fan_hun_card);
            $hun_card = $this->m_hun_card[0] * 100 + $this->m_hun_card[1];

            $this->_set_record_game(ConstConfig::RECORD_FANHUN, $this->m_nChairBanker, $this->m_fan_hun_card, $this->m_nChairBanker, $hun_card);
        }

        //扣听
        if (!empty($this->m_rule->is_kou_ting)) 
        {
            $this->m_sysPhase = ConstConfig::SYSTEMPHASE_KOU_TING;

            //状态变化发消息
            for ($i=0; $i < $this->m_rule->player_count; $i++)
            {
                if(!empty($this->m_rule->is_kou_card))
                {
                    $this->m_sPlayer[$i]->kou_card_display = $this->_set_kou_arr($i);
                }

                $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_KOU_TING;

                if($i != $this->m_nChairBanker)
                {
                    $this->m_bChooseBuf[$i] = 1;
                }
                else
                {
                    $this->m_bChooseBuf[$i] = 0;
                }

                $this->_send_cmd('s_sys_phase_change', $this->OnGetChairScene($i, true), Game_cmd::SCO_SINGLE_PLAYER , $this->m_room_players[$i]['uid']);
            }
        }
        else
        {
            //状态设定
            $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD ;
            $this->m_chairCurrentPlayer = $this->m_nChairBanker;

            //状态变化发消息
            for ($i=0; $i < $this->m_rule->player_count; $i++)
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
        }
        
        
        $this->handle_flee_play(true);  //更新断线用户
    }

    // 翻混
    public function _get_fan_hun($fan_hun_card)
    {
        $temp_type = $this->_get_card_type($fan_hun_card);
        $temp_card_index = $fan_hun_card % 16;

        if($temp_type == ConstConfig::PAI_TYPE_WAN || $temp_type == ConstConfig::PAI_TYPE_TIAO || $temp_type ==ConstConfig::PAI_TYPE_TONG )
        {
            $tmp_index_array = array(0,2,3,4,5,6,7,8,9,1);
        }
        elseif($temp_type == ConstConfig::PAI_TYPE_FENG)
        {
            $tmp_index_array = array(0,2,3,4,1,6,7,5);
        }
        elseif($temp_type == ConstConfig::PAI_TYPE_DRAGON)
        {
            $tmp_index_array = array(0,2,3,4,1,6,7,8,5);
        }
        else
        {
            echo("混牌错误，出现未定义类型的牌".__LINE__.__CLASS__);
            return false;
        }

        $this->m_hun_card[] = $this->m_fan_hun_card;
        $this->m_hun_card[] = $this->_get_card_index($temp_type,$tmp_index_array[$temp_card_index]);
        return $this->m_hun_card;
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

            if($this->m_wGangScore[$i][$i]>0)
            {
                $this->m_hu_desc[$i] .= '杠分+'.$this->m_wGangScore[$i][$i].' ';
            }
            else
            {
                $this->m_hu_desc[$i] .= '杠分'.$this->m_wGangScore[$i][$i].' ';
            }
            if (!empty($this->m_rule->is_kou_ting)) 
            {
                if ($this->m_kou_ting[$i]) 
                {
                    $this->m_hu_desc[$i] .= '扣听 ';
                }
            }
            
            if (!empty($this->m_rule->is_contend_banker)) 
            {
                $count = array_sum($this->contendBankerScore[$i]->is_contend_banker);
                if ($count) 
                {
                    if ($i == $this->m_nChairBanker) 
                    {
                        
                        $this->m_hu_desc[$i] .= '坐庄'.$count.'次';
                    }
                    else
                    {
                        $this->m_hu_desc[$i] .= '拉庄';
                    }
                }
            }
        }
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
				if($i == $chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU)
				{
					continue;
				}

				$PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;

                //扣听分数
                $kou_ting_fan = 1;
                if (!empty($this->m_rule->is_kou_ting)) 
                {
                    $kou_ting_fan = empty($this->m_kou_ting[$chair]) ? 1 : 4;
                    if (!empty($this->m_kou_ting[$i])) 
                    {
                        $kou_ting_fan *= 4;
                    }
                }
           
                //拉庄分数
                $contend_banker_fan = 0;
                if (!empty($this->m_rule->is_contend_banker)) 
                {
                    if ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i) 
                    {
                        $contend_banker_fan = array_sum($this->contendBankerScore[$chair]->is_contend_banker);
                        $count = array_sum($this->contendBankerScore[$i]->is_contend_banker);
                        $contend_banker_fan += $count;
                    }
                }
               
				$wWinScore = $PerWinScore * $kou_ting_fan * pow(2, $contend_banker_fan);

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

		echo("此人没有胡".__LINE__.__CLASS__);
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
            if ($hu_type != self::HU_TYPE_PINGHU) 
            {
                $tmp_hu_desc .= self::$hu_type_arr[$hu_type][2].' ';
            }
        }

        for($i=1; $i<$this->m_HuCurt[$chair]->count; $i++)
        {
            if(isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]]))
            {
                $fan_sum *= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
                $tmp_hu_desc .= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';
            }
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

        if ($fan_sum == 1)
        {
            $tmp_hu_desc = "自摸胡 ";
        }

        $this->m_hu_desc[$chair] = $tmp_hu_desc;
        return $fan_sum;
    }

	//不带混捉五魁
	public function _is_danting_wuwan($chair)
	{
		$is_danting_wuwan = true;
		$replace_card = array(1,2,3,4,6,7,8,9,17,18,19,20,21,22,23,24,25,33,34,35,36,37,38,39,40,41,49,50,51,52,53,54,55);
		
        if (empty($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_WAN][5])) 
        {
            return false;
        }
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

    //不带混天胡捉五魁
    public function _is_tianhu_zhuowu($chair)
    {
        if (
            empty($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_WAN][4])
            || empty($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_WAN][5])
            || empty($this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_WAN][6])
        ) 
        {
            return false;
        }

        $this->_list_delete($chair,4);
        $this->_list_delete($chair,5);
        $this->_list_delete($chair,6);

        $tmp_hu_data = &ConstConfig::$hu_data;
        $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

        $this->_list_insert($chair, 4);
        $this->_list_insert($chair, 5);
        $this->_list_insert($chair, 6);

        if (isset($tmp_hu_data[$key]) && ($tmp_hu_data[$key] & 1) == 1) 
        {
            $hu_type = self::HU_TYPE_ZHUOWUKUI;
        }
        else
        {
            $hu_type = self::HU_TYPE_FENGDING_TYPE_INVALID;
        }

        return $hu_type;
    }

    //带混捉五判断
    private function _is_zhuo_wu_hun($chair, $insert_arr, $is_tianhu)
    {   
        $type = ConstConfig::PAI_TYPE_WAN;

        if (
            empty($this->m_sPlayer[$chair]->card[$type][4])
            || empty($this->m_sPlayer[$chair]->card[$type][5])
            || empty($this->m_sPlayer[$chair]->card[$type][6])
        ) 
        {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        }

        $this->_list_delete($chair, 4);
        $this->_list_delete($chair, 5);
        $this->_list_delete($chair, 6);

        $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$type], 1)));
        $tmp_hu_data = &ConstConfig::$hu_data;

        $this->_list_insert($chair, 4);
        $this->_list_insert($chair, 5);
        $this->_list_insert($chair, 6);

        if (isset($tmp_hu_data[$key]) && ($tmp_hu_data[$key] & 1) == 1) 
        {
            $hu_type = self::HU_TYPE_ZHUOWUKUI;
            if ($is_tianhu) 
            {
                if (in_array(4, $insert_arr) && in_array(6, $insert_arr))
                {
                    $hu_type = self::HU_TYPE_SHUANGHUN_ZHUOWUKUI;
                }
            }
            else
            {
                if (in_array(4, $insert_arr) && in_array(6, $insert_arr) && (in_array(5, $insert_arr) || $this->m_HuCurt[$chair]->card == 5))
                {
                    $hu_type = self::HU_TYPE_SHUANGHUN_ZHUOWUKUI;
                }
            }
        }
        else
        {
            $hu_type = self::HU_TYPE_FENGDING_TYPE_INVALID;
        }

        return $hu_type;
    }

    //32混儿吊
    private function _is_hunyou($chair, $insert_arr, $jiang_type, $is_hu_card_hun)
    {
        $hu_type = self::HU_TYPE_FENGDING_TYPE_INVALID;
        $temp_card = $this->m_HuCurt[$chair]->card;

        if ($is_hu_card_hun)
        {
            $array = array_count_values($insert_arr);
            foreach ($array as $card => $num) {
                if ($num >= 2) {
                    $temp_cards = $this->_get_card_index($jiang_type, $card);
                    $hu_type = $this->_judge32hundiao($chair, $temp_cards, $jiang_type);
                    if ($hu_type) {
                        break;
                    }
                }
            }
        }
        else
        {
            $hu_type = $this->_judge32hundiao($chair, $temp_card, $jiang_type);
        }

        return $hu_type;
    }

    //天胡32混儿吊
    private function _is_tianhu_hunyou($chair, $insert_arr, $jiang_type)
    {   
        $hu_type = self::HU_TYPE_FENGDING_TYPE_INVALID;
        foreach ($insert_arr as $key => $card) 
        {
            if ($this->m_sPlayer[$chair]->card[$jiang_type][$card] >= 2) 
            {
                $temp_card = $this->_get_card_index($jiang_type, $card);
                $hu_type = $this->_judge32hundiao($chair, $temp_card, $jiang_type);
                
                if ($hu_type == self::HU_TYPE_HUNDIAO) 
                {
                    break;
                }
            }
        }

        return $hu_type;
    }

    private function _judge32hundiao($chair, $jiang_card, $jiang_type)
    {
        $hu_type = self::HU_TYPE_FENGDING_TYPE_INVALID;

        $this->_list_delete_multiple_times($chair,$jiang_card, 2);

        $tmp_hu_data = &ConstConfig::$hu_data;
        if($jiang_type == ConstConfig::PAI_TYPE_FENG)
        {
            $tmp_hu_data = &ConstConfig::$hu_data_feng;
        }

        $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$jiang_type], 1)));
        if(isset($tmp_hu_data[$key]) && ($tmp_hu_data[$key] & 1 ) == 1)
        {
            $hu_type = self::HU_TYPE_HUNDIAO;
        }

        //还原手牌
        $this->_list_insert_multiple_times($chair,$jiang_card, 2);

        return $hu_type;
    }

    //参数校验公共函数
    private function _validateParameterAndPhase($params, $rule_group, $phase)
    {
        $error_code = 0;

        foreach ($rule_group as $argument => $rules_of_single_param) {
            $rules_of_single_param = explode("|", $rules_of_single_param);
            foreach ($rules_of_single_param as $rule) {
                switch ($rule) {
                    case 'required':
                        if (empty($params[$argument])) {
                            $error_code = ConstConfig::PARAMETER_ERROR;
                        }
                        break;
                    
                    case 'exit':
                        if (!exit($params[$argument])) {
                            $error_code = ConstConfig::PARAMETER_ERROR;
                        }
                        break;

                    default:
                        break;
                }
            }
        }

        if ($error_code == 0 && $this->m_sysPhase != $phase) {
            $error_code = SYSTERM_PHASE_ERROR;
        }

        return $error_code;
    }

    private function _deBugs($error_code, &$return_send, $line)
    {
        $error_describe = ConstConfig::$error_describe;
        if (isset($error_describe[$error_code]))
        {
            $return_send['code'] = $error_code;
            $return_send['text'] = $error_describe[$error_code];
            $return_send['desc'] = $line;
        }
    }

    //判断有没有碰
    public function _find_peng($chair)
    {
        $error_code = 0;

        if($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
        {
            $error_code = ConstConfig::PLAYER_PHASE_ERROR;
        }

        $card_type = $this->_get_card_type($this->m_sOutedCard->card);
        if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
        {
            $error_code = ConstConfig::PENG_ERROR;
        }

        if(empty($this->m_sOutedCard->card))
        {
            $error_code = ConstConfig::NOT_HAVE_OUT_CARD;
        }

        if ($this->m_sOutedCard->chair == $chair)
        {
            $error_code = ConstConfig::CURRENT_USER_ERROR;
        }

        $card_count = $this->_list_find($chair, $this->m_sOutedCard->card);
        if ($card_count < 2)
        {
            $error_code = ConstConfig::NOT_HAVE_PENG;
        }

        return $error_code;
    }

    //判断有没有别人打来的明杠
    public function _find_zhi_gang($chair)
    {
        $error_code = 0;

        if($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING)
        {
            $error_code = ConstConfig::PLAYER_PHASE_ERROR;
        }

        $card_type = $this->_get_card_type($this->m_sOutedCard->card);
        if(ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type)
        {
            $error_code = ConstConfig::GANG_ERROR;
        }

        if (empty($this->m_sOutedCard->card))
        {
            $error_code = ConstConfig::NOT_HAVE_OUT_CARD;
        }

        if ($this->m_sOutedCard->chair == $chair)
        {
            $error_code = ConstConfig::CURRENT_USER_ERROR;
        }

        $card_count = $this->_list_find($chair, $this->m_sOutedCard->card);
        if($card_count != 3)
        {
            $error_code = ConstConfig::NOT_HAVE_ZHIGANG;
        }

        return $error_code;
    }
    
    //判断有没有杠
    private function _find_an_gang($chair, $gang_card)
    {
        $error_code = 0;

        $card_type = $this->_get_card_type($gang_card);
        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
        {
            $error_code = ConstConfig::GANG_ERROR;
        }

        if($chair != $this->m_chairCurrentPlayer)
        {
            $error_code = ConstConfig::CURRENT_USER_ERROR;
        }

        if ($gang_card == $this->m_fan_hun_card) 
        {
            if(3 != $this->_list_find($chair,$gang_card)
                && !(($gang_card == $this->m_sPlayer[$chair]->card_taken_now) && 2 == $this->_list_find($chair,$gang_card))
            )
            {
                $error_code = ConstConfig::GANG_ERROR;
            }
        }
        else
        {
            if(4 != $this->_list_find($chair,$gang_card)
            && !(($gang_card == $this->m_sPlayer[$chair]->card_taken_now) && 3 == $this->_list_find($chair,$gang_card))
            )
            {
                $error_code = ConstConfig::GANG_ERROR;
            }
        }

        return $error_code;
    }

    private function _find_wan_gang($chair, $gang_card)
    {
        $error_code = 0;

        $card_type = $this->_get_card_type($gang_card);
        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
        {
            $error_code = ConstConfig::GANG_ERROR;
        }

        if($chair != $this->m_chairCurrentPlayer)
        {
            $error_code = ConstConfig::CURRENT_USER_ERROR;
        }

        $have_wan_gang = false;
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i ++)
        {
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                && $this->m_sStandCard[$chair]->card[$i] == $gang_card)
            {
                $have_wan_gang = true;
                break;
            }
        }
        
        if(!$have_wan_gang || ($gang_card != $this->m_sPlayer[$chair]->card_taken_now && 0 == $this->_list_find($chair,$gang_card)))
        {
            $error_code = ConstConfig::GANG_ERROR;
        }
    }

    //删除出牌者最后一张桌面牌
    private function _deleteLastTableCard($chair, $card)
    {
        $this->m_nNumTableCards[$chair] = $this->m_nNumTableCards[$chair] -1;
        if($this->m_nTableCards[$chair][$this->m_nNumTableCards[$chair]] == $card)
        {
            unset($this->m_nTableCards[$chair][$this->m_nNumTableCards[$chair]]);
        }
    }

    //处理出牌 
    public function HandleOutCard($chair, $is_14 = false, $out_card = 0, $is_ting = 1)      
    {
        //一旦有人出牌，表示上一轮竞争已经结束, 可以清CMD
        $this->m_chairSendCmd = 255;                            // 当前发命令的玩家
        $this->m_currentCmd = 0;                            // 当前的命令
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
        else if($temp_out_card) //若打出的是第1-13张牌, 要整理牌列表
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

        $this->m_nNumCmdHu = 0; //重置抢胡牌命令个数
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

        $this->handle_flee_play(true);  //更新断线用户

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

            if(empty($this->_find_peng($chair_next)) || empty($this->_find_zhi_gang($chair_next)))
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

    //找出第14张牌
    public function _find_14_card($chair)
    {
        $last_type = ConstConfig::PAI_TYPE_DRAGON;
        
        if(!empty($this->m_rule->is_kou_card))
        {
            foreach ($this->m_sPlayer[$chair]->kou_card_display as $value)
            {
                $this->_list_delete($chair, $value);
            }
        }
        
        if(!empty($this->m_hun_card))
        {
            $hun_num = $this->_list_find($chair, $this->m_hun_card[1]);        
            $fanhun_num = $this->_list_find($chair, $this->m_hun_card[0]);  
            $total_hun_num = $hun_num + $fanhun_num;
            $hun_type = $this->_get_card_type($this->m_hun_card[0]);
            $hun_card = $this->m_hun_card[1] % 16;
            $fanhun_card = $this->m_hun_card[0] % 16;
        }
        if(!empty($total_hun_num))
        {
            $this->m_sPlayer[$chair]->card[$hun_type][$hun_card] = 0;
            $this->m_sPlayer[$chair]->card[$hun_type][$fanhun_card] = 0;
            $this->m_sPlayer[$chair]->card[$hun_type][0] -= $total_hun_num;
            $this->m_sPlayer[$chair]->len -= $total_hun_num;
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
            if(!empty($total_hun_num))
            {
                $this->m_sPlayer[$chair]->card[$hun_type][$hun_card] = $hun_num;
                $this->m_sPlayer[$chair]->card[$hun_type][$fanhun_card] = $fanhun_num;
                $this->m_sPlayer[$chair]->card[$hun_type][0] += $total_hun_num;
                $this->m_sPlayer[$chair]->len += $total_hun_num;

                if(!empty($this->m_rule->is_kou_card))
                {
                    foreach ($this->m_sPlayer[$chair]->kou_card_display as $value)
                    {
                        $this->_list_insert($chair, $value);
                    }
                }

                echo ("竟然没有牌aaaaaaaas".__LINE__.__CLASS__ );
                return false;
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

        if(!empty($total_hun_num))
        {
            $this->m_sPlayer[$chair]->card[$hun_type][$hun_card] = $hun_num;
            $this->m_sPlayer[$chair]->card[$hun_type][$fanhun_card] = $fanhun_num;
            $this->m_sPlayer[$chair]->card[$hun_type][0] += $total_hun_num;
            $this->m_sPlayer[$chair]->len += $total_hun_num;
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

    //删除多张
    public function _list_delete_multiple_times($chair, $card, $times = 1)
    {
        $card_type = $this->_get_card_type($card);
        if($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
        {
            return false;
        }

        $card_key = $card%16;
        if($this->m_sPlayer[$chair]->card[$card_type][$card_key] >= $times)
        {
            $this->m_sPlayer[$chair]->card[$card_type][$card_key] -= $times;
            $this->m_sPlayer[$chair]->card[$card_type][0] -= $times;
            $this->m_sPlayer[$chair]->len -= $times;
            return true;
        }
        return false;
    }

    //插入多张
    public function _list_insert_multiple_times($chair, $card, $times = 1)
    {
        $card_type = $this->_get_card_type($card);
        if($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID)
        {
            echo("错误牌类型，_list_insert".__LINE__.__CLASS__);
            return false;
        }

        $card_key = $card % 16;
        $this->m_sPlayer[$chair]->card[$card_type][$card_key] += $times;
        $this->m_sPlayer[$chair]->card[$card_type][0] += $times;
        $this->m_sPlayer[$chair]->len += $times;

        return true;
    }

    //设置倒牌
    private function _set_down_card($chair, $type, $first_card, $act_card, $who_give_me)
    {
        $stand_count = $this->m_sStandCard[$chair]->num;
        $this->m_sStandCard[$chair]->type[$stand_count] = $type;
        $this->m_sStandCard[$chair]->first_card[$stand_count] = $first_card;
        $this->m_sStandCard[$chair]->card[$stand_count] = $act_card;
        $this->m_sStandCard[$chair]->who_give_me[$stand_count] = $who_give_me;
        $this->m_sStandCard[$chair]->num ++;
    }
    
}
