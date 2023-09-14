<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/layuiadmin/style/admin.css" media="all">
    @yield('page-head')
    
</head>

<body class="">
    @yield('page-content')
</body>
<script src="/layuiadmin/layui/layui.js"></script>
<script>
    /^http(s*):\/\//.test(location.href) || alert('请先部署到 localhost 下再访问');
</script>
<script>
    layui.config({
        version: true //一般用于更新模块缓存，默认不开启。设为true即让浏览器不缓存。也可以设为一个固定的值，如：201610
        ,debug: true //用于开启调试模式，默认false，如果设为true，则JS模块的节点会保留在页面
        ,base: '/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use('index');


</script>

@yield('scripts')
</html> 