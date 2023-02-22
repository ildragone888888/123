<?php
 
$__password__ = '345a';
$__hostsdeny__ = array(); // $__hostsdeny__ = array('.youtube.com', '.youku.com');
$__content_type__ = 'image/gif';
 
$__timeout__ = 20;
$__content__ = '';
$__chunked__= 0;
$__trailer__= 0;

function message_html($title, $banner, $detail) {
$error = "$title $banner $detail";
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
    global $__password__, $__content_type__,$__chunked__,$__content__;
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
    
    echo $chunk;
}


function curl_header_function($ch, $header) {
    global $__content__, $__content_type__,$__chunked__;
    $pos = strpos($header, ':');
    if ($pos == false) {
        $__content__ .= $header;
    } else {
        $key = join('-', array_map('ucfirst', explode('-', substr($header, 0, $pos))));
        //if ($key != 'Transfer-Encoding') {
            $__content__ .= $key . substr($header, $pos);
        //}
    }
    if (preg_match('@^Content-Type: ?(audio/|image/|video/|application/octet-stream)@i', $header)) {
        $__content_type__ = 'image/x-png';
    }
    if (!trim($header)) {
        header('Content-Type: ' . $__content_type__);
    }
    if (preg_match('@^Transfer-Encoding: ?(chunked)@i', $header)) {
        $__chunked__ = 1;
    }
    return strlen($header);
}


function curl_write_function($ch, $content) {
    global $__content__,$__chunked__,$__trailer__;
    if ($__content__) {
        // for debug
        // echo_content("HTTP/1.0 200 OK\r\nContent-Type: text/plain\r\n\r\n");
        echo_content($__content__);
        $__content__ = '';
	$__trailer__ = $__chunked__;
    }
    echo_content($content);
    return strlen($content);
}


function post() {
    global $__content_type__;
    list($method, $url, $headers, $kwargs, $body) = @decode_request(@file_get_contents('php://input'));

    $password = $GLOBALS['__password__'];
  
    if ($body) {
        $headers['Content-Length'] = strval(strlen($body));
    }
    

    $header_array = array();
    foreach ($headers as $key => $value) {
        $header_array[] = join('-', array_map('ucfirst', explode('-', $key))).': '.$value;
    }

    $timeout = $GLOBALS['__timeout__'];

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
            header('Content-Type: ' . $__content_type__);
            echo_content("HTTP/1.0 502\r\n\r\n" . message_html('502 Urlfetch Error', 'Invalid Method: ' . $method,  $url));
            exit(-1);
    }

    $curl_opt[CURLOPT_HTTPHEADER] = $header_array;
    $curl_opt[CURLOPT_RETURNTRANSFER] = true;
    $curl_opt[CURLOPT_BINARYTRANSFER] = true;

    $curl_opt[CURLOPT_HEADER]         = false;
    $curl_opt[CURLOPT_HEADERFUNCTION] = 'curl_header_function';
    $curl_opt[CURLOPT_WRITEFUNCTION]  = 'curl_write_function';

    $curl_opt[CURLOPT_FAILONERROR]    = false;
    $curl_opt[CURLOPT_FOLLOWLOCATION] = false;

    $curl_opt[CURLOPT_CONNECTTIMEOUT] = $timeout;
    $curl_opt[CURLOPT_TIMEOUT]        = $timeout;

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
            header('Content-Type: ' . $__content_type__);
            echo_content($content);
        } else if($errno==CURLE_OPERATION_TIMEOUTED) {
	    if($GLOBALS['__chunked__']==1) {
            	$content = "-1\r\n\r\n";//fake chunked end flag
	        $GLOBALS['__chunked__']=0;
	        $GLOBALS['__trailer__']=0;
	    } else {
            	$content = "";
	    }
            echo_content($content);
        }
    }
    //when chunked there may be trailer
    if ($GLOBALS['__trailer__']==1 && $GLOBALS['__content__']){
	    $GLOBALS['__chunked__']=0;
            echo_content("0\r\n".$GLOBALS['__content__']."\r\n");
    }
    //normal chunked end
    if ($GLOBALS['__chunked__']==1){
        echo_content("");
    }
    curl_close($ch);
}

function get() {
   echo "123";
}


function main() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        post();
    } else {
        get();
    }
}

main();
