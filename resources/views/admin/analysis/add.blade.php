@extends('admin._layoutNew')

@section('page-head')
<style>
    .layui-form-label {
        width: 150px;
    }
    .layui-input-block {
        margin-left: 180px;
    }
</style>
@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">标题</label>
            <div class="layui-input-block">
                <input type="text" name="title" autocomplete="off" placeholder="请输入标题" class="layui-input" value="">
            </div>
        </div>
        <!--<div class="layui-form-item">-->
        <!--    <label class="layui-form-label">头像链接</label>-->
        <!--    <div class="layui-input-block">-->
        <!--        <input type="text" name="avatar" autocomplete="off" placeholder="请输入头像链接" class="layui-input" value="">-->
        <!--    </div>-->
        <!--</div>-->
        <div class="layui-form-item layui-form-text">
			<label class="layui-form-label">头像</label>
			<div class="layui-input-block">
				<button class="layui-btn" type="button" id="img_cover_btn">选择图片</button>
				<br>
				<img src="" id="img_cover" class="cover" style="max-width: 200px;height: auto;margin-top: 5px;">
				<input type="hidden" name="avatar" id="cover" value="">
			</div>
		</div>
        <div class="layui-form-item">
            <label class="layui-form-label">团队人数</label>
            <div class="layui-input-block">
                <input type="text" name="team_num" autocomplete="off" placeholder="请输入团队人数" class="layui-input" value="">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">总收益</label>
            <div class="layui-input-block">
                <input type="text" name="all_income" autocomplete="off" placeholder="请输入总收益" class="layui-input" value="">
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">准确率</label>
            <div class="layui-input-block">
                <input type="text" name="accuracy" autocomplete="off" placeholder="请输入准确率" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">交易单数</label>
            <div class="layui-input-block">
                <input type="text" name="order_num" autocomplete="off" placeholder="请输入交易单数" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">盈利单数</label>
            <div class="layui-input-block">
                <input type="text" name="profit_num" autocomplete="off" placeholder="请输入盈利单数" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">亏损单数</label>
            <div class="layui-input-block">
                <input type="text" name="loss_num" autocomplete="off" placeholder="请输入亏损单数" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">跟随人数</label>
            <div class="layui-input-block">
                <input type="text" name="follow_num" autocomplete="off" placeholder="请输入跟随人数" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">跳转链接</label>
            <div class="layui-input-block">
                <input type="text" name="url" autocomplete="off" placeholder="请输入跳转链接" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">添加记录</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/analysis/post_add')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
        
        layui.use('upload', function(){
		var upload = layui.upload;

		//执行实例
		var uploadInst1 = upload.render({
			elem: '#img_cover_btn' //绑定元素
			,url: '{{URL("api/upload")}}?scene=admin' //上传接口
			,done: function(res) {
				console.log(res);
				//上传完毕回调
				if (res.type == "ok"){
					$("#cover").val(res.message)
					$("#img_cover").show()
					$("#img_cover").attr("src",res.message)
				} else{
					alert(res.message)
				}
			}
			,error: function(){
				//请求异常回调
			}
		});
	});
    </script>

@endsection
