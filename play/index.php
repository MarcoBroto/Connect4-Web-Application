<?php
/**
 * Created by PhpStorm.
 * User: msoto
 * Date: 8/31/18
 * Time: 10:10 PM
 */

    $response = array(
        "response" => false,
        "reason" => "Pid not specified and/or move not specified"
    );

    if (isset($_GET["pid"]) and isset($_GET["move"])) {

        echo "ok";
        //{"response": false, "reason": "Unknown pid"}
        //{"response": false, "reason": "Invalid slot, 10"}
    }
    else
        echo json_encode($response);

?>