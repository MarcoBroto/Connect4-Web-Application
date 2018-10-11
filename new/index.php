<?php
/**
 * Created by Marco Soto.
 * Date: 8/31/18
 * Time: 8:03 PM
 */

    $response = array(
        'response' => false,
        'reason' => 'Strategy not specified'
    );

    if (isset($_GET['strategy'])) {
        $strategy = strtolower($_GET['strategy']);
        $BOARD_WIDTH = 7;
        $BOARD_HEIGHT = 6;

        if ($strategy == 'smart' or $strategy == 'random') {
            // Form json response
            unset($response['reason']);
            $pid = uniqid('', true);
            $response['response'] = true;
            $response['pid'] = $pid;

            // Write and store game data in /games folder
            chdir(dirname(getcwd()) . "/games"); // Change to games directory that stores saved games
            //$filename = getcwd() . "/" . $pid . ".txt"; // Used for localhost and servers with directory access
            $filename = '../games/' . $pid . '.txt'; // Required for class web server file system
            $file = fopen($filename, 'w');

            $newBoard = array_fill(0, $BOARD_HEIGHT, array_fill(0, $BOARD_WIDTH, 0));

            // json game state info written to pid file
            $writeText = array(
                'strategy' => $strategy,
                'board' => $newBoard,
                'moves' => 0
            );

            //fwrite($file, json_encode($writeText));
            fwrite($file_test, json_encode($writeText)); //TODO: Remove line after debugging
        }
        else
            $response['reason'] = 'Unknown strategy';
    }

    echo json_encode($response);
?>