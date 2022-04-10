<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="stylesheet" href="style.css">
</head>

<?php
    session_start();
    header('Content-type: application/json; charset=UTF-8');
    
    $json = file_get_contents('php://input');
    $log_origin = json_decode($json, true);
    
    $mysqli = new mysqli('***', '***', '***', '***');
    if($mysqli->connect_error){
        echo $mysqli->connect_error;
        exit();
    } else {
        $mysqli->set_charset("utf8mb4");
    }

    // ここでデータベースを更新する
    try{
        $stmt = $mysqli -> prepare("DELETE FROM member WHERE `id`=?");
        $stmt -> bind_param('i', $log_origin['id']);
        $stmt -> execute();
        $_SESSION = array();
    }catch(PDOException $e){
       //トランザクション取り消し
        $pdo -> rollBack();
        $errors['error'] = "もう一度やり直してください。";
        print('Error:'.$e->getMessage());
    }
