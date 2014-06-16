<?php
!defined('IN_APP') && exit('Access Denied');

class indexcontrol extends controlbase {

	function ondefault() {
		$title='首页';
		$postList = $this('weibo')->getTimeLine($this->user['uid']);
		include template('index');
	}

	function onindex() {
		$this->ondefault();
	}

	function onpost() {
		$title='发微博';
		$content=htmlspecialchars($this->post['content']);
		$this('weibo')->add($content);
		$this->message('发微博成功！', '?c=index'); 
	} 

 

	/*function oneveryday() {
		$title='每日一题';
		include template('everyday');
	}*/

 

 
 

}