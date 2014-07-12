#EasyTCG
- For updates & release details, please see: https://github.com/tooblue/etcg/releases
- For documentation & how-to's, please see: https://github.com/tooblue/etcg/wiki

##LICENSE
EasyTCG is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

##SPECIFICATIONS
1. PHP 5.2 and up **
2. 1 MySQL Database (MySQL v4.1.3+)
3. JavaScript enabled

*Because the script utilizes the `filter_var()` PHP function to validate emails and URLs, this script is compatible only with 5.2 and up. If you still want to use the script and you have some PHP know-how, you can go through the files and replace all occurances of filter_var() with a preg_match() equivalent, or remove it altogether at your own risk.*

*There are also known bugs for the `filter_var()` function in PHP 5.2.13. If you have this version of PHP, you also will need to apply the modification mentioned above, or attempt to upgrade your PHP installation to a more recent version.*

##INSTALLATION
*Follow these instructions if you are installing eTCG for the first time.*

1. If you haven't already, create a database to use for EasyTCG.
2. Open up your `config.php` file (inside the 'etcg' folder). Change the database settings at the top of the file to match those of your eTCG database. You can also define your **password salt**. Treat your salt as a high security password (ie. long chains of random letters, numbers, and symbols are good). You wont have to remember your salt, so don't worry about that.
3. Upload your **'etcg'** folder and its contents to your TCG post's directory (or wherever you want your eTCG admin panel to be). Feel free to change the name of this directory to something else.
4. Upload your `func.php` to your trade post directory (NOT inside the admin panel directory). You can also upload `trade.php` and `tradeform.php` to this directory if you want to use the automated trade form.
5. Inside the `func.php` file, update the path to `config.php` if you changed the name and/or location of your 'etcg' folder. 
6. Direct your browser to `install.php` (inside the 'etcg' directory) and follow the insructions on the page to set up your database. Remember to **DELETE THIS FILE WHEN YOU'RE DONE**.
7. At this point, installation should be complete and you should be able to log in to your admin panel and start adding your TCGs, cards, etc. Continue reading for details and how-to's for various features. The last section explains how to set up your TCG Post (ie. displaying your cards, logs, etc.).

##Upgrading
*Follow these instructions if you are upgrading from an older version of eTCG.*

1. DELETE the `etcg/config.php` and `etcg/install.php` files included in the download. You should continue to use your old 'config.php' file, and `install.php`is only necessary for first-time installs - **Do NOT attempt to run 'install.php'**.
2. Replace your old eTCG files with the new ones.
3. Follow any additional instructions outlined in the [release notes](https://github.com/tooblue/etcg/releases).
