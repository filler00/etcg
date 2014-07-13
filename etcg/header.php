<?php session_name("easyTCGFM"); session_start(); define('VALID_INC', TRUE); include 'class_lib.php'; 
$session = new Session;
if ( isset($_POST['login']) ) { $login = $session->start($_POST['username'],$_POST['password'],$_POST['remember']); }
$validsession = $session->validate();
if ( !$validsession ) { $enablelogin = true; include("login.php"); die(); }
else { $username = $_SESSION['username']; $database = new Database; }
$tradecount = $database->num_rows("SELECT * FROM `trades`");
if ( isset($_GET['id']) && $database->num_rows("SELECT * FROM `tcgs` WHERE `id`='".intval($_GET['id'])."'") === 1 ) { $_SESSION['currTCG'] = intval($_GET['id']); }
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="icon" href="../../favicon.ico">

		<title>EasyTCG FM | Tradepost Management</title>
	
		<!-- Fonts -->
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:600italic,400,700|Open+Sans+Condensed:300,700' rel='stylesheet' type='text/css'>
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

		<!-- Bootstrap core CSS -->
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

		<!-- Custom styles for this template -->
		<link href="style.css" rel="stylesheet">

		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	
	<body>

		<div class="container">

			<!-- Static navbar -->
			<div class="navbar navbar-default" role="navigation">
				<div class="container-fluid">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="index.php">Easy<span>TCG</span> <span>v1.1.0</span></a>
					</div>
					<div class="navbar-collapse collapse">
						<ul class="nav navbar-nav">
							<li><a href="index.php">Dashboard</a></li>
							<li><a href="settings.php">Settings</a></li>
							<li><a href="trades.php">Trades <span class="badge"><?php echo $tradecount; ?></span></a></li>
						</ul>
						
						<ul class="nav navbar-nav navbar-right">
							<li><p class="navbar-text"><i class="fa fa-user"></i> <strong><?php echo $username; ?></strong></p></li>
							<li><a href="logout.php" class="btn btn-danger navbar-btn btn-logout"><i class="fa fa-power-off"></i> Logout</a></li>
						</ul>
					</div><!--/.nav-collapse -->
				</div><!--/.container-fluid -->
			</div>

			<div class="row row-offcanvas row-offcanvas-right">

				<div class="col-xs-12 col-sm-9">
					<p class="pull-right visible-xs">
						<button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Manage &nbsp; <i class="fa fa-chevron-circle-right"></i></button>
					</p>
					<div class="row row-content">