<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 聊天主逻辑
 * 主要是处理 onMessage onClose 
 */
use \GatewayWorker\Lib\Gateway;
require_once __DIR__.'/externallibraries/autoload.php';

class Events
{
   public function onMessage($client_id, $message)
   {
        // debug
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n";
        
        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }
        
        // 根据类型执行不同的业务
        switch($message_data['type'])
        {
            // 客户端回应服务端的心跳
            case 'pong':
                return;
            // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
                // 判断是否有房间号
                if(!isset($message_data['room_id']))
                {
                    throw new \Exception("\$message_data['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }

                // 把房间号昵称放到session中
                $room_id = $message_data['room_id'];
                //把预定义的字符 "<" （小于）和 ">" （大于）转换为 HTML 实体：
                $client_name = htmlspecialchars($message_data['client_name']);
                $_SESSION['room_id'] = $room_id;
                $_SESSION['client_name'] = $client_name;

                // 获取房间内所有用户数据
//                $clients_list = Gateway::getClientSessionsByGroup($room_id);
//                foreach($clients_list as $tmp_client_id=>$item)
//                {
//                    $clients_list[$tmp_client_id] = $item['client_name'];
//                }
//                $clients_list[$client_id] = $client_name;
                //将两个用户加入分组
                if(getClientCountByGroup($message_data['room_id'])<3){
                    Gateway::joinGroup($client_id, $message_data['room_id']);

                }else{
                    $tmp_warn_message=array(
                        'type'=>"warning",
                        'client_id'=>$client_id,
                        'room_id'=>$message_data['room_id'],
                        //能不能玩,不能玩就观战
                        'canNoPlay'=>false,
                    );
                    Gateway::sendToCurrentClient(json_encode($tmp_warn_message));
                }

                break;
            case 'update':
                //var_dump(\'hello,wol');
                if(!isset($_SESSION['room_id']))
                {
                    throw new \Exception("\$_SESSION['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']}");
                }
                $room_id = $_SESSION['room_id'];
                $client_name = $_SESSION['client_name'];
                $data=$message_data['update'];
                if(is_string($data)){
                    $data=(int)$data;
                }
                $cx=floor($data/100);
                $cy=$data-($cx*100);
                echo "这x:",$cx,"这是y:",$cy;
                if(getClientCountByGroup($room_id)==2)
                {
                    $new_message = array(
                        'type'=>'updata',
                        'from_client_id'=>$client_id,
                        'from_client_name' =>$client_name,
                        'to_client_id'=>$message_data['to_client_id'],
                         "data"=>$data,
                        'time'=>date('Y-m-d H:i:s'),
                    );
                    Gateway::sendToClient($message_data['to_client_id'], json_encode($new_message));
                    return;
                }
                break;
        }
   }
   /**
    * 当客户端断开连接时
    * @param integer $client_id 客户端id
    */
   public static function onClose($client_id)
   {
       // debug
       echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";
       
       // 从房间的客户端列表中删除
       if(isset($_SESSION['room_id']))
       {
           $room_id = $_SESSION['room_id'];
           $new_message = array('type'=>'logout', 'from_client_id'=>$client_id, 'from_client_name'=>$_SESSION['client_name'], 'time'=>date('Y-m-d H:i:s'));
           Gateway::sendToGroup($room_id, json_encode($new_message));
       }
   }


    public static function onWorkerStop($businessWorker)
    {
        error_log('workerman 中途停止,不是系统错误,查看逻辑错误',3,'/erro.log');
    }


  
}
