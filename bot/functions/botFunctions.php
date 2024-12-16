<?php

function bot(string $method, array $params) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.telegram.org/bot' . TOKEN . '/' . $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $params
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function setWebhook() {
    $result = bot("setWebhook", [
        "url" => "https://kes-daniel.aranserversub.site/mian.php"
    ]);
    return $result;
}

function sendMessage($chat_id, $text, $reply_markup = null) {

    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    if ($reply_markup != null) {
        $data['reply_markup'] = $reply_markup;
    }

    return bot('sendMessage', $data);
}

function sendPhoto($chat_id, $photo, $caption) {
        return bot('sendPhoto', [
            'chat_id' => $chat_id,
            'photo'   => $photo,
            'caption' => $caption,
        ]);
}

function sendChatAction($chat_id, $action) {
        return bot('sendChatAction', [
            'chat_id' => $chat_id,
            'action'  => $action
        ]);
}
    

function debug($data, $reply_markup = null) {
    $result = print_r($data, true);
    if ($reply_markup == null) {
        return sendMessage( ADMIN, $result);
    } else {
        return sendMessage( ADMIN, $result, $reply_markup);
    }
}