<?php
    require "load.php";

    if(isset($_POST["uploadDataSubmit"]) && ! is_action('saveUploadData'))
        exit();

    ob_start();
    session_start();
    if(!isset($_SESSION['username']))
    {
        header("Location: login.php");
        exit();
    }
    if(!file_exists(ABSPATH."/templates/".$_SESSION['username'].".png"))   
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

    if(isset($_POST['uploadDataSubmit']))
    {
        $fields = checkCSV($_FILES['flieCsv']['tmp_name'],$_FILES['flieCsv']['name'],$_FILES['flieCsv']['type']);

        if(is_string($fields))
        {
            $fields = explode(",",$fields);
            if(count($fields) > 1)
                header("Location: uploadData.php?err=".$fields[0]."&linea=".$fields[1]);
            else
                header("Location: uploadData.php?err=".$fields[0]);

            exit();
        }

        $data = parseCSV($_FILES['flieCsv']['tmp_name'],$fields,true);

        $ret = createPDF($data,ABSPATH."/templates/".$_SESSION['username'].".png",$_SESSION['username']);
        
        if($ret === 403)
        {
            header("Location: index.php?err=".$ret);
            exit();
        }

        if(isset($_POST["download"]))
            header("Location: sendPdf.php?download=true");
        else
            header("Location: sendPdf.php");
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
    <title>cartificavo - Upload data</title>
</head>
<body>
    <?php require_once ABSPATH."/UIComponents/nav.php";?>
    
    <div class="content-container">
        <form action="uploadData.php" method="POST" enctype="multipart/form-data" class="upload-data-form">
            <?php form_action( 'saveUploadData' ) ?>
            <span class="back-arrow"><i class="fas fa-chevron-left"></i> Indietro</span>
            <h1>Scegli template certificato</h1>
           
            <img src="templates/<?php echo $_SESSION['username']; ?>.png" class="preview">

            <div class="upload-certificate">
                <button type="button" class="button" id="fakeUploadButton">Carica file csv</button>
                <h3 id="fileName"></h3>
                <input type="file" style="display: none;" accept=".csv" id="uploadButton" name="flieCsv">
            </div>
            
            <div class="check-download">
                <input type="checkbox" name="download" value="true">
                <label for="download">scarica pdf</label>
            </div>

            <input type="submit" name="uploadDataSubmit" value="Crea" class="button">
            <h5 class="err"><?php 
                if(isset($_GET['err']))
                {
                    switch($_GET['err'])
                    {
                        case 0:
                            echo "il file non c'è o non si può leggere";
                        break;
                        case 1:
                            echo "non è una estensione il file csv";
                        break;
                        case 2:
                            echo "non è tipo accettato";
                        break;
                        case 3:
                            echo "il file è vuoto";
                        break;
                        case 4:
                            echo "email non valida a linea: ".$_GET['linea']." ci deve essere un punto, un chioggiola e non devono esserci spazzi";
                        break;
                        case 5:
                            echo "nome non valido a linea: ".$_GET['linea']." ci deve essere solo la prima lettera maiuscola il resto minuscolo";
                        break;
                        case 6:
                            echo "cognome non valido a linea: ".$_GET['linea']." deve essere tutto minusolo";
                        break;
                        case 7:
                            echo "un campo e più lungo di 70 caratteri a linea: ".$_GET['linea'];
                        break;
                        case 8:
                            echo "intestazione sbagliata a linea: 1, cambi obligatori: E-mail, Nome, Cognome, Nome corso, Data";
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
            location.href = "templateWizard.php?redirect=1";
        }
        
    </script>
</body>
</html>
