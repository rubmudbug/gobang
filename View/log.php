<?php include ('templates\header.php');?>
<link rel="stylesheet" href="css/log.css">
<div class="container log-face">
    <form class="form-signin">
        <h2 class="form-signin-heading">请 登 录</h2>
        <label for="inputEmail" class="sr-only">邮 箱 地 址</label>
        <input type="email" id="inputEmail" class="form-control" placeholder="邮 箱 地 址" required autofocus>
        <label for="inputPassword" class="sr-only">密 码</label>
        <input type="password" id="inputPassword" class="form-control" placeholder="密 码" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">登 录</button>
    </form>
</div>
<?php include ('templates\footer.php');?>