<?php
while(true){
$data = rand(5,100000000);
$result = file_get_contents("http://localhost:8080/r=$data");
echo $result;
}
