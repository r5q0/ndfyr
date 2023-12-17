<?php


$scriptPath = 'public/index.php';

while (true) {
    echo "Starting....\n";

    exec("php $scriptPath", $output, $returnCode);

    if ($returnCode === 0) {
        echo "Bot executed successfully\n";
    } else {
        echo "Bot failed or stopped running. Retrying...\n";
    }


    sleep(2); 
}