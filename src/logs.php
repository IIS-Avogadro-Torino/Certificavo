<?php
    require "load.php";
    require_once LIBRARY_PATH."/library.php";
    doChecks();
    $logs = db()->getlogs();

    printHead('Certificavo - Cronologia',
              [ 'assets/css/style.css' ],
              [ 'assets/js/functions.js', 'assets/js/pageScript.js', 'assets/js/searchBar.js'  ]);
?>

    <div class="page-container page-container--nogap">
        <?php backArrow();?>
        <h1>Cronologia</h1>

        <hr class="line">

        <input type="text" class="search-bar" placeholder="Cerca">

        <ul class="logs">
            <?php
                foreach($logs as $log)
                {

                    $alreadyExists = db()->certiLogAlreadyExists($log["certificate_id"]);
                    
                    if($alreadyExists !== false && $alreadyExists !== $log["log_id"])
                        $phrase = "ha certificato di nuovo";
                    else
                        $phrase = "ha certificato";


                    if($log["certificate_hours"] == 1)
                        $log["certificate_hours"] = $log["certificate_hours"]." ora";
                    else
                        $log["certificate_hours"] = $log["certificate_hours"]." ore";
                ?>
                    <li> 
                        <?php echo "<span class='bold'>".$log["user_name"]."</span> ".$phrase." <span class='bold'>".$log["certificate_name"]." ".(strtoupper($log["certificate_surname"][0]).substr($log["certificate_surname"],1))."</span> al corso <span class='bold'>".$log["certificate_course"]."</span> per un totale di <span class='bold'>".$log["certificate_hours"]."</span> il <span class='bold'>".$log["log_date"]."</span>" ?>
                    </li>
                <?php
                }
            ?>
        </ul>
        
    </div>
    
<?php printFooter(); ?>
