<?php
class WeixinController extends Controller{
	public $WxObj;
	public $appID = '';
	public $appsecret = '';
	public $accessToken;
	public function index(){
		$token = 'weixinchen';
		//绑定
		$this->verify($token);

		//获取微信对象
		$DxObj = $this->getWxObj();
		$this->WxObj = $DxObj;

		//获取accesstoken
		$this->accessToken = $this->getAccesstoken();

		//被动回复消息
		if($this->isText()){
			switch(trim($this->WxObj->Content)){
				case 1:
					$this->responseMsg('因吹斯汀');
				break;
				case 2:
					$this->responseMsg($this->accessToken);
				break;
				default:
					$this->responseMsg('没有指定的回复信息');
				break;
			}
		}

		//关注自动回复
		if($this->isSubscribe()){
			$this->responseMsg('欢迎关注~');
		}

		//二维码
		$imgurl = $this->getQRcode();

		//生成自定义菜单
		$this->createNav($imgurl);

		//自定义菜单点击事件
		if($this->isClick()){
			$arr = array(
				array(
					'title'=>'女超人',
					'description'=>'女超人就在CW',
					'picurl'=>'http://img2.imgtn.bdimg.com/it/u=1779716527,1180361689&fm=21&gp=0.jpg',
					'url'=>'http://baike.baidu.com/link?url=K_SEPOeLdeXCi0HeTMDAYELKB5Tt2dBa6RaFyZYesSW7dz8owhCME--Qe05itJ7kwt1veVeW1r4vzbYrIljw3s6AZ0HcYMdK5NwB7vZhossjPX_VoZEbsfvArJ7rQDFK'
				)
			);
			switch($this->WxObj->EventKey){
				case 'AMC':
					$this->responseMsg('美剧我只看AMC');
				break;
				case 'HBO':
					$this->responseMsg('美剧我只看HBO');
				break;
				case 'V1001_GOOD':
					$this->responseNews($arr);
				break;
			}


		}


		//获取用户列表（openid）
		$openidArr = $this->getUserList();


		//获取用户基本信息
		$this->getUserInfo($openidArr);

	}


	/**
	 * 各种方法
	 */


	//二维码
	public function getQRcode(){
		$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->accessToken;
		$json = '{"expire_seconds": 7200, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}';
		$result = $this->https_request($url,$json);
		file_put_contents('QRcode.php',$result);
		$data = json_decode($result,true);
		$imgUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($data['ticket']);
		file_put_contents('QRimage.php',$imgUrl);
		$res = $this->https_request($imgUrl);
		file_put_contents('image.jpg',$res);
		return $imgUrl;
	}


	//获取用户列表(每个用户的openid)
	public function getUserList(){
		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$this->accessToken;
		$result = $this->https_request($url);

		//获取json格式用户openid
		file_put_contents('userList.php',$result);

		//转换为数组
		$data = json_decode($result,true);


		return $data['data']['openid'];
	}




	//获取用户基本信息
	public function getUserInfo($arr){
		$json = '{"user_list": [';
		for($i=0;$i<count($arr);$i++){
			$json .= '{"openid": "'.$arr[$i].'","lang": "zh-CN"},';
		}
		$json = rtrim($json,',');
		$json .= ']}';
		//file_put_contents('userInfo11.php',$json);
// {
// "user_list": [
   // {
	   // "openid": "otvxTs4dckWG7imySrJd6jSi0CWE",
	   // "lang": "zh-CN"
   // },
   // {
	   // "openid": "otvxTs_JZ6SEiP0imdhpi50fuSZg",
	   // "lang": "zh-CN"
   // }

// ]
// }
		$url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=".$this->accessToken;
		$result = $this->https_request($url,$json);
		file_put_contents('userInfo.php',$result);
		return $result;

	}


	//回复图文消息
	public function responseNews($arr){
		$WxObj = $this->WxObj;
		$fromUsername = $WxObj->ToUserName;
        $toUsername = $WxObj->FromUserName;
        $time = time();
		$news=<<<str
<xml>
<ToUserName><![CDATA[{$toUsername}]]></ToUserName>
<FromUserName><![CDATA[{$fromUsername}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>1</ArticleCount>
<Articles>
<item>
<Title><![CDATA[{$arr[0]['title']}]]></Title>
<Description><![CDATA[{$arr[0]['description']}]]></Description>
<PicUrl><![CDATA[{$arr[0]['picurl']}]]></PicUrl>
<Url><![CDATA[{$arr[0]['url']}]]></Url>
</item>
</Articles>
</xml>
str;
	echo $news;
	}

	//判断点击事件
	public function isClick(){
		if($this->WxObj->MsgType=='event'){
			if($this->WxObj->Event=='CLICK'){
				return true;
			}
		}
		return false;
// <xml><ToUserName><![CDATA[gh_47ac47b94859]]></ToUserName>
// <FromUserName><![CDATA[oJVi3vx-gBqaaW6BTUlLjmfl0Hro]]></FromUserName>
// <CreateTime>1474185035</CreateTime>
// <MsgType><![CDATA[event]]></MsgType>
// <Event><![CDATA[CLICK]]></Event>
// <EventKey><![CDATA[HBO]]></EventKey>
// </xml>
	}


	//自定义菜单
	public function createNav($imgurl){
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->accessToken;
		$json = '{
     "button":[
     {
          "type":"view",
          "name":"美剧",
          "url":"https://www.baidu.com"
      },
	  {
          "type":"click",
          "name":"美剧AMC",
          "key":"AMC"
      },
      {
           "name":"美剧CW",
           "sub_button":[
           {
               "type":"view",
               "name":"吸血鬼日记",
               "url":"http://baike.baidu.com/link?url=dGCyfrTsjFsd5-c81qIhXsqp6UGdp0UCnfSTvcUy7rWY1IPs672qEpFXeYQn_wfejorWDyV3IhW4X4ozgg0Y3uGTclZBqZ1Lev8sIumwVTm"
            },
            {
               "type":"view",
               "name":"绿箭侠",
               "url":"http://baike.baidu.com/link?url=3ov0gX3Ip_j_zPaSWrn_cma48PX_XwyCG2U1tRwZqEmbcK38tg92O2zPxx-2dicI7Fy5wuZXWVAlhtnTVh-W7p_YRveSduFYro6HKnE2CKy"
            },
            {
                "type":"click",
               "name":"女超人",
               "key":"V1001_GOOD"
            }]
       }]
 }';

		$this->https_request($url,$json);
	}

	//获取accesstoken
	public function getAccesstoken(){
		if($acessToken = $this->getRedis('accessToken')){
			return $acessToken;
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appID.'&secret='.$this->appsecret;
		$json = $this->https_request($url);
		//把json格式转化为数组
		$arr = json_decode($json,true);
		if(array_key_exists('errcode',$arr) && $arr['errcode']!=0){
			return false;
		}
		$accessToken = $arr['access_token'];
		file_put_contents('accessToken.php',$accessToken);
		//存入redis
		$this->setRedis('accessToken',$accessToken,7000);
		return $accessToken;
	}


	//是否为关注事件
	public function isSubscribe(){
		if($this->WxObj->MsgType=='event'){
			if($this->WxObj->Event=='subscribe'){
				return true;
			}
		}
		return false;

// <xml>
// <ToUserName><![CDATA[gh_47ac47b94859]]></ToUserName>
// <FromUserName><![CDATA[oJVi3vx-gBqaaW6BTUlLjmfl0Hro]]></FromUserName>
// <CreateTime>1474181734</CreateTime>
// <MsgType><![CDATA[event]]></MsgType>
// <Event><![CDATA[subscribe]]></Event>
// <EventKey><![CDATA[]]></EventKey>
// </xml>
	}

	//回复信息
	public function responseMsg($msg){
		$WxObj = $this->WxObj;
		$fromUsername = $WxObj->ToUserName;
        $toUsername = $WxObj->FromUserName;
        $time = time();
        $textTpl=<<<str
<xml>
<ToUserName><![CDATA[{$toUsername}]]></ToUserName>
<FromUserName><![CDATA[{$fromUsername}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[{$msg}]]></Content>
</xml>
str;

	echo $textTpl;


	}


	//判断微信对象是否为文本类型
	public function isText(){
		if($this->WxObj->MsgType=='text'){
			return true;
		}
		return false;

// <xml>
//<ToUserName><![CDATA[gh_47ac47b94859]]></ToUserName>
// <FromUserName><![CDATA[oJVi3vx-gBqaaW6BTUlLjmfl0Hro]]></FromUserName>
// <CreateTime>1474179867</CreateTime>
// <MsgType><![CDATA[text]]></MsgType>
// <Content><![CDATA[65]]></Content>
// <MsgId>6331554317707181587</MsgId>
// </xml>
	}



	//获取微信对象
	public function getWxObj(){
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		file_put_contents('xml.php',$postStr);
		//安全加密
		libxml_disable_entity_loader(true);
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		return $postObj;
	}

	//绑定方法
	public function verify($token){

        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];


		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature && isset($echoStr)){
			echo $echoStr;die;
		}else{
			return false;
		}
	}

}






?>