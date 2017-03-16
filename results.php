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

$query = "SELECT * FROM logs";

$results = $db->query($query)->fetch_all();

if(!$results){
    throw new \Exception("nothing is here");
};

?>
<table>
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