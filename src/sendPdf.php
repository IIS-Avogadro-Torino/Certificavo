<?php
    require "load.php";

    if(isset($_POST["sendCertiSub"]) && ! is_action('saveSendPDF'))
        exit();

    ob_start();
    session_start();
    if(!isset($_SESSION['username']))
    {
        header("Location: login.php");
        exit();
    }
    else if(!is_dir("tmpPDF/".$_SESSION['username']))
    {
        header("Location: uploadData.php");
        exit();
    }

    header("Cache-Control: no-cache, must-revalidate"); //cleare the cache
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

    require LIBRARY_PATH."/library.php";

    if(isset($_POST['sendCertiSub']))
    {
        $filteredData = [];
        $i = 0;
        if(isset($_POST['cerToSend']))
        {
            foreach($_POST['cerToSend'] as $indexForData)
            {
                if($indexForData < count($data))
                {
                    $alreadyInArray = array_search($indexForData,$_POST['cerToSend']);
                    if($alreadyInArray == $i)
                    {
                        $status = sendMail($data[intval($indexForData)][2],$data[intval($indexForData)][1].".pdf",$data[intval($indexForData)][0]);
                        array_push($filteredData,$data[intval($indexForData)]);
                    }
                }

                $i++;
            }
            $path = "tmpPDF/".$_SESSION['username']."/cartificati.pdf";
            unisciPDF( appeandForEachValue(array_column($filteredData,1),".pdf"), ABSPATH."/tmpPDF/".$_SESSION['username']."/cartificati.pdf");
            createLog(ABSPATH."/tmpPDF/".$_SESSION['username']."/cartificati.pdf",$_SESSION["username"]);

            unlink(ABSPATH."/templates/".$_SESSION["username"].".png");
            
            if(isset($_GET["download"]))
            {
                header("Location: index.php?download=2");
                exit();
            }                
            else
            {
                removeDir(ABSPATH."/tmpPDF/".$_SESSION["username"]);
                header("Location: index.php");
                exit();
            }
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
    <title>cartificavo - Send PDF</title>
</head>
<body>
    <?php require_once ABSPATH."/UIComponents/nav.php";?>
    
    <div class="content-container">
        <form action="sendPdf.php<?php if(isset($_GET['download'])) echo "?download=true"?>" method="POST" enctype="multipart/form-data" class="upload-data-form">
            <?php form_action( 'saveSendPDF' ) ?>
            <span class="back-arrow"><i class="fas fa-chevron-left"></i> Indietro</span>
            <h1>certificazioni</h1>
            <div class="send-certificate-container">
            <?php
                $data = parseCSV(ABSPATH."/tmpPDF/".$_SESSION['username']."/data.csv");
                if(!is_string($data))
                {
                    $i = 0;
                    foreach($data as $line)
                    {
                        echo '<div class="send-certificate certificate-selection">';
                            echo '<img src="'.$line[1].'.png" >';
                            echo '<h4>'.$line[0].'</h4>';
                            echo '<input type="checkbox" name="cerToSend[]" value="'.$i.'" checked>';
                        echo '</div>';
                        $i++;
                    }
                }
            ?>
            </div>

            <input type="submit" name="sendCertiSub" value="Invia" class="button">
        </form>
    </div>
    <?php require_once ABSPATH."/UIComponents/footer.php";?>
    <script>
        document.querySelector(".back-arrow").onclick = () =>{
            location.href = "uploadData.php";
        }
    </script>
</body>
</html>
