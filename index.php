<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.10.2/css/all.css" rel="stylesheet">
    <title>Home</title>
</head>

<?php
    session_start();
    function h($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // 成功・エラーメッセージの初期化
    $errors = array();

    // envファイル使用のため
    require '../vendor/autoload.php';
    // .envを使用する
    Dotenv\Dotenv::createImmutable(__DIR__)->load();
    // .envファイルで定義したHOST等を変数に代入
    $SETTING_PASS = $_ENV["SETTING_PASS"];

    $password = 'password';
    $param_json = json_encode($password); //JSONエンコード

    if(isset($_POST['check_password'])){
        $input_pass = isset($_POST['password']) ? $_POST['password'] : NULL;
        if($input_pass == $SETTING_PASS){
            header("Location:setting.php");
        }else{
            $errors['miss'] = 'パスワードが間違っています。';

        }
    }
?>

<body>
    <div class='login-area'>
        <div class='login-page'>
            <div class='login-form'>
                <h3 class='login-title'>小早川・早見研究室　来校ログ</h3>
                <div class='login-botton-area'>
                    <a class='login-button' href='management.php'><i class="fas fa-eye fa-fw"></i>閲覧</a>
                    <a class='login-button' href='attendance.php'><i class="fas fa-pen-square fa-fw"></i>登録</a>
                    <label class='login-button' for='setting_pass'><i class="fas far fa-cog fa-fw"></i>設定</label>
                    <!-- モーダルウィンドウ -->
                    <input type='checkbox' id='setting_pass' <?php if(count($errors) != 0 && isset($_POST['check_password'])){ echo 'checked'; } ?>/>
                    <label class='overlay' for='setting_pass'>
                        <div class='window'>
                            <label class='close' for='setting_pass'>×</label>
                            <p class='text'>                                
                                <p class='setting-pass-explanation'>パスワードを入力してください</p>
                                <?php if(count($errors) != 0 && isset($_POST['check_password'])): ?>
                                    <div class='setting-pass-explanation'>
                                        <?=h($errors['miss']);?>
                                    </div>
                                <?php endif; ?>
                                <form action='' method='post'>
                                    <input class='passward-box' type='password' id='password' name='password' placeholder='passward' value=''/>
                                    <p><input type='submit' class='submit-button' name='check_password' value='OK'></p>
                                </form>                                                    
                            </p>
                        </div>
                    </label>
                </div>                
            </div>
        </div>        
    </div>
</body>
</html>
