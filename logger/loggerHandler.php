<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 14.03.2017
 * Time: 15:56
 */

session_start();
require_once './logger.php';
echo( \blago\logger::saveData() );
