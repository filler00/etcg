<?php include 'header.php'; 

if ( !isset($_GET['id']) || $_GET['id'] == '' ) {
           
	echo '<h1>Easy Updater</h1> <br />';
	
	$database = new Database;
	$result = $database->query("SELECT * FROM `tcgs` ORDER BY `name`");
	$count = mysqli_num_rows($result);
	
	if ( $count > 0 ) {
		while ( $row = mysqli_fetch_assoc($result) ) {
			echo '&raquo; <a href="update.php?id='.$row['id'].'">'.$row['name'].'</a><br />';
		}
	}
	else {
		echo 'You haven\'t set up any TCGs yet.';
	}


} else if ( $_GET['id'] != '' ) {

	$database = new Database;
	$upload = new Upload;
	
	function trim_value(&$value) { $value = trim($value); }
	
	$id = intval($_GET['id']);
	
	if ( $database->num_rows("SELECT * FROM `tcgs` WHERE `id`='$id'") == 0 ) { echo "This TCG does not exist."; }
	else {
		
		if ( isset($_POST['update']) ) {
			
			$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
			
			$sanitize = new Sanitize;
			function sanitize(&$value) { $sanitize = new Sanitize; $value = trim($value); $value = $sanitize->for_db($value); }
			
			$logentry = $sanitize->for_db($_POST['logentry']);
			$logtype = $sanitize->for_db($_POST['logtype']);
			if ( $logtype !== 'activity' && $logtype !== 'trade' ) { $logtype = 'activity'; }
			$newcards = $_POST['newcards'];
			$cardscat = $_POST['cardscat'];
			$activitylog = $sanitize->for_db($_POST['activitylog']);
			
			array_walk($newcards,'sanitize');
			array_walk($cardscat,'sanitize');
			
			$result = $database->query("SELECT * FROM `additional` WHERE `tcg`='$id'");
			while ( $row = mysqli_fetch_assoc($result) ) {
				$varname = ''.$row['name'].'_add';
				$$varname = $sanitize->for_db($_POST[$varname]);
			}
			
			// Update activity log first, if changed
			if ( $tcginfo['activitylog'] != $activitylog ) {
				$result = $database->query("UPDATE `tcgs` SET `activitylog`='$activitylog' WHERE `id`='$id' LIMIT 1");
				if ( !$result ) { $error[] = "Could not update the activity log. ".mysqli_error().""; }
			}
			
			// Insert the new log entry, if supplied
			if ( $logentry !== '' ) {
				$log = "- $logentry";
				$today = date("Y-m-d");
			
				$dateformat = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting`='dateformat'");
				$dateformat = $dateformat['value'];
				$dateheaderformat = $database->get_assoc("SELECT `value` FROM `settings` WHERE `setting`='dateheaderformat'");
				$dateheaderformat = $dateheaderformat['value'];
				
				$date = date("$dateformat");
				$logdate = $sanitize->for_db(str_replace('[DATE]',$date,$dateheaderformat));
				
				if ( $logtype == 'activity' ) { $logtype = 'activitylog'; } else { $logtype = 'tradelog'; }
				
				$oldlog = $sanitize->for_db($tcginfo[$logtype]);
				
				if ( strpos($oldlog,$logdate) !== false ) { $tradelog = str_replace($logdate,"$logdate\n$log",$oldlog); }
				else { $tradelog = "$logdate\n$log\n\n".$oldlog.""; }
				
				$result = $database->query("UPDATE `tcgs` SET `$logtype`='$tradelog', `lastupdated`='$today' WHERE `id`='$id' LIMIT 1");
				if ( !$result ) { $error[] = "Failed to insert new entry into the $logtype log. ".mysqli_error().""; } else { $success[] = "Inserted new log entry."; }
			}
			
			// Insert new cards, if any
			if ( implode(',',array_filter($newcards)) !== '' ) {
				
				$i = 0;
				foreach ( $newcards as $cardgroup ) {
					
					if ( !isset($error) && trim($cardgroup) !== '' ) {
						
						if ( $cardscat[$i] == 'collecting' ) {
							
							$cardgroup = explode(',',$cardgroup);
							array_walk($cardgroup, 'trim_value');
							
							foreach ( $cardgroup as $card ) {
							
								$deck = preg_replace('/[^a-z_-]{2,3}$/i', '', $card);
								$exists = $database->num_rows("SELECT `id` FROM `collecting` WHERE `deck` LIKE '%$deck%' AND `tcg`='".$tcginfo['id']."' AND `mastered`='0' LIMIT 1");
								if ( $exists == 0 ) { 
									$result = $database->query("INSERT INTO `collecting` (`tcg`,`deck`,`cards`,`worth`,`count`,`break`,`filler`,`pending`,`puzzle`,`auto`,`uploadurl`) VALUE ('".$tcginfo['id']."','$deck','$card','1','15','5','filler','pending','0','0','default')");
									if ( !$result ) { $error[] = "Failed to add $card to a collecting deck."; }
									else { $success[] = "<em>No existing collecting deck was found matching $card</em>. Created a new collecting deck for $card titled $deck."; }
								}
								else {
									$collectinginfo = $database->get_assoc("SELECT * FROM `collecting` WHERE `deck` LIKE '%$deck%' AND `tcg`='".$tcginfo['id']."' AND `mastered`='0' LIMIT 1");
									$collectingid = $collectinginfo['id'];
									$collectingcards = $collectinginfo['cards'];
									$collectingauto = $collectinginfo['auto'];
									$autourl = $collectinginfo['uploadurl'];
									$format = $collectinginfo['format'];
									if ( $collectingcards === '' ) { $collectingcards = $card; } else { $collectingcards = "$collectingcards, $card"; }
									$result = $database->query("UPDATE `collecting` SET `cards`='$collectingcards' WHERE `id`='$collectingid' LIMIT 1");
									if ( !$result ) { $error[] = "Failed to add $card to a collecting deck."; }
									else { $success[] = "Added $card to the $deck collecting deck."; }
									
									if ( $tcginfo['autoupload'] == 1 && $collectingauto == 1 ) {
										
										if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
										else { $defaultauto = $autourl; }
										
										if ( $format == 'default' ) { $formatval = $tcginfo['format']; }
										else { $formatval = $format; }
											
										$upsuccess = $upload->card($tcginfo,$collectinginfo,'collecting',$card);
									
										if ( $upsuccess === false ) { $error[] = "Failed to upload $card.$formatval from $defaultauto"; }
										else if ( $upsuccess === true ) { $success[] = "Uploaded $card.$formatval."; }
												
									}
									
								}
								
							}
						
						}
						
						else {
						
							$catinfo = $database->get_assoc("SELECT * FROM `cards` WHERE `tcg`='$id' AND `category`='".$cardscat[$i]."'");
							$catcards = $catinfo['cards'];
							$catauto = $catinfo['auto'];
							$autourl = $catinfo['autourl'];
							$format = $catinfo['format'];
							
							if ( $catcards === '' ) { $catcards = $cardgroup; } else { $catcards = ''.$catcards.', '.$cardgroup.''; }
							
							$catcards = explode(',',$catcards);
							array_walk($catcards, 'trim_value');
							
							if ( $tcginfo['autoupload'] == 1 && $catauto == 1 ) {
								foreach ( $catcards as $card ) {
									if ( !isset($error) ) {
										
										if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
										else { $defaultauto = $autourl; }
										
										if ( $format == 'default' ) { $formatval = $tcginfo['format']; }
										else { $formatval = $format; }
										
										$upsuccess = $upload->card($tcginfo,$catinfo,'cards',$card);
									
										if ( $upsuccess === false ) { $error[] = "Failed to upload $card.$formatval from $defaultauto"; }
										else if ( $upsuccess === true ) { $success[] = "Uploaded $card.$formatval."; }
									
									}
								}
							}
							
							sort($catcards);
							$catcards = implode(', ',$catcards);
							
							$result = $database->query("UPDATE `cards` SET `cards`='$catcards' WHERE `tcg`='$id' AND `category`='".$cardscat[$i]."'");
							if ( !$result ) { $error[] = "Could not add cards to category '".$cardscat[$i]."'. ".mysqli_error().""; }
						
						}
						
					}
					
					$i++;
					
				}
				
			}
			
			// Update Additional Fields
			$resultt = $database->query("SELECT * FROM `additional` WHERE `tcg`='$id' AND `easyupdate`='1'");
			while ( $row = mysqli_fetch_assoc($resultt) ) {
				
				if ( !isset($error) ) {
					
					$varname = ''.$row['name'].'_add';
					$addname = $$varname;
					
					$fieldname = $row['name'];
					$result = $database->query("UPDATE `additional` SET `value`='$addname' WHERE `tcg`='$id' AND `name`='$fieldname' LIMIT 1");
					if ( !$result ) { $error[] = "Failed to update the aditional field '$fieldname'. ".mysqli_error().""; }
				
				}
				
			}
			
			if ( !isset($error) ) { $success[] = "All changes have been made successfully."; }
		
		}
	
		$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
		
		$categories[] = 'collecting';
		$result = $database->query("SELECT `category` FROM `cards` WHERE `tcg`='$id'"); 
		while ( $row = mysqli_fetch_assoc($result) ) {
			$categories[] = $row['category'];
		}
		sort($categories);
		
		?>
		
		<script type="text/javascript">

		function addElement() {
		  var ni = document.getElementById('myDiv');
		  var numi = document.getElementById('theValue');
		  var num = (document.getElementById('theValue').value -1)+ 2;
		  numi.value = num;
		  var newdiv = document.createElement('div');
		  var divIdName = 'my'+num+'Div';
		  newdiv.setAttribute('id',divIdName);
		  newdiv.innerHTML = '<br /> <input name="newcards[]" type="text" id="newcards[]" size="50"> = <select name="cardscat[]" id="cardscat[]"><?php foreach ( $categories as $category ) { ?><option value="<?php echo trim($category); ?>" <?php if ( trim($categ[$i]) == trim($category) ) { echo 'selected="selected"'; } ?>><?php echo trim($category); ?></option><?php } ?></select>';
		  ni.appendChild(newdiv);
		}
		
		</script>
		
		<h1>Easy Updater</h1>
        <p>Use the quick log to automatically insert a new log entry. You can also view and edit your full activity log manually at the bottom of the form. <em>All fields are optional</em>.</p>
		
		<?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>
		<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><div class="success"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><?php } } ?>
		       
		<form action="" method="post">
		<table align="center" cellpadding="5" cellspacing="5" class="style1">
   	  <tr>
				<td colspan="2" align="center" class="top">Quick Log</td>
			</tr>
			<tr>
				<td>TCG: </td>
				<td>
					<strong><?php echo $tcginfo['name']; ?></strong>
				</td>
			</tr>
			<tr class="xlight">
				<td>New Log Entry: </td>
				<td><input name="logentry" type="text" id="logentry" size="60"></td>
			</tr>
            <tr>
				<td>Log Type: </td>
				<td><select name="logtype" id="logtype">
				  <option value="activity" selected>Activity</option>
				  <option value="trade">Trade</option>
			  </select></td>
			</tr>
			<tr>
				<td colspan="2" align="center" class="top">New Cards <input type="hidden" value="0" id="theValue" /> <input name="newField" type="button" id="newField" value="+" onclick="addElement('easyupdate');"></td>
			</tr>
			<tr>
				<td colspan="2" align="center">
                    <input name="newcards[]" type="text" id="newcards[]" size="50"> = 
                  	<select name="cardscat[]" id="cardscat[]">
                        <?php foreach ( $categories as $category ) { ?><option value="<?php echo trim($category); ?>" <?php if ( trim($categ[$i]) == trim($category) ) { echo 'selected="selected"'; } ?>><?php echo trim($category); ?></option><?php } ?>
                    </select>
                    <div id="myDiv"> </div>
              </td>
			</tr>
            <?php if ( $database->num_rows("SELECT `name`, `value` FROM `additional` WHERE `tcg`='$id' AND `easyupdate`='1' ORDER BY `id`") > 0 ) { ?>
            <tr>
				<td colspan="2" align="center" class="top">Additional Fields</td>
			</tr>
            <?php $result = $database->query("SELECT `name`, `value` FROM `additional` WHERE `tcg`='$id' AND `easyupdate`='1' ORDER BY `id`"); while ( $row = mysqli_fetch_assoc($result) ) { ?>
            <tr>
           	  <td><?php echo $row['name'] ?>: </td>
                <td><input name="<?php echo $row['name'] ?>_add" type="text" id="kk" value="<?php echo $row['value']; ?>" size="45"></td>
            </tr>
            <?php } } ?>
            <tr>
            	<td colspan="2" align="center" class="top">Activity Log</td>
            </tr>
            <tr>
            	<td colspan="2" align="center"><textarea name="activitylog" cols="70" rows="5" id="activitylog"><?php echo $tcginfo['activitylog']; ?></textarea></td>
            </tr>
            <tr>
            	<td colspan="2" align="right" class="xdark"><input name="update" type="submit" value="Update" id="update"> <input name="reset" type="reset" value="Reset Fields" id="reset"></td>
            </tr>
		</table>
		</form>

<?php } } include 'footer.php'; ?>
