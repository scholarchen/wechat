<?php
/**
 * 微信公众平台 PHP SDK 示例文件
 *
 * @author NetPuter <netputer@gmail.com>
 */
  require_once("config.php");
  require_once('../src/device.class.php');
  require_once('../src/robot.php');
//根据收到的数据进行处理

$weObj = new DeviceWechat($options);
//$weObj->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败
$type = $weObj->getRev()->getRevType();
$weObj->log($_SERVER['REQUEST_URI']);
switch($type) {
	case Wechat::MSGTYPE_TEXT:
	
    $content=process_text($weObj);
		$weObj->text($content)->reply();
			break;
	case Wechat::MSGTYPE_IMAGE:
    		$data= $weObj->getRevPic();
    //$weObj->image($data["mediaid"])->reply();
   		 $weObj->text($data["picurl"])->reply();
			break;
	case Wechat::MSGTYPE_LOCATION:
			break;
	case Wechat::MSGTYPE_LINK:
			break;
	case Wechat::MSGTYPE_EVENT:
			break;
	case Wechat::MSGTYPE_MUSIC:
			break;
	case Wechat::MSGTYPE_NEWS:
			break;
	case Wechat::MSGTYPE_VOICE:
			break;
	case Wechat::MSGTYPE_VIDEO:
			break;
	case Wechat::MSGTYPE_SHORTVIDEO:
			break;
	//硬件设备消息
	case DeviceWechat::MSGTYPE_DEVICE_TEXT:
    	$content=process_device_text($weObj);
    	$weObj->device_text($content)->reply();
			break;
	case DeviceWechat::MSGTYPE_DEVICE_EVENT:
        switch($weObj->getRevEvent()){
                case DeviceWechat::EVENT_SUBSCRIBE_STATUS:
            	$status=process_device_status($weObj);
            	$weObj->device_status($status)->reply();
                break;
                case DeviceWechat::EVENT_UNSUBSCRIBE_STATUS:
                break;
        }
			break;
	case DeviceWechat::MSGTYPE_HARDWARE:
			break;
	case DeviceWechat::MSGTYPE_DEVICE_STATUS:
			break;
    case 'get_router_passids':
			$data='';
			$weObj->device_get_router_passids($data)->reply();
			//$weObj->test('get_router_passids')->reply();
			break;
	case 'add_router_passids':
			$data='';
			$weObj->device_get_router_passids($data)->reply();
			//$weObj->test('get_router_passids')->reply();
			break;
	default:
			$weObj->text("unknown msgtype".$type)->reply();
}

 function process_text(DeviceWechat $wechat){
     $data=array(from=>$wechat->getRevFrom(),
     			content=>$wechat->getRevContent());
       $res=rebot_response($data);
	return $res['res'];
}
 function process_device_text(DeviceWechat $wechat){
	return  $wechat->getRevContent();
}
 function process_device_status(DeviceWechat $wechat){
	return  '1';
}
