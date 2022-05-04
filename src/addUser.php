<?php
    require "load.php";
    
    if(isset($_POST["addUserSubmit"]) && ! is_action('saveAddUser'))
        exit();
        
    session_start();
    if(!isset($_SESSION['username']))
    {
        header("Location: login.php");
        exit();
    }
    else if($_SESSION['privilege'] != "admin")
    {
        header("Location: uploadData.php");
        exit();
    }

    if(isset($_POST['addUserSubmit']))
    {
        include_once "connect.php";
        $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
        $username = mysqli_real_escape_string($conn,$username);
        $password = mysqli_real_escape_string($conn,$_POST['password']);
        $privilege = mysqli_real_escape_string($conn,$_POST['privilege']);
        
        $salt = bin2hex(random_bytes(10));
        $hash = hash("sha256",$password.$salt).".".$salt;
        if($privilege == "user" || $privilege == "admin")
        {
            $query = mysqli_query($conn,"INSERT INTO users VALUES (NULL,'".$username."','".$hash."','".$privilege."');");
            if(mysqli_errno($conn))
            {
                if(mysqli_errno($conn) === 1062) //duplicate error
                {
                    header("Location: addUser.php?err=1");
                    exit();
                }

                header("Location: addUser.php?err=0"); //general error
                exit();          
            }

            header("Location: addUser.php?succ=0"); //general error
            exit();    
        }
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
    <title>cartificavo - Aggiugi user</title>
</head>
<body>
    <?php require_once ABSPATH."/UIComponents/nav.php";?>

    <div class="content-container">
        <form action="" method="POST" class="addUser add-user-form">
            <?php form_action( 'saveAddUser' ) ?>
            <h1>Aggiungi nuovo utente</h1>
            <div class="input-container">
                <input type="text" name="username" class="input-text" autocomplete="off" required> 
                <label for="username">Username</label>
            </div>
            <div class="input-container">
                <input type="password" name="password" class="input-text" autocomplete="off" required> 
                <label for="password">Password</label>
            </div>
            <h2>Permesso</h2>
            <div>
                <input type="radio" name="privilege" value="user" id="user" checked required>
                <label for="user">User</label>
            </div>
            <div>
                <input type="radio" name="privilege" value="admin" id="admin" required>
                <label for="admin">Adimn</label>
            </div>
            <input type="submit" name="addUserSubmit" class="button">
            <?php 
                if(isset($_GET['err']))
                {
                    echo '<h5 class="err">'; 
                    switch($_GET['err'])
                    {
                        case 0:
                            echo "errore";
                        break;
                        case 1:
                            echo "username già esistente";
                        break;
                    }
                }
                if(isset($_GET['succ']))
                {
                    echo '<h5 class="err" style="color: green">'; 
                    switch($_GET['succ'])
                    {
                        case 0:
                            echo "nuovo utente creato corretamente";
                        break;
                    }
                }
            ?></h5>
        </form>
    </div>

    <?php require_once ABSPATH."/UIComponents/footer.php";?>
</body>
</html>