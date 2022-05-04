<?php
    require "load.php";
    session_start();
    if(!isset($_SESSION['username']))
    {
        header("Location: login.php");
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
    <title>cartificavo - Menu</title>
</head>

<body>
    <?php require_once ABSPATH."/UIComponents/nav.php";?>
    
    <div class="content-container">
        <div class="home-sele">
            <h1>Home</h1>
            <a href="templateWizard.php?redirect=1" class="button">crea certificati con CSV</a>
            <a href="templateWizard.php?redirect=2" class="button">crea con form</a>
                    
        </div>
    </div>

    <?php require_once ABSPATH."/UIComponents/footer.php";?>
    
    <?php
        if(isset($_GET["download"]) && $_GET["download"] !== false)
        {
            echo "<script> location.href = 'download.php?file=".$_GET["download"]."'</script>";	
        }
    ?>
</body>
</html>
