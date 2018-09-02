<?php
/**
 * Created by Marco Soto.
 * Date: 8/31/18
 * Time: 10:10 PM
 */

    $response = array( //Standard Response
        "response" => false,
        "reason" => "Pid not specified and&#47;or move not specified"
    );


    if (isset($_GET["pid"]) and isset($_GET["move"])) { //Request has required parameters and is valid
        $MAX_MOVES = 7*6;
        $pid = $_GET['pid']; // Player id #
        $col = $_GET['move']; // Move column
        $gameFile = getGameFile($pid);

        if ($col < 0 or $col > 6) {
            $response['reason'] = 'Invalid slot, ' . $col;
            echo json_encode($response);
            return;
        }

        if (!isset($gameFile)) {
            $response['reason'] = 'Unknown PID';
            echo json_encode($response);
            return;
        }



        echo "ok";

        //TODO: Impelement pid and strategy memory and the following error response
        //{"response": false, "reason": "Unknown pid"}
        //TODO: Impelement application concurrency
    }

    echo json_encode($response);



    function getGameFile($pid) {
        $filename = getcwd() . "/games/" . $pid;
        echo $filename;
        if (isset($pid) and file_exists($filename)) {
            return $filename;
        }
        return null;
    }


    function checkForWin($board, $player, $row, $col) {
        return (checkForwardlash($board, $player, $row, $col) or
            checkBackslash($board, $player, $row, $col) or
            checkCardinals($board, $player, $row, $col));
    }


    function checkForwardlash($board, $player, $row, $col) {
        if ($row < $col) {
            $col -= $row;
            $row = 0;
        }
        else {
            $row -= $col;
            $col = 0;
        }

        $count = 0;
        $colLimit = count($board[0]);
        while ($row < count($board) and $col < $colLimit) {
            if ($board[$row][$col] == $player)
                $count++;
            else
                $count = 0;

            if ($count >= 4)
                return true;

            $row++;
            $col++;
        }

        return false;
    }


    function checkBackslash($board, $player, $row, $col) {
        while ($row > 0 and $col < count($board[0])) {
            $row--;
            $col++;
        }

        $count = 0;
        $rowLimit = count($board);
        while ($row < $rowLimit and $col >= 0) {
            if ($board[$row][$col] == $player)
                $count++;
            else
                $count = 0;

            if ($count >= 4)
                return true;

            $row++;
            $col--;
        }

        return false;
    }


    function checkCardinals($board, $player, $row , $col) {
        $verticalCount = $horizontalCount = 0;
        for ($i = 0; $i < count($board[0]); $i++) {
            if ($i < count($board)) {
                if ($board[$i][$col] == $player)
                    $verticalCount++;
                else
                    $verticalCount = 0;
            }

            if ($board[$row][$i] == $player)
                $horizontalCount++;
            else
                $horizontalCount = 0;

            if ($verticalCount >= 4 or $horizontalCount >= 4)
                return true;
        }

        return false;
    }