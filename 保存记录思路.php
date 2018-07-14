<?php
ini_set('display_errors', 'on');

class chatClass {
    private $redis;

    //这个变量模拟用户当前状态，是否登录，是否可查看
    public $checkUserReadable = false;

    //构造函数链接redis数据库
    public function __construct() {
        $this -> redis = new Redis();
        $this -> redis -> connect('127.0.0.1', '6379');
        $this -> redis -> auth('***');
    }

    /*
    发送消息时保存聊天记录
    * 这里用的redis存储是list数据类型
    * 两个人的聊天用一个list保存
    *
    * @from 消息发送者id
    * @to 消息接受者id
    * @meassage 消息内容
    *
    * 返回值，当前聊天的总聊天记录数
    */
    public function setChatRecord($from, $to, $message) {
        $data = array('from' => $from, 'to' => $to, 'message' => $message, 'sent' => time()/*, 'recd' => 0*/);
        $value = json_encode($data);
        //生成json字符串
        $keyName = 'rec:' . $this -> getRecKeyName($from, $to);
        //echo $keyName;
        $res = $this -> redis -> lPush($keyName, $value);
        if (!$this -> checkUserReadable) {//消息接受者无法立刻查看时，将消息设置为未读
            $this -> cacheUnreadMsg($from, $to);
        }
        return $res;
    }

    /*
    * 获取聊天记录
    * @from 消息发送者id
    * @to 消息接受者id
    * @num 获取的数量
    *
    * 返回值，指定长度的包含聊天记录的数组
    */
    public function getChatRecord($from, $to, $num) {
        $keyName = 'rec:' . $this -> getRecKeyName($from, $to);
        //echo $keyName;
        $recList = $this -> redis -> lRange($keyName, 0, (int)($num));
        return $recList;
    }

    /*
    * 当用户上线时，或点开聊天框时，获取未读消息的数目
    * @user 用户id
    *
    * 返回值，一个所有当前用户未读的消息的发送者和数组
    * 数组格式为‘消息发送者id’＝>‘未读消息数目’
    *
    */
    public function getUnreadMsgCount($user) {
        return $this -> redis -> hGetAll('unread_' . $user);
    }

    /*
    * 获取未读消息的内容
    * 通过未读消息数目，在列表中取得最新的相应消息即为未读
    * @from 消息发送者id
    * @to 消息接受者id
    *
    * 返回值，包括所有未读消息内容的数组
    *
    *
    */
    public function getUnreadMsg($from, $to) {
        $countArr = $this -> getUnreadMsgCount($to);
        $count = $countArr[$from];
        $keyName = 'rec:' . $this -> getRecKeyName($from, $to);
        return $this -> redis -> lRange($keyName, 0, (int)($count));
    }

    /*
    * 将消息设为已读
    * 当一个用户打开另一个用户的聊天框时，将所有未读消息设为已读
    * 清楚未读消息中的缓存
    * @from 消息发送者id
    * @to 消息接受者id
    *
    * 返回值，成功将未读消息设为已读则返回true,没有未读消息则返回false
    */

    public function setUnreadToRead($from, $to) {
        $res = $this -> redis -> hDel('unread_' . $to, $from);
        return (bool)$res;
    }

    /*
    * 当用户不在线时，或者当前没有立刻接收消息时，缓存未读消息,将未读消息的数目和发送者信息存到一个与接受者关联的hash数据中
    *
    * @from 发送消息的用户id
    * @to 接收消息的用户id
    *
    * 返回值，当前两个用户聊天中的未读消息
    *
    */
    private function cacheUnreadMsg($from, $to) {
        return $this -> redis -> hIncrBy('unread_' . $to, $from, 1);
    }

    /*生成聊天记录的键名，即按大小规则将两个数字排序
    * @from 消息发送者id
    * @to 消息接受者id
    *
    *
    */
    private function getRecKeyName($from, $to) {
        return ($from > $to) ? $to . '_' . $from : $from . '_' . $to;
    }

}

/*
* 下面为测试用的代码 ，伪造数据模拟场景
* 假定有两个用户id为2和3 ，2 向 3 发送消息
*

$chat = new chatClass();

$chat -> checkUserReadable = true;
for ($i = 0; $i < 20; $i++) {
$chat -> setChatRecord('2', '3', 'message_' . $i);
}

echo 'get 20 chat records</br>';
$arr = $chat -> getChatRecord('2', '3', 20);
for ($j = 0; $j < count($arr); $j++) {
echo $arr[$j] . '</br>';
}

$chat -> checkUserReadable = false;

for ($m = 0; $m < 5; $m++) {
$chat -> setChatRecord('2', '3', 'message_' . $m);
}

echo "</br>";
$umsg_1 = $chat -> getUnreadMsgCount(3);
echo "Unread message counts ";
echo "</br>";
print_r($umsg_1);
echo "Unread message content </br> ";
$umsgContent = $chat -> getUnreadMsg(2, 3);
for ($n = 0; $n < count($umsgContent); $n++) {
echo $arr[$n] . '</br>';
}
echo "</br>";
$chat -> setUnreadToRead(2, 3);
$umsg_2 = $chat -> getUnreadMsgCount(3);
echo "</br>";
echo "Unread message counts ";
echo "</br>";
print_r($umsg_2);
*
*/
?>