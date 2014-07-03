<?php  if ( ! defined('VALID_INC') ) exit('No direct script access allowed');

//****************** EASYTCG FM CLASS LIBRARY ******************//
// CHANGE SETTINGS BELOW TO MATCH YOUR EASYTCG FM DATABASE SETTINGS

class Config {

	const DB_SERVER = 'localhost', // In most cases, you can leave this as 'localhost'. If you're unsure, check with your host.
		  DB_USER = 'dbuser', // Database user
		  DB_PASSWORD = 'dbpassword', // Database user password
		  DB_DATABASE = 'dbdatabase', // Database name
		  DB_SALT = 'aEF#TGgs-!dgaw3324_WQ+'; // This is your password salt. Feel free to keep the default value, but it is advised that you change it. Treat it like a high security password.

}