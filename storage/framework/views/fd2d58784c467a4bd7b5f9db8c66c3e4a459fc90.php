<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="row">
            <div class=" col-md-12">
                <div class="box-color">
                    <h4><?php echo e(trans('auth.sign_account')); ?></h4>
                    <br>
                    <?php echo Form::open(['url' => url('signin'), 'method' => 'post', 'name' => 'form']); ?>

                    <div class="form-group <?php echo e($errors->has('email') ? 'has-error' : ''); ?>">
                        <?php echo Form::label(trans('auth.email')); ?> :
                        <span><?php echo e($errors->first('email', ':message')); ?></span>
                        <?php echo Form::email('email', null, ['class' => 'form-control', 'required'=>'required', 'placeholder'=>'E-mail' ]); ?>

                    </div>
                    <div class="form-group <?php echo e($errors->has('password') ? 'has-error' : ''); ?>">
                        <?php echo Form::label(trans('auth.password')); ?> :
                        <span><?php echo e($errors->first('password', ':message')); ?></span>
                        <?php echo Form::password('password', ['class' => 'form-control', 'required'=>'required', 'placeholder'=>'Password']); ?>

                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="remember" value="remember" name="remember">
                            <i class="primary"></i> <?php echo e(trans('auth.keep_login')); ?>

                        </label>
                    </div>
                    <input type="submit" class="btn btn-primary btn-block" value="<?php echo e(trans('auth.login')); ?>"></input>
                    <?php echo Form::close(); ?>

                </div>
                <hr class="separator">
                <div class="text-center">
                    <h5><a href="<?php echo e(url('forgot')); ?>" class="forgot_pw _600"><?php echo e(trans('auth.forgot')); ?>?</a></h5>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>