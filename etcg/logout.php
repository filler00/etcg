<?php

session_name("easyTCGFM"); session_start(); define('VALID_INC', TRUE); include 'class_lib.php';

$session = new Session;

$session->close();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>easyTCG FM - Login</title>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body>

<div align="center"><img src="images/top.png" alt="" /></div>
<br />
<table cellspacing="5" cellpadding="5" align="center" class="style1" width="500">
<tr>
  <td align="center"><table cellspacing="5" cellpadding="5" align="center" class="style1" width="500">
    <tr>
      <td align="center">You have been logged out.<br />&raquo; <a href="index.php">Return to the Login Page</a></td>
    </tr>
  </table></td>
</tr>
</table>
</body>
</html>
