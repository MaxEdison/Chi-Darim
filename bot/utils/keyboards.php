<?php 

$chi_darim_keyboard = json_encode([
    "resize_keyboard" => true,
    "keyboard" => [
        [["text" => "چی داریم ؟"]],
    ]
]);

$weeks_keyboard = json_encode([
    "resize_keyboard" => true,
    "keyboard" => [
        [["text" => "این هفته"] , ["text" => "هفته بعد"]],
    ]
]);

$admin_weeks_keyboard = json_encode([
    "resize_keyboard" => true,
    "keyboard" => [
        [["text" => "این هفته"] , ["text" => "هفته بعد"]],
        [["text" => "بروزرسانی"]]     
    ]
]);

$meals_keyboard = json_encode([
    "resize_keyboard" => true,
    "keyboard" => [
        [["text" => "صبحانه"], ["text" => "نهار"], ["text" => "شام"]],
        [["text" => "بازگشت"]]
    ]
]);

