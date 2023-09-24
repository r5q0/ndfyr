<?php
$raw = file_get_contents("https://clicksfly.com/api?api=245362e1a50e0e07a022f4d9dbf58f30f642be80&url=localhost.com/index.php?id=1?tempkey=1");
$clean = json_decode($raw, true);
echo $clean['shortenedUrl'];
?>