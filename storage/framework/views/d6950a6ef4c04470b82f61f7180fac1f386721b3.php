<meta charset="UTF-8">
<title>
    <?php echo e(isset($title) ? $title : 'CRMSMART'); ?> | <?php echo e(Settings::get('site_name')); ?>

</title>
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<meta id="token" name="token" value="<?php echo e(csrf_token()); ?>">
<?php if(Sentinel::check()): ?>
    <meta id="pusherKey" name="pusherKey" value="<?php echo e(Settings::get('pusher_key')); ?>">
    <meta id="userId" name="userId" value="<?php echo e($user_data->id); ?>">
<?php endif; ?>