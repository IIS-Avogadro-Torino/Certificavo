<?php
    require "load.php";
    require_once LIBRARY_PATH."/library.php";

    doChecks(["uploadDataSubmit","saveReCertiByDB"],4);

    if(isset($_POST["reCertibyDBSubmit"]))
    {
        if(is_dir(ABSPATH."/tmpPDF/".$_SESSION['username']))
            removeDir(ABSPATH."/tmpPDF/".$_SESSION['username']);

        if(empty($_POST["certificates"]))
        {
            header("Location: reCertiByDB.php?err=0");
            die();
        }

        $data = array();
        $certiIds = [];
       
        foreach($_POST["certificates"] as $certiId)
        {   
            $certiId = db()->cleanStr($certiId."");

            $certInfo = db()->query("SELECT certificate_name,certificate_surname,certificate_email,certificate_date,certificate_course,certificate_hours,other_fields
            FROM certificates 
            WHERE certificate_id = ".$certiId.";");

            $certInfo = mysqli_fetch_assoc($certInfo);

            //creates the data package manually by merging all queries result for the certificates selected
            $certData = array(
                "NOME" => htmlspecialchars($certInfo["certificate_name"], ENT_QUOTES, 'UTF-8'),
                "COGNOME" => htmlspecialchars($certInfo["certificate_surname"], ENT_QUOTES, 'UTF-8'),
                "E-MAIL" => htmlspecialchars($certInfo["certificate_email"], ENT_QUOTES, 'UTF-8'),
                "DATA" => htmlspecialchars($certInfo["certificate_date"], ENT_QUOTES, 'UTF-8'),
                "NOME CORSO" => htmlspecialchars($certInfo["certificate_course"], ENT_QUOTES, 'UTF-8'),
                "ORE" => htmlspecialchars($certInfo["certificate_hours"], ENT_QUOTES, 'UTF-8')
            );

            //takes the string form the DB for other fields and palces in the array of data
            $otherFields = explode("|",$certInfo["other_fields"]);
            for($i = 0;$i < count($otherFields);$i++)
                $certData += array("ALTRO".$i => htmlspecialchars($otherFields[$i], ENT_QUOTES, 'UTF-8'));

            array_push($data,$certData);
            array_push($certiIds,$certiId);

        }
    
    }

    printHead('Certificavo - Ricertifica persone',
              [ 'assets/css/style.css' ], 
              [ 'assets/js/pageScript.js', 'assets/js/searchBar.js' ]);
    
?>

<?php 
        if(isset($_POST['reCertibyDBSubmit'])) 
        {
            echo "<div  class='page-container page-container--nogap'>";
                echo "<h1>Stato certificati</h1>";
                createPDF($data,ABSPATH."/templates/".$_SESSION['username'].".jpeg",$_SESSION['username'],$certiIds,true,
                            array_search("archivia",array_keys($_POST)) !== false && $_POST["archivia"] === "true" ? true : false);
                echo "<h4>Finito</h4>";
                echo "<a href='index.php' class='button'>Torna alla Home</a>";
            echo "</div>";
        }
        else
        {
    ?>
        <form action="reCertiByDB.php" method="POST" enctype="multipart/form-data" class="page-container">
            <?php form_action( 'saveReCertiByDB' ) ?>
            <?php backArrow();?>
            <h1>Scegli persone da ricertitficare</h1>
        
            <img src="templates/<?php echo $_SESSION['username']; ?>.jpeg" class="width60">

            <hr class="line">

            <input type="text" class="search-bar" placeholder="Cerca">

            <?php
                $certificates = db()->query("SELECT certificate_id,certificate_name,certificate_surname,certificate_hours,certificate_course,certificate_email
                FROM certificates ORDER BY certificate_id DESC;");
            ?>
            <ul class="people-list">
                <?php
                    while($certificate = mysqli_fetch_array($certificates))
                    {
                    ?>
                        <li> 
                            <h3><?php echo $certificate["certificate_name"]." ".$certificate["certificate_surname"] ?></h3> 
                            <h4>eMail: <?php echo $certificate["certificate_email"]?></h4>
                            <h4>corso: <?php echo $certificate["certificate_course"]?></h4>
                            <h4>ore: <?php echo $certificate["certificate_hours"]?></h4>
                            <input type="checkbox" name="certificates[]" value="<?php echo $certificate["certificate_id"] ?>">
                        </li>
                    <?php
                    }
                ?>
            </ul>
            
            <div class="archive">
                <input type="checkbox" name="archivia" value="true">
                <label for="download">Archivia Certificati</label>
            </div>

            <input type="submit" name="reCertibyDBSubmit" class="button" value="Crea">
            <h5 class="err"><?php 
                if(isset($_GET['err']))
                {
                    switch($_GET['err'])
                    {
                        case 0:
                            echo "selezionare almeno un certificato";
                        break;
                    }
                }
                
            ?></h5>
        </form>
    <?php
        }
    ?> 

<?php printFooter(); ?>>
