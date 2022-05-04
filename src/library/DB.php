<?php
    /**
    *   class to interact with DB
    */
    class DB
    {
        private static $istance;

        /**
        *   return an istance of DB if exists otherwise will create and return it
        *   Params:
        *       @return DB - the DB istance
        */
        public static function instance()
        {
            if(!isset(self::$istance))
                self::$istance = new DB();
            return self::$istance;
        }

        private $conn;

        //constructor
        private function __construct()
        {
            $this->conn = mysqli_connect($GLOBALS["location"],$GLOBALS["username"],$GLOBALS["password"],$GLOBALS["database"]);
        }

        /**
        *   check if the certificate already exist
        *   Params: 
        *       @param string $email - the email of the parteciopant
        *       @param string $courseName - the name of the course that the participant have been participated 
        *       @return int|bool - false(not found) int(the line where is found the duplicate certificate)
        */
        public function alreadyExistsCert($email,$courseName,$line) //uses the email and the course to uniquely identify the certificate
        {
            $email = $this->cleanStr($email);
            $courseName = $this->cleanStr($courseName);
            $cert = $this->query("SELECT certificate_id FROM certificates WHERE certificate_course = '".$courseName."' && certificate_email = '".$email."';");
            $cert = mysqli_num_rows($cert);
            
            if($cert === 0)
                return false;
                
            return $line;
        }
        
        /**
        *   check if the certificate already exist
        *   Params: 
        *       @param string[] $userInfo - the certificate's data
        *       @param int $partId - id of the person to be certificate
        *       @return int|bool - true(all went well) int(id of the person already certificate)
        */
        function addCertificate($userInfo,$line)
        {
            // merges all non-mandatory fields into one separated by |
            $otherFields = "";
            foreach( $userInfo as $key => $value)
                if($key !== "DATA" && $key !== "E-MAIL" && $key !== "COGNOME" && $key !== "NOME" && $key !== "NOME CORSO" && $key !== "NOME_CORSO" && $key !== "ORE")
                    $otherFields = $otherFields.$value."|";
            
            if($otherFields !== "")
                $otherFields = substr($otherFields, 0, -1);

            $hours = $userInfo["ORE"];
            $date = $this->cleanStr($userInfo["DATA"]);
            $otherFields = $this->cleanStr($otherFields);
            $email = $this->cleanStr($userInfo["E-MAIL"]);
            $surname = $this->cleanStr($userInfo["COGNOME"]);
            $name = $this->cleanStr($userInfo["NOME"]);
            if(array_key_exists("NOME CORSO",$userInfo))
                $courseName = $this->cleanStr($userInfo["NOME CORSO"]);
            else
                $courseName = $this->cleanStr($userInfo["NOME_CORSO"]);

            $res = $this->alreadyExistsCert($email,$courseName,$line);

            if($res !== false)
                return $res;

            $this->query("INSERT INTO certificates VALUES(null,'".$name."','".$surname."','".$email."','".$courseName."','".$date."','".$hours."','".$otherFields."');");

            $certId = $this->getCertiId($email,$courseName);

            $this->createLog($certId,$this->getUserId());

            return true;
        }
        
        /**
        *   removes a certificate entry from the DB if a log id is specified it will only cancel the log entry given
        *   Params:
        *       @param int $certId - certificate to cancel 
        *       @param int $logId - optional, log id
        *       @return bool - void
        */
        function rmCertificate($certId,$logId = null)
        {
            if($logId !== null)
            {
                $this->query("DELETE FROM logs WHERE log_id = ".$logId.";");
            }
            else
            {
                $this->query("DELETE FROM logs WHERE fk_certificate_id = ".$certId.";");
                $this->query("DELETE FROM certificates WHERE certificate_id = ".$certId.";");
            }  
        }

        /**
        *   given a certificate id returns the last log id for a certificate 
        *   Params: 
        *       @param int $certId - the certificate id
        *       @return int - the last log id or if there is only one returns -1
        */
        function getLastLogForCerti($certId)
        {
            $certId = $this->cleanStr($certId."");
            $query = $this->query("SELECT log_id FROM logs WHERE fk_certificate_id = ".$certId." ORDER BY log_id DESC;");
            $logIds = mysqli_fetch_array($query,MYSQLI_NUM);
            if(mysqli_fetch_array($query) !== null) // nxet line
                return $logIds[0];
            return -1;

        }

        /**
        *   creates a log entry for the action of creating or recreating a certificate
        *   Params: 
        *       @param int $cartId - the certificate id
        *       @param int $userId - the id of the user that create the certificate
        *       @return void
        */
        function createLog($cartId,$userId)
        {
            $date = getdate();
            $date = $date["mday"]."/".$date["mon"]."/".$date["year"]." ".$date["hours"].":".$date["minutes"].":".$date["seconds"];
            $cartId = $this->cleanStr($cartId."");
            $this->query("INSERT INTO logs VALUES(null,".$cartId.",".$userId.",'".$date."');");
        }

        /**
        *   shortcut for query the DB
        *   Params: 
        *       @return mysqli_result|bool - the result of the query
        */
        function query($query) { return mysqli_query($this->conn,$query); }
        
        /**
        *   shortcut for sanitize a string
        *   Params: 
        *       @return string - sanitized string
        */
        function cleanStr($str) { return mysqli_real_escape_string($this->conn,$str); }

        /**
        *   given a participant id and a course id returns the certificate id
        *   Params: 
        *       @param string $email - the email of the parteciopant
        *       @param string $courseName - the name of the course that the participant have been participated 
        *       @return int - certificate id
        */
        private function getCertiId($email,$courseName)//uses the email and the course to uniquely identify the certificate
        {
            $certId = $this->query("SELECT certificate_id FROM certificates WHERE certificate_email = '".$email."' && certificate_course = '".$courseName."';");
            $certId = mysqli_fetch_array($certId,MYSQLI_NUM)[0];
            return $certId;
        }

        /**
        *   picks from the session the username and returns the user id
        *   Params:
        *       @return int - user id
        */
        function getUserId()
        {
            $userId = $this->query("SELECT user_id FROM users WHERE user_name = '".$this->cleanStr($_SESSION["username"])."';");
            $userId = mysqli_fetch_array($userId,MYSQLI_NUM)[0];
            return $userId;
        }

        /**
        *   returns an array of all data needed for dumping the log table
        *   Params:
        *       @return array - array of logs data
        */
        function getlogs()
        {
            $query = $this->query("SELECT certificate_name,certificate_surname,user_name,certificate_course,certificate_hours,log_date,log_id,certificate_id
            FROM logs
            INNER JOIN certificates ON certificate_id = fk_certificate_id
            INNER JOIN users ON user_id = fk_user_id ORDER BY log_id DESC;");

            $logs = array();
            while($log = mysqli_fetch_array($query))
                array_push($logs,$log);

            return $logs;
        }

        /**
        *   check if the certificate was loged more than once if it was it returns the first log id for the certificate, if not returns false
        *   Params:
        *       @param int $certId - certificate id
        *       @return int|bool - the fist log for the certificate or false(not have been logged more then once)
        */
        function certiLogAlreadyExists($certId)
        {
            $query = $this->query("SELECT log_id FROM logs WHERE fk_certificate_id = ".$certId.";");
            if(mysqli_num_rows($query) > 1)
                return mysqli_fetch_array($query,MYSQLI_NUM)[0];
            return false;
        }

        /**
        *   given login credential try to login and returns if it's logged in or not
        *   Params:
        *       @param string $username - username 
        *       @param string $password - password
        *       @return bool - resoult of the login process
        */
        function login($username,$password)
        {
            $username = $this->cleanStr($username);
            $password = $this->cleanStr($password);
            $query = $this->query("SELECT user_name,user_pwd,user_privilege FROM users WHERE user_name = '".$username."';");
            $rows = mysqli_num_rows($query);
            if($rows == 1)
            {
                $user = mysqli_fetch_assoc($query);

                $DBpass = explode(".",$user['user_pwd']);
                $password = $password.$DBpass[1];
                $hash = hash("sha256",$password);
                
                
                if($DBpass[0] === $hash)
                {
                    $_SESSION['username'] = $user["user_name"];
                    $_SESSION['privilege'] = $user["user_privilege"];
                    return true;
                }
            }
            return false;
        }

        /**
        *   given login credential and privilege tries to create a new user only by admin user, returns the status code if the creation goes wrong otherwise 
        *   Params:
        *       @param string $username - username 
        *       @param string $password - password
        *       @param string $privilege - the privilege of the user
        *       @return bool|int - int(error code) true(success)
        */
        function addUser($username,$password,$privilege)
        {
            $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
            $username = $this->cleanStr($username);
            $password = $this->cleanStr($_POST['password']);
            $privilege = $this->cleanStr($_POST['privilege']);

            $salt = bin2hex(random_bytes(10));
            $hash = hash("sha256",$password.$salt).".".$salt;
            if($privilege == "user" || $privilege == "admin")
            {
                $this->query("INSERT INTO users VALUES (NULL,'".$username."','".$hash."','".$privilege."');");
                
                if(mysqli_errno($this->conn))
                {
                    if(mysqli_errno($this->conn) === 1062) //duplicate error
                        return 1;
                    else
                        return 0;
                }

                return true;
            }

        }
    }
    

    