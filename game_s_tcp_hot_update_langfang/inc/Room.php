<?php


namespace gf\inc;

// use gf\inc\GameBaoding;
// use gf\inc\GameXingtaiDahu;
// use gf\inc\GameXingtaiPinghu;
// use gf\inc\GameXingtaiTuidaohu;
// use gf\inc\GameDaHong5;
use gf\inc\ConstConfig;
use gf\conf\Config;
use gf\inc\BaseFunction;


class TotalScore
{
	public $n_score;	        // 房总分
	public $n_zimo;		        // 总自摸次数
	public $n_jiepao;	        // 总接炮次数
	public $n_dianpao;	        // 总点炮次数
	public $n_angang;	        // 总暗杠次数
	public $n_zhigang_wangang;	// 总明杠次数

	public function clear()
	{
		$this->n_score = 0;
		$this->n_zimo = 0;
		$this->n_jiepao = 0;
		$this->n_dianpao = 0;
		$this->n_angang = 0;
		$this->n_zhigang_wangang = 0;
	}

	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－比分－－－－－－－－－－－
class Score
{
	public $score;		      // 分
	public $win_count;	      // 胜
	public $lose_count;	      // 负
	public $draw_count;	      // 和
	public $flee_count;	      // 逃跑
	public $set_count;	      // 局数

	public function clear()
	{
		$this->score = 0;
		$this->win_count = 0;
		$this->lose_count = 0;
		$this->draw_count = 0;
		$this->flee_count = 0;
		$this->set_count = 0;
	}

	public function __construct()
	{
		$this->clear();
	}
}

// 比分
class Score_Runfast
{
	public $score;		// 分
	public $win_count;		// 胜
	public $lose_count;	// 负
	public $draw_count;	// 和
	public $flee_count;	// 逃跑
	public $set_count;		// 局数
	public $bomb_count;		// 炸弹分
	public $times;		// 关门倍数
	public $is_baopei;		// 包赔

	public function clear()
	{
		$this->score = 0;
		$this->win_count = 0;
		$this->lose_count = 0;
		$this->draw_count = 0;
		$this->flee_count = 0;
		$this->set_count = 0;
		$this->bomb_count = 0;
		$this->times = 0;
		$this->is_baopei = 0;
	}

	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－命令格式－－－－－－－－－－－－
class Game_cmd
{
	
	// 发送命令的类型
	const SCO_ALL_PLAYER = 1;			// 所有玩家
	const SCO_ALL_PLAYER_EXCEPT = 2;	// 除指定玩家之外的所有玩家
	const SCO_SINGLE_PLAYER = 3;		// 指定用户	
	
	public $rid = 0;
	public $act = 0;
	public $data = array();
	public $scope = self::SCO_ALL_PLAYER;
	public $uid = 0;	                // 根据scope 排除或发送的 uid
	public $chair = 255;

	public function __construct($rid = 0, $act = 0, $data = array(), $scope=self::SCO_ALL_PLAYER , $uid = 0)
	{
		$this->rid = $rid;
		$this->act = $act;
		$this->data = $data;
		$this->scope = $scope;
		$this->uid = $uid;
	}

	public function set_data($key, $data)
	{
		$this->data[$key] = $data;
		return true;
	}

    public function send($serv, $after_time = 0)
    {
        if($this->act == 's_sys_phase_change' && $after_time > 0)
        {
            swoole_timer_after($after_time, function() use ($serv)
                {
                    $this->_send($serv);
                }
            );
        }
        else
        {
            $this->_send($serv);
        }
    }

	public function _send($serv)
	{
		if(empty(Room::$room_arr[$this->rid]) || empty(Room::$room_arr[$this->rid]->m_room_players))
		{
			return false;
		}

		foreach (Room::$room_arr[$this->rid]->m_room_players as $key => $player_item)
		{
			$this->chair = $key;

			if(empty($player_item['fd']))
			{
				return false;
			}
			if($this->scope == self::SCO_ALL_PLAYER )
			{
				$serv->send($player_item['fd'], Room::tcp_encode(($this)));
			}
			else if ($this->scope == self::SCO_ALL_PLAYER_EXCEPT && $player_item['uid'] != $this->uid)
			{
				$serv->send($player_item['fd'], Room::tcp_encode(($this)));
			}
			else if ($this->scope == self::SCO_SINGLE_PLAYER  && $player_item['uid'] == $this->uid)
			{
				$serv->send($player_item['fd'], Room::tcp_encode(($this)));
			}
		}
	}
}

//－－－－－－－－－－－－斗地主玩家手牌数据－－－－－－－－－－－－
class Play_data_landlord
{
    public $state;            // 玩家状态
    public $len;              // 手拿多少张牌;
    public $card;     // 存放牌的数组
    public $card_index;     // 存放牌的数组

    public function clear()
    {
        $this->state = ConstConfig::PLAYER_LANDLORD_STATUS_WAITING;
        $this->len = 0;
        $this->card = array();
        $this->card_index = [0, 0,0,0,0,0, 0,0,0,0,0, 0,0,0,0,0];   //每个level相同的牌的个数
    }

    public function __construct()
    {
        $this->clear();
    }
}

//－－－－－－－－－－－－斗地主牌型数据－－－－－－－－－－－－
class Outed_card_landlord
{
    public $chair;      // 谁打出的
    public $card;       // 牌
    public $len;        // 牌数量
    public $pai_type;   // 是什么牌牌型
    public $level;      // 牌大小

    public function clear()
    {
        $this->chair = 255;
        $this->card = array();
        $this->len = 0;
        $this->pai_type = ConstConfig::PAI_TYPE_LANDLORD_INVALID;
        $this->level = 0;
    }

    public function __construct()
    {
        $this->clear();
    }
}
//－－－－－－－－－－－－跑得快玩家手牌数据－－－－－－－－－－－－
class Play_data_runfast
{
    public $state;            // 玩家状态
    public $len;              // 手拿多少张牌;
    public $card;     // 存放牌的数组
    public $card_index;     // 存放牌的数组

    public function clear()
    {
        $this->state = ConstConfig::PLAYER_RUNFAST_STATUS_WAITING;
        $this->len = 0;
        $this->card = array();
        $this->card_index = [0, 0,0,0,0,0, 0,0,0,0,0, 0,0,0];   //每个level相同的牌的个数
    }

    public function __construct()
    {
        $this->clear();
    }
}

//－－－－－－－－－－－－跑得快牌型数据－－－－－－－－－－－－
class Outed_card_runfast
{
    public $chair;      // 谁打出的
    public $card;       // 牌
    public $len;        // 牌数量
    public $pai_type;   // 是什么牌牌型
    public $level;      // 牌大小
    public $is_first;      // 主动出牌  1主动出牌  0被动出牌

    public function clear()
    {
        $this->chair = 255;
        $this->card = array();
        $this->len = 0;
        $this->pai_type = ConstConfig::PAI_TYPE_RUNFAST_INVALID;
        $this->level = 0;
        $this->is_first = 0;
    }

    public function __construct()
    {
        $this->clear();
    }
}


//－－－－－－－－－－－－打红5玩家手牌数据－－－－－－－－－－－－
class Play_data_dahong5
{
    public $state;          // 玩家状态
    public $len;            // 手拿多少张牌;
    public $card;     		// 存放牌的数组

    public function clear()
    {
        $this->state = ConstConfig::PLAYER_LANDLORD_STATUS_WAITING;
        $this->len = 0;
        $this->card = array();
    }

    public function __construct()
    {
        $this->clear();
    }
}

//－－－－－－－－－－－－打红5牌型数据－－－－－－－－－－－－
class Outed_card_dahong5
{
    public $chair;      // 谁打出的
    public $card;       // 牌
    public $pai_type;   // 是什么牌牌型
    public $level;      // 牌大小
    public $len;        // 牌数量

    public function clear()
    {
        $this->chair = 255;
        $this->card = array();
        $this->pai_type = ConstConfig::PAI_TYPE_DAHONG5_INVALID;
        $this->level = 0;
        $this->len = 0;
    }

    public function __construct()
    {
        $this->clear();
    }
}

//－－－－－－－－－－－－玩家手牌数据－－－－－－－－－－－－
class Play_data
{
	public $state;            // 玩家状态
	public $len;              // 手拿多少张牌,不包括 card_taken_now;
	public $card_taken_now;   // 表示拿到那张牌的id，如果吃碰杠了，则是第14张牌
	public $card = array();     // 存放4种牌型的数组,cCardes[0][0]:万字牌数,1:条子,2:筒子 3 风牌
	public $seen_out_card;    // 能否看到出的牌，在竞争选择时候用
	public $kou_card = array();		// [[0,0], [0,0], ...] 牌; 状态 0未操作 1 扣 2 不扣 3 扣之后倒牌用到
	public $kou_card_display = array();	// [17, 1, 33, 18 ...] 已经扣的牌;
	public function clear()
	{
		$this->state = 3;	  // ConstConfig::PLAYER_STATUS_WAITING ;	//3
		$this->len = 0;
		$this->card_taken_now = 0;
		$this->card[0] = [0,0,0,0,0,0,0,0,0,0];
		$this->card[1] = [0,0,0,0,0,0,0,0,0,0];
		$this->card[2] = [0,0,0,0,0,0,0,0,0,0];
		$this->card[3] = [0,0,0,0,0,0,0,0,0,0];
		$this->seen_out_card = 1;
		$this->kou_card = array();
		$this->kou_card_display = array();
	}
	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－玩家倒牌－－－－－－－－－－－－
class Stand_card
{
	public $num = 0;                // 倒牌次数
	public $type = array();         // 顺，刻，明杠，暗杠 //类型
	public $who_give_me = array();  // 谁打给我上的
	public $card = array();         // 被上的牌
	public $first_card = array();   // 牌型的第一张牌
	public function clear()
	{
		$this->num = 0;
		$this->type = array();
		$this->who_give_me = array();
		$this->card = array();
		$this->first_card = array();
	}
	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－跟庄－－－－－－－－－－－－－－－－－－－－
class Follow_card
{
	public $status;                // 跟庄状态
	public $num;                   // 跟庄人数
	public $follow_card;           // 跟庄的牌

	public function clear()
	{
		$this->status = ConstConfig::FOLLOW_STATUS;
		$this->num = 0;
		$this->follow_card = 0; 
	}
	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－胡牌－－－－－－－－－－－－－－－－－－
class Hu_curt
{
	public $state;
	public $card;
	public $jiang_card;
	public $type;
	public $count;
	public $method = array();
	public $gain_chair = array();
	public $card_state;

	public function __construct()
	{
		$this->clear();
	}

	public function clear()
	{
		$this->state = 0;	//ConstConfig::WIN_STATUS_NOTHING ; //事不关己状态 0
		$this->card = 0;
		$this->jiang_card = 0;
		$this->type = 255;
		$this->count = 0;
		$this->method = array();
		$this->gain_chair = array(0);
		$this->card_state = 1;
	}

	public function add_hu($method_item)
	{
		$this->method[$this->count] = $method_item;
		$this->count++;
	}
}

//－－－－－－－－－－－－出牌－－－－－－－－－－－－
class Outed_card
{
	public $chair;            // 谁打出的
	public $card;             // 是什么牌

	public function clear()
	{
		$this->chair = 255;
		$this->card = 0;
	}

	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－抢杠－－－－－－－－－－－－
class Qiang_gang
{
	public $mark;
	public $card;
	public $chair;

	public function init_data($mark, $card, $chair)
	{
		$this->mark = $mark;
		$this->card = $card;
		$this->chair = $chair;
	}
	public function clear()
	{
		$this->mark = false;
		$this->card = 0;
		$this->chair = 255;
	}

	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－杠炮－－－－－－－－－－－－
class Gang_pao
{
	public $mark;
	public $card;
	public $chair;
	public $type;	        // 杠类型  直杠弯杠 暗杠
	public $score;	        // 本次杠分数

	public function init_data($mark, $card, $chair, $type, $score)
	{
		$this->mark = $mark;
		$this->card = $card;
		$this->chair = $chair;
		$this->type = $type;
		$this->score = $score;
	}
	public function clear()
	{
		$this->mark = false;
		$this->card = 0;
		$this->chair = 255;
		$this->type = 0;
		$this->score = 0;
	}
	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－杠－－－－－－－－－－－－
class Gang_suit
{
	public $num;               // 有几个杠
	public $card = array();    // 杠的牌
	public $type = array();    // 是暗杠,还是可胡明杠
	public function clear()
	{
		$this->num = 0;
		$this->card = array();
		$this->type = array();
	}
	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－吃牌的结构－－－－－－－－－－－－
class Eat_suit
{
	public $num;                // 有几种吃法
	public $card = array();     // 第i种吃法3张牌

	public function clear()
	{
		$this->num = 0;
		$this->card = array();
	}
	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－炮子结构－－－－－－－－－－－－－－－
class Pao_zi
{
	public $recv = false;          // 是否收到客户端消息
	public $num;                   // 炮子数
	public function clear()
	{
		$this->recv = false;
		$this->num = 0;
	}
	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－拉庄结构－－－－－－－－－－－－－－－
class La_zhuang
{
    public $recv = false;          // 是否收到客户端消息
    public $num;                   // 拉庄数
    public function clear()
    {
        $this->recv = false;
        $this->num = 0;
    }
    public function __construct()
    {
        $this->clear();
    }
}

//－－－－－－－－－－－－换三张结构－－－－－－－－－－－－－－－
class Huan_3
{
	public $card_arr;             // 换掉牌
	public $get_card_arr;         // 得到牌

	public function clear()
	{
		$this->card_arr = array();
		$this->get_card_arr = array();
	}
	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－定缺结构－－－－－－－－－－－－－－－－－－
class Ding_que
{
	public $recv = false;         // 是否收到客户端定缺消息
	public $card_type;            // 定缺牌型
	public function clear()
	{
		$this->recv = false;
		$this->card_type = 255;
	}
	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－买马结构－－－－－－－－－－－－－－－－－－
class Mai_ma
{
	public static $ma_chair_arr = [1=>0, 2=>1, 3=>2, 4=>3, 5=>0, 6=>1, 7=>2, 8=>3, 9=>0];
	public static $ma_chair_feng_arr = [1=>0, 2=>1, 3=>2, 4=>3, 5=>1, 6=>2, 7=>3];
	
	public $card_arr;            // 马牌
	public $state_arr;	         // 状态 0打合 -1输 1赢
	public $site_arr;	         // 相对位置 0 自己 1 下家 2对门 3 上家
	public function clear()
	{
		$this->card_arr = array();
		$this->state_arr = array();
		$this->site_arr = array();
	}
	public function __construct()
	{
		$this->clear();
	}
}

//－－－－－－－－－－－－河北承德推倒胡规则 －－－－－－－－－－－－－－－－－
class RuleChengDeTuiDaoHu
{
    public $game_type;
    public $player_count;        // 玩家人数 2 3 4
    public $set_num;             // 房间局数 4 8 16
    public $min_fan;             // 胡牌最小番 0
    public $top_fan;             // 不封顶 255

    public $is_feng;             // 带风牌 0 否 1 是 (默认1)

    public function __construct()
    {
        $this->clear();
    }
    public function clear()
    {
        $this->game_type = 0;
        $this->player_count = 4;
        $this->set_num = 8;
        $this->min_fan = 0;
        $this->top_fan = 255;

        $this->is_feng = 0;
    }
}

//－－－－－－－－－－－－廊坊文安规则 －－－－－－－－－－－－－－－－－
class RuleWenAn
{
    public $game_type;
    public $player_count;       // 玩家人数 2 3 4
    public $set_num;            // 房间局数 4 8 16
    public $min_fan;            // 胡牌最小番 0
    public $top_fan;            // 不封顶 255

    public $is_circle;          // 圈数 1 2 4
    public $is_feng;            // 带风牌 0 否 1 是
    public $is_yipao_duoxiang;  // 是否一炮多响 0 否 1 是
    public $is_chipai;          // 七对加番  0 否 1 是
    public $is_genzhuang;       //跟庄 0 否 1 是
    public $is_paozi;           //是否下炮子 0 否 1 是

    public $is_daizhuang;       //是否带庄闲 0 否 1 是
    public $is_lazhuang;        //是否带拉庄 0 否 1 是

    public $is_qingyise_fan;    //清一色加番 0 否 1 是
    public $is_ziyise_fan;      //字一色加番 0 否 1 是
    public $is_yitiaolong_fan;  //一条龙加番  0 否 1 是
    public $is_ganghua_fan;     //杠上花加番 0 否 1 是
    public $is_qidui_fan;       //七对加番  0 否 1 是
    public $is_pengpenghu_fan;  //碰碰胡加分 0 否 1 是

    public $is_wangang_1_lose;  //弯杠点碰者1人给分  0 否 1 是
	public $is_dianpao_bao;     //是否点炮大包 0 点炮三家出（发胡） 1 点炮大包庄（点炮）
	public $is_wukui;	        //是否捉五魁 0 否 1 是
	public $is_diaowuwan;	    //是否吊五万 0 否 1 是
	public $is_zhongfabai_shun;	//中发白做顺子
	public $is_bian_zuan;	    //带边钻 0 否 1 是
	public $is_za;	            //带3砸 0 否 1 是
	public $pay_type;           //付费方式

    public $cancle_clocker;     //是否有倒计时 0 否 1 是
    public $allow_louhu;        //是否允许漏胡 0 否 1 是
    public $qg_is_zimo;         //抢杠算自摸 0 否 1 是
    public $is_fanhun;          //是否翻混 0 否 1 是

    public function __construct()
    {
        $this->clear();
    }
    public function clear()
    {
        $this->game_type = 0;
        $this->player_count = 4;
        $this->set_num = 4;
        $this->min_fan = 0;
        $this->top_fan = 255;

        $this->is_circle = 1;
        $this->is_feng = 1;
        $this->is_yipao_duoxiang = 0;
        $this->is_chipai = 0;
        $this->is_genzhuang = 0;
        $this->is_paozi = 0;
        $this->is_daizhuang = 1;
        $this->is_lazhuang = 1;
        
        $this->is_qingyise_fan = 0;
        $this->is_ziyise_fan = 0;
        $this->is_yitiaolong_fan = 1;
        $this->is_qidui_fan = 1;
        $this->is_ganghua_fan = 1;
        $this->is_pengpenghu_fan = 0;

        $this->is_wangang_1_lose = 0;
        $this->is_dianpao_bao = 0;
        $this->is_wukui = 0;
        $this->is_diaowuwan = 0;
        $this->is_zhongfabai_shun = 0;
        $this->is_bian_zuan = 0;
        $this->is_za = 0;
        $this->pay_type = 0;

        $this->cancle_clocker = 1;
        $this->allow_louhu = 0;
        $this->qg_is_zimo = 0;
        $this->is_fanhun = 1;
    }
}
//－－－－－－－－－－－－河北沧州规则－－－－－－－－－－－－－－－－－
class RuleCangZhou
{
    public $game_type;
    public $player_count;        // 玩家人数 2 3 4
    public $set_num;             // 房间局数 4 8 16   
    public $min_fan;             // 胡牌最小番 0
    public $top_fan;             // 封顶 255

    public $is_circle;			//是否按圈打 0 否 1 是
    public $is_feng;             // 带风牌 0 否 1 是
    public $is_yipao_duoxiang;   // 是否一炮多响 0 否 1 是
    public $is_chipai;        // 七对加番  0 否 1 是
    public $is_genzhuang;  //跟庄 0 否 1 是
    public $is_paozi;        //是否下炮子 0 否 1 是
    public $is_zhuang_fan;   //是否带庄闲 0 否 1 是

    public $is_qingyise_fan; //清一色加番 0 否 1 是
    public $is_ziyise_fan; //字一色加番 0 否 1 是
    public $is_yitiaolong_fan;   //一条龙加番  0 否 1 是
    public $is_ganghua_fan;  //杠上花加番 0 否 1 是
    public $is_qidui_fan; //七对加番  0 否 1 是
    public $is_pengpenghu_fan;  //碰碰胡加分 0 否 1 是

    public $is_wangang_1_lose; //弯杠点碰者1人给分  0 否 1 是
	public $is_dianpao_bao;    //是否点炮大包 0 点炮三家出（发胡） 1 点炮大包庄（点炮）
	public $is_wukui;	//是否捉五魁 0 否 1 是
	public $is_diaowuwan;	//是否吊五万 0 否 1 是
	public $is_zhongfabai_shun;	//中发白做顺子
	public $is_bian_zuan;	//带边钻 0 否 1 是
	public $is_za;	//带3砸 0 否 1 是
	public $pay_type;//付费方式
	
	public $cancle_clocker;//是否带倒计时功能 0 否 1 是
	public $allow_louhu;//是否允许漏胡（过手胡） 0 否 1 是
	public $qg_is_zimo;//是否抢杠胡算自摸 0 否 1 是
	
	//积分新增
	public $is_score_field;    //0:普通场,1:积分场
	public $score;             //积分登记,0:0积分,100:100积分, 500:500积分场              

    public function __construct()
    {
        $this->clear();
    }
    public function clear()
    {
        $this->game_type = 0;
        $this->player_count = 4;
        $this->set_num = 8;
        $this->min_fan = 0;
        $this->top_fan = 255;

        $this->is_circle = 1;
        $this->is_feng = 1;
        $this->is_yipao_duoxiang = 0;
        $this->is_chipai = 0;
        $this->is_genzhuang = 0;
        $this->is_paozi = 0;
        $this->is_zhuang_fan = 1;
        
        $this->is_qingyise_fan = 1;
        $this->is_ziyise_fan = 1;
        $this->is_yitiaolong_fan = 1;
        $this->is_qidui_fan = 1;
        $this->is_ganghua_fan = 1;
        $this->is_pengpenghu_fan = 1;

        $this->is_wangang_1_lose = 0;
        $this->is_dianpao_bao = 0;
        $this->is_wukui = 1;
        $this->is_diaowuwan = 1;
        $this->is_zhongfabai_shun = 0;
        $this->is_bian_zuan = 1;
        $this->is_za = 0;
        $this->pay_type = 0;
        $this->cancle_clocker = 1;
		$this->allow_louhu = 1;
		$this->qg_is_zimo = 1;

        //积分新增
        $this->is_score_field = 0;
        $this->score = 0;
    }
}

//－－－－－－－－－－血战到底规则－－－－－－－－－－－－－－－－－
class RuleXueZhan
{
    public $game_type;
    public $player_count;        // 玩家人数 2 3 4
    public $set_num;             // 房间局数 4 8 16
    public $min_fan;             // 胡牌最小番 0
    public $top_fan;             // 封顶 1024

    public $is_circle;			//按圈打: (0:不按圈打, 1:一圈, 2:两圈, 4:四圈)
    public $zimo_rule;			//自摸规则:(0:自摸加底, 1:自摸加番)
    public $dian_gang_hua;		//点杠花: (0:点炮, 1:自摸)
    public $is_change_3;			//换三张:(0:否, 1:是)
    public $is_yaojiu_jiangdui;		//幺九将对:(0:否, 1:是)

    public $is_menqing_zhongzhang;		//门清中张:(0:否, 1:是)
    public $is_tiandi_hu;		//天地胡:(0:否, 1: 是)
    //public $is_feng;             // 带风牌 0 否 1 是
    public $is_yipao_duoxiang;   // 是否一炮多响 0 否 1 是
    //public $is_chipai;        //带吃牌: (0:否,1:是)

    //public $is_genzhuang;  //跟庄 0 否 1 是
    public $is_qingyise_fan; //清一色加番 0 否 1 是
    //public $is_ziyise_fan; //字一色加番 0 否 1 是
    //public $is_yitiaolong_fan;   //一条龙加番  0 否 1 是
    public $is_ganghua_fan;  //杠上花加番 0 否 1 是

    public $is_qidui_fan; //七对加番  0 否 1 是
    public $is_pengpenghu_fan;  //碰碰胡加分 0 否 1 是
    public $pay_type; //付费方式: (0:房主付费, 1:AA付费, 2:大赢家付费)
    public $is_score_field;    //是否积分场: (0:普通场,1:积分场)
    public $score;	//是否捉五魁 0 否 1 积分场底分: (100, 500, 2500, 8000, 15000, 40000, 100000, 0)

    public $cancle_clocker;     //是否有倒计时 0 否 1 是
    //public $allow_louhu;        //是否允许漏胡 0 否 1 是
    //public $qg_is_zimo;         //抢杠算自摸 0 否 1 是

    public function __construct()
    {
        $this->clear();
    }

    public function clear()
    {
        $this->game_type = 0;
        $this->player_count = 4;
        $this->set_num = 8;
        $this->min_fan = 0;
        $this->top_fan = 1024;

        $this->is_circle = 0;
        $this->zimo_rule = 1;
        $this->dian_gang_hua = 1;
        $this->is_change_3 = 1;
        $this->is_yaojiu_jiangdui = 1;

        $this->is_menqing_zhongzhang = 1;
        $this->is_tiandi_hu = 1;
        //$this->is_feng = 0;
        $this->is_yipao_duoxiang = 1;
        //$this->is_chipai = 0;

        //$this->is_genzhuang = 0;
        $this->is_qingyise_fan = 1;
        //$this->is_ziyise_fan = 0;
        //$this->is_yitiaolong_fan = 0;
        $this->is_ganghua_fan = 1;

        $this->is_qidui_fan = 1;
        $this->is_pengpenghu_fan = 1;
        $this->pay_type = 1;
        $this->is_score_field = 0;
        $this->score = 100;

        $this->cancle_clocker = 1;
        //$this->allow_louhu = 0;
        //$this->qg_is_zimo = 0;
    }
}

//－－－－－－－－－－－－河北黄骅规则－－－－－－－－－－－－－－－－－
class RuleHuangHua
{
    public $game_type;	            //游戏类型
    public $player_count;	        //玩家人数: (2:2人, 3:3人, 4:4人)
    public $set_num;	            //房间局数: (4:4局2钻, 8:8局3钻, 16:16局5钻)
    public $top_fan;	            //封顶番数: (0:不封顶, 255:封顶255番)
    public $is_circle;	            //按圈打: (0:不按圈打, 1:一圈, 2:两圈, 4:四圈)

    public $is_feng;	            //带风牌: (0:否,1:是)
    public $min_fan;	            //胡牌最小番
    public $is_yipao_duoxiang;	    //一炮多响: (0:否,1:是)
    public $is_chi;	                //带吃牌: (0:否,1:是)
    public $is_genzhuang;	        //带跟庄: (0:否,1:是)

    public $is_paozi;	            //下跑儿: (0:否,1:是)
    public $is_zhuang_fan;	        //带庄闲: (0:否,1:是)
    public $is_qingyise_fan;	    //清一色: (0:否,1:是)
    public $is_hunyise_fan;	        //混一色: (0:否,1:是)
    public $is_yitiaolong_fan;	    //一条龙: (0:否,1:是)

    public $is_ganghua_fan;     	//杠上开花: (0:否,1:是)
    public $is_bianzhang_fan;	    //边张: (0:否,1:是)
    public $is_kanzhang_fan;	    //坎张: (0:否,1:是)
    public $is_zhongfabai_fan;	    //中发白: (0:否,1:是)
    public $is_yibiangao_fan;	    //一边高: (0:否,1:是)

    public $is_menqing_fan;	        //门清: (0:否,1:是)
    public $is_erwubajiang_fan;	    //258将: (0:否,1:是)
    public $is_quemen_fan;	        //缺门: (0:否,1:是)
    public $is_gujiang_fan;	        //孤将: (0:否,1:是)
    public $is_dandiao_fan;	        //单吊: (0:否,1:是)
    public $is_duanyaojiu_fan;	    //断幺九: (0:否,1:是)

    public $is_siguiyi_fan;	        //四归一: (0:否,1:是)
    public $is_gulianliu_fan;	    //孤连六: (0:否,1:是)
    public $is_daxiaowu_fan;	    //大小五: (0:否,1:是)
    public $is_goushan_fan;	        //够扇: (0:否,1:是)
    public $is_tianhu_fan;	        //天胡: (0:否,1:是)

    public $is_dihu_fan;	        //地胡: (0:否,1:是)
    public $is_za;	                //带三砸: (0:否,1:是)
    public $is_bian_zuan;	        //带边钻: (0:否,1:是)
    public $is_zhongfabai_shun;	    //中发白成顺: (0:否,1:是)
    public $is_wukui;	            //捉五魁: (0:否,1:是)

    public $is_diaowuwan;	        //吊五万: (0:否,1:是)
    public $is_ziyise_fan;	        //字一色: (0:否,1:是)
    public $is_qidui_fan;	        //七对: (0:否,1:是)
    public $is_shisanbukao_fan;	    //十三不靠: (0:否,1:是)
    public $is_pengpenghu_fan;	    //碰碰胡: (0:否,1:是)

    public $is_wangang_1_lose;	    //弯杠点碰者1人给分: (0:否,1:是)
    public $allow_louhu;	        //是否允许过手胡: (0:否,1:是)
    public $qg_is_zimo;	            //抢杠胡是否算自模: (0:否,1:是)
    public $cancle_clocker;	        //解散带倒计时功能: (0:否,1:是)
    public $pay_type;	            //付费方式: (0:房主付费, 1:AA付费, 2:大赢家付费)

    public $is_dianpao_bao;	        //点炮选项(发胡：0, 大包：1)
    public $is_score_field;	        //是否积分场: (0:普通场,1:积分场)
    public $score;	                //积分场底分: (100, 500, 2500, 8000, 15000, 40000, 100000, 0)

    public function clear()
    {
        $this->game_type = 263;
        $this->player_count = 4;
        $this->set_num = 8;
        $this->top_fan = 255;
        $this->is_circle = 4;

        $this->is_feng = 1;
        $this->min_fan = 0;
        $this->is_yipao_duoxiang = 0;
        $this->is_chi = 0;
        $this->is_genzhuang = 0;

        $this->is_paozi = 0;
        $this->is_zhuang_fan = 0;
        $this->is_qingyise_fan = 1;
        $this->is_hunyise_fan = 1;
        $this->is_yitiaolong_fan = 1;

        $this->is_ganghua_fan = 1;
        $this->is_bianzhang_fan = 1;
        $this->is_kanzhang_fan = 1;
        $this->is_zhongfabai_fan = 1;
        $this->is_yibiangao_fan = 1;

        $this->is_menqing_fan = 1;
        $this->is_erwubajiang_fan = 1;
        $this->is_quemen_fan = 1;
        $this->is_gujiang_fan = 1;
        $this->is_dandiao_fan = 1;
        $this->is_duanyaojiu_fan = 1;

        $this->is_siguiyi_fan = 1;
        $this->is_gulianliu_fan = 1;
        $this->is_daxiaowu_fan = 1;
        $this->is_goushan_fan = 1;
        $this->is_tianhu_fan = 1;

        $this->is_dihu_fan = 1;
        $this->is_za = 1;
        $this->is_bian_zuan = 1;
        $this->is_zhongfabai_shun = 1;
        $this->is_wukui = 0;

        $this->is_diaowuwan = 0;
        $this->is_ziyise_fan = 0;
        $this->is_qidui_fan = 1;
        $this->is_shisanbukao_fan = 1;
        $this->is_pengpenghu_fan = 0;

        $this->is_wangang_1_lose = 0;
        $this->allow_louhu = 0;
        $this->qg_is_zimo = 0;
        $this->cancle_clocker = 1;
        $this->pay_type = 1;

        $this->is_dianpao_bao = 0;
        $this->is_score_field = 0;
        $this->score = 0;
    }

    public function __construct()
    {
        $this->clear();
    }
}

//－－－－－－－－－－－－河北献县规则－－－－－－－－－－－－－－－－－
class RuleXianXian
{
    public $game_type;	//游戏类型
    public $player_count;	//玩家人数: (2:2人, 3:3人, 4:4人)
    public $set_num;	//房间局数: (4:4局2钻, 8:8局3钻, 16:16局5钻)
    public $min_fan;	//胡牌最小番
    public $top_fan;	//封顶番数: (0:不封顶, 255:封顶255番)

    public $is_circle;	//按圈打: (0:不按圈打, 1:一圈, 2:两圈, 4:四圈)
    public $is_dianpaohu; //带点炮胡: (0:否,1:是)
    public $is_feng;	//带风牌: (0:否,1:是)
    public $is_yipao_duoxiang;	//一炮多响: (0:否,1:是)
    public $is_chi;	//带吃牌: (0:否,1:是)
    public $is_yitiaolong_fan;	//一条龙: (0:否,1:是)

    public $is_ganghua_fan;	//杠上开花: (0:否,1:是)
    public $is_qidui_fan;	//七对: (0:否,1:是)
    public $is_wangang_1_lose;	//弯杠点碰者1人给分: (0:否,1:是)
    public $is_dianpao_bao;	//点炮选项(发胡：0, 大包：1)
    public $pay_type;	//付费方式: (0:房主付费, 1:AA付费, 2:大赢家付费)

    public $cancle_clocker;	//解散带倒计时功能: (0:否,1:是)
    public $is_menqing_fan;	//门清: (0:否,1:是)
    public $is_biankadiao;	//边卡吊: (0:否,1:是)
    public $is_suhu;	//素胡: (0:否,1:是)
    public $is_fanhun;	//带翻混: (0:否,1:是)
    public $qg_is_zimo;  //抢杠算自摸: (0:否,1:是)

    public $is_score_field;	//是否积分场: (0:普通场,1:积分场)
    public $score;	//积分场底分: (100, 500, 2500, 8000, 15000, 40000, 100000, 0)

    public function clear()
    {
        $this->game_type = 264;
        $this->player_count = 4;
        $this->set_num = 8;
        $this->min_fan = 0;
        $this->top_fan = 255;

        $this->is_circle = 0;
        $this->is_dianpaohu = 1;
        $this->is_feng = 1;
        $this->is_yipao_duoxiang = 0;
        $this->is_chi = 0;
        $this->is_yitiaolong_fan = 1;

        $this->is_ganghua_fan = 1;
        $this->is_qidui_fan = 1;
        $this->is_wangang_1_lose = 0;
        $this->is_dianpao_bao = 0;
        $this->pay_type = 1;

        $this->cancle_clocker = 1;
        $this->is_menqing_fan = 1;
        $this->is_biankadiao = 1;
        $this->is_suhu = 1;
        $this->is_fanhun = 1;

        $this->is_score_field = 0;
        $this->score = 100;
    }

    public function __construct()
    {
        $this->clear();
    }
}

//－－－－－－－－－－－－斗地主规则 －－－－－－－－－－－－－－－－－
class RuleLandLord
{
    public $game_type;
    public $player_count;        // 玩家人数 2 3 4
    public $set_num;             //房间局数: (6:6局2钻, 12:12局3钻, 24:24局6钻)
    public $is_show;           //是否明牌：（0：不明牌，1：发牌后明牌，2：发牌前明牌）
    public $is_double;             //是否加倍：（0：不加倍，1：加倍）
    
    public $top_fan;             //封顶番数: (0:不封顶, N:封顶N)
    public $min_fan;            //胡牌最小番
    public $is_fanhun;          //翻混儿: (0:否,1:是)
    public $pay_type;       // 付费方式 0 房主付费 1 AA付费 2 大赢家付费
    public $cancle_clocker;
    
    //积分新增
	public $is_score_field;    //0:普通场,1:积分场
	public $score;             //积分登记,0:0积分,100:100积分, 500:500积分场                          

    public function __construct()
    {
        $this->clear();
    }
    public function clear()
    {
        $this->game_type = 0;
        $this->player_count = 3;
        $this->set_num = 12;
        $this->is_show = 1;
        $this->is_double = 1;
        
        $this->top_fan = 0;
        $this->min_fan = 0;
        $this->is_fanhun = 0;
        $this->pay_type = 0;
        $this->cancle_clocker = 1;

        //积分新增
        $this->is_score_field = 0;
        $this->score = 0;
    }
}

//－－－－－－－－－－－－打红5规则 －－－－－－－－－－－－－－－－－
class RuleDaHong5
{
    public $game_type;
    public $player_count;       // 玩家人数 2 3 4
    public $set_num;            //房间局数: (6:6局2钻, 12:12局3钻, 24:24局6钻)
    public $top_fan;            //封顶番数: (0:不封顶, N:封顶N)
    public $min_fan;            //胡牌最小番
    public $pay_type;       	// 付费方式 0 房主付费 1 AA付费 2 大赢家付费

    //积分新增
	public $is_score_field;    //0:普通场,1:积分场
	public $score;             //积分登记,0:0积分,100:100积分, 500:500积分场    
	                           
    public function __construct()
    {
        $this->clear();
    }
    public function clear()
    {
        $this->game_type = 0;
        $this->player_count = 4;
        $this->set_num = 12;
        $this->top_fan = 0;
        $this->min_fan = 0;
        $this->pay_type = 0;

        //积分新增
        $this->is_score_field = 0;
        $this->score = 0;
    }
}

class RuleRunFast
{
    public $game_type;	//游戏类型
    public $player_count;	//玩家人数: (3:3人)
    public $set_num;	//房间局数: (6:6局2钻, 12:12局3钻, 24:24局6钻)
    public $min_fan;	//胡牌最小番
    public $top_fan;	//封顶番数: (0:不封顶, 255:封顶255番)

    public $pay_type;	//付费方式: (0 房主付费 1 AA付费 2 大赢家付费 3 公会房)
    public $is_score_field;	//是否积分场: (0:普通场,1:积分场)
    public $spades_3;	//是否黑桃3必出: (0:否,1:是)
    public $must_out_card;	//是否必须管: (0:否,1:是)
    public $card_num;	//打法: (0:16张,1:15张)

    public function clear()
    {
        $this->game_type = 351;
        $this->player_count = 3;
        $this->set_num = 12;
        $this->min_fan = 0;
        $this->top_fan = 255;

        $this->pay_type = 0;
        $this->is_score_field = 0;
        $this->spades_3 = 0;
        $this->must_out_card = 0;
        $this->card_num = 0;
    }

    public function __construct()
    {
        $this->clear();
    }
}
//－－－－－－－－－－－－河北盐山规则－－－－－－－－－－－－－－－－－
class RuleYanshan
{
    public $game_type;
    public $player_count;           // 玩家人数 2 3 4
    public $set_num;                // 房间局数 4 8 16
    public $min_fan;                // 胡牌最小番 0
    public $top_fan;                // 封顶 255

    public $is_circle;			    //是否按圈打 0 否 1 是
    public $is_feng;                // 带风牌 0 否 1 是
    public $is_yipao_duoxiang;      // 是否一炮多响 0 否 1 是
    public $is_chipai;              // 七对加番  0 否 1 是
    public $is_genzhuang;           //跟庄 0 否 1 是
    public $is_paozi;               //是否下炮子 0 否 1 是
    public $is_zhuang_fan;          //是否带庄闲 0 否 1 是

    public $is_qingyise_fan;        //清一色加番 0 否 1 是
    public $is_ziyise_fan;          //字一色加番 0 否 1 是
    public $is_yitiaolong_fan;      //一条龙加番  0 否 1 是
    public $is_ganghua_fan;         //杠上花加番 0 否 1 是
    public $is_qidui_fan;           //七对加番  0 否 1 是
    public $is_pengpenghu_fan;      //碰碰胡加分 0 否 1 是

    public $is_wangang_1_lose;      //弯杠点碰者1人给分  0 否 1 是
    public $is_dianpao_bao;         //是否点炮大包 0 点炮三家出（发胡） 1 点炮大包庄（点炮）
    public $is_wukui;	            //是否捉五魁 0 否 1 是
    public $is_diaowuwan;	        //是否吊五万 0 否 1 是
    public $is_zhongfabai_shun;	    //中发白做顺子
    public $is_bian_zuan;	        //带边钻 0 否 1 是
    public $is_za;	                //带3砸 0 否 1 是
    public $pay_type;               //付费方式

    public $cancle_clocker;         //是否带倒计时功能 0 否 1 是
    public $allow_louhu;            //是否允许漏胡（过手胡） 0 否 1 是
    public $qg_is_zimo;             //是否抢杠胡算自摸 0 否 1 是

    //积分新增
    public $is_score_field;         //0:普通场,1:积分场
    public $score;                  //积分登记,0:0积分,100:100积分, 500:500积分场

    public function __construct()
    {
        $this->clear();
    }
    public function clear()
    {
        $this->game_type = 0;
        $this->player_count = 4;
        $this->set_num = 8;
        $this->min_fan = 0;
        $this->top_fan = 0;

        $this->is_circle = 1;
        $this->is_feng = 1;
        $this->is_yipao_duoxiang = 0;
        $this->is_chipai = 0;
        $this->is_genzhuang = 0;
        $this->is_paozi = 0;
        $this->is_zhuang_fan = 0;

        $this->is_qingyise_fan = 1;
        $this->is_ziyise_fan = 1;
        $this->is_yitiaolong_fan = 1;
        $this->is_qidui_fan = 1;
        $this->is_ganghua_fan = 1;
        $this->is_pengpenghu_fan = 1;

        $this->is_wangang_1_lose = 0;
        $this->is_dianpao_bao = 0;
        $this->is_wukui = 1;
        $this->is_diaowuwan = 1;
        $this->is_zhongfabai_shun = 0;
        $this->is_bian_zuan = 1;
        $this->is_za = 0;
        $this->pay_type = 0;
        $this->cancle_clocker = 1;
        $this->allow_louhu = 1;
        $this->qg_is_zimo = 0;

        //积分新增
        $this->is_score_field = 0;
        $this->score = 0;
    }
}

class Room
{
	public static $room_arr = array();
	public static $get_conf = array();
	public static $key_arr = array();
	public static $set_num_arr = array(8=>1, 16=>2);
	public static $room_timeout = 86400;
	public static $game_type = array(
        '242' => 'gf\inc\GameChengDeTuiDaoHu'
		,'421' => 'gf\inc\GameWenAn'
        , '261' => 'gf\inc\GameCangZhou'
        , '262' => 'gf\inc\GameXueZhan'
        , '263' => 'gf\inc\GameHuangHua'
        , '264' => 'gf\inc\GameXianxian'
        , '331' => 'gf\inc\GameLandLord'
        , '341' => 'gf\inc\GameDaHong5'
        , '351' => 'gf\inc\GameRunFast'
        , '265' => 'gf\inc\GameYanshan'
    );
    
	public static $json_re_key_arr = array(
   "\"aa\"",
   "\"ab\"",
   "\"ac\"",
   "\"ad\"",
   "\"ae\"",
   "\"af\"",
   "\"ag\"",
   "\"ah\"",
   "\"ai\"",
   "\"aj\"",
   "\"ak\"",
   "\"al\"",
   "\"am\"",
   "\"an\"",
   "\"ao\"",
   "\"ap\"",
   "\"aq\"",
   "\"ar\"",
   "\"as\"",
   "\"at\"",
   "\"au\"",
   "\"av\"",
   "\"aw\"",
   "\"ax\"",
   "\"ay\"",
   "\"az\"",
   "\"ba\"",
   "\"bb\"",
   "\"bc\"",
   "\"bd\"",
   "\"be\"",
   "\"bf\"",
   "\"bg\"",
   "\"bh\"",
   "\"bi\"",
   "\"bj\"",
   "\"bk\"",
   "\"bl\"",
   "\"bm\"",
   "\"bn\"",
   "\"bo\"",
   "\"bp\"",
   "\"bq\"",
   "\"br\"",
   "\"bs\"",
   "\"bt\"",
   "\"bu\"",
   "\"bv\"",
   "\"bw\"",
   "\"bx\"",
   "\"by\"",
   "\"bz\"",
   "\"ca\"",
   "\"cb\"",
   "\"cc\"",
   "\"cd\"",
   "\"ce\"",
   "\"cf\"",
   "\"cg\"",
   "\"ch\"",
   "\"ci\"",
   "\"cj\"",
   "\"ck\"",
   "\"cl\"",
   "\"cm\"",
   "\"cn\"",
   "\"co\"",
   "\"cp\"",
   "\"cq\"",
   "\"cr\"",
   "\"cs\"",
   "\"ct\"",
   "\"cu\"",
   "\"cv\"",
   "\"cw\"",
   "\"cx\"",
   "\"cy\"",
   "\"cz\"",
   "\"da\"",
   "\"db\"",
   "\"dc\"",
   "\"dd\"",
   "\"de\"",
   "\"df\"",
   "\"dg\"",
   "\"dh\"",
   "\"di\"",
   "\"dj\"",
   "\"dk\"",
   "\"dl\"",
   "\"dm\"",
   "\"dn\"",
   "\"do\"",
   "\"dp\"",
   "\"dq\"",
   "\"dr\"",
   "\"ds\"",
   "\"dt\"",
   "\"du\"",
   "\"dv\"",
   "\"dw\"",
   "\"dx\"",
   "\"dy\"",
   "\"dz\"",
   "\"ea\"",
   "\"eb\"",
   "\"ec\"",
   "\"ed\"",
   "\"ee\""	
	);
	
	public static $json_key_arr = array(
   "\"act\"",
   "\"rid\"",
   "\"uid\"",
   "\"rule\"",
   "\"set_num\"",
   "\"player_count\"",
   "\"min_fan\"",
   "\"top_fan\"",
   "\"zimo_rule\"",
   "\"dian_gang_hua\"",
   "\"is_change_3\"",
   "\"is_yaojiu_jiangdui\"",
   "\"is_menqing_zhongzhang\"",
   "\"is_tiandi_hu\"",
   "\"game_type\"",
   "\"info\"",
   "\"code\"",
   "\"desc\"",
   "\"text\"",
   "\"is_room_owner\"",
   "\"uname\"",
   "\"head_pic\"",
   "\"sex\"",
   "\"data\"",
   "\"flee_time\"",
   "\"scope\"",
   "\"chair\"",
   "\"m_room_players\"",
   "\"fd\"",
   "\"ip\"",
   "\"m_ready\"",
   "\"base_player_count\"",
   "\"m_nSetCount\"",
   "\"m_wTotalScore\"",
   "\"n_score\"",
   "\"n_zimo\"",
   "\"n_jiepao\"",
   "\"n_dianpao\"",
   "\"n_angang\"",
   "\"n_zhigang_wangang\"",
   "\"n_huazhu\"",
   "\"n_dajiao\"",
   "\"m_rule\"",
   "\"m_sDingQue\"",
   "\"recv\"",
   "\"card_type\"",
   "\"m_dice\"",
   "\"m_Score\"",
   "\"score\"",
   "\"win_count\"",
   "\"lose_count\"",
   "\"draw_count\"",
   "\"flee_count\"",
   "\"set_count\"",
   "\"is_cancle\"",
   "\"m_cancle\"",
   "\"m_cancle_first\"",
   "\"m_nChairBanker\"",
   "\"m_sysPhase\"",
   "\"m_nCountAllot\"",
   "\"m_bHaveGang\"",
   "\"m_sQiangGang\"",
   "\"mark\"",
   "\"card\"",
   "\"m_sGangPao\"",
   "\"type\"",
   "\"m_bTianRenHu\"",
   "\"m_nDiHu\"",
   "\"m_bChairHu\"",
   "\"m_bChairHu_order\"",
   "\"m_HuCurt\"",
   "\"state\"",
   "\"jiang_card\"",
   "\"count\"",
   "\"method\"",
   "\"gain_chair\"",
   "\"card_state\"",
   "\"m_sPlayer_len\"",
   "\"m_sPlayer_state\"",
   "\"m_sPlayer_card_taken_now\"",
   "\"m_nEndReason\"",
   "\"m_nNumCheat\"",
   "\"m_nNumTableCards\"",
   "\"m_nTableCards\"",
   "\"m_sStandCard\"",
   "\"num\"",
   "\"who_give_me\"",
   "\"first_card\"",
   "\"m_sOutedCard\"",
   "\"m_sPlayer\"",
   "\"len\"",
   "\"card_taken_now\"",
   "\"seen_out_card\"",
   "\"m_hu_desc\"",
   "\"m_end_time\"",
   "\"m_chairCurrentPlayer\"",
   "\"m_huan_3_type\"",
   "\"m_huan_3_arr\"",
   "\"card_arr\"",
   "\"get_card_arr\"",
   "\"m_only_out_card\"",
   "\"huan_card\"",
   "\"que_card_type\"",
   "\"m_bChooseBuf\"",
   "\"m_nHuGiveUp\"",
   "\"cmd\"",
   "\"gang_card\"",
   "\"is_14\"",
   "\"out_card\""
	);

	public static function sub_encryptMD5($content)
	{
		$content = $content.Config::RPC_KEY;
		$content = md5($content);
		if( strlen($content) > 10 )
		{
			$content = substr($content, 0, 10);
		}
		return $content;
	}
	
	public static function check_encrypt($rid_key, $rid)
	{
		$key = substr($rid_key, 0, 10);
		$rid_str = substr($rid_key, 10, 6);
		$check_time = substr($rid_key, 16);
		if(intval($rid_str) != $rid || time() - intval($check_time) > 7200)
		{
			return false;
		}
		if($key != self::sub_encryptMD5($rid_str.$check_time))
		{
			return false;
		}
		return true;
	}
	
	public static function tcp_encode($buffer_obj, $force=true)
    {
    	$buffer = json_encode($buffer_obj);
    	
    	if($force)
    	{
			$replace_buf = str_replace(self::$json_key_arr, self::$json_re_key_arr, $buffer);
    	}
    	else 
    	{
    		$replace_buf = $buffer;
    	}
    	
        $tmp_buf = pack('N', strlen($replace_buf)) . $replace_buf;
        if(!empty(Config::DEBUG))
        {
    	    var_dump($buffer);
        }
        return $tmp_buf;
    }
    
    public static function tcp_decode($buffer)
    {
		if(!empty(Config::DEBUG))
        {  	
   			var_dump($buffer);
        }
        $data = json_decode(substr($buffer, 4), true);
    	
        return $data;
    }
	
	public static function c_bind($serv, $fd, $params)
	{
		$return_send = array("act"=>"s_result","info"=>__FUNCTION__ , "code"=>0, "desc"=>__LINE__.__CLASS__);
		do {
			if( empty($params['rid']) || empty($params['rid_key']))
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}
			if(!self::check_encrypt($params['rid_key'], $params['rid']))
			{
				$serv->close($fd, true);
				return ;
			}
			$s_result = $serv->bind($fd, $params['rid']);
            $fd_info = $serv->connection_info($fd);

			if(empty($fd_info['uid']) || $fd_info['uid'] != $params['rid'])
			{
				$return_send['code'] = 2; $return_send['text'] = '绑定频道失败'; $return_send['desc'] = __LINE__.__CLASS__; break;
			}

		}while(false);

		$serv->send($fd, Room::tcp_encode($return_send,false));
		return $return_send['code'];
	}

	public static function c_msg($serv, $fd, $params, $act)
	{
		$return_send = array("act"=>"s_result","info"=>$act , "code"=>0, "desc"=>__LINE__.__CLASS__);

		do {
			if( empty($params['rid']))
			{
				$return_send['code'] = 1; $return_send['text'] = '参数错误'; $return_send['desc'] = __LINE__.__CLASS__;
				$serv->send($fd, Room::tcp_encode($return_send, false));
				break;
			}
			
			if('c_open_room' == $act)
			{
				unset(self::$room_arr[$params['rid']]);
                //开房时取得getconf
                BaseFunction::web_curl(array('mod'=>'Business', 'act'=>'get_conf', 'platform'=>'gfplay'),'get_conf','get_conf');
			}

			if(empty(self::$room_arr[$params['rid']]))
			{
				if('c_open_room' == $act && array_key_exists($params['game_type'],self::$game_type))
				{
					self::$room_arr[$params['rid']] = new self::$game_type[$params['game_type']]($serv);
				}
			}
			if(empty(self::$room_arr[$params['rid']]))
			{
				$return_send['code'] = 1; $return_send['text'] = '没有此房间'; $return_send['desc'] = __LINE__.__CLASS__;
				$serv->send($fd, Room::tcp_encode($return_send, false));
				break;
			}
			if(!empty($params['uid']) && !empty(self::$room_arr[$params['rid']]->m_room_players) && is_array(self::$room_arr[$params['rid']]->m_room_players))
			{
				foreach (self::$room_arr[$params['rid']]->m_room_players as $key => $play_item)
				{
					if($params['uid'] == $play_item['uid'] && 'c_open_room' != $act && 'c_get_room' != $act )
					{
						self::$room_arr[$params['rid']]->m_room_players[$key]['fd'] = $fd;
					}
				}
			}
			
			$return_send['code'] = self::$room_arr[$params['rid']]->$act($fd, $params);
		}while(false);

		return $return_send['code'];
	}
}


