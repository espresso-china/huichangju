{
    "code"
:
    0
        , "msg"
:
    ""
        , "data"
:
    [{
        "title": "主页"
        , "icon": "layui-icon-home"
        , "list": [{
            "title": "控制台"
            , "jump": "/"
        }]
    }, {
        "name": "user"
        , "title": "用户"
        , "icon": "layui-icon-user"
        , "list": [{
            "name": "administrators-list"
            , "title": "后台用户"
            , "jump": "user/administrators/list"
        }, {
            "name": "member"
            , "title": "会员信息"
            , "jump": "user/user/list"
        }, {
            "name": "suggest"
            , "title": "会员建议"
            , "jump": "user/user/suggest"
        }, {
            "name": "search"
            , "title": "搜索记录"
            , "jump": "user/user/search"
        }]
    }, {
        "name": "news"
        , "title": "内容"
        , "icon": "layui-icon-link"
        , "list": [{
            "name": "news-list"
            , "title": "资讯列表"
            , "jump": "news/list"
        }, {
            "name": "news-classify"
            , "title": "资讯分类"
            , "jump": "news/classify"
        }, {
            "name": "activity-list"
            , "title": "活动列表"
            , "jump": "activity/list"
        }, {
            "name": "activity-join"
            , "title": "报名列表"
            , "jump": "activity/join"
        }]
    }, {
        "name": "promoter"
        , "title": "分销"
        , "icon": "layui-icon-cart-simple"
        , "list": [{
            "name": "promoter-list"
            , "title": "团长列表"
            , "jump": "promoter/list"
        }, {
            "name": "promoter-whole"
            , "title": "全部团长"
            , "jump": "promoter/promoter-whole"
        }, {
            "name": "promoter-account"
            , "title": "团长账户"
            , "jump": "promoter/account"
        }, {
            "name": "promoter-total"
            , "title": "全部账户"
            , "jump": "promoter/total"
        }, {
            "name": "promoter-goods"
            , "title": "分销商品"
            , "jump": "promoter/goods/list"
        }, {
            "name": "commission-list"
            , "title": "佣金申请"
            , "jump": "promoter/commission/list"
        }, {
            "name": "commission-whole"
            , "title": "全部申请"
            , "jump": "promoter/commission/whole"
        }]
    }, {
        "name": "promotion"
        , "title": "营销"
        , "icon": "layui-icon-diamond"
        , "list": [{
            "name": "promotion-miaosha"
            , "title": "秒杀活动"
            , "jump": "promotion/miaosha"
        }]
    }, {
        "name": "goods"
        , "title": "商品"
        , "icon": "layui-icon-component"
        , "list": [
            {
                "name": "goods-category"
                , "title": "商品分类"
                , "jump": "goods/category/list"
            },
            {
                "name": "goods-attribute"
                , "title": "商品类型"
                , "jump": "goods/attribute/list"
            },
            {
                "name": "goods-spec"
                , "title": "商品规格"
                , "jump": "goods/spec/list"
            },
            {
                "name": "goods-list"
                , "title": "全部商品"
                , "jump": "goods/list"
            },
            {
                "name": "shop-goods-list"
                , "title": "我的商品"
                , "jump": "goods/shop-goods-list"
            },
            {
                "name": "shop-goods-add"
                , "title": "发布商品"
                , "jump": "goods/add"
            }, {
                "name": "shop-goods-group-whole"
                , "title": "商品分组"
                , "jump": "goods/group/whole-list"
            }, {
                "name": "shop-goods-group"
                , "title": "店内分组"
                , "jump": "goods/group/list"
            }, {
                "name": "goods-brand-whole"
                , "title": "全部品牌"
                , "jump": "goods/brand/whole-list"
            }, {
                "name": "goods-brand"
                , "title": "我的品牌"
                , "jump": "goods/brand/list"
            }, {
                "name": "goods-brand"
                , "title": "全部评价"
                , "jump": "goods/evaluate/list"
            }, {
                "name": "goods-brand"
                , "title": "商品评价"
                , "jump": "goods/evaluate/shop-evaluate-list"
            }
        ]
    }, {
        "name": "order"
        , "title": "订单"
        , "icon": "layui-icon-list"
        , "list": [
            {
                "name": "order-list"
                , "title": "全部订单"
                , "jump": "order/list"
            },
            {
                "name": "shop-order-list"
                , "title": "我的订单"
                , "jump": "order/shop-order-list"
            }
        ]
    }, {
        "name": "shop"
        , "title": "商家"
        , "icon": "layui-icon-template"
        , "list": [
            {
                "name": "shop-list"
                , "title": "全部商家"
                , "jump": "shop/list"
            },
            {
                "name": "shop-config"
                , "title": "商家设置"
                , "jump": "shop/config"
            },
            {
                "name": "shop-apply-list"
                , "title": "商家申请"
                , "jump": "shop/apply/list"
            }, {
                "name": "shop-pickup-point"
                , "title": "提货点"
                , "jump": "shop/pickup/list"
            }
        ]
    }, {
        "name": "finance"
        , "title": "财务"
        , "icon": "layui-icon-rmb"
        , "list": [
            {
                "name": "shop-account-list"
                , "title": "商家资金"
                , "jump": "finance/shop-account-list"
            },
            {
                "name": "member-account-list"
                , "title": "会员资金"
                , "jump": "finance/member-account-list"
            }
            ,
            {
                "name": "member-account-period"
                , "title": "商城账期"
                , "jump": "finance/period-account-list"
            },
            {
                "name": "shop-account-withdraw"
                , "title": "商家提现"
                , "jump": "finance/shop/withdraw/list"
            }
            ,
            {
                "name": "shop-account-whole-withdraw"
                , "title": "全部提现"
                , "jump": "finance/shop/withdraw/whole-list"
            }
            ,
            {
                "name": "shop-records"
                , "title": "财务流水"
                , "spread": true
                , "list": [
                    {
                        "name": "shop-account-money-records"
                        , "title": "入账流水"
                        , "jump": "finance/records/shop-money-list"
                    }
                    ,
                    {
                        "name": "shop-account-proceed-records"
                        , "title": "收益流水"
                        , "jump": "finance/records/shop-proceed-list"
                    }
                    ,
                    {
                        "name": "shop-account-profit-records"
                        , "title": "营业额流水"
                        , "jump": "finance/records/shop-profit-list"
                    }
                    ,
                    {
                        "name": "shop-account-records"
                        , "title": "账户流水"
                        , "jump": "finance/records/shop-list"
                    }
                    ,
                    {
                        "name": "shop-account-return-records"
                        , "title": "抽成流水"
                        , "jump": "finance/records/shop-return-list"
                    }
                    ,
                    {
                        "name": "shop-account-commission-records"
                        , "title": "佣金流水"
                        , "jump": "finance/records/shop-commission-list"
                    }
                    ,
                    {
                        "name": "shop-account-withdraw-records"
                        , "title": "提现流水"
                        , "jump": "finance/records/shop-withdraw-list"
                    }
                ]
            },
            {
                "name": "platform-records"
                , "title": "平台流水"
                , "spread": true
                , "list": [
                    {
                        "name": "platform-account-records"
                        , "title": "平台账户流水"
                        , "jump": "finance/platform-records/platform-list"
                    },
                    {
                        "name": "platform-account-return-records"
                        , "title": "平台利润流水"
                        , "jump": "finance/platform-records/platform-return-list"
                    },
                    {
                        "name": "platform-account-withdraw-records"
                        , "title": "平台提现流水"
                        , "jump": "finance/platform-records/platform-withdraw-list"
                    }
                ]
            }
        ]
    }, {
        "name": "set"
        , "title": "设置"
        , "icon": "layui-icon-set"
        , "list": [
            {
                "name": "set"
                , "title": "平台设置"
                , "jump": "set/system/website"
            },
            {
                "name": "system"
                , "title": "小程序设置"
                , "spread": true
                , "list": [
                    {
                        "name": "menus"
                        , "title": "导航设置"
                        , "jump": "system/menu"
                    }
                    , {
                        "name": "menus"
                        , "title": "轮播图设置"
                        , "jump": "system/focus"
                    }
                ]
            }
            , {
                "name": "system"
                , "title": "系统设置"
                , "spread": true
                , "list": [
                    {
                        "name": "system"
                        , "title": "短信服务"
                        , "jump": "system/sms"
                    }, {
                        "name": "system"
                        , "title": "地区管理"
                        , "jump": "system/region/region"
                    }, {
                        "name": "administrators-rule"
                        , "title": "角色管理"
                        , "jump": "user/administrators/role"
                    }, {
                        "name": "administrators-permission"
                        , "title": "权限管理"
                        , "jump": "user/administrators/permission"
                    }
                ]
            }
            , {
                "name": "user"
                , "title": "我的设置"
                , "spread": true
                , "list": [
                    {
                        "name": "info"
                        , "title": "基本资料"
                    }, {
                        "name": "password"
                        , "title": "修改密码"
                    }
                ]
            }]
    }]
}