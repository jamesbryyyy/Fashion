<?php
session_start();

$con = mysqli_connect("localhost", "root", "", "fashion");

if (!$con) {
  die("Connection failed");
}

if (isset($_POST["btnlogin"])) {

  $email = $_POST["email"];
  $password = $_POST["password"];

  $q = mysqli_query($con, "
        SELECT *
        FROM users
        WHERE email='$email'
        AND password='$password'
    ");

  if (mysqli_num_rows($q) > 0) {

    $row = mysqli_fetch_array($q);

    /*
        CREATE SESSION
        */
    $_SESSION["client_id"] = $row["id"];
    $_SESSION["client_name"] = $row["fullname"];

    /*
        REDIRECT BACK
        */
    if (isset($_GET["redirect"])) {

      header("Location: " . $_GET["redirect"]);
    } else {

      header("Location: shop.php");
    }

    exit();
  } else {

    $error = "Invalid Email or Password";
  }
}
?>

<!DOCTYPE html>
<html>

<head>

  <title>Client Login</title>

  <style>
    body {
      font-family: Arial;
      background: #f5f5f5;
    }

    .login-box {
      width: 350px;
      background: white;
      margin: 100px auto;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0px 0px 10px #ccc;
    }

    input {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      margin-bottom: 15px;
    }

    button {
      width: 100%;
      padding: 12px;
      background: black;
      color: white;
      border: none;
      cursor: pointer;
    }

    button:hover {
      background: #444;
    }

    .error {
      color: red;
      margin-bottom: 15px;
    }
  </style>

</head>

<body>

  <div class="login-box">

    <h2>Client Login</h2>

    <?php
    if (isset($error)) {
    ?>
      <div class="error">
        <?php echo $error; ?>
      </div>
    <?php } ?>

    <form method="POST">

      <input
        type="email"
        name="email"
        placeholder="Enter Email"
        required>

      <input
        type="password"
        name="password"
        placeholder="Enter Password"
        required>

      <button type="submit" name="btnlogin">
        Login
      </button>

    </form>

  </div>

</body>

</html>