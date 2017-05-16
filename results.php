<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 15.03.2017
 * Time: 13:26
 */

namespace blago;
use Mysqli;
require_once "./logger/DBSettings.php";
require_once "./logger/logger.php";

$sessionId = session_id();

// Set DB parametrs
$servername = DBSettings::servername;
$dbname = DBSettings::dbname;
$username = DBSettings::username;
$password = DBSettings::password;

// Connect to DB
$db = new Mysqli($servername, $username, $password, $dbname);

// Check connection
if ($db->connect_error) {
    throw new \Exception("");
    die("Connection failed: " . $db->connect_error);
}
//
//var_dump("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".logger::dbname."' AND RIGHT(TABLE_NAME,5)='_logs'");
//var_dump($db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".logger::dbname."' AND RIGHT(TABLE_NAME,5)='_logs'") );
//var_dump($db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".logger::dbname."' AND RIGHT(TABLE_NAME,5)='_logs'")->fetch_array() );
$tables = [];
$res = $db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".logger::dbname."' AND RIGHT(TABLE_NAME,5)='_logs'");
while($row = $res->fetch_array()) {
        $tables[] = $row;
    }

//$tables = $db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".logger::dbname."' AND RIGHT(TABLE_NAME,5)='_logs'")->fetch_all();

if (!$tables)
    throw new \Exception("No tables to fetch results");

foreach ($tables as $tableName) {
    $query = "SELECT * FROM $tableName[0]";

    $results = [];
    $res = $db->query($query);

    while($row = $res->fetch_assoc() ){
        $results[] = $row;
    }

    if(!$results){
        throw new \Exception("No results given");
    };

    ?>
    <table>

        <th>Table <?=$tableName[0]?></th>
        <?
        foreach ($results as $tr){
            ?>
            <tr>
                <?
                foreach ($tr as $td)
                {
                    ?><td style="text-align: right; padding: 0 10px 0 20px; border: 1px solid black;"><?=$td?></td><?
                }
                ?>
            </tr>
            <?
        }
        ?>
    </table>
<?}?>