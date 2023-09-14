<?php $__env->startSection('page-head'); ?>
<link rel="stylesheet" type="text/css" href="<?php echo e(URL("admin/css/personal.css")); ?>" media="all">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page-content'); ?>
	<form class="layui-form" method="POST">
		<?php echo e(csrf_field()); ?>

        <input type="hidden" name="id" value="<?php if(isset($id)): ?><?php echo e($id); ?><?php endif; ?>" >
		<div class="layui-form-item">
			<label class="layui-form-label">分类名称</label>
			<div class="layui-input-block">
				<input class="layui-input newsName" name="name" lay-verify="required" placeholder="请输入分类名称" type="text" value="<?php if(isset($name)): ?><?php echo e($name); ?><?php endif; ?>">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">显示顺序</label>
			<div class="layui-input-block">
				<input class="layui-input" placeholder="请输入文章关键字" type="text" name="sorts" value="<?php if(isset($sorts)): ?><?php echo e($sorts); ?><?php else: ?><?php echo e(0); ?><?php endif; ?>">
			</div>
		</div>
		
		<div class="layui-form-item">
			<label class="layui-form-label">是否显示</label>
			<div class="layui-input-block">
                <input type="radio" name="is_show" value="1" title="是" <?php if(isset($is_show)): ?>  <?php if($is_show == 1): ?> checked <?php endif; ?> <?php else: ?> checked <?php endif; ?> >
                <input type="radio" name="is_show" value="0" title="否" <?php if(isset($is_show) && $is_show == 0): ?> checked <?php endif; ?>>
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
<script type="text/javascript" src="<?php echo e(URL("/admin/js/newsCateForm.js")); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>