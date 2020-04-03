<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>会场聚-找会场</title>
    <meta name="_token" content="{!! csrf_token() !!}"/>
    <script type="text/javascript" src="{!! asset('js/meta.js')!!}"></script>
    <link href="{!! asset('./css/hotel.css?v='.time())!!}" rel="stylesheet">
</head>
<body>
<div class='cover'>
    <img src='{!! asset('/images/logo.jpg') !!}'/>
</div>
<div class="search-box">

</div>
<div class='activity-info'>

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
