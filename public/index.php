<?php
$__content__ = '';
$__chunked__= 0;
$__trailer__= 0;
function namef() {
$req = $_SERVER['REQUEST_URI'];
if ($req == '//') {
exit;
}
if ($req == '/') {
$nff = 'file.7z';
$nfr = 'application/octet-stream'; }
else {
$nff = str_replace('/', '', $req);
$nfr = substr($req, 1); 
$nfr = explode('.', $nfr);
$nfr = $nfr[1];
$tmp = file('mime.tmp');
foreach ($tmp as $key) {
$key = explode('||', $key); 
if ($key[0] == $nfr) {
$nfr = $key[1];
break; }
}
}
return array($nff, $nfr);
}
$__password__ = base64_decode('MzQ1YQ==');
function message_html($title, $banner, $detail) {
    $error =  "$title$banner$detail";
    return $error;
}
function decode_request($data) {
    list($headers_length) = array_values(unpack('n', substr($data, 0, 2)));
    $headers_data = gzinflate(substr($data, 2, $headers_length));
    $body = substr($data, 2+intval($headers_length));
    $lines = explode("\r\n", $headers_data);
    $request_line_items = explode(" ", array_shift($lines));
    $method = $request_line_items[0];
    $url = $request_line_items[1];
    $headers = array();
    $kwargs  = array();
    $kwargs_prefix = 'X-URLFETCH-';
    foreach ($lines as $line) {
        if (!$line)
            continue;
        $pair = explode(':', $line, 2);
        $key  = $pair[0];
        $value = trim($pair[1]);
        if (stripos($key, $kwargs_prefix) === 0) {
            $kwargs[strtolower(substr($key, strlen($kwargs_prefix)))] = $value;
        } else if ($key) {
            $key = join('-', array_map('ucfirst', explode('-', $key)));
            $headers[$key] = $value;
        }
    }
    if (isset($headers['Content-Encoding'])) {
        if ($headers['Content-Encoding'] == 'deflate') {
            $body = gzinflate($body);
            $headers['Content-Length'] = strval(strlen($body));
            unset($headers['Content-Encoding']);
        }
    }
    return array($method, $url, $headers, $kwargs, $body);
}

function echo_content($content) {
    global $__password__, $__chunked__, $__content__;
    $chunk="";
    if($__chunked__==1) {
    	if(empty($__content__)) {
    		$chunk=sprintf("%x\r\n%s\r\n", strlen($content), $content);
	} else {
    	        $chunk=$content;
	}
    } else {
    	        $chunk=$content;
    }
$chunk = $chunk ^ str_repeat($__password__[0], strlen($chunk));
   list($nameff, $namefr) = namef();
header('Content-type: '.$namefr.'');
header('Content-Disposition: attachment; filename='.$nameff.'');
	echo $chunk;
}


function curl_header_function($ch, $header) {
    global $__content__, $__chunked__;
    $pos = strpos($header, ':');
    if ($pos == false) {
        $__content__ .= $header;
    } else {
        $key = join('-', array_map('ucfirst', explode('-', substr($header, 0, $pos))));
            $__content__ .= $key . substr($header, $pos);
    }
    
    if (!trim($header)) {
   
    }
    if (preg_match('@^Transfer-Encoding: ?(chunked)@i', $header)) {
        $__chunked__ = 1;
    }
    return strlen($header);
}


function curl_write_function($ch, $content) {
    global $__content__,$__chunked__,$__trailer__;
    if ($__content__) {
        echo_content($__content__);
        $__content__ = '';
	$__trailer__ = $__chunked__;
    }
    echo_content($content);
    return strlen($content);
}

function post() {
    global $__password__;
    list($method, $url, $headers, $kwargs, $body) = @decode_request(@file_get_contents('php://input'));
    $password = $GLOBALS['__password__'];
   
	if ($password) {
        if (!isset($kwargs['password']) || $password != $kwargs['password']) {
            header("HTTP/1.0 403 Forbidden");
            echo message_html('403 Forbidden', 'Error Password', "please confirm your password.");
            exit(-1);
        }
    }

   // if ($body) {
        //$headers['Content-Length'] = strval(strlen($body));
   // }
   
    $header_array = array();
    foreach ($headers as $key => $value) {
        $header_array[] = join('-', array_map('ucfirst', explode('-', $key))).': '.$value;
    }

    $curl_opt = array();
   switch (strtoupper($method)) {
        case 'HEAD':
            $curl_opt[CURLOPT_NOBODY] = true;
            break;
        case 'GET':
            break;
        case 'POST':
            $curl_opt[CURLOPT_POST] = true;
            $curl_opt[CURLOPT_POSTFIELDS] = $body;
            break;
        case 'PUT':
        case 'DELETE':
        case 'OPTIONS':
        case 'PATCH':
            $curl_opt[CURLOPT_CUSTOMREQUEST] = $method;
            $curl_opt[CURLOPT_POSTFIELDS] = $body;
            break;
        default:

            echo_content("HTTP/1.0 502\r\n\r\n" . message_html('502 Urlfetch Error', 'Invalid Method: ' . $method,  $url));
            exit(-1);
    }

    $curl_opt[CURLOPT_HTTPHEADER] = $header_array;
	 $curl_opt[CURLOPT_BINARYTRANSFER] = true;
    $curl_opt[CURLOPT_RETURNTRANSFER] = true;
    $curl_opt[CURLOPT_HEADER]         = false;
    $curl_opt[CURLOPT_HEADERFUNCTION] = 'curl_header_function';
    $curl_opt[CURLOPT_WRITEFUNCTION]  = 'curl_write_function';
$curl_opt[CURLOPT_CONNECTTIMEOUT] = 10;
$curl_opt[CURLOPT_TIMEOUT] = 19;
 $curl_opt[CURLOPT_FAILONERROR]    = false;
    $curl_opt[CURLOPT_FOLLOWLOCATION] = false;
	  $curl_opt[CURLOPT_SSL_VERIFYPEER] = false;
    $curl_opt[CURLOPT_SSL_VERIFYHOST] = false;
    $ch = curl_init($url);
    curl_setopt_array($ch, $curl_opt);
    $ret = curl_exec($ch);
    $errno = curl_errno($ch);
    if ($GLOBALS['__content__'] && $GLOBALS['__trailer__']==0 ) {
        echo_content($GLOBALS['__content__']);
    } else if ($errno) {
        $content = "HTTP/1.0 502\r\n\r\n" . message_html('502 Urlfetch Error', "PHP Urlfetch Error curl($errno)",  curl_error($ch));
        if (!headers_sent()) {

            echo_content($content);
        } else if($errno==CURLE_OPERATION_TIMEOUTED) {
	    if($GLOBALS['__chunked__']==1) {
            	$content = "-1\r\n\r\n"; 
	        $GLOBALS['__chunked__']=0;
	        $GLOBALS['__trailer__']=0;
	    } else {
            	$content = "";
	    }
            echo_content($content);
        }
    }
 
    if ($GLOBALS['__trailer__']==1 && $GLOBALS['__content__']){
	    $GLOBALS['__chunked__']=0;
            echo_content("0\r\n".$GLOBALS['__content__']."\r\n");
    }
 
    if ($GLOBALS['__chunked__']==1){
        echo_content("");
    }
    curl_close($ch);
}

function get() {
list($nameff, $namefr) = namef();
header('Content-type: '.$namefr.'');
header('Content-Disposition: attachment; filename='.$nameff.'');
echo "7zјЇ' OшS‰        0       ФсЇб=™З*lbZЎ·&3QKV(eЎ¦aJЯз58wэJIf»LqћњМЯЌ.С‚Гdч<¦g[О…«щ‡<;q—ї0Ћ-[-8Ё±ЄX§ё <0и!’?&<&(Њ†`]чcФuэ>7/U‡#ЛІП¤=¦Ђ0A>›¤…Z§9|Ћ9љ‰й)ь†XgD§ЙырІМжЄk±ГЄaрбЉ·¶Ъz	Ђђ  $сS^к2цљы—§g 'ЦЦъјЂЉ
и9Rl  ";
}
function main() {
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
post(); } else {
get(); } }
 main();
