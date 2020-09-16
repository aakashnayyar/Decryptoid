<?php
session_start();
require_once 'login.php';

$conn = new mysqli($hn,$un,$pw,$db);

if ($conn->connect_error){
  mysql_fatal_error($err, $conn);
}

if (isset($_COOKIE['userid']) && isset($_COOKIE['username'])) {
  require 'cipher.php';
} else {
  echo <<<_END
  <h1>Decryptoid</h1>
  <form enctype="multipart/form-data" action="decryptoid.php" method="POST">
  <input type="email" required autocomplete="off" name="email" placeholder="Email"> <br>
  <input type="password" required autocomplete="off" name="password" placeholder="Password"> <br>
  <button class="button button-block" name="login" >Log In</button> <br>
  </form>
  <form enctype="multipart/form-data" action="decryptoid.php" method="POST">
  <input type="email" required autocomplete="off" name="email" placeholder="Email"> <br>
  <input type="text" required autocomplete="off" name="username" placeholder="Username"> <br>
  <input type="password" required autocomplete="off" name="password" placeholder="Password"> <br>
  <button class="button button-block" name="register" >Create A New account</button>
  </form>
  _END;


  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
      require 'enteredlogin.php';
    }
    if (isset($_POST['register'])) {
      require 'register.php';
    }
  }

}

$query = "SELECT * FROM finalusers";
$result = $conn->query($query);

if (!$result){
  mysql_fatal_error("OOPS!", $conn);
} 

$result->close();
$conn->close();


function mysql_fatal_error($msg, $conn){
  $msg2 = mysqli_error($conn);
  echo <<< _END
  We are sorry, but it was not possible to complete the requested task.
  The error message we got was:
  <p>$msg:$msg2</p>
  Please click the back button on your browser and try again.
  _END;
}

?>