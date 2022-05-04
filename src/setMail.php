<?php
    require "load.php";
    require_once LIBRARY_PATH."/library.php";

    doChecks(["submitSetMail","saveSetMail"]);
    
    if(!isset($_GET['redirect']))
    {
        header("Location: index.php");
        exit();
    }

    if(isset($_POST["submitSetMail"]))
    {
        $validKeys = ["nomeMit","emailMit","titolo","testo","firma","archiviazione"];

        $postEmailData = filterPostArray($_POST,$validKeys);

        $checkRes = checkMail($postEmailData);
        if($checkRes !== true)
        {
            header("Location: setMail.php?err=".$checkRes."&redirect=".$_GET["redirect"]);
            exit(); 
        }

        printMailToFile($postEmailData);

        header("Location: templateWizard.php?redirect=".$_GET['redirect']);

        exit(); 
    }

    printHead('Certificavo - Configura Mail',
              [ 'assets/css/style.css' ],
              [ 'assets/js/functions.js', 'assets/js/pageScript.js' ]);
?>
    
    <form action="setMail.php?redirect=<?php echo $_GET['redirect']; ?>" method="POST" class="page-container">
        <?php form_action( 'saveSetMail' ) ?>
        <?php backArrow();?>
        <h1>Imposta Mail</h1>

        <?php
            $eMailData = parseKeyValue(ABSPATH."/mails/default.txt");
        ?>
        <div class = "inline-item width80">
            <?php 
                inputText('Mittente nome', 'nomeMit', 'text', ['width30'], $eMailData["NOMEMIT"]);
                inputText('Mittente e-mail', 'emailMit', 'text', ['width60'], $eMailData["EMAILMIT"]);
            ?>
        </div>
        <?php 
                inputText('Titolo', 'titolo', 'text', ['width80'], $eMailData["TITOLO"]);
                textArea('Testo', 'testo', ['width80'], $eMailData["TESTO"]);
                inputText('Firma', 'firma', 'text', ['width80'], $eMailData["FIRMA"]);
                inputText('E-mail Archiviazione', 'archiviazione', 'text', ['width80'], $eMailData["ARCHIVIAZIONE"]);
        ?>
        <input type="submit" name="submitSetMail" value="Crea" class="button">
        <h5 class="err"><?php 
            if(isset($_GET['err']))
            {
                switch($_GET['err'])
                {
                    case 1:
                        echo "E-mail di destinazione o di archiviazione sbagliate";    
                    break;
                    case 2:
                        echo "testo non è valido per esserlo deve minore di 6000 caratteri e maggiore di 0 non deve essere nullo";    
                    break;
                    case 3:
                        echo "Un campo non è valido poiche supera i 200 caratteri o nullo ";    
                    break;
                }
            }
            
        ?></h5>
    </form>

<?php printFooter(); ?>