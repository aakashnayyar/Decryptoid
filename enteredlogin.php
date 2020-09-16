<?php

if (isset($_POST['login'])) {
  $email = mysql_entities_fix_string($conn, $_POST['email']);
  $password = mysql_entities_fix_string($conn, $_POST['password']);
  $result = $conn->query("SELECT * FROM finalusers WHERE email='$email'");
  if ($result->num_rows == 0) { // User doesn't exist
    echo "Login failed";
  } 
  else { // User exists
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $username = $user['username'];
      $userid = $user['id'];
      setcookie('userid', $userid, time() + 60 * 60 * 24 * 7, '/');
      setcookie('username', $username, time() + 60 * 60 * 24 * 7, '/');
      setcookie('email', $email, time() + 60 * 60 * 24 * 7, '/');
      header("location: decryptoid.php");
    } else {
      echo "You have entered wrong password or username, try again!";
      header("location: decryptoid.php");
    }
  }
}

function sanitizeString($var) {
  $var = stripslashes($var);
  $var = strip_tags($var);
  $var = htmlentities($var);
  return $var;
}

function sanitizeMySQL($connection, $var) {
  $var = $connection->real_escape_string($var);
  $var = sanitizeString($var);
  return $var;
}

function mysql_entities_fix_string($conn, $string){
  return htmlentities(mysql_fix_string($conn, $string));
}

function mysql_fix_string($conn, $string){
 if (get_magic_quotes_gpc()) $string = stripslashes($string);
 return $conn->real_escape_string($string);
}


?>