<?php

require "path.php";
require $configPHP;
require $keyboardsPHP;
require $botFunctionsPHP;
require $userFunctionsPHP;
require $requestFunctionsPHP;


$input = json_decode(file_get_contents("php://input") , true);

require $variablesPHP;

$user = getStep($userStateJSON ,$from_id );

if ($text == "/start"){
    sendMessage($from_id , "به ربات خوش اومدی " , $chi_darim_keyboard);
    die;
}

if ($text == "چی داریم ؟"){
    if ($user["admin"] == 1){
        setStep($userStateJSON , $from_id , "chooseWeek" , "old");
        sendMessage($from_id , "کدومش ؟" , $admin_weeks_keyboard);
    } else {
        setStep($userStateJSON , $from_id , "chooseWeek" , "old");
        sendMessage($from_id , "کدومش ؟" , $weeks_keyboard);
    }
    die;
}

if ($user[$from_id] == "chooseWeek"){
    if ($text == "این هفته"){
        setStep($userStateJSON , $from_id , "thisWeek" , "old");
        sendMessage($from_id , "انتخاب کن عزیزم!", $meals_keyboard);
        die;
    }
    
    if ($text =="هفته بعد"){
        setStep($userStateJSON , $from_id , "nextWeek" , "old");
        sendMessage($from_id , "انتخاب کن عزیزم!", $meals_keyboard);
        die;
    }
}

if (($user[$from_id] == "thisWeek" || $user[$from_id] == "nextWeek") && $text == "بازگشت"){
    if ($user["admin"] == 1){
        setStep($userStateJSON , $from_id , "chooseWeek" , "old");
        sendMessage($from_id , "کدومش ؟" , $admin_weeks_keyboard);
    } else {
        setStep($userStateJSON , $from_id , "chooseWeek" , "old");
        sendMessage($from_id , "کدومش ؟" , $weeks_keyboard);
    }
    die;
}

if ($text == "بروزرسانی" && $user["admin"] == 1){
    sendChatAction($from_id , "sending photo...");
    $url = "http://109.95.61.92:5000/get_captcha?user_id=$from_id"; 
    sendCaptchaImage($from_id , $url);
    setStep($userStateJSON , $from_id , "solveCaptcha" , "old");
    die;
}

if ($user["admin"] == 1 && $user[$from_id] == "solveCaptcha"){
    $login_response = logIn(
        "http://109.95.61.92:5000/login",
        ['user_id' => $from_id, 'captcha' => $text]
    );
    if ($login_response['status'] == 'success'){
        fetchData($databaseJSON , $from_id);
    }
    setStep($userStateJSON , $from_id , "chooseWeek" , "old");
    sendMessage($from_id , "بروزرسانی شد!");
}

if (in_array($user[$from_id], ["thisWeek", "nextWeek"]) && in_array($text, ["صبحانه", "نهار", "شام"])) {
    
    $mealType = match ($text) {
        "صبحانه" => $user[$from_id] === "thisWeek" ? "breakfast" : "nextBreakfast",
        "نهار"   => $user[$from_id] === "thisWeek" ? "lunch"     : "nextLunch",
        "شام"   => $user[$from_id] === "thisWeek" ? "dinner"    : "nextDinner",
    };

    sendMessage($from_id, getMeal($databaseJSON, $user[$from_id], $mealType));
    die;
}

