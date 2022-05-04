<?php
    use PHPMailer\PHPMailer\PHPMailer;// namespaces for PHPmailer
    use PHPMailer\PHPMailer\Exception;

    use Endroid\QrCode\QrCode;          // namespaces for Endroid/qr-code
    use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeEnlarge;
    use Endroid\QrCode\Writer\PngWriter;
    use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

    require_once LIBRARY_PATH."/UILibrary.php";
    require_once LIBRARY_PATH."/DB.php";

    /**
    *   before the page loads checks permission,if the user is logged in,csrf token ec...
    *   and initialize things like session in the buffer with ob_start()
    *   Params: 
    *       @param array $checkCsrf - tha array that contains the data for check the csrf token index 0(submit btn name) 1(label of csrf token)
    *       @param int|bool $checkTemp - if not false contain the page number to redirect in case the template does not exist
    *       @param string|bool $checkTmpPDF - if not false contain the page name to redirect in case tmpPDF folder of the user does not exist
    *       @param bool $clearCache - indicates to clear the cache or not
    *       @param bool $privCheck - if true checks if the user have the admin privilege
    *       @return void
    */
    function doChecks($checkCsrf = [],$checkTemp = false,$checkTmpPDF = false,$clearCache = false,$privCheck = false)
    {
        ob_start();
        session_start();

        if(!isset($_SESSION['username']))
        {
            header("Location: login.php");
            exit();
        }

        if($privCheck === true)
            if($_SESSION['privilege'] != "admin")
            {
                header("Location: index.php");
                exit();
            }

        if($checkCsrf !== [])
            if(isset($_POST[$checkCsrf[0]]) && !is_action($checkCsrf[1]))
                exit();
        
        if($checkTemp !== false)
            if(!file_exists(ABSPATH."/templates/".$_SESSION['username'].".jpeg"))   
            {
                header("Location: templateWizard.php?redirect=".$checkTemp);
                exit();
            }

        if($checkTmpPDF !== false)
            if(!is_dir("tmpPDF/".$_SESSION['username']))
            {
                header("Location: ".$checkTmpPDF);
                exit();
            }
        
        if($clearCache === true)
            header("Cache-Control: no-cache, must-revalidate"); //clear the cache
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

    }

    /**
    *   appeands a value for each element in the array
    *   Params: 
    *       @param array $array - array where to appeand the value
    *       @param string $ele - element to appeand
    *       @return error|array - the the array with the appended value
    */
    function appeandForEachValue($array,$ele) 
    {
        try
        {
            for($i = 0;$i < count($array);$i++)
                $array[$i] = $array[$i].$ele;

            return $array;
        }
        catch(Exception $e)
        {
            return $e;
        }
        
    }

   /**
    *   given a CSV file check if it is valid and return an array of fields
    *   Params: 
    *       @param string $file - path of the file
    *       @param string $name - the name of the file
    *       @param string $type - the type of the file 
    *       @return string|array - string if there are errors else return an array of field 
    */
    function checkCSV($file,$name = "",$type = "text/csv")
    {
        if($name === "")
            $name = $file;

        $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        
        $ret = true;
        $checkedField = 0;
        $fieldsName = [];
        $linea = 1; // it contain the line where the program is

        if(!is_readable($file))
            $ret = "0";
        else if(!is_numeric(strpos($name,'.csv')))
            $ret = "1";
        else if(!in_array($type,$csvMimes))
            $ret = "2";
        else if(empty($file))
            $ret = "3";
        else
        {
            $separator = findSeparator($file);
            if(($f = fopen($file,"r")) !== null)
            {
                $data = fgetcsv($f,10000,$separator);
                //checks if there at leat the mandatory fields
                foreach($data as $field)
                {
                    $field = trim($field);
                    $field = strtoupper($field);
                    if($field == "NOME" || $field == "COGNOME" || $field == "E-MAIL" || $field == "NOME CORSO" || $field == "CORSO")
                    {
                        if($field == "CORSO")
                            $field = "NOME CORSO";
                        array_push($fieldsName,$field);
                        $checkedField++; 
                    }
                    else
                    {
                        if($field == "NUMERO ORE")
                            $field = "ORE";
                        array_push($fieldsName,$field);//if not a mandatory field it will stores anyway                     
                    }

                }
                if($checkedField != 4)
                        $ret = "8,"."0";
                
                while(($data = fgetcsv($f,10000,$separator)) && $ret === true)
                {
                    // $fieldNo it's the nuber of the field (0 index-dased)
                    for($fieldNo = 0; $fieldNo < count($data); $fieldNo++)
                    {
                        switch($fieldsName[$fieldNo])
                        {
                            case "E-MAIL":
                                if(!filter_var(trim($data[$fieldNo]), FILTER_VALIDATE_EMAIL))
                                    $ret = "4,".$linea;
                            break;
                            case "NUMERO ORE":
                            case "ORE":
                                if($data[$fieldNo] < 1 && !empty($data[$fieldNo]))
                                    $ret = "5,".$linea;
                            break;
                            default:
                                if(strlen($data[$fieldNo]) > 70)
                                    $ret = "7,".$linea;
                            break;
                        }
                    }
                    $linea++;
                }
            }
        }
        
        
        if($ret === true)
            return $fieldsName; 
        else 
            return $ret;
    }

    /**
    *   given a file path it serch for the separator, it do so by dividing with evry possible 
    *   separator, the separator that can divide the line in most part will be the separator of the CSV
    *   Params: 
    *       @param string $file - path of the file
    *       @return string - the separator
    */
    function findSeparator($file)
    {
        $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

        $handle = fopen($file, "r");
        $firstLine = fgets($handle);
        fclose($handle); 
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }
    
        return array_search(max($delimiters), $delimiters);
    }

    /**
    *   parse a CSV file 
    *   Params: 
    *       @param string $file - path of the file
    *       @param array $fieldsName - an array that contain all fields it's optional
    *       @param bool $skipFirstLine - if it's true skip the first line, in case of header that we don't want to parse
    *       @return array - a matrix where every line of it is a line of the CSV file
    */
    function parseCSV($file,$fieldsName = null,$skipFirstLine = false,$hiddenFields = [])
    {
        $allData = $fieldsName !== null ? array() : [];

        if(($f = fopen($file,"r")) !== null)
        {
            $separator = findSeparator($file);
            if($skipFirstLine === true)//read the first line to skip it
                $data = fgetcsv($f,10000,$separator);

            while($data = fgetcsv($f,10000,$separator))
            {
                if($fieldsName !== null)//it means that if there is the fieldsName array means that is a CSV for a certificate
                {
                    $line = array();
                    for($i = 0;$i < count($fieldsName);$i++)// uses one variable called i to goes through two array in a parallel way
                    {
                        if(in_array($fieldsName[$i],$hiddenFields))// if the $fieldsName[$i] is in the array of the fields name to hide it will skip the entaire process of parsing for that particular field
                            continue;
                        
                        if($fieldsName[$i] === "ORE" || $fieldsName[$i] === "DATA")
                        {
                            $dataToInsert = "";
                            if($fieldsName[$i] === "DATA")
                            { 
                                $dataToInsert = dateToString("d/m/Y");
                            }
                            else
                                $dataToInsert = 25;

                            if(array_key_exists($i, $data)) // in case it's an invalid index
                            {
                                if(empty($data[$i]))
                                    $data[$i] = $dataToInsert;                      
                            }
                            else
                                array_push($data,trim(htmlspecialchars($dataToInsert, ENT_QUOTES, 'UTF-8')));
                        }

                        $data[$i] = trim($data[$i]);

                        if($fieldsName[$i] == "NOME")
                            $data[$i] = htmlspecialchars(strtoupper(substr($data[$i],0,1)).strtolower(substr($data[$i],1)), ENT_QUOTES, 'UTF-8'); 
                        else if($fieldsName[$i] == "COGNOME")
                            $data[$i] = htmlspecialchars(strtolower($data[$i]), ENT_QUOTES, 'UTF-8');

                        $line += array($fieldsName[$i] => $data[$i]);
                    }
                    if(!in_array("DATA",$fieldsName))
                    {
                        $date = dateToString("d/m/Y");
                        $line += array("DATA" => $date);
                    }
                    if(!in_array("ORE",$fieldsName))
                    {
                        $line += array("ORE" => 25);
                    }
                    
                    array_push($allData, $line);
                }
                else
                {
                    array_push($allData, $data);
                }
                    
            }
        }
        
        return $allData;
    }

    /**
    *   calculate the x space needed to position the box of the text in the middle 
    *   Params: 
    *       @param GDImage $image - the GD object where the function works on
    *       @param float $fontSize - the font size
    *       @param float $angle - the angle of the text box
    *       @param string $font - file path of the font file (extension .ttf)
    *       @param string $text - the content inside the text box
    *       @return float - the x spacing for centrate the text box
    */
    function getXspcing($image,$fontSize,$angle,$font,$text)
    {
        $temLen = ImageSX($image);// image size X
        $box = imagettfbbox($fontSize,$angle,$font,$text);
        $Xmax = abs($box[0] - $box[2]);
        $Xspacing = intval(($temLen - $Xmax)/2);

        return $Xspacing;
    }   

    /**
    *   given the path of the image(png) and ideal pdf path, it convert the png image into a pdf with A4 size
    *   Params: 
    *       @param string $pathImg - png image path
    *       @param string $pathPdf - ideal pdf path
    *       @return bool - the status of the operation false(failed), true(successed)
    */
    function pngToPdf($pathImg,$pathPdf)
    {
        $image = $pathImg;
        $pdf = new FPDF("L","mm","A4");
        $pdf->AddPage("L",[297,210]);
        $pdf->Image($image,0,0, 297,210,'JPEG');
        $pdf->Output("F",$pathPdf);

        return true;
    } 

    /**
    *   print the text given on to the png image with other details specified in the other parameters
    *   Params: 
    *       @param GDImage $image - the GD object where the function works on
    *       @param float $fontSize - the font size
    *       @param float $y - y offset from the top
    *       @param float $x - x offset from the left but if the param centered is true the offset starts from the centre
    *       @param int $color - GD color identifier for the text
    *       @param string $font - file path of the font file (extension .ttf)
    *       @param bool $centered - true: text centred, false: non centred
    *       @return bool - the status of the operation false(failed), true(successed)
    */
    function printOnImg($image, $fontSize, $y, $x, $color, $font, $text, $centered = false)
    {
        if($centered)
            $Xspacing = getXspcing($image,$fontSize,0,$font,$text) + $x;
        else
            $Xspacing = $x;

        imagettftext($image, $fontSize, 0, $Xspacing, $y, $color, $font, $text);

        return true;
    } 

    /**
    *   given an array of data it prints all on the image
    *   Params: 
    *       @param array $userInfo - the array of data
    *       @param string $template - file path of the base image where all data are printed on to
    *       @param int|string $line - it indicate in what line of the CSV file we are, if the data derived from 
    *                                 the form so there is only one set of data the parameter if not specified goes to the default value that is "singolo"
    *       @return void - it create the image in the folder tmpPDF/
    */
    function printAllOnImg($userInfo,$template,$line = "singolo")
    {
        // Set Path to Font File
        $fontPathText = realpath('assets/fonts/Overpass-Black.ttf');
        //$fontPathTitle = realpath('assets/fonts/Pacifico-Regular.ttf');

        $font = $fontPathText;

        $image = imagecreatefromjpeg($template);
        
        // Allocate A Color For The Text
        $black = imagecolorallocate($image,0, 0, 0);
        $red = imagecolorallocate($image,255, 0, 0);

        $optField = 1; 
        foreach($userInfo as $key => $value)
        {
            switch($key)
            {
                case "NOME": //nome & cognome
                    $tmpStr = '';
                    foreach(explode(' ', $value) as $name) {
                        $tmpStr = $tmpStr . (strtoupper(substr($name,0,1)).strtolower(substr($name,1)).' ');
                    }

                    $value = htmlspecialchars(substr($tmpStr, 0, -1), ENT_QUOTES, 'UTF-8'); 

                    if(strlen($value." ".strtoupper($userInfo["COGNOME"])) > 30)
                        printOnImg($image, 90 - (strlen($value." ".strtoupper($userInfo["COGNOME"])) - 8), 540 , 0, $red, $font, $value." ".strtoupper($userInfo["COGNOME"]),true);
                    else    
                        printOnImg($image, 90, 540 , 0, $red, $font, $value." ".strtoupper($userInfo["COGNOME"]),true);
                break;
                case "NOME_CORSO": //nome corso
                case "NOME CORSO":
                    if(strlen($value) > 31)
                        printOnImg($image, 80 - (strlen($value) - 20), 740 , 0, $black, $font, $value,true);
                    else
                        printOnImg($image, 80, 740 , 0, $black, $font, $value,true);
                break;
                case "DATA": //data di attestazione
                    printOnImg($image, 30, ImageSY($image) - 234, 226, $black, $font, "Torino, ".$value);
                break;
                case "ORE": //ore conseguite
                    printOnImg($image, 35, 860, 0, $black, $font,"Per un totale di"."                      ".$value." ore",true);
                break;
                default: //altri campi
                    if($key !== "COGNOME" && $key !== "E-MAIL")
                    {
                        printOnImg($image, 35, 860+(75*($optField)) , 0, $black, $font, $value,true);
                        $optField ++;
                    }
                break;
            }
        }

        $sign = createSign(strtoupper($userInfo['NOME'].$userInfo['COGNOME'].$userInfo[array_key_exists('NOME CORSO',$userInfo) ? "NOME CORSO" : "NOME_CORSO"].dateToString("d/m/Y")));
        
        $url = baseUrl()."verifyCert.php?".
        "nome=".rtrim(strtr(base64_encode($userInfo['NOME']), '+/', '-_'), '=').
        "&cognome=".rtrim(strtr(base64_encode($userInfo['COGNOME']), '+/', '-_'), '=').
        "&corso=".rtrim(strtr(base64_encode($userInfo[array_key_exists('NOME CORSO',$userInfo) ? "NOME CORSO" : "NOME_CORSO"]), '+/', '-_'), '=').
        "&data=".dateToString("d/m/Y").
        "&sign=".$sign;

        $qr = imagecreatefrompng(generateQR($url,"tmpPDF/".$_SESSION["username"]."/".$line.'.png', 244, 3));

        imagecopy($image, $qr, 5 ,5 , 0 , 0, imageSX($qr), imageSY($qr));//imageSY($image) - imageSY($qr) - 5

        imagejpeg($image, ABSPATH."/tmpPDF/".$_SESSION["username"]."/".$line.".jpeg",60);
           
        imagedestroy($qr);
        imagedestroy($image);

        unlink(ABSPATH."/tmpPDF/".$_SESSION["username"]."/".$line.'.png');
    } 

    /** 
    *   takes a matrix of data or an array along with a template to write the data on and a username, it also takes care to insert the certificates into the DB, logging it, send it and if specified archive it
    *   if we want to recreate a certificate the flag parameter logsOnly need to be true and the function will take the ids array and start to log the cretificates with that ids in the log table
    *   Params: 
    *       @param array $data - the matrix or array of data
    *       @param string $template - file path of the base image where all data are printed on to
    *       @param string $username - the user that create the PDF/PDFs
    *       @param int[] $ids - list of ids of certificates to recreate
    *       @param bool $logsOnly - tells if the array of ids is make of log ids
    *       @param bool $archive - the to the function to archive the certificate or not 
    *       @return void/int - may return a status code in case of an error
    */
    function createPDF($data,$template,$username,$ids = [],$logsOnly = false,$archive = false)
    {
        require_once LIBRARY_PATH."/FPDF/fpdf.php";

        if(!mkdir(ABSPATH."/tmpPDF/".$username)) 
            return -1;

        if(!file_exists(ABSPATH."/mails/".$username.".txt"))
            copy(ABSPATH."/mails/default.txt",ABSPATH."/mails/".$username.".txt");

        if(is_array($data[ array_keys($data)[0] ]))//checks if there is only one person to certificate or more than one
        {
            $line = 0;
            foreach($data as $userInfo) // goes through all the lines of the data package
            {
                printAllOnImg($userInfo,$template,$line);

                pngToPdf(ABSPATH."/tmpPDF/".$username."/".$line.".jpeg","tmpPDF/".$username."/".$line.".pdf");

                $res = true;
                $alreadyExist = db()->alreadyExistsCert($userInfo["E-MAIL"],$userInfo["NOME CORSO"],$line);
                
                if($alreadyExist === false || $logsOnly)
                    $res = sendMail($userInfo["E-MAIL"], "tmpPDF/".$username."/".$line.".pdf", $userInfo["NOME"],$userInfo["COGNOME"], $userInfo["NOME CORSO"],$archive ? true : false);
                else
                {
                    $res = false;
                    echo "<p>Il certificato di <b>".$userInfo["NOME"]." ".$userInfo["COGNOME"]."</b> con email <b>".$userInfo["E-MAIL"]."</b> esiste già.</p>";
                }

                if($res)
                {
                    //DB interactions
                    if(!$logsOnly)
                    {
                        db()->addCertificate($userInfo,$line);
                        createLog("tmpPDF/".$username."/".$line.".pdf",$_SESSION["username"]);
                        echo "<p>Il certificato di <b>".$userInfo["NOME"]." ".$userInfo["COGNOME"]."</b> è stato mandato all'email <b>".$userInfo["E-MAIL"]."</b> con successo alle <b>".dateToString()."</b>.</p>";
                    }
                    else
                    {
                        $userId = db()->getUserId();
                        db()->createLog($ids[$line],$userId);
                        createLog("tmpPDF/".$username."/".$line.".pdf",$_SESSION["username"]);
                        echo "<p>Il certificato ricreato di <b>".$userInfo["NOME"]." ".$userInfo["COGNOME"]."</b> è stato mandato all'email <b>".$userInfo["E-MAIL"]."</b> con successo alle <b>".dateToString()."</b>.</p>";
                    }
                }

                unlink(ABSPATH."/tmpPDF/".$username."/".$line.".pdf");
                unlink(ABSPATH."/tmpPDF/".$username."/".$line.".jpeg");

                $line++;
            }
        }
        else
        {
            printAllOnImg($data,$template);

            pngToPdf(ABSPATH."/tmpPDF/".$username."/singolo.jpeg",ABSPATH."/tmpPDF/".$username."/singolo.pdf");
            
            $res = sendMail($data["E-MAIL"],ABSPATH."/tmpPDF/".$_SESSION["username"]."/singolo.pdf",$data["NOME"],$data["COGNOME"],$data["NOME_CORSO"],$archive ? true : false);
        
            if($res)
            {
                $resCreation = db()->addCertificate($data,1);

                if(!is_bool($resCreation))
                    return $resCreation; 
                
                createLog(ABSPATH."/tmpPDF/".$_SESSION["username"]."/singolo.pdf",$_SESSION["username"],$data["E-MAIL"]);
            }
        }

        removeDir(ABSPATH."/tmpPDF/".$username);
    }

    /**
    *   sends an email with the certificate as attachments, the function needs the email, the certificate to send, the username of the user that create the pdf 
    *   Params: 
    *       @param string $email - the email destination
    *       @param string $certi - the file path of the certificate to send
    *       @param string $name - name of the person who get certificated
    *       @param string $surname - surname of the person who get certificated
    *       @param string $courseName - the course attended
    *       @param string $archive - tells if the certificate need to be archived
    *       @return bool - the status of the operation false(failed), true(successed)
    */
    function sendMail($email,$certi,$name,$surname,$courseName,$archive = false)
    {
        require_once LIBRARY_PATH.'/PHPmailer/src/Exception.php';
        require_once LIBRARY_PATH.'/PHPmailer/src/PHPMailer.php';
        require_once LIBRARY_PATH.'/PHPmailer/src/SMTP.php';

        $fullName = $name." ".$surname;
        
        $data = parseKeyValue(ABSPATH."/mails/".$_SESSION["username"].".txt",1);

        replacePlaceHolder($data,$courseName,$fullName,createSign(strtoupper($name.$surname.$courseName.dateToString("d/m/Y"))));

        $mail = new PHPMailer;
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host =  $GLOBALS['SMTPHost'];                  // Specify main and backup SMTP servers 
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $GLOBALS['SMTPUsername'];           // SMTP username
        $mail->Password = $GLOBALS['SMTPPassword'];           // SMTP password
        $mail->SMTPSecure = 'TLS';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to 465 (SSL) o 587 (TLS)
        
        $mail->setFrom($data["EMAILMIT"],$data["NOMEMIT"]);
        $mail->addAddress($email, $fullName);                     // Add a recipient
        if($archive)
            $mail->addBCC($data["ARCHIVIAZIONE"], "Archiviazione");
        
        $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
        $mail->addAttachment($certi,$fullName.'.pdf');            // Optional name
        $mail->isHTML(true);                                  // Set email format to HTML
        
        $mail->Subject = $data["TITOLO"];
        $mail->Body    = $data["TESTO"].'<br> <b>'.$data["FIRMA"].'</b>';
        
        if(!$mail->send()) {
            return false;
        } else {
            return true;
        }

        return true;
    }
  
    /**
    *   joins all PDFs in the array passed as argument and output the joiined PDF in the directory selected
    *   Params: 
    *       @param array $arrayPdf - array of PDFs
    *       @param string $path - the file path for the outputed joiined PDF
    *       @return void
    */
    function unisciPDF($arrayPdf,$path)
    {
        ini_set('memory_limit', '3024M');
        require_once LIBRARY_PATH.'/FPDF/FPDF_Merge.php';

        $merge = new FPDF_Merge();
        foreach($arrayPdf as $pdf)
            $merge->add($pdf);

        $merge->output($path);
    }

    /**
    *   removes a directory with any things inside 
    *   Params: 
    *       @param string $dir - the directory to remove
    *       @return bool - the status of the operation false(failed), true(successed)
    */
    function removeDir($dir) //remove directory with file inside
    {
        if (is_dir($dir))
            $dir_handle = opendir($dir);
        if (!$dir_handle)
            return false;
        while($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (!is_dir($dir."/".$file))
                        unlink($dir."/".$file);
                    else
                        removeDir($dir.'/'.$file);
                }
        }
        closedir($dir_handle);
        rmdir($dir);
        return true;
    }

    /**
    *   makes a force download of the specified file 
    *   Params: 
    *       @param string $filePath - file path of the file that need to be downloaded
    *       @return void
    */
    function forceDownload($filePath)
    {
        clearstatcache();
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
        header('Content-Length: '.filesize($filePath));
        header('Pragma: public');
        ob_clean();
        flush();

        readfile($filePath,true);
    }

    /**
    *   creates a template where the program can write on
    *   Params: 
    *       @param string $username - the username of the user that crate the PDF 
    *       @param string $logo - file path of the logo image selected
    *       @param string $frame - file path of the frame image selected   
    *       @param string $signature - file path of the signature image selected   
    *       @param string $background - file path of the background image
    *       @param string $cockade - file path of the cockade image
    *       @return bool - the status of the operation false(failed), true(successed)
    */
    function createTemplate($username,$logo,$frame,$signature,$background,$cockade)
    {
        $newTemplate = imagecreatefrompng($background);
        $frame = imagecreatefrompng($frame);
        $logo = imagecreatefrompng($logo);
        $signature = imagecreatefrompng($signature);
        $cockade = imagecreatefrompng($cockade);
        
        imagecopy($newTemplate,$frame,0,0,0,0,2200,1700);
        imagedestroy($frame);
        imagecopy($newTemplate,$logo, (imageSX($newTemplate)/2) - (imageSX($logo)/2) ,330 - imageSY($logo),0,0,imageSX($logo),imageSY($logo));
        imagedestroy($logo);
        imagecopy($newTemplate,$signature, imageSX($newTemplate) - (210 + imageSX($signature)) ,imageSY($newTemplate) - (230 + imageSY($signature)),0,0,imageSX($signature),imageSY($signature));
        imagedestroy($signature);
        imagecopy($newTemplate,$cockade, (imageSX($newTemplate)/2) - (imageSX($cockade)/2) ,imageSY($newTemplate) - 350,0,0,imageSX($cockade),imageSY($cockade));
        imagedestroy($cockade);


        imagejpeg($newTemplate, ABSPATH."/templates/".$username.".jpeg",50);
        imagedestroy($newTemplate);
        
        return true;
    }

    /**
    *   write in the template the fixed phases 
    *   Params: 
    *       @param string $username - the username of the user that crate the PDF 
    *       @param string $text1 - the first phases
    *       @param string $text2 - the second phases  
    *       @param string $text3 - the third phases  
    *       @return bool - the status of the operation false(failed), true(successed)
    */
    function fixedtextTemplate($username,$text1,$text2,$text3)
    {
        $newTemplate = imagecreatefromjpeg(ABSPATH."/templates/".$username.".jpeg");

        // Set Path to Font File
        $fontPathText = realpath(ABSPATH.'/assets/fonts/Overpass-Black.ttf');
        $fontPathTitle = realpath(ABSPATH.'/assets/fonts/Pacifico-Regular.ttf');

        $font = $fontPathText;

        // Allocate A Color For The Text
        $black = imagecolorallocate($newTemplate,0, 0, 0);
        $red = imagecolorallocate($newTemplate,255, 0, 0);

        printOnImg($newTemplate, 30, 420 , 2, $black, $font, $text1, true);

        printOnImg($newTemplate, 30, 615 , 0, $black, $font, $text2 ,true);

        printOnImg($newTemplate, 30, ImageSY($newTemplate) - 370 , 0, $black, $font, $text3 ,true);

        $ret = imagejpeg($newTemplate, ABSPATH."/templates/".$username.".jpeg",40);

        imagedestroy($newTemplate);

        return $ret;
    }
    
    /**
    *   make a new entry on the log file, putting username file with random name and timestamp
    *   Params: 
    *       @param string $filePath - file path of the file 
    *       @param string $name - the username of the user that crate the PDF  
    *       @return void
    */
    function createLog($filePath,$name)
    {
        $path = ABSPATH."/log/".bin2hex(random_bytes(10)).".pdf";
        copy($filePath,$path);
        $f = fopen("log/log.txt","a");
        $date = dateToString();
        fwrite($f,$name."  ".$path."  ".$date." \n");
        fclose($f);
    }

    function dateToString($pattern = "d/m/Y H:i:s")
    {
        $now = new DateTime('now', new DateTimeZone('Europe/Rome'));
        return $now->format($pattern);
    }

    /**
    *   return an istance of DB but if already exists will return the existing one 
    *   Params:
    *       @return DB - the istance of DB
    */
    function db() { return DB::instance(); }

    /**
    *   checks if the data provided by the certification form are correct
    *   Params: 
    *       @param array $postArray - the form data
    *       @param array &$data - where the function collect the form data after checks if it's correct  
    *       @param array $invalidKeys - an array of invalid keys of the post array
    *       @return int|bool - int(error code) false(no errors)
    */
    function checkCertiForm($postArray,&$data,$invalidKeys)
    {
        $data = array();
        $err = false;
        foreach($postArray as $key => $value)
        {
            if(!in_array($key,$invalidKeys))
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
                    case "ORE":
                        if($value < 1)
                            $err = 2;
                    break;
                    default:
                        if(strlen($value) > 70)
                            $err = 3;                       
                    break;
                }
                if(!$err)
                {
                    $data += array($key => $value);
                }    
            }
        }

        return $err;
    }

    /**
    *   given the path of a file that contains key-value information, try to parse it 
    *   Params: 
    *       @param string $pathFile - the path of the file
    *       @param int $option - specify a parse option 0(normal text) 1(html version of the text)
    *       @return array - data parsed from the file
    */
    function parseKeyValue($pathFile,$option = 0)
    {
        $f = fopen($pathFile,"r");
        $data = array();

        switch($option)
        {
            case 0:
                $newLine = "\n";
            break;
            case 1:
                $newLine = " <br> ";
            break;
        }

        while($linea = fgets($f))
        {
            $splitedLine = explode("=",$linea,2);
            $data += array( $splitedLine[0] => str_replace("[Invio]",$newLine,$splitedLine[1]));
        }
    
        fclose($f);

        return $data;
    }

    /**
    *   check the email form data 
    *   Params: 
    *       @param array $mail - array of email information coming from the form 
    *       @return boolean - int(error number) true(no error)
    */
    function checkMail($mail)
    {
        $err = true;
        
        foreach($mail as $key => $value)
        {
            $key = strtoupper($key);
            switch($key)
            {
                case "EMAILMIT":
                case "ARCHIVIAZIONE":
                    if(!filter_var($value, FILTER_VALIDATE_EMAIL))
                        $err = 1;
                break;
                case "TESTO":
                    if(strlen($value) <= 0 || strlen($value) >= 6000 || $value == false)
                        $err = 2;                       
                break;

                default:
                    if(strlen($value) <= 0 || strlen($value) >= 200 || $value == false)
                        $err = 3;                       
                break;  
            }
        }

        return $err;
    }

    /**
    *   prints into a file the the email data passed as argument in a key value form
    *   Params: 
    *       @param array $mail - the email data to print oin the file
    *       @return void
    */
    function printMailToFile($mail)
    {
        $f = fopen(ABSPATH."/mails/".$_SESSION["username"].".txt","w");

        foreach($mail as $key => $value)
        {
            $key = strtoupper($key);

            fwrite($f,$key."=".str_replace("\r","",str_replace("\n","[Invio]",$value))."\n");
        }

        fclose($f);
    }

    /**
    *   filters the post array from the form by returning the array with only the keys specified ad argument
    *   Params: 
    *       @param array $postArray - the entire array before the filtering
    *       @param array $validKeys - the array of keys that we want in the final array
    *       @return array - the filtered array
    */
    function filterPostArray($postArray,$validKeys)
    {
        $finalArray = array();
        
        foreach($postArray as $key => $value)
        {
            if(in_array($key,$validKeys))
                $finalArray += array( $key => $value );
        }

        return $finalArray;
    }

    /**
    *   replace in the data array the placeholder with the real data
    *   Params: 
    *       @param array &$data - the data with the placeholder
    *       @param array $realData - the array of the real data to swap with the placeholder
    *       @return void
    */
    function replacePlaceHolder(&$data,...$realData)
    {
        $dataLength = count($data);
        $keys = array_keys($data);
        for($i = 0; $i < $dataLength; $i++)
        {
            $data[$keys[$i]] = count($realData) > 0 ? preg_replace("/\[\s*[N,n]\s*[O,o]\s*[M,m]\s*[E,e]\s*[C,c]\s*[O,o]\s*[R,r]\s*[S,s]\s*[O,o]\s*\]/i", $realData[0], $data[$keys[$i]]) : $data[$keys[$i]]; // replace [nomeCorso]
            $data[$keys[$i]] = count($realData) > 1 ? preg_replace("/\[\s*[N,n]\s*[O,o]\s*[M,m]\s*[E,e]\s*\]/i", $realData[1], $data[$keys[$i]]) : $data[$keys[$i]];  // replace [nome]
            $data[$keys[$i]] = count($realData) > 2 ? preg_replace("/\[\s*[F,f]\s*[I,i]\s*[R,r]\s*[M,m]\s*\s*[A,a]\]/i", $realData[2], $data[$keys[$i]]) : $data[$keys[$i]]; // replace [firma]
        }
    }

    /**
    *   given a message as argument, creates his sign and encode it in base64.
    *   Using Ed25519 key pair
    *   Params: 
    *       @param string $message - message thet we want the sign back
    *       @return string message's sign in base64
    */
    function createSign($message)
    {
        $privateKey = fopen(PRIVATE_KEY_PATH,'r');
        $privateKey = fread($privateKey, SODIUM_CRYPTO_SIGN_SECRETKEYBYTES);
                    
        $sign = sodium_crypto_sign(hash("sha256",$message),$privateKey);
        $sign = str_replace(hash("sha256",$message),'',$sign);
        return base64_encode($sign);
    }

    /**
    *   given a message and his sign as arguments, checks if the message is signed by us 
    *   Params: 
    *       @param string $sign - the sign of the message in base64
    *       @param string $message - the message
    *       @return boolean the result of the check
    */
    function checkSign($sign,$message)
    {
        $publicKey = fopen(PUBLIC_KEY_PATH,'r');
        $publicKey = fread($publicKey,SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);
        return sodium_crypto_sign_open(base64_decode($sign).hash("sha256",$message),$publicKey) !== false;
    }

    /**
    *   generates a QRcode by passing the message, the destination of the QRcode once created 
    *   and the dimension with the border   
    *   Params: 
    *       @param string $msg - message to put ine the QRcode
    *       @param string $dest - destination where the QRcode will be put once created
    *       @param string $size - the sioze of the QRcode
    *       @param string $margin - the white margin around the QRcode
    *       @return string the absolute path of newly creted QRcode 
    */
    function generateQR($msg,$dest,$size = 300,$margin = 10)
    {
        require_once LIBRARY_PATH.'/Endroid_QR/vendor/autoload.php';

        $writer = new PngWriter();
        $qrCode = QrCode::create($msg)
        ->setSize($size)
        ->setMargin($margin)
        ->setRoundBlockSizeMode(new RoundBlockSizeModeEnlarge());
    
        $result = $writer->write($qrCode);
        $result->saveToFile(ABSPATH.'/'.$dest);

        return ABSPATH.'/'.$dest;
    }

    /**
    *   builds the base URL of the site and return it
    *   Params: 
    *       @return string base URL of the site
    */
    function baseUrl() {
        $url = null;
    
        // as default it's just the current URL
        if( !$url ) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    
            $url = $protocol . $_SERVER['HTTP_HOST'] . innerJoinURL($_SERVER['REQUEST_URI'],str_replace('\\', '/', dirname(__DIR__)));
        }
    
        return trim($url);
    }

    /**
    *   given two URLs returns a URL with the only parts in common between the two URLs
    *   Params: 
    *       @return string the URL with only the parts in common
    */
    function innerJoinURL($str1,$str2) {
        $str1Splitted = explode("/", $str1);
        $str2Splitted = explode("/", $str2);

        return "/".implode("/",array_intersect($str1Splitted, $str2Splitted))."/";
    }