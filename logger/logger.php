<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 14.03.2017
 * Time: 11:22
 */

namespace blago;


class logger
{
    public static function saveData(){
        $userIP = $_SERVER['REMOTE_ADDR'];

        $explodedInputs = explode("&",$_POST[inputs]);
        $inputs = [];
        foreach ($explodedInputs as $input){
            $arrayIndex = substr($input,0,strpos($input,'='));
            $arrayValue = substr($input,strpos($input,'=')+1, strlen($input) );
            if($arrayValue)
                $inputs[$arrayIndex] = addslashes($arrayValue);
        }
        return true;

    }
}