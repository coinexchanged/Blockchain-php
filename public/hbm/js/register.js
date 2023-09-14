$("#agree").click(function () {
    $("#sendSet").toggleClass("agree");
    var st = $("#sendSet").attr("disabled");
    if (st == "disabled") {
        $("#sendSet").removeAttr("disabled");
    } else {
        $("#sendSet").attr({ "disabled": "disabled" });
    }
})

//注册验证密码
$("#setpassword").change(function () {
    var pass = $("#setpassword").val();
    if (pass.length < 6 || pass.length > 16) {
        // $("#mes1").html("密码的长度在6~16位");
        layer_msg("密码的长度在6~16位");

    } 
})
$("#verifypassword").change(function () {
    if ($("#setpassword").val() != $("#verifypassword").val()) {
        layer_msg("两次密码输入不一致");
    }
})

$('#sendSet').click(function () {
    var s = function(){

        var ps = /^[a-zA-Z0-9]{6,16}$/;
        if (!ps.test($('#setpassword').val())) {
            layer_msg('密码必须为 6-16 位');
        } else {
            return true;
        }
    }

    var ss = function () {
        if ($('#verifypassword').val() == $('#setpassword').val()) {
            return true;
        } else {
            layer_msg('两次密码输入不一致');
        }
    }
    var name = $('#name').val();
    var verify = $("#verificate").val();
    var pass = $('#setpassword').val();
    var repass = $("#verifypassword").val();
    var reg = /^1[3456789]\d{9}$/;
    var emreg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;

    if (s() == true && ss() == true) {
        if (reg.test(name)) {
            $.ajax({
                type: "post",
                url: _API+"user/register",
                data: { "user_string": name, "password": pass, "extension_code": "", "re_password": repass, "type": "mobile", "code": verify },
                datatype: "json",
                success: function (data) {
                    console.log(data.message);
                    if (data.type == "ok") {
                        layer_msg(data.message);
                        
                        $("#sendSet").attr("disabled", "true");
                        setTimeout(function () {
                            $("#sendSet").removeAttr("disabled");
                            window.location.href = "login.html";
                        }, 3000);
                    }
                }
            });
        } else if (emreg.test(name)) {
            $.ajax({
                type: "post",
                url: _API + "user/register",
                data: { "user_string": name, "password": pass, "extension_code": "", "re_password": repass, "type": "email", "code": verify },
                datatype: "json",
                success: function (data) {
                    console.log(data.message);
                    //layer_msg(data.message);
                    if (data.type == "ok") {
                        layer_msg(data.message);
                        $("#sendSet").attr("disabled", "true");
                        setTimeout(function () {
                            $("#sendSet").removeAttr("disabled");
                            window.location.href = "login.html";
                        }, 3000);
                    }
                }
            });
        }
        
    } else {
        layer_msg('请检查填写信息')
    }
});
//
function maVals() {
    var url = window.location.search;
    var theRequest = new Object();
    if (url.indexOf("?") != -1) {
        var str = url.substr(1);
        strs = str.split("&");
        for (i = 0; i < strs.length; i++) {
            theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
        }
    }
    return theRequest;
}
var v = maVals();
$("#name").attr("value", v['user_string']);
$("#verificate").attr("value", v['code']);
