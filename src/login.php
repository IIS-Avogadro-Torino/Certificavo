<?php
    require "load.php";
    if(isset($_POST["login_submit"]) && !is_action('saveLogin'))
        exit();

    ob_start();
    session_start();
    if(isset($_SESSION['username']))
    {
        header("Location: index.php");
        exit();
    }

    if(isset($_POST['login_submit']))
    {
        require_once "connect.php";
        $username = mysqli_real_escape_string($conn,$_POST['username']);
        $password = mysqli_real_escape_string($conn,$_POST['password']);
        $query = mysqli_query($conn,"SELECT username,password,privilege FROM users WHERE username = '".$username."';");
        $rows = mysqli_num_rows($query);
        if($rows == 1)
        {
            $user = mysqli_fetch_assoc($query);

            $DBpass = explode(".",$user['password']);
            $password = $password.$DBpass[1];
            $hash = hash("sha256",$password);
            
            
            if($DBpass[0] === $hash)
            {
                session_start();
                $_SESSION['username'] = $user["username"];
                $_SESSION['privilege'] = $user["privilege"];
                header("Location: index.php");
                exit();
            }
            else
            {
                header("Location: login.php?err=0");
                exit();
            }
            
        }
        header("Location: login.php?err=0");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets/img/avologo.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>cartificavo - Login</title>
</head>
<body>
    <div class="body-cover"></div>

    <div class="login-content">
        <form action="login.php" method="POST" class="login-form">
            <?php form_action( 'saveLogin' ) ?>
            <img src="assets/img/logo-certificavo.png" class="login-form-img">
            <div class="input-container">
                <input type="text" name="username" class="input-text" autocomplete="off" required> 
                <label for="name">Username</label>
            </div>
            <div class="input-container">
                <input type="password" name="password" class="input-text" autocomplete="off" required> 
                <label for="password">Password</label>
            </div>
            <input type="submit" value="LOGIN" class="button" name="login_submit" value="AGGIUNGI">
            <h5 class="err"><?php 
                if(isset($_GET['err']))
                {
                    switch($_GET['err'])
                    {
                        case 0:
                            echo "password o username sbagliati";
                        break;
                    }
                }
                
            ?></h5>
        </form>
    </div>
</body>
</html>
