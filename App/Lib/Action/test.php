<?php
// $data=' {
//      "button":[
//      {
//           "type":"click",
//           "name":"今日歌曲",
//           "key":"V1001_TODAY_MUSIC"
//       },
//       {
//            "type":"click",
//            "name":"歌手简介",
//            "key":"V1001_TODAY_SINGER"
//       },
//       {
//            "name":"菜单",
//            "sub_button":[
//             {
//                "type":"click",
//                "name":"hello word",
//                "key":"V1001_HELLO_WORLD"
//             },
//             {
//                "type":"click",
//                "name":"赞一下我们",
//                "key":"V1001_GOOD"
//             }]
//        }]
//  }';

// $ch = curl_init($urlcon); //请求的URL地址
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
// curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//$data JSON类型字符串
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
// $data = curl_exec($ch);
// print_r($data);//创建成功返回：{"errcode":0,"errmsg":"ok"}
// /**
　　* Created by PhpStorm.
　　* User: bin
　　* Date: 15-1-16
　　* Time: 上午9:48
　　*/
　　namespace HomeCommon;
　　// 微信处理类
　　set_time_limit(30);
　　class Weixin{
    　　//构造方法
    　　static $qrcode_url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?";
    　　static $token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&";
    　　static $qrcode_get_url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?";

    　　//生成二维码
    　　public function getEwm($wechatid,$fqid,$type = 1){
        　　$wechat = M('Member_public')->where(array('id'=> $wechatid))->find();
        　　$appid = $wechat['appid'];
        　　$secret = $wechat['secret'];
        　　$ACCESS_TOKEN = $this->getToken($appid,$secret);
        　　$url = $this->getQrcodeurl($ACCESS_TOKEN,$fqid,1);
        　　return DownLoadQr($url,time());
    　　}

    　　protected function getQrcodeurl($ACCESS_TOKEN,$fqid,$type = 1){
        　　$url = self::$qrcode_url.'access_token='.$ACCESS_TOKEN;
        　　if($type == 1){
            　　//生成永久二维码
            　　$qrcode= '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$fqid.'}}}';
        　　}else{
            　　//生成临时二维码
            　　$qrcode = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$fqid.'}}}';
        　　}
        　　$result = $this->http_post_data($url,$qrcode);
        　　$oo = json_decode($result[1]);
        　　if(!$oo->ticket){
            　　$this->ErrorLogger('getQrcodeurl falied. Error Info: getQrcodeurl get failed');
            　　exit();
        　　}
        　　$url = self::$qrcode_get_url.'ticket='.$oo->ticket.'';
        　　return $url;
    　　}

    　　protected function getToken($appid,$secret){
        　　$ACCESS_TOKEN = file_get_contents(self::$token_url."appid=$appid&secret=$secret");
        　　$ACCESS_TOKEN = json_decode($ACCESS_TOKEN);
        　　$ACCESS_TOKEN = $ACCESS_TOKEN->access_token;
        　　return $ACCESS_TOKEN;
    　　}

    　　protected function http_post_data($url, $data_string) {
        　　$ch = curl_init();
        　　curl_setopt($ch, CURLOPT_POST, 1);
        　　curl_setopt($ch, CURLOPT_URL, $url);
        　　curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        　　curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            　　'Content-Type: application/json; charset=utf-8',
            　　'Content-Length: ' . strlen($data_string))
        　　);
        　　ob_start();
        　　curl_exec($ch);
        　　if (curl_errno($ch)) {
        　　    $this->ErrorLogger('curl falied. Error Info: '.curl_error($ch));
        　　}
        　　$return_content = ob_get_contents();
        　　ob_end_clean();
        　　$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        　　return array($return_code, $return_content);
    　　}



    　　//下载二维码到服务器
    　　protected function DownLoadQr($url,$filestring){
        　　if($url == ""){
        　　    return false;
        　　}

        　　$filename = $filestring.'.jpg';
        　　ob_start();
        　　readfile($url);
        　　$img=ob_get_contents();
        　　ob_end_clean();
        　　$size=strlen($img);
        　　$fp2=fopen('./Uploads/qrcode/'.$filename,"a");
        　　if(fwrite($fp2,$img) === false){
        　　    $this->ErrorLogger('dolwload image falied. Error Info: 无法写入图片');
        　　    exit();
        　　}
        　　fclose($fp2);
        　　return './Uploads/qrcode/'.$filename;
        }

    　　private function ErrorLogger($errMsg){
    　　    $logger = fopen('./ErrorLog.txt', 'a+');
    　　    fwrite($logger, date('Y-m-d H:i:s')." Error Info : ".$errMsg."rn");
　　    }

     //参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
function curl_request($url,$post='',$cookie='', $returnCookie=0){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
    if($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    if($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
    if($returnCookie){
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie']  = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    }else{
        return $data;
    }
}
//     public function http_post_data($url, $data_string) {
//     　　$ch = curl_init();
//     　　curl_setopt($ch, CURLOPT_POST, 1);
//     　　curl_setopt($ch, CURLOPT_URL, $url);
//     　　curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
//     　　curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//         　　'Content-Type: application/json; charset=utf-8',
//         　　'Content-Length: ' . strlen($data_string))
//     　　);
//     　　ob_start();
//     　　curl_exec($ch);
//     　　if (curl_errno($ch)) {
//     　　    $this->ErrorLogger('curl falied. Error Info: '.curl_error($ch));
//     　　}
//     　　$return_content = ob_get_contents();
//     　　ob_end_clean();
//     　　$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     　　return array($return_code, $return_content);
// 　　}




　　}



/**
 * 微信授权相关接口
 */

class Wechat {

  //高级功能-》开发者模式-》获取
  private $app_id = 'xxx';
  private $app_secret = 'xxxxxxx';


  /**
   * 获取微信授权链接
   *
   * @param string $redirect_uri 跳转地址
   * @param mixed $state 参数
   */
  public function get_authorize_url($redirect_uri = '', $state = '')
  {
    $redirect_uri = urlencode($redirect_uri);
    return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
  }

  /**
   * 获取授权token
   *
   * @param string $code 通过get_authorize_url获取到的code
   */
  public function get_access_token($app_id = '', $app_secret = '', $code = '')
  {
    $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secret}&code={$code}&grant_type=authorization_code";
    $token_data = $this->http($token_url);

    if($token_data[0] == 200)
    {
      return json_decode($token_data[1], TRUE);
    }

    return FALSE;
  }

  /**
   * 获取授权后的微信用户信息
   *
   * @param string $access_token
   * @param string $open_id
   */
  public function get_user_info($access_token = '', $open_id = '')
  {
    if($access_token && $open_id)
    {
      $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
      $info_data = $this->http($info_url);

      if($info_data[0] == 200)
      {
        return json_decode($info_data[1], TRUE);
      }
    }

    return FALSE;
  }

  public function http($url, $method, $postfields = null, $headers = array(), $debug = false){
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ci, CURLOPT_TIMEOUT, 30);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
      case 'POST':
        curl_setopt($ci, CURLOPT_POST, true);
        if (!empty($postfields)) {
          curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
          $this->postdata = $postfields;
        }
        break;
    }
    curl_setopt($ci, CURLOPT_URL, $url);
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    $response = curl_exec($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
      echo "=====post data======\r\n";
      var_dump($postfields);
      echo '=====info=====' . "\r\n";
      print_r(curl_getinfo($ci));
      echo '=====$response=====' . "\r\n";
      print_r($response);
    }
    curl_close($ci);
    return array($http_code, $response);
  }

}

    function http_curl($url,$type='get',$res='json',$arr){
        $ch=curl_init();
        curl_setopt($ch , CURLOPT_URL, $url);
        curl_setopt($ch , CURLOPT_RETURNTRANSFER, true);
        if ($type=="post") {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $output=curl_close($ch);
        curl_close($ch);
        if ($res='json') {
            if (curl_errno($ch)) {
                return curl_errno($ch);
            }else{
                return json_decode($output,true);
            }
        }
    }

    /**
 * curl 函数
 * @param string $url 请求的地址
 * @param string $type POST/GET/post/get
 * @param array $data 要传输的数据
 * @param string $err_msg 可选的错误信息（引用传递）
 * @param int $timeout 超时时间
 * @param array 证书信息
 * @author 勾国印
 */
function GoCurl($url, $type, $data = false, &$err_msg = null, $timeout = 20, $cert_info = array()){
    $type = strtoupper($type);
    if ($type == 'GET' && is_array($data)) {
        $data = http_build_query($data);
    }
    $option = array();
    if ( $type == 'POST' ) {
        $option[CURLOPT_POST] = 1;
    }
    if ($data) {
        if ($type == 'POST') {
            $option[CURLOPT_POSTFIELDS] = $data;
        } elseif ($type == 'GET') {
            $url = strpos($url, '?') !== false ? $url.'&'.$data :  $url.'?'.$data;
        }
    }
    $option[CURLOPT_URL]            = $url;
    $option[CURLOPT_FOLLOWLOCATION] = TRUE;
    $option[CURLOPT_MAXREDIRS]      = 4;
    $option[CURLOPT_RETURNTRANSFER] = TRUE;
    $option[CURLOPT_TIMEOUT]        = $timeout;
    //设置证书信息
    if(!empty($cert_info) && !empty($cert_info['cert_file'])) {
        $option[CURLOPT_SSLCERT]       = $cert_info['cert_file'];
        $option[CURLOPT_SSLCERTPASSWD] = $cert_info['cert_pass'];
        $option[CURLOPT_SSLCERTTYPE]   = $cert_info['cert_type'];
    }
    //设置CA
    if(!empty($cert_info['ca_file'])) {
        // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
        $option[CURLOPT_SSL_VERIFYPEER] = 1;
        $option[CURLOPT_CAINFO] = $cert_info['ca_file'];
    } else {
        // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
        $option[CURLOPT_SSL_VERIFYPEER] = 0;
    }
    $ch = curl_init();
    curl_setopt_array($ch, $option);
    $response = curl_exec($ch);
    $curl_no  = curl_errno($ch);
    $curl_err = curl_error($ch);
    curl_close($ch);
    // error_log
    if($curl_no > 0) {
        if($err_msg !== null) {
            $err_msg = '('.$curl_no.')'.$curl_err;
        }
    }
    return $response;
}

$url   = '请求地址';
$data = array(
            'phoneNum' => '18614064456',
        );
$json = GoCurl($url, $data, 'POST', $error_msg);

$array = json_decode($json, true);

print_r($array);
        //创建菜单
        function createMenu($url,$data){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $tmpInfo = curl_exec($ch);
            if (curl_errno($ch)) {
              return curl_error($ch);
            }

            curl_close($ch);
            return $tmpInfo;
        }

        //获取菜单
        function getMenu($ACCESS_TOKEN){
        return file_get_contents("https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$ACCESS_TOKEN);
        }

        //删除菜单
        function deleteMenu($ACCESS_TOKEN){
        return file_get_contents("https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$ACCESS_TOKEN);
        }

        $data = '{
             "button":[
             {
                  "type":"click",
                  "name":"首页",
                  "key":"home"
              },
              {
                   "type":"click",
                   "name":"简介",
                   "key":"introduct"
              },
              {
                   "name":"菜单",
                   "sub_button":[
                    {
                       "type":"click",
                       "name":"hello word",
                       "key":"V1001_HELLO_WORLD"
                    },
                    {
                       "type":"click",
                       "name":"赞一下我们",
                       "key":"V1001_GOOD"
                    }]
               }]
        }';
        echo "<hr/>";
        echo createMenu($url,$data);
        echo "<hr/>";
        // echo getMenu($access_token);
        //echo deleteMenu($access_token);


                // $postArr=array(
        //     'button'=>array(
        //         array(
        //             'type'=>'click',
        //             'name'=>urlencode('第一个'),
        //             'key'=>'item1'
        //         ),
        //         array(
        //             // 'type'=>'click',
        //             'name'=>urlencode('菜单二'),
        //             'sub_button'=>array(
        //                 array(
        //                     'name'=>urlencode('歌曲'),
        //                     'type'=>'click',
        //                     'key'=>'songs'
        //                 ),
        //                 array(
        //                     'name'=>urlencode('电影'),
        //                     'type'=>'view',
        //                     'url'=>'http://www.baidu.com'
        //                 )
        //             )
        //             // 'key'=>'item2'
        //         ),
        //         array(
        //             'type'=>'view',
        //             'name'=>urlencode('第san个'),
        //             'url'=>'http://www.webonly.org'
        //         )
        //     )
        // );
        // $postJson=urldecode( json_encode($postArr) );
        // // var_dump($postArr);
        // // echo "<hr/>";
        // echo $postJson;
        // echo "<hr/>";
        // $res=$this->http_crul($url,'post','json',$postJson);
        // var_dump($res);




         $menuPostString = '{//构造POST给微信服务器的菜单结构体
         "button":[
              {
                   "name":"产品介绍",
                   "sub_button":[
                   {
                       "type":"view",
                       "name":"分销A型",
                       "url":"http://www.027099.com/fenxiao/jianjie/soft.html"
                    },
                    {
                       "type":"view",
                       "name":"分销B型",
                       "url":"http://www.027099.com/fenxiaob/jianjie/soft.html"
                    },{
                       "type":"view",
                       "name":"地接批发",
                       "url":"http://www.027099.com/dijie/jianjie/soft.html"
                    },{
                       "type":"view",
                       "name":"精简组团",
                       "url":"http://www.027099.com/zutuan/jianjie/soft.html"
                    },{
                       "type":"view",
                       "name":"直客网站",
                       "url":"http://www.027099.com/tripal/jianjie/soft.html"
                    }]
               },
              {
                   "name":"申请试用",
                   "sub_button":[
                    {
                       "type":"click",
                       "name":"分销A型",
                       "key":"fxa"
                    },
                    {
                       "type":"click",
                       "name":"分销B型",
                       "key":"fxb"
                    },
                    {
                       "type":"click",
                       "name":"地接批发",
                       "key":"dj"
                    },
                    {
                       "type":"click",
                       "name":"精简组团",
                       "key":"zutuan"
                    },
                    {
                       "type":"click",
                       "name":"直客网站",
                       "key":"zhike"
                    }
                    ]
               },
                   {
                   "name":"博纵在线",
                   "sub_button":[
                    {
                       "type":"view",
                       "name":"企业介绍",
                       "url":"http://www.027099.com/about.html"
                    },
                    {
                       "type":"view",
                       "name":"公司新闻",
                       "url":"http://www.027099.com/news/company/"
                    },
                    {
                       "type":"view",
                       "name":"联系我们",
                       "url":"http://www.027099.com/contact.html"
                    }
                    ]
               }
               ]
         }';
        $menu = dataPost($menuPostString, $url);//将菜单结构体POST给微信服务器

        function getCurl($url){//get https的内容
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);//不输出内容
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $result =  curl_exec($ch);
            curl_close ($ch);
            return $result;
        }
        function dataPost($post_string, $url) {//POST方式提交数据
            $context = array ('http' => array ('method' => "POST", 'header' => "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) \r\n Accept: */*", 'content' => $post_string ) );
            $stream_context = stream_context_create ( $context );
            $data = file_get_contents ( $url, FALSE, $stream_context );
            return $data;
        }
?>