<?php include 'header.php'; if ( !isset($_GET['id']) || $_GET['id'] == '' ) { ?>
           
<h1>Collecting Cards</h1>

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
		else if ( $autourl != 'default' && !filter_var($autourl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = 'Invalid auto upload URL.'; }
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
		else if ( $autourl != 'default' && !filter_var($autourl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = 'Invalid auto upload URL.'; }
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
							if ( preg_match("/".$deck."[0-9]{1,5}/i", $card) && strpos($newcards,$card) === false ) { 
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
    
    <h1>Mastered: <?php echo $tcginfo['name']; ?></h1>
	<p>Selecting the <strong>auto</strong> option will allow the auto upload system to automatically upload cards that are added to that category through the Easy Updater. The auto upload feature must be enabled in the TCG's settings as well for this to work. Deselect the auto checkbox to disable auto uploading for that category. Leave the <strong>upload url</strong> as <em>default</em> unless the Auto Upload URL for that category is different from the default defined in the TCG's settings.</p>
	<p>Upload filler and pending cards to your cards folder. Only type the file name into the filler/pending fields (ex. if the file name is filler.gif, just type 'filler')</p>
    <p>&raquo; <a href="cards.php?id=<?php echo $id; ?>">View Categories</a> <br />&raquo; <a href="collecting.php?id=<?php echo $id; ?>">View Collecting</a></p>
    
    <?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>
	<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><div class="success"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><?php } } ?>
    
    <?php if ( !isset($_GET['deck']) || $_GET['deck'] === '' ) { ?>
    <br />
    <form action="mastered.php?id=<?php echo $id; ?>" method="post">
	<p align="center"><strong>New Mastered</strong>:</p>
    <table cellspacing="5" cellpadding="0" align="center">
      <tr>
        <td><input name="deck" type="text" id="deck" value="deck" onfocus="if (this.value=='deck') this.value='';" onblur="if (this.value=='') this.value='deck';"></td>
        <td><input name="worth" type="text" id="worth" value="worth" size="3" onfocus="if (this.value=='worth') this.value='';" onblur="if (this.value=='') this.value='worth';"></td>
        <td><input name="count" type="text" id="count" value="count" size="3" onfocus="if (this.value=='count') this.value='';" onblur="if (this.value=='') this.value='count';"></td>
        <td><input name="break" type="text" id="break" value="break" size="3" onfocus="if (this.value=='break') this.value='';" onblur="if (this.value=='') this.value='break';"></td>
        <td><input name="filler" type="text" id="filler" value="filler" size="8" onfocus="if (this.value=='filler') this.value='';" onblur="if (this.value=='') this.value='filler';"></td>
        <td><input name="pending" type="text" id="pending" value="pending" size="8" onfocus="if (this.value=='pending') this.value='';" onblur="if (this.value=='') this.value='pending';"></td>
      </tr>
      <tr>
        <td colspan="3"><input name="cards" type="text" id="cards" value="cards (01, 02, 03)" size="35" onfocus="if (this.value=='cards (01, 02, 03)') this.value='';" onblur="if (this.value=='') this.value='cards (01, 02, 03)';" /></td>
        <td><strong>OR</strong></td>
        <td colspan="2">grab from categories: <input name="findcards" type="checkbox" value="1" id="findcards"></td>
      </tr>
      <tr>
        <td colspan="2">format: <input name="format" type="text" value="default" id="format" /></td>
		<td colspan="3">auto url: <input name="autourl" type="text" value="default" id="autourl" /></td>
        <td colspan="1">auto: <input name="auto" type="checkbox" value="1" id="auto" /></td>
      </tr>
      <tr>
        <td colspan="6" align="right"><input name="newcat" type="submit" value="Submit" id="newcat"></td>
      </tr>
    </table>
    </form>
    <?php } ?>
    
    <?php if ( !isset($_GET['deck']) || $_GET['deck'] === '' ) { ?>
    
    	<?php  
			
			$result = $database->query("SELECT * FROM `collecting` WHERE `tcg`='$id' AND `mastered` = '1' ORDER BY `mastereddate`");
			while ( $row = mysqli_fetch_assoc($result) ) {
				echo '<a href="mastered.php?id='.$id.'&deck='.$row['id'].'"><img src="'.$tcginfo['cardsurl'].''.$row['badge'].'" alt="'.$row['deck'].'" title="Mastered '.$row['mastereddate'].'" /></a> ';
			}
		
		?>
    
    <?php } else if ( isset($_GET['deck']) && $_GET['deck'] !== '' ) { $deckid = intval($_GET['deck']); ?>
    
<?php
	$deckinfo = $database->get_assoc("SELECT * FROM `collecting` WHERE `id`='$deckid' LIMIT 1");
	
		$cards = explode(',',$deckinfo['cards']); 
			
		array_walk($cards, 'trim_value');
		
		$count = count($cards); 
		?>
    	
        <br />
		<h2><?php echo $deckinfo['deck']; ?> <a href="mastered.php?id=<?php echo $id; ?>&action=delete&cat=<?php echo $deckinfo['id']; ?>" onclick="go=confirm('Are you sure that you want to permanently delete this deck? The contents will be lost completely.'); return go;"><img src="images/delete.gif" alt="delete" /></a></h2>
        &laquo; <a href="mastered.php?id=<?php echo $id; ?>">Back to Mastered Decks</a><br />
        <p align="center">
			<?php
				for ( $i = 1; $i <= $deckinfo['count']; $i++ ) {
					
					$number = $i;
					if ( $number < 10 ) { $number = "0$number"; }
					$card = "".$deckinfo['deck']."$number";
					
					if ( $deckinfo['format'] !== 'default' ) { $format = $deckinfo['format']; } else { $format = $tcginfo['format']; }
					
					$pending = $database->num_rows("SELECT * FROM `trades` WHERE `tcg`='$id' AND `receiving` LIKE '%$card%'");
					
					if ( in_array($card, $cards) ) echo '<img src="'.$tcginfo['cardsurl'].''.$card.'.'.$format.'" alt="" />';
					else if ( $pending > 0 ) { echo '<img src="'.$tcginfo['cardsurl'].''.$deckinfo['pending'].'.'.$format.'" alt="" />'; }
					else { echo '<img src="'.$tcginfo['cardsurl'].''.$deckinfo['filler'].'.'.$format.'" alt="" />'; }
					
					if ( $deckinfo['puzzle'] == 0 ) { echo ' '; }
					if ( $deckinfo['break'] !== '0' && $i % $deckinfo['break'] == 0 ) { echo '<br />'; }
					
				}
			?>
        </p>
        

		    <form enctype="multipart/form-data" action="" method="post">
   		  <table align="center" cellspacing="5" cellpadding="5" class="style1">
            	<tr>
            		<td rowspan="3"><img src="<?php echo $tcginfo['cardsurl']; echo $deckinfo['badge']; ?>" alt="" /></td>
                	<td><strong>Master Badge</strong></td>
                </tr><tr>
                    <td><input id="file" type="file" name="file"></td>
                </tr><tr>
                	<td><input name="upload" type="submit" id="upload" value="Change Badge"></td>
            	</tr>
            </table>
            </form>

			<br /><br />
<form action="" method="post">
            <input name="id" type="hidden" value="<?php echo $deckinfo['id']; ?>" />
   	<table width="400" align="center" cellspacing="5" cellpadding="5">
       	<tr>
           	<td colspan="6" class="top">Deck Settings</td>
   	    </tr><tr>
            <td colspan="3" align="center">
                <input name="cards" type="text" id="cards" value="<?php echo str_replace($deckinfo['deck'],'',$deckinfo['cards']); ?>" size="60">
            </td>
       	  </tr><tr>
           	  <td>worth: <input name="worth" type="text" id="worth" value="<?php echo $deckinfo['worth']; ?>" size="2" /></td>
              <td>count: <input name="count" type="text" id="count" value="<?php echo $deckinfo['count']; ?>" size="2" /></td>
              <td>break: <input name="break" type="text" id="break" value="<?php echo $deckinfo['break']; ?>" size="2" /></td>
            </tr><tr>
           		<td>filler: <input name="filler" type="text" id="filler" value="<?php echo $deckinfo['filler']; ?>" size="8" /></td>
                <td>pending: <input name="pending" type="text" id="pending" value="<?php echo $deckinfo['pending']; ?>" size="8" /></td>
                <td>puzzle: <input name="puzzle" type="checkbox" id="puzzle" value="1" <?php if ( $deckinfo['puzzle'] == 1 ) { echo 'checked="checked"'; } ?> /></td>
              </tr><tr>
               	  <td colspan="2">auto url: <input name="" type="text" value="<?php echo $deckinfo['uploadurl']; ?>" /></td>
                <td>auto: <input name="auto" type="checkbox" id="auto" value="1" <?php if ( $deckinfo['auto'] == 1 ) { echo 'checked="checked"'; } ?> /></td>
              </tr><tr>
                  <td colspan="3" align="right">format: <input name="format" type="text" id="format" value="<?php echo $deckinfo['format']; ?>" size="8" /> mastered: <input name="mastereddate" type="text" id="mastereddate" value="<?php echo $deckinfo['mastereddate']; ?>" size="10"> <input name="update" type="submit" value="Update" id="update" /></td>
              </tr>
    </table>
  </form>

<?php } } include 'footer.php'; ?>