<?php
class Controller{
	//��accesstoken����redis
	public function setRedis($key,$val,$time){
		$redis = new redis();
		$redis->connect('127.0.0.1',6379);
		$redis->set($key,$val,$time);
	}
	
	//��accesstoken��redis���ó�
	public function getRedis($key){
		$redis = new redis();
		$redis->connect('127.0.0.1',6379);
		if($redis->get($key)){
			return $redis->get($key);
		}
	}
	
	
	// curl�������ܣ���ȡԶ��url�����Լ���������
    public function https_request($url, $data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

    //�趨Ϊ����֤֤���host
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        //�� curl_exec() ��ȡ����Ϣ���ļ�������ʽ���أ�������ֱ�����
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}

?>