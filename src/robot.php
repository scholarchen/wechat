<?php>
define("TL_ROBOT_API","http://www.tuling123.com/openapi/api?key=727579dafec5439e8580d477b94819d9&");
define("WECHAT_MAX_RESPONSE_LEN" , 2048);    
    
/*
 * 注意：info字段需要urlencode编码
 * $postObj是微信返回的数据，我对它进行了二次封装
 */

 function rebot_response($postObj){
    $r["r"] = true;
    $response = getWebCont(TL_ROBOT_API . "userid=" . $postObj['from'] . '&info=' . urlencode($postObj['content']));
    if(!$response){
        $r['res'] = "我无法理解你的问题。抱歉。";
        return $r;
    }

    $json = json_decode($response);

    if(!is_object($json) || !property_exists($json, "code")){
        $r['res'] = "我无法理解你的问题。抱歉。";
        return $r;
    }

    //$r["r"] = true;
    switch($json->code){
        //文本类数据
        case 100000:
            $tmp = $json->text;
            break;
        //网址类数据 打开百度
        case 200000:
            $tmp = $json->text . "\n" . $json->url;
            break;
        //菜谱  红烧肉怎么做？
        case 308000:
            $tmp = $json->text . "\n\n";

            foreach($json->list as $kv){
                $t = WXResponseHelper::buildHref($kv->name,$kv->detailurl,false);
                $t .= "(" . $kv->info . ")";
                $t .= "\n\n";

                if(!WXResponseHelper::maxLen($tmp, $t)){
                    $tmp .= $t;
                }else{
                    break;
                }
            }

        break;

        //列车信息  深圳到成都的火车
        case 305000:
            $tmp = $json->text . "\n\n";
            foreach($json->list as $kv){
                $t = $kv->trainnum . "\n";
                $t .= $kv->start . "(" . $kv->starttime . ")" . " → " . $kv->terminal . "(" . $kv->endtime . ")";
                $t .= "\n\n";

                if(!WXResponseHelper::maxLen($tmp, $t)){
                    $tmp .= $t;
                }else{
                    break;
                }
            }
            break;
        //航班 明天成都飞深圳的飞机
        case 306000:
            $tmp = $json->text . "\n\n";
            foreach($json->list as $kv){
                $t = $kv->starttime . " - " . $kv->endtime . "  " . $kv->flight . "\n\n";
                if(!WXResponseHelper::maxLen($tmp, $t)){
                    $tmp .= $t;
                }else{
                    break;
                }
            }
            break;
        //酒店 深圳南山区附近的酒店
        case 309000:
            $tmp = $json->text . "\n\n";
            foreach($json->list as $kv){
                $t = $kv->price . "  " . $kv->satisfaction . "  " . WXResponseHelper::buildHref($kv->name,$kv->icon) . "\n";
                if(!WXResponseHelper::maxLen($tmp, $t)){
                    $tmp .= $t;
                }else{
                    break;
                }
            }
            break;
        //商品价格 惠人榨汁机多少钱
        case 311000:
            $tmp = $json->text . "\n\n";
            foreach($json->list as $kv){
                $t = $kv->price . "  " . WXResponseHelper::buildHref($kv->name,$kv->detailurl) . "\n\n";
                if(!WXResponseHelper::maxLen($tmp, $t)){
                    $tmp .= $t;
                }else{
                    break;
                }
            }
            break;
        //新闻 最新新闻
        case 302000:
            $tmp = $json->text . "\n\n";
            foreach($json->list as $kv){
                $t = WXResponseHelper::buildHref($kv->article,$kv->icon) . "(" . $kv->source . ")" . "\n\n";
                if(!WXResponseHelper::maxLen($tmp, $t)){
                    $tmp .= $t;
                }else{
                    break;
                }
            }
            break;

        case 40001:
            $tmp = "key的长度错误（32位）";
            break;
        case 40002:
            $tmp = "请求内容为空";
            break;
        case 40003:
            $tmp = "key错误或帐号未激活";
            break;
        case 40004:
            $tmp = "当天请求次数已用完";
            break;
        case 40005:
            $tmp = "暂不支持该功能";
            break;
        case 40006:
            $tmp = "服务器升级中";
            break;
        case 40007:
            $tmp = "服务器数据格式异常";
            break;
        case 50000:
            $tmp = "机器人设定的“学用户说话”或者“默认回答”";
            break;
        default:
            $tmp = "我无法理解你的问题。抱歉。";
            break;
    }

    $r['res'] = $tmp;
    return $r;
}
function getWebCont($url, $data = '', $sslVerify = false) {
	$ch = curl_init();
	if ($sslVerify) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if ($data) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
	$body = curl_exec($ch);
	$head = curl_getinfo($ch);
	$error = curl_error($ch);
	curl_close($ch);
	if ($head['http_code'] == '200') {
		return $body;
	} else {
		//log here 
		return false;
	}
}
class WXResponseHelper {

	public static function buildHref($txt, $link, $blank = true){
		if($link == "")
			return $txt;

		return sprintf("<a href=\"%s\" %s >%s</a>", $link, ($blank ? "target=\"_blank\"" : ""), $txt);
	}

	public static function maxLen($allText, $text){
		$len = strlen($text);
		$total_bytes = strlen($allText);
		$total_bytes += $len;
		return $total_bytes < WECHAT_MAX_RESPONSE_LEN ? false : true;
	}

}

