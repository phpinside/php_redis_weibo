<?php

$m = new Memcache(); 
$m->connect('192.168.0.13', 11211); 
$session = $m->get("tofcomuum6kencl4s067ebh0h6"); 
var_dump($session );
echo $session."<br/>"; //会得到这样的数据：username|s:16:"pandao";，解析一下就可以得到相应的值了
echo session_id()."<br/>";