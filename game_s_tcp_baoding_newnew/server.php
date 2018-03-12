<?php
/**
 * @author xuqiang76@163.com
 * @final 20161113
 */
namespace gf;

use gf\conf\Config;
use gf\inc\Room;
use gf\inc\ConstConfig;

class Head
{
	protected static $_autoload_root_path = '';
	public static function init_tcp()
	{
		$serv = new \swoole_server("0.0.0.0", Config::GAME_PORT);

		$serv->set(array(
		'reactor_num' => 4, //reactor thread num
		'worker_num' => 16,    //worker process num
		'backlog' => 128,   //listen backlog
		'max_request' => 0,
		'dispatch_mode' => 5,
		'max_conn' => 10000,
		'daemonize'=> 1,
		'log_file' => Config::LOG_FILE,
		//'open_eof_check' => true, //打开EOF检测
		//'package_eof' => "\r\n", //设置EOF
		'open_length_check' => true, //打开包长检测特性。包长检测提供了固定包头+包体这种格式协议的解析。启用后，可以保证Worker进程onReceive每次都会收到一个完整的数据包。
		'package_max_length' => 81920,
		'package_length_type' => 'N', //see php pack()
		'package_length_offset' => 0,
		'package_body_offset' => 4,

		'open_tcp_nodelay' => 1,
		'pipe_buffer_size' => 33554432,	//32 * 1024 *1024, //必须为数字
		'buffer_output_size' => 33554432,	//1024 *1024, //必须为数字
		'socket_buffer_size' => 33554432,	// 32 * 1024 *1024, //必须为数字
		'heartbeat_idle_time' => 11,	//一个连接如果N秒内未向服务器发送任何数据，此连接将被强制关闭
		'heartbeat_check_interval' => 5,	//表示每N秒遍历一次
		));

		//监听连接进入事件
		$serv->on('connect', function ($serv, $fd)
		{
			if(!empty(Config::DEBUG))
			{
				echo $fd."Connect.\n";
			}
		});

		//监听数据发送事件
		$serv->on('receive', function ($serv, $fd, $from_id, $data)
		{
			$fd_info = $serv->connection_info($fd);
		    
			$data_arr = Room::tcp_decode($data);

			if(!$data_arr || empty($data_arr['act']))
			{
				$serv->send($fd, Room::tcp_encode((array('act'=>'s_error', "info"=>__FILE__, 'code'=>1, 'desc'=>__LINE__))));
				return ;
			}

			if($data_arr['act'] != 'c_tiao' && $data_arr['act'] != 'c_bind' && ( empty($data_arr['rid']) || empty($fd_info['uid']) || $fd_info['uid'] != $data_arr['rid']) )
			{
				//$return_send['code'] = 2; $return_send['text'] = '绑定频道失败'; $return_send['desc'] = __LINE__; break;
				$serv->close($fd, true);
			}

			$data_arr['ip'] = $fd_info['remote_ip'];
			$act = $data_arr['act'];
			
			switch ($act)
			{
				case 'c_bind':
					Room::c_bind($serv, $fd, $data_arr);
					break;

				case 'c_join_room':
				case 'c_ready':
				case 'c_cancle_game':
				case 'c_get_game':
				case 'c_cancle_gang':

				case 'c_huan_3':
				case 'c_ding_que':
				case 'c_pao_zi':
				case 'c_zimo_hu':
				case 'c_an_gang':
				case 'c_wan_gang':
				case 'c_out_card':

				case 'c_eat':
				case 'c_peng':
				case 'c_zhigang':
				case 'c_hu':

				case 'c_cancle_choice':
				case 'c_chat':
				case 'c_tiao':
				case 'c_hu_give_up':
				case 'c_bian_zuan':
				case 'c_kou_card':

				case 'c_show':
				case 'c_double':
				case 'c_bid':
				case 'c_leave':
					Room::c_msg($serv, $fd, $data_arr, $act);
					break;

				case 'c_get_room':
				case 'c_open_room':
                case 'c_make_card':
					if (in_array($fd_info['remote_ip'], Config::WHITE_IP))
					{
						Room::c_msg($serv, $fd, $data_arr, $act);
					}
					else
					{
						echo 'remote_ip error';
					}
					break;

				default:
					if(!empty(Config::DEBUG))
					{
						echo 'default';
					}
					break;
			}

		});

		//监听连接关闭事件
		$serv->on('close', function ($serv, $fd)
		{
			if(!empty(Config::DEBUG))
			{
				echo "Close.\n";
			}
		});

		ConstConfig::get_hu_data();

		//启动服务器
		$serv->start();

	}

	public static function init()
	{
		if(Config::DEBUG)
		{
			error_reporting(7);
			error_reporting(E_ALL|E_STRICT);
			ini_set('display_errors', 'on');
		}

		date_default_timezone_set('Asia/Chongqing');

		spl_autoload_register('\gf\Head::load_by_namespace');
	}

	public static function load_by_namespace($name)
	{
		$class_path = str_replace('\\', DIRECTORY_SEPARATOR, $name);
		if (strpos($name, 'gf\\') === 0)
		{
			$class_file = __DIR__ . substr($class_path, strlen('gf')) . '.php';
		} else {
			if (self::$_autoload_root_path)
			{
				$class_file = self::$_autoload_root_path . DIRECTORY_SEPARATOR . $class_path . '.php';
			}
			if (empty($class_file) || !is_file($class_file))
			{
				$class_file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "$class_path.php";
			}
		}

		if (is_file($class_file))
		{
			require_once($class_file);
			if (class_exists($name, false) || interface_exists($name, false))
			{
				return true;
			}
		}
		return false;
	}
}

require('./conf/config.php');
Head::init();
Head::init_tcp();


