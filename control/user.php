<?php

!defined('IN_APP') && exit('Access Denied');

class usercontrol extends controlbase {

	function onregister() {
		$title = '注册';
 
 		if (isset($this->post['submit'])) {
			$username = $user['username']=trim($this->post['username']);
			$user['email']=trim($this->post['email']);
	 
			$finduser=$this('user')->findByUsername($username);
			if($finduser) $this->message("用户名 $username 已经注册!", 'c=user&a=register');
	 
			$user['posts'] = 0; //微博数为 0
			$user['fans'] = 0;   //粉丝数为 0
			$user['regip'] = $this->ip;
			$user['regtime'] = $this->time;
			$user['password'] = md5($this->post['password']);
			$user['uid'] = $this('user')->getNextUid();

			$this('user')->insert($user);
			$this('user')->refresh($user);

			header('location:?c=index');

		} else {
			include template('register');
		}
	}

	function onlogin() {
		$title = '登录';
		$forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
		if (isset($this->post['submit'])) {
			$username=$this->post['username'];
			$password=md5($this->post['password']);
			
		  	$user=$this('user')->findByUsername($username);

			if(is_array($user)&&($password==$user['password'])){
				$this('user')->refresh($user);
				$this->message('登录成功！', '?c=index');
			}else{
				$this->message('登录失败！', '?c=user&a=login');
			}
		}
		include template('login');
	}

 

	
	/* 退出系统 */
	function onlogout() {
		$title = '登出系统';
		$forward = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SITE_URL;
		$this('user')->logout();
		header('location:?c=index');
	}

 

	/* 用户空间 */
	function onspace() {
		$title = '用户空间';
		$uid=$this->get['uid'];
		$userInfo=$this('user')->findById($uid);
		$postList = $this('weibo')->getUserPosts($uid);
		include template('space');
	}

	/* 关注 */
	function onfollow() {
		$title = '关注用户';
		$uid=$this->get['uid'];
		$targetUser=$this('user')->findById($uid);
		if( 0 != $this->user['uid']){
			$this('user')->follow($targetUser,$this->user);
		}
		$this->message('关注成功！', '?c=user&a=space&uid='.$targetUser['uid']);
	}

	/* 取消关注 */
	function onunfollow() {
		$title = '取消关注';
		$uid=$this->get['uid'];
		$targetUser=$this('user')->findById($uid);
		if( 0 != $this->user['uid']){
			$this('user')->unfollow($targetUser,$this->user);
		}
		$this->message('取消关注成功！', '?c=user&a=space&uid='.$targetUser['uid']); 
	}



	/* 修改密码 */
	function onuppass() {
	 
	}
	
	/* 修改头像 */
	function onupimg() {
	 
	}
	
}