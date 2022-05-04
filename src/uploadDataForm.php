<?php
    require "load.php";

    if(isset($_POST["uploadDataFormSubmit"]) && ! is_action('saveUploadDataForm'))
        exit();

    ob_start();
    session_start();
    if(!isset($_SESSION['username']))
    {
        header("Location: login.php");
        exit();
    }
    if(!file_exists("templates/".$_SESSION['username'].".png"))
    {
        header("Location: templateWizard.php?redirect=2");
        exit();
    }
    
    require LIBRARY_PATH."/library.php";

    if(is_dir(ABSPATH."/tmpPDF/".$_SESSION['username']))
    {
        removeDir(ABSPATH."/tmpPDF/".$_SESSION['username']);
    }

    header("Cache-Control: no-cache, must-revalidate"); //cleare the cache
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

    if(isset($_POST['uploadDataFormSubmit']))
    {
        $err = false;
        $data = array();
        foreach($_POST as $key => $value)
        {
            if($key !== "radio_certificate" && $key !=="uploadDataFormSubmit" && $key != "download")
            {
                switch($key)
                {
                    case "E-MAIL":
                        if(!filter_var($value, FILTER_VALIDATE_EMAIL))
                            $err = 1;
                    break;
                    case "NOME":
                        if(preg_match('/[A-Z]/',substr($value,0,1)) == 0 || preg_match('/[A-Z]/',substr($value,1)))
                            $value = strtoupper(substr($value,0,1)).strtolower(substr($value,1));                        
                    break;
                    case "COGNOME":
                        if(preg_match('/[A-Z]/',$value))
                            $value = strtolower($value);                      
                    break;
                    default:
                        if(strlen($value) > 70)
                            $err = 2;                       
                    break;
                }
                if(!$err)
                {
                    $data += array($key => $value);
                }
                    
            }
        }

        if(!$err)
        {
            $date = getdate();
            $date = $date["mday"]."/".$date["mon"]."/".$date["year"];
            $data += array("DATA" => $date);

            $ret = createPDF($data,ABSPATH."/templates/".$_SESSION['username'].".png",$_SESSION["username"]);
            
            if($ret === 403)
            {
                header("Location: uploadDataForm.php?err=".$ret);
                exit();
            }
           
            sendMail($data["E-MAIL"],ABSPATH."/tmpPDF/".$_SESSION["username"]."/singolo.pdf",$data["NOME"]." ".$data["COGNOME"]);
           
	        createLog(ABSPATH."/tmpPDF/".$_SESSION["username"]."/singolo.pdf",$_SESSION["username"]);
            
            unlink(ABSPATH."/templates/".$_SESSION["username"].".png");
            
            if(isset($_POST["download"]))
            {
                
                header("Location: index.php?download=1");
            }                
            else
            {
                removeDir(ABSPATH."/tmpPDF/".$_SESSION["username"]);
                header("Location: index.php");
            }
        }
        else
        {
            header("Location: uploadDataForm.php?err=".$err);
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
    <title>cartificavo - Upload data con form</title>
</head>
<body>
    <?php require_once ABSPATH."/UIComponents/nav.php";?>
    
    <div class="content-container">
        <form action="uploadDataForm.php" method="POST" enctype="multipart/form-data" class="upload-data-form">
            <?php form_action( 'saveUploadDataForm' ) ?>
            <span class="back-arrow"><i class="fas fa-chevron-left"></i> Indietro</span>
            <h1>Modulo per creare PDF</h1>

            <img src="templates/<?php echo $_SESSION['username']; ?>.png" class="preview">

            <div class="manual-upload-form">
                <div class="input-container">
                    <input type="text" name="E-MAIL" class="input-text" autocomplete="off" required> 
                    <label for="email">Email</label>
                </div>
                <div class="orizz" style="width: 500px;">
                    <div class="input-container">
                        <input type="text" name="NOME" class="input-text" autocomplete="off" required> 
                        <label for="nome">Nome</label>
                    </div>
                    <div class="input-container">
                        <input type="text" name="COGNOME" class="input-text" autocomplete="off" required> 
                        <label for="cognome">Cognome</label>
                    </div>
                </div>
                <div class="input-container">
                    <input type="text" name="NOME CORSO" class="input-text" autocomplete="off" required> 
                    <label for="nomeCorso">Nome corso</label>
                </div>
                <div class="orizz" style="width: 500px;">
                    <div class="input-container">
                        <input type="text" name="CAMPO1" class="input-text" autocomplete="off" required> 
                        <label for="dataCorso">Campo 1</label>
                    </div>
                    <div class="input-container">
                        <input type="text" name="CAMPO2" class="input-text" autocomplete="off" required> 
                        <label for="oreCorso">Campo 2</label>
                    </div>
                </div>
                <div class="input-container">
                    <input type="text" name="CAMPO3" class="input-text" autocomplete="off" required> 
                    <label for="oreCorso">Campo 3</label>
                </div>
           </div>
           <button class="button" id="addField" type="button" style="margin-bottom: 20px">+</button>

            <div class="check-download">
                <input type="checkbox" name="download" value="true">
                <label for="download">scarica pdf</label>
            </div>
            <input type="submit" name="uploadDataFormSubmit" value="Crea" class="button">
            <h5 class="err"><?php 
                if(isset($_GET['err']))
                {
                    switch($_GET['err'])
                    {
                        case 1:
                            echo "email non valida ci deve essere un punto, un chioggiola e non devono esserci spazzi";
                        break;
                        case 2:
                            echo "un campo e più lungo di 70 caratteri";
                        break;
                        case 403:
                            echo "la cartella non è stata creata";
                        break;
                    }
                }
                
            ?></h5>
        </form>
    </div>
    
    <?php require_once ABSPATH."/UIComponents/footer.php";?>
    <script>
        document.querySelector(".back-arrow").onclick = () =>{
            location.href = "templateWizard.php?redirect=2";
        }
    </script>
</body>
</html>