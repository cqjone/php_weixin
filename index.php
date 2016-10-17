<?php
//     $timestamp = $_GET['timestamp'];
//     $nonce = $_GET['nonce'];
//     $token = 'weixin';

//     $signature = $_GET['signature'];
//     $echostr = $_GET['echostr'];
//     $array = array($timestamp,$nonce,$token);
//     sort($array);
//     $tmpstr = implode('',$array);
//     $tmpstr = sha1($tmpstr);
//     if($tmpstr == $signature){
//         echo $echostr;
//         exit;
//     }

    define('APP_DEBUG',true);
    define('APP_NAME','App');
    define('APP_PATH','./App/');
    require './ThinkPHP/ThinkPHP.php';


?>

