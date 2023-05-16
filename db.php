<?php

$db = [
    'host'=>'localhost',
    'db'=>'',
    'username'=>'',
    'password'=>''
];

$dsn = "mysql:host={$db['host']};dbname={$db['db']};charset=utf8";

$opt = [
  PDO::ATTR_ERRMODE =>PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE =>PDO::FETCH_ASSOC,
];

$pdo = new PDO($dsn,$db['username'],$db['password'],$opt);

function get_chat_id($chat_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM chat WHERE chat_id =?");
    $stmt->execute([$chat_id]);
    return $stmt->fetch();
}

function add_chat($chat_id,$first_name,$lang)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT  INTO chat  (chat_id,first_name,lang) VALUE  (?,?,?)");
    $stmt->execute([$chat_id,$first_name,$lang]);
    file_put_contents(__DIR__.'/stmt.txt',print_r($stmt,1),FILE_APPEND);
    return $stmt->fetch();

}

function update_chat($chat_id,$lang)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE chat SET lang = ? WHERE chat_id = ?");
    $stmt->execute([$lang,$chat_id]);
    return $stmt->fetch();
}