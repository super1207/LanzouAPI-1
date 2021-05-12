<?php
function getUrlPage($url)
{
    $context_options = array(
        'http' => array(
        'method' => "GET",
        'header' => "User-Agent:Mozilla/5.0 (Linux; Android 5.1.1; OPPO R9 Plustm A Build/LMY47V; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/55.0.2883.91 Mobile Safari/537.36 Html5Plus/1.0\r\n",
    ));
    $context = stream_context_create($context_options);
    $fh= file_get_contents($url,FALSE,$context);
    if($fh == NULL){
        return array("retCode" => -1,"retStr" => "open {$url} failed");
    }else{
        return array("retCode" => 0,"retStr" => $fh);
    }
}

function getLocationUrl($tempUrl)
{
    $tempUrl = str_replace("https://","http://",$tempUrl);
    $contextOptions = array(
        'http' => array(
        'method' => "HEAD",
        'header' => "Accept-Language:zh-CN,zh;q=0.8\r\n",
        'follow_location' => 0
    ));
    //$context = stream_context_create($contextOptions);
	stream_context_set_default($contextOptions);
    $headers = get_headers($tempUrl,1);
    if ($headers && array_key_exists("Location",$headers)){
	return array("retCode" => 0,"retStr" => $headers['Location']);
        //return getLocationUrl($headers['Location']);
    }
    return array("retCode" => 0,"retStr" => $tempUrl);
}

function gerUrlFromPage($urlPage)
{
    $list = explode("\n",$urlPage);
    $cdomain = "";
    $sts = "";
    foreach($list as $v){
        if(!(strpos($v,"var domianload = ") === FALSE)){
            $tempStr  = str_replace(" ","",$v);
            $tempStr = str_replace("'","",$tempStr);
            $tempStr = str_replace(";","",$tempStr);
            $tempStr = explode("=",$tempStr)[1];
            $cdomain = $tempStr;
        }
        if(!(strpos($v,"var downloads =") === FALSE)){
            $tempStr  = str_replace(" ","",$v);
            $tempStr = str_replace("'","",$tempStr);
            $tempStr = str_replace(";","",$tempStr);
            $tempStr = explode("=",$tempStr)[1];
            $tempStr = str_replace("domianload+","",$tempStr);
            $sts = $tempStr;
        }
    }
    if($cdomain == ""){
        return array("retCode" => -1,"retStr" => $urlPage);
    }
    if($sts == ""){
        return array("retCode" => -1,"retStr" => "not fine sts");
    }
    return getLocationUrl($cdomain . $sts);
}

function getLanzousUrl($sharedUrl)
{
    if(strpos($sharedUrl,".lanzous.com") === FALSE && strpos($sharedUrl,".lanzoux.com") === FALSE){
        return array("retCode" => -1,"retStr" => "It is not lanzous shared url");
    }
    if(strpos($sharedUrl,"com/tp/") === FALSE){
        $sharedUrl = str_replace("com/","com/tp/",$sharedUrl);
    }
    $sharedUrl = $sharedUrl . "?p=1";
    if(strpos($sharedUrl,"http") === FALSE){
        $sharedUrl = "http://" . $sharedUrl;
    }
    $sharedUrl = str_replace("https://","http://",$sharedUrl);
    $arr = getUrlPage($sharedUrl);
    if($arr["retCode"] == 0){
        return gerUrlFromPage($arr["retStr"]);
    }else{
        return array("retCode" => $arr["retCode"],"retStr" => $arr["retStr"]);
    }
}

function isHttps() {
	if ( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
		return true;
	} elseif ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
		return true;
	} elseif ( !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
		return true;
	}
	return false;
}

function phpSelf(){
    $ret=substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/'));
    return $ret;
}
//echo getUrlPage("https://www.google.com")["retStr"];
if(array_key_exists("url",$_GET))
{
	
	$ret = getLanzousUrl($_GET["url"]);
	header('Access-Control-Allow-Origin: *');
	header('Timing-Allow-Origin: *');
	if($ret["retCode"] == 0){
		header('Location: '.$ret["retStr"]);
	}else{
		echo "get lanzou's url failed:".$ret["retStr"];
	}
}else{	
	echo "this is a lanzou's download help tool,example usage:<br />";
	$method = $_SERVER['HTTP_HOST'].phpSelf()."?url=https://wwa.lanzous.com/iPHgRfi417a"."<br />";
	if(!isHttps()){
		echo "http://".$method;
	}else{
		echo "https://".$method;
	}
	echo "2020/08/11";
}
