<?php
session_start();
$userid = $_SESSION['userid'];
if(!$_SESSION['username']) {
    header("location:".'/imcase/logout.php');
}
list($var1, $var2) = explode('st', $_GET['user']);
$touserid = $var2;
?>
<!DOCTYPE html>
<html>
<head>
    <title>websocket demo</title>
    <meta charset="UTF-8">
    <style>
        .main {
            text-align: center;
            margin-top: 8%;
        }

        .step2 {
            display: block;
            margin-top: 20px;
        }

        .message {
            display: block;
            width: 268px;
            text-align: left;
            margin: 0 auto;
            height: 297px;
            overflow: auto;
            padding-left: 10px;
            padding-right: 10px;
            background: blanchedalmond;
        }
        .crolls{ display: none;}
        .left{ float: left; clear: both}
        .right{ float: right;clear: both}
    </style>
</head>
<body>
<div class='main'>
    <div class="step2">
        欢迎你 ：<?php echo $_SESSION['username']; ?> 来测试  <a href="logout.php">退出</a>
        <p>
            当前你与<?php echo $_GET['user']; ?>聊天中<p>
            (请换个客户端(浏览器，微信 手机浏览器) 登陆账号<?php echo $_GET['user']; ?> 进行测试)<p>
            还可以继续和 <?php
            session_start();
            $usernames = array('test1', 'test2', 'test3', 'test4', 'test5');
            foreach ($usernames as $value) {
                if($_SESSION['username'] != $value and $value != 'test'.$touserid) {
                    echo '<a target="_blank" href="imClient.php?user='.$value.'">'.$value.'</a>&nbsp';
                }
            }
            ?> 聊天 （一个账号可以同时多客户端 对聊 多用户多客户端）
    </div>
    <div class="message">

    </div>

    <div class='step2' data-id="1000">
        <form class="sendmsg">
            <input type="text" class="content">
            <input type="submit" value="发送"/>
        </form>
    </div>
    <div class="crolls">300</div>
</div>
</body>
</html>
<script src='layer/jquery-1.9.1.min.js'></script>
<script src="layer/layer.js"></script>
<script type="text/javascript">
    var websocket = null;
    if (window.WebSocket) {
        websocket = new WebSocket("ws://47.89.23.36:9504/<?php echo $userid.'@'.$touserid; ?>");
    } else {
        alert("您的浏览器不支持WebSocket,请更换浏览器再试");
    }
    websocket.onopen = function (event) {
        //websocket.send("开始聊天吧..");
    };
    //监听服务器推送
    websocket.onmessage = function (event) {
        append_text_msg(event.data);
    };
    //监听连接关闭
    websocket.onclose = function (event) {
        append_text_msg('服务器拒绝');
    };
    //监听服务器连接错误信息
    websocket.onerror = function (event, e) {
        append_text_msg('错误' + e.data);
    };

    //监听窗口关闭事件，当窗口关闭时，主动去关闭websocket连接，防止连接还没断开就关闭窗口，server端会抛异常。
    window.onbeforeunload = function () {
        websocket.close();
        layer.msg('您已关闭当前聊天', {icon:2});
    };

    //发送信息
    $('.sendmsg').submit(function () {
        var text = $('.content').val(); //获取数据
        if(!text) {
            layer.msg('请填写！', {icon:2});return false;
        }
        $('.content').val('');
        websocket.send(text);
        return false;
    });
    //给message框追加信息
    function append_text_msg(str) {
        var message = $('.message');
        message.append(str);
		//下拉一次消息框滚动条
        var stepleng = $('.crolls').html();
        var movescroll = parseInt(stepleng) + 100;
        $('.crolls').html(movescroll);
        message.scrollTop(movescroll); 
    }
</script>
