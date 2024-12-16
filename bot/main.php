<?php

require "path.php";
require $configPHP;
require $keyboardsPHP;
require $botFunctionsPHP;
require $userFunctionsPHP;
require $requestFunctionsPHP;


$input = json_decode(file_get_contents("php://input") , true);

require $variablesPHP;

$user = getStep($userStateJSON ,$chat_id );

if ($text == "/start"){
    sendMessage($chat_id , "به ربات خوش اومدی " , $chi_darim_keyboard);
    die;
}

if ($text == "چی داریم ؟"){
    if ($user["admin"] == 1){
        setStep($userStateJSON , $chat_id , "chooseWeek" , "old");
        sendMessage($chat_id , "کدومش ؟" , $admin_weeks_keyboard);
    } else {
        setStep($userStateJSON , $chat_id , "chooseWeek" , "old");
        sendMessage($chat_id , "کدومش ؟" , $weeks_keyboard);
    }
    die;
}

if ($user[$chat_id] == "chooseWeek"){
    if ($text == "این هفته"){
        setStep($userStateJSON , $chat_id , "thisWeek" , "old");
        sendMessage($chat_id , "انتخاب کن عزیزم!", $meals_keyboard);
        die;
    }
    
    if ($text =="هفته بعد"){
        setStep($userStateJSON , $chat_id , "nextWeek" , "old");
        sendMessage($chat_id , "انتخاب کن عزیزم!", $meals_keyboard);
        die;
    }
}

if (($user[$chat_id] == "thisWeek" || $user[$chat_id] == "nextWeek") && $text == "بازگشت"){
    if ($user["admin"] == 1){
        setStep($userStateJSON , $chat_id , "chooseWeek" , "old");
        sendMessage($chat_id , "کدومش ؟" , $admin_weeks_keyboard);
    } else {
        setStep($userStateJSON , $chat_id , "chooseWeek" , "old");
        sendMessage($chat_id , "کدومش ؟" , $weeks_keyboard);
    }
    die;
}

if ($text == "بروزرسانی" && $user["admin"] == 1){
    sendChatAction($chat_id , "sending photo...");
    $url = "http://109.95.61.92:5000/get_captcha?user_id=$chat_id"; 
    sendCaptchaImage($chat_id , $url);
    setStep($userStateJSON , $chat_id , "solveCaptcha" , "old");
    die;
}

if ($user["admin"] == 1 && $user[$chat_id] == "solveCaptcha"){
    $login_response = logIn(
        "http://109.95.61.92:5000/login",
        ['user_id' => $chat_id, 'captcha' => $text]
    );
    if ($login_response['status'] == 'success'){
        fetchData($databaseJSON , $chat_id);
    }
    setStep($userStateJSON , $chat_id , "chooseWeek" , "old");
    sendMessage($chat_id , "بروزرسانی شد!");
}

if (in_array($user[$chat_id] ,["thisWeek", "nextWeek"]) && in_array($text , ["صبحانه" , "نهار" , "شام"])){
    if ($text == "صبحانه"  && $user[$chat_id] == "thisWeek"){
        sendMessage($chat_id , getMeal($databaseJSON , $user["$chat_id"] , "breakfast"));
    } else if ($text == "نهار"  && $user[$chat_id] == "thisWeek"){
        sendMessage($chat_id , getMeal($databaseJSON , $user["$chat_id"] , "lunch"));
    } else if ($text == "شام"  && $user[$chat_id] == "thisWeek"){
        sendMessage($chat_id , getMeal($databaseJSON , $user["$chat_id"] , "dinner"));
    } else if ($text == "صبحانه" && $user[$chat_id] == "nextWeek"){
        sendMessage($chat_id , getMeal($databaseJSON , $user["$chat_id"] , "nextBreakfast"));
    } else if ($text == "نهار"  && $user[$chat_id] == "nextWeek"){
        sendMessage($chat_id , getMeal($databaseJSON , $user["$chat_id"] , "nextLunch"));
    } else if ($text == "شام"  && $user[$chat_id] == "nextWeek"){
        sendMessage($chat_id , getMeal($databaseJSON , $user["$chat_id"] , "nextDinner"));
    }
    die;
}
