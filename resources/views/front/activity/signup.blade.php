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
<div class="signup-main">
    <div class="signup-item">
        <div class="siguup-item-title">姓名</div>
        <input class="siguup-item-input" type="text" placeholder="请输入您的姓名" />
    </div>
    <div class="signup-item">
        <div class="siguup-item-title">手机号码</div>
        <input class="siguup-item-input" type="text" placeholder="请输入您的手机号码" />
    </div>
    <div class="signup-item">
        <div class="siguup-item-title">参与人数</div>
        <input class="siguup-item-input" type="text" placeholder="请输入参与人数" />
    </div>
</div>

<div class='signup'>
    <div class='signup-button'>提交</div>
</div>
</body>
</html>