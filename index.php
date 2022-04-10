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
    function h($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    $password = 'password';
    $param_json = json_encode($password); //JSONエンコード
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
                    <input type='checkbox' id='setting_pass'/>
                    <label class='overlay' for='setting_pass'>
                        <div class='window'>
                            <label class='close' for='setting_pass'>×</label>
                            <p class='text'>                                
                                <p class='setting-pass-explanation'>パスワードを入力してください</p>
                                <div class='setting-pass-explanation' id='comment'></div>    <!-- パスワード間違い表示 -->
                                <input class='passward-box' type='password' id='password' placeholder='passward' value=''/>
                                <p><input type='button' class='submit-button' name='check_password' value='OK' onclick='checkPass()'></p>                                
                            </p>
                        </div>
                    </label>
                </div>                
            </div>
        </div>        
    </div>
    
<script>
    var correct_pass = '<?php echo $password; ?>'; //JSONデコード
    const commentArea = document.getElementById('comment');

    function checkPass(){
        let enter_password = document.getElementById('password').value;
        if(enter_password == correct_pass){
            window.location.href = 'setting.php';
        }else{
            commentArea.innerHTML = '';
        
            const commentDiv = document.createElement('div');
            commentDiv.innerText = 'パスワードが間違っています';
            commentArea.appendChild(commentDiv);
        }
    }
</script>
</body>
</html>
