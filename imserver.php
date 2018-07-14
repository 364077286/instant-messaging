<?php
//im通信
$ws = new swoole_websocket_server('0.0.0.0', 9504);
//on函数 open mwssage close
//$open
//设置异步 进程工作数
$ws->set(array(
    'daemonize' => true,
    'worker_num' => 10
));

//把fd 和用户id 绑定在一起 保存redis hash
$GLOBALS['redis'] = new Redis();
//连接
$GLOBALS['redis']->connect('127.0.0.1', 6379);
$GLOBALS['redis']->auth('huangliuyuan33');
$GLOBALS['redis']->select('1'); //选择1库

$ws->on('open', function($ws, $request){
    //var_dump($request);
    echo "新用户 $request->fd 加入。\n";
    $userid_touserid = str_replace('/', '', $request->server['path_info']);
    //发送send_uid  接受acc_ouid
    list($GLOBALS['send_uid'], $GLOBALS['acc_ouid']) = explode('@', $userid_touserid);

    $GLOBALS['fd'][$request->fd]['id'] = $request->fd; //设置发送者fd
    $GLOBALS['fd'][$request->fd]['name'] = "test".$GLOBALS['send_uid']; //设置用户名
    //  保存到 redis hash
    //  假如test1(两个客户端 userid(1) fd(3,6)) 发给 test5 (两个客户端 userid(5) fd(4,7))
    //  websct_uid:1 3 => 5 , 6 => 5 ........
    //  websct_uid:5 4 => 1 , 7 => 1 ........
    //  key:userid field(发送者客户端fd) value(接受者userid)
    $GLOBALS['redis']->hset('websct_uid:'.$GLOBALS['send_uid'], $request->fd, $GLOBALS['acc_ouid']);
});
//message
$ws->on('message', function ($ws, $request){
   $msg = $GLOBALS['fd'][$request->fd]['name'].":".$request->data."\n";
   if(strstr($request->data, "#name#")) {
       $GLOBALS['fd'][$request->fd]['name'] = str_replace("#name#",'',$request->data);
   }else { //进行用户发送信息
//       //发送每一个在线客户端
//       foreach ($GLOBALS['fd'] as $i) {
//           $ws->push($i['id'], $msg.$GLOBALS['send_uid']);
//       }


       //接受信息的客户端
       $accclient_fd = $GLOBALS['redis']->hGetAll("websct_uid:".$GLOBALS['acc_ouid']);
       //发给自己的客户端
       $sendclient_fd = $GLOBALS['redis']->hGetAll("websct_uid:".$GLOBALS['send_uid']);
       foreach ($accclient_fd as $k1 => $v1) {
           if($v1 == $GLOBALS['send_uid']) {
               $ws->push($k1, '<p class="left">'.$msg.'<p>');
           }
       }
       //发给自己一份 （也有可能自己也开了多个客户端）
       foreach ($sendclient_fd as $k2 => $v2) {
           if($v2 == $GLOBALS['acc_ouid']) {
               $ws->push($k2, '<p class="right">'.$msg.'<p>');
           }
       }
       //写入数据库信息
   }
});
//close
$ws->on('close', function ($ws, $request) {
    echo "客户端-{$request} 断开连接\n";
    //从redis hash 删掉客户端  fd
    $GLOBALS['redis']->hdel('websct_uid:'.$GLOBALS['send_uid'], $request);
});
$ws->start();
