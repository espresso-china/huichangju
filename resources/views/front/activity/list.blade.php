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
<div class='tab'>
    <div class='active' id="nav1" onMouseOver="doClick(this)">热门活动</div>
    <div id="nav2" onMouseOver="doClick(this)">我参与的</div>
</div>
<div class='list' id="item1">
    <div class='list-item' data-url='{!! route('h5.customer.activity.info') !!}'>
        <img src='{!! asset('/images/bargain/bargain.png') !!}' class='item-cover'/>
        <div class='item-info'>
            <div class='item-title'>测试活动</div>
            <div class='item-desc'>这里是描述</div>
            <div class='item-date'>时间：2019-10-23</div>
        </div>
    </div>
</div>
<div class='list' id="item2">

</div>
<script type="text/javascript" src="{!! asset('/js/jquery.js') !!}"></script>
<script type="text/javascript" src="{!! asset('/js/layer/layer.js') !!}?v=1"></script>
<script>
    function doClick(o) {
        o.className = "active";
        var j;
        var id;
        var e;
        for (var i = 1; i <= 2; i++) {
            id = "nav" + i;
            j = document.getElementById(id);
            e = document.getElementById("item" + i);
            if (id != o.id) {
                j.classList.remove("active");
                e.style.display = "none";
            } else {
                j.classList.add("active");
                e.style.display = "block";
            }
        }
    }

    $(function () {
        $('.list-item').click(function () {
            var url = $(this).data('url')
            if (url.length > 0) {
                window.location.href = url;
            }
        })
    })
</script>
</body>
</html>