<?php
    $id = '321123';
        $tempkey = rand(10000, 99999);
        r::exec("UPDATE users SET tempkey = $tempkey WHERE id = $id");
        echo $link = file_get_contents("http://adfoc.us/api/?key=4a950ed379959bde2fe57166af3ede54&url=http://clothesremoved.com/link.php?id=$id&tempkey=$tempkey");
    
