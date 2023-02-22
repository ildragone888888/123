<?php
$__content__ = '';
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
function message_html($title, $banner) {
$error = "<title>".$title."</title>".$banner."";
return $error;
}
function decode_request($data) {
global $__password__;
list($headers_length) = array_values(unpack('n', substr($data, 0, 2)));
$headers_data = substr($data, 2, $headers_length);
$headers_data  = $headers_data ^ str_repeat($__password__, strlen($headers_data)); 
$headers_data = gzinflate($headers_data);
$lines = explode("\r\n", $headers_data); 
$request_line_items = explode(" ", array_shift($lines)); 
$method = $request_line_items[0];
$url = $request_line_items[1];
$kwargs  = array();
$kwargs_prefix = 'X-URLFETCH-';
$header_array = array();
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
$header_array[] = join('-', array_map('ucfirst', explode('-', $key))).': '.$value;
}
}
$body = substr($data, 2+$headers_length);
if ($body) { 
$body  = $body ^ str_repeat($__password__, strlen($body));
$body = gzinflate($body);
}
$__password__ = $kwargs['password'];
return array($method, $url, $header_array, $body);
}
function echo_content($content) {
global $__password__;
 list($nameff, $namefr) = namef();
header('Content-type: '.$namefr.'');
header('Content-Disposition: attachment; filename='.$nameff.'');
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
$data = file_get_contents('php://input');

$header_array = array();

$header_array['Content-Type'] = 'application/octet-stream'; 

if ($data) {
$header_array['Content-Length'] = strval(strlen($data));
}

 

$curl_opt = array();
$ch = curl_init();
$curl_opt[CURLOPT_URL] = $url;
$curl_opt[CURLOPT_CUSTOMREQUEST] = "GET";
$curl_opt[CURLOPT_POSTFIELDS] = $data;
$curl_opt[CURLOPT_HTTPHEADER] = $header_array;
$curl_opt[CURLOPT_RETURNTRANSFER] = true;
//$curl_opt[CURLOPT_CONNECTTIMEOUT] = 10;
//$curl_opt[CURLOPT_TIMEOUT] = 19;
$curl_opt[CURLOPT_HEADERFUNCTION] = 'curl_header_function';
$curl_opt[CURLOPT_WRITEFUNCTION]  = 'curl_write_function';
//$curl_opt[CURLOPT_SSL_VERIFYPEER] = false;
//$curl_opt[CURLOPT_SSL_VERIFYHOST] = false;
curl_setopt_array($ch, $curl_opt);
curl_exec($ch);
if (curl_errno($ch)) {
$error0 = curl_error($ch);
exit;
}
curl_close($ch);
if ($GLOBALS['__content__']) {
echo_content($GLOBALS['__content__']);
} 
}
 if ($_SERVER['REQUEST_METHOD'] == 'GET') {
 post("https://google.com/"); 
echo "\n";
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}
  }
 else 
 {
echo "1";
 }
