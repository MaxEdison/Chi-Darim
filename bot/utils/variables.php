<?php

if (array_key_exists('message', $input)) {
    $first_name = $input['message']['from']['first_name'];
    $chat_id = $input['message']['from']['id'];
    $text = $input['message']['text'];
}