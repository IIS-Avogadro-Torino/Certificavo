<?php
    require "load.php";

    if(isset($_POST["templateCreation"]) && ! is_action('saveTemplateWizard'))
        exit();
        
    ob_start();
    session_start();
    if(!isset($_SESSION['username']))
    {
        header("Location: login.php");
        exit();
    }
    
    if(!isset($_GET['redirect']))
    {
        header("Location: index.php");
        exit();
    }

    /*if(!file_exists(ABSPATH."/templates/".$_SESSION['username'].".png"))   
    {
	    unlink(ABSPATH."/templates/".$_SESSION['username'].".png");
        exit();
    }*/

    if(isset($_POST["templateCreation"]))
    {
        require LIBRARY_PATH."/library.php";

        if(strlen($_POST["frase1"]) > 70 || strlen($_POST["frase2"]) > 70 ||strlen($_POST["frase3"]) > 70)
        {
            header("Location: templateWizard.php?err=1&&redirect=".$_GET["redirect"]);
            exit();
        }

        $ret = createTemplate($_SESSION["username"],$_POST["radioLoghi"],$_POST["radioCornici"],$_POST["radioFirme"],ABSPATH."/templates/components/sfondo.png",ABSPATH."/templates/components/coccarda.png");
        $ret = fixedtextTemplate($_SESSION["username"],$_POST["frase1"],$_POST["frase2"],$_POST["frase3"]);
        if($ret === true)
        {
            if($_GET['redirect'] == 1)
                header("Location: uploadData.php");
            else if($_GET['redirect'] == 2)
                header("Location: uploadDataForm.php");
            else
                header("Location: index.php");

            exit();
        }
        else
            header("Location: templateWizard.php?err=0&&redirect=".$_GET["redirect"]);
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
    <title>cartificavo - Wizard templates</title>
</head>
<body>

    <?php require_once ABSPATH."/UIComponents/nav.php";?>
    
    <div class="content-container">
        <form action="templateWizard.php?redirect=<?php echo $_GET['redirect']; ?>" method="POST" class="wizard-pick-component-container">
            <?php form_action( 'saveTemplateWizard' ) ?>
            <span class="back-arrow"><i class="fas fa-chevron-left"></i> Indietro</span>
            <h1>Scegli parti template</h1>

            <img src="templates/esempio.png" class="preview">

            <h2>Metti frasi fisse</h2>
            <div class="input-container">
                    <input type="text" name="frase1" class="input-text" autocomplete="off" required> 
                    <label for="frase1">Frase 1</label>
            </div>
            <div class="input-container">
                    <input type="text" name="frase2" class="input-text" autocomplete="off" required> 
                    <label for="frase2">Frase 2</label>
            </div>
            <div class="input-container">
                    <input type="text" name="frase3" class="input-text" autocomplete="off" required> 
                    <label for="frase3">Frase 3</label>
            </div>
            <?php
                /*
                *   from here you can add new components, 
                *   you need to write into the array the name of the folder that have to corrispond to the name of the component in lowercase.
                */
                $components = ["cornici","loghi","firme"];

                foreach($components as $component)
                {
                    $nameWithCaps = strtoupper(substr($component,0,1)).substr($component,1);
            ?>
                   <h2><?php echo $nameWithCaps; ?></h2>
                    <div class="wizard-pick-component">
                        <?php 
                            $files = glob("templates/components/".$component."/*.png");
                            foreach($files as $file)
                            {
                        ?>
                             <div class="certificate-selection">
                                <img src="<?php echo $file; ?>">
                                <input type="radio" name="radio<?php echo $nameWithCaps?>" value="<?php echo __DIR__."/".$file; ?>" required>
                            </div>
                        <?php         
                            }
                        ?>
                    </div>
            <?php
                }

            ?>

            <input type="submit" name="templateCreation" value="Crea" class="button">
            <h5 class="err"><?php 
                if(isset($_GET['err']))
                {
                    switch($_GET['err'])
                    {
                        case 0:
                            echo "c'è stato un erroe nel processo di creazione";    
                        break;
                        case 1:
                            echo "una delle frasi e troppo lunghe deve essere minore di 70 caratteri";    
                        break;
                    }
                }
                
            ?></h5>
        </form>
    </div>
    <?php require_once ABSPATH."/UIComponents/footer.php";?>
    <script>
        document.querySelector(".back-arrow").onclick = () =>{
            location.href = "index.php";
        }
    </script>
</body>
</html>