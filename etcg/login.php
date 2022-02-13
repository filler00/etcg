<?php 
if ( $enablelogin ) { 
	
	if ( isset($_POST['lostpass']) ) {
		$database = new Database;
		$sanitize = new Sanitize;
		
		$settings = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting`='email'");
		$email = $settings['value'];
		
		if ( $sanitize->for_db($_POST['email']) === $email ) {
			$passwordNew = md5(time());
			$passwordHash = sha1("$passwordNew".Config::DB_SALT."");
			
			$result = $database->query("UPDATE `settings` SET `value`='".$passwordHash."' WHERE `setting`='password' LIMIT 1");
			if ( !$result ) { $error[] = "Could not update the password. ".$database->error().""; }
			else {
				$message = "Your EasyTCG password has been reset! \n\nNew Password: $passwordNew";
				$headers = "From: EasyTCG";
				if ( !mail($email,'EasyTCG - Password Reset',$message,$headers) ) { $error[] = "Could not send the email."; }
				else { 
					$success[] = "Your password has been reset! The new password has been sent to your email address.";
				}
			}
		} else { $error[] = "Wrong email address!"; }
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow, noarchive">

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

			.form-signin {
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
			
			<form id="login" name="login" method="post" action="" class="form-signin" role="form">
			
				<?php if ( isset($login) && !$login ) { ?>
					<div class="alert alert-danger alert-dismissible" role="alert">
						<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<strong>Error!</strong> Login attempt failed.
					</div>
				<?php } ?>
				<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?>
					<div class="alert alert-danger alert-dismissible" role="alert">
						<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<strong>Error!</strong> <?php echo $msg; ?>
					</div>
				<?php } } ?>
				<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?>
					<div class="alert alert-success alert-dismissible" role="alert">
						<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<strong>Success!</strong> <?php echo $msg; ?>
					</div>
				<?php } } ?>
			
				<div class="form-signin-heading">Easy<span>TCG</span></div>
				<input name="username" id="username" type="text" class="form-control" placeholder="Username" required autofocus>
				<input name="password" id="password" type="password" class="form-control" placeholder="Password" required>
				<div class="checkbox">
					<label>
						<input name="remember" id="remember" type="checkbox" value="1"> Remember me
					</label>
				</div>
				<button name="login" id="login" class="btn btn-lg btn-primary btn-block" type="submit">Log In</button>
				<p class="text-center"><button type="button" class="btn btn-link" data-toggle="modal" data-target="#lost-pass-modal">Lost your password?</button></p>
				
			</form>

		</div> <!-- /container -->
		
		<!-- Lost Password -->
		<div class="modal fade" id="lost-pass-modal" tabindex="-1" role="dialog" aria-labelledby="lost-pass-modal-label" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h2 class="modal-title" id="lost-pass-modal-label">Password Reset</h2>
					</div>
					<div class="modal-body">
						<form role="form" method="post" action="">
							<div class="form-group">
								<label for="email">Verify your email address</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="This must match the email address stored within EasyTCG. The new password will be sent to this email address."></i>
								<input name="email" id="email" type="email" class="form-control" placeholder="your@email.com">
							</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button name="lostpass" id="lostpass" type="submit" class="btn btn-primary">Send me my password!</button>
						</form>
					</div>
				</div>
			</div>
		</div>


		<!-- Bootstrap core JavaScript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
		<script src="js/scripts.js"></script>
	</body>
</html>
<?php } ?>
