<?php

!defined('IN_APP') && exit('Access Denied');

class controlbase {

	public  $ip;
	public  $time;
	public  $redis;	//redis
	public  $cache;
	public  $user = array();
	public  $setting = array();
	protected  $get = array();
	protected  $post = array();
 
	function __construct( $get,  $post) {
		$this->time = time();
		$this->ip = getip();
		$this->get =  $get;
		$this->post = $post;
		$this->init_db();
		//$this->init_cache();
		$this->init_user();
	}

	/* 
	$eventlist=$this('event')->findAll(); 
	本特性只在PHP 5.3.0 及以上版本有效。 
	*/
	function __invoke($modelname, $base = NULL) {
		$base = $base ? $base : $this;
		if (empty($_ENV[$modelname])) {
			$modelfile= APP_ROOT.'/model/'.$modelname.'.class.php';
			//动态创建model类，一般的通用model无需再创建。
			if(false===@include($modelfile)) {
			//	echo $modelname;
				eval('class '.$modelname.'model {}');
			}
		   eval('$_ENV[$modelname] = new ' . $modelname . 'model($base);');
		}
		return $_ENV[$modelname];
	}

	function init_db() {
		try {
			$redis = new Redis();
			$redis->connect( REDIS_HOST, REDIS_PORT );  //php客户端设置的ip及端口
			$redis->select( REDIS_INDEX );
 			$this->redis = $redis ;
		} catch(RedisException $e) {
			exit("Redis连接失败，请检查服务是否正常！");
		}
	}
	


	/* 一旦setting的缓存文件读取失败，则更新所有cache */

	function init_cache() {
		global $setting;
		$this->cache = new cache($this->db);
		$setting = $this->setting = $this->cache->load('setting');
	}

	function init_user() {
		@$auth = tcookie('auth');
		$user = array('uid'=>0);
		@list($uid, $password) = empty($auth) ? array(0, 0) : taddslashes(explode("\t", strcode($auth, AUTH_KEY, 'DECODE')), 1);
		if ($uid && $password) {
			$finduser = $this('user')->findById($uid);
			$finduser && ($password == $finduser['password']) && $user = $finduser;
		}
		$user['ip'] = $this->ip;
		$this->user = $user;
	}

	/* 权限检测 */
	function checkable($regular) {
		return 1; 
	}


	/*中转提示页面
	  $ishtml=1 表示是跳转到静态网页
	 */
	function message($message, $url = '') {
		$url= $url ? $url : SITE_URL ; 
		header('location:'.$url);
		exit;
	}

 
 

 

 
}