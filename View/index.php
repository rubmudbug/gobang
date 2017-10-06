<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
    <title>GiLiGiLi</title>

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/chess.css" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://cdn.bootcss.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="js/chess.js"></script>
</head>
<body onload="onLoad_d()">

<div class="cent">
    <canvas id="canvas" width="808" height="785"></canvas>

</div>

<div class="userlist">
    <ul class="list-group">
        <li class="list-group-item"><b>房间列表:</b>
            （当前在&nbsp;房间<?php echo isset($_GET['room_id'])&&intval($_GET['room_id'])>0 ? intval($_GET['room_id']):1; ?>）<br></li>
        <li class="list-group-item">
            <a href="/?room_id=1">房间一</a>
            <span class="badge count"></span>
            <ul class="dropdown-menu client_list">
            </ul>
        </li>
        <li class="list-group-item">
            <a href="/?room_id=2">房间二</a>
            <span class="badge count"></span>
            <ul class="dropdown-menu client_list">
            </ul>
        </li>
        <li class="list-group-item">
            <a href="/?room_id=3">房间三</a>
            <span class="badge count"></span>
            <ul class="dropdown-menu client_list">

            </ul>
        </li>
        <li class="list-group-item">
            <a href="/?room_id=4">房间四</a>
            <span class="badge count"></span>
            <ul class="dropdown-menu client_list">

            </ul>
        </li>
        <li class="list-group-item">
            <a href="/?room_id=5">房间五</a>
            <span class="badge"></span>
            <ul class="dropdown-menu client_list">
            </ul>
        </li>
    </ul>
</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
</body>
</html>