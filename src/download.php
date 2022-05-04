<?php
    require "load.php";
    
    session_start();
    if(!isset($_SESSION['username']))
    {
        header("Location: login.php");
        exit();
    }

    require LIBRARY_PATH."/library.php";

    if($_GET["file"] == 1)
        forceDownload(ABSPATH."/tmpPDF/".$_SESSION["username"]."/singolo.pdf");
    else
        forceDownload(ABSPATH."/tmpPDF/".$_SESSION["username"]."/cartificati.pdf");

    removeDir(ABSPATH."/tmpPDF/".$_SESSION["username"]);

?>