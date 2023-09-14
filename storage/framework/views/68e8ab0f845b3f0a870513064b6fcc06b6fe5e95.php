<?php $__env->startSection('page-head'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(URL("layui/css/layui.css")); ?>" media="all">
<link rel="stylesheet" type="text/css" href="<?php echo e(URL("admin/common/bootstrap/css/bootstrap.css")); ?>" media="all">
<link rel="stylesheet" type="text/css" href="<?php echo e(URL("admin/common/global.css")); ?>" media="all">
<link rel="stylesheet" type="text/css" href="<?php echo e(URL("admin/css/personal.css")); ?>" media="all">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page-content'); ?>
	<form class="layui-form" method="POST">
		<input type="hidden" name="id" value="<?php if(isset($news['id'])): ?><?php echo e($news['id']); ?><?php endif; ?>">
		<?php echo e(csrf_field()); ?>

		<div class="layui-form-item">
			<label class="layui-form-label">新闻标题</label>
			<div class="layui-input-block">
				<input class="layui-input newsName" name="title" lay-verify="required" placeholder="请输入文章标题" type="text" value="<?php if(isset($news['title'])): ?><?php echo e($news['title']); ?><?php endif; ?>">
			</div>
		</div>
		<div class="layui-form-item">
			<div class="layui-inline">
				<label class="layui-form-label">所属分类</label>
				<div class="layui-input-inline">
					<select name="c_id" class="" lay-filter="c_id" lay-verify="required">
						<?php $__currentLoopData = $cateList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				        <option value="<?php if(isset($cate['id'])): ?><?php echo e($cate['id']); ?><?php endif; ?>" <?php if(isset($news) && $news['c_id'] == $cate['id']): ?> selected <?php endif; ?>><?php if(isset($cate['name'])): ?><?php echo e($cate['name']); ?><?php endif; ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				    </select>
				</div>
			</div>
			<div class="layui-inline">
				<label class="layui-form-label">浏览权限</label>
				<div class="layui-input-inline">
					<select name="browse_grant" class="" lay-filter="browse_grant" lay-verify="required">
				        <option value="0" <?php if(isset($news) && $news['browse_grant'] == 0): ?> selected <?php endif; ?>>开放浏览</option>
				        <option value="1" <?php if(isset($news) && $news['browse_grant'] == 1): ?> selected <?php endif; ?>>会员浏览</option>
				    </select>
				</div>
			</div>
			<div class="layui-inline">
				<label class="layui-form-label">语言</label>
				<div class="layui-input-inline">
					<select name="lang" class="" lay-filter="lang" lay-verify="required">
						<?php $__currentLoopData = $langList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				        <option value="<?php echo e($lang); ?>" <?php echo e(isset($news['lang']) && $news['lang'] == $lang ? 'selected' : ''); ?>><?php echo e($lang); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				    </select>
				</div>
			</div>
			<div class="layui-inline">		
				<label class="layui-form-label">顺序</label>
				<div class="layui-input-inline">
					<input class="layui-input" lay-verify="required" name="sorts" type="text" value="<?php echo e($news->sorts ?? 0); ?>">
				</div>
				<div class="layui-form-mid layui-word-aux">数字越大越靠前</div>
			</div>
		</div>

		<div class="layui-form-item layui-form-text">
			<label class="layui-form-label">缩略图</label>
			<div class="layui-input-block">
				<button class="layui-btn" type="button" id="upload_test">选择图片</button>
				<br>
				<img src="<?php if(!empty($news->thumbnail)): ?><?php echo e($news->thumbnail); ?><?php endif; ?>" id="img_thumbnail" class="thumbnail" style="display: <?php if(!empty($news->thumbnail)): ?><?php echo e("block"); ?><?php else: ?><?php echo e("none"); ?><?php endif; ?>;max-width: 200px;height: auto;margin-top: 5px;">
				<input type="hidden" name="thumbnail" id="thumbnail" value="<?php if(!empty($news->thumbnail)): ?><?php echo e($news->thumbnail); ?><?php endif; ?>">
			</div>
		</div>

		<div class="layui-form-item layui-form-text">
			<label class="layui-form-label">封面</label>
			<div class="layui-input-block">
				<button class="layui-btn" type="button" id="img_cover_btn">选择图片</button>
				<br>
				<img src="<?php echo e($news->cover ?? ''); ?>" id="img_cover" class="cover" style="display: <?php if(!empty($news->cover)): ?><?php echo e("block"); ?><?php else: ?><?php echo e("none"); ?><?php endif; ?>;max-width: 200px;height: auto;margin-top: 5px;">
				<input type="hidden" name="cover" id="cover" value="<?php echo e($news->cover ?? ''); ?>">
			</div>
		</div>

		<div class="layui-form-item">
			<div class="layui-inline">
				<label class="layui-form-label">自定义属性</label>
				<div class="layui-input-block">
					<input name="recommend" class="tuijian" title="推荐" type="checkbox" value="1" <?php if(isset($news) && $news['recommend'] == 1): ?> checked <?php endif; ?> >
					<input name="audit" class="newsStatus" title="审核" type="checkbox" value="1" <?php if(isset($news) && $news['audit'] == 1): ?> checked <?php endif; ?> >
					<input name="display" class="isShow" title="展示" type="checkbox" value="1" <?php if(isset($news) && $news['display'] == 1): ?> checked <?php endif; ?> >
					<!-- <input name="discuss" class="isShow" title="评论" type="checkbox" value="1" <?php if(isset($news) && $news['discuss'] == 1): ?> checked <?php endif; ?> > -->
				</div>
			</div>
			<div class="layui-inline">		
				<label class="layui-form-label">文章作者</label>
				<div class="layui-input-inline">
					<input class="layui-input newsAuthor" lay-verify="required" placeholder="请输入文章作者" name="author" type="text" value="<?php if(isset( $news['author'] )): ?><?php echo e($news['author']); ?><?php else: ?>管理员<?php endif; ?>">
				</div>
			</div>
			<div class="layui-inline">		
				<label class="layui-form-label">阅读量</label>
				<div class="layui-input-inline">
					<input class="layui-input newsTime" lay-verify="required" name="views" type="text" value="<?php if(isset( $news['views'] )): ?><?php echo e($news['views']); ?><?php else: ?><?php echo e(0); ?><?php endif; ?>">
				</div>
			</div>
			<div class="layui-inline">		
				<label class="layui-form-label">发布时间</label>
				<div class="layui-input-inline">
					<input class="layui-input newsTime" lay-verify="required|date" name="create_time" type="text" value="<?php if(isset($news['create_time'])): ?><?php echo e(substr($news['create_time'], 0, 10)); ?><?php else: ?><?php echo e(date('Y-m-d')); ?><?php endif; ?>" id="create_time">
				</div>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">关键字</label>
			<div class="layui-input-block">
				<input class="layui-input" placeholder="请输入文章关键字" type="text" name="keyword" value="<?php if(isset( $news['keyword'] )): ?><?php echo e($news['keyword']); ?><?php endif; ?>">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">内容摘要</label>
			<div class="layui-input-block">
				<textarea placeholder="请输入内容摘要" class="layui-textarea" name="abstract"><?php if(isset($news['abstract'])): ?><?php echo e($news['abstract']); ?><?php endif; ?></textarea>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">文章内容</label>
			<div class="layui-input-block">
				<script id="news_content" name="content" type="text/plain" style="width:100%; height:300px;"><?php if(isset($news['content'])): ?><?php echo $news['content']; ?><?php endif; ?></script>
			</div>
		</div>
		<div class="layui-form-item">
			<div class="layui-input-block">
				<button class="layui-btn" lay-submit="" lay-filter="submit">立即提交</button>
				<button type="reset" class="layui-btn layui-btn-primary">重置</button>
		    </div>
		</div>
	</form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<script type="text/javascript" src="<?php echo e(URL('vendor/ueditor/1.4.3/ueditor.config.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(URL('vendor/ueditor/1.4.3/ueditor.all.js')); ?>"> </script>
<script type="text/javascript" src="<?php echo e(URL('vendor/ueditor/1.4.3/lang/zh-cn/zh-cn.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(URL("/admin/js/newsFormSubmit.js?v=").time()); ?>"></script>
<script>
	layui.use('upload', function(){
		var upload = layui.upload;

		//执行实例
		var uploadInst = upload.render({
			elem: '#upload_test' //绑定元素
			,url: '<?php echo e(URL("api/upload")); ?>?scene=admin' //上传接口
			,done: function(res){
				//上传完毕回调
				if (res.type == "ok"){
					$("#thumbnail").val(res.message)
					$("#img_thumbnail").show()
					$("#img_thumbnail").attr("src",res.message)
				} else{
					alert(res.message)
				}
			}
			,error: function(){
				//请求异常回调
			}
		});

		//执行实例
		var uploadInst1 = upload.render({
			elem: '#img_cover_btn' //绑定元素
			,url: '<?php echo e(URL("api/upload")); ?>?scene=admin' //上传接口
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>