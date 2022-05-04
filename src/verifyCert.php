<?php
    require "load.php";
    require_once LIBRARY_PATH."/library.php";
    
    if(isset($_POST["submitVeriCert"]) && !is_action("saveVeriCert"))
        exit();

    if(isset($_POST["submitVeriCert"]))
    {
        $certData = filterPostArray($_POST,["nomeCert","cognomeCert","corsoCert","dataCert"]);
        $sign = $_POST["codVal"];
 
    }

    if(isset($_GET["sign"])
    && isset($_GET["nome"])
    && isset($_GET["cognome"])
    && isset($_GET["corso"])
    && isset($_GET["data"]))
    {
        $certData = array("nomeCert" => base64_decode($_GET["nome"]),
                          "cognomeCert" => base64_decode($_GET["cognome"]), 
                          "corsoCert" =>  base64_decode($_GET["corso"]), 
                          "dataCert" =>  $_GET["data"] );
        $sign = str_replace(" ","+",$_GET["sign"]);
    }

    if(isset($certData))
        $validCert = checkSign($sign, strtoupper($certData["nomeCert"].$certData["cognomeCert"].$certData["corsoCert"].$certData["dataCert"]));

    printHead('Certificavo - Verifica certificato',
              [ 'assets/css/style.css' ],
              [],
              false);

?>
    
    <?php
        if(!isset($validCert))
        {
    ?>
        <form action="verifyCert.php" method="POST" class="page-container">
            <?php form_action( 'saveVeriCert' ) ?>
            <h1>Verifica certificato</h1>

            <div class = "inline-item width80">
                <?php 
                    inputText('Nome sul certificato', 'nomeCert', 'text', ['width30']);
                    inputText('Cognome sul certificato', 'cognomeCert', 'text', ['width60']);
                ?>
            </div>

            <div class = "inline-item width80">
                <?php 
                    inputText('Corso sul certificato', 'corsoCert', 'text', ['width60']);
                    inputText('Data sul certificato', 'dataCert', 'text', ['width30'], 'gg/mm/aaaa');
                ?>
            </div>

            <?php inputText('Codice Validità', 'codVal', 'text', ['width80']); ?>

            <input type="submit" name="submitVeriCert" value="Controlla Validà" class="button">
            <h5 class="err"><?php 
                if(isset($_GET['err']))
                {
                    switch($_GET['err'])
                    {
                        
                    }
                }
                
            ?></h5>
        </form>
    <?php
        }
        else
        {
            ?>
                <div class="page-container">
                    <h1><?php echo ($validCert) ? "Il certificato è valido" : "Il certificato non è valido" ?></h1>
                    
                    <?php
                        if($validCert)
                        {
                    ?>
                        <h4>certificato di </h4>
                        <h3><?php echo $certData["nomeCert"] ?>&nbsp;&nbsp;<?php echo $certData["cognomeCert"] ?></h3>

                        <div class = "inline-item ">
                            <h5>per il corso: &nbsp;&nbsp;&nbsp;</h5>
                            <h3><?php echo $certData["corsoCert"] ?></h3>
                            <h5 class="margin-left-30">in data: &nbsp;&nbsp;&nbsp;</h5>
                            <h3><?php echo $certData["dataCert"] ?></h3>
                        </div>
                        
                    <?php
                        }
                    ?>
                    <a href="verifyCert.php" class="button">torna al validatore</a>
                </div>
            <?php
        }
    ?>
    
<?php printFooter(); ?>