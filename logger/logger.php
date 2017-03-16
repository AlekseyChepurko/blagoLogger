<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 14.03.2017
 * Time: 11:22
 */

namespace blago;
use Mysqli;

require_once 'DBSettings.php';
require_once 'MailerSettings.php';
class logger
{
    function __construct()
    {

    }
//constants
    // DB parametrs
    const servername = DBSettings::servername;
    const dbname = DBSettings::dbname;
    const username = DBSettings::username;
    const password = DBSettings::password;

    // Mailer parametrs
    const mailTo = MailerSettings::mailTo;
    const subject = MailerSettings::subject;
    const message = MailerSettings::message;
    const mailFrom = MailerSettings::mailFrom;

    const logFileName = "usersDataLog.csv";
    const logFilePath = "../logs/".logger::logFileName;

    // saves data to DB
    public static function saveData(){
        session_start();
        $sessionId = session_id();


        // Connect to DB
        $db = new Mysqli(logger::servername, logger::username, logger::password, logger::dbname);

        // Check connection
        if ($db->connect_error) {
//            throw new \Exception("could noy cpnnect to DB");
            die("Connection failed: " . $db->connect_error);
        }
        //check the tatble exists
        // if does not -> create
        if ($result = $db->query("SHOW TABLES LIKE 'logs'")) {
            if($result->num_rows !== 1) {
                $createTableQuery = "CREATE TABLE logs (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, session_id VARCHAR(60))";
                if (!$db->query($createTableQuery) === true)
                    return http_response_code(424);
            }
        }
        else{
            return http_response_code(500);
        }

        if ( strlen($_POST[inputs])== 0)
            throw new \Exception("inputs is empty");

        $explodedInputs = explode("&",urldecode($_POST[inputs]));
        $inputs = [];

        foreach ($explodedInputs as $input){
            $arrayIndex = substr( $input, 0, strpos($input,'=') );

            // check if column of input exists
            // if does not -> add
            $result = $db->query("SHOW COLUMNS FROM `logs` LIKE '".$arrayIndex."'");
            $columnExists = (mysqli_num_rows($result))?TRUE:FALSE;
            if(!$columnExists) {
                $db->query("ALTER TABLE logs ADD ".$arrayIndex." VARCHAR(60)");
            }

            $arrayValue = substr( $input, strpos($input,'=')+1, strlen($input) );
            if($arrayValue)
                $inputs[$arrayIndex] = addslashes($arrayValue);
        }

        $selectBySession = $db->query("SELECT session_id FROM logs WHERE session_id='".$sessionId."'");



    // make queries for insert/update
        if( $selectBySession->num_rows === 0 ){

        // make keys and according values for sql query for insert/update
            $values = "";
            $keys = "";
            foreach ($inputs as $key => $value){
                $values = $values."'".$value."',";
                $keys = $keys.$key.",";
            }

            $values = rtrim($values, ',');
            $keys = rtrim($keys, ',');
        // end make

            $insertQuery = "INSERT INTO logs (session_id,".$keys.") VALUES ('".$sessionId."',".$values.")";

            if (!$db->query($insertQuery))
                return http_response_code(500);
        }
        else{
            $updateQuery = "UPDATE logs SET ";

            foreach ($inputs as $key => $value) {
                $updateQuery = $updateQuery.$key."='".$value."',";
            }

            $updateQuery = rtrim($updateQuery,',');
            $updateQuery = $updateQuery." WHERE session_id='".$sessionId."'";
            if (!$db->query($updateQuery))
                return http_response_code(500);

        }
    // end make


        return http_response_code(200);

    }

    // writes all records from DB to file ../logs/usersDataLog.csv
    public static function writeData(){

        // Connect to DB
        $db = new Mysqli(logger::servername, logger::username, logger::password, logger::dbname);

        // Check connection
        if ($db->connect_error) {
//            throw new \Exception("could noy cpnnect to DB");
            die("Connection failed: " . $db->connect_error);
        }
        //check the tatble exists
        // if does not -> create
        if ($result = $db->query("SHOW TABLES LIKE 'logs'")) {
            if($result->num_rows !== 1) {
                header("HTTP/1.0 Not Found");
            }
        }
        else{
            return http_response_code(500);
        }

        if (!$usersData = $db->query("SELECT * FROM logs")->fetch_all())
            return http_response_code(500);


        if (!is_dir("../logs"))
            mkdir("../logs");

        $logFile = fopen(logger::logFilePath, "w");

        foreach ($usersData as $line){
            fputcsv($logFile, $line);
        }

        fclose($logFile);

        return http_response_code(200);

    }

    // sends email with usersDataLog.csv according to the data in MailerSettings.php
    public static function sendData()
    {
        logger::writeData();
        $filePath = logger::logFilePath;
        $fileName = logger::logFileName;

        $mailto = logger::mailTo;
        $subject = logger::subject;
        $message = logger::message;

        $content = file_get_contents($filePath);
        $content = chunk_split(base64_encode($content));

        // a random hash will be necessary to send mixed content
        $separator = md5(time());

        // carriage return type (RFC)
        $eol = "\r\n";

        // main header (multipart mandatory)
        $headers = "From: ". logger::mailFrom . $eol;
        $headers .= "MIME-Version: 1.0" . $eol;
        $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
        $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
        $headers .= "This is a MIME encoded message." . $eol;

        // message
        $body = "--" . $separator . $eol;
        $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
        $body .= "Content-Transfer-Encoding: 8bit" . $eol;
        $body .= $message . $eol;

        // attachment
        $body .= "--" . $separator . $eol;
        $body .= "Content-Type: application/octet-stream; name=\"" . $fileName . "\"" . $eol;
        $body .= "Content-Transfer-Encoding: base64" . $eol;
        $body .= "Content-Disposition: attachment" . $eol;
        $body .= $content . $eol;
        $body .= "--" . $separator . "--";

        //SEND Mail
        if (!mail($mailto, $subject, $body, $headers)) {
            echo "mail send ... ERROR!";
            print_r( error_get_last() );
            return http_response_code(500);
        }

        // Drop table logs

        $db = new Mysqli(logger::servername, logger::username, logger::password, logger::dbname);
        // Check connection
        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }

        $db->query("DROP TABLE IF EXISTS logs");

        return http_response_code(200);
    }


}
