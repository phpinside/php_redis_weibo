<?php

!defined('IN_APP') && exit('Access Denied');

class settingmodel  {

	private $base;
	private $redis;
	
	function __construct($base) {
		$this->base=$base;
		$this->redis=$base->redis;
	}
    
	function update($setting) {
		foreach($setting as $key=>$value) {
			
  		}
		$this->base->cache->remove('setting');
	}

	/*读取view文件夹，获取模板的选项*/
	function tpl_list() {
		$tpllist=array();
		$filedir=APP_ROOT.'/view';
		$handle=opendir($filedir);
		while($filename=readdir($handle)) {
			if (is_dir($filedir.'/'.$filename) && '.'!=$filename{0} && 'admin'!=$filename) {
				$tpllist[]=$filename;
			}
		}
		closedir($handle);
		return $tpllist;
	}

 
}
