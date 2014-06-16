<?php
/*the app entrance */
ini_set('display_errors', 'on');
error_reporting(E_ALL);
date_default_timezone_set('PRC');
//session_start();
#set_magic_quotes_runtime(0);
$mtime = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];
define('IN_APP', TRUE);
define('APP_ROOT', dirname(__FILE__));
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
define('SITE_URL','http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'],0,-10) );

require APP_ROOT.'/config.php';
require APP_ROOT.'/lib/global.func.php';
require APP_ROOT.'/lib/cache.class.php';
require APP_ROOT.'/control/controlbase.php';

header('Content-type: text/html; charset=UTF-8');

/*$get=taddslashes($_GET);
$post=taddslashes($_POST);
*/
$get=$_GET;
$post=$_POST;
unset($GLOBALS, $_ENV, $_GET, $_POST);

empty($get['c']) && $get['c']='index';
empty($get['a']) && $get['a']='default';
define('ACTION', $get['a']);
define('REGULAR', $get['c'].'/'.$get['a']);
//load control...
$controlfile=APP_ROOT.'/control/'.$get['c'].'.php';


if(false===@include($controlfile)) {
  	notfound('control file "'.$controlfile.'" not found!');
}

$controlname=$get['c'].'control';
$control = new $controlname($get,$post);
$method=strtolower('on'.$get['a']);
if(method_exists($control, $method)) {
   $isajax=(0===strpos($get['a'],'ajax'));
   if($control->checkable(REGULAR) || $isajax) {
       $control->$method();
   }else {
   	   $querystring=strcode($_SERVER["QUERY_STRING"], '', 'ENCODE');
   	   tcookie('querystring', $querystring, 86400);
       $control->message('您无权进行当前操作，原因如下：<br/> 您所在的用户组('.$control->user['title'].')无法进行此操作。','c=user&a=login');
   }
}else {
   notfound('control "'.$controlname.'" method "'.$method.'" not found!');
}



