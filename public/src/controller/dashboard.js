/**

 @Name：控制台
 @Author：tacker
 @Site：http://xshop.xndl.com
 @License：GPL-2

 */
layui.define(['admin', 'carousel', 'element'], function (exports) {

    var $ = layui.jquery
        , admin = layui.admin
        , carousel = layui.carousel
        , element = layui.element
        , device = layui.device();

    //轮播切换
    $('.layadmin-carousel').each(function () {
        var othis = $(this);
        carousel.render({
            elem: this
            , width: '100%'
            , arrow: 'none'
            , interval: othis.data('interval')
            , autoplay: othis.data('autoplay') === true
            , trigger: (device.ios || device.android) ? 'click' : 'hover'
            , anim: othis.data('anim')
        });
    });

    element.render('progress');


    function getCookie(c_name) {
        if (document.cookie.length > 0) {
            c_start = document.cookie.indexOf(c_name + "=")
            if (c_start != -1) {
                c_start = c_start + c_name.length + 1
                c_end = document.cookie.indexOf(";", c_start)
                if (c_end == -1) c_end = document.cookie.length
                return unescape(document.cookie.substring(c_start, c_end))
            }
        }
        return ""
    }

    var userInfo = layui.data('layuiAdmin').user;
    //console.log(userInfo);
    if (userInfo.is_agree == 0) {
        layer.open({
            type: 2,
            title: false
            , content: '../cooperation/rule'
            , shadeClose: false
            , area: admin.screen() < 2 ? ['100%', '80%'] : ['100%', '100%']
            , closeBtn: 0
        });
    }

    admin.req({
        url: '../api/common/dashboard',
        success: function (result) {
            if (result.code == 0) {
                loadYearSaleChart(result.data.month_sales_moneys, result.data.month_sale_orders, result.data.month_success_moneys, result.data.month_success_orders)

                loadHotGoods(result.data.current_month_top_goods);

                loadHotMembers(result.data.current_month_top_members);

                var month_sale_money_percent = Math.abs(result.data.month_sale_money_percent) + '%';
                element.progress('month_sale_money_percent', month_sale_money_percent)
                if (result.data.month_sale_money_percent < 0) {
                    $("#progress_month_sale_money_percent").find('.tips').text('同上期减少' + month_sale_money_percent);
                } else {
                    $("#progress_month_sale_money_percent").find('.tips').text('同上期增长' + month_sale_money_percent);
                }

                var month_sale_order_percent = Math.abs(result.data.month_sale_order_percent) + '%';
                element.progress('month_sale_order_percent', month_sale_order_percent)
                if (result.data.month_sale_order_percent < 0) {
                    $("#progress_month_sale_order_percent").find('.tips').text('同上期减少' + month_sale_order_percent);
                } else {
                    $("#progress_month_sale_order_percent").find('.tips').text('同上期增长' + month_sale_order_percent);
                }

                var month_success_money_percent = Math.abs(result.data.month_success_money_percent) + '%';
                element.progress('month_success_money_percent', month_success_money_percent)
                if (result.data.month_success_money_percent < 0) {
                    $("#progress_month_success_money_percent").find('.tips').text('同上期减少' + month_success_money_percent);
                } else {
                    $("#progress_month_success_money_percent").find('.tips').text('同上期增长' + month_success_money_percent);
                }

                var month_success_order_percent = Math.abs(result.data.month_success_order_percent) + '%';
                element.progress('month_success_order_percent', month_success_order_percent)
                if (result.data.month_success_order_percent < 0) {
                    $("#progress_month_success_order_percent").find('.tips').text('同上期减少' + month_success_order_percent);
                } else {
                    $("#progress_month_success_order_percent").find('.tips').text('同上期增长' + month_success_order_percent);
                }

                $.each(result.data, function (index, item) {
                    $("#" + index).text(item);
                })

            }
        }
    })


    var loadYearSaleChart = function (sale_moneys, sale_totals, success_moneys, success_totals) {
        //访问量
        layui.use(['carousel', 'echarts'], function () {
            var $ = layui.$
                , carousel = layui.carousel
                , echarts = layui.echarts;

            var echartsApp = [], options = [
                {
                    tooltip: {
                        trigger: 'axis'
                    },
                    calculable: true,
                    legend: {
                        data: ['销售额', '成交额', '销售订单数', '成交订单数']
                    },

                    xAxis: [
                        {
                            type: 'category',
                            data: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月']
                        }
                    ],
                    yAxis: [
                        {
                            type: 'value',
                            name: '销售额',
                            axisLabel: {
                                formatter: '{value} 千元'
                            }
                        },
                        {
                            type: 'value',
                            name: '订单数',
                            axisLabel: {
                                formatter: '{value}'
                            }
                        }
                    ],
                    series: [
                        {
                            name: '销售额',
                            type: 'line',
                            data: sale_moneys
                        },
                        {
                            name: '成交额',
                            type: 'line',
                            data: success_moneys
                        },
                        {
                            name: '销售订单数',
                            type: 'line',
                            yAxisIndex: 1,
                            data: sale_totals
                        },
                        {
                            name: '成交订单数',
                            type: 'line',
                            yAxisIndex: 1,
                            data: success_totals
                        }
                    ]
                }
            ]
                , elemDataView = $('#LAY-index-pagetwo').children('div')
                , renderDataView = function (index) {
                echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
                echartsApp[index].setOption(options[index]);
                window.onresize = echartsApp[index].resize;
            };

            //没找到DOM，终止执行
            if (!elemDataView[0]) return;
            renderDataView(0);

        });
    }

    var loadMapData = function () {
        //地图
        layui.use(['carousel', 'echarts'], function () {
            var $ = layui.$
                , carousel = layui.carousel
                , echarts = layui.echarts;

            var echartsApp = [], options = [
                {
                    title: {
                        text: '全国的用户分布',
                        subtext: '不完全统计'
                    },
                    tooltip: {
                        trigger: 'item'
                    },
                    dataRange: {
                        orient: 'horizontal',
                        min: 0,
                        max: 60000,
                        text: ['高', '低'],
                        splitNumber: 0
                    },
                    series: [
                        {
                            name: '全国的用户分布',
                            type: 'map',
                            mapType: 'china',
                            selectedMode: 'multiple',
                            itemStyle: {
                                normal: {label: {show: true}},
                                emphasis: {label: {show: true}}
                            },
                            data: [
                                {name: '西藏', value: 60},
                                {name: '青海', value: 167},
                                {name: '宁夏', value: 210},
                                {name: '海南', value: 252},
                                {name: '甘肃', value: 502},
                                {name: '贵州', value: 570},
                                {name: '新疆', value: 661},
                                {name: '云南', value: 8890},
                                {name: '重庆', value: 10010},
                                {name: '吉林', value: 5056},
                                {name: '山西', value: 2123},
                                {name: '天津', value: 9130},
                                {name: '江西', value: 10170},
                                {name: '广西', value: 6172},
                                {name: '陕西', value: 9251},
                                {name: '黑龙江', value: 5125},
                                {name: '内蒙古', value: 1435},
                                {name: '安徽', value: 9530},
                                {name: '北京', value: 51919},
                                {name: '福建', value: 3756},
                                {name: '上海', value: 59190},
                                {name: '湖北', value: 37109},
                                {name: '湖南', value: 8966},
                                {name: '四川', value: 31020},
                                {name: '辽宁', value: 7222},
                                {name: '河北', value: 3451},
                                {name: '河南', value: 9693},
                                {name: '浙江', value: 62310},
                                {name: '山东', value: 39231},
                                {name: '江苏', value: 35911},
                                {name: '广东', value: 55891}
                            ]
                        }
                    ]
                }
            ]
                , elemDataView = $('#LAY-index-pagethree').children('div')
                , renderDataView = function (index) {
                echartsApp[index] = echarts.init(elemDataView[index], layui.echartsTheme);
                echartsApp[index].setOption(options[index]);
                window.onresize = echartsApp[index].resize;
            };
            //没找到DOM，终止执行
            if (!elemDataView[0]) return;

            renderDataView(0);
        });
    }

    var loadHotGoods = function (data) {
        //热销商品
        layui.use('table', function () {
            var $ = layui.$
                , table = layui.table;

            table.render({
                elem: '#LAY-home-goods'
                , data: data
                , cols: [[
                    {
                        field: 'id', title: '排名', width: 60, align: 'center', templet: function (d) {
                            return d.LAY_TABLE_INDEX + 1;
                        }
                    }
                    , {field: 'goods_name', title: '商品名称', minWidth: 360}
                    , {field: 'total', title: '销量（件）', width: 100, align: 'center'}
                    , {field: 'money', title: '金额（元）', width: 100, align: 'center'}
                ]]
                , skin: 'line'
            });

        });
    }

    var loadHotMembers = function (data) {
        //热销商品
        layui.use('table', function () {
            var $ = layui.$
                , table = layui.table;

            table.render({
                elem: '#LAY-home-members'
                , data: data
                , cols: [[
                    {
                        field: 'id', title: '排名', width: 60, align: 'center', templet: function (d) {
                            return d.LAY_TABLE_INDEX + 1;
                        }
                    }
                    , {field: 'member_name', title: '会员', minWidth: 180}
                    , {field: 'total', title: '销量（件）', width: 100, align: 'center'}
                    , {field: 'money', title: '金额（元）', width: 100, align: 'center'}
                ]]
                , skin: 'line'
            });

        });
    }

    //回复留言
    admin.events.replyNote = function (othis) {
        var nid = othis.data('id');
        layer.prompt({
            title: '回复留言 ID:' + nid
            , formType: 2
        }, function (value, index) {
            //这里可以请求 Ajax
            //…
            layer.msg('得到：' + value);
            layer.close(index);
        });
    };


    exports('dashboard', {})
});
