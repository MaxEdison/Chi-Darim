<?php

function setStep($path , $chat_id , $step , $type){
    
    $jsonString = file_get_contents($path);
    $data = json_decode($jsonString , true);
    
    if ($type == "new"){
        array_push($data["users"] , [$chat_id => $step]);
    } else if ($type == "old") {
        for ($i = 0 ; $i < count($data["users"]) ; $i++){
            if (array_keys($data["users"][$i])[0] == $chat_id){
                $data["users"][$i] = [$chat_id => $step];
                break;
            }
        }
    }
    $jsonString = json_encode($data, JSON_PRETTY_PRINT);

    $file = fopen($path, 'w');
    fwrite($file, $jsonString);
    fclose($file);
}