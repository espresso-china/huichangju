/**

 @Title: layui.bMap 百度地图
 @Author: yl
 @License：MIT

 */

layui.define('jquery',function(exports){
    "use strict";
    var $ = layui.jquery
        ,isSetter = !!(layui.setter && layui.setter.bMap)
        ,error = function(msg){
            window.console && console.error && console.error('bMap: ' + msg);
        }
        //外部接口
        ,bMap = {
            config: isSetter ? layui.setter.bMap : {}
            ,index: layui.bMap ? (layui.bMap.index + 10000) : 0
            ,callbackName:'onBMapCallback_tmp'//回调函数名。无需更改
            ,hasLoad: false//是否已经加载了BMap

            //设置全局项
            ,set: function(options){
                var that = this;
                that.config = $.extend({}, that.config, options);
                return that;
            }

            //事件监听
            ,on: function(events, callback){
                return layui.onevent.call(this, MOD_NAME, events, callback);
            }
        }

        //操作当前实例
        ,thisBmap = function(){
            var that = this
                ,options = that.config;

            return {
                // setvalue: function(value){
                //     that.setvalue.call(that, value);
                // },
                config: options
            }
        }

        //字符常量
        ,MOD_NAME = 'bMap'

        //构造器
        ,Class = function(options){
            var that = this;
            that.index = ++bMap.index;
            that.config = $.extend({}, that.config, bMap.config, options);

            if (!that.config.ak) {
                error('请配置ak参数');
                return false;
            }
            that.render();
        };

    //默认配置
    Class.prototype.config = {
        v:'3.0'//默认版本3.0，若传值1.0则判定为lite版本
        ,autolite:false//是否根据当前系统版本自动开启lite版本，默认false。此参数与参数v无关。
        ,https:false//是否开启https， 默认false
        ,timeout:10//等待bMap的加载时间。默认10。若在10s内未能加载bMap则控制台会报错：bMap加载超时
    };

    Class.prototype.render = function(){
        var that = this;
        var config = that.config;

        if (!window.BMap && !bMap.hasLoad) {//hasLoad可保证同时打开两个bMap的时候不会重复加载两次js
            //不存在bMap则加载
            window[bMap.callbackName] = function(){
                delete window[bMap.callbackName];//删除临时函数
                config.done && config.done(BMap);
            };
            that.load();
            bMap.hasLoad = true;
        }else{
            var timeout = 0;
            config.done && (
                function poll() {
                    if(++timeout > config.timeout * 1000 / 4){
                        return error('bMap加载超时:' + config.timeout + 's');
                    };
                    window.BMap && window.BMap.Map ? config.done(BMap) : setTimeout(poll, 4);//通过核心类Map判断是否加载成功
                }());
        }
    }

    //拼接url
    Class.prototype.url = function(){
        var config = this.config
            ,device = layui.device()
            ,v = config.v
            ,isLite = '';
        config.autolite && (device.android || device.weixin || device.ios) ? v = '1.0' : '';
        v == '1.0' ? isLite = '&type=lite' : '';
        return  "//api.map.baidu.com/api?v="+v+"&ak="+config.ak+"&callback="+bMap.callbackName+isLite;
    }

    //加载百度地图
    Class.prototype.load = function(){
        var script = document.createElement("script");
        script.src = this.url();
        document.body.appendChild(script);
    }

    //核心入口
    bMap.render = function(options){
        if (typeof options === 'function') {
            options = {done:options};
        }
        var inst = new Class(options);
        return thisBmap.call(inst);
    };

    bMap.init = function(){
        bMap.render();
    }

    if (isSetter) {
        //提前请求
        bMap.init();
    }

    exports(MOD_NAME, bMap);
})