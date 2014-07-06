#EasyTCG FM
For updates please see: https://github.com/tooblue/etcg/releases

##LICENSE
EasyTCG FM is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

##SPECIFICATIONS
1. Size: 211 KB
2. PHP 5.2 and up **
3. 1 MySQL Database (MySQL v4.1.3+)
4. JavaScript enabled

*Because the script utilizes the `filter_var()` PHP function to validate emails and URLs, this script is compatible only with 5.2 and up. If you still want to use the script and you have some PHP know-how, you can go through the files and replace all occurances of filter_var() with a preg_match() equivalent, or remove it altogether at your own risk.*

*There are also known bugs for the `filter_var()` function in PHP 5.2.13. If you have this version of PHP, you also will need to apply the modification mentioned above, or attempt to upgrade your PHP installation to a more recent version.*

##INSTALLATION
1. If you haven't already, create a database to use for EasyTCG.
2. Open up your `config.php` file (inside the 'etcg' folder). Change the database settings at the top of the file to match those of your eTCG database. You can also define your **password salt**. Treat your salt as a high security password (ie. long chains of random letters, numbers, and symbols are good). You wont have to remember your salt, so don't worry about that.
3. Upload your **'etcg'** folder and its contents to your TCG post's directory (or wherever you want your eTCG admin panel to be). Feel free to change the name of this directory to something else.
4. Upload your `func.php` to your trade post directory (NOT inside the admin panel directory). You can also upload `trade.php` and `tradeform.php` to this directory if you want to use the automated trade form.
5. Inside the `func.php` file, update the path to `config.php` if you changed the name and/or location of your 'etcg' folder. 
6. Direct your browser to `install.php` (inside the 'etcg' directory) and follow the insructions on the page to set up your database. Remember to **DELETE THIS FILE WHEN YOU'RE DONE**.
7. At this point, installation should be complete and you should be able to log in to your admin panel and start adding your TCGs, cards, etc. Continue reading for details and how-to's for various features. The last section explains how to set up your TCG Post (ie. displaying your cards, logs, etc.).


##SETTINGS
Manage general eTCG settings by clicking the "settings" tab in the navigation. 

- **Your Name**: This is both your username to log into the eTCG admin panel, and the name used in automated outgoing emails sent by eTCG.
- **Password**: Your password to log in to your eTCG admin panel. This field only needs to be filled out if you're changing your password.
- **Your Email**: All outgoing emails (ie. trade acceptance emails) will be sent from this address, and incoming emails (trade requests) will be sent TO this address.
- **Trade Post**: The URL to your trade post. A link to your trade post is included at the bottom of outgoing emails.
- **eTCG URL**: The URL to  your eTCG admin panel. This is included in incoming trade request emails.
- **Email Message**: An optional message to include at the top of outgoing trade acceptance emails.
- **Log Date Format**: This is the date format that eTCG will use for your log entries. For example, if you want your dates in mm/dd/yy format, you would put "m/d/y" into the Log Date Format field.
- **Date Header Format**: This is the pattern to use for date headers in your logs. Insert [DATE] into the pattern where you want the date displayed. 
	* ie. Put `[DATE] ------------` into the Date Header Format field if you want your headers to look like: `04/23/10 ------------`.
- **Hiatus Trading**: This feature, if enabled, will automatically block incoming trade requests for TCGs that you have set as 'hiatus' when using the eTCG trade form.
- **Inactive Trading**: This feature, if enabled, will automatically block incoming trade requests for TCGs that you have set as 'inactive' when using the eTCG trade form.

##NEW TCG
Add new TCGs to your eTCG admin panel through this page.

- **TCG Name**: The name of the TCGs. Spaces and special characters are allowed. You'll use this name to call up data for specific TCGs in your code snippets.
- **TCG URL**: The URL to the TCG's website.
- **Cards Directory URL**: The URL to your cards/images directory.
- **Cards Path**: The direct PATH to the directory where you will be uploading cards and other images for the TCG. All images uploaded by the auto upload feature will be sent here as well.
- **Image Format**: The image format used by the TCG. Do not include the period. ex. gif, jpg, jpeg, png
- **Default Upload URL**: The URL to the directory where the TCG owner has uploaded their cards (ex. http://tcg.domain.com/cards/). If the TCG owner saves their cards in different directories according to worth/type/etc, just input the URL that will be used most often (most likely the URL to the regular cards directory). You can set different upload URLs for individual card categories if they are different from this default URL. This feature may be incompatible with some TCGs depending on how the TCG owner has organized their cards. If you will not be using the auto upload feature, you can leave this blank.
- **Auto Upload**: Select YES to enable the auto upload feature for this TCG. This feature will attempt to automatically upload your cards directly from the TCG's site. This feature is incompatible with some TCGs (ie. if the TCG organizes their cards in directories by deck name instead of by category, such as http://tcg.domain.com/cards/deckname/).
- **Status**: Your status in the TCG. You can use this feature in conjunction with the "Hiatus Trading" and "Inactive Trading" feature in your general eTCG settings to block incoming trades for TCGs that you are not active in.
- **Additional Fields**: List the names of any additional fields that you would like to use. This can be used for currency, coupons, levels, items, or anything that you would like to keep track of through eTCG. Max 255 characters.

_* A note about the Auto Upload Feature: It can't check whether the image that it is attempting to upload actually exists on the TCG's server, and so it will upload the contents of the file (ex. http://tcg.domain.com/cards/cardname00.gif) even if it is just a blank page or an error page. This may occur if you make a typo while inserting cards into your collection, or if your auto upload URL is wrong, and will result in broken images. If this occurs, just delete the bad file, correct your mistakes, and add it again._

##MANAGE TCGS
After adding a TCG, you can select a TCG to manage from the left drop down menu. When a TCG is selected, additional options become available in the left side navigation. There are five actions that you can take from here: __settings, cards, logs, trades, update__.

###TCG SETTINGS
Settings and additional fields for individual TCGs can be managed here. The details for most fields are covered in the previous section (New TCG). Fields which only appear in TCG Settings include:

- **Last Updated**: This is date of your last update. This field if updated automatically when you complete a trade or use the easy updater. If changing the date manually, keep the format (yyyy-mm-dd) intact.
- **Additional Fields**: You can add, modify, and delete your additional fields from here. Select the check box next the the field values to make that field editable through the easy updater.
- **Remove This TCG**: Does just what its name suggests. This will completely remove the TCG and all related data items from the database.

###CARD CATEGORIES
- **Priorities**:
	- **LOW PRIORITY**: The default priority value. Intended for cards that you are willing to trade away unconditionally. Cards is this category will be searched first whenever someone requests cards from you via the trade form, as well as when you are adding cards to a pending trade via the "grab" feature. This category will also be searched LAST when using the "grab" feature to move cards to a new collecting deck.
	- **MEDIUM PRIORITY**: Intended for cards that you value more than Low priority cards, but are still willing to trade away (keeping or future decks). Cards in this category will be searched second (after low priority categories) whenever someone requests cards from you via the trade form, as well as when you are adding cards to a pending trade via the "grab" feature. This category is searched second (after high priority cards) when using the "grab" feature to move cards to a new collecting deck.
	- **HIGH PRIORTITY**: Intended for cards that you will probably (if not definately) be collecting, and that you will not even consider trading away. Cards in this category will NOT be searched whenever someone requests cards from you via the trade form, as well as when you are adding cards to a pending trade via the "grab" feature. This category is searched FIRST when using the "grab" feature to move cards to a new collecting deck.
- **New Category**: Fill this out to add a new card category. The checkbox is for the AUTO feature. Select it if you want cards that are added to this category to be uploaded automatically (should the feature be turned on in the TCG's settings).
I suggest setting up and organizing your categories by worth and priority. For example, I might make a category with the name "regkeeping" for cards that are worth 1 and of a medium priority. Then another called "speckeeping" (for cards worth 2, medium priority), "regtrading" (for cards worth 1, low priority), and "spectrading" (for cards worth 2, low priority).
You can also make categories for member cards, trade patches, items, and other miscellaneous things. Set the priority to HIGH if they can't be traded and worth to 0 if they don't count towards your card worth.
- **AUTO**: You can turn the auto upload feature on or off for each category by selecting/deselecting this checkbox.
- **Upload URL**: If the cards in a category are kept in a directory other than the default defined in your TCG settings, you can define a different directory for the category here. If the card directory is the same as the one defined as the default in your TCG settings, leave the value of this field as 'default'.

###COLLECTING DECKS
- **Deck Name**: The name of the deck as it will be used in the card file names.
- **Worth**: The worth of each card in the deck.
- **Count**: The total number of cards in the deck when completed.
- **Break**: Defines where to insert a line break (ex. input 5 to insert a line break after every 5 cards.). Set the value to 0 if you don't want line breaks.
- **Filler**: The filler card file name. This is used to show missing cards in your deck collection. Must be the same format as defined in the TCG settings.
- **Pending**: The pending card file name. This is used to show cards that you may be receiving from a trade. Must be the same format as defined in the TCG settings.
- **Cards**: The cards that you currently have. Only insert the card numbers. ex. Instead of putting deckname01, deckname02, deckname03, put 01, 02, 03.
OR
- **Grab From Categories**: If this option is selected, the value of the 'cards' field is ignored and the "grab" feature will search for cards in your card categories that match the supplied deck name. It will not grab doubles. The cards that it finds will be removed from the card categories and added to the new collecting deck.
- **AUTO**: You can turn the auto upload feature on or off for each collecting deck by selecting/deselecting this checkbox.
- **Auto URL**: If the cards in a collecting deck are kept in a directory other than the default defined in your TCG settings, you can define a different directory for the deck here. If the card directory is the same as the one defined as the default in your TCG settings, leave the value of this field as 'default'.
- **Puzzle**: Select this option to bring the cards closer together (eliminate spaces). Ideal for "puzzle" decks.
- **Sort**: The order that you would like the decks displayed in, in ascending order. Decks with the same sort value are ordered by worth and deck name.

_* When the script detects that you have all of the cards in a collecting deck, a "MASTER" button will show up next to the deck's "UPDATE" button, which you can click to move that deck to the Mastered decks section._

###MASTERED DECKS
Fields are exactly the same as those in the collecting section, with the exception of two new fields for master badges and a date (for when the deck was mastered).

Click the master badge (or the name of the deck, if you have not added a badge yet) to view the deck settings and add/change the master badge and date.

##LOGS
There are two logs per TCG: one to log various TCG **activities**, and another to log **trades**.

Your logs have a maximum length of approximately 4,294,967,295 or 4GB characters (also depends on your server). However, very long logs can potentially cause things to load slowly, so you should ARCHIVE them periodically. There is an archive button for your logs here, which will move all current logs to **archived** logs. 

Keep in mind that these archived logs have the same limit as your current logs, and take up the same amount of space in your database. The benefit is that they are never called up by the script, except if you should choose to edit them or display them on your trade post. You may also prefer to just save the archives on your own computer.

##EASY UPDATER
Ideal for quickly adding new cards, logging your activities, and editing select additional fields efficiently and simultaneously (i.e. for use while you're playing games!).

- **New Log Entry**: Input a new log entry to be automatically inserted into your log under the appropriate date. A dash (-) is automatically added before log entries as they're inserted.
- **Log Type**: Select the log to insert the new entry into. Default is activity log.
- **New Cards**: Insert new cards into your collection through these fields, separating cards with commas and selecting the category to add the cards to through the drop down menu. Click the "+" button next to the "NEW CARDS" header to generate more fields.
If collecting is selected, cards are added to a collecting deck with a matching name. If no collecting deck is found with a matching name, a new collecting deck is created. (*)
	- _* Do NOT rely on this feature if you can help it. The way that the script generates the deck name from the card name is not full proof, and the settings may not be what you were expecting. Set up the collecting deck yourself through the Collecting Decks manager FIRST._
- **Additional Fields**: Self explanitory. If you selected the check box next to any additional fields in your TCG settings, they will show up on this page so that you can edit them from here.
- **Activity Log**: You can edit and/or update your activity log manually from here as well.

##TRADES
By default, this page will show all pending trades for your active TCGs (the counter in the navigation shows total trades, including those from hiatus/inactive TCGs). If you click "trade" under the "Quick Access" section on the index, or on the "Manage TCGs" page, it will show trades only for the corresponding TCG.

Trade requests submitted via the tradeform.php that came with your easyTCG download will be automatically added to the database, and can be seen on this page under the "incoming" headers. You can also manually add pending trades by clicking "New Pending Trade" under the appropriate TCG's header.

- **Trading Cards**: The cards that you are trading away. The categories that these cards were pulled from should be defined so that they are still added to your card worth. They will also be replaced in these categories should you cancel the trade.
- **Receiving Cards**: The cards that you will be receiving from the trade. Select a category from the drop down menu to define the category that the cards should be sent to should the trade be completed.
- **Grab From Categories**: Selecting this option when you add a new pending trade will enable the "grab" feature, which will search for the cards defined in the 'trading cards' field, remove them from the card categories, and add them to the pending trade. If a card is not found, it will display an error and the pending trade will be added without the card.
- **Email Cards**: This option will appear for pending trades where the trader's email has been supplied. If this option is selected, the trader will be emailed when you click "complete trade" with an overview of the trade and URL links to their new cards.
- **UPDATE Button**: This button will update the data for the trade and add any new fields that you have defined.
- **COMPLETE TRADE Button**: The pending trade and all 'trading' cards will be removed, and receiving cards will be added to the indicated categories. If that category has the AUTO feature enabled, the images will be uploaded as well.
If collecting is selected as the category, cards are added to a collecting deck with a matching name. If no collecting deck is found with a matching name, a new collecting deck is created. (*)
	- _* Do NOT rely on this feature if you can help it. The way that the script generates the deck name from the card name is not fool proof, and the settings may not be what you were expecting. Set up the collecting deck yourself through the Collecting Decks manager FIRST._
- **REMOVE TRADE Button**: The pending trade will be removed and 'trading' cards are replaced in their categories.

##ETCG TRADE FORM
Using the trade form (`trade.php` and `tradeform.php`) included with eTCG is optional. Features include:

- Automatically populated TCG selection menu.
- Automatically removes the requested cards from your collection and adds them to a new pending trade.
- Sends a notification to your email address with the trade details and a link to the eTCG trades manager.
	
To use the eTCG trade form, simply upload `trade.php` and `tradeform.php` to your trade post directory.

You can work off of `trade.php` directly, or you can include it on another page. To include the form on an existing page, use the following snippet:

```php
<?php include 'trade.php'; ?>
```

Be sure to include the `func.php` file (instructions in the next section) on any page that you use the form on.

##TCG POST SET-UP
The code snippets that you will be using to display your cards, logs, and other things that you've been managing in your eTCG admin panel are below. **YOU MUST INCLUDE `func.php` ON ANY PAGE THAT YOU USE THESE CODE SNIPPETS ON**. The code to include `func.php` is below. This must be inserted BEFORE any code snippets.

Include `func.php`:

```php
<?php define('VALID_INC', TRUE); include_once 'func.php'; ?>
```


###DISPLAYING LOGS
Replace **TCG NAME** with the name of the TCG that you are referencing (as defined in your TCG settings) and replace **LOG TYPE** with `activitylog`, `activitylogarch`, `tradelog`, or `tradelogarch`.

Use this code to display your logs in a text-box:

```php
<?php echo get_logs('TCG NAME','LOG TYPE'); ?>
```

If you don't want your logs in a text-box, use this code to convert line breaks (`\n`) to `<br />` tags:

```php
<?php echo str_replace("\n","<br />",get_logs('TCG NAME','LOG TYPE')); ?>
```

If you want to use BOLD, ITALICS, and other HTML tags in your logs, use the following:
	
```php
<?php
$codes = array("[b]","[/b]","[i]","[/i]");
$html = array('<strong>','</strong>','<em>','</em>');
?>

<?php echo str_replace($codes,$html,str_replace("\n","<br />",get_logs('TCG NAME','activitylog'))); ?>
```

This will replace any occurances of [b][/b] and [i][/i] with the appropriate HTML tags, similar to the way BBCode works on forums. It also includes the snippet mentioned before it, and so will convert line breaks to `<br />` as well. You can add to the arrays if you want to use more tags.

###DISPLAYING ADDITIONAL FIELD VALUES
Replace **TCG NAME** with the name of the TCG that you are referencing (as defined in your TCG settings) and replace **FIELD NAME** with the name of the additional field that you are calling up.

```php
<?php echo get_additional('TCG NAME','FIELD NAME'); ?>
```

###DISPLAYING CARDS FROM CATEGORIES
Replace **TCG NAME** with the name of the TCG that you are referencing (as defined in your TCG settings) and replace **CATEGORY NAME** with the name of the card category that you want displayed.

```php
<?php show_cards('TCG NAME','CATEGORY NAME'); ?>
```
	
###DISPLAYING DOUBLES FROM CATEGORIES
Replace **TCG NAME** with the name of the TCG that you are referring to (as defined in your TCG settings) and replace **CATEGORY NAME** with the name of the card category that you want displayed.

```php
<?php show_doubles('TCG NAME','CATEGORY NAME'); ?>
```
	
To display all cards from a category omitting doubles, use the following snippet:

```php
<?php show_cards('TCG NAME','CATEGORY NAME', 1); ?>
```

###DISPLAYING COLLECTING DECKS
Replace **TCG NAME** with the name of the TCG that you are referencing (as defined in your TCG settings).

To display all collecting decks:

```php
<?php show_collecting('TCG NAME'); ?>
```

To display only collecting decks with the given card worth value:

```php
<?php show_collecting('TCG NAME','WORTH[optional]'); ?>
```

To display only collecting decks with the given deck name:

```php
<?php show_collecting('TCG NAME','','DECK NAME[optional]'); ?>
```

###DISPLAYING MASTERED DECKS
Replace **TCG NAME** with the name of the TCG that you are referencing (as defined in your TCG settings). Mastered decks are displayed as badges, with title = date mastered.

To display all mastered decks:

```php
<?php show_mastered('TCG NAME'); ?>
```

To display only mastered decks with the given card worth value:

```php
<?php show_mastered('TCG NAME','WORTH[optional]'); ?>
```

To display only mastered decks with the given deck name:

```php
<?php show_mastered('TCG NAME','','DECK NAME[optional]'); ?>
```

###DISPLAYING PENDING TRADES
Replace **TCG NAME** with the name of the TCG that you are referencing (as defined in your TCG settings).

```php
<?php show_pending('TCG NAME'); ?>
```
	
###DISPLAYING CARD WORTH/COUNT
Replace **TCG NAME** with the name of the TCG that you are referencing (as defined in your TCG settings).

To display card worth for entire collection:

```php
<?php echo cardcount('TCG NAME','worth'); ?>
```

To display card COUNT for entire collection (ignoring worth):

```php
<?php echo cardcount('TCG NAME'); ?>
```

To display card worth for a given card category. Replace **CATEGORY NAME** with the name of a user defined category, OR `collecting`, `mastered`, or `pending` to display the worth of those static categories:

```php
<?php echo cardcount('TCG NAME','worth','CATEGORY NAME'); ?>
```

To display card **COUNT** for a given card category (ignoring worth): 

```php
<?php echo cardcount('TCG NAME','','CATEGORY NAME'); ?>
```

###DISPLAYING TCG INFORMATION
Replace **TCG NAME** with the name of the TCG that you are referencing (as defined in your TCG settings). Replace **FIELD** with the TCG information that you want displayed.

**FIELD values**: `url`, `cardsurl`, `cardspath`, `status`, `format`, `lastupdated`

```php
<?php $database = new Database; 
$tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `name`='TCG NAME'");
echo $tcginfo['FIELD']; ?>
```

When calling up multiple fields, you only need to include lines 1-2 once. Ex. If you want to call up the `cardsurl` field after executing the code snippet above, you would just need:

```php
<?php echo $tcginfo['cardsurl']; ?>
```

Display lastupdated date in **Month day, Year** format:

```php
<?php echo date('F d, Y', strtotime($tcginfo['lastupdated'])); ?>
```
