/**

 @Name：layuiAdmin 用户管理 管理员管理 角色管理 权限管理
 @Author：star1029
 @Site：http://www.layui.com/admin/
 @License：LPPL

 */
layui.define(['table', 'form'], function (exports) {
    var $ = layui.$
        , admin = layui.admin
        , view = layui.view
        , table = layui.table
        , router = layui.router()
        , form = layui.form;

    //会员管理
    table.render({
        elem: '#LAY-user-manage'
        , url: '../api/member/list?token=' + layui.data('layuiAdmin').token //模拟接口
        , cols: [[
            {type: 'checkbox', fixed: 'left'}
            , {field: 'uid', width: 60, title: 'ID', align: 'center', sort: true}
            , {field: 'member_name', title: '用户名'}
            , {field: 'phone', title: '手机', align: 'center',width:120}
            , {field: 'email', title: '邮箱',width:180,align: 'center'}
            , {field: 'member_level_name', title: '等级', align: 'center',width:90}
            , {field: 'member_point', title: '平台积分', align: 'center',width:90}
            , {field: 'member_balance', title: '平台余额', align: 'center',width:90}
            , {title: '操作', width: 130, align: 'center', fixed: 'right', toolbar: '#table-useradmin-webuser'}
        ]]
        , page: true
        , limit: 30
        , height: 'full-320'
    });

    //监听工具条
    table.on('tool(LAY-user-manage)', function (obj) {
        var data = obj.data;
        if (obj.event === 'del') {
            layer.prompt({
                formType: 1
                , title: '敏感操作，请验证口令'
            }, function (value, index) {
                layer.close(index);

                layer.confirm('真的删除行么', function (index) {
                    obj.del();

                    layer.close(index);
                });
            });
        } else if (obj.event === 'edit') {
            admin.popup({
                title: '编辑用户'
                , area: ['500px', '450px']
                , id: 'LAY-popup-user-add'
                , success: function (layero, index) {
                    view(this.id).render('user/user/userform', data).done(function () {
                        form.render(null, 'layuiadmin-form-useradmin');

                        //监听提交
                        form.on('submit(LAY-user-front-submit)', function (data) {
                            var field = data.field; //获取提交的字段

                            //提交 Ajax 成功后，关闭当前弹层并重载表格
                            //$.ajax({});
                            layui.table.reload('LAY-user-manage'); //重载表格
                            layer.close(index); //执行关闭
                        });
                    });
                }
            });
        }
    });

    //管理员管理
    table.render({
        elem: '#LAY-user-back-manage'
        , url: '../api/user/list?token=' + layui.data('layuiAdmin').token //模拟接口
        , method: 'post'
        , height: 'full-250'
        , cols: [[
            // , {field: 'id', width: 80, align: 'center', title: 'ID', sort: true}
            {field: 'name', width: 120, align: 'center', title: '用户名'}
            , {field: 'phone', width: 120, align: 'center', title: '手机'}
            , {field: 'user_email', width: 180, title: '登录邮箱'}
            , {field: 'role_name', align: 'center', width: 140, title: '角色'}
            , {field: 'shop_name', align: 'center', title: '所属店铺'}
            , {field: 'created_time', width: 170, align: 'center', title: '加入时间', sort: true}
            , {field: 'check', width: 70, title: '状态', templet: '#buttonTpl', align: 'center'}
            , {title: '操作', width: 160, align: 'center', fixed: 'right', toolbar: '#table-useradmin-admin'}
        ]]
        , text: '对不起，加载出现异常！'
        , page: true
        , done: function (res) {
            if (res.code == layui.setter.response.statusCode.logout) {
                layer.alert(res.msg, function (index) {
                    layer.close(index);
                    view.exit();
                });
            }
        }
    });

    //监听工具条
    table.on('tool(LAY-user-back-manage)', function (obj) {
        var data = obj.data;
        if (obj.event === 'del') {
            layer.prompt({
                formType: 1
                , title: '敏感操作，请验证口令'
            }, function (value, index) {
                layer.close(index);
                layer.confirm('确定删除此管理员？', function (index) {
                    admin.req({
                        url: '../api/user/delete',
                        type: "post",
                        data: {id: data.id},
                        async: false,
                        done: function (res) {
                            if (res.code == 0) {
                                obj.del();
                                layer.msg('删除成功！', {icon: 1, time: 1000})
                            } else {
                                layer.msg(res.msg, {icon: 2, time: 1000})
                            }
                        }
                    })
                    layer.close(index);
                });
            });
        } else if (obj.event === 'edit') {
            admin.popup({
                title: '编辑管理员'
                , area: ['600px', '550px']
                , id: 'LAY-popup-user-add'
                , success: function (layero, index) {
                    view(this.id).render('system/administrators/admin-add', data).done(function () {


                        admin.req({
                            url: '../api/roles/allRoles'
                            , data: {id: data.id}
                            , type: 'post'
                            , done: function (res) {
                                if (res.code == 0) {
                                    var html = '';
                                    for (var i = 0; i < res.data.length; i++) {
                                        if (res.data[i].checked) {
                                            html += ' <input type="checkbox" name="roles[]" value="' + res.data[i].id + '" title="' + res.data[i].display_name + '" checked>';
                                        } else {
                                            html += ' <input type="checkbox" name="roles[]" value="' + res.data[i].id + '" title="' + res.data[i].display_name + '">';
                                        }
                                    }
                                    $('#layui-roles').html(html);
                                    console.log($('#layui-roles'));
                                } else {
                                    layer.msg('获取失败', {
                                        offset: '15px'
                                        , icon: 2
                                        , time: 1000
                                    });
                                }

                                //渲染表单
                                form.render(null, 'layuiadmin-form-admin');
                            }
                        });

                        admin.req({
                            url: '../api/shop/all'
                            , data: {}
                            , type: 'post'
                            , done: function (res) {
                                if (res.code == 0) {
                                    let shop_id = data.shop_id;
                                    var html = '<option value="0">平台账号</option>';
                                    for (var i = 0; i < res.data.length; i++) {
                                        let select = (shop_id == res.data[i].shop_id) ? 'selected' : '';
                                        html += ' <option value="' + res.data[i].shop_id + '" ' + select + '>' + res.data[i].shop_name + '</option>';
                                    }
                                    $('#shop_id').html(html);
                                } else {
                                    layer.msg('获取失败', {
                                        offset: '15px'
                                        , icon: 2
                                        , time: 1000
                                    });
                                }
                                //渲染表单
                                form.render(null, 'layuiadmin-form-admin');
                            }
                        });

                        //监听提交
                        form.on('submit(LAY-user-back-submit)', function (data) {
                            var field = data.field; //获取提交的字段

                            //提交 Ajax 成功后，关闭当前弹层并重载表格
                            admin.req({
                                url: '../api/user/register'
                                , data: data.field
                                , async: false
                                , type: 'post'
                                , done: function (res) {
                                    layer.msg(res.msg, {
                                        offset: '15px'
                                        , icon: res.code == 0 ? 1 : 2
                                        , time: 1000
                                    });
                                    layui.table.reload('LAY-user-back-manage'); //重载表格
                                    layer.close(index); //执行关闭
                                }
                            })

                        });
                    });
                }
            });
        }
    });

    var useradmin_actives = {
        batchdel: function () {
            var checkStatus = table.checkStatus('LAY-user-back-manage')
                , checkData = checkStatus.data; //得到选中的数据

            if (checkData.length === 0) {
                return layer.msg('请选择数据');
            }

            layer.prompt({
                formType: 1
                , title: '敏感操作，请验证口令'
            }, function (value, index) {
                layer.close(index);

                layer.confirm('确定删除吗？', function (index) {

                    //执行 Ajax 后重载
                    /*
                    admin.req({
                      url: 'xxx'
                      //,……
                    });
                    */
                    table.reload('LAY-user-back-manage');
                    layer.msg('已删除');
                });
            });
        }
        , add: function () {
            admin.popup({
                title: '添加管理员'
                , area: ['600px', '550px']
                , id: 'LAY-popup-useradmin-add'
                , success: function (layero, index) {
                    view(this.id).render('system/administrators/admin-add').done(function () {

                        admin.req({
                            url: '../api/roles/allRoles'
                            , data: {}
                            , type: 'post'
                            , done: function (res) {
                                if (res.code == 0) {
                                    var html = '';
                                    for (var i = 0; i < res.data.length; i++) {
                                        html += ' <input type="checkbox" name="roles[]" value="' + res.data[i].id + '" title="' + res.data[i].display_name + '">';
                                    }
                                    $('#layui-roles').html(html);
                                    console.log($('#layui-roles'));
                                } else {
                                    layer.msg('获取失败', {
                                        offset: '15px'
                                        , icon: 2
                                        , time: 1000
                                    });
                                }
                                //渲染表单
                                form.render(null, 'layuiadmin-form-admin');
                            }
                        });

                        admin.req({
                            url: '../api/shop/all'
                            , data: {}
                            , type: 'post'
                            , done: function (res) {
                                if (res.code == 0) {
                                    var html = '<option value="0">平台账号</option>';
                                    for (var i = 0; i < res.data.length; i++) {
                                        html += ' <option value="' + res.data[i].shop_id + '">' + res.data[i].shop_name + '</option>';
                                    }
                                    $('#shop_id').html(html);
                                } else {
                                    layer.msg('获取失败', {
                                        offset: '15px'
                                        , icon: 2
                                        , time: 1000
                                    });
                                }
                                //渲染表单
                                form.render(null, 'layuiadmin-form-admin');
                            }
                        });

                        //监听提交
                        form.on('submit(LAY-user-back-submit)', function (data) {
                            var field = data.field; //获取提交的字段

                            //提交 Ajax 成功后，关闭当前弹层并重载表格
                            admin.req({
                                url: '../api/user/register'
                                , data: field
                                , async: false
                                , type: 'post'
                                , done: function (res) {
                                    if (res.code == 0) {
                                        parent.layer.closeAll();
                                        layui.table.reload('LAY-user-back-manage'); //重载表格
                                        layer.msg(res.msg, {icon: 1, time: 1000})
                                    } else {
                                        layer.msg(res.msg, {icon: 2, time: 3000})
                                    }
                                }
                            })
                        });
                    });
                }
            });
        }
    }

    //角色管理
    table.render({
        elem: '#table-roles'
        , url: '../api/roles/lists?token=' + layui.data('layuiAdmin').token
        , method: 'post'
        , height: 'full-250'
        , cols: [[
            {field: 'id', width: 70, title: 'ID', align: 'center', sort: true, fixed: 'left'}
            , {field: 'type_name', align: 'center', title: '后台',width:160,}
            , {field: 'display_name', align: 'center', title: '角色名称',width:120}
            , {field: 'name', align: 'center', title: '系统标识',width:120}
            , {field: 'description', title: '说明'}
            , {field: 'created_at', align: 'center', title: '创建时间',width:170}
            , {title: '操作', align: 'center', fixed: 'right', width: 100, toolbar: '#role-table-operate-bar'}

        ]]
        , page: true
        , done: function (res) {
            if (res.code == layui.setter.response.statusCode.logout) {
                layer.alert(res.msg, function (index) {
                    layer.close(index);
                    view.exit();
                });
            }
        }
    });

    //监听工具条
    table.on('tool(role-table-operate)', function (obj) {
        var data = obj.data;
        if (obj.event === 'detail') {
            layer.msg('ID：' + data.id + ' 的查看操作');
        } else if (obj.event === 'del') {
            layer.confirm('真的删除行么', function (index) {
                obj.del();
                layer.close(index);
                admin.req({
                    url: '../api/roles/delete',
                    type: "post",
                    data: {id: data.id},
                    async: false,
                    done: function (res) {
                        if (res.code == 0) {
                            layer.msg('删除成功！', {icon: 1, time: 1000})
                        } else {
                            layer.msg('删除失败！', {icon: 2, time: 1000})
                        }
                    }
                })
            });
        } else if (obj.event === 'edit') {
            admin.popup({
                title: '编辑角色'
                , area: ['1000px', '720px']
                , id: 'LAY-popup-user-add'
                , success: function (layero, index) {
                    view(this.id).render('system/administrators/role-add', data).done(function () {

                        //监听提交
                        form.on('submit(role-save)', function (formData) {
                            formData.field['id'] = data.id;
                            admin.req({
                                url: '../api/roles/add'
                                , data: formData.field
                                , async: false
                                , type: 'post'
                                , done: function (res) {
                                    parent.layer.msg(res.msg, {
                                        offset: '15px'
                                        , icon: res.code == 0 ? 1 : 2
                                        , time: 1000
                                    });
                                    parent.layer.closeAll();
                                    parent.layui.table.reload('table-roles');
                                }
                            })
                        });

                    });
                }
            });
        }
    });

    var role_actives = {
        add: function () { //获取选中数据
            admin.popup({
                title: '新增角色'
                , area: ['1000px', '720px']
                , id: 'LAY-popup-user-add'
                , success: function (layero, index) {
                    view(this.id).render('system/administrators/role-add').done(function () {

                        //监听提交
                        form.on('submit(role-save)', function (formData) {
                            formData.field['id'] = 0;
                            admin.req({
                                url: '../api/roles/add'
                                , data: formData.field
                                , async: false
                                , type: 'post'
                                , done: function (res) {
                                    parent.layer.msg(res.msg, {
                                        offset: '15px'
                                        , icon: res.code == 0 ? 1 : 2
                                        , time: 1000
                                    });
                                    parent.layer.closeAll();
                                    parent.layui.table.reload('table-roles');
                                }
                            })
                        });

                    });
                }
            });

        }
        , getCheckLength: function () { //获取选中数目
            var checkStatus = table.checkStatus('role-table-operate')
                , data = checkStatus.data;
            layer.msg('选中了：' + data.length + ' 个');
        }
        , isAll: function () { //验证是否全选
            var checkStatus = table.checkStatus('role-table-operate');
            layer.msg(checkStatus.isAll ? '全选' : '未全选')
        }
    };


    //权限管理
    table.render({
        elem: '#table-permission'
        , url: '../api/permissions/lists?token=' + layui.data('layuiAdmin').token
        , where: {fid: router.search.fid || 0}
        , method: 'post'
        , height: 'full-250'
        , cols: [[
            {field: 'id', align: 'center', width: 60, title: 'ID', sort: true, fixed: 'left'}
            , {field: 'type_name', align: 'center', width: 120, title: '后台'}
            , {field: 'display_name', align: 'center', minWidth: 150, title: '权限名称'}
            , {field: 'name', align: 'left', minWidth: 150, title: '路由名称'}
            , {field: 'uri', align: 'left', minWidth: 150, title: '资源'}
            , {field: 'sort', align: 'left', minWidth: 60, title: '排序', edit: 'text'}
            , {field: 'description', title: '说明', minWidth: 150, hide: true}
            , {field: 'is_menu', width: 100, align: 'center', title: '菜单', templet: '#permissionIsMenu'}
            , {field: 'disabled', width: 100, align: 'center', title: '禁用', templet: '#permissionStatus'}
            , {field: 'created_at', width: 160, align: 'center', title: '创建时间'}
            , {title: '操作', align: 'center', fixed: 'right', width: 160, toolbar: '#permission-table-operate-barDemo'}
        ]]
        , page: true
        , done: function (res) {
            if (res.code == layui.setter.response.statusCode.logout) {
                layer.alert(res.msg, function (index) {
                    layer.close(index);
                    view.exit();
                });
            }
        }
    });

    //监听表格复选框选择
    table.on('checkbox(permission-table-operate)', function (obj) {
        console.log(obj)
    });

    //监听工具条
    table.on('tool(permission-table-operate)', function (obj) {
        var data = obj.data;
        if (obj.event === 'child') {
            location.hash = '/system/administrators/permission/fid=' + data.id;
        } else if (obj.event === 'del') {
            layer.confirm('真的删除行么', function (index) {
                obj.del();
                layer.close(index);
                admin.req({
                    url: '../api/permissions/delete?token=' + layui.data('layuiAdmin').token,
                    type: "post",
                    data: {id: data.id},
                    async: false,
                    done: function (res) {
                        if (res.code == 0) {
                            layer.msg('删除成功！', {icon: 1, time: 1000})
                        } else {
                            layer.msg('删除失败！', {icon: 2, time: 1000})
                        }
                    }
                })
            });
        } else if (obj.event === 'edit') {

            admin.popup({
                title: '编辑权限'
                , area: ['640px', '600px']
                , id: 'LAY-popup-user-add'
                , success: function (layero, index) {
                    view(this.id).render('system/administrators/permission-add', data).done(function () {

                        var types = data.type.split(',');
                        $.each(types, function (index, type) {
                            $("input[name='type[]'][value=" + type + "]").attr("checked", true);
                        });

                        if (data.is_menu == 1) {
                            $("input[name=is_menu]").attr("checked", true);
                        }
                        if (data.disabled == 1) {
                            $("input[name=disabled]").attr("checked", true);
                        }

                        admin.req({
                            url: '../api/permissions/menus'
                            , data: {}
                            , async: false
                            , done: function (res) {
                                if (res.code == 0) {
                                    var menus = res.data;

                                    var options = '<option value="0">顶级菜单</option>';
                                    for (var i = 0; i < menus.length; i++) {
                                        options += '<option value="' + menus[i].id + '">' + menus[i].display_name + '</option>'
                                        for (var j = 0; j < menus[i].sub_menus.length; j++) {
                                            options += '<option value="' + menus[i].sub_menus[j].id + '">&nbsp;&nbsp;' + menus[i].sub_menus[j].display_name + '</option>'
                                        }
                                    }

                                    $("#form-permission-fid").html(options);

                                    $("#form-permission-fid").val(data.fid);
                                }

                                $("#form-permission-fid").find("option[value='" + data.id + "']").attr("disabled", "disabled")

                                //渲染表单
                                form.render(null, 'form-permission');
                            }
                        });

                        //监听提交
                        form.on('submit(form-permission-submit)', function (formData) {
                            formData.field['id'] = data.id;
                            admin.req({
                                url: '../api/permissions/add'
                                , data: formData.field
                                , method: 'post'
                                , async: false
                                , done: function (res) {
                                    parent.layer.msg(res.msg, {
                                        offset: '15px'
                                        , icon: res.code == 0 ? 1 : 2
                                        , time: 1000
                                    });
                                    parent.layer.closeAll();
                                    parent.layui.table.reload('table-permission');
                                }
                            })
                        });
                    });
                }
            });
        }
    });

    var permission_actives = {
        add: function () { //获取选中数据
            admin.popup({
                title: '添加权限'
                , area: ['640px', '600px']
                , id: 'LAY-popup-user-add'
                , success: function (layero, index) {
                    view(this.id).render('system/administrators/permission-add').done(function () {

                        admin.req({
                            url: '../api/permissions/menus'
                            , data: {}
                            , async: false
                            , done: function (res) {
                                if (res.code == 0) {
                                    var menus = res.data;

                                    var options = '<option value="0">顶级菜单</option>';
                                    for (var i = 0; i < menus.length; i++) {
                                        options += '<option value="' + menus[i].id + '">' + menus[i].display_name + '</option>'
                                        for (var j = 0; j < menus[i].sub_menus.length; j++) {
                                            options += '<option value="' + menus[i].sub_menus[j].id + '">&nbsp;&nbsp;' + menus[i].sub_menus[j].display_name + '</option>'
                                        }
                                    }

                                    $("#form-permission-fid").html(options);
                                }

                                //渲染表单
                                form.render(null, 'form-permission');
                            }
                        });

                        //监听提交
                        form.on('submit(form-permission-submit)', function (formData) {
                            admin.req({
                                url: '../api/permissions/add'
                                , data: formData.field
                                , method: 'post'
                                , async: false
                                , done: function (res) {
                                    parent.layer.msg(res.msg, {
                                        offset: '15px'
                                        , icon: res.code == 0 ? 1 : 2
                                        , time: 1000
                                    });
                                    parent.layer.closeAll();
                                    parent.layui.table.reload('table-permission');
                                }
                            })
                        });
                    });
                }
            });

        }
        , getCheckLength: function () { //获取选中数目
            var checkStatus = table.checkStatus('permission-table-operate')
                , data = checkStatus.data;
            layer.msg('选中了：' + data.length + ' 个');
        }
        , isAll: function () { //验证是否全选
            var checkStatus = table.checkStatus('permission-table-operate');
            layer.msg(checkStatus.isAll ? '全选' : '未全选')
        }
    };

    exports('useradmin', {
        permission: {actives: permission_actives},
        admin: {actives: useradmin_actives},
        role: {actives: role_actives}
    })
});
