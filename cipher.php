<?php

$touse= null;
$glbfile= null;
$filepicked= false;
$todo = null;

if (isset($_COOKIE['username'])) {
  require_once 'login.php';
  $connection = new mysqli($hn,$un,$pw,$db);

  if ($connection->connect_error){
    mysql_fatal_err($err, $connection);
  }


  echo <<<_END
  <h1>Decrpyt/Encrypt your file content.</h1>
  <form enctype="multipart/form-data" action="cipher.php" method="POST">

  Ecrypt or decrypt file content?</br>
  <select name="type[]" multiple="multiple" required="required">
  <option value="Encrypt" >Encrypt</option>
  <option value="Decrypt">Decrypt</option>
  </select></br></br>

  Choose a decryption/encryption type.</br>
  <select name="ary[]" multiple="multiple" required="required">
  <option value="Simple Substitution" >Simple Substitution</option>
  <option value="Double Transposition">Double Transposition</option>
  <option value="RC4">RC4</option>
  <option value="DES">DES</option>
  </select></br>

  <p>Key:  <input type='text' name='key' required="required" id = 'key'></p>

  <input type="file" name="filename" required="required"></input> </br>
  <input type = 'submit' name = 'submit' value = Submit> 

  </form>
  <p> Would you like to logout? </p>
  <input type="button" value="Log Out" id="logout" onClick="document.location='/logout.php'" /> <br>
  _END;

  if(isset($_POST['ary'])) { 
    foreach ($_POST['ary'] as $cipher){
        //print "You selected $cipher<br/>"; 
      $touse = $cipher;
    }  
  } 

  if(isset($_POST['type'])) { 
    foreach ($_POST['type'] as $crypt){
        //print "You selected $cipher<br/>"; 
      $todo = $crypt;
    }  
  } 

  if($_FILES){
    $name = $_FILES['filename']['name'];
    $name = preg_replace("[^A-Za-z0-9.]", "", $name);
    $type = $_FILES['filename']['type']; //file type
    $file = $_FILES['filename']['tmp_name']; //temporary file stored on the server
    if($type === "text/plain"){
      echo("File accepted.<br/> ");
      $filepicked = true;  
      $glbfile = $file;
    }

    else{
      echo "$name was not accepted.<br/>";
    }
  }

  if(isset($_POST['submit'])&&$filepicked){
    $key = $_POST['key'];
    $key = sanitizeMySQL($connection,$key);
    $key = mysql_entities_fix_string($connection, $key);

    cryption($touse,$key,read($glbfile),$todo);
  }

  $query = "SELECT * FROM storedinfo";
  $result = $connection->query($query);

  if (!$result){
    mysql_fatal_err("OOPS!", $connection);
  } 

  $result->close();
  $connection->close();

}

else{
  require 'decryptoid.php';
}

  function read($f){
    $fo = fopen($f, 'r');
    $file = file($f);
    fclose($fo);
    return $file[0];
  }


  function cryption($cipher,$key,$input,$todo){
    global $connection; 
    if($cipher == "RC4"){
      if($todo == "Encrypt"){
       echo "Below is your encrypted file content. </br>";
       echo utf8_encode(rc4($key,$input));
     }
     else if($todo == "Decrypt"){
       echo "Below is your decrypted file content. </br>";
       echo rc4($key,utf8_decode($input));
     }
   }
   else if($cipher == "Simple Substitution"){
    if($todo == "Encrypt"){
      echo "Below is your encrypted file content. </br>";
      echo "Warning: Only letters will be encrypted. </br>";
      echo ssencrypt($input,$key);
    }
    else if($todo == "Decrypt"){
      echo "Below is your decrypted file content. </br>";
      echo "Warning: Only letters will be decrypted. </br>";
      echo ssdecrypt($input,$key);
    }
  }

  else if($cipher == "Double Transposition"){
    if($todo == "Encrypt"){
    	echo "Below is your encrypted file content. </br>";
      echo base64_encode(xorencrypt($input,$key));
    }
    else if($todo == "Decrypt"){
    	echo "Below is your decrypted file content. </br>";
    	echo xorencrypt(base64_decode($input),$key);
    }
  }
  else if($cipher == "DES"){
    if($todo == "Encrypt"){
      echo "Below is your encrypted file content. </br>";
      echo base64_encode(xorencrypt($input,$key));
    }
    else if($todo == "Decrypt"){
      echo "Below is your decrypted file content. </br>";
      echo xorencrypt(base64_decode($input),$key);
    }
  }
  $email = $_COOKIE["email"];

  $query = "INSERT INTO storedinfo (email,cipher,cryptionkey) VALUES ('$email','$cipher','$key')";
  $result = $connection->query($query);

  if (!$result){
    mysql_fatal_err("OOPS!", $connection);
  }

  // $query = "SELECT * FROM storedinfo";
  // $result = $connection->query($query);

  // if (!$result){
  //     mysql_fatal_error($err, $connection);
  // } 

  // $rows = $result->num_rows;

  // for($i=0; $i<$rows; $i++){
  //   $result->data_seek($i);
  //   $row = $result->fetch_array(MYSQLI_ASSOC);

  //   echo 'Email: ' .$row['email'] .'<br>';
  //   echo 'Cipher: ' .$row['cipher'] .'<br>';
  //   echo 'Key: ' .$row['cryptionkey'] .'<br>';
  //   echo 'Time: ' .$row['timestamp'] .'<br>';

  // }

}


function xorencrypt($input, $key) {
  $output = $input;

  for ($i = 0; $i < strlen($input); ++$i) {
    $output[$i] = $input[$i] ^ $key[$i % strlen($key)];
  }

  return $output;
}


function ssencrypt($input,$key){
  $alphabet = "abcdefghijklmnopqrstuvwxyz";
  $return = "";
  $length = strlen($input);
  if(strlen($key)!=strlen($alphabet)){
    return "Key must be 26 characters long to use the simple subsitution cipher.";
  }
  for($i=0;$i<$length;$i++){
    $oldInd = strpos($alphabet, strtolower($input[$i]));

    if ($oldInd !== false)
      $return .= ctype_upper($input[$i]) ? strtoupper($key[$oldInd]) : $key[$oldInd];
    else
      $return .= $input[$i];
  }

  return $return;
}

function ssdecrypt($input,$key){
  $alphabet = $key;
  $key = "abcdefghijklmnopqrstuvwxyz";
  $return = "";
  $length = strlen($input);
  if(strlen($key)!=strlen($alphabet)){
    return "Key must be 26 characters long to use the simple subsitution cipher.";
  }
  for($i=0;$i<$length;$i++){
    $oldInd = strpos($alphabet, strtolower($input[$i]));

    if ($oldInd !== false)
      $return .= ctype_upper($input[$i]) ? strtoupper($key[$oldInd]) : $key[$oldInd];
    else
      $return .= $input[$i];
  }

  return $return;

}

function rc4($key,$str){ //instead of using regular xor encryption we followed a psuedocode for this
  //it might not work perfectly but we thought it would be cool to actually attempt to implement rc4
	$s=array();
	for($i=0; $i<256; $i++){
		$s[$i]=$i;
	}
	$j = 0;
	for ($i = 0; $i < 256; $i++) {
		$j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
		$x = $s[$i];
		$s[$i] = $s[$j];
		$s[$j] = $x;
	}
	$i = 0;
	$j = 0;
	$res = '';
	for ($y = 0; $y < strlen($str); $y++) {
		$i = ($i + 1) % 256;
		$j = ($j + $s[$i]) % 256;
		$x = $s[$i];
		$s[$i] = $s[$j];
		$s[$j] = $x;
		$res .= $str[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
	}
	return $res;
} 

function mysql_fatal_err($msg, $connection){
  $msg2 = mysqli_error($connection);
  echo <<< _END
  We are sorry, but it was not possible to complete the requested task.
  The error message we got was:
  <p>$msg:$msg2</p>
  Please click the back button on your browser and try again.
  _END;
}

function sanitizeString($var) {
  $var = stripslashes($var);
  $var = strip_tags($var);
  $var = htmlentities($var);
  return $var;
}

function sanitizeMySQL($connectionection, $var) {
  $var = $connectionection->real_escape_string($var);
  $var = sanitizeString($var);
  return $var;
}

function mysql_entities_fix_string($connection, $string){
  return htmlentities(mysql_fix_string($connection, $string));
}

function mysql_fix_string($connection, $string){
  if (get_magic_quotes_gpc()) $string = stripslashes($string);
  return $connection->real_escape_string($string);
}



