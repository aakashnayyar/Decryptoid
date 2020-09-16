<?php
   global $conn; global $result; global $query;
   $email = mysql_entities_fix_string($conn, $_POST['email']);
   $username = mysql_entities_fix_string($conn, $_POST['username']);
   $password = mysql_entities_fix_string($conn, password_hash($_POST['password'], PASSWORD_BCRYPT));
      
   // Check if user with that email already exists
   $result = $conn->query("SELECT * FROM finalusers WHERE email='$email'") or die($conn->error());

if ($result->num_rows > 0 ) {  
  echo 'User with this email already exists!'; 
}
else { // Email doesn't already exist in a database
  $sql = "INSERT INTO finalusers(email, username, password) VALUES ('$email', '$username', '$password')";
  $result = $conn->query($sql);
  if ($result){
    echo "Registration complete.";
  }
  else {
    echo 'Registration failed!';
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