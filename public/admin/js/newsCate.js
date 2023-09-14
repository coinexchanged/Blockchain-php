layui.use(['element', 'form', 'layedit', 'laypage', 'layer'], function() {
    var element = layui.element, form = layui.form, $ = layui.$, layedit = layui.layedit, laypage = layui.laypage;
    $('#newsCateAdd').click(function() {
        var index = layer.open({
            title:'添加新闻分类'
            ,type:2
            ,content: '/admin/news_cate_add'
            ,area: ['529px', '342px']
        });
    });

    $('.newsCateEdit').click(function(){
        var id = $(this).data('id');
        var index = layer.open({
            title: '编辑新闻分类'
            ,type: 2
            ,content: '/admin/news_cate_edit/' + id
            ,area: ['529px', '342px']
            ,maxmin: true
        });
    });

    $('.newsCateDel').click(function() {
        var id = $(this).data('id');
        layer.confirm('真的确定要删除吗？',
        function(index) {
            $.ajax({
                url : '/admin/news_cate_del/' + id
                , success: function(data) {
                    if(data.type == 'ok') {
                        layer.close(index);
                        layer.msg(data.message, {icon:1});
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);                        
                    } else {
                        layer.msg(data.message, {icon:2});
                    }
                }
                , error: function(data) {
                    layer.msg('删除失败!服务器错误:' + data.statusText + ',错误码：' + data.status + '.');
                }
            });          
        });
    });
});