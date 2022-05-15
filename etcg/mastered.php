<?php include 'header.php'; if ( !isset($_GET['id']) || $_GET['id'] == '' ) { ?>

<div class="content col-12 col-sm-12 col-lg-12">
	<h1>Mastered Cards</h1>
</div>

<?php } else if ( $_GET['id'] != '' ) {
	
	function trim_value(&$value) { $value = trim($value); }
	
	$id = intval($_GET['id']);
	$database = new Database;
	$upload = new Upload;

	$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
	$altname = strtolower(str_replace(' ','',$tcginfo['name']));
	
	if ( isset($_POST['update']) ) {
	
		$sanitize = new Sanitize;
		
		$catid = intval($_POST['id']);
		$cards = $sanitize->for_db($_POST['cards']);
		$worth = intval($_POST['worth']);
		$count = intval($_POST['count']);
		$break = intval($_POST['break']);
		$filler = $sanitize->for_db($_POST['filler']);
		$pending = $sanitize->for_db($_POST['pending']);
		$puzzle = intval($_POST['puzzle']);
		$mastereddate = $sanitize->for_db($_POST['mastereddate']);
		$auto = intval($_POST['auto']);
		$autourl = $sanitize->for_db($_POST['autourl']);
		$format = $sanitize->for_db($_POST['format']);
		if ( $format == '' ) { $format = 'default'; }
		if ( $autourl == '' ) { $autourl = 'default'; }
		if ( $autourl != 'default' && substr($autourl, -1) != '/' ) { $autourl = "$autourl/"; }
		
		if ( $worth === '' ) { $error[] = 'Card worth must be defined.'; }
		else if ( $count === '' ) { $error[] = 'Card count must be defined.'; }
		else if ( $break === '' ) { $error[] = 'Break field must be defined. Set it to 0 if you don\'t want line breaks.'; }
		else if ( $filler === '' ) { $error[] = 'Please define a filler card.'; }
		else if ( $pending === '' ) { $error[] = 'Please define a pending card.'; }
		else if ( $puzzle != 1 && $puzzle != 0 ) { $error[] = 'Invalid Puzzle value.'; }
		else if ( $auto != 1 && $auto != 0 ) { $error[] = 'Invalid Auto value.'; }
		else if ( $autourl != 'default' && !filter_var($autourl, FILTER_VALIDATE_URL) ) { $error[] = 'Invalid auto upload URL.'; }
		else {
			
			$deckinfo = $database->get_assoc("SELECT * FROM `collecting` WHERE `id`='$catid'");
			$deck = $deckinfo['deck'];
			
			$cards = explode(',',$cards);
			
			function adddeck(&$value,$key,$deck) {
				$value = trim($value);
				$value = ''.$deck.''.$value.'';
			}
			array_walk($cards,'adddeck',$deck);
			
			if ( $tcginfo['autoupload'] == 1 && $auto == 1 ) {
				foreach ( $cards as $card ) {
					if ( !isset($error) ) {
						if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
						else { $defaultauto = $autourl; }
						
						if ( $format == 'default' ) { $formatval = $tcginfo['format']; }
						else { $formatval = $format; }

						$upsuccess = $upload->card($tcginfo,$deckinfo,'collecting',$card);
									
						if ( $upsuccess === false ) { $error[] = "Failed to upload $card.$formatval from $defaultauto"; }
						else if ( $upsuccess === true ) { $success2 = " and all missing cards have been uploaded"; }
					}
				}
			}
			
			$cards = implode(', ',$cards);
			
			$result = $database->query("UPDATE `collecting` SET `cards`='$cards', `worth`='$worth', `count`='$count', `break`='$break', `filler`='$filler', `pending`='$pending', `puzzle`='$puzzle', `auto`='$auto', `uploadurl`='$autourl', `format`='$format', `mastereddate`='$mastereddate' WHERE `id`='$catid' LIMIT 1");
			if ( !$result ) { $error[] = "Failed to update the collecting deck. ".mysqli_error().""; }
			else { $success[] = "The deck has been updated$success2!"; }
			
		}
	
	}
	
	if ( isset($_POST['newcat']) ) {
	
		$sanitize = new Sanitize;
		
		$deck = $sanitize->for_db($_POST['deck']);
		$cards = $sanitize->for_db($_POST['cards']);
		$findcards = intval($_POST['findcards']);
		$worth = intval($_POST['worth']);
		$count = intval($_POST['count']);
		$break = intval($_POST['break']);
		$filler = $sanitize->for_db($_POST['filler']);
		$pending = $sanitize->for_db($_POST['pending']);
		$puzzle = intval($_POST['puzzle']);
		$auto = intval($_POST['auto']);
		$autourl = $sanitize->for_db($_POST['autourl']);
		$format = $sanitize->for_db($_POST['format']);
		if ( $format == '' ) { $format = 'default'; }
		if ( $autourl == '' ) { $autourl = 'default'; }
		if ( $autourl != 'default' && substr($autourl, -1) != '/' ) { $autourl = "$autourl/"; }
		if ( $cards == 'cards (01, 02, 03)' ) { $cards = ''; }
		
		if ( $worth === '' || $deck == 'worth' ) { $error[] = 'Card worth must be defined.'; }
		else if ( $deck === '' || $deck == 'deck' ) { $error[] = 'Deck name must be defined.'; }
		else if ( $count === '' || $deck == 'count' ) { $error[] = 'Card count must be defined.'; }
		else if ( $break === '' || $deck == 'break' ) { $error[] = 'Break field must be defined. Set it to 0 if you don\'t want line breaks.'; }
		else if ( $filler === '' ) { $error[] = 'Please define a filler card.'; }
		else if ( $pending === '' ) { $error[] = 'Please define a pending card.'; }
		else if ( $puzzle != 1 && $puzzle != 0 ) { $error[] = 'Invalid Puzzle value.'; }
		else if ( $auto != 1 && $auto != 0 ) { $error[] = 'Invalid Auto value.'; }
		else if ( $findcards != 1 && $findcards != 0 ) { $error[] = 'Invalid FindCards value.'; }
		else if ( $autourl != 'default' && !filter_var($autourl, FILTER_VALIDATE_URL) ) { $error[] = 'Invalid auto upload URL.'; }
		else {
			
			if ( $findcards == 0 ) {
				
				$cards = explode(',',$cards);
				
				function adddeck(&$value,$key,$deck) {
					$value = trim($value);
					$value = ''.$deck.''.$value.'';
				}
				array_walk($cards,'adddeck',$deck);
				
				if ( $tcginfo['autoupload'] == 1 && $auto == 1 ) {
					foreach ( $cards as $card ) {
						if ( !isset($error) ) {
								
							if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
							else { $defaultauto = $autourl; }
							
							if ( $format == 'default' ) { $formatval = $tcginfo['format']; }
							else { $formatval = $format; }
							
							$upsuccess = $upload->card($tcginfo,'','collecting',$card,$defaultauto,$formatval);
									
							if ( $upsuccess === false ) { $error[] = "Failed to upload $card.$formatval from $defaultauto"; }
							else if ( $upsuccess === true ) { $success2 = " and all missing cards have been uploaded"; }
						
						}
					}
				}
				
				$cards = implode(', ',$cards);
			
			}
			
			else {
				
				$result = $database->query("SELECT * FROM `cards` WHERE `tcg`='$id' ORDER BY `priority` DESC");
				
				while ( $row = mysqli_fetch_array($result) ) {
					
					if ( !isset($error) ) {
						
						$cards = explode(',',$row['cards']);
						
						array_walk($cards, 'trim_value');
						
						$i = 0;
						foreach ( $cards as $card ) {
							if ( preg_match("/^(".$deck.")([0-9]{2})$/i", $card) && strpos($newcards,$card) === false ) { 
								if ( $newcards == '' ) { $newcards = $card; } else { $newcards .= ', '.$card.''; } 
								$cards[$i] = '';
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
					
				}
				
				$cards = $newcards;
				
			}
			
			if ( !isset($error) ) {
				$today = date("Y-m-d");
				$result = $database->query("INSERT INTO `collecting` (`tcg`,`deck`,`cards`,`worth`,`count`,`break`,`filler`,`pending`,`puzzle`,`auto`,`uploadurl`,`format`,`mastered`,`mastereddate`) VALUE ('$id','$deck','$cards','$worth','$count','$break','$filler','$pending','$puzzle','$auto','$autourl','$format','1','$today')");
				if ( !$result ) { $error[] = "Failed to add the deck."; }
				else { $success[] = "The new mastered deck has been added$success2."; }
			}
			
		}
		
	}
	
	if ( $_GET['action'] == 'delete' && isset($_GET['cat']) ) {
	
		$catid = intval($_GET['cat']);	
		$exists = $database->num_rows("SELECT * FROM `collecting` WHERE `id`='$catid'");
		
		if ( $exists === 1 ) {
		
			$result  = $database->query("DELETE FROM `collecting` WHERE `id` = '$catid' LIMIT 1");
			if ( !$result ) { $error[] = "There was an error while attempting to remove the collecting set. ".mysqli_error().""; }
			else { $success[] = "The mastered deck and containing cards have been removed."; }
		
		}
		
		else { $error[] = "The set no longer exists."; }
	
	}
	
	if ( isset($_POST['upload']) ) {
			
		
		if ( $_FILES["file"]["type"] != "image/gif" && $_FILES["file"]["type"] != "image/jpeg" && $_FILES["file"]["type"] != "image/pjpeg" && $_FILES["file"]["type"] != "image/png" ) { $error[] = "Invalid file type."; }
		else {
			
			if ( move_uploaded_file($_FILES["file"]["tmp_name"],"".$tcginfo['cardspath']."".$_FILES["file"]["name"]."") ) {
				
				$filename = $_FILES["file"]["name"];
				$result = $database->query("UPDATE `collecting` SET `badge` = '$filename' WHERE `id`='".$_GET['deck']."'");
				if ( !$result ) { $error[] = "Failed to update badge. ".mysqli_error().""; }
				else { $success[] = "The new badge has been added."; }
				
			}
			
			else { $error[] = "Could not upload the file."; }
		
		}
		
	}
	
	?>
    <div class="content col-12 col-sm-12 col-lg-12">
	
    <h1>Mastered <small><?php echo $tcginfo['name']; ?></small></h1>
	<p class="clearfix">
		&raquo; <a href="cards.php?id=<?php echo $id; ?>">View Categories</a> &nbsp; 
		&raquo; <a href="collecting.php?id=<?php echo $id; ?>">View Collecting</a>
		<button class="btn btn-primary btn-xs pull-right" data-toggle="modal" data-target="#new-mastered-modal"><i class="fa fa-plus"></i> &nbsp; New Mastered</button>
	</p>
    
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
	
	<div class="modal fade" id="new-mastered-modal" tabindex="-1" role="dialog" aria-labelledby="new-mastered-label" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h2 class="modal-title" id="new-mastered-label">New Mastered Deck</h2>
				</div>
				<div class="modal-body">
					<form action="mastered.php?id=<?php echo $id; ?>" method="post" role="form">
					<div class="form-group">
						<label for="deck">Deck Name</label>
						<input name="deck" id="deck" type="text" class="form-control">
					</div>
					<div class="form-group">
						<label for="worth">Card Worth</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The worth of each card in the deck."></i>
						<input name="worth" id="worth" type="number" class="form-control" placeholder="ie. 1">
					</div>
					<div class="form-group">
						<label for="count">Card Count</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The total number of cards in the deck when completed."></i>
						<input name="count" id="count" type="number" class="form-control" placeholder="ie. 15">
					</div>
					<div class="form-group">
						<label for="break">Break Points</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Defines where to insert a line break (ie. input 5 to insert a line break after every 5 cards). Set the value to 0 if you don't want line breaks."></i>
						<input name="break" id="break" type="number" class="form-control" placeholder="ie. 5">
					</div>
					<div class="form-group">
						<label for="filler">Filler Card Image</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Upload filler card images to your cards folder. Only type the file name into the field - no file format."></i>
						<input name="filler" id="filler" type="text" class="form-control" placeholder="Filler card image name" value="filler">
					</div>
					<div class="form-group">
						<label for="pending">Pending Card Image</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Upload pending card images to your cards folder. Only type the file name into the field - no file format."></i>
						<input name="pending" id="pending" type="text" class="form-control" placeholder="Pending card image name" value="pending">
					</div>
					<div class="form-group">
						<label for="format">Image Format</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Leave as 'default' to use the Format defined in the TCG Settings."></i>
						<input name="format" id="format" type="text" class="form-control" placeholder="ie. '.png'" value="default">
					</div>
					<div class="form-group">
						<label for="cards">List Cards</label> OR <label for="findcards">Grab From Categories</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="If you check the checkbox, EasyTCG will search your categories and move any matching cards into the new mastered deck. Cards listed in the input field to the left will be IGNORED."></i>
						<div class="input-group">
							<input id="cards" name="cards" type="text" class="form-control" placeholder="01, 02, 03">
							<span class="input-group-addon">
								<input name="findcards" type="checkbox" value="1" id="findcards" data-toggle="tooltip" data-placement="top" title="Grab From Categories">
							</span>
						</div>
					</div>
					<div class="form-group">
						<label for="autourl">Auto-Upload URL</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Leave as 'default' to use the Auto-Upload URL defined in the TCG Settings."></i>
						<div class="input-group">
							<input id="autourl" name="autourl" type="text" class="form-control" value="default">
							<span class="input-group-addon">
								<input name="auto" type="checkbox" id="auto" value="1" <?php if ( $row['auto'] == 1 ) { echo 'checked="checked"'; } ?> data-toggle="tooltip" data-placement="top" title="Enable Auto-Upload">
							</span>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button name="newcat" id="newcat" type="submit" class="btn btn-primary">Create Mastered Deck</button>
					</form>
				</div>
			</div>
		</div>
	</div>
    
    <?php if ( !isset($_GET['deck']) || $_GET['deck'] === '' ) { ?>
    
    	<?php  
			
			$result = $database->query("SELECT * FROM `collecting` WHERE `tcg`='$id' AND `mastered` = '1' ORDER BY `mastereddate`");
			while ( $row = mysqli_fetch_assoc($result) ) {
				echo '<a href="mastered.php?id='.$id.'&deck='.$row['id'].'"><img src="'.$tcginfo['cardsurl'].''.$row['badge'].'" alt="'.$row['deck'].'" title="Mastered '.$row['mastereddate'].'" /></a> ';
			}
		
		?>
    
    <?php } else if ( isset($_GET['deck']) && $_GET['deck'] !== '' ) { 
	$deckid = intval($_GET['deck']); 
	$deckinfo = $database->get_assoc("SELECT * FROM `collecting` WHERE `id`='$deckid' LIMIT 1");
	
	$cards = explode(',',$deckinfo['cards']); 
		
	array_walk($cards, 'trim_value');
	
	$count = count($cards); 
	?>
		<p>&laquo; <a href="mastered.php?id=<?php echo $id; ?>">Back to Mastered Decks</a></p>
    	
		<div class="panel panel-default">
			<div class="panel-heading">
				<?php echo $deckinfo['deck']; ?>
			</div>
			<div class="panel-body">

				<p class="text-center">
					<?php
						for ( $i = 1; $i <= $deckinfo['count']; $i++ ) {
							
							$number = $i;
							if ( $number < 10 ) { $number = "0$number"; }
							$card = "".$deckinfo['deck']."$number";
							
							if ( $deckinfo['format'] !== 'default' ) { $format = $deckinfo['format']; } else { $format = $tcginfo['format']; }
							
							$pending = $database->num_rows("SELECT * FROM `trades` WHERE `tcg`='$id' AND `receiving` RLIKE '(^| )$card(,|;|$)'");
							
							if ( in_array($card, $cards) ) echo '<img src="'.$tcginfo['cardsurl'].''.$card.'.'.$format.'" alt="" />';
							else if ( $pending > 0 ) { echo '<img src="'.$tcginfo['cardsurl'].''.$deckinfo['pending'].'.'.$format.'" alt="" />'; }
							else { echo '<img src="'.$tcginfo['cardsurl'].''.$deckinfo['filler'].'.'.$format.'" alt="" />'; }
							
							if ( $deckinfo['puzzle'] == 0 ) { echo ' '; }
							if ( $deckinfo['break'] !== '0' && $i % $deckinfo['break'] == 0 ) { echo '<br />'; }
							
						}
					?>
				</p>
				
				<hr>
				
				<div class="row">
					<div class="col-md-6">
						<img src="<?php echo $tcginfo['cardsurl']; echo $deckinfo['badge']; ?>" alt="" class="center-block img-thumbnail" />
						<p class="text-center"><?php echo $deckinfo['badge']; ?></p>
					</div>
					<div class="col-md-6">
						<form enctype="multipart/form-data" action="" method="post" role="form">
							<div class="form-group">
								<label for="file">Master Badge</label>
								<input id="file" type="file" name="file" class="form-control">
							</div>
							<button name="upload" type="submit" id="upload" class="btn btn-primary btn-sm">Change Badge</button>
						</form>
					</div>
				</div>
				
				<hr>
				
				<form action="" method="post" role="form">
					<input name="id" type="hidden" value="<?php echo $deckinfo['id']; ?>">
					<div class="form-group">
						<label for="cards" class="sr-only">Cards</label>
						<input name="cards" id="cards" type="text" class="form-control" placeholder="01, 02, 03" value="<?php echo str_replace($deckinfo['deck'],'',$deckinfo['cards']); ?>">
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-xs-3">
								<label for="worth">Worth</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The worth of each card in the deck."></i>
								<input name="worth" id="worth" type="number" class="form-control" value="<?php echo $deckinfo['worth']; ?>">
							</div>
							<div class="col-xs-3">
								<label for="count">Count</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="The total number of cards in the deck when completed."></i>
								<input name="count" id="count" type="number" class="form-control" value="<?php echo $deckinfo['count']; ?>">
							</div>
							<div class="col-xs-3">
								<label for="break">Break</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Defines where to insert a line break (ie. input 5 to insert a line break after every 5 cards). Set the value to 0 if you don't want line breaks."></i>
								<input name="break" id="break" type="number" class="form-control" value="<?php echo $deckinfo['break']; ?>">
							</div>
							<div class="col-xs-3">
								<label for="mastereddate">Mastered</label>
								<input name="mastereddate" id="mastereddate" type="date" class="form-control" value="<?php echo $deckinfo['mastereddate']; ?>">
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-xs-3">
								<label for="filler">Filler</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Upload filler card images to your cards folder. Only type the file name into the field - no file format."></i>
								<input name="filler" id="filler" type="text" class="form-control" value="<?php echo $deckinfo['filler']; ?>">
							</div>
							<div class="col-xs-3">
								<label for="pending">Pending</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Upload pending card images to your cards folder. Only type the file name into the field - no file format."></i>
								<input name="pending" id="pending" type="text" class="form-control" value="<?php echo $deckinfo['pending']; ?>">
							</div>
							<div class="col-xs-3">
								<label for="format">Format</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Leave as 'default' to use the Format defined in the TCG Settings."></i>
								<input name="format" id="format" type="text" class="form-control" value="<?php echo $deckinfo['format']; ?>">
							</div>
							<div class="col-xs-3">
								<label for="puzzle">Puzzle</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Removes spaces from between card images."></i>
								<br>
								<input name="puzzle" type="checkbox" id="puzzle" value="1" <?php if ( $deckinfo['puzzle'] == 1 ) { echo 'checked'; } ?>>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="autourl">Auto-Upload URL</label> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="Leave as 'default' to use the Auto-Upload URL defined in the TCG Settings."></i>
						<div class="input-group">
							<input id="autourl" name="autourl" type="text" class="form-control" value="<?php echo $deckinfo['uploadurl']; ?>">
							<span class="input-group-addon">
								<input name="auto" type="checkbox" id="auto" value="1" <?php if ( $deckinfo['auto'] == 1 ) { echo 'checked="checked"'; } ?> data-toggle="tooltip" data-placement="top" title="Enable Auto-Upload">
							</span>
						</div>
					</div>
			</div>
			<div class="panel-footer clearfix">
				<div class="btn-group pull-right">
					<button name="update" id="update" type="submit" class="btn btn-sm btn-primary">Update Deck</button>
					<a class="btn btn-danger btn-sm" href="mastered.php?id=<?php echo $id; ?>&action=delete&cat=<?php echo $deckinfo['id']; ?>" onclick="go=confirm('Are you sure that you want to permanently delete this deck? The contents will be lost completely.'); return go;" data-toggle="tooltip" data-placement="top" title="Delete This Deck"><i class="fa fa-times-circle"></i></a>
				</div>
				</form>
			</div>
		</div>

<?php } ?>

 </div>

<?php } include 'footer.php'; ?>