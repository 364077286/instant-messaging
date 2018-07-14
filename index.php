<?php
session_start();
if($_SESSION['username']) {
    header("location:".'/imcase/selectuser.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>websocket demo</title>
    <meta charset="UTF-8">
    <style>
        .main {
            text-align: center;
            margin-top: 12%;
        }

        .step2 {
            display: none;
            margin-top: 20px;
        }

        .message {
            display: none;
            width: 268px;
            text-align: left;
            margin: 0 auto;
            height: 297px;
            overflow: auto;
            padding-left: 10px;
            padding-right: 10px;
            background: blanchedalmond;
        }
    </style>
</head>
<body>
<div class='main'>
    <div class="step1">
        这是一个简易的 即时通信demo （IM 使用 php mysql swoole 的 websocket redis 实现）
        </p>
        （一个账号多客户端 可对聊 多用户多客户端）<p>
    现有的5个测试账号（同一客户端，选一个进行登录）：<p>
            test1, test2, test3, test4, test5<p>
    密码都是 ：123456
    </div>
    <div class="step1">
       <form id="loginfrom">
           账号：<input type="text" class="lg" name="username" value="test1">
           密码：<input type="text" class="lg" name="password" value="123456">
           <input type="submit" value="登录">
       </form>
    </div>
</div>
</body>
</html>
<script src='layer/jquery-1.9.1.min.js'></script>
<script src="layer/layer.js"></script>
<script>
    $('#loginfrom').submit(function () {
        var data = getFormdata('.lg');
        if(!data.username || !data.password){
            layer.msg('填写账号密码', {icon:2});
        }
        parent.layer.load();
        $.post('dologin.php',data,function (data) {
            parent.layer.closeAll('loading');
            if(data.status) {
                layer.msg(data.info, {icon:1});
                $('.lg').val('');
                window.location.href = 'selectuser.php';
            } else {
                layer.msg(data.info, {icon:2});
            }
        },'json');
        return false;
    })
</script>
