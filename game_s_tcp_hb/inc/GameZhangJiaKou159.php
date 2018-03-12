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

class GameZhangJiaKou159 extends BaseGame
{
    const GAME_TYPE = 273;

    //－－－－－－－－－－－－－胡牌类型 －－－－－－－－－－－－－－－－－－－
    const HU_TYPE_PINGHU = 21;                // 平胡
    const HU_TYPE_QIDUI = 22;                 // 七对
    const HU_TYPE_SIHUNZI = 23;               // 四混儿
    const HU_TYPE_FENGDING_TYPE_INVALID = 0;  // 错误

    //－－－－－－－－－－－－－附加番 －－－－－－－－－－－－－－－－－－－
    const ATTACHED_HU_QIANGGANG = 61;         // 抢杠
    const ATTACHED_HU_SUHU = 62;           	  // 无王牌
    const ATTACHED_HU_ZIMOFAN = 63;           //自摸

    //－－－－－－－－－－－－－杠分 －－－－－－－－－－－－－－－－－－－
    const M_ZHIGANG_SCORE = 2;                // 直杠 2分
    const M_ANGANG_SCORE = 4;                 // 暗杠 4分
    const M_WANGANG_SCORE = 2;                // 弯杠 2分

    public static $hu_type_arr = array(
    self::HU_TYPE_PINGHU=>[self::HU_TYPE_PINGHU, 2, '平胡']
    ,self::HU_TYPE_QIDUI=>[self::HU_TYPE_QIDUI, 2, '七对']
    ,self::HU_TYPE_SIHUNZI=>[self::HU_TYPE_SIHUNZI, 10, '四个王']
    
    );

    public static $attached_hu_arr = array(
    //self::ATTACHED_HU_QIANGGANG=>[self::ATTACHED_HU_QIANGGANG, 0, '抢杠胡']
    self::ATTACHED_HU_SUHU=>[self::ATTACHED_HU_SUHU, 2, '无王牌']  //2倍
    //,self::ATTACHED_HU_ZIMOFAN=>[self::ATTACHED_HU_ZIMOFAN, 0, '自摸']
    );

    public $m_cancle_time;                 // 解散房间开始时间
    public $m_bSiwang = false;         	   // 是否为四王胡
    public $m_ZhaMaCard = array();         // 扎码翻出的牌
    public $m_wZhamaScore = array(0,0,0,0);// 扎码分数
    public $m_nPengGiveUp = array();	   // 该轮放弃碰的,m_nPengGiveUp = [][0]: 个数

    ///////////////////////方法/////////////////////////
    //同圈过碰
    public static function is_peng_give_up($peng_card, $peng_give_up)
	{
		if($peng_give_up == 0 || $peng_card == 0)
		{
			return 0;
		}
		else
		{
			if($peng_give_up % 100 == $peng_card)
			{
				return 1;
			}
			else
			{
				return self::is_peng_give_up($peng_card, floor($peng_give_up/100));
			}
		}
	}

    //构造方法
    public function __construct($serv)
    {
        parent::__construct($serv);
        $this->m_game_type = self::GAME_TYPE;
    }

    public function InitDataSub()
    {
        $this->m_game_type = self::GAME_TYPE;   //游戏类型，见http端协议
        $this->m_cancle_time = 0;
        $this->m_bSiwang = false;
        $this->m_ZhaMaCard = array();
        $this->m_wZhamaScore = array(0,0,0,0);
        for ($i = 0; $i<$this->m_rule->player_count ; ++$i)
        {
            $this->m_nPengGiveUp[$i] = 0;
        }
    }

    public function _open_room_sub($params)
    {
        $this->m_rule = new RuleZhangjiakou159();
        if (empty($params['rule']['player_count']) || !in_array($params['rule']['player_count'],
                array(1, 2, 3, 4))
        ) {
            $params['rule']['player_count'] = 4;
        }

        $params['rule']['min_fan'] = !isset($params['rule']['min_fan']) ? 0 : $params['rule']['min_fan'];
		$params['rule']['top_fan'] = !isset($params['rule']['top_fan']) ? 255 : $params['rule']['top_fan'];
		$params['rule']['is_yipao_duoxiang'] = !isset($params['rule']['is_yipao_duoxiang']) ? 0 : $params['rule']['is_yipao_duoxiang'];
		$params['rule']['is_genzhuang'] = !isset($params['rule']['is_genzhuang']) ? 1 : $params['rule']['is_genzhuang'];
		$params['rule']['is_wangpai'] = !isset($params['rule']['is_wangpai']) ? 1 : $params['rule']['is_wangpai'];
		
		$params['rule']['is_siwangpai'] = !isset($params['rule']['is_siwangpai']) ? 1 : $params['rule']['is_siwangpai'];
		$params['rule']['is_zhama'] = !isset($params['rule']['is_zhama']) ? 0 : $params['rule']['is_zhama'];
		$params['rule']['is_chipai'] = !isset($params['rule']['is_chipai']) ? 0 : $params['rule']['is_chipai'];
		$params['rule']['is_suhu_fan'] = !isset($params['rule']['is_suhu_fan']) ? 1 : $params['rule']['is_suhu_fan'];

        $this->m_rule->game_type = $params['rule']['game_type'];
        $this->m_rule->player_count = $params['rule']['player_count'];
        $this->m_rule->set_num = $params['rule']['set_num'];
       
        $this->m_rule->min_fan = $params['rule']['min_fan'];
        $this->m_rule->top_fan = $params['rule']['top_fan'];
        $this->m_rule->is_genzhuang = $params['rule']['is_yipao_duoxiang'];
        $this->m_rule->is_genzhuang = $params['rule']['is_genzhuang'];
        $this->m_rule->is_wangpai = $params['rule']['is_wangpai'];
        
        $this->m_rule->is_siwangpai = $params['rule']['is_siwangpai'];
        $this->m_rule->is_zhama = $params['rule']['is_zhama'];
        $this->m_rule->is_chipai = $params['rule']['is_chipai'];
        $this->m_rule->is_suhu_fan = $params['rule']['is_suhu_fan'];
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

            $this->handle_flee_play(true);  //更新断线用户
            $this->_cancle_game();

        }while(false);

        $this->serv->send($fd,  Room::tcp_encode(($return_send)));

        return $return_send['code'];
    }

    //下炮子
    public function c_pao_zi($fd, $params)
    {
        $return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
        do {
            if( empty($params['rid'])
                || empty($params['uid'])
                || !isset($params['pao_zi_num'])
                || !in_array($params['pao_zi_num'], array(0, 1, 2, 3, 4 ))
            )
            {
                $return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            if($this->m_sysPhase != ConstConfig::SYSTEMPHASE_XIA_PAO || ConstConfig::ROOM_STATE_GAMEING != $this->m_room_state)
            {
                $return_send['code'] = 2; $return_send['text'] = '房间状态错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user)
            {
                if($room_user['uid'] == $params['uid'])
                {
                    if ($this->m_own_paozi[$key]->recv)
                    {
                        $return_send['code'] = 4; $return_send['text'] = '您已经下炮子了'; $return_send['desc'] = __LINE__.__CLASS__; break 2;
                    }

                    $this->handle_pao_zi($key, $params['pao_zi_num']);
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

    ///////////////////出牌阶段//////////////////////

    //自摸
    public function c_zimo_hu($fd, $params)
    {
        $return_send = array("act" => "s_result", "info" => __FUNCTION__, "code" => 0, "desc" => __LINE__.__CLASS__);
        do {
            if (empty($params['rid'])
                || empty($params['uid'])
            ) {
                $return_send['code'] = 1;
                $return_send['text'] = '参数错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD) {
                $return_send['code'] = 2;
                $return_send['text'] = '房间状态错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user) {
                if ($room_user['uid'] == $params['uid']) {
                    //连续发胡牌请求
                    if (empty($this->m_room_players[$key]['hu_time']) || (time() - $this->m_room_players[$key]['hu_time']) > 2) {
                        $this->m_room_players[$key]['hu_time'] = time();
                    } else {
                        {
                            $return_send['code'] = 6;
                            $return_send['text'] = '连续发送胡牌信息';
                            $return_send['desc'] = __LINE__.__CLASS__;
                            break 2;
                        }
                    }

                    if ($this->m_sPlayer[$key]->state != ConstConfig::PLAYER_STATUS_CHOOSING) {
                        $return_send['code'] = 7;
                        $return_send['text'] = '胡牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    if ($key != $this->m_chairCurrentPlayer) {
                        $return_send['code'] = 4;
                        $return_send['text'] = '当前用户错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }
                    if ($this->m_only_out_card[$key] == true) {
                        $return_send['code'] = 6;
                        $return_send['text'] = '当前用户状态只能出牌';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }
                    if (!$this->HandleHuZiMo($key)) {    // 诈胡
                        $this->_clear_choose_buf($key);
                        $return_send['code'] = 5;
                        $return_send['text'] = '诈胡';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }
                    $this->_clear_choose_buf($key);      //自摸不可能抢杠胡
                    $is_act = true;
                }
            }
            if (!$is_act = true) {
                $return_send['code'] = 3;
                $return_send['text'] = '用户不属于本房间';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }
        } while (false);

        $this->serv->send($fd, Room::tcp_encode(($return_send)));

        return $return_send['code'];
    }

    //暗杠
    public function c_an_gang($fd, $params)
    {
        $return_send = array("act" => "s_result", "info" => __FUNCTION__, "code" => 0, "desc" => __LINE__.__CLASS__);
        do {
            if (empty($params['rid'])
                || empty($params['uid'])
                || empty($params['gang_card'])
            ) {
                $return_send['code'] = 1;
                $return_send['text'] = '参数错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD) {
                $return_send['code'] = 2;
                $return_send['text'] = '房间状态错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user) {
                if ($room_user['uid'] == $params['uid']) {
                    if ($key != $this->m_chairCurrentPlayer) {
                        $return_send['code'] = 4;
                        $return_send['text'] = '当前用户错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    if (4 != $this->_list_find($key, $params['gang_card'])
                        && !(($params['gang_card'] == $this->m_sPlayer[$key]->card_taken_now) && 3 == $this->_list_find($key,$params['gang_card']))) 
                    {
                        $return_send['code'] = 5;
                        $return_send['text'] = '杠牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    if($params['gang_card'] == 55)
                    {
                        $return_send['code'] = 5;
                        $return_send['text'] = '杠牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    $this->_clear_choose_buf($key);
                    $this->HandleChooseAnGang($key, $params['gang_card']);
                    $is_act = true;
                }
            }
            if (!$is_act = true) {
                $return_send['code'] = 3;
                $return_send['text'] = '用户不属于本房间';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }
        } while (false);

        $this->serv->send($fd, Room::tcp_encode(($return_send)));

        return $return_send['code'];
    }

    //弯杠
    public function c_wan_gang($fd, $params)
    {
        $return_send = array("act" => "s_result", "info" => __FUNCTION__, "code" => 0, "desc" => __LINE__.__CLASS__);
        do {
            if (empty($params['rid'])
                || empty($params['uid'])
                || empty($params['gang_card'])
            ) {
                $return_send['code'] = 1;
                $return_send['text'] = '参数错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD) {
                $return_send['code'] = 2;
                $return_send['text'] = '房间状态错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user) {
                if ($room_user['uid'] == $params['uid']) {
                    if ($key != $this->m_chairCurrentPlayer) {
                        $return_send['code'] = 4;
                        $return_send['text'] = '当前用户错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    $have_wan_gang = false;
                    for ($i = 0; $i < $this->m_sStandCard[$key]->num; $i++) {
                        if ($this->m_sStandCard[$key]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                            && $this->m_sStandCard[$key]->card[$i] == $params['gang_card']
                        ) {
                            $have_wan_gang = true;
                            break;
                        }
                    }
                    if (!$have_wan_gang || ($params['gang_card'] != $this->m_sPlayer[$key]->card_taken_now && 0 == $this->_list_find($key,$params['gang_card']))) 
                    {
                        $return_send['code'] = 5;
                        $return_send['text'] = '杠牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    $this->_clear_choose_buf($key);
                    $this->HandleChooseWanGang($key, $params['gang_card']);
                    $is_act = true;
                }
            }
            if (!$is_act = true) {
                $return_send['code'] = 3;
                $return_send['text'] = '用户不属于本房间';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }
        } while (false);

        $this->serv->send($fd, Room::tcp_encode(($return_send)));

        return $return_send['code'];
    }

    //出牌
    public function c_out_card($fd, $params)
    {
        $return_send = array("act" => "s_result", "info" => __FUNCTION__, "code" => 0, "desc" => __LINE__.__CLASS__);
        do {
            if (empty($params['rid'])
                || empty($params['uid'])
                || (empty($params['is_14']) && empty($params['out_card']))
            ) {
                $return_send['code'] = 1;
                $return_send['text'] = '参数错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD) {
                $return_send['code'] = 2;
                $return_send['text'] = '房间状态错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            if(!isset($params['is_ting']))
			{
				$params['is_ting'] = 1;
			}
            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user) {
                if ($room_user['uid'] == $params['uid']) {
                    if ($key != $this->m_chairCurrentPlayer) {
                        $return_send['code'] = 4;
                        $return_send['text'] = '当前用户错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    $this->_clear_choose_buf($key);
                    if (empty($params['is_14']) && 0 == $this->_list_find($key, $params['out_card'])) {
                        $return_send['code'] = 5;
                        $return_send['text'] = '出牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }
                    $tmp_card = 0;
                    if (!empty($params['is_14'])) {
                        $tmp_card = $this->m_sPlayer[$key]->card_taken_now;
                    } else {
                        if (!empty($params['out_card'])) {
                            $tmp_card = $params['out_card'];
                        }
                    }

                    $this->HandleOutCard($key, $params['is_14'], $params['out_card'], $params['is_ting']);
                    $is_act = true;
                }
            }
            if (!$is_act = true) {
                $return_send['code'] = 3;
                $return_send['text'] = '用户不属于本房间';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }
        } while (false);

        $this->serv->send($fd, Room::tcp_encode(($return_send)));

        return $return_send['code'];
    }

    //自己出牌阶段取消操作（暗杠 弯杠 自摸胡）
    public function c_cancle_gang($fd, $params)
    {
        $return_send = array("act" => "s_result", "info" => __FUNCTION__, "code" => 0, "desc" => __LINE__.__CLASS__);
        do {
            if (empty($params['rid'])
                || empty($params['uid'])
            ) {
                $return_send['code'] = 1;
                $return_send['text'] = '参数错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD) {
                $return_send['code'] = 2;
                $return_send['text'] = '房间状态错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user) 
            {
                if ($room_user['uid'] == $params['uid']) 
                {
                    if ($key != $this->m_chairCurrentPlayer) 
                    {
                        $return_send['code'] = 4;
                        $return_send['text'] = '当前用户错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    $this->m_sPlayer[$key]->state = ConstConfig::PLAYER_STATUS_THINK_OUTCARD;
                    $this->_clear_choose_buf($key);
                    $is_act = true;
                }
            }
            if (!$is_act = true) {
                $return_send['code'] = 3;
                $return_send['text'] = '用户不属于本房间';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }
        } while (false);

        $this->serv->send($fd, Room::tcp_encode(($return_send)));

        return $return_send['code'];
    }

    /////////////选择阶段/////////////////

    //胡
    public function c_hu($fd, $params)
    {
        $return_send = array("act" => "s_result", "info" => __FUNCTION__, "code" => 0, "desc" => __LINE__.__CLASS__);

        do {
            if (empty($params['rid'])
                || empty($params['uid'])
            ) {
                $return_send['code'] = 1;
                $return_send['text'] = '参数错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING) {
                $return_send['code'] = 2;
                $return_send['text'] = '房间状态错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user) 
            {
                if ($room_user['uid'] == $params['uid']) 
                {
                    //连续发胡牌请求
                    if (empty($this->m_room_players[$key]['hu_time']) || (time() - $this->m_room_players[$key]['hu_time']) > 2) 
                    {
                        $this->m_room_players[$key]['hu_time'] = time();
                    } 
                    else 
                    {
                        $return_send['code'] = 6;
                        $return_send['text'] = '连续发送胡牌信息';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    $params['type'] = 4;
                    if(empty($this->m_sQiangGang->card) || ($this->m_sQiangGang->card && $this->m_sQiangGang->chair == $key)) 
					{
                        $this->c_cancle_choice($fd, $params);
                        $return_send['code'] = 5;
                        $return_send['text'] = '胡牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }
                    if($this->m_sPlayer[$key]->state != ConstConfig::PLAYER_STATUS_CHOOSING) 
                    {
                        $this->c_cancle_choice($fd, $params);
                        $return_send['code'] = 5;
                        $return_send['text'] = '胡牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    if($this->m_sQiangGang->card && $this->m_sQiangGang->mark) 
                    {
                        $temp_card = $this->m_sQiangGang->card;
                    } 
					else 
                    {
                        $this->c_cancle_choice($fd, $params);
                        $return_send['code'] = 5;
                        $return_send['text'] = '胡牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }
                    $this->_list_insert($key, $temp_card);
                    $this->m_HuCurt[$key]->card = $temp_card;
                    if (!$this->judge_hu($key)) 
                    {
                        $this->m_HuCurt[$key]->clear();
                        $this->_list_delete($key, $temp_card);
                        $this->HandleZhaHu($key);
                        $this->c_cancle_choice($fd, $params);
                        $return_send['code'] = 4;
                        $return_send['text'] = '当前用户诈胡';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }
                    $this->m_HuCurt[$key]->clear();
                    $this->_list_delete($key, $temp_card);

                    $this->_clear_choose_buf($key, false);
                    $this->HandleChooseResult($key, $params['act']);
                    $is_act = true;

                    //判断截胡和一炮多响
                    for ($i = 0; $i < $this->m_rule->player_count; $i++) 
                    {
                        $c_act = "c_hu";
                        $last_chair = $i;
                        if ($last_chair == $this->m_chairCurrentPlayer || !($this->m_bChooseBuf[$last_chair]) || $i == $key) 
                        {
                            continue;
                        }

                        $this->_list_insert($last_chair, $temp_card);
                        $this->m_HuCurt[$last_chair]->card = $temp_card;
                        if (self::is_hu_give_up($temp_card,$this->m_nHuGiveUp[$last_chair]) || !$this->judge_hu($last_chair)) 
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
            if (!$is_act = true) {
                $return_send['code'] = 3;
                $return_send['text'] = '用户不属于本房间';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }
        } while (false);

        $this->serv->send($fd, Room::tcp_encode(($return_send)));

        return $return_send['code'];
    }

    //碰牌
    public function c_peng($fd, $params)
    {
        $return_send = array("act" => "s_result", "info" => __FUNCTION__, "code" => 0, "desc" => __LINE__.__CLASS__);
        do {
            if (empty($params['rid'])
                || empty($params['uid'])
            ) {
                $return_send['code'] = 1;
                $return_send['text'] = '参数错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING) {
                $return_send['code'] = 2;
                $return_send['text'] = '房间状态错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user) 
            {
                if ($room_user['uid'] == $params['uid']) 
                {
                    $params['type'] = 2;
                    if (!$this->_find_peng($key)) 
                    {
                        $this->c_cancle_choice($fd, $params);
                        $return_send['code'] = 4;
                        $return_send['text'] = '当前用户无碰';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }
                    if (empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || 2 > $this->_list_find($key,$this->m_sOutedCard->card) || $this->m_sOutedCard->card == 55) 
                    {
                        $this->c_cancle_choice($fd, $params);
                        $return_send['code'] = 5;
                        $return_send['text'] = '碰牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    $this->_clear_choose_buf($key);
                    $this->HandleChooseResult($key, $params['act']);
                    $is_act = true;
                }
            }
            if (!$is_act = true) {
                $return_send['code'] = 3;
                $return_send['text'] = '用户不属于本房间';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }
        } while (false);

        $this->serv->send($fd, Room::tcp_encode(($return_send)));

        return $return_send['code'];
    }

    //吃牌
    public function c_eat($fd, $params)
    {
        return self::HU_TYPE_FENGDING_TYPE_INVALID;

        $return_send = array("act" => "s_result", "info" => __FUNCTION__, "code" => 0, "desc" => __LINE__.__CLASS__);
        do {
            if (empty($params['rid'])
                || empty($params['uid'])
                || empty($params['num'])
                || !in_array($params['num'], array(1, 2, 3))
            ) {
                $return_send['code'] = 1;
                $return_send['text'] = '参数错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING) {
                $return_send['code'] = 2;
                $return_send['text'] = '房间状态错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user) {
                if ($room_user['uid'] == $params['uid']) {
                    if (!$this->_find_eat($key, $params['num'])) {
                        $this->c_cancle_choice($fd, $params);
                        $return_send['code'] = 4;
                        $return_send['text'] = '当前用户无吃牌';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }
                    if (empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || $this->m_sOutedCard->chair != $this->_anti_clock($key,
                            -1)
                    ) {
                        $this->c_cancle_choice($fd, $params);
                        $return_send['code'] = 5;
                        $return_send['text'] = '吃牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    $this->_clear_choose_buf($key);
                    $this->HandleChooseResult($key, $params['act'], $params['num']);
                    $is_act = true;
                }
            }
            if (!$is_act = true) {
                $return_send['code'] = 3;
                $return_send['text'] = '用户不属于本房间';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }
        } while (false);

        $this->serv->send($fd, Room::tcp_encode(($return_send)));

        return $return_send['code'];
    }

    //直杠
    public function c_zhigang($fd, $params)
    {
        $return_send = array("act" => "s_result", "info" => __FUNCTION__, "code" => 0, "desc" => __LINE__.__CLASS__);
        do {
            if (empty($params['rid']) || empty($params['uid'])) 
            {
                $return_send['code'] = 1;
                $return_send['text'] = '参数错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING) 
            {
                $return_send['code'] = 2;
                $return_send['text'] = '房间状态错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user) 
            {
                if ($room_user['uid'] == $params['uid']) 
                {
                    if (!$this->_find_zhi_gang($key)) 
                    {
                        $this->c_cancle_choice($fd, $params);
                        $return_send['code'] = 4;
                        $return_send['text'] = '当前用户无直杠';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }
                    if (empty($this->m_sOutedCard->card) || $this->m_sOutedCard->chair == $key || 3 > $this->_list_find($key,$this->m_sOutedCard->card) || $this->m_sOutedCard->card == 55) 
                    {
                        $this->c_cancle_choice($fd, $params);
                        $return_send['code'] = 5;
                        $return_send['text'] = '杠牌错误';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    $this->_clear_choose_buf($key);
                    $this->HandleChooseResult($key, $params['act']);
                    $is_act = true;
                }
            }
            if (!$is_act = true) {
                $return_send['code'] = 3;
                $return_send['text'] = '用户不属于本房间';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }
        } while (false);

        $this->serv->send($fd, Room::tcp_encode(($return_send)));

        return $return_send['code'];
    }

    //选择阶段取消选择（吃 碰 直杠 点炮胡）
    public function c_cancle_choice($fd, $params)
    {
        $return_send = array("act" => "s_result", "info" => __FUNCTION__, "code" => 0, "desc" => __LINE__.__CLASS__);
        do {
            if (empty($params['rid'])
                || empty($params['uid'])
                || !isset($params['type'])
            ) {
                $return_send['code'] = 1;
                $return_send['text'] = '参数错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            $params['act'] = 'c_cancle_choice';

            if ($this->m_sysPhase != ConstConfig::SYSTEMPHASE_CHOOSING) {
                $return_send['code'] = 2;
                $return_send['text'] = '房间状态错误';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }

            $is_act = false;
            foreach ($this->m_room_players as $key => $room_user) {
                if ($room_user['uid'] == $params['uid']) {
                    if (!($this->m_bChooseBuf[$key])) {
                        $return_send['code'] = 4;
                        $return_send['text'] = '当前用户无需选择';
                        $return_send['desc'] = __LINE__.__CLASS__;
                        break 2;
                    }

                    //过手胡
                    if (4 == $params['type']) {
                        $temp_card = $this->m_sOutedCard->card;
                        if ($this->m_sQiangGang->mark) {
                            $temp_card = $this->m_sQiangGang->card;
                        }
                        $this->_list_insert($key, $temp_card);
                        $this->m_HuCurt[$key]->card = $temp_card;

                        if ($this->judge_hu($key)) {
                            $this->m_nHuGiveUp[$key] = $this->m_nHuGiveUp[$key] * 100 + $temp_card;
                        }
                        $this->m_HuCurt[$key]->clear();
                        $this->_list_delete($key, $temp_card);
                    }
                    if($this->_find_peng($key))
                    {
						$temp_card = $this->m_sOutedCard->card;
                    	$this->m_nPengGiveUp[$key] = $this->m_nPengGiveUp[$key] * 100 + $temp_card;
                    }

                    $this->_clear_choose_buf($key, false); //有可能取消的是抢杠胡，这是需要后面判断来补张
                    $this->m_sPlayer[$key]->state = ConstConfig::PLAYER_STATUS_WAITING;
                    $this->HandleChooseResult($key, $params['act']);
                    $is_act = true;
                }
            }
            if (!$is_act = true) {
                $return_send['code'] = 3;
                $return_send['text'] = '用户不属于本房间';
                $return_send['desc'] = __LINE__.__CLASS__;
                break;
            }
        } while (false);

        $this->serv->send($fd, Room::tcp_encode(($return_send)));

        return $return_send['code'];
    }

    ///////////////胡牌逻辑判断区/////////////////

    //判断胡   ok
    public function judge_hu($chair)
    {
        //胡牌型
        $hu_type = $this->judge_hu_type_wangpai($chair, $is_suhu);

        if ($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID) {
            return false;
        }
        //记录在全局数据
        $this->m_HuCurt[$chair]->method[0] = $hu_type;
        $this->m_HuCurt[$chair]->count = 1;

        //抢杠杠开杠炮
        // if ($this->m_sQiangGang->mark && $this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO) 
        // {   
        // 	// 处理抢杠
        //     //$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_QIANGGANG);
        // }

        //素胡
		if($is_suhu && $this->m_rule->is_suhu_fan)
		{
			$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_SUHU);
		}

		//自摸加番
		// if($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)
		// {
		// 	//$this->m_HuCurt[$chair]->add_hu(self::ATTACHED_HU_ZIMOFAN);
		// }

        return true;
    }

    //判断红中癞子
    public function judge_hu_type_wangpai($chair, &$is_suhu)
    {
        $is_suhu = false;
        $fanhun_num = $this->m_sPlayer[$chair]->card[ConstConfig::PAI_TYPE_FENG][7];	//手牌翻混个数
        $fanhun_type = ConstConfig::PAI_TYPE_FENG;        //翻混牌类型
        $fanhun_card = 7;       //翻混牌

        if(0 == $this->m_rule->is_wangpai || 0 >= $fanhun_num)	//规则混子 或者 手牌无混中
        {
            if(!empty($this->m_rule->is_wangpai))
			{
				$is_suhu = true;
			}
            return $this->judge_hu_type($chair);
        }
        else
        {
            $return_type = self::HU_TYPE_FENGDING_TYPE_INVALID;

            //四混儿直接胡
            if ($fanhun_num == 4 && ($this->m_rule->is_siwangpai != 0) && ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO)) {
                $this->m_bSiwang = true;
                return self::HU_TYPE_SIHUNZI;
            }

            //7对牌型
            $need_fanhun = 0;	//需要混子个数
            $hu_qidui = false;
            if($this->m_sStandCard[$chair]->num == 0)
            {
                //去掉翻混
                $this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] = 0;

                for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
                {
                    if(0 == $this->m_sPlayer[$chair]->card[$i][0] || (0 == ($this->m_sPlayer[$chair]->card[$i][0] - $fanhun_num) && $i == $fanhun_type ))
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

                $this->m_sPlayer[$chair]->card[$fanhun_type][$fanhun_card] += $fanhun_num;

                if($need_fanhun <= $fanhun_num)
                {
                    $hu_qidui = true;
                }

                if($hu_qidui)
                {
                    return self::HU_TYPE_QIDUI;
                }
            }

            //32牌型
            $is_hu_data = false;
            $max_hu = array(0=>-1);

            $jiang_judge_arr = array(0=>2,1=>1,2=>0,3=>2,4=>1,5=>0,6=>2,7=>1,8=>0,9=>2,10=>1,11=>0,12=>2,13=>1,14=>0);
            $no_jiang_judge_arr = array(0=>0,1=>2,2=>1,3=>0,4=>2,5=>1,6=>0,7=>2,8=>1,9=>0,10=>2,11=>1,12=>0);

            for($i=ConstConfig::PAI_TYPE_WAN ; $i<=ConstConfig::PAI_TYPE_FENG ; $i++)
            {
                if(0 == $this->m_sPlayer[$chair]->card[$i][0] || (0 == $this->m_sPlayer[$chair]->card[$i][0]-$fanhun_num && $i == $fanhun_type && $this->m_sPlayer[$chair]->len > $fanhun_num))
                {
                    continue;
                }

                $is_hu_data = false;
                $jiang_type = $i;	//假设将牌是某一门
                $need_fanhun = 0;	//需要混个数
                $replace_fanhun = array(0,0,0,0);

                for($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
                {
                    if(0 == $this->m_sPlayer[$chair]->card[$j][0] || ($j == $fanhun_type && 0 == $this->m_sPlayer[$chair]->card[$j][0]-$fanhun_num && $this->m_sPlayer[$chair]->len > $fanhun_num))
                    {
                        continue;
                    }

                    $pai_num = $this->m_sPlayer[$chair]->card[$j][0];	//一门牌个数
                    $pai_num = ($j == $fanhun_type) ? $pai_num - $fanhun_num : $pai_num;	//混牌的牌型个数得减去混牌个数

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

                    if($need_fanhun > $fanhun_num)
                    {
                        break;
                    }
                }

                if($need_fanhun <= $fanhun_num)
                {
                    $is_check_hu = false;
                    for($j=ConstConfig::PAI_TYPE_WAN ; $j<=ConstConfig::PAI_TYPE_FENG ; $j++)
                    {
                        $is_hu_data = false;
                        $max_type_hu_arr = array(0=>-1);
                        if(0 == $this->m_sPlayer[$chair]->card[$j][0] || ($this->m_sPlayer[$chair]->card[$j][0] == $fanhun_num && $j == $fanhun_type && $this->m_sPlayer[$chair]->len > $fanhun_num))
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

                            $is_hu_data = false;
                            $tmp_type_hu_num = 0;
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

                        if($max_hu[0] > self::$hu_type_arr[self::HU_TYPE_PINGHU][1])
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

            if($max_hu[0] > 0)
            {
                return self::HU_TYPE_PINGHU;
            }
            return $return_type;
        }
    }

    //胡牌类型判断  没有混的情况
    public function judge_hu_type($chair)
    {
        $jiang_arr = array();
        $qidui_arr = array();

        $bType32 = false;
        $bQiDui = false;

        //手牌
        if ($i = ConstConfig::PAI_TYPE_FENG) {
            if (0 < $this->m_sPlayer[$chair]->card[$i][0]) {
                $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));
                if (!isset(ConstConfig::$hu_data_feng[$key])) {
                    return self::HU_TYPE_FENGDING_TYPE_INVALID;
                } else {
                    $hu_list_val = ConstConfig::$hu_data_feng[$key];

                    $qidui_arr[] = $hu_list_val & 64;

                    if (($hu_list_val & 1) == 1) {
                        $jiang_arr[] = $hu_list_val & 32;
                    } else {
                        //非32牌型设置
                        $jiang_arr[] = 32;
                        $jiang_arr[] = 32;
                    }
                }
            }
        }

        for ($i = ConstConfig::PAI_TYPE_WAN; $i <= ConstConfig::PAI_TYPE_TONG; $i++) {
            if (0 == $this->m_sPlayer[$chair]->card[$i][0]) {
                continue;
            }
            if (in_array($this->m_sPlayer[$chair]->card[$i][0], array(1, 7, 13))) {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            }
            $key = intval(implode('', array_slice($this->m_sPlayer[$chair]->card[$i], 1)));

            if (!isset(ConstConfig::$hu_data[$key])) {
                return self::HU_TYPE_FENGDING_TYPE_INVALID;
            } else {
                $hu_list_val = ConstConfig::$hu_data[$key];

                $qidui_arr[] = $hu_list_val & 64;

                if (($hu_list_val & 1) == 1) {
                    $jiang_arr[] = $hu_list_val & 32;
                } else {
                    //非32牌型设置
                    $jiang_arr[] = 32;
                    $jiang_arr[] = 32;
                }
            }
        }

        //倒牌
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++) {
            $qidui_arr[] = 0;
        }

        //记录根到全局数据
        $bType32 = (32 == array_sum($jiang_arr));
        $bQiDui = !array_keys($qidui_arr, 0);

        ///////////////////////基本牌型的处理///////////////////////////////

        //不是32牌型也不是7对
        if (!$bType32 && !$bQiDui) {
            return self::HU_TYPE_FENGDING_TYPE_INVALID;
        } elseif ($bQiDui) {
            //七对
            return self::HU_TYPE_QIDUI;
        } else {
            return self::HU_TYPE_PINGHU;
        }
    }

    //判断基本牌型+附加牌型+庄分
    public function judge_fan($chair)
    {
        $fan_sum = 0;
        $hu_type = $this->m_HuCurt[$chair]->method[0];
        if ($hu_type == self::HU_TYPE_FENGDING_TYPE_INVALID) {
            return 0;
        }

        $tmp_hu_desc = '(';

        //基本牌型
        if (isset(self::$hu_type_arr[$hu_type])) {
            $fan_sum = self::$hu_type_arr[$hu_type][1];
            $tmp_hu_desc .= self::$hu_type_arr[$hu_type][2] . ' ';
        }


        //四个王胡牌时不计附加番
        if($hu_type != self::HU_TYPE_SIHUNZI)
        {
			//附加番
	        for ($i = 1; $i < $this->m_HuCurt[$chair]->count; $i++) 
	        {
	            if (isset(self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]])) 
	            {
	                //素胡翻倍
					$fan_sum *= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][1];
					$tmp_hu_desc .= self::$attached_hu_arr[$this->m_HuCurt[$chair]->method[$i]][2].' ';	
	            }
	        }
        }


        if ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO) 
        {
            if($hu_type != self::HU_TYPE_SIHUNZI)
            {
            	$tmp_hu_desc = '自摸胡' . $tmp_hu_desc;
            }
        } 
        else 
        {
            $tmp_hu_desc = '抢杠胡' . $tmp_hu_desc;
        }
        $tmp_hu_desc .= ') ';
        //if(!$this->m_hu_desc[$chair])
        //{
        $this->m_hu_desc[$chair] = $tmp_hu_desc;
        //}

        return $fan_sum;
    }

    /////////////////命令处理函数/////////////////

    //处理碰  ok
    public function HandleChoosePeng($chair)
    {
        $temp_card = $this->m_sOutedCard->card;
        $card_type = $this->_get_card_type($temp_card);

        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID) {
            echo("uuuuuuuuuuuuuuuuuuuuuu" . __LINE__.__CLASS__);
            return false;
        }

        if (($this->_list_find($chair, $temp_card)) >= 2) {
            $this->_list_delete($chair, $temp_card);
            $this->_list_delete($chair, $temp_card);
        } else {
            echo "error asdff" . __LINE__.__CLASS__;
            return false;
        }

        // 设置倒牌
        $stand_count = $this->m_sStandCard[$chair]->num;
        $this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_KE;
        $this->m_sStandCard[$chair]->first_card[$stand_count] = $temp_card;
        $this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
        $this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
        $this->m_sStandCard[$chair]->num++;

        // 找出第14张牌
        $car_14 = $this->_find_14_card($chair);
        if (!$car_14) {
            echo "error dddf" . __LINE__.__CLASS__;
            return false;
        }

        //置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
        --$this->m_nNumTableCards[$this->m_sOutedCard->chair];
        if ($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card) {
            unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
        }

        $this->_set_record_game(ConstConfig::RECORD_PENG, $chair, $temp_card, $this->m_sOutedCard->chair);

        $this->m_sOutedCard->clear();

        $this->m_sPlayer[$chair]->card_taken_now = $car_14;

        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU) {
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

        $this->handle_flee_play(true);    //更新断线用户
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i),
                Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
            $cmd->send($this->serv);
            unset($cmd);
        }

        return true;
    }

    //处理吃牌  ok
    public function HandleChooseEat($chair, $eat_num)
    {
        $temp_card = $this->m_sOutedCard->card;
        $card_type = $this->_get_card_type($temp_card);

        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID || $card_type == ConstConfig::PAI_TYPE_FENG || $card_type == ConstConfig::PAI_TYPE_DRAGON) {
            echo("uuuuuuuuuuuuuuuuuuuuuu" . __LINE__.__CLASS__);
            return false;
        }

        if ($eat_num == 1) {
            $eat_card_first_tmp = $this->m_sOutedCard->card;
            $del_card_second_tmp = $this->m_sOutedCard->card + 1;
            $del_card_third_tmp = $this->m_sOutedCard->card + 2;
        } elseif ($eat_num == 2) {
            $eat_card_first_tmp = $this->m_sOutedCard->card - 1;
            $del_card_second_tmp = $this->m_sOutedCard->card - 1;
            $del_card_third_tmp = $this->m_sOutedCard->card + 1;
        } elseif ($eat_num == 3) {
            $eat_card_first_tmp = $this->m_sOutedCard->card - 2;
            $del_card_second_tmp = $this->m_sOutedCard->card - 2;
            $del_card_third_tmp = $this->m_sOutedCard->card - 1;
        }

        if ($this->_get_card_type($eat_card_first_tmp) != $card_type) {
            echo("uuuuuuuuuuuuuuuuuuuuuu" . __LINE__.__CLASS__);
            return false;
        } else {
            $this->_list_delete($chair, $del_card_second_tmp);
            $this->_list_delete($chair, $del_card_third_tmp);
        }

        // 设置倒牌
        $stand_count = $this->m_sStandCard[$chair]->num;
        $this->m_sStandCard[$chair]->type[$stand_count] = ConstConfig::DAO_PAI_TYPE_SHUN;
        $this->m_sStandCard[$chair]->first_card[$stand_count] = $eat_card_first_tmp;
        $this->m_sStandCard[$chair]->card[$stand_count] = $temp_card;
        $this->m_sStandCard[$chair]->who_give_me[$stand_count] = $this->m_sOutedCard->chair;
        $this->m_sStandCard[$chair]->num++;

        $this->_set_record_game(ConstConfig::RECORD_CHI, $chair, $temp_card, $this->m_sOutedCard->chair, $eat_num);

        // 找出第14张牌
        $car_14 = $this->_find_14_card($chair);
        if (!$car_14) {
            echo "error dddf" . __LINE__.__CLASS__;
            return false;
        }

        //置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
        --$this->m_nNumTableCards[$this->m_sOutedCard->chair];
        if ($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card) {
            unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
        }

        $this->m_sOutedCard->clear();

        $this->m_sPlayer[$chair]->card_taken_now = $car_14;

        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU) {
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

        $this->handle_flee_play(true);    //更新断线用户
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i),
                Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
            $cmd->send($this->serv);
            unset($cmd);
        }
        return true;
    }

    //处理暗杠  ok
    public function HandleChooseAnGang($chair, $gang_card)
    {
        $temp_card = $gang_card;
        $card_type = $this->_get_card_type($temp_card);

        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID) {
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
        $this->m_sStandCard[$chair]->num++;

        $this->m_bHaveGang = true;  //for 杠上花

        $GangScore = 0;
        $nGangPao = 0;
        //$this->m_wGFXYScore = [0, 0, 0, 0];
        for ($i = 0; $i < $this->m_rule->player_count; ++$i) {
            if ($i == $chair) {
                continue;
            }

            if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU) {
                $nGangScore = self::M_ANGANG_SCORE;

                //$this->m_wGFXYScore[$i] = -$nGangScore;            //扣本次刮风下雨分
                $this->m_wGangScore[$i][$i] -= $nGangScore;        //总刮风下雨分

                //$this->m_wGFXYScore[$chair] += $nGangScore;                //赢本次刮风下雨分
                $this->m_wGangScore[$chair][$chair] += $nGangScore;        //总刮风下雨分

                $this->m_wGangScore[$chair][$i] += $nGangScore;            //赢对应玩家刮风下雨分

                $nGangPao += $nGangScore;
            }
        }

        $this->_set_record_game(ConstConfig::RECORD_ANGANG, $chair, $temp_card, $chair);
        $this->m_sGangPao->init_data(true, $gang_card, $chair, ConstConfig::DAO_PAI_TYPE_ANGANG, $nGangPao);

        $this->m_wTotalScore[$chair]->n_angang += 1;

        // 补发张牌给玩家
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
        $this->m_chairCurrentPlayer = $chair;
        if (!($this->DealCard($chair))) {
            return;
        }

        //暗杠需要记录入命令
        $this->m_chairSendCmd = $this->m_chairCurrentPlayer;
        $this->m_currentCmd = 'c_an_gang';
        $this->m_sOutedCard->clear();
        if ($this->m_nEndReason == ConstConfig::END_REASON_NOCARD) {
            //CCLog("end reason no card");
            return;
        }

        $this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
        //状态变化发消息
        $this->_send_act($this->m_currentCmd, $chair);

        $this->handle_flee_play(true);    //更新断线用户
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i),
                Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
            $cmd->send($this->serv);
            unset($cmd);
        }
    }

    //处理直杠  ok
    public function HandleChooseZhiGang($chair)
    {
        $temp_card = $this->m_sOutedCard->card;
        $card_type = $this->_get_card_type($temp_card);

        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID) {
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
        $this->m_sStandCard[$chair]->num++;
        $stand_count_after = $this->m_sStandCard[$chair]->num;

        //$this->m_sPlayer[$chair]->len -= 3;

        $this->m_bHaveGang = true;  //for 杠上花

        $nGangScore = 0;
        $nGangPao = 0;
        //$this->m_wGFXYScore = [0, 0, 0, 0];

        //点杠者2分
        for ($i=0; $i<$this->m_rule->player_count; $i++)
        {
            if ($i == $chair)
            {
                continue;
            }

            if ($stand_count_after > 0 && $i == $this->m_sStandCard[$chair]->who_give_me[$stand_count_after-1])
            {
                $nGangScore = self::M_ZHIGANG_SCORE;

                //$this->m_wGFXYScore[$i] = -$nGangScore;
                $this->m_wGangScore[$i][$i] -= $nGangScore;

                //$this->m_wGFXYScore[$chair] += $nGangScore;
                $this->m_wGangScore[$chair][$chair] += $nGangScore;

                $this->m_wGangScore[$chair][$i] += $nGangScore;

                $nGangPao += $nGangScore;
            }
        }

        $this->_set_record_game(ConstConfig::RECORD_ZHIGANG, $chair, $temp_card, $this->m_sOutedCard->chair);

        $this->m_sGangPao->init_data(true, $temp_card, $chair, ConstConfig::DAO_PAI_TYPE_MINGGANG, $nGangPao);

        $this->m_wTotalScore[$chair]->n_zhigang_wangang += 1;

        // 补发张牌给玩家
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
        $this->m_chairCurrentPlayer = $chair;
        if (!$this->DealCard($chair)) {
            return;
        }

        //置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
        --$this->m_nNumTableCards[$this->m_sOutedCard->chair];
        if ($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card) {
            unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
        }

        $this->m_sOutedCard->clear();

        if ($this->m_nEndReason == ConstConfig::END_REASON_NOCARD) {
            //CCLOG("end reason no card");
            return;
        }
        $this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;  //有吃碰杠了 ,不能跟庄
        //状态变化发消息
        $this->_send_act($this->m_currentCmd, $chair);
        $this->handle_flee_play(true);    //更新断线用户
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i),
                Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
            $cmd->send($this->serv);
            unset($cmd);
        }
    }

    //处理弯杠  ok
    public function HandleChooseWanGang($chair, $gane_card)
    {
        $temp_card = $gane_card;
        $card_type = $this->_get_card_type($temp_card);

        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID) {
            return false;
        }

        $card_type_taken_now = $this->_get_card_type($this->m_sPlayer[$chair]->card_taken_now);
        if (ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type_taken_now) {
            echo("错误的牌类型" . __LINE__.__CLASS__);
            return false;
        }

        // 改变手持牌，弯杠牌是第14张牌
        if ($this->m_sPlayer[$chair]->card_taken_now == $temp_card) {
            $this->m_sPlayer[$chair]->card_taken_now = 0;
        } else { //弯杠牌在手持牌中
            $this->_list_delete($chair, $temp_card);
            $this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now);
            $this->m_sPlayer[$chair]->card_taken_now = 0;
        }

        // 设置倒牌
        for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++) {
            if ($this->m_sStandCard[$chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_KE
                && $this->m_sStandCard[$chair]->card[$i] == $temp_card
            ) {
                $this->m_sStandCard[$chair]->type[$i] = ConstConfig::DAO_PAI_TYPE_WANGANG;
                break;
            }
        }

        // 初始化杠结构
        $this->m_sQiangGang->init_data(true, $temp_card, $chair); //处理抢杠

        $this->m_sOutedCard->clear();

        //若有人可以胡，抢杠胡
        $this->m_nNumCmdHu = 0;    //重置抢胡牌命令个数
        $this->m_chairHu = array();
        $next_chair = $chair;

        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_CHOOSING;

        $this->handle_flee_play(true);    //更新断线用户
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $next_chair = $this->_anti_clock($next_chair);

            if ($this->m_sPlayer[$next_chair]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU) {
                continue;
            }

            $this->m_bChooseBuf[$next_chair] = 1;
            $this->m_sPlayer[$next_chair]->state = ConstConfig::PLAYER_STATUS_CHOOSING;

            if ($next_chair == $chair) {
                $this->m_bChooseBuf[$next_chair] = 0;
                $this->m_sPlayer[$next_chair]->state = ConstConfig::PLAYER_STATUS_WAITING;
            }
        }
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i),
                Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
            $cmd->send($this->serv);
            unset($cmd);
        }

        $this->m_chairSendCmd = 255;                            // 当前发命令的玩家
        $this->m_currentCmd = 0;                            // 当前的命令
    }

    //处理自摸  ok
    public function HandleHuZiMo($chair)
    {
        $temp_card = $this->m_sPlayer[$chair]->card_taken_now;
        $card_type = $this->_get_card_type($temp_card);

        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID) {
            echo("hu_zi_mo_error" . __LINE__.__CLASS__);
            return false;
        }

        $this->_list_insert($chair, $temp_card);
        $this->m_HuCurt[$chair]->state = ConstConfig::WIN_STATUS_ZI_MO;
        $this->m_HuCurt[$chair]->card = $temp_card;

        $bHu = $this->judge_hu($chair);

        $this->_list_delete($chair, $temp_card);

        if (!$bHu) { //诈胡
            echo("有人诈胡" . __LINE__.__CLASS__);
            $this->HandleZhaHu($chair);
            $this->m_HuCurt[$chair]->clear();
        } 
        else 
        {
            $tmp_lost_chair = 255;
            if ($this->ScoreOneHuCal($chair, $tmp_lost_chair)) 
            {
                //总计自摸
                if ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO) {
                    $this->m_wTotalScore[$chair]->n_zimo += 1;
                    $this->m_currentCmd = 'c_zimo_hu';
                }

                $this->m_chairSendCmd = $this->m_chairCurrentPlayer;

                if ($this->m_game_type == self::GAME_TYPE) {
                    $this->m_bChairHu[$chair] = true;
                    $this->m_bChairHu_order[] = $chair;
                    $this->m_nCountHu++;
                    $this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;

                    //$this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now); //整理完毕

                    //去除胡牌者 card_taken_now  这个牌就只有在 m_HuCurt 有
                    $this->m_sPlayer[$chair]->card_taken_now = 0;

                    if (255 == $this->m_nChairBankerNext) 
                    {    //下一局庄家
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
            else 
            {    //番数不够，判诈胡，一般进不来
                echo("有人诈胡" . __LINE__.__CLASS__);
                $this->HandleZhaHu($chair);
                $this->m_HuCurt[$chair]->clear();

                $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
                $this->m_chairCurrentPlayer = $chair;
                $this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_THINK_OUTCARD;

                //发消息
                $this->handle_flee_play(true);    //更新断线用户
                for ($i = 0; $i < $this->m_rule->player_count; $i++) {
                    $cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i),
                        Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
                    $cmd->send($this->serv);
                    unset($cmd);
                }
            }
        }
    }

    //处理出牌  ok
    public function HandleOutCard($chair, $is_14 = false, $out_card = 0, $is_ting = 1)
    {
        //一旦有人出牌，表示上一轮竞争已经结束, 可以清CMD
        $this->m_chairSendCmd = 255;                            // 当前发命令的玩家
        $this->m_currentCmd = 0;                            // 当前的命令
        $this->m_eat_num = 0;

        // 更新桌面牌
        if ($this->m_sOutedCard->card) {
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
        $pos = $is_14;
        $temp_out_card = $out_card;

        //若打出的是第14张牌
        if ($pos) 
        {
            $this->m_sOutedCard->card = $this->m_sPlayer[$chair]->card_taken_now;
            $this->m_sPlayer[$chair]->card_taken_now = 0;
        } 
        elseif ($temp_out_card) //若打出的是第1-13张牌, 要整理牌列表
        {
            $this->m_sOutedCard->card = $temp_out_card;
            if (!$this->_list_delete($chair, $this->m_sOutedCard->card)) {
                echo "出牌错误" . __LINE__.__CLASS__;
                return false;
            }

            $card_type = $this->_get_card_type($this->m_sPlayer[$chair]->card_taken_now);
            if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID) {
                echo "出牌错误" . __LINE__.__CLASS__;
                return false;
            }
            $this->_list_insert($chair, $this->m_sPlayer[$chair]->card_taken_now); //整理完毕
            $this->m_sPlayer[$chair]->card_taken_now = 0;   
        }

        $this->m_is_ting_arr[$chair] = $is_ting;
        $this->m_sPlayer[$chair]->state = ConstConfig::PLAYER_STATUS_WAITING;

        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_CHOOSING;
        $chair_next = $chair;

        $this->m_nNumCmdHu = 0;    //重置抢胡牌命令个数
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

        $this->handle_flee_play(true);    //更新断线用户

        $tmp_arr = [];
        for ($i = 0; $i < $this->m_rule->player_count - 1; $i++) 
        {
            $chair_next = $this->_anti_clock($chair_next);
            if ($this->m_sPlayer[$chair_next]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU) 
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
//////////////////////////////////////////////
            if($this->_find_peng($chair_next) 
             || $this->_find_zhi_gang($chair_next) 
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

    //竞争选择处理
    public function HandleChooseResult($chair, $nCmdID, $eat_num = null)
    {
        $this->handle_flee_play(true);

        //处理竞争
        $order_cmd = array('c_cancle_choice' => 0, 'c_eat' => 1, 'c_peng' => 2, 'c_zhigang' => 3, 'c_hu' => 4);
        if (empty($this->m_currentCmd) || ($order_cmd[$nCmdID] > $order_cmd[$this->m_currentCmd] && $order_cmd[$nCmdID] >= $order_cmd['c_cancle_choice'])) 
        {    //吃, 碰, 杠竞争
            $this->m_chairSendCmd = $chair;
            $this->m_currentCmd = $nCmdID;
            $this->m_eat_num = $eat_num;
        }
        if ($nCmdID == 'c_hu') 
        {
            $this->m_chairHu[$this->m_nNumCmdHu++] = $chair;
        }

        //等待大家都选了竞争 吃碰杠胡 再去执行
        $sum = 0;

        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            if ($i == $this->m_chairCurrentPlayer || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU) {
                continue;
            }
            $sum += $this->m_bChooseBuf[$i];
        }
        if ($sum > 0) {
            return false;
        }

        //漏胡重置
        if ($this->m_currentCmd != 'c_cancle_choice') {
            $card_chair = 255;
            if ($this->m_sQiangGang->mark) {
                $card_chair = $this->m_sQiangGang->chair;
            } else {
                if ($this->m_sOutedCard->card) {
                    $card_chair = $this->m_sOutedCard->chair;
                }
            }
            if ($card_chair != 255) {
                $tmp_chair = $this->m_chairSendCmd;
                for ($i = 0; $i < $this->m_rule->player_count; $i++) {
                    $this->m_nHuGiveUp[$tmp_chair] = 0;
                    $this->m_nPengGiveUp[$tmp_chair] = 0;
                    //本人与动牌的玩家之间的上家
                    $tmp_chair = $this->_anti_clock($tmp_chair, -1);
                    if ($tmp_chair == $card_chair || $tmp_chair == $this->m_chairSendCmd) {
                        break;
                    }
                }
            }
        }

        $temp_card = 0;
        $card_type = ConstConfig::PAI_TYPE_PAI_TYPE_INVALID;

        //抉择后全部可见
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $this->m_sPlayer[$i]->seen_out_card = 1;
        }

        //抢杠胡
        if ($this->m_sQiangGang->mark) 
        {
            $temp_card = $this->m_sQiangGang->card;
            $card_type = $this->_get_card_type($temp_card);
            if (ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type) 
            {
                echo("错误的牌类型，发生在-> 抢杠" . __LINE__.__CLASS__);
                return false;
            }

            $bHaveHu = false;
            $record_hu_chair = array();

            //截胡和一炮多响
            $this->_do_c_hu($temp_card, $this->m_sQiangGang->chair, $bHaveHu, $record_hu_chair);

            $this->m_sGangPao->clear();

            if ($bHaveHu) 
            { 	
            	//抢杠胡,处理原来的杠
                if($record_hu_chair && is_array($record_hu_chair))
                {
                    $this->_set_record_game(ConstConfig::RECORD_HU_QIANGGANG, $record_hu_chair, $this->m_sQiangGang->card, $this->m_sQiangGang->chair);
                }
                //$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
                $this->m_sOutedCard->chair = $this->m_sQiangGang->chair;
                $this->m_sOutedCard->card = $this->m_sQiangGang->card;
                $this->m_currentCmd = 'c_hu';

                // 设置倒牌, 抢杠后杠牌变成刻子
                for ($i = 0; $i < $this->m_sStandCard[$this->m_sOutedCard->chair]->num; $i++) {
                    if ($this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] == ConstConfig::DAO_PAI_TYPE_WANGANG
                        && $this->m_sStandCard[$this->m_sOutedCard->chair]->card[$i] == $this->m_sOutedCard->card
                    ) {
                        $this->m_sStandCard[$this->m_sOutedCard->chair]->type[$i] = ConstConfig::DAO_PAI_TYPE_KE;
                        break;
                    }
                }

                if ($this->m_game_type == self::GAME_TYPE) {
                    $this->m_nEndReason = ConstConfig::END_REASON_HU;
                    $this->HandleSetOver();
                    return;
                }
            } else { 
            	// 给杠的玩家补张
                $GangScore = 0;
                $nGangPao = 0;
                $m_wGFXYScore = [0, 0, 0, 0];

                //弯杠 赢3家
                for ($i = 0; $i < $this->m_rule->player_count; ++$i) 
                {
                    if ($i == $this->m_sQiangGang->chair || $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU) 
                    {
                        continue;
                    }
                    $nGangScore = self::M_WANGANG_SCORE;

                    //$this->m_wGFXYScore[$i] = -$nGangScore;
                    $this->m_wGangScore[$i][$i] -= $nGangScore;

                    //$this->m_wGFXYScore[$this->m_sQiangGang->chair] += $nGangScore;
                    $this->m_wGangScore[$this->m_sQiangGang->chair][$this->m_sQiangGang->chair] += $nGangScore;
                    $this->m_wGangScore[$this->m_sQiangGang->chair][$i] += $nGangScore;

                    $nGangPao += $nGangScore;
                }

                $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
                $this->m_chairCurrentPlayer = $this->m_sQiangGang->chair;

                $this->m_bHaveGang = true;                    //for 杠上花
                $this->m_sGangPao->init_data(true, $this->m_sQiangGang->card, $this->m_sQiangGang->chair,
                    ConstConfig::DAO_PAI_TYPE_WANGANG, $nGangPao);

                $this->_set_record_game(ConstConfig::RECORD_ZHUANGANG, $this->m_sQiangGang->chair,
                    $this->m_sQiangGang->card, $this->m_sQiangGang->chair);

                $this->m_wTotalScore[$this->m_sQiangGang->chair]->n_zhigang_wangang += 1;

                //摸杠需要记录入命令
                $this->m_chairSendCmd = $this->m_chairCurrentPlayer;
                $this->m_currentCmd = 'c_wan_gang';

                if (!$this->DealCard($this->m_chairCurrentPlayer)) {
                    return;
                }

                if ($this->m_nEndReason == ConstConfig::END_REASON_NOCARD) {
                    //CCLOG("end reason no card");
                    return;
                }

                //状态变化发消息
                $this->_send_act($this->m_currentCmd, $this->m_sQiangGang->chair, $this->m_sQiangGang->card);
                $this->handle_flee_play(true);    //更新断线用户
                for ($i = 0; $i < $this->m_rule->player_count; $i++) {
                    $cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i),
                        Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
                    $cmd->send($this->serv);
                    unset($cmd);
                }

                $this->m_sQiangGang->clear();

                return;
            }
        } 
        else 
        {
            // 普通竞争选择
            $bHaveHu = false;
            $record_hu_chair = array();

            $temp_card = $this->m_sOutedCard->card;
            $card_type = $this->_get_card_type($temp_card);
            if (ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type) {
                echo("错误的牌类型，发生在" . __LINE__.__CLASS__);
                return false;
            }

            //截胡和一炮多响
            $this->_do_c_hu($temp_card, $this->m_sOutedCard->chair, $bHaveHu, $record_hu_chair);

            $this->m_sGangPao->clear();

            if ($bHaveHu) {
                if ($record_hu_chair && is_array($record_hu_chair)) {
                    $this->_set_record_game(ConstConfig::RECORD_HU, $record_hu_chair, $this->m_sOutedCard->card,
                        $this->m_sOutedCard->chair);
                }
                //$this->m_chairSendCmd = $this->m_chairCurrentPlayer;
                $this->m_currentCmd = 'c_hu';

                //置出牌序列最后一张，是有可能被取消的（吃 碰 直杠 点炮）
                --$this->m_nNumTableCards[$this->m_sOutedCard->chair];
                if ($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]] == $this->m_sOutedCard->card)
                {
                    unset($this->m_nTableCards[$this->m_sOutedCard->chair][$this->m_nNumTableCards[$this->m_sOutedCard->chair]]);
                }

                if ($this->m_game_type == self::GAME_TYPE) {
                    $this->m_nEndReason = ConstConfig::END_REASON_HU;
                    $this->HandleSetOver();
                    return;
                }
            }

            //没有胡， 继续处理其他命令
            switch ($this->m_currentCmd) {
                case 'c_peng':
                    $this->HandleChoosePeng($this->m_chairSendCmd);
                    break;
                case 'c_zhigang':
                    $this->HandleChooseZhiGang($this->m_chairSendCmd);
                    break;
                case 'c_eat':
                    $this->HandleChooseEat($this->m_chairSendCmd, $this->m_eat_num);
                    break;
                case 'c_cancle_choice':    // 发牌给下家

                    ////////////////跟庄处理/////////////////////////////
                    $this->_genzhuang_do();

                default:  //预防有人诈胡后,游戏得以继续
                    $this->m_sGangPao->clear();

                    $next_chair = $this->m_chairCurrentPlayer;
                    $next_chair = $this->_anti_clock($next_chair);

                    if ($next_chair == $this->m_chairCurrentPlayer) {
                        echo("find unHu player error, chair" . __LINE__.__CLASS__);
                        return;
                    }

                    $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
                    $this->m_chairCurrentPlayer = $next_chair;

                    if (!$this->DealCard($next_chair)) {
                        return;
                    }
                    if ($this->m_nEndReason == ConstConfig::END_REASON_NOCARD) {
                        echo("end reason no card");
                        return;
                    }

                    //状态变化发消息
                    $this->_send_act($this->m_currentCmd, $chair);
                    $this->handle_flee_play(true);    //更新断线用户
                    for ($i = 0; $i < $this->m_rule->player_count; $i++) {
                        $cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i),
                            Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
                        $cmd->send($this->serv);
                        unset($cmd);
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
        $card_type = $this->_get_card_type($temp_card);
        if (ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type) {
            echo("错误的牌类型，发生在-> 抢杠" . __LINE__.__CLASS__);
            return false;
        }

        //截胡和一炮多响
        if ($this->m_nNumCmdHu) {
            $tmp_hu_arr = array();
            if (empty($this->m_rule->is_yipao_duoxiang)) {
                //计算距离最近的玩家
                $distance = $this->m_rule->player_count;
                $hu_chair = ConstConfig::PLAYER_STATUS_PLAYER_STATUS_INVALIDE;
                for ($i = 0; $i < $this->m_nNumCmdHu; $i++) {
                    $tmp_distance = $this->_chair_to($this->m_chairCurrentPlayer, $this->m_chairHu[$i]);
                    if ($tmp_distance < $distance) {
                        $distance = $tmp_distance;
                        $hu_chair = $this->m_chairHu[$i];
                    } else {
                        unset($this->m_chairHu[$i]);
                    }
                }
                if ($hu_chair != ConstConfig::PLAYER_STATUS_PLAYER_STATUS_INVALIDE) {
                    $tmp_hu_arr[] = $hu_chair;
                }
            } else {
                $tmp_hu_arr = $this->m_chairHu;
            }

            foreach ($tmp_hu_arr as $hu_chair) {

                $this->_list_insert($hu_chair, $temp_card);
                $this->m_HuCurt[$hu_chair]->state = ConstConfig::WIN_STATUS_CHI_PAO;   //抢杠算作吃炮
                $this->m_nChairDianPao = $dian_pao_chair;
                $this->m_HuCurt[$hu_chair]->card = $temp_card;
                $bHu = $this->judge_hu($hu_chair);
                $this->_list_delete($hu_chair, $temp_card);
                if (!$bHu) {
                    echo("有人诈胡 at" . __LINE__.__CLASS__);
                    $this->HandleZhaHu($hu_chair);
                    $this->m_HuCurt[$hu_chair]->clear();
                } else {
                    if ($this->ScoreOneHuCal($hu_chair, $dian_pao_chair)) {
                        $bHaveHu = true;
                        $record_hu_chair[] = $hu_chair;
                        if ($this->m_HuCurt[$hu_chair]->state == ConstConfig::WIN_STATUS_CHI_PAO) {
                            $this->m_wTotalScore[$hu_chair]->n_jiepao += 1;
                            $this->m_wTotalScore[$this->m_nChairDianPao]->n_dianpao += 1;
                        }
                        //if ($this->m_game_type == self::GAME_TYPE)
                        {
                            $this->m_bChairHu[$hu_chair] = true;
                            $this->m_bChairHu_order[] = $hu_chair;
                            $this->m_nCountHu++;
                            $this->m_sPlayer[$hu_chair]->state = ConstConfig::PLAYER_STATUS_BLOOD_HU;
                        }
                        $this->_send_act($this->m_currentCmd, $hu_chair);
                        if (255 == $this->m_nChairBankerNext || $hu_chair == $this->m_nChairBanker) 
                        {    //下一局庄家
                            $this->m_nChairBankerNext = $hu_chair;
                        }
                    } else {
                        $this->HandleZhaHu($hu_chair);
                        $this->m_HuCurt[$hu_chair]->clear();
                    }
                }
            }
        }
    }

    //诈胡处理
    public function HandleZhaHu($chair)
    {
        //以后另做处理，客户端诈胡等于作弊
        $this->m_nNumCheat[$chair]++;
        //$this->m_bChooseBuf[$chair] = 0; //clear the hu signal
    }

    //处理下炮子
    public function handle_pao_zi($chair, $pao_zi_num)
    {
        $this->m_own_paozi[$chair]->recv = true;
        $this->m_own_paozi[$chair]->num = $pao_zi_num;

        $tmp_paozi_arr = [0, 0, 0, 0];
        for ($i = 0; $i<$this->m_rule->player_count; ++$i)
        {
            $tmp_paozi_arr[$i] = $this->m_own_paozi[$i]->num;
            if (!$this->m_own_paozi[$i]->recv)
            {
                break;
            }
        }

        //开始牌局
        if ($this->m_rule->player_count == $i)
        {
            $this->_set_record_game(ConstConfig::RECORD_PAOZI, $tmp_paozi_arr[0], $tmp_paozi_arr[1], $tmp_paozi_arr[2], $tmp_paozi_arr[3]);

            $this->DealAllCardEx();
            $this->game_to_playing();
        }
    }

    public function game_to_playing()
    {
        //状态设定
        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD;
        $this->m_sPlayer[$this->m_nChairBanker]->state = ConstConfig::PLAYER_STATUS_THINK_OUTCARD;

        $this->m_chairCurrentPlayer = $this->m_nChairBanker;

        $this->m_sPlayer[$this->m_nChairBanker]->state = ConstConfig::PLAYER_STATUS_CHOOSING;
        $this->m_bChooseBuf[$this->m_nChairBanker] = 1;

        $this->m_hun_card = 55;

        //状态变化发消息
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $cmd = new Game_cmd($this->m_room_id, 's_sys_phase_change', $this->OnGetChairScene($i, true),
                Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
            $cmd->send($this->serv);
            unset($cmd);
        }
        $this->handle_flee_play(true);    //更新断线用户
    }

    //取得所有玩家数据
    public function OnGetChairScene($chair, $is_more = false)
    {
        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_INVALID) {
            echo("sysPhase invalid," . __LINE__.__CLASS__ . "\n");
            return false;
        }

        $data = array();
        if ($is_more) {
            $data['base_player_count'] = $this->m_rule->player_count;
            $data['m_room_players'] = $this->m_room_players;
            $data['m_rule'] = clone $this->m_rule;
            $data['m_dice'] = $this->m_dice;
            $data['m_Score'] = $this->m_Score;        //分数

            $data['m_wTotalScore'] = $this->m_wTotalScore;
            $data['m_ready'] = $this->m_ready;
            $data['is_cancle'] = $this->m_cancle;
            $data['m_cancle'] = $this->m_cancle;
            $data['m_cancle_first'] = $this->m_cancle_first;
            $data['m_hun_card'] = $this->m_hun_card;
        }

        $data['m_nChairBanker'] = $this->m_nChairBanker;  //庄家
        $data['m_nSetCount'] = $this->m_nSetCount;
        $data['m_sysPhase'] = $this->m_sysPhase;    // 当前的阶段
        $data['m_nCountAllot'] = $this->m_nCountAllot;    // 发到第几张
        $data['m_nAllCardNum'] = $this->m_nAllCardNum;    //牌总数
        $data['m_bHaveGang'] = $this->m_bHaveGang;
        $data['m_sQiangGang'] = $this->m_sQiangGang;
        $data['m_sGangPao'] = $this->m_sGangPao;
        $data['m_bTianRenHu'] = $this->m_bTianRenHu;  //天胡
        $data['m_nDiHu'] = $this->m_nDiHu;            //地胡
        $data['m_bChairHu'] = $this->m_bChairHu;
        $data['m_bChairHu_order'] = $this->m_bChairHu_order;
        $data['m_HuCurt'] = $this->m_HuCurt;        //胡牌详情
        $data['m_bLastGameOver'] = $this->m_bLastGameOver;		//胡牌最终结束
        if(!empty($this->m_cancle_time))
        {
            $data['m_cancle_time'] = $this->m_cancle_time + Config::CANCLE_GAME_CLOCKER_NUM - time(); 
        }

        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $data['m_sPlayer_len'][$i] = $this->m_sPlayer[$i]->len;
            $data['m_sPlayer_state'][$i] = $this->m_sPlayer[$i]->state;
            $data['m_sPlayer_card_taken_now'][$i] = intval(0 != $this->m_sPlayer[$i]->card_taken_now);
            if ($is_more && !empty($data['m_room_players'][$i])) {
                $data['m_room_players'][$i]['fd'] = 0;
            }
        }

        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_THINKING_OUT_CARD) {
            $data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;                                // 当前出牌者
            $data['m_nNumTableCards'] = $this->m_nNumTableCards;        // 玩家桌面牌数量
            $data['m_nTableCards'] = $this->m_nTableCards;    // 玩家桌面牌
            $data['m_sStandCard'] = $this->m_sStandCard;        // 玩家倒牌
            $data['m_sOutedCard'] = $this->m_sOutedCard;        //刚出的牌

            for ($i = 0; $i < $this->m_rule->player_count; $i++) {                                         // 玩家手持牌长度
                if ($i == $chair) {
                    $data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
                    $data['m_bChooseBuf'] = $this->m_bChooseBuf[$i];             //命令缓冲
                    $data['m_nHuGiveUp'] = $this->m_nHuGiveUp[$i];
                    $data['m_nPengGiveUp'] = $this->m_nPengGiveUp[$i];
                    $data['m_only_out_card'] = $this->m_only_out_card[$i];
                } else {
                    $data['m_sPlayer'][$i] = (object)null;
                }

                // if(!empty($this->m_sPlayer[$i]->minglou))
                // {
                // 	$data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
                // }
            }

            //$data['m_chairSendCmd'] = $this->m_chairSendCmd;                  //发命令的玩家
            //$data['m_currentCmd'] = $this->m_currentCmd;

            return $data;
        }

        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_CHOOSING) {
            $data['m_chairCurrentPlayer'] = $this->m_chairCurrentPlayer;                                // 当前出牌者
            $data['m_nNumTableCards'] = $this->m_nNumTableCards;        // 玩家桌面牌数量
            $data['m_nTableCards'] = $this->m_nTableCards;    // 玩家桌面牌
            $data['m_sStandCard'] = $this->m_sStandCard;        // 玩家倒牌
            $data['m_sOutedCard'] = $this->m_sOutedCard;        //刚出的牌

            for ($i = 0; $i < $this->m_rule->player_count; $i++) {                                         // 玩家手持牌长度
                if ($i == $chair) {
                    $data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
                    $data['m_bChooseBuf'] = $this->m_bChooseBuf[$i];             //命令缓冲
                    $data['m_nHuGiveUp'] = $this->m_nHuGiveUp[$i];
                    $data['m_nPengGiveUp'] = $this->m_nPengGiveUp[$i];
                    $data['m_only_out_card'] = $this->m_only_out_card[$i];
                } else {
                    $data['m_sPlayer'][$i] = (object)null;
                }

                // if(!empty($this->m_sPlayer[$i]->minglou))
                // {
                // 	$data['m_sPlayer'][$i] = $this->m_sPlayer[$i];
                // }
            }

            return $data;
        }
        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_SET_OVER) {
            $data['m_nEndReason'] = $this->m_nEndReason;                                        //结束原因
            //$data['m_nCountFlee'] = $this->m_nCountFlee;

            $data['m_nNumTableCards'] = $this->m_nNumTableCards;        // 玩家桌面牌数量
            $data['m_nTableCards'] = $this->m_nTableCards;    // 玩家桌面牌
            $data['m_sStandCard'] = $this->m_sStandCard;        // 玩家倒牌
            $data['m_sOutedCard'] = $this->m_sOutedCard;        //刚出的牌

            $data['m_ZhaMaCard'] = $this->m_ZhaMaCard;    //扎码牌
            $data['m_wZhamaScore'] = $this->m_wZhamaScore; // 扎码分数
            $data['m_sPlayer'] = $this->m_sPlayer;                // 玩家数据
            if (isset($data['m_sPlayer'][''])) {
                unset($data['m_sPlayer']['']);
            }
            foreach ($data['m_sPlayer'] as $tmp_key => $tmp_val) {
                if ($tmp_key >= $this->m_rule->player_count) {
                    $data['m_sPlayer'][$tmp_key] = (object)null;
                }
            }

            $data['m_hu_desc'] = $this->m_hu_desc;
            $data['m_end_time'] = $this->m_end_time;

            return $data;
        }
        return true;
    }

    //发牌
    public function DealCard($chair)
    {
        if ($this->m_game_type == self::GAME_TYPE && $this->m_bChairHu[$chair]) {    //未胡玩家发牌
            if ($this->m_nCountHu >= 1) {
                return false;
            }
            $this->m_chairCurrentPlayer = $this->_anti_clock($chair);
            return $this->DealCard($this->m_chairCurrentPlayer);
        }

        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            if ($this->m_sPlayer[$i]->state != ConstConfig::PLAYER_STATUS_BLOOD_HU) {
                $this->m_sPlayer[$i]->seen_out_card = 1;        //如无人吃碰杠，则全部可见
                $this->m_sPlayer[$i]->state = ConstConfig::PLAYER_STATUS_WAITING;
                $this->m_sPlayer[$i]->card_taken_now = 0;
            }
        }

        if (empty($this->m_nCardBuf[$this->m_nCountAllot])) {
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

        $this->m_nHuGiveUp[$chair] = 0;    //重置过手胡
        $this->m_nPengGiveUp[$chair] = 0;	//重置同圈过碰

        $this->_set_record_game(ConstConfig::RECORD_DRAW, $chair, $the_card, $chair);

        return true;
    }

    //游戏结束
    public function HandleSetOver()
    {
        if ($this->m_sysPhase == ConstConfig::SYSTEMPHASE_SET_OVER) {
            return false;
        }

        $this->m_sysPhase = ConstConfig::SYSTEMPHASE_SET_OVER;
        //m_sOutedCard->clear();

        if ($this->m_nEndReason == ConstConfig::END_REASON_HU) 
        {
            if(!$this->m_bSiwang)
            {
				$this->_zha_ma();
            }
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
        if ($this->m_nEndReason == ConstConfig::END_REASON_NOCARD && 255 == $this->m_nChairBankerNext) 
        {
            $this->m_nChairBankerNext = $this->_anti_clock($this->m_nChairBanker, 0);
        }

        //准备状态
        $this->m_ready = array(0, 0, 0, 0);

        //本局结束时间
        $this->m_end_time = date('Y-m-d H:i:s', time());

        //写记录
        $this->WriteScore();

        //最后一局结束时候修改房间状态
        if(empty($this->m_rule) || $this->m_rule->set_num <= $this->m_nSetCount)
        {
            $this->m_room_state = ConstConfig::ROOM_STATE_OVER;
            $this->m_bLastGameOver = 1;
        }

        //状态变化发消息
        $this->handle_flee_play(true);    //更新断线用户
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $cmd = new Game_cmd($this->m_room_id, 's_game_over', $this->OnGetChairScene($i, true),
                Game_cmd::SCO_SINGLE_PLAYER, $this->m_room_players[$i]['uid']);
            $cmd->send($this->serv);
            unset($cmd);
        }

        $this->_set_game_and_checkout();
    }

    /////////////////////////得分处理///////////////////////////

    //每局个人  +=赢的分  +=输的分  +=庄家 的分  一共4局
    public function ScoreOneHuCal($chair, &$lost_chair)
    {
        $fan_sum = $this->judge_fan($chair);  //这个就是  一共多少分
        if ($fan_sum < $this->m_rule->min_fan) {
            $this->m_HuCurt[$chair]->clear();
            return false;
        }

        $PerWinScore = $fan_sum;

        $this->m_wHuScore = [0, 0, 0, 0];

        if ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_ZI_MO) 
        {
            for ($i = 0; $i < $this->m_rule->player_count; $i++) {
                if ($i == $chair) {
                    continue;    //单用户测试需要关掉
                }

                if ($this->m_game_type == self::GAME_TYPE && $this->m_sPlayer[$i]->state == ConstConfig::PLAYER_STATUS_BLOOD_HU) {
                    continue;
                }

                $banker_fan = 1;
                //庄家分
                // if ($this->m_rule->is_zhuang_fan && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $i)) {
                //     $banker_fan = 2;
                // }

                $PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
                $wWinScore = 0;
                $wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;  //赢的分 加  庄家的分

                $this->m_wHuScore[$i] -= $wWinScore;
                $this->m_wHuScore[$chair] += $wWinScore;

                $this->m_wSetLoseScore[$i] -= $wWinScore;
                $this->m_wSetScore[$chair] += $wWinScore;

                $this->m_HuCurt[$chair]->gain_chair[0]++;
                $this->m_HuCurt[$chair]->gain_chair[$this->m_HuCurt[$chair]->gain_chair[0]] = $i;
            }
            return true;
        } 
        elseif ($this->m_HuCurt[$chair]->state == ConstConfig::WIN_STATUS_CHI_PAO) 
        {
            $banker_fan = 1;
            //庄家翻倍
            // if ($this->m_rule->is_zhuang_fan && ($this->m_nChairBanker == $chair || $this->m_nChairBanker == $lost_chair)) {
            //     $banker_fan = 2;
            // }

            $PerWinScore = ($PerWinScore == 0)? 1 : $PerWinScore;
            $wWinScore = 0;
            $wWinScore += ConstConfig::SCORE_BASE * $PerWinScore * $banker_fan;  //赢的分 加  庄家的分
            $wWinScore *= $this->m_rule->player_count - 1;   //抢杠胡大包

            $this->m_wHuScore[$lost_chair] -= $wWinScore;
            $this->m_wHuScore[$chair] += $wWinScore;

            $this->m_wSetLoseScore[$lost_chair] -= $wWinScore;
            $this->m_wSetScore[$chair] += $wWinScore;

            $this->m_HuCurt[$chair]->gain_chair[0] = 1;
            $this->m_HuCurt[$chair]->gain_chair[1] = $lost_chair;

            //抢杠胡包杠分
            for($i=0; $i<$this->m_rule->player_count; $i++)
			{
				$this->m_wGangScore[$i] = array(0,0,0,0);
			}

            for($i=0; $i<$this->m_rule->player_count; $i++)
			{
				if($i == $lost_chair)
				{
					continue;
				}
				for($j = 0; $j < $this->m_sStandCard[$i]->num; $j ++)
				{
					if ($this->m_sStandCard[$i]->type[$j] == ConstConfig::DAO_PAI_TYPE_MINGGANG)
					{
						$nGangScore = self::M_ZHIGANG_SCORE;
						$this->m_wGangScore[$i][$i] += $nGangScore;
						$this->m_wGangScore[$lost_chair][$lost_chair] -= $nGangScore;
						$this->m_wGangScore[$i][$lost_chair] += $nGangScore;
					}
					if ($this->m_sStandCard[$i]->type[$j] == ConstConfig::DAO_PAI_TYPE_ANGANG)
					{
						$nGangScore = self::M_ANGANG_SCORE * ($this->m_rule->player_count - 1);
						$this->m_wGangScore[$i][$i] += $nGangScore;
						$this->m_wGangScore[$lost_chair][$lost_chair] -= $nGangScore;
						$this->m_wGangScore[$i][$lost_chair] += $nGangScore;
					}
					if ($this->m_sStandCard[$i]->type[$j] == ConstConfig::DAO_PAI_TYPE_WANGANG)
					{
						$nGangScore = self::M_WANGANG_SCORE * ($this->m_rule->player_count - 1);
						$this->m_wGangScore[$i][$i] += $nGangScore;
						$this->m_wGangScore[$lost_chair][$lost_chair] -= $nGangScore;
						$this->m_wGangScore[$i][$lost_chair] += $nGangScore;
					}
				}
			}

			//抢杠胡包扎码分，改到扎码里
			/*for($i=0; $i<$this->m_rule->player_count; $i++)
			{
				if($this->m_wZhamaScore[$i] < 0)
				{
					$this->m_wZhamaScore[$i] = 0;
				}
			}
			$this->m_wZhamaScore[$lost_chair] = - $this->m_wZhamaScore[$chair];*/

			//抢杠胡包跟庄分
			if($lost_chair != $this->m_nChairBanker)
			{
				$this->m_wFollowScore[$lost_chair] += $this->m_wFollowScore[$this->m_nChairBanker];
				$this->m_wFollowScore[$this->m_nChairBanker] = 0;
			}
			
            return true;
        }

        echo("此人没有胡" . __LINE__.__CLASS__);
        return false;
    }

    //每局牌局最终  分  赢的分-输的分
    public function CalcHuScore()
    {
        $cash = 0;
        //	Score_Struct score[PLAYER_COUNT];
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $this->m_Score[$i]->clear();
        }
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $this->m_Score[$i]->score = $this->m_wSetScore[$i] + $this->m_wSetLoseScore[$i] + $this->m_wGangScore[$i][$i] + $this->m_wZhamaScore[$i] + $this->m_wFollowScore[$i];
            $this->m_Score[$i]->set_count = $this->m_nSetCount;
            if ($this->m_Score[$i]->score > 0) {
                $this->m_Score[$i]->win_count = 1;
            } else {
                $this->m_Score[$i]->lose_count = 1;
            }

            $this->m_room_players[$i]['score'] = $this->m_Score[$i]->score;
        }
    }

    //荒庄结算
    public function CalcNoCardScore()
    {
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $this->m_Score[$i]->clear();
        }

        if ($this->m_game_type != self::GAME_TYPE) {
            echo("error m_game_type" . __LINE__.__CLASS__);
            return false;
        }

        for ($i = 0; $i < $this->m_rule->player_count; $i++) 
        {
            //$this->m_wGangScore[$i][$i] = 0;
            $this->m_wZhamaScore[$i] = 0;
            $this->m_Score[$i]->score = $this->m_wGangScore[$i][$i] + $this->m_wFollowScore[$i];
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

    //总分处理(显示分数区)
    public function WriteScore()
    {
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $this->m_wTotalScore[$i]->n_score += $this->m_Score[$i]->score;

            if ($this->m_wSetScore[$i]) {
                $this->m_hu_desc[$i] = $this->m_hu_desc[$i] . '+' . ($this->m_wSetScore[$i]) . ' ';
            } else {
                $this->m_hu_desc[$i] = '';
            }

            if ($this->m_wSetLoseScore[$i]) {
                $this->m_hu_desc[$i] .= '被胡' . $this->m_wSetLoseScore[$i] . ' ';
            }

            if ($this->m_wGangScore[$i][$i] > 0) {
                $this->m_hu_desc[$i] .= '杠分+' . $this->m_wGangScore[$i][$i] . ' ';
            } else {
                $this->m_hu_desc[$i] .= '杠分' . $this->m_wGangScore[$i][$i] . ' ';
            }

            if (!empty($this->m_rule->is_zhama)) {
                if ($this->m_wZhamaScore[$i] > 0) {
                    $this->m_hu_desc[$i] .= '扎码+' . $this->m_wZhamaScore[$i] . ' ';
                } else {
                    $this->m_hu_desc[$i] .= '扎码' . $this->m_wZhamaScore[$i] . ' ';
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

    //洗牌
    public function WashCard()
    {
        $this->m_nCardBuf = ConstConfig::ALL_CARD_112_BAIBAN;
        $this->m_nAllCardNum = ConstConfig::BASE_CARD_NUM_HONG_ZHONG;
        if(defined("gf\\conf\\Config::TEST_PAI") && Config::TEST_PAI)
        {
            $this->m_nCardBuf = ConstConfig::ALL_CARD_112_BAIBAN_TEST;
        }

        if (Config::WASHCARD) {
            shuffle($this->m_nCardBuf);
            shuffle($this->m_nCardBuf);    //为了测试 不洗牌
        }
    }

    //批量发牌
    public function DealAllCardEx()
    {
        $temp_card = 255;
        $this->WashCard();

        //$this->_deal_test_card();

        //给每人发13张牌,整合成每个用户发一圈牌(4张)
        $tmp_card_arr = array(['', '', '', ''], ['', '', '', ''], ['', '', '', ''], ['', '', '', '']);
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            for ($k = 0; $k < ConstConfig::BASE_HOLD_CARD_NUM; $k++) {
                $temp_card = $this->m_nCardBuf[$this->m_nCountAllot++];    //从牌缓冲区里那张牌
                $this->_list_insert($i, $temp_card);
                $tmp_card_arr[intval($k / 4)][$i] .= sprintf("%02d", $temp_card);
            }
        }
        foreach ($tmp_card_arr as $tmp_card_item) {
            $this->_set_record_game(ConstConfig::RECORD_DRAW_ALL, intval($tmp_card_item[0]), intval($tmp_card_item[1]),
                intval($tmp_card_item[2]), intval($tmp_card_item[3]));
        }

        //给庄家发第14张牌
        $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->m_nCardBuf[$this->m_nCountAllot++];
        $this->_set_record_game(ConstConfig::RECORD_DRAW, $this->m_nChairBanker,
            $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);

        //整理排序
        $this->_list_insert($this->m_nChairBanker, $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now);
        $this->m_sPlayer[$this->m_nChairBanker]->card_taken_now = $this->_find_14_card($this->m_nChairBanker);
    }

    /////////////////////////公共函数区///////////////////////////

    //玩家i相对于玩家j的位置,如(0,3),返回3(即下家)
    public function _chair_to($i, $j)
    {
        return ($j - $i + $this->m_rule->player_count) % $this->m_rule->player_count;
    }

    //返回chair逆时针转 n 的玩家
    public function _anti_clock($chair, $n = 1)
    {
        return ($chair + $this->m_rule->player_count + $n) % $this->m_rule->player_count;
    }

    //插入牌  ok
    public function _list_insert($chair, $card)
    {
        $card_type = $this->_get_card_type($card);
        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID) {
            echo("错误牌类型，_list_insert" . __LINE__.__CLASS__);
            return false;
        }
        $card_key = $card % 16;
        //if($this->m_sPlayer[$chair]->card[$card_type][$card_key] < 4)
        {
            $this->m_sPlayer[$chair]->card[$card_type][$card_key] += 1;
            $this->m_sPlayer[$chair]->card[$card_type][0] += 1;
            $this->m_sPlayer[$chair]->len += 1;
            return true;
        }
        return false;
    }

    //删除牌  ok
    public function _list_delete($chair, $card)
    {
        $card_type = $this->_get_card_type($card);
        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID) {
            return false;
        }
        $card_key = $card % 16;
        if ($this->m_sPlayer[$chair]->card[$card_type][$card_key] > 0) {
            $this->m_sPlayer[$chair]->card[$card_type][$card_key] -= 1;
            $this->m_sPlayer[$chair]->card[$card_type][0] -= 1;
            $this->m_sPlayer[$chair]->len -= 1;
            return true;
        }
        return false;
    }

    // 查找牌，返回个数  ok
    public function _list_find($chair, $card)
    {
        $card_type = $this->_get_card_type($card);
        if ($card_type == ConstConfig::PAI_TYPE_PAI_TYPE_INVALID) {
            return false;
        }
        $card_key = $card % 16;
        return $this->m_sPlayer[$chair]->card[$card_type][$card_key];
    }

    // 返回牌的类型  ok
    public function _get_card_type($card)
    {
        if ($card <= 9 && $card >= 1) {
            return ConstConfig::PAI_TYPE_WAN;
        }
        if ($card <= 25 && $card >= 17) {
            return ConstConfig::PAI_TYPE_TIAO;
        }
        if ($card <= 41 && $card >= 33) {
            return ConstConfig::PAI_TYPE_TONG;
        }
        if ($card <= 55 && $card >= 49) {
            return ConstConfig::PAI_TYPE_FENG;
        }
        if ($card <= 72 && $card >= 65) {
            return ConstConfig::PAI_TYPE_DRAGON;
        }
        return ConstConfig::PAI_TYPE_PAI_TYPE_INVALID;
    }

    // 牌index  ok
    public function _get_card_index($type, $key)
    {
        //四川麻将没有风牌和花牌
        if ($type >= ConstConfig::PAI_TYPE_WAN && $type <= ConstConfig::PAI_TYPE_DRAGON && $key >= 1 && $key <= 9) {
            return $type * 16 + $key;
        }
        return 0;
    }

    // 取消选择buf
    public function _clear_choose_buf($chair, $ClearGang = true)
    {
        if ($ClearGang) {
            $this->m_sQiangGang->clear();
        }
        $this->m_bChooseBuf[$chair] = 0;
    }

    // 判断有没有吃
    public function _find_eat($chair, $num)
    {
        if ($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING) {
            return false;
        }

        $card_type = $this->_get_card_type($this->m_sOutedCard->card);
        if (ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type || ConstConfig::PAI_TYPE_FENG == $card_type || ConstConfig::PAI_TYPE_DRAGON == $card_type) {
            return false;
        }

        if ($num == 1) {
            $eat_card_first_tmp = $this->m_sOutedCard->card + 1;
            $eat_card_second_tmp = $this->m_sOutedCard->card + 2;
        } elseif ($num == 2) {
            $eat_card_first_tmp = $this->m_sOutedCard->card - 1;
            $eat_card_second_tmp = $this->m_sOutedCard->card + 1;
        } elseif ($num == 3) {
            $eat_card_first_tmp = $this->m_sOutedCard->card - 2;
            $eat_card_second_tmp = $this->m_sOutedCard->card - 1;
        }

        $card_count_first_tmp = $this->_list_find($chair, $eat_card_first_tmp);
        $card_count_second_tmp = $this->_list_find($chair, $eat_card_second_tmp);

        if ($card_count_first_tmp >= 1 && $card_count_second_tmp >= 1) {
            return true;
        }

        return false;
    }

    // 判断有没有碰  ok
    public function _find_peng($chair)
    {
        if ($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING) {
            return false;
        }

        if (self::is_peng_give_up($this->m_sOutedCard->card,$this->m_nPengGiveUp[$chair])){
        	return false;
        }

        $card_type = $this->_get_card_type($this->m_sOutedCard->card);
        if (ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type) {
            return false;
        }

        $card_count = $this->_list_find($chair, $this->m_sOutedCard->card);

        if ($card_count == 2 || $card_count == 3) {
            return true;
        }

        return false;
    }

    // 判断有没有别人打来的明杠  ok
    public function _find_zhi_gang($chair)
    {
        if ($this->m_sPlayer[$chair]->state != ConstConfig::PLAYER_STATUS_CHOOSING) {
            return false;
        }

        $card_type = $this->_get_card_type($this->m_sOutedCard->card);
        if (ConstConfig::PAI_TYPE_PAI_TYPE_INVALID == $card_type) {
            return false;
        }

        $card_count = $this->_list_find($chair, $this->m_sOutedCard->card);
        if ($card_count == 3) {
            return true;
        }
        return false;
    }

    //找出第14张牌  ok
    public function _find_14_card($chair)
    {
        $last_type = ConstConfig::PAI_TYPE_DRAGON;
        while (empty($this->m_sPlayer[$chair]->card[$last_type][0])) {
            $last_type--;
            if ($last_type < 0) {
                break;
            }
        }
        if ($last_type < 0) {
            echo("竟然没有牌aaaaaaaas" . __LINE__.__CLASS__);
            return false;
        }

        for ($i = 9; $i > 0; $i--) {
            if ($this->m_sPlayer[$chair]->card[$last_type][$i] > 0) {
                $fouteen_card = $this->_get_card_index($last_type, $i);
                $this->m_sPlayer[$chair]->card[$last_type][$i] -= 1;
                $this->m_sPlayer[$chair]->card[$last_type][0] -= 1;
                $this->m_sPlayer[$chair]->len -= 1;
                break;
            }
        }

        if (empty($fouteen_card)) {
            return false;
        }

        return $fouteen_card;
    }

    //掷骰定庄家
    public function _on_table_status_to_playing()
    {
        $result = Room::$get_conf;
        if (empty($result['data']['winner_currency'])) {
            $this->m_nChairBanker = 0;
        } else {
            $this->m_nChairBanker = mt_rand(0, ($this->m_rule->player_count - 1));
        }
        return;
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
            $this->m_nSetCount = 255;   //用于解散结束牌局判定
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

    //倒牌某门牌的个数
    public function _stand_type_count($chair, $card_type)
    {
        $card_num = 0;

        if ($this->m_sStandCard[$chair]->num > 0) {//有倒牌
            for ($i = 0; $i < $this->m_sStandCard[$chair]->num; $i++) {
                if ($this->_get_card_type($this->m_sStandCard[$chair]->card[$i]) == $card_type) {
                    if (ConstConfig::DAO_PAI_TYPE_SHUN == $this->m_sStandCard[$chair]->type[$i] || ConstConfig::DAO_PAI_TYPE_KE == $this->m_sStandCard[$chair]->type[$i]) {
                        $card_num += 3;
                    } elseif (ConstConfig::DAO_PAI_TYPE_MINGGANG == $this->m_sStandCard[$chair]->type[$i] || ConstConfig::DAO_PAI_TYPE_ANGANG == $this->m_sStandCard[$chair]->type[$i] || ConstConfig::DAO_PAI_TYPE_WANGANG == $this->m_sStandCard[$chair]->type[$i]) {
                        $card_num += 4;
                    }
                }
            }
        }
        return $card_num;
    }

    //跟庄
    public function _genzhuang_do()
    {
        if (!empty($this->m_rule->is_genzhuang) && $this->m_sFollowCard->status == ConstConfig::FOLLOW_STATUS && 4 == $this->m_rule->player_count) 
        {
            if (0 == $this->m_sFollowCard->follow_card && $this->m_sOutedCard->chair == $this->m_nChairBanker) 
            {
                $this->m_sFollowCard->follow_card = $this->m_sOutedCard->card;
                $this->m_sFollowCard->num += 1;
            } 
            elseif (($this->m_sFollowCard->follow_card == $this->m_sOutedCard->card) || ($this->m_sOutedCard->card == 55)) 
            {
                $this->m_sFollowCard->num += 1;
            } 
            else 
            {
                $this->m_sFollowCard->status = ConstConfig::NOT_FOLLOW_STATUS;
            }

            if ($this->m_sFollowCard->num >= $this->m_rule->player_count) 
            {
                for ($i = 0; $i < $this->m_rule->player_count; $i++) 
                {
                    $nFollowScore = 2 * ConstConfig::N_FOLLOWSCORE;//跟庄2分
                    if ($i == $this->m_nChairBanker) 
                    {
                        continue;
                    }
                    $this->m_wFollowScore[$this->m_nChairBanker] -= $nFollowScore;
                    $this->m_wFollowScore[$i] += $nFollowScore;
                }

                $this->_set_record_game(ConstConfig::RECORD_GENZHUANG, $this->m_nChairBanker,
                    $this->m_sFollowCard->follow_card);

                //状态变化发消息
                $this->_send_act('c_follow', 0, $this->m_sFollowCard->follow_card);
                $this->m_sFollowCard->clear();//更新跟庄标记 status=1,
            }
        }
    }

    public function _deal_test_card()
    {
        //发测试牌
        for ($i = 0; $i < $this->m_rule->player_count; $i++) {
            $power = 0;
            if ($i == 0) {
                $power = 25;
            }
            if (defined("gf\\conf\\Config::WHITE_UID") && in_array($this->m_room_players[$i]['uid'],
                    Config::WHITE_UID)
            ) {
                $power = 100;
            }
            if (mt_rand(1, 100) <= $power) {
                $this->_change_pai($i);
                break;
            }
        }
    }

    //换三张
    public function _change_pai($chair)
    {
        $change_arr = array();
        $pai = mt_rand(ConstConfig::PAI_TYPE_WAN, ConstConfig::PAI_TYPE_TIAO);
        $key = mt_rand(1, 7);
        $change_arr[] = $this->_get_card_index($pai, $key);
        $change_arr[] = $this->_get_card_index($pai, $key + 1);
        $change_arr[] = $this->_get_card_index($pai, $key + 2);
        $key = mt_rand(1, 7);
        $change_arr[] = $this->_get_card_index($pai, $key);
        $change_arr[] = $this->_get_card_index($pai, $key + 1);
        $change_arr[] = $this->_get_card_index($pai, $key + 2);
        // $key = mt_rand(1,9);
        // $change_arr[] = $this->_get_card_index($pai, $key);
        // $change_arr[] = $this->_get_card_index($pai, $key);
        // $change_arr[] = $this->_get_card_index($pai, $key);

        $index = 0;
        foreach ($change_arr as $change_item) {
            if ($this->m_nCardBuf[$index] != $change_item) {
                for ($k = $index + 1; $k < $this->m_nAllCardNum; $k++) {
                    if ($this->m_nCardBuf[$k] == $change_item) {
                        $this->m_nCardBuf[$k] = $this->m_nCardBuf[$index];
                        $this->m_nCardBuf[$index] = $change_item;
                        break;
                    }
                }
            }
            $index = $index + 1;
        }

        if ($chair != 0) {
            $offset = $chair * 13;
            for ($m = 1; $m <= 13; $m++) {
                $tmp = $this->m_nCardBuf[$m];
                $this->m_nCardBuf[$m] = $this->m_nCardBuf[$m + $offset];
                $this->m_nCardBuf[$m + $offset] = $tmp;
            }
        }
    }

    //订翻混
    public function _get_fan_hun($fan_hun_card)
    {
        $temp_type = $this->_get_card_type($fan_hun_card);
        $temp_card_index = $fan_hun_card % 16;

        if ($temp_type == ConstConfig::PAI_TYPE_WAN || $temp_type == ConstConfig::PAI_TYPE_TIAO || $temp_type == ConstConfig::PAI_TYPE_TONG) {
            $tmp_index_array = array(0, 2, 3, 4, 5, 6, 7, 8, 9, 1);
        } elseif ($temp_type == ConstConfig::PAI_TYPE_FENG) {
            $tmp_index_array = array(0, 2, 3, 4, 1, 6, 7, 5);
        } elseif ($temp_type == ConstConfig::PAI_TYPE_DRAGON) {
            $tmp_index_array = array(0, 2, 3, 4, 1, 6, 7, 8, 5);
        } else {
            echo("混牌错误，出现未定义类型的牌" . __LINE__.__CLASS__);
            return false;
        }

        $this->m_hun_card = $this->_get_card_index($temp_type, $tmp_index_array[$temp_card_index]);  //翻混的index
        return $this->m_hun_card;
    }

    //写录像
    public function _set_record_game($act, $param_1 = 0, $param_2 = 0, $param_3 = 0, $param_4 = 0)
    {
        $param_1_tmp = 0;
        $param_3_tmp = 0;
        if (in_array($act, [
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
            ConstConfig::RECORD_HU_QIANGGANG,
        ])) {
            if (is_array($param_1)) {
                foreach ($param_1 as $value) {
                    $param_1_tmp += pow(2, $value);
                }
            } else {
                $param_1_tmp += pow(2, $param_1);
            }

            $param_3_tmp += pow(2, $param_3);
        } else {
            $param_1_tmp = $param_1;
            $param_3_tmp = $param_3;
        }

        $this->m_record_game[] = $act . '|' . $param_1_tmp . '|' . $param_2 . '|' . $param_3_tmp . '|' . $param_4;
        $a = $act . '|' . $param_1_tmp . '|' . $param_2 . '|' . $param_3_tmp . '|' . $param_4;
    }

    //写完录像 整理
    public function _set_game_info()
    {
        $game_info = [];
        $game_info['date'] = date('m-d H:i:s', time());
        $game_info['rule'] = $this->m_rule;
        $game_info['play'] = $this->m_room_players;
        $game_info['game'] = implode(',', $this->m_record_game);

        if (!$game_info['game']) {
            return false;
        }
        return $game_info;
    }

    //回调web   ok
    public function _set_game_and_checkout($is_log = false)
    {
        //游戏记录
        $itime = time();
        $uid_arr = array();
        foreach ($this->m_room_players as $key => $room_user) {
            if (!empty($room_user['uid'])) {
                $uid_arr[] = $room_user['uid'];
            }
        }

        $is_room_over = 0;
        if (empty($this->m_rule)
            || ($this->m_nSetCount != 255 && $this->m_rule->set_num <= $this->m_nSetCount && (empty($this->m_rule->is_circle) || $this->m_nChairBanker != $this->m_nChairBankerNext))
            || ($this->m_nSetCount == 255 && $is_log)
        ) {
            $is_room_over = 1;
        }
        //web set_game_log
        $tmp_game_info = $this->_set_game_info();
        if ($tmp_game_info && $this->m_nSetCount != 255) {    //非投票解散的牌局
            BaseFunction::web_curl(array(
                'mod' => 'Business',
                'act' => 'set_game_log',
                'platform' => 'gfplay',
                'rid' => $this->m_room_id,
                'uid' => $this->m_room_owner,
                'uid_arr' => implode(',', $uid_arr)
            ,
                'game_info' => json_encode($tmp_game_info, JSON_UNESCAPED_UNICODE),
                'type' => 1,
                'is_room_over' => $is_room_over,
                'game_type' => $this->m_game_type,
                'play_time' => $itime - $this->m_start_time
            ));
        }

        //扣费或充值
        $result = Room::$get_conf;
        if (!empty($result['data']['room_type'])) {
            $currency_tmp = BaseFunction::need_currency($result['data']['room_type'], $this->m_game_type,
                $this->m_rule->set_num);
        }

        if (empty($result['data']['winner_currency'])) {
            if ($this->m_nSetCount == 1) {
                $currency = !empty($currency_tmp) ? (-$currency_tmp) : 0;
                BaseFunction::web_curl(array(
                    'mod' => 'Business',
                    'act' => 'checkout_open_room',
                    'platform' => 'gfplay',
                    'uid' => $this->m_room_owner,
                    'currency' => $currency,
                    'type' => 1
                ));
            }
        } else {
            if ($is_room_over == 1) {
                $big_score = 0;
                $winner_arr = array();
                for ($i = 0; $i < $this->m_rule->player_count; $i++) {
                    if ($this->m_wTotalScore[$i]->n_score > $big_score) {
                        $big_score = $this->m_wTotalScore[$i]->n_score;
                        $winner_arr = array();
                        $winner_arr[] = $this->m_room_players[$i]['uid'];
                    } else {
                        if ($this->m_wTotalScore[$i]->n_score == $big_score && !empty($this->m_room_players[$i])) {
                            $winner_arr[] = $this->m_room_players[$i]['uid'];
                        }
                    }
                }
                $winner_count = 1;
                if ($winner_arr) {
                    $winner_count = count($winner_arr);
                }
                $currency_all = !empty($currency_tmp) ? $currency_tmp : 0;
                $currency = -(intval($currency_all / $winner_count));
                foreach ($winner_arr as $item_user) {
                    BaseFunction::web_curl(array(
                        'mod' => 'Business',
                        'act' => 'checkout_open_room',
                        'platform' => 'gfplay',
                        'uid' => $item_user,
                        'currency' => $currency,
                        'type' => 1
                    ));
                }
            }
        }
    }

    //扎码
    public function _zha_ma()
    {
        $chair = 255;
        $count_ma = 0;

        foreach ($this->m_HuCurt as $key => $item_hu_curt) 
        {
            if ($item_hu_curt->state == ConstConfig::WIN_STATUS_ZI_MO || $item_hu_curt->state == ConstConfig::WIN_STATUS_CHI_PAO) 
            {
                $chair = $key;
                break;
            }
        }
        if ($chair == 255) 
        {
            return;
        }

        if (empty($this->m_rule->is_zhama))
        {
        	return;
        }
        elseif($this->m_rule->is_zhama == 1)
        {
        	if (!empty($this->m_nCardBuf[$this->m_nCountAllot])) 
        	{
                $yima = $this->m_nCardBuf[$this->m_nCountAllot];
                $this->m_nCountAllot++;
                $this->m_ZhaMaCard[] = $yima;
            }
            if($yima == 55)
            {
            	$count_ma = 10; 
            }
            else
            {
            	$count_ma = $yima % 16;
            }
        } 
        else
        {
            $bird_pool = array(1, 5, 9, 17, 21, 25, 33, 37, 41, 55);
            for ($i = 0; $i < $this->m_rule->is_zhama; $i++) 
            {
                //没牌了
                if (empty($this->m_nCardBuf[$this->m_nCountAllot]))
                {
                    break;
                } 
                else 
                {
                    $this->m_ZhaMaCard[] = $this->m_nCardBuf[$this->m_nCountAllot];
                    if (in_array($this->m_nCardBuf[$this->m_nCountAllot], $bird_pool)) 
                    {
                        $count_ma += 1;
                    }
                    $this->m_nCountAllot++;
                }
            }
        }
        
        //算分
        for ($j = 0; $j < $this->m_rule->player_count; $j++) 
        {
            if ($j == $chair) 
            {
                continue;
            } 
            else 
            {
                $this->m_wZhamaScore[$j] -=$count_ma;
                $this->m_wZhamaScore[$chair] += $count_ma;
            }
        }

        //抢杠胡包扎码分
        foreach ($this->m_HuCurt as $key => $item_hu_curt) 
        {
            if ($item_hu_curt->state == ConstConfig::WIN_STATUS_CHI_PAO) 
            {
                for($i=0; $i<$this->m_rule->player_count; $i++)
				{
					if($this->m_wZhamaScore[$i] < 0)
					{
						$this->m_wZhamaScore[$i] = 0;
					}
				}
				$this->m_wZhamaScore[$this->m_sQiangGang->chair] = - $this->m_wZhamaScore[$chair];
            }
        }
        

        $zhama = $this->m_ZhaMaCard;
        $num1 = isset($zhama[0]) ? $zhama[0] : 0;
        $num2 = isset($zhama[1]) ? $zhama[1] : 0;
        $num3 = isset($zhama[2]) ? $zhama[2] : 0;
        $num4 = isset($zhama[3]) ? $zhama[3] : 0;
        $num5 = isset($zhama[4]) ? $zhama[4] : 0;
        $num6 = isset($zhama[5]) ? $zhama[5] : 0;

        $param1 = $num1 * 100 + $num2;
        $param2 = $num3 * 100 + $num4;
        $param3 = $num5 * 100 + $num6;
        $this->_set_record_game(ConstConfig::RECORD_ZHUANIAO, $chair, $param1, $param2,$param3);

        return $count_ma;

    }
}

