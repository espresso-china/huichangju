
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>找会场-会场聚</title>
    <meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" name="viewport" />
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta content="black" name="apple-mobile-web-app-status-bar-style" />
    <meta content="telephone=no" name="format-detection" />
    <link href="{!! asset('css/hotel.css') !!}" rel="stylesheet" type="text/css" />
</head>
<body>
<section class="jq22-flexView">
{{--    <header class="jq22-navBar jq22-navBar-fixed b-line">--}}
{{--        <a href="javascript:;" class="jq22-navBar-item">--}}
{{--            <i class="icon icon-return"></i>--}}
{{--        </a>--}}
{{--        <div class="jq22-center">--}}
{{--            <span class="jq22-center-title">汽车随心购</span>--}}
{{--        </div>--}}
{{--        <a href="javascript:;" class="jq22-navBar-item">--}}
{{--            <i class="icon icon-sys"></i>--}}
{{--        </a>--}}
{{--    </header>--}}
    <section class="jq22-scrollView">
        <div class="jq22-auto-img">
            <img src="{!! asset('images/logo.png') !!}" alt="" class="logo">
        </div>
        <div class="jq22-auto-form">
            <div class="jq22-auto-box">

                <form action="{!! route('h5.hotel.lists') !!}" class="jq22-auto-inp">
                    <div class="jq22-flex">
                        <label>区域</label>
                        <div class="jq22-flex-box">
                            <select class="weui-select" name="district_id">
                                <option selected="" value="1">区域</option>
                                @foreach($citys as $v)
                                    <option value="{!! $v->district_id !!}">{!! $v->district_name !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="jq22-flex">
                        <label>星级</label>
                        <div class="jq22-flex-box">
                            <select class="weui-select" name="stars">
                                <option selected="" value="1">全部</option>
                                <option value="1">五星及准五星</option>
                                <option value="2">四星及准四星</option>
                                <option value="3">三星及以下</option>
                            </select>
                        </div>
                    </div>
                    <div class="jq22-flex">
                        <label>价格</label>
                        <div class="jq22-flex-box">
                            <select class="weui-select" name="price">
                                <option selected="" value="1">全部</option>
                                <option value="1">2000元以下</option>
                                <option value="2">2001-4000元</option>
                                <option value="3">4001-8000元</option>
                                <option value="4">8001-30000元</option>
                                <option value="5">30000元以上</option>
                            </select>
                        </div>
                    </div>
                    <div class="jq22-flex">
                        <label>面积</label>
                        <div class="jq22-flex-box">
                            <select class="weui-select" name="area">
                                <option selected="" value="1">全部</option>
                                <option value="1">80平米以下</option>
                                <option value="1">81-200平米</option>
                                <option value="1">201-400平米</option>
                                <option value="1">401-800平米</option>
                                <option value="1">801平米以上</option>
                            </select>
                        </div>
                    </div>
                    <div class="jq22-flex">
                        <label>人数</label>
                        <div class="jq22-flex-box">
                            <select class="weui-select" name="persons">
                                <option selected="" value="0">全部</option>
                                <option value="1">30人以下</option>
                                <option value="1">31-100人</option>
                                <option value="1">101-300人</option>
                                <option value="1">301-600人</option>
                                <option value="1">601人以上</option>
                            </select>
                        </div>
                    </div>
                    <div class="jq22-flex">
                        <label>类别</label>
                        <div class="jq22-flex-box">
                            <select class="weui-select" name="type">
                                <option selected="" value="0">全部</option>
                                <option value="1">酒店</option>
                                <option value="0">非酒店</option>
                            </select>
                        </div>
                    </div>

{{--                    <div class="jq22-flex">--}}
{{--                        <label>手机码</label>--}}
{{--                        <div class="jq22-flex-box">--}}
{{--                            <input type="text" id="phone1" autocomplete="off" placeholder="请输入手机号码">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="jq22-flex">--}}
{{--                        <label>短信验证码</label>--}}
{{--                        <div class="jq22-flex-box">--}}
{{--                            <input type="text" id="code1" autocomplete="off" placeholder="请输入验证码">--}}
{{--                            <input id="btnSendCode1" type="button" class="btn btn-default" value="获取验证码" onClick="sendMessage1()" />--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <button class="jq22-apply-btn" type="submit">立即搜索</button>
                    <div style="width: 100%;text-align: center;padding: 20px 0">找会场 方便快捷 服务到位</div>
                </form>
            </div>
        </div>
    </section>
</section>
</body>
<script src="https://www.jq22.com/jquery/jquery-1.10.2.js"></script>
<script type="text/javascript">
    var phoneReg = /(^1[3|4|5|7|8]\d{9}$)|(^09\d{8}$)/;
    var count = 60;
    var InterValObj1;
    var curCount1;
    function sendMessage1() {
        curCount1 = count;
        var phone = $.trim($('#phone1').val());
        if (!phoneReg.test(phone)) {
            alert(" 请输入有效的手机号码");
            return false;
        }

        $("#btnSendCode1").attr("disabled", "true");
        $("#btnSendCode1").val( + curCount1 + "秒再获取");
        InterValObj1 = window.setInterval(SetRemainTime1, 1000);

    }
    function SetRemainTime1() {
        if (curCount1 == 0) {
            window.clearInterval(InterValObj1);
            $("#btnSendCode1").removeAttr("disabled");
            $("#btnSendCode1").val("重新发送");
        }
        else {
            curCount1--;
            $("#btnSendCode1").val( + curCount1 + "秒再获取");
        }
    }

    function binding(){
        alert('请输入手机号码')
    }
</script>
</html>
