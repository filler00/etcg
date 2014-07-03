<?php include 'header.php'; if ( !isset($_GET['id']) || $_GET['id'] == '' ) { ?>
           
<h1>Collecting Cards</h1>

<?php } else if ( $_GET['id'] != '' ) {
	
	function trim_value(&$value) { $value = trim($value); }
	
	$id = intval($_GET['id']);
	$database = new Database;

	$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
	$altname = strtolower(str_replace(' ','',$tcginfo['name']));
	
	if ( isset($_POST['update']) ) {
	
		$sanitize = new Sanitize;
		
		$catid = intval($_POST['id']);
		$sort = intval($_POST['sort']);
		$cards = $sanitize->for_db($_POST['cards']);
		$worth = intval($_POST['worth']);
		$count = intval($_POST['count']);
		$break = intval($_POST['break']);
		$filler = $sanitize->for_db($_POST['filler']);
		$pending = $sanitize->for_db($_POST['pending']);
		$puzzle = intval($_POST['puzzle']);
		$auto = intval($_POST['auto']);
		$autourl = $sanitize->for_db($_POST['autourl']);
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
			
			$deck = $database->get_assoc("SELECT `deck` FROM `collecting` WHERE `id`='$catid'");
			$deck = $deck['deck'];
			
			if ( $cards !== '' ) {
				
				$cards = explode(',',$cards);
				
				function adddeck(&$value,$key,$deck) {
					$value = trim($value);
					$value = ''.$deck.''.$value.'';
				}
				array_walk($cards,'adddeck',$deck);
				
				if ( $tcginfo['autoupload'] == 1 && $auto == 1 ) {
					foreach ( $cards as $card ) {
						if ( !isset($error) ) {
							$filename = ''.$tcginfo['cardspath'].''.$card.'.'.$tcginfo['format'].'';
						
							if ( !file_exists($filename) ) {
								
								if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
								else { $defaultauto = $autourl; }
								$imgurl = ''.$defaultauto.''.$card.'.'.$tcginfo['format'].'';
								
								if ( !$img = file_get_contents($imgurl) ) { $error[] = "Couldn't find the file named $card.$format at $defaultauto"; }
								else {
									if ( !file_put_contents($filename,$img) ) { $error[] = "Failed to upload $filename"; }	
									else { $success2 = " and all missing cards have been uploaded"; }
								}
								
							}
						
						}
					}
				}
				
				$cards = implode(', ',$cards);
				
			}
			
			$result = $database->query("UPDATE `collecting` SET `sort`='$sort',`cards`='$cards', `worth`='$worth', `count`='$count', `break`='$break', `filler`='$filler', `pending`='$pending', `puzzle`='$puzzle', `auto`='$auto', `uploadurl`='$autourl' WHERE `id`='$catid' LIMIT 1");
			if ( !$result ) { $error[] = "Failed to update the collecting deck. ".mysql_error().""; }
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
			
			if ( $findcards == 0 && $cards !== '' ) {
				
				$cards = explode(',',$cards);
				
				function adddeck(&$value,$key,$deck) {
					$value = trim($value);
					$value = ''.$deck.''.$value.'';
				}
				array_walk($cards,'adddeck',$deck);
				
				if ( $tcginfo['autoupload'] == 1 && $auto == 1 ) {
					foreach ( $cards as $card ) {
						if ( !isset($error) ) {
							$filename = ''.$tcginfo['cardspath'].''.$card.'.'.$tcginfo['format'].'';
						
							if ( !file_exists($filename) ) {
								
								if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
								else { $defaultauto = $autourl; }
								$imgurl = ''.$defaultauto.''.$card.'.'.$tcginfo['format'].'';
								
								if ( !$img = file_get_contents($imgurl) ) { $error[] = "Couldn't find the file named $card.$format at $defaultauto"; }
								else {
									if ( !file_put_contents($filename,$img) ) { $error[] = "Failed to upload $filename"; }	
									else { $success2 = " and all missing cards have been uploaded"; }
								}
								
							}
						
						}
					}
				}
				
				$cards = implode(', ',$cards);
			
			}
			
			else {
				
				$result = $database->query("SELECT * FROM `cards` WHERE `tcg`='$id' ORDER BY `priority` DESC");
				
				while ( $row = mysql_fetch_array($result) ) {
					
					if ( !isset($error) ) {
						
						$cards = explode(',',$row['cards']);
						
						array_walk($cards, 'trim_value');
						
						$i = 0;
						foreach ( $cards as $card ) {
							if ( preg_match('/^'.$deck.'[0-9]{1,5}$/i', $card) && strpos($newcards,$card) === false ) { 
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
				$result = $database->query("INSERT INTO `collecting` (`tcg`,`deck`,`cards`,`worth`,`count`,`break`,`filler`,`pending`,`puzzle`,`auto`,`uploadurl`) VALUE ('$id','$deck','$cards','$worth','$count','$break','$filler','$pending','$puzzle','$auto','$autourl')");
				if ( !$result ) { $error[] = "Failed to add the collecting deck."; }
				else { $success[] = "The new collecting deck has been added$success2."; }
			}
			
		}
		
	}
	
	if ( $_GET['action'] == 'delete' && isset($_GET['cat']) ) {
	
		$catid = intval($_GET['cat']);	
		$exists = $database->num_rows("SELECT * FROM `collecting` WHERE `id`='$catid'");
		
		if ( $exists === 1 ) {
		
			$result  = $database->query("DELETE FROM `collecting` WHERE `id` = '$catid' LIMIT 1");
			if ( !$result ) { $error[] = "There was an error while attempting to remove the collecting set. ".mysql_error().""; }
			else { $success[] = "The collecting deck and containing cards have been removed."; }
		
		}
		
		else { $error[] = "The set no longer exists."; }
	
	}
	
	if ( isset($_POST['master']) ) {
		
		$catid = intval($_POST['id']);
		$today = date("Y-m-d");
		$result = $database->query("UPDATE `collecting` SET `mastered`='1', `mastereddate`='$today' WHERE `id`='$catid'");
		if ( !$result ) { $error[] = "Failed to move deck to mastered category."; }
		else { $success[] = "The deck has been moved to the mastered decks pile."; }
	
	}
	
	?>
    
    <h1>Collecting: <?php echo $tcginfo['name']; ?></h1>
	<p>Selecting the <strong>auto</strong> option will allow the auto upload system to automatically upload cards that are added to that category through the Easy Updater. The auto upload feature must be enabled in the TCG's settings as well for this to work. Deselect the auto checkbox to disable auto uploading for that category. Leave the <strong>auto url</strong> as <em>default</em> unless the Auto Upload URL for that category is different from the default defined in the TCG's settings.</p>
	<p>Upload filler and pending cards to your cards folder. Only type the file name into the filler/pending fields (ex. if the file is  filler.gif, just type 'filler')</p>
    <p>&raquo; <a href="cards.php?id=<?php echo $id; ?>">View Categories</a> <br />&raquo; <a href="mastered.php?id=<?php echo $id; ?>">View Mastered</a></p>
    
    <?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>
	<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><div class="success"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><?php } } ?>
    
    <br />
    <form action="collecting.php?id=<?php echo $id; ?>" method="post">
	<p align="center"><strong>New Collecting</strong>:</p>
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
        <td colspan="3">auto url: <input name="autourl" type="text" value="default" id="autourl" /></td>
        <td colspan="3">auto: <input name="auto" type="checkbox" value="1" id="auto" /></td>
      </tr>
      <tr>
        <td colspan="6" align="right"><input name="newcat" type="submit" value="Submit" id="newcat"></td>
      </tr>
    </table>
    </form>
    
    <?php
	$result = $database->query("SELECT * FROM `collecting` WHERE `tcg` = '$id' AND `mastered` = '0' ORDER BY `sort`, `worth`, `deck`");
	while ( $row = mysql_fetch_assoc($result) ) { 
	
		$cards = explode(',',$row['cards']); 
			
		array_walk($cards, 'trim_value');
		
		if ( $row['cards'] == '' ) { $count = 0; } else { $count = count($cards); }
		?>
    	
        <br />
		<h2><?php echo $row['deck']; ?> (<?php echo $count; ?>/<?php echo $row['count']; ?>) <a href="collecting.php?id=<?php echo $id; ?>&action=delete&cat=<?php echo $row['id']; ?>" onclick="go=confirm('Are you sure that you want to permanently delete this collecting deck? The contents will be lost completely.'); return go;"><img src="images/delete.gif" alt="delete" /></a></h2>
        <p align="center">
        	<?php
				for ( $i = 1; $i <= $row['count']; $i++ ) {
					
					$number = $i;
					if ( $number < 10 ) { $number = "0$number"; }
					$card = "".$row['deck']."$number";
					
					$pending = $database->num_rows("SELECT * FROM `trades` WHERE `tcg`='$id' AND `receiving` LIKE '%$card%'");
					
					if ( in_array($card, $cards) ) echo '<img src="'.$tcginfo['cardsurl'].''.$card.'.'.$tcginfo['format'].'" alt="" />';
					else if ( $pending > 0 ) { echo '<img src="'.$tcginfo['cardsurl'].''.$row['pending'].'.'.$tcginfo['format'].'" alt="" />'; }
					else { echo '<img src="'.$tcginfo['cardsurl'].''.$row['filler'].'.'.$tcginfo['format'].'" alt="" />'; }
					
					if ( $row['puzzle'] == 0 ) { echo ' '; }
					if ( $row['break'] !== '0' && $i % $row['break'] == 0 ) { echo '<br />'; }
					
				}
			?>
        </p>
        
            <form action="collecting.php?id=<?php echo $id; ?>" method="post">
            <input name="id" type="hidden" value="<?php echo $row['id']; ?>" />
            	<table width="450" align="center">
                	<tr>
                        <td colspan="3" align="center">
                            <input name="cards" type="text" id="cards" value="<?php echo str_replace($row['deck'],'',$row['cards']); ?>" size="70">
                        </td>
                 	</tr><tr>
                    	<td>worth: <input name="worth" type="text" id="worth" value="<?php echo $row['worth']; ?>" size="2" /></td>
                        <td>count: <input name="count" type="text" id="count" value="<?php echo $row['count']; ?>" size="2" /></td>
                        <td>break: <input name="break" type="text" id="break" value="<?php echo $row['break']; ?>" size="2" /></td>
                    </tr><tr>
                		<td>filler: <input name="filler" type="text" id="filler" value="<?php echo $row['filler']; ?>" size="8" /></td>
                        <td>pending: <input name="pending" type="text" id="pending" value="<?php echo $row['pending']; ?>" size="8" /></td>
                        <td>puzzle: <input name="puzzle" type="checkbox" id="puzzle" value="1" <?php if ( $row['puzzle'] == 1 ) { echo 'checked="checked"'; } ?> /></td>
                    </tr><tr>
                    	<td colspan="3" align="right"><input name="autourl" type="text" id="autourl" value="<?php echo $row['uploadurl']; ?>" /> auto: <input name="auto" type="checkbox" id="auto" value="1" <?php if ( $row['auto'] == 1 ) { echo 'checked="checked"'; } ?> /></td>
                    </tr><tr>
                    	<td colspan="3" align="right">sort: <input name="sort" type="text" id="sort" value="<?php echo $row['sort']; ?>" size="2" /> <input name="update" type="submit" value="Update" id="update" /> <?php if ( $row['count'] == $count ) { echo '<input name="master" type="submit" value="Master" id="master" />'; } ?></td>
                    </tr>
                </table>
          </form>
        
        
<?php
	}
	
?>

<?php } include 'footer.php'; ?>