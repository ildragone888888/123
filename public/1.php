<?php
$__content__ = '';
  
function message_html($title, $banner) {
$error = "<title>".$title."</title>".$banner."";
return $error;
}
function decode_request($data) {
return $data;
}
function echo_content($content) {
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
function post($url) {
$body = file_get_contents('php://input');
$header_array = array();
$curl_opt = array();
$ch = curl_init();
$curl_opt[CURLOPT_URL] = $url;
$curl_opt[CURLOPT_CUSTOMREQUEST] = "GET";
//$curl_opt[CURLOPT_POSTFIELDS] = $body;
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
curl_close($ch);
exit;
}
curl_close($ch);
if ($GLOBALS['__content__']) {
echo_content($GLOBALS['__content__']);
} 
}
function get() {
echo "Запрос get";
}
post("https://google.com/");
//function main() {
//if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//post(); } else {
//get(); } }
//main(); 
