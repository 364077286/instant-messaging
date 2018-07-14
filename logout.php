<?php
session_start();
$_SESSION['username'] = null;
$_SESSION['userid'] = null;
header("location:/imcase/index.php");