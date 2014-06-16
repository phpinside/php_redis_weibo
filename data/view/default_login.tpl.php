<? if(!defined('IN_APP')) exit('Access Denied'); include template('header'); ?>
    <div class="container-narrow">
 
      <div class="masthead">
        <div class="pull-left">
          <span><a style="vertical-align: bottom;" href="<?=SITE_URL?>">返回首页</a></span>
        </div>
      <div class="user_nav pull-right">
          <a type="button" class="btn btn-default" role="button" href="?c=user&a=login">登录</a>
          <a type="button" class="btn btn-default" role="button" href="?c=user&a=register">注册</a>
      </div>

    </div>

    <div class="clearfix"></div>
    <hr>

    <div class="container high well">

      <form class="form-signin" role="form" method="post"  action="?c=user&a=login">
        <h2 class="form-signin-heading">用户登录</h2>
        <input type="text" name="username" class="form-control" placeholder="用户名" required="" autofocus="">
        <input type="password" name="password" class="form-control" placeholder="密码" required="">
        <label class="checkbox">
          <input type="checkbox" value="remember">记住密码？
        </label>
        <button  name="submit" class="btn btn-lg btn-primary btn-block" type="submit">立即登录</button>
      </form>

      <div class="alert  noaccount center-block">
        <p class="text-center">
           还没有帐号？<a href="?c=user&a=register">注册</a>
        </p>

      </div>

 
      <p class="text-center">
        <a href="#">忘记密码？</a>
      </p>

    </div> 
<? include template('footer'); ?>
