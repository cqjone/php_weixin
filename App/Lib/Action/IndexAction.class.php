<?php
header("Content-Type:text/html; charset=utf-8");
class IndexAction extends Action {
	protected $AppID = "wx60ed01dc892a54fc";
	protected $AppSecret = "ab692fb1a88d800799c9ad06acffd206";
	protected $token = 'fhjdskancklasjhfsljkglbjsajkfl';
	public function index() {
		//三个参数及微信生成的密令
		$timestamp = $_GET['timestamp'];
		$nonce = $_GET['nonce'];
		$signature = $_GET['signature'];
		$echostr = $_GET['echostr'];
		$array = array($timestamp, $nonce, $this->token);
		//将数组进行字典排序
		sort($array);
		//将数组组成字符串
		$tmpstr = implode('', $array);
		//对字符串进行sha1加密
		$tmpstr = sha1($tmpstr);
		//判断是否是微信
		if ($tmpstr == $signature && $echostr) {
			//第一次验证
			echo $echostr;
			//第二次就不一样
			exit;
		} else {
			//转到回复消息
			$this->responseMsg();
		}
	}
    //curl get
    public function crulGet($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch,CURLOPT_HEADER,0);
        $output = curl_exec($ch);
        curl_close( $ch );
        if($output === FALSE ){
            return "CURL Error:".curl_error($ch);
        }else {
            return json_decode($output, true);
        }
    }
	//curl get和post请求方法
	public function http_crul($url, $type = 'get', $res = 'json', $arr = '') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($type == 'post') {
			curl_setopt($ch, CURLOPT_POST, 1);
			// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		$output = curl_exec($ch);
		// curl_close( $ch );
		if ($res == 'json') {
			if (curl_errno($ch)) {
				return curl_errno($ch);
			} else {
				return json_decode($output, true);
			}
		}
		// echo var_dump( $output );
	}
	//通过url抓取网页
	public function http_curlbaidu() {
		$url = 'http://www.baidu.com';
		$output = $this->http_crul($url);
		var_dump($output);
	}

	//获取access_token
	public function getAccess_token() {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->AppID . "&secret=" . $this->AppSecret;
		$res = $this->crulGet($url);
		var_dump($res);
		return $res;
	}
	////或取微信服务器的Ip地址
	//需要用到上面的access_token
	public function getWeixinIp() {
		$access_token = $this->getAccess_token();
		$access_token = $access_token['access_token'];
		$url = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=" . $access_token;
		$res = $this->crulGet($url);
		var_dump($res);
		return $res;
	}

	//网页授权接口
	//1.基本类型   2.详细类型
	//base openid
	public function webAccess() {
		$redirect_uri = urlencode("http://www.cqjoneco.com/weixin/index.php/Index/getUserOpenId");
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $AppID . "&redirect_uri=" . $redirect_uri . "&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
		header('location:' . $url);
	}

	//获取用户的openid
	public function getUserOpenId() {
		$code = $_GET['code'];
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $AppID . "&secret=" . $AppSecret . "&code=" . $code . "&grant_type=authorization_code";
		$res = $this->http_crul($url);
		var_dump($res);
		return $res;
	}

	//获取用户的详细信息
	//userinfo
	public function getUerAll() {
		$redirect_uri = urlencode("http://www.cqjoneco.com/weixin/index.php/Index/getUserInfo");
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $AppID . "&redirect_uri=" . $redirect_uri . "&response_type=code&scope=snsapi_userinfo&state=246#wechat_redirect";
		header('location:' . $url);
	}
	public function getUserInfo() {
		$appid = "wx60ed01dc892a54fc";
		$appsecret = "ab692fb1a88d800799c9ad06acffd206";
		$code = $_GET['code'];
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appid . "&secret=" . $appsecret . "&code=" . $code . "&grant_type=authorization_code";
		$res = $this->http_crul($url);
		$access_token = $res['access_token'];
		$openid = $res['openid'];
		$urla = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
		$resa = $this->http_crul($urla);
		var_dump($resa);
		return $resa;
	}

	function getJsApiTicket() {
		//判断缓存
		if ($_SESSION['jsapi_ticket_expire_time'] > time() && $_SESSION['jsapi_ticket']) {
			$jsapi_ticket = $_SESSION['jsapi_ticket'];
		} else {
			$access_token = $this->getAccess_token();
			$access_token = $access_token['access_token'];
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $access_token . "&type=jsapi";
			$res = $this->httpCurl($url);
			$arr = json_decode($res, true);
			$jsapi_ticket = $arr['ticket'];
			$_SESSION['jsapi_ticket'] = $jsapi_ticket;
			$_SESSION['jsapi_ticket_expire_time'] = time() + 7000;
		}
		return $jsapi_ticket;
	}

	//随机数
	function getRandCode($num = 16) {
		$array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$tmpstr = "";
		$max = count($array);
		for ($i = 0; $i <= $num; $i++) {
			$key = rand(1, $max - 1);
			$tmpstr .= $array[$key];
		}
		return $tmpstr;
	}

	//分享接口//扫描二维码/选取图片
	public function shareWeixin() {
		//获取全局的jsapi_ticket票据
		$jsapi_ticket = $this->getJsApiTicket();
		$timestamp = time();
		$noncestr = $this->getRandCode();
		$url = "http://www.cqjoneco.com/weixin/index.php/Index/shareWeixin";
		$signature = "jsapi_ticket=" . $jsapi_ticket . "&noncestr=" . $noncestr . "&timestamp=" . $timestamp . "&url=" . $url;
		$signature = sha1($signature);
		$this->assign('name', "分享测试页面哈哈哈");
		$this->assign('jsapi_ticket', $jsapi_ticket);
		$this->assign('url', $url);
		$this->assign('timestamp', $timestamp);
		$this->assign('noncestr', $noncestr);
		$this->assign('signature', $signature);
		$this->display('share');
	}

	//获取access_token并放入session
	public function getACTOKEN() {
		if ($_SESSION['access_token'] && $_SESSION['expire_time'] > time()) {
			var_dump($_SESSION['access_token']);
			return $_SESSION['access_token'];
		} else {
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $AppID . "&secret=" . $AppSecret;
			$res = $this->http_crul($url, 'get', 'json');
			$access_token = $res['access_token'];
			$_SESSION['access_token'] = $access_token;
			$_SESSION['expire_time'] = time() + 7000;
			var_dump($access_token);
			return $access_token;
		}
	}

	//创建自定义菜单
	// public function createItem(){
	//     $access_token=$this->getACTOKEN();
	//     // echo "<hr/>";
	//     $url=" https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;

	// }

	public function definedItem() {
		//创建微信菜单
		//目前微信接口的调用方式都是通过curl post/get
		header('content-type:text/html;charset=utf-8');
		echo $access_token = $this->getACTOKEN();
		echo '<br/>';
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
		$postArr = array(
			'button' => array(
				array(
					'name' => urlencode('菜单一'),
					'type' => 'click',
					'key' => 'item1',
				), //第一个一级菜单
				array(
					'name' => urlencode('菜单二'),
					'sub_button' => array(
						array(
							'name' => urlencode('歌曲'),
							'type' => 'click',
							'key' => 'songs',
						), //第一个二级菜单
						array(
							'name' => urlencode('电影'),
							'type' => 'view',
							'url' => 'http://www.qq.com',
						), //第二个二级菜单
					),
				), //第二个一级菜单
				array(
					'name' => urlencode('菜单三'),
					'type' => 'view',
					'url' => 'http://www.qq.com',
				), //第三个一级菜单
			),

		);
		echo "<hr/>";
		var_dump($postArr);
		echo "<hr/>";
		echo $postJson = urldecode(json_encode($postArr));
		$res = $this->ht_cl($url, 'post', 'json', $postJson);
		echo "<hr/>";
		var_dump($res);
	}

	//接收事件推送并回复
	public function responseMsg() {
		//获取微信推送过来的post数据（xml格式）
		$postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
		$postObj = simplexml_load_string($postArr);
		$toUser = $postObj->FromUserName;
		$fromUser = $postObj->ToUserName;
        $time=time();
        $MsgType = 'text';
        $template = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
        if (strtolower($postObj->MsgType) == 'event'&&strtolower($postObj->Event) == 'subscribe') {
                $Content = '您好！欢迎关注我的微信测试开发账号！';
                echo $info = sprintf($template, $toUser, $fromUser, $time, $MsgType, $Content);
        }else if (strtolower($postObj->MsgType) == 'text') {
            switch ($postObj->Content) {
                case 'hello':
                    $content='你好';
                    break;
                default:
                    $content='欢迎光临！';
                    break;
            }
            echo sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
        }
        // $time=time();
        // $template = "<xml>
        //     <ToUserName><![CDATA[%s]]></ToUserName>
        //     <FromUserName><![CDATA[%s]]></FromUserName>
        //     <CreateTime>%s</CreateTime>
        //     <MsgType><![CDATA[%s]]></MsgType>
        //     <Content><![CDATA[%s]]></Content>
        //     </xml>";
        // switch ($postObj->MsgType) {
        //     case 'event':
        //         $this->guanzhu();
        //         break;
        //     default:
        //         $this->moren();
        //         break;
        // }
        // function moren(){
        //     $msgType='text';
        //     $content=$toUser.'您好！';
        //     echo sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
        // }
        // function guanzhu(){
        //     if (strtolower($postObj->Event) == 'subscribe') {
        //         $MsgType = 'text';
        //         $Content = $toUser.'您好！欢迎关注我的微信公众账号！';
        //         //解析上面的字符串模板将各个变量的值依次传入
        //         echo $info = sprintf($template, $toUser, $fromUser, $time, $MsgType, $Content);
        //     }
        // }

        //判断该数据包是否是订阅的事件推送(转为小写进行判断)
        // if (strtolower($postObj->MsgType) == 'event') {
        //     //如果是关注事件
        //     if (strtolower($postObj->Event) == 'subscribe') {
        //         $toUser = $postObj->FromUserName;
        //         $fromUser = $postObj->ToUserName;
        //         $time = time;
        //         $MsgType = 'text';
        //         $Content = $toUser.'您好！欢迎关注我的微信公众账号！';
        //         $template = "<xml>
        //                     <ToUserName><![CDATA[%s]]></ToUserName>
        //                     <FromUserName><![CDATA[%s]]></FromUserName>
        //                     <CreateTime>%s</CreateTime>
        //                     <MsgType><![CDATA[%s]]></MsgType>
        //                     <Content><![CDATA[%s]]></Content>
        //                     </xml>";
        //         //解析上面的字符串模板将各个变量的值依次传入
        //         echo $info = sprintf($template, $toUser, $fromUser, $time, $MsgType, $Content);
        //     }
        // }else if(strtolower($postObj->MsgType) == 'textf' && $postObj->Content == '图文') {
        //     $arr = array(
        //         array(
        //             'title' => '慕课网',
        //             'description' => "慕课网",
        //             'picUrl' => 'http://www.imooc.com/static/img/common/logo.png',
        //             'url' => 'http://www.imooc.com',
        //         ),
        //         array(
        //             'title' => $fromUser,
        //             'description' => "百度一下",
        //             'picUrl' => 'https://www.baidu.com/img/bdlogo.png',
        //             'url' => 'http://www.hao123.com',
        //         ),
        //         array(
        //             'title' => $toUser,
        //             'description' => "QQ空间",
        //             'picUrl' => 'http://www.imooc.com/static/img/common/logo.png',
        //             'url' => 'http://www.qq.com',
        //         ),
        //     );
        //     $template = "<xml>
        //                 <ToUserName><![CDATA[%s]]></ToUserName>
        //                 <FromUserName><![CDATA[%s]]></FromUserName>
        //                 <CreateTime>%s</CreateTime>
        //                 <MsgType><![CDATA[%s]]></MsgType>
        //                 <ArticleCount>" . count($arr) . "</ArticleCount>
        //                 <Articles>";
        //     foreach ($arr as $k => $v) {
        //         $template .= "<item>
        //                     <Title><![CDATA[" . $v['title'] . "]]></Title>
        //                     <Description><![CDATA[" . $v['description'] . "]]></Description>
        //                     <PicUrl><![CDATA[" . $v['picUrl'] . "]]></PicUrl>
        //                     <Url><![CDATA[" . $v['url'] . "]]></Url>
        //                     </item>";
        //     }
        //     $template .= "</Articles>
        //                 </xml> ";
        //     echo sprintf($template, $toUser, $fromUser, time(), 'news');
        // }
        // else {
        //     //输入城市的名字返回天气信息，
        //     $template = "<xml>
        //                 <ToUserName><![CDATA[%s]]></ToUserName>
        //                 <FromUserName><![CDATA[%s]]></FromUserName>
        //                 <CreateTime>%s</CreateTime>
        //                 <MsgType><![CDATA[%s]]></MsgType>
        //                 <Content><![CDATA[%s]]></Content>
        //                 </xml>";
        //     // $toUser=$postObj->FromUserName;
        //     // $fromUser=$postObj->ToUserName;
        //     $time = time();
        //     $msgType = 'text';
        //     $ch = curl_init();
        //     $url = 'http://apis.baidu.com/apistore/weatherservice/cityname?cityname=' . urldecode($postObj->Content);
        //     $header = array(
        //         'apikey: 3286e94f22b3fa9b371a0cd8773f5099',
        //     );
        //     // 添加apikey到header
        //     curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //     // 执行HTTP请求
        //     curl_setopt($ch, CURLOPT_URL, $url);
        //     $res = curl_exec($ch);
        //     $arr = json_decode($res, true);
        //     $data = $arr[retData];
        //     // var_dump($arr);
        //     $content = "城市名：" . $data['city'] . "\n日期：" . $data['date'] . "\n时间：" . $data['time'] . "\n当前气温：" . $data['temp'] . "\n最低温度：" . $data['l_tmp'] . "\n最高温度：" . $data['h_tmp'];
        //     echo sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
        // }
		// $postObj->ToUserName = '';
		// $postObj->FromUser = '';
		// $postObj->CreateTime = '';
		// $postObj->MsgType = '';
		// $postObj->Event = '';
		//判断关键字，如果接收到的字为joneco时自动回复

	}
}
