$(document).keydown(function (event) {
    if (event.keyCode == 13) {
        logon();
    }
});
var vue = new Vue({
    el: '#app',
    data: {
        userName: '',
        passwords: '',
        checkboxValue: true,
        areaList: [],
        codeId: '',
        areaCode: '',
        areaText: '',
        listShow: false,
        status: 'email',
        listLangShow: false,
        langLogo: '',
    },
    mounted: function () {
        let that = this;
        // FastClick.attach(document.body);
        that.userName = localStorage.getItem('userName') || '';
        that.passwords = localStorage.getItem('passwords') || '';
        that.checkboxValue = localStorage.getItem('loginStute') || false;
        if (that.userName != '' && that.passwords != '') {
            $("#sendLogin").css("background", "#f0b90b");
        } else {
            $("#sendLogin").css("background", "#7d818a")
        }
        $.ajax({
            type: "post",
            url: _API + "area_code",
            dataType: "json",
            success: function (data) {
                if (data.type == "ok") {
                    var datas = data.message;
                    if (datas.length > 0) {
                        that.areaList = datas;
                        that.areaText = datas[0].name;
                        that.areaCode = datas[0].area_code;
                        that.codeId = datas[0].id;
                    }


                } else {
                    layer_msg(data.message);
                }
            }
        });

        if (getLocal('language') && getLocal('language') == 'zh') {
            that.langLogo = 'images/cn.png';
        } else if (getLocal('language') && getLocal('language') == 'hk') {
            that.langLogo = 'images/hk.png';

        } else if (getLocal('language') && getLocal('language') == 'jp') {
            that.langLogo = 'images/jp_f.png';
        } else if (getLocal('language') && getLocal('language') == 'kr') {
            that.langLogo = 'images/kr_f.png';
            // $('.logo-lang').attr('src', 'images/kr_f.png');
        } else {
            that.langLogo = 'images/en.png';
            // $('.logo-lang').attr('src', 'images/en.png');
        }

    },
    methods: {
        // 密码显示或者隐藏
        shpass() {
            $("#text").toggle();
            $("#password").toggle();
            if ($("#imgs").attr('src') == 'images/accountm.png') {
                $("#imgs").attr('src', 'images/eyes.png');
            } else {
                $("#imgs").attr('src', 'images/accountm.png');
            }
        },
        passblur() {
            $("#text").val($("#password").val());
        },

        setLocal(name, value) {
            window.localStorage.setItem(name, value)
        },

        getLocal(name) {

            return window.localStorage.getItem(name) || '';
        },
        // 密码验证
        passwordConfirm() {
            var pass = $("#password").val();
            if ($('#name').val() != '' && $('#password').val() != '') {
                $("#sendLogin").css("background", "#f0b90b");
            } else {
                $("#sendLogin").css("background", "#7d818a")
            }
            if (pass.length < 6 || pass.length > 16) {
                $("#mes2").html(getlg('plength'));
            } else {
                $("#mes2").html("");
            }
        },
        // 用户验证
        userConfirm() {
            if ($('#name').val() != '' && $('#password').val() != '') {
                $("#sendLogin").css("background", "#f0b90b");
            } else {
                $("#sendLogin").css("background", "#7d818a")
            }
        },
        setLanguage(e) {
            window.setLang(e);
            window.location.href = window.location.href;
        },
        download(){
            window.location.href = 'https://app.ifti.top/mobile/download.html'
        },
        // 点击登录
        logon() {
            let that = this;

            if (that.status == "mobile") {
                that.userName = $('#name').val();
                var reg = /^[0-9]\d*$/;
                if (!that.userName) {
                    layer_msg(getlg('paccount'));
                    return false;
                } else if (!reg.test(that.userName)) {
                    layer_msg(getlg('pmobile'));
                    return false;
                }
            } else {
                that.userName = $('#email-account').val();
                if (that.userName.indexOf('@') == -1) {
                    layer_msg(getlg('mnot'));
                    return false;
                }
            }
            that.passwords = $('#password').val();
            if (!that.passwords) {
                layer_msg(getlg('pinpwd'));
                return false;
            } else if (that.passwords.length < 6) {
                layer_msg(getlg('ptpwd'));
                return false;
            }
            if (that.checkboxValue) {
                localStorage.setItem('userName', that.userName);
                localStorage.setItem('passwords', that.passwords);
                localStorage.setItem('loginStute', that.checkboxValue);
            } else {
                localStorage.setItem('userName', '');
                localStorage.setItem('passwords', '');
                localStorage.setItem('loginStute', false);
            }
            var data = {};
            data.user_string = that.userName;
            data.password = that.passwords;
            if (that.status == "mobile") {
                data.area_code = that.areaCode;
                data.area_code_id = that.codeId;
            }
            initDatas({
                url: 'user/login',
                data: data,
                type: 'post'
            }, function (res) {
                if (res.type == 'ok') {
                    layer_msg(getlg('lgsuccess'))
                    set_user(res.message, 7);
                    setTimeout(function () {
                        window.location.href = "index.html";
                    }, 500)
                } else {
                    layer_msg(data.message);

                }
            });
        },
        selectInput(val) {
            let that = this;
        },
        selectedTab() {
            var that = this;
            that.listShow = true;
        },
        areaSelected(ids, areaCode, name) {
            var that = this;
            that.codeId = ids;
            that.areaCode = areaCode;
            that.areaText = name;
            that.listShow = false;
        },
        slectedTap() {
            var that = this;
            that.status = that.status == 'mobile' ? 'email' : 'mobile'
        }


    }
});