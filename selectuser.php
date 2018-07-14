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
    </style>
</head>
<body>
<div class='main'>
    <div class='step2'>
      选择一个用户进行聊天
        <?php
        session_start();
        $usernames = array('test1', 'test2', 'test3', 'test4', 'test5');
        foreach ($usernames as $value) {
            if($_SESSION['username'] != $value) {
                echo '<a href="imClient.php?user='.$value.'">'.$value.'</a>&nbsp';
            }
        }
        ?>
    </div>
</div>
</body>
</html>
