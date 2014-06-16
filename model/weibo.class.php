<?php

!defined('IN_APP') && exit('Access Denied');

class weibomodel  {


	private $base;
	private $redis;
	
	function __construct($base) {
		$this->base=$base;
		$this->redis=$base->redis;
	}
    
	function getNextPostId(){
        $this->redis->incr('next_post_id');
        return $this->redis->get('next_post_id');
    }	

	function add($content){
       #创建投稿id
       $next_post_id = $this->getNextPostId();
       $uid = $this->base->user['uid'];
       #保存投稿数据
       $this->redis->set('post:'.$next_post_id, $uid.'|'.$this->base->time.'|'.$content);

       #包含全部用户微博的时间线中追加投稿id
       $this->redis->lPush('timeline',$next_post_id);

       #所有粉丝的时间线中追加投稿id,包括自己
       $followers = $this->redis->sMembers("followers:$uid");
       $followers[]= $uid; //加上自己
       foreach ($followers as $follower_uid) {	
			$this->redis->lPush("timeline:$follower_uid", $next_post_id );
       }

       #往自己的微博数据中追加投稿id
	   $this->redis->lPush("$uid:posts", $next_post_id );

	   #增加发微博的条数
	   $this->redis->hIncrBy( "user:$uid", 'posts', 1);

       
	}


	//获取首页的时间线微博数据
	function getTimeLine($uid=0){
	   $postList=array();
	   if(0==$uid){
       	  $post_ids=$this->redis->lRange('timeline',0,-1);
	   }else{
       	  $post_ids=$this->redis->lRange("timeline:$uid",0,-1);
	   }
	   //获取微博的内容
       foreach ($post_ids as $post_id) {
       		$post_line=$this->redis->get("post:$post_id");
       		$postList[]=$this->convert($post_line);
       }
       return $postList;
	}



	//用户自己的微博数据
	function getUserPosts($uid){
	   $postList=array();
       $post_ids=$this->redis->lRange("$uid:posts",0,-1);
	   //获取微博的内容
       foreach ($post_ids as $post_id) {
	       	$post_line=$this->redis->get("post:$post_id");
	       	$postList[]=$this->convert($post_line);
       }
       return $postList;
	}


	//对于单条微博数据的获取	
 	function convert($line){
 		$params=explode('|',$line);  
		$base = $this->base;
		$item['user'] = $base('user')->findById($params[0]);
		$item['format_dateline'] = tdate($params[1]);
		$item['content'] = implode('|',array_slice($params, 2)); //万一内容里面有这个符号，不能去掉了。
		return $item;
	}
	
	 
	 
}

