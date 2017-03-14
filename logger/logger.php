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

class logger
{
    function __construct()
    {

    }

    public static function saveData(){


        session_start();
        $userId = session_id();
        if ( strlen($_POST[inputs])== 0)
            throw new \Exception("inputs is empty");

        $explodedInputs = explode("&",$_POST[inputs]);
        $inputs = [];

        foreach ($explodedInputs as $input){
            $arrayIndex = substr( $input, 0, strpos($input,'=') );
            $arrayValue = substr( $input, strpos($input,'=')+1, strlen($input) );
            if($arrayValue)
                $inputs[$arrayIndex] = addslashes($arrayValue);
        }
        $servername = DBSettings::servername;
        $dbname = DBSettings::dbname;
        $username = DBSettings::username;
        $password = DBSettings::password;
        $conn = new Mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            throw new \Exception("");
            die("Connection failed: " . $conn->connect_error);
        }

        //check the tatble exists



        return http_response_code(200);

    }
}
