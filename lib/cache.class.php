<?php

class cache {

    var $db;
    var $cachefile;


    function cache(& $db) {
        $this->db=$db;
    }

    function getfile($cachename) {
        $this->cachefile=APP_ROOT.'/data/cache/'.$cachename.'.php';
    }

    function isvalid($cachename,$cachetime) {
        if(0==$cachetime) {
            return true;
        }
        $this->getfile($cachename);
        if(!is_readable($this->cachefile)||$cachetime<0) {
            return false;
        }
        clearstatcache();
        return (time()-filemtime($this->cachefile))<$cachetime;
    }

    function read($cachename,$cachetime=0) {
        $this->getfile($cachename);
        if($this->isvalid($cachename,$cachetime)) {
            return @include $this->cachefile;
        }
        return false;
    }

    function write($cachename, $arraydata) {
        $this->getfile($cachename);
        if(!is_array($arraydata)) return false;
        $strdata = "<?php\nreturn ".var_export($arraydata, true).";\n?>";
        $bytes = writetofile($this->cachefile, $strdata);
        return $bytes;
    }

    function remove($cachename) {
        $this->getfile($cachename);
        if(file_exists($this->cachefile)) {
            unlink($this->cachefile);
        }
    }

    function load($cachename,$id='id') {
        $arraydata=$this->read($cachename);
        if(!$arraydata) {
            $cursor = $this->db->selectCollection($cachename)->find();
            foreach($cursor as $item) {
                if( isset($item['k']) ) {
                    $arraydata[$item['k']]=$item['v'];
                }else {
                    $arraydata[$item[$id]]=$item;
                }
            }
            $this->write($cachename,$arraydata);
        }
        return $arraydata;
    }

}

?>