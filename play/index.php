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
        $MAX_MOVES = 7*6; // Move count that determines if game is a tie
        $pid = $_GET['pid']; // Player id #
        $opponentMove = $_GET['move']; // Opponent move column
        $gameFileName = getGameFileName($pid); // Game state filename

        // Check if request queries are valid
        if ($opponentMove < 0 or $opponentMove > 6) {
            $response['reason'] = 'Invalid slot, ' . $opponentMove;
            echo json_encode($response);
            return;
        }
        if ($gameFileName == null) {
            $response['reason'] = 'Unknown PID';
            echo json_encode($response);
            return;
        }
        $gameFile = fopen($gameFileName, 'r+'); // Actual Game state file


        // Formulate non-final json response
        unset($response);
        $response = array(
            'response' => 'true',
            'ack_move' => array(
                'slot' => $opponentMove,
                'isWin' => false,
                'isDraw' => false,
                'row' => array()
            ),
            'move' => array(
                'slot' => 0,
                'isWin'=> false,
                'isDraw' => false,
                'row' => array()
            )
        );


        // Extract game state information from file
        //echo $gameFile; //NOTE: (debug) //TODO: Remove line when testing is finished
        $fileContents = fread($gameFile, filesize($gameFileName)); // Read json text from file
        $fileContents = json_decode($fileContents, true); // Turn json into usable array
        $board = $fileContents['board']; // Current board state
        $numMoves = $fileContents['moves']; // Total move count
        $strategy = $fileContents['strategy']; // Strategy type


        // Register opponent move
        $moveResponse = makeMove($board, -1, $opponentMove);
        $pieceRow = findRowOfPiece($board, -1, $opponentMove); // Row height (top row = 0) where opponent's placed piece resides
        $winningRow = checkForWin($board, -1, $pieceRow, $opponentMove);

        // Check for win or draw after opponent move
        if ($winningRow != null) { // Opponent wins
            $response['ack_move']['isWin'] = true;
            $response['ack_move']['row'] = $winningRow;
            unlink($gameFileName); // Delete game state file
            echo json_encode($response);
            return;
        }
        else if (++$numMoves >= $MAX_MOVES) { // Game is a draw
            $response['ack_move']['isDraw'] = true;
            $response['move']['isDraw'] = true;
            unlink($gameFileName); // Delete game state file
            echo json_encode($response);
            return;
        }

        //printBoard($board); //TODO: Remove line when testing is finished

        // Register computer move and send revised response
        if ($strategy == 'smart') { // Make calculated move
            do {
                //$computerMove = calculateMove($board);
                $computerMove = rand(0, 6); //TODO: Implement smart move and remove line, used for testing
                // echo "move: " . $computerMove; //NOTE: (debug) //TODO: Remove line when testing is finished
            } while (!isValidMove($board, $computerMove));
            $moveResponse = makeMove($board, 1, $computerMove);
            $response['move']['slot'] = $computerMove;
        }
        else { // Make random move
            do {
                $computerMove = rand(0, 6);
                //echo "move: " . $computerMove; //NOTE: (debug) //TODO: Remove line when testing is finished
            } while (!isValidMove($board, $computerMove));
            $moveResponse = makeMove($board, 1, $computerMove);
            $response['move']['slot'] = $computerMove;
        }
        $pieceRow = findRowOfPiece($board, 1, $computerMove);
        $winningRow = checkForWin($board, 1, $pieceRow, $computerMove);

        // Check for win or draw after computer move
        if ($winningRow != null) { // Opponent wins
            $response['move']['isWin'] = true;
            $response['move']['row'] = $winningRow;
            unlink($gameFileName); // Delete game state file
            echo json_encode($response); // Deliver final json response
            return;
        }
        else if (++$numMoves >= $MAX_MOVES) { // Game is a draw
            $response['ack_move']['isDraw'] = true;
            $response['move']['isDraw'] = true;
            unlink($gameFileName); // Delete game state file
            echo json_encode($response); // Deliver final json response
            return;
        }

        //printBoard($board); // (debug)

        // Write new game state to file
        $gameFile = fopen($gameFileName, 'w+');
        $writeToFile = array(
            'strategy' => $strategy,
            'board' => $board,
            'moves' => $numMoves
        );
        fwrite($gameFile, json_encode($writeToFile));


        //TODO: Impelement application concurrency

    }

    echo json_encode($response);


    /*
     * The following functions are required for the web application to function and play connect 4.
     * They implement required functionality for the previous script, playing both random and smart strategies, and checking the board for wins.
     */

    function getGameFileName($pid) {
        /* The following block is used for locahost and servers with directory access
        $dir = dirname(getcwd()) . "/games/";
        chdir($dir); // Change to game storage directory
        $filename = $dir . $pid . ".txt";
        */

        $filename = '../games/' . $pid . '.txt'; // Required for class website file system functionality
        if (!file_exists($filename))
            return null;
        return $filename;
    }


    function isValidMove(&$board, $col) {
        if ($board[0][$col] != 0) return false;
        return true;
    }


    function makeMove(&$board, $player, $col) {
        if ($board[0][$col] != 0)
            return false;

        for ($i = 1; $i < count($board); $i++) {
            if ($board[$i][$col] != 0) {
                $board[$i - 1][$col] = $player;
                return true;
            }
        }
        $board[count($board)-1][$col] = $player;
        return true;
    }


    function checkForWin($board, $player, $row, $col) {
        //echo "Checking For Win\n"; //TODO: Remove line when testing is finished
        $foundWin = null;
        $fslash = checkForwardlash($board, $player, $row, $col, 4);
        $foundWin = $fslash != null ? $fslash : $foundWin;
        if ($foundWin) return $foundWin; // Short circuit

        $bslash = checkBackslash($board, $player, $row, $col, 4);
        $foundWin = $bslash != null ? $bslash : $foundWin;
        if ($foundWin) return $foundWin; // Short curcuit

        $cardinals = checkCardinals($board, $player, $row, $col, 4);
        $foundWin = $cardinals != null ? $cardinals : $foundWin;

        return $foundWin;
    }


    function checkForwardlash($board, $player, $row, $col, $count_limit) {
        //echo "Checking Forward Slash\n"; //TODO: Remove line when testing is finished
        // Determine calculation limits
        if ($row < $col) {
            $col -= $row;
            $row = 0;
        }
        else {
            $row -= $col;
            $col = 0;
        }

        $count = 0; // Sequence counter
        $winningRowIndices = array(); // Stack storing winning row indices
        $colLimit = count($board[0]); // Used in index calculation
        while ($row < count($board) and $col < $colLimit) { // Valid sequence, increment sequence count
            if ($board[$row][$col] == $player) {
                $count++;
                array_push($winningRowIndices, $col, $row); // Push cell index onto stack
            }
            else { // Invalid sequence, reset sequence count
                $count = 0;
                unset($winningRowIndices);
                $winningRowIndices = array();
            }

            if ($count >= $count_limit) // Win found
                return $winningRowIndices;

            $row++;
            $col++;
        }

        return null;
    }


    function checkBackslash($board, $player, $row, $col, $count_limit) {
        //echo "Checking Backslash\n"; //NOTE: (debug)
        // Determine calculation limits
        while ($row > 0 and $col < count($board[0])) {
            $row--;
            $col++;
        }

        $count = 0; // Sequence counter
        $winningRowIndices = array(); // Stack storing winning row indices
        $rowLimit = count($board); // Used in indices calculation
        while ($row < $rowLimit and $col >= 0) {
            if ($board[$row][$col] == $player) {
                $count++;
                array_push($winningRowIndices, $col, $row); // Push cell indices onto stack
            }
            else { // Invalid sequence, reset sequence count
                $count = 0;
                unset($winningRowIndices);
                $winningRowIndices = array();
            }

            if ($count >= $count_limit) // Win found
                return $winningRowIndices;

            $row++;
            $col--;
        }

        return null;
    }


    function checkCardinals($board, $player, $row , $col, $count_limit) {
        //echo "Checking Cardinals\n"; //NOTE: (debug)
        $verticalCount = $horizontalCount = 0; // Sequence counters
        $winningVerticalIndices = array(); // Stack storing winning row indices of column
        $winningHorizontalIndices = array(); // Stack storing winning row indices of row

        for ($i = 0; $i < count($board[0]); $i++) {
            if ($i < count($board)) {
                if ($board[$i][$col] == $player) { // Valid sequence, increment sequence count
                    $verticalCount++;
                    array_push($winningVerticalIndices, $col, $i); // Push cell indices onto stack
                }
                else { // Invalid sequence, reset sequence count
                    $verticalCount = 0;
                    unset($winningVerticalIndices);
                    $winningVerticalIndices = array();
                }
            }

            if ($board[$row][$i] == $player) { // Valid sequence, increment sequence count
                $horizontalCount++;
                // TODO: Fix index storage numbers
                array_push($winningHorizontalIndices, $i, $row); // Push cell indices onto stack
            }
            else { // Invalid sequence, reset sequence count
                $horizontalCount = 0;
                unset($winningHorizontalIndices);
                $winningHorizontalIndices = array();
            }

            if ($verticalCount >= $count_limit) { // Win found in column
                return $winningVerticalIndices;
            }
            else if ($horizontalCount >= $count_limit) { // Win found in row
                return $winningHorizontalIndices;
            }
        }

        return null;
    }


    function calculateMove($board) {
        //TODO: Implement smart strategy

//        for ($col = 0; $col < count($board); $col++) {
//            if (checkCardinals($board, ))
//        }



        return 0;
    }


    function findPartiallyFilledRow($board, $player) {

    }


    function findRowOfPiece($board, $player, $col) {
        for ($i = 0; $i < count($board); $i++) {
            if ($board[$i][$col] == $player)
                return $i;
        }
        return -1;
    }



    //NOTE: (debug)
    function printBoard(&$board) {
        echo "Printing Board" . "<br/><br/>";
        for ($i = 0; $i < count($board); $i++) {
            for ($j = 0; $j < count($board[$i]); $j++)
                echo $board[$i][$j] . " ";
            echo "<br/><br/>";
        }
    }
?>