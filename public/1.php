<?php
$__content__ = '';

function echo_content($content) {
global $__password__;
list($nameff, $namefr) = namef();

echo $content;
}
function curl_header_function($ch, $header) {
global $__content__;
$pos = strpos($header, ':');
if ($pos == false) {
$__content__ .= $header;
} 
else {
$key = join('-', array_map('ucfirst', explode('-', substr($header, 0, $pos))));
if ($key != 'Transfer-Encoding') {
$__content__ .= $key . substr($header, $pos);
}
}
return strlen($header);
}
function curl_write_function($ch, $content) {
global $__content__;
if ($__content__) {
echo_content($__content__);
$__content__ = '';
}
echo_content($content);
return strlen($content);
}
 
function post($method, $url, $body) {

$curl_opt = array();
$ch = curl_init();
$curl_opt[CURLOPT_URL] = $url;
switch (strtoupper($method)) {  
case 'GET':
break;
case 'HEAD':
$curl_opt[CURLOPT_NOBODY] = true;
break;
case 'OPTIONS':
case 'TRACE':
$curl_opt[CURLOPT_CUSTOMREQUEST] = $method;
break;
case 'POST':
case 'PATCH':
case 'PUT':
case 'DELETE':
$curl_opt[CURLOPT_CUSTOMREQUEST] = $method;
if ($body) {
$curl_opt[CURLOPT_POSTFIELDS] = $body;
}
break;
default:
echo_content("HTTP/1.0 502\r\n\r\n" . message_html('502 Urlfetch Error', 'Method error ' . $method));
exit(-1);
}
$curl_opt[CURLOPT_HTTPHEADER] = $header_array;
$curl_opt[CURLOPT_RETURNTRANSFER] = true;
$curl_opt[CURLOPT_CONNECTTIMEOUT] = 10;
$curl_opt[CURLOPT_TIMEOUT] = 19;
$curl_opt[CURLOPT_HEADERFUNCTION] = 'curl_header_function';
$curl_opt[CURLOPT_WRITEFUNCTION]  = 'curl_write_function';
$curl_opt[CURLOPT_SSL_VERIFYPEER] = false;
$curl_opt[CURLOPT_SSL_VERIFYHOST] = false;
curl_setopt_array($ch, $curl_opt);
curl_exec($ch);
if (curl_errno($ch)) {
$error0 = curl_error($ch);
echo_content("HTTP/1.0 502\r\n\r\n" . message_html('502 Urlfetch Error', 'URL error ' . $error0));
}
curl_close($ch);
if ($GLOBALS['__content__']) {
echo_content($GLOBALS['__content__']);
} 
}
$body = '';
post("GET", "https://google.com/", $body);
