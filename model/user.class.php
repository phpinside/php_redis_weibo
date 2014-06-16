<?php

!defined('IN_APP') && exit('Access Denied');

class usermodel {
	
	private $base;
	private $redis;
	
	function __construct($base) {
		$this->base=$base;
		$this->redis=$base->redis;
	}
	
    function getNextUid(){
        $this->redis->incr('next_uid');
        return $this->redis->get('next_uid');
    }	

    function findById($uid){
		return $this->redis->hGetAll('user:'.$uid);
	}
    
    function findByUsername($username){
        $uid = $this->redis->get('user:'.$username);
        if( false === $uid ) return false;
        return $this->findById($uid);
    }

    function insert($user){
        $hkey='user:'.$user['uid'];
        $this->redis->hMset($hkey,$user);
        $this->redis->set('user:'.$user['username'], $user['uid']); //同时存好username和uid的对应
    }


    function refresh($user) {
        $uid = $user['uid'];
        $password = $user['password'];
        $this->base->user = $user;
        $auth = strcode("$uid\t$password", AUTH_KEY , 'ENCODE');
		tcookie('auth', $auth, DAY*30);
    }

    function logout() {
        tcookie('auth', '', 0);
    }

    //对某人进行关注
    function follow($targetUser,$user){
         //自己关注的列表
         $followingKey='following:'.$user['uid'];
         $this->redis->sadd( $followingKey, $targetUser['uid']);
         //被关注者的粉丝列表
         $followersKey='followers:'.$targetUser['uid'];
         $this->redis->sadd( $followersKey, $user['uid']);
         $followers = $this->redis->sCard($followersKey); //获取粉丝数
         $this->redis->hset( 'user:'.$targetUser['uid'], 'fans', $followers);
    }


    //对某人取消关注
    function unfollow($targetUser,$user){
         //自己关注的列表
         $followingKey='following:'.$user['uid'];
         $this->redis->sRem( $followingKey, $targetUser['uid']);
         //被关注者的粉丝列表
         $followersKey='followers:'.$targetUser['uid'];
         $this->redis->sRem( $followersKey, $user['uid']);
         $followers = $this->redis->sCard($followersKey); //获取粉丝数
         $this->redis->hset( 'user:'.$targetUser['uid'], 'fans', $followers);
    }

    


}
