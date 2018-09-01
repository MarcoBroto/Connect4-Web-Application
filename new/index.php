<?php
/**
 * Created by PhpStorm.
 * User: msoto
 * Date: 8/31/18
 * Time: 8:03 PM
 */

    $response = array(
        'response' => false,
        'reason' => 'Strategy not specified'
    );

    if (isset($_GET['strategy'])) {
        $strategy = $_GET['strategy'];

        if ($strategy == 'Smart') {
            unset($response['reason']);
            $response['response'] = true;
            $response['pid'] = uniqid('', true);
        }
        elseif ($strategy == 'Random') {
            unset($response['reason']);
            $response['response'] = true;
            $response['pid'] = uniqid('', true);
        }
        else
            $response['reason'] = 'Unknown strategy';

        echo json_encode($response);
    }
    else {
        echo json_encode($response);
    }
?>

