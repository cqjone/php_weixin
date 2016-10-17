<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>微信分享</title>
    <meta name="viewpoint" content="initial-scale=1.0;width=device-width" />
    <meta http-equiv="content" content="text/html;charset=utf-8" />
    <script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
</head>
<body>
<?php echo ($name); ?>
<script type="text/javascript">
    wx.config({
        debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: 'wx60ed01dc892a54fc', // 必填，公众号的唯一标识
        timestamp: <?php echo ($timestamp); ?>, // 必填，生成签名的时间戳
        nonceStr: <?php echo ($noncestr); ?>, // 必填，生成签名的随机串
        signature: <?php echo ($signature); ?>,// 必填，签名，见附录1
        jsApiList: [
            'onMenuShareTimeline',
            'onMenuShareAppMessage'
        ] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
    });

    wx.ready(function(){

        wx.onMenuShareTimeline({
            title: '测试分享11', // 分享标题
            link: 'http://baidu.com', // 分享链接
            imgUrl: 'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png', // 分享图标
            success: function () {
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });

        wx.onMenuShareAppMessage({
            title: '测试分享11', // 分享标题
            desc: '第一个微信开发的分享测试', // 分享描述
            link: 'http://baidu.com', // 分享链接
            imgUrl: 'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png', // 分享图标
            type: 'link', // 分享类型,music、video或link，不填默认为link
            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
            success: function () {
                // 用户确认分享后执行的回调函数
            },
            cancel: function () {
                // 用户取消分享后执行的回调函数
            }
        });

    });

    wx.error(function(res){


    });


</script>
</body>
</html>