<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>后台管理</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">

    <link rel="stylesheet" href="/admin/plugins/layui/css/layui.css" media="all">
    <?php echo $__env->yieldContent('page-head'); ?>
</head>
<body>
<section class="layui-larry-box">
    <div class="larry-personal" style="margin: 10px;">
        <?php echo $__env->yieldContent('page-content'); ?>
    </div>
</section>
<script src="/js/jquery-1.11.1.min.js"></script>
<script src="/admin/plugins/layui/layui.js"></script>
<script src="/admin/plugins/layui/layui.js"></script>
<script>
    /*弹出层*/
    /*
     参数解释：
     title	标题
     url		请求的url
     id		需要操作的数据id
     w		弹出层宽度（缺省调默认值）
     h		弹出层高度（缺省调默认值）
     */
    function layer_show(title,url,w,h,full){
        if (title == null || title == '') {
            title=false;
        };
        if (url == null || url == '') {
            url="404.html";
        };
        if (w == null || w == '') {
            w=800;
        };
        if (h == null || h == '') {
            h=($(window).height() - 50);
        };
        if (full == null || full == ''){
            full = false;
        }else{
            full = true;
        }
        if(full == true){
            var index = layer.open({
                type:2,
                title: title,
                content: url,
            });
            setTimeout(function(){
                layer.full(index);
            },100)
        }else {
            layer.open({
                type: 2,
                area: [w + 'px', h + 'px'],
                fix: false, //不固定
                maxmin: true,
                shade: 0.4,
                title: title,
                content: url,
                offset: '10px',
            });
        }
    }
    /*选择图片弹出层*/
    function upload_select(callback,num)
    {
        var url = '/admin/uploader?form=admin&callback=' + callback + '&num=' + num
        layer.open({
            type: 2,
            area: ['800px', '450px'],
            fix: false, //不固定
            maxmin: true,
            shade:0.4,
            closeBtn:0,
            title: '选择图片',
            content: url,
        });
    }
    /*关闭弹出框口*/
    function layer_close(){
        layer.closeAll();
    }
    function upload_box(callback) {
        layer_show('上传','/admin/uploader?callback=' + callback,600,160);
    }
</script>
<?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
