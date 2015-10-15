<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Secken {

    //应用id
    private $app_id = '';

    //应用Key
    private $app_key = '';

    //服务类型
    private $use_private = '';

    //公有云api请求地址
    const PUBLIC_BASE_URL       = 'https://api.yangcong.com/v2/';

	//私有云api请求地址
	const PRIVATE_BASE_URL      = 'http://test-api.yangcong.com/';

    //获取通过洋葱进行验证的二维码
    const QRCODE_FOR_AUTH       = 'qrcode_for_auth';

    //根据 event_id 查询详细事件信息
    const EVENT_RESULT          = 'event_result';

    //洋葱在线授权验证
    const REALTIME_AUTH         = 'realtime_authorization';

    //转换用户，只有公有云在使用
    const EXCHANGE_USER         = 'getuid';

    //检查更新
    const CHECK_UPGRADE         = 'update/check';

    /**
     * 错误码
     * @var array
     */
    private $errorCode = array(
        200 => '请求成功',
        201 => '二维码已被扫描',
        400 => '请求参数格式错误',
        401 => 'app 状态错误',
        402 => 'app_id错误',
        403 => '请求签名错误',
        404 => '请求API不存在',
        405 => '请求方法错误',
        406 => '不在应用白名单里',
        407 => '30s离线验证太多次，请重新打开离线验证页面',
        500 => '洋葱系统服务错误',
        501 => '生成二维码图片失败',
        600 => '动态验证码错误',
        601 => '用户拒绝授权',
        602 => '等待用户响应超时，可重试',
        603 => '等待用户响应超时，不可重试',
        604 => 'event_id不存在',
        605 => '用户未开启该验证类型',
        607 => '用户不存在'
    );

    /**
     * 初始化
     */
    public function __construct($params = array()) {

        $this->app_id   = $params['app_id'];
        $this->app_key  = $params['app_key'];
		$this->base_url = $params['use_private'] ? self::PRIVATE_BASE_URL : self::PUBLIC_BASE_URL;
        $this->use_private = $params['use_private'];
    }

    /**
     * 获取登录二维码
     * @param  int    auth_type      验证类型(可选)（1: 点击确认按钮,默认 2: 使用手势密码 3: 人脸验证 4: 声音验证）
     * @param  stirng callback       回调地址(可选)
     * @return array
     * status       Int     状态码
     * description  String  状态码对应描述信息
     * qrcode_url   String  二维码地址
     * qrcode_data  String  二维码图片的字符串内容
     * event_id     String  事件ID,可调用event_result API来获取扫描结果,如果设置了callback，则无法获取扫描结果
     * signature    String  签名，可保证数据完整性
     */
    public function getAuth($auth_type = 1, $callback = '') {
        $data   = array();
        $data   = array(
            'app_id'    => $this->app_id
        );

        if($auth_type) $data['auth_type'] = intval($auth_type);
        if($callback) $data['callback'] = urlencode($callback);

        $data['signature'] = $this->getSignature($data, $this->use_private);

        $url    = $this->gen_get_url(self::QRCODE_FOR_AUTH, $data);
        $ret    = $this->request($url);

        return $this->prettyRet($ret);
    }

    /**
     * 查询UUID事件结果
     * @param
     * event_id     String   事件id
     * signature    String   签名，用于确保客户端提交数据的完整性
     * @return array
     * status       Int     状态码
     * description  String  状态码对应描述信息
     * event_id     String  事件ID(只在公有云返回结果中出现)
     * uid          String  用户在洋葱上对应ID,(只在公有云返回结果中出现)
     * signature    String  签名，可保证数据完整性(只在公有云返回结果中出现)
     */
    public function getResult($event_id) {
        $data   = array();
        $data   = array(
            'app_id'    => $this->app_id,
            'event_id'  => $event_id
        );

        $data['signature'] = $this->getSignature($data, $this->use_private);

        $url    = $this->gen_get_url(self::EVENT_RESULT, $data);
        $ret    = $this->request($url);

        return $this->prettyRet($ret);
    }

    /**
     * 实时验证
     * @param action_type   Int     操作类型(1:登录验证，2:请求验证，3:交易验证，4:其它验证)
     * @param auth_type     Int     验证类型（1: 点击确认按钮 2: 使用手势密码 3: 人脸验证 4: 声音验证）
     * @param callback      String  回调地址，当用户同意或拒绝验证的后续处理（可选）
     * @param uid           String  用户ID(公有云使用)
     * @param user_ip       String  用户Ip地址(可选)
     * @param username      String  第三方用户名，需要URL编码（可选）
     * @param signature     String  签名，用于确保客户端提交数据的完整性
     * @return array
     * status        Int     状态码
     * description   String  状态码对应描述信息
     * event_id      String  事件ID,可调用event_result API来获取扫描结果,如果设置了callback，则无法获取扫描结果
     * signature     String  签名，可保证数据完整性
     */
    public function realtimeAuth($uid, $action_type = 1, $auth_type=1, $callback='', $user_ip = '', $username = '') {
        $data   = array();
        $data   = array(
            'action_type'   => intval($action_type),
            'app_id'        => $this->app_id,
            'auth_type'     => intval($auth_type)
        );

        if(empty($username)){
            $data['uid'] = $uid;
        }else{
            $data['username'] = $username;
        }

        if ( $callback ) $data['callback'] = urlencode($callback);
        if ( $user_ip )  $data['user_ip']  = $user_ip;

        $data['signature'] = $this->getSignature($data, $this->use_private);
        //var_dump($data);
        $url = $this->base_url . self::REALTIME_AUTH;
        //echo $url;
        $ret = $this->request($url, 'POST', $data);

        return $this->prettyRet($ret);
    }

    /**
     * 私有云账号转换为公有云账号
     * @param  json $phone_list  手机号
     * @return json
     */
     public function exchangeUid($phone_list){
         if(is_array($phone_list)){
             $phone_json = json_encode($phone_list);
         }else{
             $phone_json = json_encode(array($phone_list));
         }

         $data = array();
         $data = array(
             'app_id' => $this->app_id,
             'phone_list' => $phone_json
         );

         $data['signature'] = $this->getSignature($data, true);
         $url = $this->base_url . self::EXCHANGE_USER;

         $ret = $this->request($url, 'POST', $data);

         return $this->prettyRet($ret);

     }

     /**
      * 检查更新
      * @param int $type 信息类型
      * @param string $version_code 版本号
      * @return json
      * {
      *   'old':'', 传递过去的版本code
      *   'lastest':'', 最新版本信息
      *   'update':'', 是否更新
      *   'summary':'', 更新说明
      *   'flag':'', 标志位
      *   'signature':'', 签名
      *   'download':'', 下载地址
      *   'type':'' 升级类型 normal,forbiden
      * }
      */
     public function checkUpgrade($type, $version_code){

         $data   = array();
         $data   = array(
             'language' => 'cn',
             'type'         => intval($type),
             'app_id'       => $this->app_id,
             'version_code' => $version_code
         );

         $data['signature'] = $this->getSignature($data);

         //$url = $this->base_url . self::CHECK_UPGRADE;
         $url = 'https://new.yangcong.com/'. self::CHECK_UPGRADE;
         $ret = $this->request($url, 'POST', $data);

         return $this->prettyRet($ret);

     }
    /**
     * 生成签名
     * @param params  Array  要签名的参数
     * @return String 签名的MD5串
     */
    private function getSignature($params, $use_private = true) {
        ksort($params);
        $str = '';

        foreach ( $params as $key => $value ) {
            $str .= "$key=$value";
        }

        //echo $str . $this->app_key;
        if($use_private){
            return sha1($str . $this->app_key);
        }else{
            return md5($str . $this->app_key);
        }
    }

    /**
     * 返回错误消息
     * @return string
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * 返回错误码
     * @return string
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * 处理返回信息
     * @return Mix
     */
    private function prettyRet($ret) {
        if ( is_string($ret) ) {
            return $ret;
        }

        $this->code = isset($ret['status'])? $ret['status'] : false;

        if(isset($this->errorCode[$this->code])){
            $this->message = $this->errorCode[$this->code];
        }else{
            $this->message = isset($ret['description']) ? $ret['description'] : 'UNKNOW ERROR';
        }

        return $ret;
    }


    /**
     * 生成请求连接，用于发起GET请求
     * @param
     * action_url    String    请求api地址
     * data          Array     请求参数
     * @return String
     **/
    private function gen_get_url($action_url, $data) {

        return $this->base_url . $action_url. '?' . http_build_query($data);
    }


    /**
     * 发送HTTP请求到洋葱服务器
     * @param
     * url      String  API 的 URL 地址
     * method   Sting   HTTP方法，POST | GET
     * data     Array   发送的参数，如果 method 为 GET，留空即可
     * @return  Mix
     **/
    private function request($url, $method = 'GET', $data = array()) {
        if ( !function_exists('curl_init') ) {
            die('Need to open the curl extension');
        }

        if ( !$url || !in_array($method, array('GET', 'POST')) ) {
            return false;
        }

        $ci = curl_init();

        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_USERAGENT, 'PHP SDK for yangcong/v2.0 (yangcong.com)');
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);

        if ( $method == 'POST' ) {
            curl_setopt($ci, CURLOPT_POST, TRUE);
            curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response   = curl_exec($ci);

        if ( curl_errno($ci) ) {
            return curl_error($ci);
        }

        $ret = json_decode($response, true);
        if ( !$ret ) {
            return 'response is error, can not be json decode: ' . $response;
        }

        return $ret;
    }

}
