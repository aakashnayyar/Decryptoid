<?php
	setcookie('userid', '',time() - 60 * 60 * 24 * 7, '/');
	setcookie('username', '', time() - 60 * 60 * 24 * 7, '/');
	setcookie('email', '', time() - 60 * 60 * 24 * 7, '/');
	  //include 'decryptoid.php';
	header("Location: decryptoid.php");
?>