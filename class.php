<?php
class Trimepay {
    
    private $appId;
    private $appSecret;
    /*
	 * @name	初始化
	 * @param	appId 商户AppId
	 * @param	appSecret 商户appSectet
	 * @return
	 */

	public function __construct($appId, $appSecret) {
	    $this->appId = $appId;
		$this->appSecret = $appSecret;
		$this->gatewayUri = 'https://api.trimepay.com/gateway/pay/go';
		$this->refundUri = 'https://api.trimepay.com/gateway/refund/go';
		$this->preUri = 'https://api.trimepay.com/gateway/pay/pre';
	}
	
	/*
	 * @name	准备签名
	 * @param	data 签名数据
	 * @return  排列后的签名数据
	 */
	public function prepareSign($data) {
		ksort($data);
		return http_build_query($data);
	}

	/**
	 * @name	生成签名
	 * @param	data
	 * @return	签名数据
	 */
	public function sign($data) {
		$signature = strtolower(md5(md5($data).$this->appSecret));
		return $signature;
	}

	/*
	 * @name	验证签名
	 * @param	data 签名数据
	 * @param	signature 签名
	 * @return
	 */
	public function verify($data, $signature) {
		$mySign = $this->sign($data);
		if ($mySign === $signature) {
			return true;
		} else {
			return false;
		}
	}
	
	/*
	 * @name	下单
	 * @param	data 下单数据
	 * @return  Array
	 */
	public function create($data){
        	return $this->post($data);
	}
	
	/*
	 * @name	POST方法
	 * @param	data 发送数据
	 * @param	url 发送url
	 * @return  Array
	 */
    private function post($data, $url = ''){
        if($url == '') {
            $url = $this->gatewayUri;
        }
        
        $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		$data = curl_exec($curl);
		curl_close($curl);
		return json_decode($data, true);
    }
    
    /*
	 * @name	退款接口
	 * @param	callbackTradeNo 网关回调单号
	 * @return
	 */
    public function refund($callbackTradeNo) {
        $params['callbackTradeNo'] = $callbackTradeNo;
        $params['appId'] = $this->appId;
        $prepareSign = $this->prepareSign($params);
        $params['sign'] = $this->sign($prepareSign);
        return $this->post($params, $this->refundUri);
    }
    
    /*
	 * @name	预下单
	 * @param	data 下单数据
	 * @return  Array
	 */
    public function pre($data){
        return $this->post($data, $this->preUri);
    }
}
