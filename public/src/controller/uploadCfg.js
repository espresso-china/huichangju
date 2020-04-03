/**
 @Name：文件上传
 @Author：Espresso
 */

layui.define(['form', 'upload'], function (exports) {
    var $ = layui.$
        , layer = layui.layer
        , admin = layui.admin
        , form = layui.form
        , upload = layui.upload;

    //上传图片
    var ins = upload.render({
        url: '../api/upload/save?token=' + layui.data('layuiAdmin').token
        , elem: '.LAY_Upload'
        , before: function (obj) {
            ins.config.data.type = this.type;
        }
        ,accept:'file'
        , done: function (res) {
            if (res.success == true) {
                $('#' + this.id).val(res.data[this.pic]);
                if ($('#' + this.aid).length) {
                    $('#' + this.aid).val(res.data[this.aid])
                }
                form.render();
            } else {
                layer.msg(res.msg, {icon: 5});
            }
        }
        , error: function (res) {

        }
    });
    //上传音频
    var ins = upload.render({
        url: '../api/upload/audio-save?token=' + layui.data('layuiAdmin').token
        , elem: '.LAY_Audio_Upload'
        , before: function (obj) {
            ins.config.data.type = this.type;
        }
        ,accept:'file'
        , done: function (res) {
            console.log(res.data[this.pic])
            if (res.success == true) {
                $('#' + this.id).val(res.data[this.pic]);
                if ($('#' + this.aid).length) {
                    $('#' + this.aid).val(res.data[this.aid])
                }
                form.render();
            } else {
                layer.msg(res.msg, {icon: 5});
            }
        }
        , error: function (res) {

        }
    });

    var baseUrl = (function () {
        return document.domain;
    })();

    //查看图片
    admin.events.Preview = function (othis) {
        var Src = $('#' + $(this).data('id'));
        if (!Src.val()) {
            layer.msg('图片为空', {icon: 5});
            return false;
        }

        var src = Src.val();
        if (src.match(/(http|https):\/\/([\w.]+\/?)\S*/) == null) {
            src = '/' + src;
        }
        layer.photos({
            photos: {
                "title": "查看图片"
                , "data": [{
                    "src": src
                }]
            }
            , shade: 0.6
            , closeBtn: 1
            , anim: 5
        });
    };
    //对外暴露的接口
    exports('uploadCfg', {});
});
