<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.10.2/css/all.css" rel="stylesheet">
    <title>Register</title>
</head>

<?php
    session_start();
    function h($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    $mysqli = new mysqli('***', '***', '***', '***');
    if($mysqli->connect_error){
        echo $mysqli->connect_error;
        exit();
    } else {
        $mysqli->set_charset("utf8mb4");
    }
    
     // 成功・エラーメッセージの初期化
     $errors = array();

    // position名を取得
    $drop_positions = array();
    $drop_position = '';
    try{
        $result = $mysqli->query("SELECT position FROM position");
        while ($row = $result->fetch_assoc()){
            $drop_positions[] = $row["position"];
        }
        $result->close();
    }catch(PDOException $e){
        //トランザクション取り消し
        $pdo -> rollBack();
        $errors['error'] = "もう一度やり直してください。";
        print('Error:'.$e->getMessage());
    }

    foreach($drop_positions as $position){
        $drop_position .= "<option value='{$position}'>{$position}</option>";
    }
    

    // 名前を取得
    $drop_name_position = array();
    try{
        $result_name = $mysqli->query("SELECT name,position FROM member");
        while ($row_name = $result_name->fetch_assoc()){
            $drop_name_position[] = $row_name["name"];
            $drop_name_position[] = $row_name["position"];
        }
        $result_name->close();
    }catch(PDOException $e){
        //トランザクション取り消し
        $pdo -> rollBack();
        $errors['error'] = "もう一度やり直してください。";
        print('Error:'.$e->getMessage());
    }
    $drop_name_position = json_encode($drop_name_position);


    // 確認する(btn_confirm)を押した後の処理
    if(isset($_POST['btn_confirm'])){
        // POSTされたデータをいれる
        $name = isset($_POST['name']) ? $_POST['name']:NULL;
        $date = isset($_POST['date']) ? $_POST['date']:NULL;
        $arrival_time = date("H:i", strtotime($_POST['arrival_time']));
        $departure_time = date("H:i", strtotime($_POST['departure_time']));
        $health = isset($_POST['health']) ? $_POST['health']:NULL;       
        $log = isset($_POST['log']) ? $_POST['log']:NULL;
        $IN_other = isset($_POST['IN_other']) ? $_POST['IN_other']:NULL;
        $other = isset($_POST['other']) ? $_POST['other']:NULL;

        if($name=='' || $date=='' || $arrival_time=='' || $departure_time=='' || $health == ''){
            if($name == ''){
                $errors['name'] = '名前が入力されていません';
            }
            if($date == '2021-10-01'){
                $errors['date'] = '日付が入力されていません';
            }
            if($arrival_time == ''){
                $errors['arrival_time'] = '登校時間が入力されていません';
            }
            if($departure_time == ''){
                $errors['departure_time'] = '下校時間が入力されていません';
            }
            if($health == ''){
                $errors['health'] = '体温はどうでしたか';
            }
        }else{
            if(!empty($errors['name'])){
                unset($errors['name']);
            }
            if(!empty($errors['date'])){
                unset($errors['date']);
            }
            if(!empty($errors['arrival_time'])){
                unset($errors['arrival_time']);
            }
            if(!empty($errors['departure_time'])){
                unset($errors['departure_time']);
            }
            if(!empty($errors['health'])){
                unset($errors['health']);
            }
        }

        if($name != ''){
            $_SESSION['name'] = $name;
        }
        if($date != '2021-10-01'){
            $_SESSION['date'] = $date;
        }
        if($arrival_time != ''){
            $_SESSION['arrival_time'] = $arrival_time;
        }
        if($departure_time != ''){
            $_SESSION['departure_time'] = $departure_time;
        }
        if($health != ''){
            $_SESSION['health'] = $health;
        }
        if($log != '' || is_array($log)){
            $_SESSION['log'] = $log;
        }
        if($IN_other != ''){
            $_SESSION['IN_other'] = $IN_other;
        }
        if($other != ''){
            $_SESSION['other'] = $other;
        }
    }


    // 登録する(btn_submit)を押した後の処理
    if(isset($_POST['btn_submit'])){
        if($_SESSION){
            $name = $_SESSION['name'];
            $date = $_SESSION['date'];
            $arrival_time = $_SESSION['arrival_time'];
            $departure_time = $_SESSION['departure_time'];
            $health = '〇';
            $places = array_fill(0, 12, NULL);
            $logs = $_SESSION['log'];
            foreach($logs as $log){
                if($log == 'IN401N'){
                    $places[0] = '〇';
                }
                if($log == 'IN501N'){
                    $places[1] = '〇';
                }
                if($log == 'IN505N'){
                    $places[2] = '〇';
                }
                if($log == 'IN418N（小早川）'){
                    $places[3] = '〇';
                }
                if($log == 'IN419N（早見）'){
                    $places[4] = '〇';
                }
                if($log == 'IN603N（動物舎A）'){
                    $places[5] = '〇';
                }
                if($log == 'IN601N（動物舎B）'){
                    $places[6] = '〇';
                }
                if($log == 'IN409N'){
                    $places[7] = '〇';
                }
                if($log == 'IN412N'){
                    $places[8] = '〇';
                }
                if($log == '食堂'){
                    $places[9] = '〇';
                }
                if($log == '購買部'){
                    $places[10] = '〇';
                }
                if($log == '図書館'){
                    $places[11] = '〇';
                }
            }
            $IN_other = isset($_SESSION['IN_other']) ? $_SESSION['IN_other']:NULL;
            $other = isset($_SESSION['other']) ? $_SESSION['other']:NULL;

            // ここでデータベースに登録する
            try{
                $stmt = $mysqli -> prepare("INSERT INTO time_and_place (date, name, health, arrival_time, departure_time, IN401N, IN501N, IN505N, IN418N, IN419N, IN603N, IN601N, IN409N, IN412N, IN_other, dining, purchasing, library, other) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt -> bind_param('sssssssssssssssssss', $date, $name, $health, $arrival_time, $departure_time, $places[0], $places[1], $places[2], $places[3], $places[4], $places[5], $places[6], $places[7], $places[8], $IN_other, $places[9], $places[10], $places[11], $other);
                $stmt -> execute();
                $_SESSION = array();
                 
                
            }catch(PDOException $e){
                //トランザクション取り消し
                $pdo -> rollBack();
                $errors['error'] = "もう一度やり直してください。";
                print('Error:'.$e->getMessage());
            }
        }else{
            
        }
        
    }
?> 

<body>
    <div class='register-main-area'>
        <!-- page3 完了画面 -->
        <?php if(isset($_POST['btn_submit']) && count($errors) == 0): ?>
            <div class='success-message-area'>
                <div class='answers-each-area finish-message-area'>
                    <div class='success-message'>
                        登録しました。
                    </div>
                    <div class='login-botton-area'>
                        <a class='login-button' href='management.php'><i class="fas fa-eye fa-fw"></i>閲覧</a>
                        <a class='login-button' href='attendance.php'><i class="fas fa-pen-square fa-fw"></i>登録</a>
                    </div>   　    
                </div>
            </div>
              

        <!-- page2 確認画面 -->
        <?php elseif(isset($_POST['btn_confirm']) && count($errors) == 0): ?>
            <div class='home-botton-area'>
                    <a class='login-button home' href='login.php'><i class="fas fa-home fa-fw"></i>ホーム</a>
            </div> 
            <p class='confirm-message'>以下の情報を登録します。</p>
            <form action='' method='post'>
                <div class='answers-each-area'>
                   <p>名前: <?=h($_SESSION['name'])?></p>
                   <p>日付: <?=h($_SESSION['date'])?></p>
                   <p>登校時間: <?=h($_SESSION['arrival_time'])?></p>
                   <p>下校時間: <?=h($_SESSION['departure_time'])?></p>
                   <p>健康チェック: <?='〇'?></p>
                   <p>あなたが行った場所: </p>
                   <?php if(isset($_SESSION['log'])){
                            if(is_array($_SESSION['log'])){
                                foreach($_SESSION['log'] as $logs){
                                    echo nl2br($logs.PHP_EOL);
                                }
                            }else{
                                echo $_SESSION['log'];
                            }                            
                          } ?>
                    <?php if(isset($_SESSION['IN_other'])){
                              echo $_SESSION['IN_other'];
                          } ?>
                    <?php if(isset($_SESSION['other'])){
                              echo $_SESSION['other'];
                          } ?>
                </div>
                <div class='login-button-area'>
                    <input type='submit' name='btn_back' class='submit-button' value='戻る'>
                    <input type='submit' name='btn_submit' class='submit-button' value='登録する'>
                </div>
                
            </from>

        <!-- page1 登録画面 -->
        <?php elseif(!isset($errors['error']) || isset($_POST["btn_back"])): ?>
            <?php if(count($errors) > 0): ?>
                <div class='error-message'>
                    <?php 
                        foreach($errors as $value){
                            echo nl2br($value.PHP_EOL);
                        }
                    ?>
                </div>
            <?php endif; ?>

            <form action='' method='post'>
                <div class='home-botton-area'>
                    <a class='login-button home' href='index.php'><i class="fas fa-home fa-fw"></i>ホーム</a>
                </div> 
                <!-- 学年や名前を選ぶ -->
                <div class='answers-each-area'>
                    <div>
                        <div>学年等選択してください</div>
                        <select name='position' id='position'>
                            <option value=''>選択してください</option>
                            <?php 
                                echo $drop_position; ?>
                        </select>
                    </div><br>
                    <div>
                        <div>名前を選択してください</div>
                        <select name='name' id='name'>
                            <option value=''>選択してください</option>
                        </select>
                    </div>
                </div>
        
                <!-- 来校情報 -->
                <div class='answers-area'>
                    <!-- 来校日時登録 -->
                    <div class='answers-each-area'>
                        <input type='date' name='date' value="2021-10-01">
                        <input type='time' name='arrival_time' value='09:00'>
                        <input type='time' name='departure_time' value='17:00'>
                    </div>

                    <!-- 健康チェック -->
                    <div class='answers-each-area'>
                        <div class='answer-each'>
                            <input type='checkbox' id='health' name='health' value='health' class='attendance-check'>
                            <label for='health' class='attendance-label'>健康（37.5℃以下）</label>
                        </div>
                    </div>

                    <!-- 来校場所チェック -->
                   <div class='answers-each-area'>
                       <span>研究棟</span>
                       <div class='answer-each'>
                            <input type='checkbox' id='IN401' name='log[]' value='IN401N' class='attendance-check'>
                            <label for='IN401' class='attendance-label'>IN401N</label>
                        </div>
                        <div class='answer-each'>
                            <input type='checkbox' id='IN501' name='log[]' value='IN501N' class='attendance-check'>
                            <label for='IN501' class='attendance-label'>IN501N</label>
                        </div>
                        <div class='answer-each'>
                            <input type='checkbox' id='IN505' name='log[]' value='IN505N' class='attendance-check'>
                            <label for='IN505' class='attendance-label'>IN505N</label>
                        </div>
                        <div class='answer-each'>
                            <input type='checkbox' id='IN418' name='log[]' value='IN418N（小早川）' class='attendance-check'>
                            <label for='IN418' class='attendance-label'>IN4I8N（小早川）</label>
                        </div>
                        <div class='answer-each'>
                            <input type='checkbox' id='IN419' name='log[]' value='IN419N（早見）' class='attendance-check'>
                            <label for='IN419' class='attendance-label'>IN419N（早見）</label>
                        </div>
                        <div class='answer-each'>
                            <input type='checkbox' id='IN603' name='log[]' value='IN603N（動物舎A）' class='attendance-check'>
                            <label for='IN603' class='attendance-label'>動物舎A</label>
                        </div>
                        <div class='answer-each'>
                            <input type='checkbox' id='IN601' name='log[]' value='IN601N（動物舎B）' class='attendance-check'>
                            <label for='IN601' class='attendance-label'>動物舎B</label>
                        </div>
                        <div class='answer-each'>
                            <input type='checkbox' id='IN409' name='log[]' value='IN409N' class='attendance-check'>
                            <label for='IN409' class='attendance-label'>IN409N</label>
                        </div>
                        <div class='answer-each'>
                            <input type='checkbox' id='IN412' name='log[]' value='IN412N' class='attendance-check'>
                            <label for='IN412' class='attendance-label'>IN412N</label>
                        </div>
                        <div class='answer-each'>
                            <label class='attendance-label'>その他</label>
                            <input type='text' id='IN_other' name='IN_other' class='attendance-text' value='<?php if( !empty($_SESSION['IN_other']) ){ echo $_SESSION['IN_other']; } ?>'>
                        </div>
                    </div>
   
                    <div class='answers-each-area'>
                        <span>その他</span>
                        <div class='answer-each'>
                            <input type='checkbox' id='dining' name='log[]' value='食堂' class='attendance-check'>
                            <label for='dining' class='attendance-label'>食堂</label>
                        </div>
                        <div class='answer-each'>
                            <input type='checkbox' id='purchasing' name='log[]' value='購買部' class='attendance-check'>
                            <label for='purchasing' class='attendance-label'>購買部</label>
                        </div>
                        <div class='answer-each'>
                            <input type='checkbox' id='library' name='log[]' value='図書館' class='attendance-check'>
                            <label for='library' class='attendance-label'>図書館</label>
                        </div>
                        <div class='answer-each'>
                            <label class='attendance-label'>その他</label>
                            <input type='text' id='other' name='other' class='attendance-text' value='<?php if( !empty($_SESSION['other']) ){ echo $_SESSION['other']; } ?>'>                        
                        </div>
                    </div>
                </div>
                <input type='submit' name='btn_confirm' class='submit-button' value='確認する'>
            </form>            
        <?php endif; ?>
    </div>

    <script>
        let select_name = document.getElementById('name');
        let select_position = document.getElementById('position');
        if(document.getElementById('position')){
            select_position.onchange = changePosition;
        }
        
        const names_positions = JSON.parse('<?php echo $drop_name_position; ?>');

    
        // 学年が変更されたときの動作
        function changePosition(){
            // 変更後の学年を取得
            var changePosition = select_position.value;

            // 学年によって関数を切り替え
            if(changePosition == "Staff"){
                setStaff();
            }else if(changePosition == '博士研究員'){
                setPostdoc();
            }else if(changePosition == 'D'){
                setDoctor();
            }else if(changePosition == 'M2'){
                setMaster2();
            }else if(changePosition == 'M1'){
                setMaster1();
            }else if(changePosition == 'B4'){
                setBachelor();
            }else if(changePosition == '研究生'){
                setResearcher();
            }else if(changePosition == '共同研究員'){
                setCollab();
            }else{
                select_name.innerHTML ='';
                var option = document.createElement('option');
                option.value = '';
                option.text = '選択してください';

                select_name.appendChild(option);
            }
        }

        // Staffが選択されたとき
        function setStaff(){
            // 名前の選択肢を空にする
            select_name.textContent = null;
            
            // セットする
            let names = [];
            let number = 0;
            for(let i in names_positions){
                if(names_positions[i] == 'Staff'){
                    names[number] = names_positions[i-1];
                    number++;
                }
            }

            names.forEach((name) => {
                var nameOption = document.createElement('option');
                nameOption.value = name;
                nameOption.text = name;
                
                select_name.appendChild(nameOption);
            });
        }

        // PostDocが選択されたとき
        function setPostdoc(){
            // 名前の選択肢を空にする
            select_name.textContent = null;
            
            // セットする
            let names = [];
            let number = 0;
            for(let i in names_positions){
                if(names_positions[i] == '博士研究員'){
                    names[number] = names_positions[i-1];
                    number++;
                }
            }

            names.forEach((name) => {
                var nameOption = document.createElement('option');
                nameOption.value = name;
                nameOption.text = name;
                
                select_name.appendChild(nameOption);
            });
        }

        // Doctorが選択されたとき
        function setDoctor(){
            // 名前の選択肢を空にする
            select_name.textContent = null;
            
            // セットする
            let names = [];
            let number = 0;
            for(let i in names_positions){
                if(names_positions[i] == 'D'){
                    names[number] = names_positions[i-1];
                    number++;
                }
            }

            names.forEach((name) => {
                var nameOption = document.createElement('option');
                nameOption.value = name;
                nameOption.text = name;
                
                select_name.appendChild(nameOption);
            });
        }

        // M2が選択されたとき
        function setMaster2(){
            // 名前の選択肢を空にする
            select_name.textContent = null;
            
            // セットする
            let names = [];
            let number = 0;
            for(let i in names_positions){
                if(names_positions[i] == 'M2'){
                    names[number] = names_positions[i-1];
                    number++;
                }
            }

            names.forEach((name) => {
                var nameOption = document.createElement('option');
                nameOption.value = name;
                nameOption.text = name;
                
                select_name.appendChild(nameOption);
            });
        }

        // M1が選択されたとき
        function setMaster1(){
            // 名前の選択肢を空にする
            select_name.textContent = null;
            
            // セットする
            let names = [];
            let number = 0;
            for(let i in names_positions){
                if(names_positions[i] == 'M1'){
                    names[number] = names_positions[i-1];
                    number++;
                }
            }

            names.forEach((name) => {
                var nameOption = document.createElement('option');
                nameOption.value = name;
                nameOption.text = name;
                
                select_name.appendChild(nameOption);
            });
        }

        // B4が選択されたとき
        function setBachelor(){
            // 名前の選択肢を空にする
            select_name.textContent = null;
            
            // セットする
            let names = [];
            let number = 0;
            for(let i in names_positions){
                if(names_positions[i] == 'B4'){
                    names[number] = names_positions[i-1];
                    number++;
                }
            }

            names.forEach((name) => {
                var nameOption = document.createElement('option');
                nameOption.value = name;
                nameOption.text = name;
                
                select_name.appendChild(nameOption);
            });
        }

        // 研究生が選択されたとき
        function setResearcher(){
            // 名前の選択肢を空にする
            select_name.textContent = null;
            
            // セットする
            let names = [];
            let number = 0;
            for(let i in names_positions){
                if(names_positions[i] == '研究生'){
                    names[number] = names_positions[i-1];
                    number++;
                }
            }

            names.forEach((name) => {
                var nameOption = document.createElement('option');
                nameOption.value = name;
                nameOption.text = name;
                
                select_name.appendChild(nameOption);
            });
        }

        // 共同研究員が選択されたとき
        function setCollab(){
            // 名前の選択肢を空にする
            select_name.textContent = null;
            
            // セットする
            let names = [];
            let number = 0;
            for(let i in names_positions){
                if(names_positions[i] == '共同研究員'){
                    names[number] = names_positions[i-1];
                    number++;
                }
            }

            names.forEach((name) => {
                var nameOption = document.createElement('option');
                nameOption.value = name;
                nameOption.text = name;
                
                select_name.appendChild(nameOption);
            });
        }

        
    </script>
</body>
</html>   
