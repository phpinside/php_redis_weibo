<?php


/**
 * random
 * @param int $length
 * @return string $hash
 */
function random($length=6, $type=0) {
    $hash = '';
    $chararr = array(
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',
        '0123456789',
        '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'
    );
    $chars = $chararr[$type];
    $max = strlen($chars) - 1;
    PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

function cutstr($string, $length, $dot = ' ...') {
    if (strlen($string) <= $length) {
        return $string;
    }
    //$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
    $strcut = '';
    if (strtolower(APP_CHARSET) == 'utf-8') {
        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) {
                break;
            }
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
    } else {
        for ($i = 0; $i < $length; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
        }
    }
    //$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
    return $strcut . $dot;
}

function generate_key() {
    $random = random(20);
    $info = md5($_SERVER['SERVER_SOFTWARE'] . $_SERVER['SERVER_NAME'] . $_SERVER['SERVER_ADDR'] . $_SERVER['SERVER_PORT'] . $_SERVER['HTTP_USER_AGENT'] . time());
    $return = '';
    for ($i = 0; $i < 64; $i++) {
        $p = intval($i / 2);
        $return[$i] = $i % 2 ? $random[$p] : $info[$p];
    }
    return implode('', $return);
}

/**
 * getip
 * @return string
 */
function getip() {
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } else if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    preg_match("/[\d\.]{7,15}/", $ip, $temp);
    $ip = $temp[0] ? $temp[0] : 'unknown';
    unset($temp);
    return $ip;
}

function formatip($ip, $type=1, $hiddenstr='*') {
    if ($ip == 'unknown' || !$type)
        return $ip;
    $iparr = explode(".", $ip);
    if ($type == 1)
        return $iparr[0] . '.' . $iparr[1] . '.' . $iparr[2] . '.' . $hiddenstr;
    else
        return $iparr[0] . '.' . $iparr[1] . '.' . $hiddenstr . '.' . $hiddenstr;
}

function forcemkdir($path) {
    if (!file_exists($path)) {
        forcemkdir(dirname($path));
        mkdir($path, 0777);
    }
}

function cleardir($dir, $forceclear=false) {
    if (!is_dir($dir)) {
        return;
    }
    $directory = dir($dir);
    while ($entry = $directory->read()) {
        $filename = $dir . '/' . $entry;
        if (is_file($filename)) {
            @unlink($filename);
        } elseif (is_dir($filename) && $forceclear && $entry != '.' && $entry != '..') {
            chmod($filename, 0777);
            cleardir($filename, $forceclear);
            rmdir($filename);
        }
    }
    $directory->close();
}

function iswriteable($file) {
    $writeable = 0;
    if (is_dir($file)) {
        $dir = $file;
        if ($fp = @fopen("$dir/test.txt", 'w')) {
            @fclose($fp);
            @unlink("$dir/test.txt");
            $writeable = 1;
        }
    } else {
        if ($fp = @fopen($file, 'a+')) {
            @fclose($fp);
            $writeable = 1;
        }
    }
    return $writeable;
}

function readfromfile($filename) {
    if ($fp = @fopen($filename, 'rb')) {
        if (PHP_VERSION >= '4.3.0' && function_exists('file_get_contents')) {
            return file_get_contents($filename);
        } else {
            flock($fp, LOCK_EX);
            $data = fread($fp, filesize($filename));
            flock($fp, LOCK_UN);
            fclose($fp);
            return $data;
        }
    } else {
        return '';
    }
}

function writetofile($filename, &$data) {
    if ($fp = @fopen($filename, 'wb')) {
        if (PHP_VERSION >= '4.3.0' && function_exists('file_put_contents')) {
            return @file_put_contents($filename, $data);
        } else {
            flock($fp, LOCK_EX);
            $bytes = fwrite($fp, $data);
            flock($fp, LOCK_UN);
            fclose($fp);
            return $bytes;
        }
    } else {
        return 0;
    }
}

function extname($filename) {
    $pathinfo = pathinfo($filename);
    return strtolower($pathinfo['extension']);
}

function taddslashes($string, $force = 0) {
    if (!MAGIC_QUOTES_GPC || $force) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = taddslashes($val, $force);
            }
        } else {
            $string = addslashes($string);
        }
    }
    return $string;
}

function tstripslashes($string) {
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = tstripslashes($val);
        }
    } else {
        $string = stripslashes($string);
    }
    return $string;
}

function template($file, $tpldir = '') {
    global $setting;
    $tpldir = defined('ISADMIN') ? 'admin' : $tpldir; //如果访问管理后台，默认使用admin目录模版
    $tpldir = ('' == $tpldir) ? $setting['tpl_dir'] : $tpldir;
    $tplfile = APP_ROOT . '/view/' . $tpldir . '/' . $file . '.html';
    $objfile = APP_ROOT . '/data/view/' . $tpldir . '_' . $file . '.tpl.php';
    if ('default' != $tpldir && !is_file($tplfile)) {
        $tplfile = APP_ROOT . '/view/default/' . $file . '.html';
        $objfile = APP_ROOT . '/data/view/default_' . $file . '.tpl.php';
    }
    if (!file_exists($objfile) || (@filemtime($tplfile) > @filemtime($objfile))) {
        require_once APP_ROOT . '/lib/template.func.php';
        parse_template($tplfile, $objfile);
    }
    return $objfile;
}

function timeLength($time) {
    $length = '';
    if ($day = floor($time / (24 * 3600))) {
        $length .= $day . '天';
    }
    if ($hour = floor($time % (24 * 3600) / 3600)) {
        $length .= $hour . '小时';
    }
    if ($day == 0 && $hour == 0) {
        $length = floor($time / 60) . '分';
    }
    return $length;
}

/* 验证码生成 */

function makecode($code) {
    $codelen = strlen($code);
    $im = imagecreate(50, 20);
    $font_type = APP_ROOT . '/css/default/ninab.ttf';
    $bgcolor = ImageColorAllocate($im, 245, 245, 245);
    $iborder = ImageColorAllocate($im, 0x71, 0x76, 0x67);
    $fontColor = ImageColorAllocate($im, 0x50, 0x4d, 0x47);
    $fontColor2 = ImageColorAllocate($im, 0x36, 0x38, 0x32);
    $fontColor1 = ImageColorAllocate($im, 0xbd, 0xc0, 0xb8);
    $lineColor1 = ImageColorAllocate($im, 130, 220, 245);
    $lineColor2 = ImageColorAllocate($im, 225, 245, 255);
    for ($j = 3; $j <= 16; $j = $j + 3)
        imageline($im, 2, $j, 48, $j, $lineColor1);
    for ($j = 2; $j < 52; $j = $j + (mt_rand(3, 6)))
        imageline($im, $j, 2, $j - 6, 18, $lineColor2);
    imagerectangle($im, 0, 0, 49, 19, $iborder);
    $strposs = array();
    for ($i = 0; $i < $codelen; $i++) {
        if (function_exists("imagettftext")) {
            $strposs[$i][0] = $i * 10 + 6;
            $strposs[$i][1] = mt_rand(15, 18);
            imagettftext($im, 11, 5, $strposs[$i][0] + 1, $strposs[$i][1] + 1, $fontColor1, $font_type, $code[$i]);
        } else {
            imagestring($im, 5, $i * 10 + 6, mt_rand(2, 4), $code[$i], $fontColor);
        }
    }
    for ($i = 0; $i < $codelen; $i++) {
        if (function_exists("imagettftext")) {
            imagettftext($im, 11, 5, $strposs[$i][0] - 1, $strposs[$i][1] - 1, $fontColor2, $font_type, $code[$i]);
        }
    }
    header("Pragma:no-cache\r\n");
    header("Cache-Control:no-cache\r\n");
    header("Expires:0\r\n");
    if (function_exists("imagejpeg")) {
        header("content-type:image/jpeg\r\n");
        imagejpeg($im);
    } else {
        header("content-type:image/png\r\n");
        imagepng($im);
    }
    ImageDestroy($im);
}

/* 通用加密解密函数，phpwind、phpcms、dedecms都用此函数 */

function strcode($string, $auth_key, $action='ENCODE') {
    $key = substr(md5($_SERVER["HTTP_USER_AGENT"] . $auth_key), 8, 18);
    $string = $action == 'ENCODE' ? $string : base64_decode($string);
    $len = strlen($key);
    $code = '';
    for ($i = 0; $i < strlen($string); $i++) {
        $k = $i % $len;
        $code .= $string[$i] ^ $key[$k];
    }
    $code = $action == 'DECODE' ? $code : base64_encode($code);
    return $code;
}

/* 日期格式显示 */

function tdate($time, $type = 3, $friendly=1) {
    global $setting;
    ($setting['time_friendly'] != 1) && $friendly = 0;
    $format[] = $type & 2 ? (!empty($setting['date_format']) ? $setting['date_format'] : 'Y-n-j') : '';
    $format[] = $type & 1 ? (!empty($setting['time_format']) ? $setting['time_format'] : 'H:i') : '';
    $timeoffset = $setting['time_offset'] * 3600 + $setting['time_diff'] * 60;
    $timestring = gmdate(implode(' ', $format), $time + $timeoffset);
    if ($friendly) {
        $time = time() - $time;
        if ($time <= 24 * 3600) {
            if ($time > 3600) {
                $timestring = intval($time / 3600) . '小时前';
            } elseif ($time > 60) {
                $timestring = intval($time / 60) . '分钟前';
            } elseif ($time > 0) {
                $timestring = $time . '秒前';
            } else {
                $timestring = '现在前';
            }
        }
    }
    return $timestring;
}

/* cookie设置和读取 */

function tcookie($var, $value=0, $life = 0) {
    global $setting;
    $cookiepre = $setting['cookie_pre'] ? $setting['cookie_pre'] : 't_';
    if (0 === $value) {
        return isset($_COOKIE[$cookiepre . $var]) ? $_COOKIE[$cookiepre . $var] : '';
    } else {
        $domain = $setting['cookie_domain'] ? $setting['cookie_domain'] : '';
        setcookie($cookiepre . $var, $value, $life ? time() + $life : 0, '/', $domain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
    }
}

/* 日志记录 */

function runlog($file, $message, $halt=0) {
    $nowurl = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : ($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
    $log = tdate($_SERVER['REQUEST_TIME'], 'Y-m-d H:i:s') . "\t" . $_SERVER['REMOTE_ADDR'] . "\t{$nowurl}\t" . str_replace(array("\r", "\n"), array(' ', ' '), trim($message)) . "\n";
    $yearmonth = gmdate('Ym', $_SERVER['REQUEST_TIME']);
    $logdir = APP_ROOT . '/data/logs/';
    if (!is_dir($logdir))
        mkdir($logdir, 0777);
    $logfile = $logdir . $yearmonth . '_' . $file . '.php';
    if (@filesize($logfile) > 2048000) {
        $dir = opendir($logdir);
        $length = strlen($file);
        $maxid = $id = 0;
        while ($entry = readdir($dir)) {
            if (strexists($entry, $yearmonth . '_' . $file)) {
                $id = intval(substr($entry, $length + 8, -4));
                $id > $maxid && $maxid = $id;
            }
        }
        closedir($dir);
        $logfilebak = $logdir . $yearmonth . '_' . $file . '_' . ($maxid + 1) . '.php';
        @rename($logfile, $logfilebak);
    }
    if ($fp = @fopen($logfile, 'a')) {
        @flock($fp, 2);
        fwrite($fp, "<?PHP exit;?>\t" . str_replace(array('<?', '?>', "\r", "\n"), '', $log) . "\n");
        fclose($fp);
    }
    if ($halt)
        exit();
}

/* 翻页函数 */

function page($num, $perpage, $curpage, $operation) {
    global $setting;
    $multipage = '';
    //$operation = urlmap($operation, 2);

    $mpurl = SITE_URL . $setting['seo_prefix'] . $operation . '&page=';
    ('admin' == substr($operation, 0, 5)) && ( $mpurl = 'index.php?' . $operation . '&page=');

    if ($num > $perpage) {
        $page = 10;
        $offset = 2;
        $pages = @ceil($num / $perpage);
        if ($page > $pages) {
            $from = 1;
            $to = $pages;
        } else {
            $from = $curpage - $offset;
            $to = $from + $page - 1;
            if ($from < 1) {
                $to = $curpage + 1 - $from;
                $from = 1;
                if ($to - $from < $page) {
                    $to = $page;
                }
            } elseif ($to > $pages) {
                $from = $pages - $page + 1;
                $to = $pages;
            }
        }
        $multipage = ($curpage - $offset > 1 && $pages > $page ? '<a  class="n" href="' . $mpurl . '1' . $setting['seo_suffix'] . '" >首页</a>' . "\n" : '') .
                ($curpage > 1 ? '<a href="' . $mpurl . ($curpage - 1) . $setting['seo_suffix'] . '"  class="n">上一页</a>' . "\n" : '');
        for ($i = $from; $i <= $to; $i++) {
            $multipage .= $i == $curpage ? "<strong>$i</strong>\n" :
                    '<a href="' . $mpurl . $i . $setting['seo_suffix'] . '">' . $i . '</a>' . "\n";
        }
        $multipage .= ( $curpage < $pages ? '<a class="n" href="' . $mpurl . ($curpage + 1) . $setting['seo_suffix'] . '">下一页</a>' . "\n" : '') .
                ($to < $pages ? '<a class="n" href="' . $mpurl . $pages . $setting['seo_suffix'] . '" >最后一页</a>' . "\n" : '');
    }
    return $multipage;
}

/* 京东翻页格式 */
function departPage($num, $perpage, $curpage, $operation){
    global $setting;
    $multipage = '';
    $mpurl = SITE_URL . $setting['seo_prefix'] . $operation . '&page=';
    if ($num > $perpage) {
        $page = 6;
        $offset = 2;
        $pages = @ceil($num / $perpage);
        if ($page > $pages) {
            $from = 1;
            $to = $pages;
        } else {
            $from = $curpage - $offset;
            $to = $from + $page - 1;
            if ($from < 1) {
                $to = $curpage + 1 - $from;
                $from = 1;
                if ($to - $from < $page) {
                    $to = $page;
                }
            } elseif ($to > $pages) {
                $from = $pages - $page + 1;
                $to = $pages;
            }
        }
        $multipage = ($curpage - $offset > 1 && $pages > $page ? '<a  class="prev" href="' . $mpurl . '1' . $setting['seo_suffix'] . '" >首页</a>' . "\n" : '') .
                ($curpage > 1 ? '<a href="' . $mpurl . ($curpage - 1) . $setting['seo_suffix'] . '"  class="prev">上一页<b></b></a>' . "\n" : '');
        for ($i = $from; $i <= $to; $i++) {
            $multipage .= $i == $curpage ? '<a class="current" href="'. $mpurl . $i . $setting['seo_suffix'] .'">'.$i.'</a>'."\n" :
                    '<a href="' . $mpurl . $i . $setting['seo_suffix'] . '">' . $i . '</a>' . "\n";
        }
        $multipage .= ( $curpage < $pages ? '<a class="next" href="' . $mpurl . ($curpage + 1) . $setting['seo_suffix'] . '">下一页<b></b></a>' . "\n" : '') .
                ($to < $pages ? '<a class="next-disabled" href="' . $mpurl . $pages . $setting['seo_suffix'] . '" >最后一页</a>' . "\n" : '');
    }
    return $multipage;
}


/* 过滤关键词 */

function checkwords($content) {
    global $setting, $badword;
    $status = 0;
    $text = $content;
    if (!empty($badword)) {
        foreach ($badword as $word => $wordarray) {
            $replace = $wordarray['replacement'];
            $content = str_replace($word, $replace, $content, $matches);
            if ($matches > 0) {
                '{MOD}' == $replace && $status = 1;
                '{BANNED}' == $replace && $status = 2;
                if ($status > 0) {
                    $content = $text;
                    break;
                }
            }
        }
    }
    //$content = str_replace(array("\r\n", "\r", "\n"), '<br />', htmlentities($content));
    return array($status, $content);
}

/* http请求 */

function topen($url, $timeout = 15, $post = '', $cookie = '', $limit = 0, $ip = '', $block = TRUE) {
    $return = '';
    $matches = parse_url($url);
    $host = $matches['host'];
    $path = $matches['path'] ? $matches['path'] . ($matches['query'] ? '?' . $matches['query'] : '') : '/';
    $port = !empty($matches['port']) ? $matches['port'] : 80;
    if ($post) {
        $out = "POST $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
//$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= 'Content-Length: ' . strlen($post) . "\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cache-Control: no-cache\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
        $out .= $post;
    } else {
        $out = "GET $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
//$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
    }
    $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
    if (!$fp) {
        return '';
    } else {
        stream_set_blocking($fp, $block);
        stream_set_timeout($fp, $timeout);
        @fwrite($fp, $out);
        $status = stream_get_meta_data($fp);
        if (!$status['timed_out']) {
            while (!feof($fp)) {
                if (($header = @fgets($fp)) && ($header == "\r\n" || $header == "\n")) {
                    break;
                }
            }
            $stop = false;
            while (!feof($fp) && !$stop) {
                $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                $return .= $data;
                if ($limit) {
                    $limit -= strlen($data);
                    $stop = $limit <= 0;
                }
            }
        }
        @fclose($fp);
        return $return;
    }
}

/* 发送邮件 */

function sendmail($touser, $subject, $message, $from = '') {
    global $setting;
    $toemail = $touser['email'];
    $tousername = $touser['username'];
    $message = <<<EOT
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset={APP_CHARSET}">
		<title>$subject</title>
		</head>
		<body>
		hi, $tousername<br>
            $subject<br>
            $message<br>
		这封邮件由系统自动发送，请不要回复。
		</body>
		</html>
EOT;

    $maildelimiter = $setting['maildelimiter'] == 1 ? "\r\n" : ($setting['maildelimiter'] == 2 ? "\r" : "\n");
  
    $mailusername = isset($setting['mailusername']) ? $setting['mailusername'] : 1;
    $mailserver = $setting['mailserver'];
    $mailport = $setting['mailport'] ? $setting['mailport'] : 25;
    $mailsend = $setting['mailsend'] ? $setting['mailsend'] : 1;

    if ($mailsend == 3) {
        $email_from = empty($from) ? $setting['maildefault'] : $from;
    } else {
        $email_from = $from == '' ? '=?' . APP_CHARSET . '?B?' . base64_encode($setting['site_name']) . "?= <" . $setting['maildefault'] . ">" : (preg_match('/^(.+?) \<(.+?)\>$/', $from, $mats) ? '=?' . APP_CHARSET . '?B?' . base64_encode($mats[1]) . "?= <$mats[2]>" : $from);
    }

    $email_to = preg_match('/^(.+?) \<(.+?)\>$/', $toemail, $mats) ? ($mailusername ? '=?' . CHARSET . '?B?' . base64_encode($mats[1]) . "?= <$mats[2]>" : $mats[2]) : $toemail;
    ;

    $email_subject = '=?' . APP_CHARSET . '?B?' . base64_encode(preg_replace("/[\r|\n]/", '', '[' . $setting['site_name'] . '] ' . $subject)) . '?=';
    $email_message = chunk_split(base64_encode(str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message))))));

    $headers = "From: $email_from{$maildelimiter}X-Priority: 3{$maildelimiter}X-Mailer: Tipask1.0 {$maildelimiter}MIME-Version: 1.0{$maildelimiter}Content-type: text/html; charset=" . APP_CHARSET . "{$maildelimiter}Content-Transfer-Encoding: base64{$maildelimiter}";

    if ($mailsend == 1) {
        if (function_exists('mail') && @mail($email_to, $email_subject, $email_message, $headers)) {
            return true;
        }
        return false;
    } elseif ($mailsend == 2) {

        if (!$fp = fsockopen($mailserver, $mailport, $errno, $errstr, 30)) {
            runlog('SMTP', "($mailserver:$mailport) CONNECT - Unable to connect to the SMTP server", 0);
            return false;
        }
        stream_set_blocking($fp, true);

        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != '220') {
            runlog('SMTP', "($mailserver:$mailport) CONNECT - $lastmessage", 0);
            return false;
        }

        fputs($fp, ($setting['mailauth'] ? 'EHLO' : 'HELO') . " Tipask\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250) {
            runlog('SMTP', "($mailserver:$mailport) HELO/EHLO - $lastmessage", 0);
            return false;
        }

        while (1) {
            if (substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {
                break;
            }
            $lastmessage = fgets($fp, 512);
        }

        if ($setting['mailauth']) {
            fputs($fp, "AUTH LOGIN\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 334) {
                runlog('SMTP', "($mailserver:$mailport) AUTH LOGIN - $lastmessage", 0);
                return false;
            }

            fputs($fp, base64_encode($setting['mailauth_username']) . "\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 334) {
                runlog('SMTP', "($mailserver:$mailport) USERNAME - $lastmessage", 0);
                return false;
            }

            fputs($fp, base64_encode($setting['mailauth_password']) . "\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 235) {
                runlog('SMTP', "($mailserver:$mailport) PASSWORD - $lastmessage", 0);
                return false;
            }

            $email_from = $setting['maildefault'];
        }

        fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from) . ">\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from) . ">\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 250) {
                runlog('SMTP', "($mailserver:$mailport) MAIL FROM - $lastmessage", 0);
                return false;
            }
        }

        fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail) . ">\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail) . ">\r\n");
            $lastmessage = fgets($fp, 512);
            runlog('SMTP', "($mailserver:$mailport) RCPT TO - $lastmessage", 0);
            return false;
        }

        fputs($fp, "DATA\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 354) {
            runlog('SMTP', "($mailserver:$mailport) DATA - $lastmessage", 0);
            return false;
        }

        $headers .= 'Message-ID: <' . gmdate('YmdHs') . '.' . substr(md5($email_message . microtime()), 0, 6) . rand(100000, 999999) . '@' . $_SERVER['HTTP_HOST'] . ">{$maildelimiter}";

        fputs($fp, "Date: " . gmdate('r') . "\r\n");
        fputs($fp, "To: " . $email_to . "\r\n");
        fputs($fp, "Subject: " . $email_subject . "\r\n");
        fputs($fp, $headers . "\r\n");
        fputs($fp, "\r\n\r\n");
        fputs($fp, "$email_message\r\n.\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            runlog('SMTP', "($mailserver:$mailport) END - $lastmessage", 0);
        }
        fputs($fp, "QUIT\r\n");

        return true;
    } elseif ($mailsend == 3) {

        ini_set('SMTP', $mailserver);
        ini_set('smtp_port', $mailport);
        ini_set('sendmail_from', $email_from);

        if (function_exists('mail') && @mail($email_to, $email_subject, $email_message, $headers)) {
            return true;
        }
        return false;
    }
}

/* 取得一个字符串的拼音表示形式 */

function getpinyin($str, $ishead=0, $isclose=1) {
    if (!function_exists('gbk_to_pinyin')) {
        require_once(APP_ROOT . '/lib/iconv.func.php');
    }
    if (APP_CHARSET == 'utf-8') {
        $str = utf8_to_gbk($str);
    }
    return gbk_to_pinyin($str, $ishead, $isclose);
}

 

/* 数组类型，是否是向量类型 */

function isVector(&$array) {
    $next = 0;
    foreach ($array as $k => $v) {
        if ($k !== $next)
            return false;
        $next++;
    }
    return true;
}

/* 自己定义tjson_encode */

function tjson_encode($value) {
    switch (gettype($value)) {
        case 'double':
        case 'integer':
            return $value > 0 ? $value : '"' . $value . '"';
        case 'boolean':
            return $value ? 'true' : 'false';
        case 'string':
            return '"' . str_replace(
                    array("\n", "\b", "\t", "\f", "\r"), array('\n', '\b', '\t', '\f', '\r'), addslashes($value)
            ) . '"';
        case 'NULL':
            return 'null';
        case 'object':
            return '"Object ' . get_class($value) . '"';
        case 'array':
            if (isVector($value)) {
                if (!$value) {
                    return $value;
                }
                foreach ($value as $v) {
                    $result[] = tjson_encode($v);
                }
                return '[' . implode(',', $result) . ']';
            } else {
                $result = '{';
                foreach ($value as $k => $v) {
                    if ($result != '{')
                        $result .= ',';
                    $result .= tjson_encode($k) . ':' . tjson_encode($v);
                }
                return $result . '}';
            }
        default:
            return '"' . addslashes($value) . '"';
    }
}

  
/* 内存是否够用 */

function is_mem_available($mem) {
    $limit = trim(ini_get('memory_limit'));
    if (empty($limit))
        return true;
    $unit = strtolower(substr($limit, -1));
    switch ($unit) {
        case 'g':
            $limit = substr($limit, 0, -1);
            $limit *= 1024 * 1024 * 1024;
            break;
        case 'm':
            $limit = substr($limit, 0, -1);
            $limit *= 1024 * 1024;
            break;
        case 'k':
            $limit = substr($limit, 0, -1);
            $limit *= 1024;
            break;
    }
    if (function_exists('memory_get_usage')) {
        $used = memory_get_usage();
    }
    if ($used + $mem > $limit) {
        return false;
    }
    return true;
}

//图片处理函数
/* 根据扩展名判断是否图片 */
function isimage($extname) {
    return in_array($extname, array('jpg', 'jpeg', 'png', 'gif', 'bmp'));
}

 
/**
 * 获取内容中的第一张图
 * @param unknown_type $string
 * @return unknown|string
 */
function getfirstimg(&$string) {
    preg_match("/<img.+?src=[\\\\]?\"(.+?)[\\\\]?\"/i", $string, $imgs);
    if (isset($imgs[1])) {
        return $imgs[1];
    } else {
        return "";
    }
}

/*错误提示信息*/
function notfound($error){
	@header('HTTP/1.0 404 Not Found');
	exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p> $error </p></body></html>");
}


/*文件上传
$upname         file表单的name
$targetFile     目标文件路径
*/
function uploadfile($upname,$targetFile){
    $sucess=0;
    $targetDir=dirname($targetFile);
    forcemkdir($targetDir);
    if(@copy($_FILES[$upname]['tmp_name'],$targetDir) || @move_uploaded_file($_FILES[$upname]['tmp_name'],$targetFile)){
      $sucess=1;
    }
    if(0==$sucess && @is_readable($_FILES[$upname]['tmp_name'])){
        @$fp = fopen($_FILES[$upname]['tmp_name'], 'rb');
        @flock($fp, 2);
        @$file = fread($fp, $_FILES[$upname]['size']);
        @fclose($fp);
        @$fp = fopen($targetFile, 'wb');
        @flock($fp,2);
        if(@fwrite($fp, $file)) {
            @unlink($_FILES[$upname]['tmp_name']);
            $sucess=1;
        }
        @fclose($fp);
    }
    return $sucess;
}


    
 

?>