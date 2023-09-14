layui.use(['element', 'form', 'layedit', 'laypage', 'layer'], function() {
    var element = layui.element, form = layui.form, $ = layui.$, layedit = layui.layedit, laypage = layui.laypage;
    form.on('submit(submit)', function(dataObj) {
        var serData =  $(dataObj.form).serialize();
        $.ajax({
            type : 'POST'
            ,url : window.location.href
            ,data: serData
            ,success: function(data) {
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                icon = data.type == 'ok' ? 1 : 2;
                parent.layer.msg(data.message, {icon: icon});
                icon == 1 && setTimeout(() => {
                    parent.layer.close(index);             
                    parent.window.location.reload();
                }, 1000);
            }
            ,error: function(data) {
                layer.msg('错误：' + data.statusText, {icon: 2});
            }

        });
        return false;        
    });
});