<?php session_name("easyTCGFM"); session_start(); define('VALID_INC', TRUE); include 'class_lib.php'; 
$session = new Session;
if ( isset($_POST['login']) ) { $login = $session->start($_POST['username'],$_POST['password'],$_POST['remember']); }
$validsession = $session->validate();
if ( !$validsession ) { $enablelogin = true; include("login.php"); die(); }
else { $username = $_SESSION['username']; $database = new Database; }
$tradecount = $database->num_rows("SELECT * FROM `trades`");
if ( isset($_GET['id']) && $database->num_rows("SELECT * FROM `tcgs` WHERE `id`='".intval($_GET['id'])."'") === 1 ) { $_SESSION['currTCG'] = intval($_GET['id']); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>easyTCG FM</title>
<link href="style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js_lib.js"></script>
</head>

<body>

<a name="top"></a>

<div id="container">

	<div id="topstatus">You are logged in as <strong><?php echo $username; ?></strong> | <a href="logout.php">Logout</a></div>
	<div id="top"><a href="index.php"><img src="images/top.png" alt="" /></a></div>
	<div id="bottomstatus">v.1.0.1</div>
    <div id="nav">
    	<a href="index.php">Dashboard</a>
    	<a href="settings.php">Settings</a>
    	<a href="trades.php">Trades (<?php echo $tradecount; ?>)</a>
    </div>
    
    <div id="floatleft">
    	<div id="leftcol">
    		<form name="tcgselect">
	    		<select name="currTCG" id="currTCG" onchange="location.href=tcgselect.currTCG.options[selectedIndex].value">
					<option value="index.php?id=">-- Active --</option>
					<?php
		        	$result = $database->query("SELECT * FROM `tcgs` WHERE `status` = 'active' ORDER BY `name`");
					
					while ( $row = mysql_fetch_assoc($result) ) {
					?>
					<option value="cards.php?id=<?php echo $row['id'] ?>" <?php if ( $_SESSION['currTCG'] == $row['id'] ) { echo 'selected="selected"'; } ?>><?php echo $row['name']; ?></option>
					<?php } ?>
					<option value="index.php?id="></option>
					<option value="index.php?id=">-- Hiatus --</option>
					<?php
		        	$result = $database->query("SELECT * FROM `tcgs` WHERE `status` = 'hiatus' ORDER BY `name`");
					
					while ( $row = mysql_fetch_assoc($result) ) {
					?>
					<option value="cards.php?id=<?php echo $row['id'] ?>" <?php if ( $_SESSION['currTCG'] == $row['id'] ) { echo 'selected="selected"'; } ?>><?php echo $row['name']; ?></option>
					<?php } ?>
					<option value="index.php?id="></option>
					<option value="index.php?id=">-- Inactive --</option>
					<?php
		        	$result = $database->query("SELECT * FROM `tcgs` WHERE `status` = 'inactive' ORDER BY `name`");
					
					while ( $row = mysql_fetch_assoc($result) ) {
					?>
					<option value="cards.php?id=<?php echo $row['id'] ?>" <?php if ( $_SESSION['currTCG'] == $row['id'] ) { echo 'selected="selected"'; } ?>><?php echo $row['name']; ?></option>
					<?php } ?>
		    	</select>
	    	<div id="newtcgbtn"><a href="newtcg.php"><img src="images/addtcg.gif" title="Add New TCG" alt="" /></a></div>
	    	</form>
	    	<?php if ( $_SESSION['currTCG'] != "" ) { ?>
	    	<div id="leftnav">
	    		<a href="manage.php?id=<?php echo $_SESSION['currTCG']; ?>"><span class="icons" style="background-position: 0px -20px;">TCG Settings</span></a>
	    		<a href="cards.php?id=<?php echo $_SESSION['currTCG']; ?>"><span class="icons" style="background-position: 0px -40px;">Collection</span></a>
	    		<a href="logs.php?id=<?php echo $_SESSION['currTCG']; ?>"><span class="icons" style="background-position: 0px -60px;">Logs</span></a>
	    		<a href="trades.php?id=<?php echo $_SESSION['currTCG']; ?>"><span class="icons" style="background-position: 0px -80px;">Trades (<?php echo $database->num_rows("SELECT * FROM `trades` WHERE `tcg` = '".$_SESSION['currTCG']."'"); ?>)</span></a>
	    		<a href="update.php?id=<?php echo $_SESSION['currTCG']; ?>"><span class="icons" style="background-position: 0px -100px;">Easy Update</span></a>
	    	</div>
	    	<?php } ?>
    	</div>
    </div>
    
    <div id="floatright">	
		<div id="content_top">
		<div id="content">