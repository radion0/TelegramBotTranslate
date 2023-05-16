<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/db.php';

use Dejurin\GoogleTranslateForFree;
use Telegram\Bot\Api;

$token = '';

$telegram = new Api($token);

// Example usage
$update = $telegram->getWebhookUpdate();
print_r($update);
file_put_contents(__DIR__.'/log.txt',print_r($update,1),FILE_APPEND);
$chat_id = $update['message']['chat']['id'] ?? '';
$text = $update["message"]["text"] ?? '';




if (isset($update['callback_query'])){

    foreach ($update['callback_query']['message']['reply_markup']['inline_keyboard'][0] as $item){
        if($item['text']==$update['callback_query']['data']){
            update_chat($update['callback_query']['message']['chat']['id'],$update['callback_query']['data']);
            $response = send_request($token, 'answerCallbackQuery', [
                'callback_query_id' => $update['callback_query']['id'],
            ]);
            $response = $telegram->sendMessage([
                'chat_id' => $update['callback_query']['message']['chat']['id'],
                'text' => "Можете вводить слово для перевода с выбранного языка",
                'reply_markup' => json_encode([
                    'inline_keyboard' => get_keyboard($update['callback_query']['data'])
                ])
            ]);
        }

    }
    $response = send_request($token, 'answerCallbackQuery', [
        'callback_query_id' => $update['callback_query']['id'],
        'text' => "Активыный язык ",
    ]);



} else switch ($text)
{
    case $text=='/start':
        $data = get_chat_id($chat_id);
        if(empty($data)){
            add_chat($chat_id,$update['message']['chat']['first_name'],'en');
            $check = 'en';
        }
        else{
            $check = $data['lang'];
        }
        $response = $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => "Переключить язык",
            'reply_markup' => json_encode([
                'inline_keyboard' => get_keyboard($check)
            ])
        ]);
        break;
    case !empty($text) :
        {
            $source = 'en';
            $data = get_chat_id($chat_id);
            $source = ($data['lang'] == 'en') ? 'en' : 'ru';
            $target = ($data['lang'] == 'ru') ? 'en' : 'ru';
            $attempts = 5;
            $tr = new GoogleTranslateForFree();
            $result = $tr->translate($source, $target, $text, $attempts);
            if ($result) {
                $response = $telegram->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => $result,
                ]);
            }
        }
    break;
}


function get_keyboard($lang){
    return [
        [
            ['text'=>$lang =='en'? 'en ->': 'en','callback_data'=>'en'],
            ['text'=>$lang =='ru'? 'ru ->': 'ru','callback_data'=>'ru'],
        ]
    ];
}

function send_request($token, $method, $params = [])
{
    if (!empty($params)) {
        $url = "https://api.telegram.org/bot{$token}/{$method}" . '?' . http_build_query($params);
    } else {
        $url = "https://api.telegram.org/bot{$token}/{$method}";
    }
    return json_decode(file_get_contents($url));
}
    ?>