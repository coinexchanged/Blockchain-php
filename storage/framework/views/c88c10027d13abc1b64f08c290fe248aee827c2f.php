<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<title>后台管理系</title>
	<script src="/js/jquery.js"></script>
	<style>
		body {
			background: #ebebeb;
			font-family: "Helvetica Neue","Hiragino Sans GB","Microsoft YaHei","\9ED1\4F53",Arial,sans-serif;
			color: #222;
			font-size: 12px;
		}
		*{padding: 0px;margin: 0px;}
		.top_div{
			background: #008ead;
			width: 100%;
			height: 400px;
		}
		.ipt{
			border: 1px solid #d3d3d3;
			padding: 10px 10px;
			width: 290px;
			border-radius: 4px;
			padding-left: 35px;
			-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
			box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
			-webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
			-o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
			transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s
		}
		.ipt:focus{
			border-color: #66afe9;
			outline: 0;
			-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
			box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6)
		}
		.u_logo{
			background: url("images/phone.png") no-repeat;
			padding: 10px 10px;
			position: absolute;
			top: 43px;
			left: 40px;
			background-size: 15px;
		}
		.m_logo{
			background: url("images/username.png") no-repeat;
			padding: 10px 10px;
			position: absolute;
			top: 22px;
			left: 40px;
		}
		.p_logo{
			background: url("images/password.png") no-repeat;
			padding: 10px 10px;
			position: absolute;
			top: 12px;
			left: 40px;
		}
		a{
			text-decoration: none;
		}
		.tou{
			background: url("images/tou.png") no-repeat;
			width: 97px;
			height: 92px;
			position: absolute;
			top: -87px;
			left: 140px;
		}
		.left_hand{
			background: url("images/left_hand.png") no-repeat;
			width: 32px;
			height: 37px;
			position: absolute;
			top: -38px;
			left: 150px;
		}
		.right_hand{
			background: url("images/right_hand.png") no-repeat;
			width: 32px;
			height: 37px;
			position: absolute;
			top: -38px;
			right: -64px;
		}
		.initial_left_hand{
			background: url("images/hand.png") no-repeat;
			width: 30px;
			height: 20px;
			position: absolute;
			top: -12px;
			left: 100px;
		}
		.initial_right_hand{
			background: url("images/hand.png") no-repeat;
			width: 30px;
			height: 20px;
			position: absolute;
			top: -12px;
			right: -112px;
		}
		.left_handing{
			background: url("images/left-handing.png") no-repeat;
			width: 30px;
			height: 20px;
			position: absolute;
			top: -24px;
			left: 139px;
		}
		.right_handinging{
			background: url("images/right_handing.png") no-repeat;
			width: 30px;
			height: 20px;
			position: absolute;
			top: -21px;
			left: 210px;
		}

	</style>
	<script type="text/javascript">
		$(function(){
			//得到焦点
			$("#password").focus(function(){
				$("#left_hand").animate({
					left: "150",
					top: " -38"
				},{step: function(){
					if(parseInt($("#left_hand").css("left"))>140){
						$("#left_hand").attr("class","left_hand");
					}
				}}, 2000);
				$("#right_hand").animate({
					right: "-64",
					top: "-38px"
				},{step: function(){
					if(parseInt($("#right_hand").css("right"))> -70){
						$("#right_hand").attr("class","right_hand");
					}
				}}, 2000);
			});
			//失去焦点
			$("#password").blur(function(){
				$("#left_hand").attr("class","initial_left_hand");
				$("#left_hand").attr("style","left:100px;top:-12px;");
				$("#right_hand").attr("class","initial_right_hand");
				$("#right_hand").attr("style","right:-112px;top:-12px");
			});
		});
	</script>
</head>
<body>
<div class="top_div"></div>
<div style="width: 400px;height: 200px;margin: auto auto;background: #ffffff;text-align: center;margin-top: -100px;border: 1px solid #e7e7e7">
	<div style="width: 165px;height: 96px;position: absolute">
		<div class="tou"></div>
		<div id="left_hand" class="initial_left_hand"></div>
		<div id="right_hand" class="initial_right_hand"></div>
	</div>

	<p style="padding: 30px 0px 10px 0px;position: relative;">
		<span class="u_logo"></span>
		<input class="ipt" type="text" id="username" name="name" placeholder="请输入用户名">
	</p>
	<p style="position: relative;">
		<span class="p_logo"></span>
		<input id="password" class="ipt" id="password" name="password" type="password"  placeholder="请输入密码">
	</p>
	<!--<p style="padding: 10px 0px 10px 0px;position: relative;">-->
	<!--<span class="m_logo"></span>-->
	<!--<input class="ipt" type="text" placeholder="请输入验证码">-->
	<!--</p>-->

	<div style="height: 50px;line-height: 50px;margin-top: 30px;border-top: 1px solid #e7e7e7;">
		<p style="margin: 0px 35px 20px 45px;">
			<!--<span style="float: left"><a href="#" style="color:#ccc;">忘记密码?</a></span>-->
           <span style="float: right">
               <!--<a href="#" style="color:#ccc;margin-right:10px;">注册</a>-->
			   <!--<a href="#" style="background: #008ead;padding: 7px 10px;border-radius: 4px;border: 1px solid #1a7598;color: #FFF;font-weight: bold;">获取验证码</a>-->
               <a href="JavaScript:void(0)" class="enterlogin" style="background: #008ead;padding: 7px 10px;border-radius: 4px;border: 1px solid #1a7598;color: #FFF;font-weight: bold;">登录</a>
           </span>
		</p>
	</div>
</div>
<script>
	$('.enterlogin').click(function () {
		var username = $('#username').val()
		if (username == ''){
			$('#username').blur();return false;
		}
		var password = $('#password').val()
		if (password == ''){
			$('#password').blur();return false;
		}
		$.ajax({
			type : "POST",
			url : "/admin/login",
			data : {
				"username" : username,
				"password" : password
			},
			success : function(result) {
				if (result.type == 'ok'){
					window.location.href="/winadmin";
				}else{
					alert(result.message)
				}
			}
		});
	})

	document.onkeydown=function(event){
		var e = event || window.event || arguments.callee.caller.arguments[0];
		if(e && e.keyCode==13){ // enter 键
			$('.enterlogin').click();
		}
	};

</script>
<div style="position: fixed;bottom: 0px;text-align: center;width: 100%;">
	Copyright ©2018 <a style="margin-left: 10px;color: #000000;text-decoration: underline" href="/">后台管理系统</a>
</div>
</body>
</html>