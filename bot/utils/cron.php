<?php

require_once("../config/config.php");
require_once("../functions/botFunctions.php");

$data = file_get_contents("project/userState.json");

$data = json_decode($data , true);

foreach($data["users"] as $index => $array){
    foreach($array as $chat_id => $step){
        sendMessage($chat_id , "رزرو غذا یادت نره ❤ ");
    }
}
