<?php include 'header.php';

$database = new Database;
function trim_value(&$value) { $value = trim($value); }

if ( isset($_POST['newtrade']) ) {
	
	$sanitize = new Sanitize;
	
	$tcgid = intval($_POST['tcg']);
	$trader = $sanitize->for_db($_POST['trader']);
	$email = $sanitize->for_db($_POST['email']);
	$giving = $sanitize->for_db($_POST['giving']);
	$receiving = $sanitize->for_db($_POST['receiving']);
	$type = $sanitize->for_db($_POST['type']);
	$grab = intval($_POST['grab']);
	
	$exists = $database->num_rows("SELECT `id` FROM `tcgs` WHERE `id`='$tcgid'");
	
	if ( $exists != 1 ) { $error[] = "The TCG does not exist."; }
	if ( $trader === '' ) { $error[] = "The trader field can't be left blank."; }
	if ( $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL) ) { $error[] = "Invalid email address."; }
	if ( $type != 'outgoing' && $type != 'incoming' ) { $error[] = "Invalid trade type."; }
	if ( !isset($error) ) {
	
		if ( $grab == 1 ) {
			
			$giving = explode(',',$giving);
			array_walk($giving, 'trim_value');
			
			foreach ( $giving as $givingcard ) {
				
				unset($cardfound);
				
				$result = $database->query("SELECT * FROM `cards` WHERE `tcg`='$tcgid' AND `priority`!='3' ORDER BY `priority`");
				$x = 0;
				while ( $row = mysqli_fetch_array($result) ) {
					
					if ( !isset($cardfound) || $cardfound != true ) {
						
						$cards = explode(',',$row['cards']);
						array_walk($cards, 'trim_value');
						
						$i = 0;
						foreach ( $cards as $card ) {
							if ( preg_match('/^'.$givingcard.'$/i', $card) && !isset($cardfound) ) { 
								if ( $removedcards[$x] == '' ) { $removedcards[$x] = $card; } else { $removedcards[$x] = ''.$removedcards[$x].', '.$card.''; }
								$removedcats[$x] = $row['category'];
								$cards[$i] = '';
								$cardfound = true;
							}
							$i++;
						}
						
						$cards = array_filter($cards);
						sort($cards);
						$cards = implode(', ',$cards);
						
						$categid = $row['id'];
						$resultt = $database->query("UPDATE `cards` SET `cards`='$cards' WHERE `id`='$categid'");
						if ( !$resultt ) { $error[] = "Error updating cards from category ".$row['category'].""; }
					
					}
					
					$x++;
					
				}
			
			}
			
			if ( isset($removedcards) ) {
			$checkremoved = implode(',',$removedcards);
			$checkremoved = explode(',',$checkremoved);
			array_walk($checkremoved, 'trim_value');
			}
			
			foreach ( $giving as $givingcard ) {
				
				if ( !isset($checkremoved) || $checkremoved === '' || !in_array($givingcard,$checkremoved) ) { $error[] = "Could not grab $givingcard."; }
				
			}
			
			if ( isset($removedcards) ) { $giving = implode('; ',$removedcards); $givingcats = implode(', ',$removedcats); }
			else { $giving = ''; $givingcats = ''; }
				
		} // end if grab
		
		$today = date("Y-m-d");
		$result = $database->query("INSERT INTO `trades` (`tcg`,`trader`,`email`,`giving`,`givingcat`,`receiving`,`receivingcat`,`type`,`date`) VALUE ('$tcgid','$trader','$email','$giving','$givingcats','$receiving','','$type','$today')");
		if ( !result ) { $error[] = "Could not add the new trade. ".mysqli_error().""; }
		else { $success[]= "The trade has been added."; }
		
	}

}

?>

<div class="content col-12 col-sm-12 col-lg-12">

	<h1>New Pending Trade</h1>
	<p>Separate card names with commas. Select the <em>grab from categories</em> option to automatically remove trading cards from your collection. Low priority categories are searched first.</p>
	<?php if ( isset($_GET['id']) ) { ?><p>&laquo; <a href="trades.php?id=<?php echo intval($_GET['id']); ?>">Return to Trades</a></p>
	<?php } else { ?><p>&laquo; <a href="trades.php">Return to Trades</a></p><?php } ?>

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
	
	<form role="form" action="" method="post">
		<div class="form-group">
			<label for="exampleInputEmail1">TCG</label>
			<select name="tcg" id="tcg" class="form-control">
				<?php $result = $database->query("SELECT * FROM `tcgs` ORDER BY `name`");
				while ( $row = mysqli_fetch_assoc($result) ) { ?>
				<option value="<?php echo $row['id']; ?>" <?php if ( isset($_GET['id']) && intval($_GET['id']) == $row['id'] ) { echo 'selected="selected"'; } ?>><?php echo $row['name']; ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="form-group">
			<label for="trader">Trader's Name</label>
			<input type="text" class="form-control" id="trader" name="trader">
		</div>
		<div class="form-group">
			<label for="email">Email Address</label>
			<input type="email" class="form-control" id="email" name="email" placeholder="email@example.com">
		</div>
		<div class="form-group">
			<label for="giving">Trading Cards</label>
			<input type="text" class="form-control" id="giving" name="giving" placeholder="card01, card02, card03">
		</div>
		<div class="form-group">
			<label for="receiving">Receiving Cards</label>
			<input type="text" class="form-control" id="receiving" name="receiving" placeholder="card01, card02, card03">
		</div>
		<div class="form-group">
			<label for="type">Type</label>
			<select class="form-control" name="type" id="type">
				<option value="outgoing" selected>Outgoing</option>
				<option value="incoming">Incoming</option>
			</select>
		</div>
		<div class="checkbox">
			<label>
				<input name="grab" type="checkbox" id="grab" value="1" checked> Grab from categories <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="eTCG will search for and remove the Trading Cards from your card categories (in order of Priority). NOTE: If eTCG can't find a card that you listed under Trading Cards, it will be removed from the trade."></i>
			</label>
		</div>
		<button type="submit" class="btn btn-primary btn-block" name="newtrade" id="newtrade">Submit</button>
	</form>

</div><!--/span-->

<?php include 'footer.php'; ?>