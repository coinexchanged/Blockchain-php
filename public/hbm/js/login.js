// 密码隐藏显示
function shpass() {
    $("#text").toggle();
    $("#password").toggle();
    if ($("#yanjing").hasClass("icon-yanjing")) {
        $("#yanjing").removeClass("icon-yanjing").addClass("icon-yanjing1")
    } else {
        $("#yanjing").removeClass("icon-yanjing1").addClass("icon-yanjing")
    }
}
function txtblur() {
    $("#password").val($("#text").val());
}
function passblur() {
    $("#text").val($("#password").val());
};


$(document).keydown(function (event) {
    if (event.keyCode == 13) {
        login();
    }
});

function login() {
    var tel = $("#account_number").val();
    var reg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
    
    var t = function () {
        if (tel.length == 11 && !reg.test(tel)) {
            return false;
        } else {
            return true;
        }
    }
    var pass = $("#password").val();
    var p = function () {
        if (pass.length >= 6 && $("#mes2").html() != '请输入正确密码') {
            return true;
        } else {
            return false;
        }
    }
    if (t() == true && p() == true) {
        $.ajax({
            type: "post",
            url: _API + "user/login",
            data: { "user_string": tel, "password": pass },
            dataType: 'json',
            success: function (data) {
                console.log(data)
                if (data.type == 'ok') { 
                    layer_msg("登录成功")     
                    set_user(data.message, 7);
                    window.location.href ="../views/index.html";
                } else {
                    layer_msg(data.message)
                }
            }
        })
    } else {
        layer_msg("手机号码 邮箱不正确或密码不对")
    }
}