

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>代理商管理总后台asdfadf</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="../layui/css/layui.css" media="all">
  <script>
  /^http(s*):\/\//.test(location.href) || alert('请先部署到 localhost 下再访问');
  </script>
</head>
<body>
  <div id="LAY_app"></div>
  <script src="../layui/layui.js"></script>
  <script>
  layui.config({
    base: '../agents/' //指定 layuiAdmin 项目路径，本地开发用 src，线上用 dist
    ,version:  new Date().getTime()//'1.2.1'
  }).use('index');
  </script>
</body>
</html>


