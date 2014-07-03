<?php include 'header.php'; if ( !isset($_GET['id']) || $_GET['id'] == '' ) { ?>
           
<h1>Manage Cards</h1>

<?php } else if ( $_GET['id'] != '' ) { 
	
	$id = intval($_GET['id']);
	$database = new Database;
	$upload = new Upload;

	$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `id`='$id' LIMIT 1");
	$altname = strtolower(str_replace(' ','',$tcginfo['name']));
	
	if ( isset($_POST['update']) ) {
		
		$sanitize = new Sanitize;
		
		$catid = intval($_POST['id']);
		$category = $sanitize->for_db($_POST['category']);
		$cards = $sanitize->for_db($_POST['cards']);
		$worth = intval($_POST['worth']);
		$auto = intval($_POST['auto']);
		$priority = intval($_POST['priority']);
		$autourl = $sanitize->for_db($_POST['autourl']);
		$format = $sanitize->for_db($_POST['format']);
		
		if ( $autourl != 'default' && $autourl != '' && substr($autourl, -1) != '/' ) { $autourl = "$autourl/"; }
		if ( $autourl == '' ) { $autourl = 'default'; }
		if ( $format == '' ) { $format = 'default'; }
		if ( $worth === '' ) { $worth = 1; }
		
		if ( $category == '' ) { $error[] = "The category name must be defined."; }
		else if ( $auto != 1 && $auto != 0 ) { $error[] = "Invalid auto value."; }
		else if ( $priority != 1 && $priority != 2 && $priority != 3 ) { $error[] = "Invalid priority value."; }
		else if ( $autourl != 'default' && !filter_var($autourl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid Auto URL."; }
		else if ( $database->num_rows("SELECT * FROM `cards` WHERE `id`=".$catid."") != 1 ) { $error[] = "Invalid category ID."; }
		else {
			
			if ( $cards != '' ) {
				$cards = explode(',',$cards);
				
				function trim_value(&$value) { 
					$value = trim($value); 
				}
				
				array_walk($cards, 'trim_value');
				
				if ( $tcginfo['autoupload'] == 1 && $auto == 1 ) {
					foreach ( $cards as $card ) {
						if ( !isset($error) ) {
						
							if ( $autourl == 'default' ) { $defaultauto = $tcginfo['defaultauto']; }
							else { $defaultauto = $autourl; }
							
							if ( $format == 'default' ) { $formatval = $tcginfo['format']; }
							else { $formatval = $format; }

							$upsuccess = $upload->card($tcginfo,'','cards',$card,$defaultauto,$formatval);
									
							if ( $upsuccess === false ) { $error[] = "Failed to upload $card.$formatval from $defaultauto"; }
							else if ( $upsuccess === true ) { 
								if ( !isset($success) || (isset($success) && !in_array("All missing cards have been uploaded",$success)) ) {
									$success[] = "All missing cards have been uploaded";
								}
							}
						
						}
					}
				}
				
				sort($cards);
				$cards = implode(', ',$cards);
			}
		
			$result = $database->query("UPDATE `cards` SET `category`='$category', `cards`='$cards', `worth`='$worth', `auto`='$auto', `autourl`='$autourl', `format`='$format', `priority`='$priority' WHERE `id`='$catid' LIMIT 1");
			if ( !$result ) { $error[] = "Could not update the category. ".mysqli_error().""; }
			else { $success[] = "Category <em>$category</em> was updated successfully!"; }
		
		}
		
	}
	
	if ( isset($_POST['newcat']) ) {
		
		$sanitize = new Sanitize;
		
		$category = $sanitize->for_db($_POST['category']);
		$worth = intval($_POST['worth']);
		$auto = intval($_POST['auto']);
		$autourl = $sanitize->for_db($_POST['autourl']);
		
		if ( $autourl != 'default' && $autourl != '' && substr($autourl, -1) != '/' ) { $autourl = "$autourl/"; }
		if ( $autourl == '' ) { $autourl = 'default'; }
		if ( $worth === '' ) { $worth = 1; }
		
		$exists = $database->num_rows("SELECT `category` FROM `cards` WHERE `tcg`='$id' AND `category`='$category'");
		
		if ( $category == '' || $category == 'category name' ) { $error[] = "The category name must be defined."; }
		else if ( $exists != 0 ) { $error[] = "A category witht that name already exists."; }
		else if ( $auto != 1 && $auto != 0 ) { $error[] = "Invalid auto value."; }
		else if ( $autourl != 'default' && !filter_var($autourl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ) { $error[] = "Invalid Auto URL."; }
		else {
		
			$result = $database->query("INSERT INTO `cards` (`tcg`,`category`,`worth`,`auto`,`autourl`) VALUE ('$id','$category','$worth','$auto','$autourl')");
			if ( !$result ) { $error[] = "Could not update the category. ".mysqli_error().""; }
			else { $success[] = "Category <em>$category</em> was created successfully."; }
		
		}
		
	}
	
	if ( $_GET['action'] == 'delete' && isset($_GET['cat']) ) {
	
		$catid = intval($_GET['cat']);	
		$exists = $database->num_rows("SELECT * FROM `cards` WHERE `id`='$catid'");
		
		if ( $exists === 1 ) {
		
			$result  = $database->query("DELETE FROM `cards` WHERE `id` = '$catid' LIMIT 1");
			if ( !$result ) { $error[] = "There was an error while attempting to remove the category. ".mysqli_error().""; }
			else { $success[] = "The category has been removed."; }
		
		}
		
		else { $error[] = "The category no longer exists."; }
	
	}
	
	?>
    
    <h1>Manage Cards: <?php echo $tcginfo['name']; ?></h1>
	<p>Selecting the <strong>auto</strong> option will allow the auto upload system to automatically upload cards that are added to that category. The auto upload feature must be enabled in the TCG's settings as well for this to work. Deselect the auto checkbox to disable auto uploading for that category. Leave the <strong>auto url</strong> as <em>default</em> unless the Auto Upload URL for that category is different from the default defined in the TCG's settings.</p>
	<ul>
    	<li><strong>LOW PRIORITY</strong> <em>can be traded</em> via the trade system, checked first when moving cards to pending, and  checked last when moving cards to collecting.</li>
        <li><strong>MEDIUM PRIORITY</strong> <em>can be traded</em>, checked second when moving cards to pending, checked second when moving cards to collecting.</li>
        <li><strong>HIGH PRIORITY</strong> <em>can't be traded</em>, NOT checked when moving cards to pending, checked first when moving cards to collecting.</li>
    </ul>
	<p>&raquo; <a href="collecting.php?id=<?php echo $id; ?>">View Collecting</a> <br />&raquo; <a href="mastered.php?id=<?php echo $id; ?>">View Mastered</a></p>
    
    <br />
    
    <?php if ( isset($error) ) { foreach ( $error as $msg ) {  ?><div class="errors"><strong>ERROR!</strong> <?php echo $msg; ?></div><?php } } ?>
	<?php if ( isset($success) ) { foreach ( $success as $msg ) { ?><div class="success"><strong>SUCCESS!</strong> <?php echo $msg; ?></div><?php } } ?>
    
    <form action="cards.php?id=<?php echo $id; ?>" method="post">
	<p><strong>New Category</strong>: <input name="category" type="text" id="category" value="category name" onfocus="if (this.value=='category name') this.value='';" onblur="if (this.value=='') this.value='category name';"> 
    <input name="worth" type="text" id="worth" value="worth" size="5" onfocus="if (this.value=='worth') this.value='';" onblur="if (this.value=='') this.value='worth';"> 
	<input name="autourl" type="text" id="autourl" value="default">	
	<input name="auto" type="checkbox" value="1" id="auto"> <input name="newcat" type="submit" value="Go" id="newcat"></p>
    </form>
        
    <?php $result = $database->query("SELECT * FROM `cards` WHERE `tcg`='$id' ORDER BY `category`");
	while ( $row = mysqli_fetch_assoc($result) ) { ?>
	
    <br />
	<form action="cards.php?id=<?php echo $id; ?>" method="post">
        <input name="id" type="hidden" id="id" value="<?php echo $row['id']; ?>">
		 <table class="style1" width="100%" align="center" cellpadding="5" cellspacing="5">
            <tr>
                <td class="top" colspan="4"><?php echo $row['category']; ?> <a href="cards.php?id=<?php echo $id; ?>&action=delete&cat=<?php echo $row['id']; ?>" onclick="go=confirm('Are you sure that you want to permanently delete this category? The contents will be lost completely.'); return go;"><img src="images/delete.gif" alt="delete" style="float:right;" /></a></td>
            </tr><tr class="xlight">
                <td width="200" colspan="3"><input name="category" type="text" id="category" value="<?php echo $row['category']; ?>" size="40"></td>
                <td rowspan="4"><textarea name="cards" cols="75" rows="12" id="cards" style="font-size: 10px; font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;"><?php echo $row['cards']; ?></textarea></td>
            </tr>
            <tr class="xlight">
              <td align="center">worth<br /> <input name="worth" type="text" id="worth" value="<?php echo $row['worth']; ?>" size="2"></td>
              <td align="center">priority<br /> 
              <select name="priority" id="priority">
                <option value="1" <?php if ( $row['priority'] == 1 ) { echo 'selected="selected"'; } ?>>Low</option>
                <option value="2" <?php if ( $row['priority'] == 2 ) { echo 'selected="selected"'; } ?>>Medium</option>
                <option value="3" <?php if ( $row['priority'] == 3 ) { echo 'selected="selected"'; } ?>>High</option>
              </select>
              </td>
              <td align="center"><label>auto<br /> <input name="auto" type="checkbox" id="auto" value="1" <?php if ( $row['auto'] == 1 ) { echo 'checked'; } ?>></label></td>
            </tr>
            <tr class="xlight">
              <td colspan="3">auto url: 
                <input name="autourl" type="text" id="autourl" value="<?php echo $row['autourl']; ?>" size="25"></td>
            </tr>
			<tr class="xlight">
              <td colspan="3">format: 
                <input name="format" type="text" id="format" value="<?php echo $row['format']; ?>" size="25"></td>
            </tr>
            <tr>
            	<td colspan="4" align="right" class="xdark"><input name="update" type="submit" id="update" value="Update"> <input name="reset" type="reset" id="reset" value="Reset"></td>
           </tr>
         </table>
	</form>
        
<?php
	}
	
?>

<?php } include 'footer.php'; ?>