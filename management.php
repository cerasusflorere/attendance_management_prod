<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.10.2/css/all.css" rel="stylesheet">
    <title>management</title>
</head>

<?php
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

    // テーブルから情報を取得
    // logsは今後の出力の元、オブジェクトが入った配列
    $logs = array();
     try{
        $result = $mysqli->query("SELECT name, date, health, arrival_time, departure_time, IN401N, IN501N, IN505N, IN418N, IN419N, IN603N, IN601N, IN409N, IN412N, IN_other, dining, purchasing, library, other FROM time_and_place");
        while ($row = $result->fetch_assoc()){
            $logs[] = $row;
        }
        $result->close();
    }catch(PDOException $e){
        //トランザクション取り消し
        $pdo -> rollBack();
        $errors['error'] = "もう一度やり直してください。";
        print('Error:'.$e->getMessage());
    }

    // 並び替えたいキーを抽出
    foreach($logs as $key => $value){
        $sort_keys_date[$key] = $value['date'];
        $sort_keys_time[$key] = $value['arrival_time'];
    }

    // 並び替え
    array_multisort($sort_keys_date, SORT_ASC,$sort_keys_time, SORT_ASC, $logs);

    $logs = json_encode($logs);
    
?>

<body>
    <div class='log-page-area'>
        <a class='login-button home management-page-button' href='index.php'><i class="fas fa-home fa-fw"></i>ホーム</a>
        <!-- 検索期間選択部分 -->
        <div class='duration-select-area' id='duration-area'>
            <select name='duration' id='duration'>
                <option value='all'>全期間</option>
                <option value='week2'>2週間</option>
                <option value='month1'>1ヵ月</option>
                <option value='year1'>1年</option>
            </select>
        </div>
    
    
        <!-- ダウンロードボタン -->
        <div class='download-button-area'>
            <button class='download-button' onclick="downloadData()"><i class="fas fa-download fa-fw"></i>ダウンロード</button>
        </div>
    
        <div class='log-area'>
            <!-- ログ表示部分 -->
            <table border="1" style='border-collapse: collapse;'>
                <thead>
                    <tr>
                        <th colspan="2" class='white right-white name-date left fixed fixed-date'></th>
                        <th colspan='3' class='white left-white'></th>
                        <th colspan="10" class='green'>研究棟</th>
                        <th colspan="4" class='blue right'>その他</th>
                    </tr>

                    <tr>
                        <th class='table-date white left fixed fixed-date'>日付</th>
                        <th class='table-name white fixed fixed-name'>名前</th>
                        <th class='table-health white bottom'>健康チェック</th>
                        <th class='table-time white red bottom'>その日研究棟に最初に入館した時間</th>
                        <th class='table-time white red bottom'>帰宅のために研究棟から退館した時間</th>
                        <th class='table-cell green bottom'>IN401N</th>
                        <th class='table-cell green bottom'>IN501N</th>
                        <th class='table-cell green bottom'>IN505N</th>
                        <th class='table-cell green bottom'>IN418N（小早川）</th>
                        <th class='table-cell green bottom'>IN419N（早見）</th>
                        <th class='table-cell green bottom'>動物舎A</th>
                        <th class='table-cell green bottom'>動物舎B</th>
                        <th class='table-cell green bottom'>IN409N</th>
                        <th class='table-cell green bottom'>IN412N</th>
                        <th class='table-other green bottom'>その他</th>
                        <th class='table-cell blue bottom'>食堂</th>
                        <th class='table-cell blue bottom'>購買部</th>
                        <th class='table-cell blue bottom'>図書館/LC</th>
                        <th class='table-other blue right bottom'>その他</th>
                    </tr>
                </thead>
                <tbody id='tbody'>

                </tbody>
            </table>
        </div>
        
    </div>
    
    
    <script>
        window.onload = setAllduration;
        let select_duration = document.getElementById('duration');
        let tbody = document.getElementById('tbody');
        select_duration.onchange = changeDuration;  // 表示を変える
        let cells = [];  // 表示を変えた際に以前の表示を消す
        let data_number = 0; // 表示数を管理、これをもとに以前の表示を消す
        let new_logs = []; // 表示するデータ   
        let set_number;     

        const logs = JSON.parse('<?php echo $logs; ?>');

        // 期間が変更されたときの動作
        function changeDuration(){
            new_logs = [];
            for(let i = 0; i < data_number; i++){
                const cell = document.getElementById('cell-' + i);
                cell.remove();  // 表示していた来校ログを削除
            }

            data_number = 0;

            // 変更後の期間を取得
            var changeDuration = select_duration.value;

            // 期間によって関数を切り替え
            if(changeDuration == "all"){
                setAllduration();
            }else if(changeDuration == 'week2'){
                set2weeks();
            }else if(changeDuration == 'month1'){
                set1month();
            }else if(changeDuration == 'year1'){
                set1year();
            }
        }

        // 全期間が選択されたとき
        function setAllduration(){
            set_number = '0';
            new_logs = logs;

            addToList();
            
        }

        // 2週間前が選択されたとき
        function set2weeks(){
            set_number = '1';
            var date_2weeks = new Date();
            date_2weeks.setDate(date_2weeks.getDate()-14); // 2週間前の日付を取得

            logs.forEach((log) => {
                let new_date = new Date(log['date']);
                if(date_2weeks <= new_date){
                    new_logs.push(log);  // 2週間前の日付より後だったもので新しい配列を作成
                }
            })

            addToList();
        }

        // 1ヵ月前が選択されたとき
        function set1month(){
            set_number = '2';
            var date_1month = new Date()
            date_1month.setMonth(date_1month.getMonth()-1) // 1ヵ月前の日付を取得

            logs.forEach((log) => {
                let new_date = new Date(log['date']);
                if(date_1month <= new_date){
                    new_logs.push(log);  // 1ヵ月前の日付より後だったもので新しい配列を作成
                }
            })

            addToList();
        }

        // 1年前が選択されたとき
        function set1year(){
            set_number = '3';
            var date_1year = new Date()
            date_1year.setFullYear(date_1year.getFullYear()-1) // 1年前の日付を取得

            logs.forEach((log) => {
                let new_date = new Date(log['date']);
                if(date_1year <= new_date){
                    new_logs.push(log);  // 1年前の日付より後だったもので新しい配列を作成
                }
            })

            addToList();
        }

        // 表示
        function addToList(){
            new_logs.forEach((log) => {
                // 1行追加
                var cellsTr = document.createElement('tr');
                cellsTr.id = 'cell-' + data_number;
                
                // 日付
                var dateTd = document.createElement('td');
                var dateText = document.createTextNode(log['date']);
                dateTd.className = 'fixed fixed-date white left';

                // 名前
                var nameTd = document.createElement('td');
                var nameText = document.createTextNode(log['name']);
                nameTd.className = 'fixed fixed-name white right';

                // 健康チェック
                var healthTd = document.createElement('td');
                if(log['health'] == '〇'){
                    var healthText = document.createTextNode('〇');
                }

                // 到着時間
                var arrivalTd = document.createElement('td');
                var arrivalText = document.createTextNode(log['arrival_time']);

                // 出立時間
                var departureTd = document.createElement('td');
                var departureText = document.createTextNode(log['departure_time']);

                // 来校場所チェック
                // IN401N
                var IN401Td = document.createElement('td');
                if(log['IN401N'] != null){
                    var IN401Text = document.createTextNode('〇');
                }

                // IN501N
                var IN501Td = document.createElement('td');
                if(log['IN501N'] != null){
                    var IN501Text = document.createTextNode('〇');
                }

                // IN505N
                var IN505Td = document.createElement('td');
                if(log['IN505N'] != null){
                    var IN505Text = document.createTextNode('〇');
                }

                // IN418N
                var IN418Td = document.createElement('td');
                if(log['IN418N'] != null){
                    var IN418Text = document.createTextNode('〇');
                }

                // IN419N
                var IN419Td = document.createElement('td');
                if(log['IN419N'] != null){
                    var IN419Text = document.createTextNode('〇');
                }

                // IN601N
                var IN601Td = document.createElement('td');
                if(log['IN601N'] != null){
                    var IN601Text = document.createTextNode('〇');
                }

                // IN603N
                var IN603Td = document.createElement('td');
                if(log['IN603N'] != null){
                    var IN603Text = document.createTextNode('〇');
                }

                // IN409N
                var IN409Td = document.createElement('td');
                if(log['IN409N'] != null){
                    var IN409Text = document.createTextNode('〇');
                }

                // IN412N
                var IN412Td = document.createElement('td');
                if(log['IN412N'] != null){
                    var IN412Text = document.createTextNode('〇');
                }

                // 医心館その他
                var INotherTd = document.createElement('td');
                if(log['IN_other'] != null){
                    var INotherText = document.createTextNode(log['IN_other']);
                }
                
                // 紫苑館
                var diningTd = document.createElement('td');
                if(log['dining'] != null){
                    var diningText = document.createTextNode('〇');
                }

                // 購買部
                var purchasingTd = document.createElement('td');
                if(log['purchasing'] != null){
                    var purchasingText = document.createTextNode('〇');
                }

                // 図書館/LC
                var libraryTd = document.createElement('td');
                if(log['library'] != null){
                    var libraryText = document.createTextNode('〇');
                }

                // その他その他
                var otherTd = document.createElement('td');
                if(log['other'] != null){
                    var otherText = document.createTextNode(log['other']);
                }

                dateTd.appendChild(dateText);
                cellsTr.appendChild(dateTd);  // 日付

                nameTd.appendChild(nameText);
                cellsTr.appendChild(nameTd);  // 名前

                if(healthText){
                    healthTd.appendChild(healthText);
                }                
                cellsTr.appendChild(healthTd);  // 健康チェック

                arrivalTd.appendChild(arrivalText);
                cellsTr.appendChild(arrivalTd);  // 到着時間

                departureTd.appendChild(departureText);
                cellsTr.appendChild(departureTd);  // 出立時間

                if(IN401Text){
                    IN401Td.appendChild(IN401Text);
                }                
                cellsTr.appendChild(IN401Td);  // IN401
                

                if(IN501Text){
                    IN501Td.appendChild(IN501Text);
                }                
                cellsTr.appendChild(IN501Td);  // IN501

                if(IN505Text){
                    IN505Td.appendChild(IN505Text);
                }                
                cellsTr.appendChild(IN505Td);  // IN505

                if(IN418Text){
                    IN418Td.appendChild(IN418Text);
                }                
                cellsTr.appendChild(IN418Td);  // IN418

                if(IN419Text){
                    IN419Td.appendChild(IN419Text);
                }                
                cellsTr.appendChild(IN419Td);  // IN419

                if(IN601Text){
                    IN601Td.appendChild(IN601Text);
                }                
                cellsTr.appendChild(IN601Td);  // IN601

                if(IN603Text){
                    IN603Td.appendChild(IN603Text);
                }                
                cellsTr.appendChild(IN603Td);  // IN603

                if(IN409Text){
                    IN409Td.appendChild(IN409Text);
                }                
                cellsTr.appendChild(IN409Td);  // IN409

                if(IN412Text){
                    IN412Td.appendChild(IN412Text);
                }                
                cellsTr.appendChild(IN412Td);  // IN412

                if(INotherText){
                    INotherTd.appendChild(INotherText);
                }                
                cellsTr.appendChild(INotherTd);  // INその他

                if(diningText){
                    diningTd.appendChild(diningText);
                }                
                cellsTr.appendChild(diningTd);  // 紫苑館

                if(purchasingText){
                    purchasingTd.appendChild(purchasingText);
                }                
                cellsTr.appendChild(purchasingTd);  // 購買部

                if(libraryText){
                    libraryTd.appendChild(libraryText);
                }                
                cellsTr.appendChild(libraryTd);  // 図書館/LC

                if(otherText){
                    otherTd.appendChild(otherText);
                }                
                cellsTr.appendChild(otherTd);  // その他


                tbody.appendChild(cellsTr);
                data_number++;
            })
        } 

        // ダウンロードボタンを押された場合
        function downloadData(){
            const url = './downloadData.php'; // 通信先
            const req = new XMLHttpRequest(); // 通信用オブジェクト
        
            const data = {logs: new_logs, set_number: set_number};
    
            req.onreadystatechange = function() {
              if(req.readyState == 4 && req.status == 200) {
                alert("ダウンロードできたと思います");
              }else if(req.readyState == 4 && req.status != 200) {
                alert(req.response);
              }
            }
            req.open('POST', url, true);
            req.setRequestHeader('Content-Type', 'application/json');
            req.send(JSON.stringify(data)); // オブジェクトを文字列化して送信
        }

    </script>
</body>
</html>
