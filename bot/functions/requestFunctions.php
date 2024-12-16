<?php

function sendCaptchaImage($chatId, $captchaUrl) {
    
    $captchaImage = file_get_contents($captchaUrl);
    $tempFile = "captcha_$chatId.png";
    file_put_contents($tempFile, $captchaImage);

    sendPhoto($chatId , new CURLFile(realpath($tempFile)) , "حالا بهت که نمیخوره ربات باشی...\nولی بیا این کپچا رو حل کن.\nدمدم گرم");
    
    unlink($tempFile);
}

function logIn($url, $data) {
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ];
    return json_decode(file_get_contents($url, false, stream_context_create($options)), true);
}

function fetchData($path , $chat_id){
    $data = file_get_contents("http://109.95.61.92:5000/fetch_data", false, stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json',
            'content' => json_encode([
                'user_id' => $chat_id,
                'week' => "week",
            ])
        ]
    ]));
    
    $jsonString = $data;

    $file = fopen($path, 'w');
    fwrite($file, $jsonString);
    fclose($file);
}

function getMeal($path , $week , $meal){
    $jsonString = file_get_contents($path);
    $data = json_decode($jsonString , true);
    
    return $data[$week][$meal];
    
}