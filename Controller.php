<?php
class Controller{
	//将accesstoken存入redis
	public function setRedis($key,$val,$time){
		$redis = new redis();
		$redis->connect('127.0.0.1',6379);
		$redis->set($key,$val,$time);
	}
	
	//将accesstoken从redis中拿出
	public function getRedis($key){
		$redis = new redis();
		$redis->connect('127.0.0.1',6379);
		if($redis->get($key)){
			return $redis->get($key);
		}
	}
	
	
	// curl函数功能：获取远程url内容以及传输数据
    public function https_request($url, $data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

    //设定为不验证证书和host
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        //将 curl_exec() 获取的信息以文件流的形式返回，而不是直接输出
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}

?>