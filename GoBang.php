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
//require_once __DIR__.'/externallibraries/autoload.php';

class GoBang
{
   public static function onMessage($client_id, $message)
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
                } else{
                    $room_id = $message_data['room_id'];
                    $_SESSION['room_id'] = $room_id;
                }
                    $client_name = htmlspecialchars($message_data['client_name']);
                    $_SESSION['client_name'] = $client_name;
                    Gateway::joinGroup($client_id,$room_id);
                // 获取房间内所有用户列表
                $clients_list = Gateway::getClientSessionsByGroup($room_id);
                foreach($clients_list as $tmp_client_id=>$item)
                {
                    $clients_list[$tmp_client_id] = $item['client_name'];
                }
                $clients_list[$client_id] = $client_name;

                // 给当前用户发送用户列表
                $new_message['client_list'] = $clients_list;
                Gateway::sendToCurrentClient(json_encode($new_message));
                return;
            case 'update':
                //{"type":"updata","client_id":xxx,"client_name":"xxx","X"="cx","Y"="cy"}
                echo "数据输入判断";
                //$room_id = $_SESSION['room_id'];
                //$client_name = $_SESSION['client_name'];
                $X=$message_data['X'];
                $Y=$message_data['Y'];
                $color=$message_data['color'];
                if(is_string($X)||is_string($Y)){
                    $X=(int)$X;
                    $Y=(int)$Y;
                }

                echo "这x:",$X,"这是y:",$Y;
               // if(getClientCountByGroup($room_id)==2)
               // {
                    $new_message = array(
                        'type'=>'updata',
                        'color'=>$color,
                        'X'=>$X,
                        'Y'=>$Y
                   );
                  Gateway::sendToGroup($_SESSION["room_id"],json_encode($new_message));
                  return;
                //
               // }
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
