<?php
$data = $_POST;
$username = $data['username'];
$password = $data['password'];
$usernames = array('test1', 'test2', 'test3', 'test4', 'test5');
if(in_array($username, $usernames) and $password == '123456') {
    $arr['status'] = 1;
    $arr['info'] = '登陆成功';
    session_start();
    $_SESSION['username'] = $username;
    list($var1, $var2) = explode('st', $username);
    $_SESSION['userid'] = $var2;
} else {
    $arr['status'] = 0;
    $arr['info'] = '登陆失败';
}
echo json_encode($arr);