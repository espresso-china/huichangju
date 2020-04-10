<!DOCTYPE html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <title>会场列表</title>
    <script src="https://www.jq22.com/jquery/jquery-1.10.2.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $("a.switch_thumb").toggle(function(){
                $(this).addClass("swap");
                $("ul.display").fadeOut("fast", function() {
                    $(this).fadeIn("fast").addClass("thumb_view");
                });
            }, function () {
                $(this).removeClass("swap");
                $("ul.display").fadeOut("fast", function() {
                    $(this).fadeIn("fast").removeClass("thumb_view");
                });
            });

        });
    </script>
    <style type="text/css">
        *{margin:0;padding:0;list-style-type:none;}
        a,img{border:0;}
        body{font:12px/180% Arial, Helvetica, sans-serif, "新宋体";}
        body{
            width: 750px;margin:0 auto;
        }
        .container{width:690px;margin:50px auto;overflow:hidden;}
        /* display */
        ul.display{float:left;width:690px;padding:0;list-style:none;border-top:1px solid #333;border-right:1px solid #333;background:#222;}
        ul.display li{float:left;width:690px;padding:10px 0;border-top:1px solid #111;border-right:1px solid #111;border-bottom:1px solid #333;border-left:1px solid #333;}
        ul.display li a{color:#e7ff61;text-decoration:none;}
        ul.display li .content_block{padding:0 10px;}
        ul.display li .content_block h2{padding:5px;font-weight:normal;font-size:1.7em;}
        ul.display li .content_block p{padding:5px 5px 5px 245px;font-size:1.2em;color:#fff;}
        ul.display li .content_block a img{padding:5px;border:2px solid #ccc;background:#fff;margin:0 15px 0 0;float:left;}
        ul.thumb_view li{width:250px;}
        ul.thumb_view li h2{display:inline;}
        ul.thumb_view li p{display:none;}
        ul.thumb_view li .content_block a img{margin:0 0 10px;}
        a.switch_thumb{width:122px;height:26px;line-height:26px;padding:0;margin:10px 0;display:block;background:url({!! asset('images/switch.gif') !!}) no-repeat;outline:none;text-indent:-9999px;}
        a:hover.switch_thumb{filter:alpha(opacity=75);opacity:.75;-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=75)";}
        a.swap{background-position:left bottom; }
    </style>
</head>
<body>
<div class="container">
    <a href="javascript:void(0);" class="switch_thumb">Switch Thumb</a>
    <ul class="display">
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>JQuery是继prototype之后又一个优秀的Javascript库。它是轻量级的js库 ，它兼容CSS3，还兼容各种浏览器（IE 6.0+, FF 1.5+, Safari 2.0+, Opera 9.0+），jQuery2.0及后续版本将不再支持IE6/7/8浏览器。jQuery使用户能更方便地处理HTML（标准通用标记语言下的一个应用）、events、实现动画效果，并且方便地为网站提供AJAX交互。</p>
            </div>
        </li>
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample2.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>JQuery是继prototype之后又一个优秀的Javascript库。它是轻量级的js库 ，它兼容CSS3，还兼容各种浏览器（IE 6.0+, FF 1.5+, Safari 2.0+, Opera 9.0+），jQuery2.0及后续版本将不再支持IE6/7/8浏览器。jQuery使用户能更方便地处理HTML（标准通用标记语言下的一个应用）、events、实现动画效果，并且方便地为网站提供AJAX交互。</p>
            </div>
        </li>
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample3.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>JQuery是继prototype之后又一个优秀的Javascript库。它是轻量级的js库 ，它兼容CSS3，还兼容各种浏览器（IE 6.0+, FF 1.5+, Safari 2.0+, Opera 9.0+），jQuery2.0及后续版本将不再支持IE6/7/8浏览器。jQuery使用户能更方便地处理HTML（标准通用标记语言下的一个应用）、events、实现动画效果，并且方便地为网站提供AJAX交互。</p>
            </div>
        </li>
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample4.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>JQuery是继prototype之后又一个优秀的Javascript库。它是轻量级的js库 ，它兼容CSS3，还兼容各种浏览器（IE 6.0+, FF 1.5+, Safari 2.0+, Opera 9.0+），jQuery2.0及后续版本将不再支持IE6/7/8浏览器。jQuery使用户能更方便地处理HTML（标准通用标记语言下的一个应用）、events、实现动画效果，并且方便地为网站提供AJAX交互。</p>
            </div>
        </li>
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample5.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>JQuery是继prototype之后又一个优秀的Javascript库。它是轻量级的js库 ，它兼容CSS3，还兼容各种浏览器（IE 6.0+, FF 1.5+, Safari 2.0+, Opera 9.0+），jQuery2.0及后续版本将不再支持IE6/7/8浏览器。jQuery使用户能更方便地处理HTML（标准通用标记语言下的一个应用）、events、实现动画效果，并且方便地为网站提供AJAX交互。</p>
            </div>
        </li>
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample6.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>JQuery是继prototype之后又一个优秀的Javascript库。它是轻量级的js库 ，它兼容CSS3，还兼容各种浏览器（IE 6.0+, FF 1.5+, Safari 2.0+, Opera 9.0+），jQuery2.0及后续版本将不再支持IE6/7/8浏览器。jQuery使用户能更方便地处理HTML（标准通用标记语言下的一个应用）、events、实现动画效果，并且方便地为网站提供AJAX交互。</p>
            </div>
        </li>
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample7.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>JQuery是继prototype之后又一个优秀的Javascript库。它是轻量级的js库 ，它兼容CSS3，还兼容各种浏览器（IE 6.0+, FF 1.5+, Safari 2.0+, Opera 9.0+），jQuery2.0及后续版本将不再支持IE6/7/8浏览器。jQuery使用户能更方便地处理HTML（标准通用标记语言下的一个应用）、events、实现动画效果，并且方便地为网站提供AJAX交互。</p>
            </div>
        </li>
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample8.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>Askin', jehosephat come pudneer, sam-hell, in lament had. Cabin tax-collectors spell, chitlins spittin' watchin' hootch me rightly kinfolk that. </p>
            </div>
        </li>
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample9.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>Askin', jehosephat come pudneer, sam-hell, in lament had. Cabin tax-collectors spell, chitlins spittin' watchin' hootch me rightly kinfolk that. Woman kickin', work yer last dogs, rattler hee-haw mobilehome stew trailer driveway shootin'. Woman kickin', work yer last dogs, rattler hee-haw mobilehome stew trailer driveway shootin'.</p>
            </div>
        </li>
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample10.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>JQuery是继prototype之后又一个优秀的Javascript库。它是轻量级的js库 ，它兼容CSS3，还兼容各种浏览器（IE 6.0+, FF 1.5+, Safari 2.0+, Opera 9.0+），jQuery2.0及后续版本将不再支持IE6/7/8浏览器。jQuery使用户能更方便地处理HTML（标准通用标记语言下的一个应用）、events、实现动画效果，并且方便地为网站提供AJAX交互。</p>
            </div>
        </li>
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample11.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>JQuery是继prototype之后又一个优秀的Javascript库。它是轻量级的js库 ，它兼容CSS3，还兼容各种浏览器（IE 6.0+, FF 1.5+, Safari 2.0+, Opera 9.0+），jQuery2.0及后续版本将不再支持IE6/7/8浏览器。jQuery使用户能更方便地处理HTML（标准通用标记语言下的一个应用）、events、实现动画效果，并且方便地为网站提供AJAX交互。</p>
            </div>
        </li>
        <li>
            <div class="content_block">
                <a href="#"><img src="images/sample12.gif" alt="" /></a>
                <h2><a href="#">标题</a></h2>
                <p>JQuery是继prototype之后又一个优秀的Javascript库。它是轻量级的js库 ，它兼容CSS3，还兼容各种浏览器（IE 6.0+, FF 1.5+, Safari 2.0+, Opera 9.0+），jQuery2.0及后续版本将不再支持IE6/7/8浏览器。jQuery使用户能更方便地处理HTML（标准通用标记语言下的一个应用）、events、实现动画效果，并且方便地为网站提供AJAX交互。</p>
            </div>
        </li>
    </ul>
</div>
</body>
</html>
