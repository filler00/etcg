<?php

session_name("easyTCGFM"); session_start(); define('VALID_INC', TRUE); include 'class_lib.php';

$session = new Session;

$session->close();

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>EasyTCG FM | Login</title>

		<!-- Fonts -->
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:600italic,400,700|Open+Sans+Condensed:300,700' rel='stylesheet' type='text/css'>
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

		<!-- Bootstrap core CSS -->
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

		<!-- Custom styles for this template -->
		<link href="style.css" rel="stylesheet">
		<style>
			body {
				padding-top: 40px;
				padding-bottom: 40px;
			}

			.container, .form-signin {
				max-width: 330px;
				padding: 15px;
				margin: 0 auto;
			}
			
			.form-signin-heading {
				font-family: "Open Sans";
				font-weight: 600;
				font-style: italic;
				font-size: 42px;
				line-height: 47px;
				color: #5EB3D6;
				text-transform: lowercase;
				text-shadow: 1px 1px 2px #fff;
				text-align: center;
			}
			.form-signin-heading span {
				font-family: "Open Sans Condensed";
				font-weight: 700;
				font-style: normal;
				font-size: 32px;
				color: #666;
				text-transform: uppercase;
			}
			
			.form-signin .form-signin-heading,
			.form-signin .checkbox {
				margin-bottom: 10px;
			}
			.form-signin .checkbox {
				font-weight: normal;
			}
			.form-signin .form-control {
				position: relative;
				height: auto;
				-webkit-box-sizing: border-box;
				 -moz-box-sizing: border-box;
						box-sizing: border-box;
				padding: 10px;
				font-size: 16px;
			}
			.form-signin .form-control:focus {
				z-index: 2;
			}
			.form-signin input[name="username"] {
				margin-bottom: -1px;
				border-bottom-right-radius: 0;
				border-bottom-left-radius: 0;
			}
			.form-signin input[type="password"] {
				margin-bottom: 10px;
				border-top-left-radius: 0;
				border-top-right-radius: 0;
			}
		</style>

		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>

	<body>

		<div class="container">
			
			<div class="panel panel-default">
				<div class="panel-body">
					<p class="text-info text-center">You have been logged out.</p>
					<p class="text-center">&raquo; <a href="index.php">Return to the Login Page</a></p>
				</div>
			</div>

		</div> <!-- /container -->


		<!-- Bootstrap core JavaScript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
		<script src="js/scripts.js"></script>
	</body>
</html>