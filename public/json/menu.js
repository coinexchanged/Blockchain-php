{
    "code": 0,
    "msg": "",
    "data": [{
    "title": "主页",
    "icon": "layui-icon-home",
    "list": [{
        "title": "控制台",
        "jump": "/"
    }]
}, {
    "name": "user",
    "title": "代理商",
    "icon": "layui-icon-user",
    "list": [{
        "name": "user",
        "title": "代理商列表",
        "jump": "user/user/list"
    }]
}, {
    "name": "senior"
    ,"title": "统计报表"
    ,"icon": "layui-icon-senior"
    ,"list": [{
            "name": "line"
            ,"title": "折线图"
            ,"jump": "senior/line"
        },{
            "name": "bar"
            ,"title": "柱状图"
          ,"jump": "senior/bar"
        },{
            "name": "map"
            ,"title": "地图"
          ,"jump": "senior/map"
        }]
}, {
    "name": "set",
    "title": "设置",
    "icon": "layui-icon-set",
    "list": [{
        "name": "system",
        "title": "系统设置",
        "spread": false,
        "list": [{
            "name": "website",
            "title": "网站设置"
        }, {
            "name": "email",
            "title": "邮件服务"
        }]
    }, {
        "name": "user",
        "title": "我的设置",
        "spread": false,
        "list": [{
            "name": "info",
            "title": "基本资料"
        }, {
            "name": "password",
            "title": "修改密码"
        }]
    }]
}]
}