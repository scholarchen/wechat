<?php
require_once("wechat.class.php");
class DeviceWechat extends Wechat
{
	const MSGTYPE_DEVICE_TEXT = 'device_text';
	const MSGTYPE_DEVICE_EVENT = 'device_event';
	const MSGTYPE_HARDWARE = 'hardware';
	const MSGTYPE_DEVICE_STATUS = 'device_status';
	const EVENT_BIND = 'bind';
	const EVENT_UNBIND = 'unbind';
	const EVENT_SUBSCRIBE_STATUS = 'subscribe_status';
	const EVENT_UNSUBSCRIBE_STATUS = 'unsubscribe_status';
	const API_DEVICE_PREFIX = 'https://api.weixin.qq.com/device';
	const DEVICE_TRANSMSG_URL = '/transmsg?';
	const DEVICE_CREATE_QRCODE_URL = '/create_qrcode?';
	const DEVICE_AUTHORIZE_DEVICE_URL = '/authorize_device?';
	const DEVICE_GETQRCODE_URL = '/getqrcode?';
	const DEVICE_BIND_URL = '/bind?';
	const DEVICE_UNBIND_URL = '/unbind?';
	const DEVICE_COMPEL_BIND_URL = '/compel_bind?';
	const DEVICE_COMPEL_UNBIND_URL = '/compel_unbind?';
	const DEVICE_GET_STAT_URL = '/get_stat?';
	const DEVICE_VERIFY_QRCODE_URL = '/verify_qrcode?';
	const DEVICE_GET_OPENID_URL = '/get_openid?';
	const DEVICE_GET_BIND_DEVICE_URL = '/get_bind_device?';
		/**
	 * 获取接收消息设备类型
	 */
	public function getRevDeviceType(){
		if (isset($this->_receive['DeviceType']))
			return $this->_receive['DeviceType'];
		else
			return false;
	}
			/**
	 * 获取接收消息设备id
	 */
	public function getRevDeviceID(){
		if (isset($this->_receive['DeviceID']))
			return $this->_receive['DeviceID'];
		else
			return false;
	}
	/**
	 * 获取接收消息OpenID
	 */
	public function getRevOpenID(){
		if (isset($this->_receive['OpenID']))
			return $this->_receive['OpenID'];
		else
			return false;
	}
	/**
	 * 获取接收消息SessionID
	 */
	public function getRevSessionID(){
		if (isset($this->_receive['SessionID']))
			return $this->_receive['SessionID'];
		else
			return false;
	}
	/**
	 * 设置回复消息普通设备通信消息和绑定事件消息
	 * Example: $obj->device_text('hello')->reply();
	 * @param string $text
	 */
	public function device_text($content='')
	{
		$FuncFlag = $this->_funcflag ? 1 : 0;
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_DEVICE_TEXT,
			'DeviceType'=>$this->getRevDeviceType(),
			'DeviceID'=>$this->getRevDeviceID(),
			'SessionID'=>$this->getRevSessionID(),
			'Content'=>$content,
			'FuncFlag'=>$FuncFlag
		);
		$this->Message($msg);
		return $this;
	}
		/**
	 * 第三方可通过消息接口返回特定xml结构  社交功能消息排行榜等
	 * Example: ？？myrank  ranklist
	 * @param ？？
	 */
	public function device_social()
	{
		$FuncFlag = $this->_funcflag ? 1 : 0;
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_HARDWARE,
			'HardWare'=>array(
			        'MessageView'=>$myrank,
			        'MessageAction'=>$ranklist,
			),
			'FuncFlag'=>$FuncFlag
		);
		$this->Message($msg);
		return $this;
	}
	
		/**
	 * 设备状态查询
	 * Example: $obj->device_text('hello')->reply();
	 * @param string $DeviceStatus 0，1
	 */
	public function device_status($devicestatus='1')
	{
		$FuncFlag = $this->_funcflag ? 1 : 0;
		$msg = array(
			'ToUserName' => $this->getRevFrom(),
			'FromUserName'=>$this->getRevTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_DEVICE_STATUS,
			'DeviceType'=>$this->getRevDeviceType(),
			'DeviceID'=>$this->getRevDeviceID(),
			'DeviceStatus'=>$devicestatus,
			'FuncFlag'=>$FuncFlag
		);
		$this->Message($msg);
		return $this;
	}
    public function device_get_router_passids($data)
	{
		$msg = array(
			'errcode' => 0,
			'errmsg'=>"ok"
		);
		$this->Message($msg);
		return $this;
	}
		/**
	 * 主动发消息给设备
	 * @param array $data
	{
		"device_type":"DEVICETYPE",
		"device_id":"DEVICEID",
		"open_id": "OPEN_ID",
		"content": "BASE64编码内容"
	}
	三方主动发送设备状态
	{
		"device_type": "DEVICETYPE",
		"device_id": "DEVICEID",
		"open_id": " OPEN_ID",
		"msg_type": " MSG_TYPE",//2
		"device_status": " DEVICE_STATUS"  //0 未连接 1连接
	}
	 * @return bool|array
	 *{"ret":0,"ret_info":"this is ok"}
	 */
	public function device_transmsg($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_DEVICE_PREFIX.self::DEVICE_TRANSMSG_URL.'access_token='.$this->access_token,self::json_encode($data));
		if($result){
			$json = json_decode($result,true);
			if (!$json || !empty($json['ret'])) {
				$this->errCode = $json['ret'];
				$this->errMsg = $json['ret_info'];
				return false;
			}else if(!isset($json['ret'])){
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
			/**
	 * 获取设备二维码
	 * @param array $data
	{
		"device_num":"2",
		"device_id_list":["01234","56789"]
	}
	 * @return bool|array
	 *
	{
		"errcode":0,
		"errmsg":"succ",
		"device_num":1,
		"code_list":[{"device_id":"id1","ticket":"t1"}]
	}
	 */
	public function device_create_qrcode($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_DEVICE_PREFIX.self::DEVICE_CREATE_QRCODE_URL.'access_token='.$this->access_token,self::json_encode($data));
		if($result){
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
				/**
	 * 设备授权和更新授权信息
	 * @param array $data
	{
		"device_num":"1",
		"device_list":[
		{
			"id":"dev1",
			"mac":"123456789ABC",
			"connect_protocol":"3",
			"auth_key":"",
			"close_strategy":"1",
			"conn_strategy":"1",
			"crypt_method":"0",
			"auth_ver":"1",
			"manu_mac_pos":"-1",
			"ser_mac_pos":"-2",
			"ble_simple_protocol": "0"
		}
		],
		"op_type":"0",
		"product_id": "12222"
	}
	 * @return bool|array
	 *
	 */
	public function device_authorize_device($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_DEVICE_PREFIX.self::DEVICE_AUTHORIZE_DEVICE_URL.'access_token='.$this->access_token,self::json_encode($data));
		if($result){
			$json = json_decode($result,true);
			if (!$json || !empty($json['resp']['errcode'])) {
				$this->errCode = $json['resp']['errcode'];
				$this->errMsg = $json['resp']['errmsg'];
				return false;
			}else if(!isset($json['resp']['errcode'])){
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
	
	/**
	 * 获取新设备授权
	 * @param array $data

	 * @return bool|array
	 *
	 {"base_resp":{"errcode":0,"errmsg":"ok"},"deviceid":"gh_ef4930dd0776_87288553c6565ff3","qrticket":"http:\/\/we.qq.com\/d\/AQAW8QzDuyrBBDGrYONPXzrZKcSPiHXsXrdZBnHR"}
	 {"base_resp":{"errcode":100020,"errmsg":"account quota not enough"}}
	 */
	public function device_getqrcode($product_id=''){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_DEVICE_PREFIX.self::DEVICE_GETQRCODE_URL.'access_token='.$this->access_token.'&product_id='.$product_id);
		if($result){
			$json = json_decode($result,true);
			if(!$json || !empty($json['base_resp']['errcode']))
			{
				$this->errCode = $json['base_resp']['errcode'];
				$this->errMsg = $json['base_resp']['errmsg'];
				return false;
			}else if(!isset($json['base_resp']['errcode']))
			{
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
		/**
	 * 绑定设备
	 * @param array $data
	{
		"ticket": "TICKET",
		"device_id": "DEVICEID",
		"openid": " OPENID"
	}
	 * @return bool|array
	 *

	 */
	public function device_bind($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_DEVICE_PREFIX.self::DEVICE_BIND_URL.'access_token='.$this->access_token,self::json_encode($data));
		if($result){
			$json = json_decode($result,true);
			if(!$json || !empty($json['base_resp']['errcode']))
			{
				$this->errCode = $json['base_resp']['errcode'];
				$this->errMsg = $json['base_resp']['errmsg'];
				return false;
			}else if(!isset($json['base_resp']['errcode']))
			{
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
			/**
	 * 解绑设备
	 * @param array $data
	{
		"ticket": "TICKET",
		"device_id": "DEVICEID",
		"openid": " OPENID"
	}
	 * @return bool|array
	 *

	 */
	public function device_unbind($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_DEVICE_PREFIX.self::DEVICE_UNBIND_URL.'access_token='.$this->access_token,self::json_encode($data));
		if($result){
			$json = json_decode($result,true);
			if(!$json || !empty($json['base_resp']['errcode']))
			{
				$this->errCode = $json['base_resp']['errcode'];
				$this->errMsg = $json['base_resp']['errmsg'];
				return false;
			}else if(!isset($json['base_resp']['errcode']))
			{
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
			/**
	 * 强制绑定设备
	 * @param array $data
	{
		"device_id": "DEVICEID",
		"openid": " OPENID"
	}
	 * @return bool|array
	 *

	 */
	public function device_compel_bind($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_DEVICE_PREFIX.self::DEVICE_COMPEL_BIND_URL.'access_token='.$this->access_token,self::json_encode($data));
		if($result){
			$json = json_decode($result,true);
			if(!$json || !empty($json['base_resp']['errcode']))
			{
				$this->errCode = $json['base_resp']['errcode'];
				$this->errMsg = $json['base_resp']['errmsg'];
				return false;
			}else if(!isset($json['base_resp']['errcode']))
			{
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
			/**
	 * 强制解绑设备
	 * @param array $data
	{
		"device_id": "DEVICEID",
		"openid": " OPENID"
	}
	 * @return bool|array
	 *

	 */
	public function device_compel_unbind($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_DEVICE_PREFIX.self::DEVICE_COMPEL_UNBIND_URL.'access_token='.$this->access_token,self::json_encode($data));
		if($result){
			$json = json_decode($result,true);
			if(!$json || !empty($json['base_resp']['errcode']))
			{
				$this->errCode = $json['base_resp']['errcode'];
				$this->errMsg = $json['base_resp']['errmsg'];
				return false;
			}else if(!isset($json['base_resp']['errcode']))
			{
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
		/**
	 * 设备状态查询
	 * @param device_id=DEVICE_ID

	 * @return bool|array
	 *
	{
		"errcode":0,
		"errmsg":"ok",
		"status":2,
		"status_info":"bind"
	}
	 */
	public function device_get_stat($device_id){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_DEVICE_PREFIX.self::DEVICE_GET_STAT_URL.'access_token='.$this->access_token.'&device_id='.$device_id);
		if($result){
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
			/**
	 * 二维码验证
	 * @param array $data
	{
		"ticket":"QRCODE_TICKET",
	}
	 * @return bool|array
	 *
	{
		"errcode":0,
		"errmsg":"ok",
		"device_type":"gh_xxxxxx",
		"device_id":"DEVICE_ID",
		"mac":"MAC"
	}
	 */
	public function device_verify_qrcode($data){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_post(self::API_DEVICE_PREFIX.self::DEVICE_VERIFY_QRCODE_URL.'access_token='.$this->access_token,self::json_encode($data));
		if($result){
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
		/**
	 * 获取设备绑定openid
	 * @param device_type device_id

	 * @return bool|array
	 *
	{
		"open_id":["omN7ljrpaxQgK4NW4H5cRzFRtfa8","omN7ljtqrTZuvYLkjPEX_t_Pmmlg",],
		"resp_msg":{"ret_code":0,"error_info":"get open id list OK!"}
	}
	 */
	public function device_get_openid($device_type,$device_id){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_DEVICE_PREFIX.self::DEVICE_GET_OPENID_URL.'access_token='.$this->access_token.'&device_type='.$device_type.'&device_id='.$device_id);
		if($result){
			$json = json_decode($result,true);
			if(!$json || !empty($json['resp_msg']['ret_code']))
			{
				$this->errCode = $json['resp_msg']['ret_code'];
				$this->errMsg = $json['resp_msg']['error_info'];
				return false;
			}else if(!isset($json['resp_msg']['ret_code']))
			{
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
		/**
	 * 通过openid获取用户绑定的deviceid
	 * @param openid

	 * @return bool|array
	 *
	{
		"resp_msg": {
			"ret_code": 0,
			"error_info": "ok"
		},
		"openid": "OPENID",
		"device_list": [
			{
				"device_type": "dt1",
				"device_id": "di1"
			}
		]
	}
	 */
	public function device_get_bind_device($openid){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$result = $this->http_get(self::API_DEVICE_PREFIX.self::DEVICE_GET_BIND_DEVICE_URL.'access_token='.$this->access_token.'&openid='.$openid);
		if($result){
			$json = json_decode($result,true);
			if(!$json || !empty($json['resp_msg']['ret_code']))
			{
				$this->errCode = $json['resp_msg']['ret_code'];
				$this->errMsg = $json['resp_msg']['error_info'];
				return false;
			}else if(!isset($json['resp_msg']['ret_code']))
			{
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			return $json;
		}
		return false;
	}
	/**
	 * log overwrite
	 * @see Wechat::log()
	 */
	public function log($log){
		if ($this->debug) {
             if (is_array($log)) $log = print_r($log,true);
            sae_debug($log);
            return true;
		}
		return false;
	}

	/**
	 * 重载设置缓存
	 * @param string $cachename
	 * @param mixed $value
	 * @param int $expired
	 * @return boolean
	 */
	protected function setCache($cachename,$value,$expired){
        
        
        $data->expire_time = time() + $expired;
        $data->$cachename = $value;
        $fp = fopen("saestor://weixin/access_token.json", "w");
        fwrite($fp, json_encode($data));
        fclose($fp);
        log($data);
		return true;
	}

	/**
	 * 重载获取缓存
	 * @param string $cachename
	 * @return mixed
	 */
	protected function getCache($cachename){
         $data = json_decode(file_get_contents("saestor://weixin/access_token.json"));
         if ($data->expire_time < time()) {
             return false;
         }else {
         	return $data->$cachename;
         }
	}

	/**
	 * 重载清除缓存
	 * @param string $cachename
	 * @return boolean
	 */
	protected function removeCache($cachename){
		  $fp = fopen("http://scholar-accesstoken.stor.sinaapp.com/access_token.json", "w");
         fclose($fp);
        return true;
	}

}