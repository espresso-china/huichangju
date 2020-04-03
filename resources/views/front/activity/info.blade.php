<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>幸孕淘淘</title>
    <meta name="_token" content="{!! csrf_token() !!}"/>
    <script type="text/javascript" src="{!! asset('js/meta.js')!!}"></script>
    <link href="{!! asset('./css/activity.css?v='.time())!!}" rel="stylesheet">
</head>
<body>
<div class='cover'>
    <img src='{!! asset('/images/bargain/bargain.png') !!}'/>
</div>
<div class='activity-info'>
    <span>测试活动</span>
    <span>时间：2019-10-23 - 2019-11-11</span>
    <span>地点：测试地址</span>
    <span>发起人：测试人</span>
    <span>参与人员：测试人员</span>
</div>
<div class='activity-desc'>
    测试内容
</div>
<div class='signup'>
    <div class='signup-button'>
        <div class="signup-button-info" data-url='{!! route('h5.customer.activity.signup') !!}'>我要报名</div>
    </div>
</div>
<script type="text/javascript" src="{!! asset('/js/jquery.js') !!}"></script>
<script type="text/javascript" src="{!! asset('/js/layer/layer.js') !!}?v=1"></script>
<script>
    $(function () {
        $('.signup-button-info').click(function () {
            var url = $(this).data('url')
            if (url.length > 0) {
                window.location.href = url;
            }
        })
    })
</script>
</body>
</html>