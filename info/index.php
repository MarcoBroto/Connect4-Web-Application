<?php
/**
 * Created by PhpStorm.
 * User: msoto
 * Date: 8/31/18
 * Time: 8:03 PM
 */

    $board_width = 7;
    $board_height = 6;
    $response = array(
        "width" => $board_width,
        "height" => $board_height,
        "strategies" => array('Smart', 'Random')
    );

    echo json_encode($response);
?>