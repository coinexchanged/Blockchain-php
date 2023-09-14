<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta name="renderer" content="webkit">
    <title>管理系统</title>
    <link href="<?php echo e(URL("winadmin/lib/layui/css/layui.css",null,true)); ?>" rel="stylesheet" />
    <link href="<?php echo e(URL("winadmin/lib/animate/animate.min.css",null,true)); ?>" rel="stylesheet" />
    <link href="<?php echo e(URL("winadmin/lib/font-awesome-4.7.0/css/font-awesome.css",null,true)); ?>" rel="stylesheet" />
    <link href="<?php echo e(URL("winadmin/lib/winui/css/winui.css",null,true)); ?>" rel="stylesheet" />

    <style>
        body {
            /*在页面顶部加载背景最佳，如有必要这块可以从数据库读取*/
            background-image: url(<?php echo e(URL("winadmin/images/bg_05.jpg")); ?>);
        }
    </style>
</head>
<body>
<!-- 桌面 -->
<div class="winui-desktop">

</div>

<!-- 开始菜单 -->
<div class="winui-start sp layui-hide">
    <!-- 左边设置 -->
    <div class="winui-start-left">
        <div class="winui-start-item bottom" data-text="个人中心"><i class="fa fa-user"></i></div>
        <div class="winui-start-item winui-start-individuation bottom" data-text="主题设置"><i class="fa fa-cog"></i></div>
        <div class="winui-start-item bottom logout" data-text="注销登录"><i class="fa fa-power-off"></i></div>
    </div>
    <!-- 中间导航 -->
    <div class="winui-start-center">
        <div class="layui-side-scroll">
            <ul class="winui-menu layui-nav layui-nav-tree" lay-filter="winuimenu"></ul>
        </div>
    </div>
    <!-- 右边磁贴 -->
    <div class="winui-start-right">
        <div class="layui-side-scroll">
            
                
                    
                    
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                    
                
                
                    
                    
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                    
                
                
                    
                    
                        
                            
                            
                        
                        
                            
                            
                        
                        
                            
                            
                        
                    
                
                
                    
                    
                        
                            
                            
                        
                    
                
            </div>
        </div>
    </div>
</div>

<!-- 任务栏 -->
<div class="winui-taskbar">
    <!-- 开始菜单触发按钮 -->
    <div class="winui-taskbar-start sp"><i class="fa fa-windows"></i></div>
    <!-- 任务项 -->
    <ul class="winui-taskbar-task"></ul>
    <!-- 任务栏时间 -->
    <div class="winui-taskbar-time"></div>
    <!-- 控制中心 -->
    <div class="winui-taskbar-console sp">
        <i class="fa fa-comment-o"></i>
    </div>
    <!-- 显示桌面 -->
    <div class="winui-taskbar-desktop">

    </div>
</div>

<!--控制中心-->
<div class="winui-console layui-hide slideOutRight sp">
    <h1>最新通知</h1>
    <div class="winui-message">
        <div class="layui-side-scroll">
            <div class="winui-message-item">
                <h2>暂无新的通知</h2>
                <div class="content">
                    暂无！
                </div>
            </div>
        </div>
    </div>







</div>

<!--layui.js-->
<script src="<?php echo e(URL("winadmin/lib/layui/layui.js",null,true)); ?>"></script>
<script>
    layui.config({
        base: '<?php echo e(URL("winadmin/js",null,true)); ?>/' //指定 index.js 路径
        , version: '1.0.0-beta'
    }).use('index');
</script>
</body>
</html>
