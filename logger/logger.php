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
    const logFilesPath = "../logs/";

    // saves data to DB
    public static function saveData(){
        var_dump("save data function start");
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
        $pageTitle = $_POST[pageTitle];
        $tableName = $pageTitle."_logs";
        if ($result = $db->query("SHOW TABLES LIKE '".$tableName."'")) {
            if($result->num_rows !== 1) {
                $createTableQuery = "CREATE TABLE ".$tableName." (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, session_id VARCHAR(60), time TIMESTAMP)";
                if (!$db->query($createTableQuery) === true)
                    return http_response_code(424);
            }
        }
        else{
            return http_response_code(500);
        }

        if ( strlen($_POST[inputs]) == 0)
            throw new \Exception("inputs is empty");

        $explodedInputs = explode("&",urldecode($_POST[inputs]));
        $inputs = [];

        foreach ($explodedInputs as $input){
            $arrayIndex = substr( $input, 0, strpos($input,'=') );

            // check if column of input exists
            // if does not -> add
            $arrayIndex = str_replace("[", "_", $arrayIndex);
            $arrayIndex = str_replace("]", "", $arrayIndex);
            $arrayIndex = "_".$arrayIndex;
            $result = $db->query("SHOW COLUMNS FROM `".$tableName."` LIKE '".$arrayIndex."'");
            $columnExists = (mysqli_num_rows($result))?TRUE:FALSE;
            if(!$columnExists) {
                try {
                    $db->query("ALTER TABLE ".$tableName." ADD ".$arrayIndex." VARCHAR(60)");
                } catch (Exception $e) {
                    var_dump($e);
                }
            }

            $arrayValue = substr( $input, strpos($input,'=')+1, strlen($input) );
            if($arrayValue)
                $inputs[$arrayIndex] = addslashes($arrayValue);
        }

        $selectBySession = $db->query("SELECT session_id FROM ".$tableName." WHERE session_id='".$sessionId."'");

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
            $keys = str_replace("[", "_", $keys);
            $keys = str_replace("]", "", $keys);            
            $keys = rtrim($keys, ',');
        // end make
            $result =$db->query("SHOW TABLES from ".logger::dbname);
                    // var_dump($db->query("SHOW COLUMNS FROM `".$tableName."`"));
            // var_dump('name '.$tableName);
            $tables = $db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".logger::dbname."' AND RIGHT(TABLE_NAME,5)='_logs'");
            try {
                var_dump("tables from  ");
                var_dump($tables);
            } catch (Exception $e) {
                var_dump($e);
            }
            $insertQuery = "INSERT INTO ".$tableName." (session_id,".$keys.") VALUES ('".$sessionId."',".$values.")";
            var_dump($insertQuery);
            var_dump($db->query($insertQuery));
            if (!$db->query($insertQuery)){
                var_dump("here is 500 error");
                return http_response_code(500);
            }
        }
        else{
            $updateQuery = "UPDATE ".$tableName." SET ";
            foreach ($inputs as $key => $value) {
                $key = str_replace("[", "_", $key);
                $key = str_replace("]", "", $key);   
                $updateQuery = $updateQuery.$key."='".$value."',";
            }

            $updateQuery = rtrim($updateQuery,',');
            $updateQuery = $updateQuery." WHERE session_id='".$sessionId."'";
            if (!$db->query($updateQuery))
                return http_response_code(500);

        }
    // end make

        var_dump("save function ended");
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

        $tables = $db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".logger::dbname."' AND RIGHT(TABLE_NAME,5)='_logs'")->fetch_all();

        if (!is_dir(logger::logFilesPath))
            mkdir(logger::logFilesPath);

        $logFile = fopen(logger::logFilesPath.logger::logFileName, "w");

        foreach ($tables as $tableName) {

            if (!$usersData = $db->query("SELECT * FROM ".$tableName[0]))
                return http_response_code(500);

            fputcsv($logFile, [""], ';');
            fputcsv($logFile, $tableName, ';');

            $columns = $usersData->fetch_fields();
            $columnNames = [];
            foreach ($columns as $column)
            {
                array_push($columnNames, $column->name);
            }
            fputcsv($logFile, $columnNames, ";");
            $usersData->fetch_all();
            foreach ($usersData as $line) {
                foreach ($line as $p => $lineItem) {
                    $line[$p] = iconv("utf-8", "windows-1251", $lineItem);
                }
                fputcsv($logFile, $line, ";");
            }
        }

        fclose($logFile);
        return http_response_code(200);

    }

    // sends email with usersDataLog.csv according to the data in MailerSettings.php
    public static function sendData()
    {
        logger::writeData();
        $filePath = logger::logFilesPath;
        $fileName = logger::logFileName;

        $mailto = logger::mailTo;
        $subject = logger::subject;
        $message = logger::message;

        $content = file_get_contents($filePath.$fileName);
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

        $tables = $db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".logger::dbname."' AND RIGHT(TABLE_NAME,5)='_logs'")->fetch_all();

        foreach ($tables as $tableName) {
            $drop_query = "DROP TABLE IF EXISTS $tableName[0]";
            $db->query($drop_query);
        }

        return http_response_code(200);
    }


}
