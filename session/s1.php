<?php
//测试session读取是否正常 
session_start(); 
$_SESSION['username'] = "sijiaomao"; 
echo session_id()."\n"; 
//从Memcache中读取session 
$m = new Memcache(); 
$m->connect('192.166.1.39', 11211); 
//或者这样 
//$mem->addServer("127.0.0.1", 11211) or die ("Can't add Memcache server 127.0.0.1:12000"); 

//根据session_id获取数据 

//本机 

$session = $m->get(session_id());

var_dump($session);
