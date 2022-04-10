<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes">
    <link rel="icon" href="img_news_00.jpg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://use.fontawesome.com/releases/v5.10.2/css/all.css" rel="stylesheet">
    <title>Setting</title>
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
    $drop_position = '';    

    // テーブルから情報を取得
    // logsは今後の出力の元、オブジェクトが入った配列
    // positionを取得
    try{
        $result = $mysqli->query("SELECT position FROM position");
        while ($row = $result->fetch_assoc()){
            $logs_position[] = $row['position'];
        }
        $result->close();
    }catch(PDOException $e){
        //トランザクション取り消し
        $pdo -> rollBack();
        $errors['error'] = "もう一度やり直してください。";
        print('Error:'.$e->getMessage());
    }
    // 新規追加用ドロップメニュー
    foreach($logs_position as $position){
        $drop_position .= "<option value='{$position}'>{$position}</option>";
    }


    // 名前を取得
    try{
        $result = $mysqli->query("SELECT id, name, position, studentID, year FROM member");
        while ($row = $result->fetch_assoc()){
            $logs_members[] = $row;
        }
        $result->close();
    }catch(PDOException $e){
        //トランザクション取り消し
        $pdo -> rollBack();
        $errors['error'] = "もう一度やり直してください。";
        print('Error:'.$e->getMessage());
    }

    $add_form_number = 0;
    $drop_years = '';
    // 新規追加用ドロップメニュー（年）
    for($i=2008; $i <= intval(date('Y')); $i++){
        $logs_years[] = $i;
        $drop_years .= "<option value='{$i}'>{$i}</option>";
    }
    array_unshift($logs_years, '');
    
    $logs_position = json_encode($logs_position);
    $logs_members = json_encode($logs_members);
    $logs_years = json_encode($logs_years);

    $add_member_op = false;
    // 確認する(btn_confirm)を押した後の処理
    if(isset($_POST['btn_confirm'])){
        $POSTS = $_POST;
        $POST = array_pop($POSTS);
        // POSTされたデータをいれる
        foreach($POSTS as $index => $posts){
            foreach($posts as $number => $post){
                $index = str_replace('add_', '', $index);
                $newcomer_members[$number][$index] = $post;
                // $number→人、$index→キー名
            }
        }
        foreach($newcomer_members as $newcomer){
            $exist_position = false;
            $exist_year = false;
            foreach($newcomer as $infos => $info){
                $_SESSION[$newcomer['name']][$infos] = $info;
                if($infos == "position"){
                    $exist_position = true;
                }
                if($infos == "year"){
                    $exist_year = true;
                }
            }
            if($exist_position == false){
                $_SESSION[$newcomer['name']]['position'] = null;
            }
            if($exist_year == false){
                $_SESSION[$newcomer['name']]['year'] = null;
            }
        }
        $add_number = 1;
        $add_member_op = true;
    }

    // 登録する(btn_submit)を押した後の処理
    if(isset($_POST['btn_submit'])){
        if($_SESSION){
            foreach($_SESSION as $newcomer_name){
                if($newcomer_name['studentID'] == ''){
                    $newcomer_name['studentID'] = NULL;
                }
                if($newcomer_name['year'] == ''){
                    $newcomer_name['year'] = NULL;
                }
                // ここでデータベースに登録する
                try{
                    $stmt = $mysqli -> prepare("INSERT INTO member (name, position, studentID, year) VALUES (?, ?, ?, ?)");
                    $stmt -> bind_param('ssii', $newcomer_name['name'], $newcomer_name['position'], $newcomer_name['studentID'], $newcomer_name['year']);
                    $stmt -> execute();
                    $_SESSION = array();
                    $add_member_op = true;
                }catch(PDOException $e){
                    //トランザクション取り消し
                    $pdo -> rollBack();
                    $errors['error'] = "もう一度やり直してください。";
                    print('Error:'.$e->getMessage());
                }
            }
        }else{
            
        }
    }
?>

<body>
    <!-- タブ表示 -->
    <div class='tabs'>
        <input id='active' type='radio' name='tab_item' onclick='findActivemembers()'checked>
        <label class='tab_item' for='active'>現在のメンバー</label>
        <input id='former' type='radio' name='tab_item'onclick='findFormermembers()'>
        <label class='tab_item former_tab' for='former'>過去のメンバー</label>
        <input id='newcomer' type='radio' name='tab_item' <?= $add_member_op ? 'checked' : '' ?>>
        <label class='tab_item newcomer_tab' for='newcomer'>メンバー追加</label>

        <div class='setting-page-login-button-area'>
            <a class='login-button home management-page-button setting-page-login-botton' href='login.php'><i class="fas fa-home fa-fw"></i>ホーム</a>
        </div>
                        
        <!-- タブ中身 -->
        <!-- 現在のメンバー -->
        <div class='tab_content' id='active_members'></div>
        <!-- 過去のメンバー -->
        <div class='tab_content former_content' id='former_members'>
            <table class='setting-tab' id='table_former' border='1' style='border-collapse: collapse'></table>
        </div>
        <!-- メンバー追加 -->
        <div class='tab_content newcomer_content' id='newcomer_members'>
            <!-- page3 完了画面 -->
            <?php if(isset($_POST['btn_submit']) && count($errors) == 0): ?>
                <?php $_POST = array(); ?>
                <div class='all-add-form-area'>
                    <div class='add-form-area'>
                        <div class='success-message'>
                            登録しました。
                        </div>
                    </div>
                    <a href="setting.php" class='submit-button more-add-button'>さらに追加</a>
                </div>        

            <!-- page2 確認画面 -->
            <?php elseif(isset($_POST['btn_confirm']) && count($errors) == 0): ?>
                <p class='confirm-message'>以下の情報を登録します。</p>
                <form action='' method='post'>
                    <div class='all-add-form-area'>
                        <?php foreach($_SESSION as $newcomer_name){ ?>
                            <div class='add-form-area'>
                                <p class='add-number'><?=h($add_number)?><?php $add_number++?></p>
                                <div class='add-form-area'>
                                    <div class='add-input-area add-input-area-confirm'>
                                        <p class='holder'>Name</p>
                                        <div class='add-input'><?=h($newcomer_name['name'])?></div>
                                    </div>
                                    <div class='add-input-area add-input-area-confirm'>
                                        <p class='holder'>Position</p>
                                        <div class='add-input'><?=h($newcomer_name['position'])?></div>
                                    </div>
                                    <div class='add-input-area add-input-area-confirm'>
                                        <p class='holder'>学籍番号</p>
                                        <div class='add-input'><?=h($newcomer_name['studentID'])?></div>
                                    </div>
                                    <div class='add-input-area add-input-area-confirm'>
                                        <p class='holder'>卒業年</p>
                                        <div class='add-input'><?=h($newcomer_name['year'])?></div>
                                    </div>
                                </div>                                
                            </div>
                        <?php } ?>
                    </div>
                    <div class='login-button-area'>
                        <input type='submit' name='btn_back' class='submit-button' value='戻る'>
                        <input type='submit' name='btn_submit' class='submit-button' value='登録する'>
                    </div>                
            </from>

            <!-- page1 登録画面 -->
            <?php elseif(!isset($errors['error']) || isset($_POST["btn_back"])): ?>
                <?php $add_form = '1'; ?>
                <?php if(count($errors) > 0): ?>
                    <div class='error-message'>
                        <?php 
                            foreach($errors as $value){
                                echo nl2br($value.PHP_EOL);
                            }
                        ?>
                    </div>
                <?php endif; ?>

                <form action='' method='post' id='add_form'>
                    <div class='all-add-form-area' id='all_add_form_area_id'>
                        <div class='add-form-area' name='add_form_area_name[]' id='add_form_area_id_<?php echo $add_form_number ?>'>
                            <p class='add-number' id='add_number_<?php echo $add_form_number ?>'>1</p>
                            <div class='add-div' name='add_div[]'>                            
                                <!-- 名前を記入 -->
                                <div class='add-input-area'>
                                    <p class='holder'>Name</p>
                                    <input type='text' class='content' name='add_name[]' id='add_named_<?php echo $add_form_number ?>' placeholder='name' required>
                                </div>
                                <!-- 学年を選ぶ -->                    
                                <div class='add-input-area'>
                                    <p class='holder'>Position</p>
                                    <select class='content' name='add_position[]' id='add_position_<?php echo $add_form_number ?>' required>
                                        <option value=''>選択してください</option>
                                        <?php 
                                            echo $drop_position; ?>
                                    </select>
                                </div>
                                <!-- 学籍番号を記入 -->
                                <div class='add-input-area'>
                                    <div class='add-holder-area'>
                                        <p class='holder'>学籍番号</p>
                                    </div>                                
                                    <input type='text' class='content' name='add_studentID[]' id='add_studentID_<?php echo $add_form_number ?>' placeholder='学籍番号'>
                                </div>
                                <!-- 卒業年を記入 -->
                                <div class='add-input-area add-input-area-year'>
                                    <div class='add-holder-area'>
                                        <p class='holder'>卒業年</p>
                                    </div>                                
                                    <select class='content' name='add_year[]' id='add_year_<?php echo $add_form_number ?>'>
                                        <option value=''>選択してください</option>
                                        <?php 
                                            echo $drop_years; ?>
                                    </select>
                                </div>       
                            </div>
                        </div>
                    </div>
                    
                    <p class='add-button-area'>
                        <input type='button' value='+' class='add-button' onclick="addMember_add()">
                        <input type='button' value='-' class='add-button' onclick="addMember_disp()">
                    </p>                            
                    <input type='submit' name='btn_confirm' class='submit-button' value='確認する'>
                </form>                            
            <?php endif; ?>
        </div>
    </div>

    <!-- 編集画面 -->
    <input type='checkbox' id='edit_modal'/>
    <label class='edit-overlay' for='edit_modal'>
        <a href='#!'></a>      
        <div class='edit-window'>
            <a href="#!" class="edit-close">×</a>
            <div class='edit-text' id='editlist'>
                <!-- 編集情報 -->
            </div>
        </div>      
    </label>
</body>

<script>
    window.onload = findActivemembers;
    var members = JSON.parse('<?php echo $logs_members; ?>'); //JSONデコード
    const logs_positions = Array(JSON.parse('<?php echo $logs_position; ?>')); //JSONデコード
    const logs_years = Array(JSON.parse('<?php echo $logs_years; ?>'));
    // 現在のメンバー
    const active_members = document.getElementById('active_members');
    // 過去のメンバー     
    const table_former = document.getElementById('table_former');
    // 編集
    let edit_modal = document.getElementById('edit_modal');
    const editArea = document.getElementById('editlist');
    // メンバー追加
    let currentNumber = 1;
    let parent_elements = document.getElementById('all_add_form_area_id');
    let newcomer = document.getElementById('newcomer');
    let active = document.getElementById('active');
    let add_position_number = [];
    add_position_number[0] = document.getElementById('add_position_0');
    if(document.getElementById('add_position_0')){
        add_position_number[0].addEventListener('change', changePosition);
    }    

    // position,yearセット
    let positions = [];
    let years = [];

    logs_positions.forEach((logs_position) => {
        logs_position.forEach((log_position) => {
            positions.push(log_position);
        })
    })
    logs_years.forEach((logs_year) => {
        logs_year.forEach((log_year) => {
            years.push(log_year);
        })
    })
    
    // 現在のメンバー＆過去のメンバー
    // members配列を各position毎に分割
    var members_Staff = [];
    var members_Postdoc = [];
    var members_Doctor = [];
    var members_Master2 = [];
    var members_Master1 = [];
    var members_Bachelor = [];
    var members_Researcher  = [];
    var members_Collab = [];
    var members_Former = [];
    members.forEach((member) => {
        if(member['position'] == 'Staff'){
            members_Staff.push(member);
        }else if(member['position'] == '博士研究員'){
            members_Postdoc.push(member);
        }else if(member['position'] == 'D'){
            members_Doctor.push(member);
        }else if(member['position'] == 'M2'){
            members_Master2.push(member);
        }else if(member['position'] == 'M1'){
            members_Master1.push(member);
        }else if(member['position'] == 'B'){
            members_Bachelor.push(member);
        }else if(member['position'] == '研究生'){
            members_Researcher.push(member);
        }else if(member['position'] == '共同研究員'){
            members_Collab.push(member);
        }else if(member['position'] == '過去メンバー'){
            members_Former.push(member);
        }
    })
    
    // members配列を並び替え
    function array_members_ID(a, b){
        return (a.studentID < b.studentID) ? - 1 : 1;
    }
    members_Doctor.sort(array_members_ID);
    members_Master2.sort(array_members_ID);
    members_Master1.sort(array_members_ID);
    members_Bachelor.sort(array_members_ID);
    members_Former.sort((a, b) => b.year - a.year);

    // positionによって入力制限
    // 学年が変更されたときの動作
    function changePosition(event){
        // 変更後の学年を取得
        const str = event.currentTarget.id;
        let now_id_number = '';
        if(str == 'position'){
            now_id_number = str;
        }else{
            now_id_number = str.replace('add_position_', '');
        }// idの番号を取得
        const changePosition = event.currentTarget.value; // position名を取得

        // 学籍番号：D,M2,M1,B4は任意、他は無効
        if(changePosition == "D" || changePosition == "M2" || changePosition == "M1" || changePosition == "B"){
            setNoDisableID(now_id_number);
        }else{
            setDisableID(now_id_number);
        }
        // 卒業年：過去メンバーは必須、他は無効
        if(changePosition == "過去メンバー"){
            setRequiredYear(now_id_number);
        }else{
            setDisableYear(now_id_number);
        }
    }

    // 学籍番号について
    function setNoDisableID(id_number){
        let now_studentID_id = '';
        if(id_number == 'position'){
            now_studentID_id = 'edit-studentID';
        }else{
            now_studentID_id = 'add_studentID_' + id_number;
        }
        document.getElementById(now_studentID_id).disabled = false;
    }
    function setDisableID(id_number){
        let now_studentID_id = '';
        if(id_number == 'position'){
            now_studentID_id = 'edit-studentID'
        }else{
            now_studentID_id = 'add_studentID_' + id_number;
        }
        document.getElementById(now_studentID_id).disabled = true;
    }
    // 卒業年について
    function setRequiredYear(id_number){
        let now_year_id = '';
        if(id_number == 'position'){
            now_year_id = 'edit-year';
        }else{
            now_year_id = 'add_year_' + id_number;
        }
        document.getElementById(now_year_id).required = true;
        document.getElementById(now_year_id).disabled = false;
    }
    function setDisableYear(id_number){
        let now_year_id = '';
        if(id_number == 'position'){
            now_year_id = 'edit-year';
        }else{
            now_year_id = 'add_year_' + id_number;
        }
        document.getElementById(now_year_id).required = false;
        document.getElementById(now_year_id).disabled = true;
    }

    // 表示する
    function addToList(new_members, table){
        let new_number = 1;

        new_members.forEach((member) => {
            // 1行追加
            const cellsTr = document.createElement('tr');
            cellsTr.id = 'cell-' + member.id;

            // 番号
            const numberTd = document.createElement('td');
            numberTd.className = 'numberTd';
            const numberDiv = document.createElement('div');
            numberDiv.innerText = new_number;

            // 名前
            const nameTd = document.createElement('td');
            nameTd.className = 'nameTd';
            const nameDiv = document.createElement('div');
            nameDiv.innerText = member.name;

            // 編集ボタン
            const editTd = document.createElement('td');
            editTd.className = 'buttonTd';
            const editForm = document.createElement('form'); // 編集ボタン
            editForm.className = 'edit-show-button';
            editForm.action = '#edit-modal'
            editForm.method = 'post';
        
            const editA = document.createElement('a'); 
            editA.href = "#edit-modal";
            editA.setAttribute('name', 'edit_button');
            editA.onclick = function() {
                edit_modal.checked = true;
                showEditData(member.id, member.name, member.position, member.studentID, member.year, new_members);
            }
        
            const editI = document.createElement('i'); // 編集ボタンアイコン
            editI.className = 'fas fa-edit worksicon fa-fw'; 
            
            // 表示させる
            numberTd.appendChild(numberDiv);
            cellsTr.appendChild(numberTd); // 番号

            nameTd.appendChild(nameDiv);
            cellsTr.appendChild(nameTd);  // 名前

            editTd.appendChild(editForm);
            editForm.appendChild(editA);
            editA.appendChild(editI); 
            cellsTr.appendChild(editTd); // 編集ボタン
            
            table.appendChild(cellsTr);
            new_number ++;
        })             
    }

    // 現在のメンバーを探す
    function findActivemembers(){
        active_members.innerHTML ='';
       
        const staffDiv = document.createElement('div');
        staffDiv.className = 'position_name';
        staffDiv.innerText = 'Staff';

        const staffTable = document.createElement('table');
        staffTable.className = 'setting-tab';
        staffTable.border = '1';
        staffTable.style = "border-collapse: collapse";
        
        active_members.appendChild(staffDiv);
        active_members.appendChild(staffTable);
        addToList(members_Staff, staffTable);

        // 博士研究員      
        const postdocDiv = document.createElement('div');
        postdocDiv.className = 'position_name';
        postdocDiv.innerText = '博士研究員';

        const postdocTable = document.createElement('table');
        postdocTable.className = 'setting-tab';
        postdocTable.border = '1';
        postdocTable.style = "border-collapse: collapse";
        
        active_members.appendChild(postdocDiv);
        active_members.appendChild(postdocTable);
        addToList(members_Postdoc, postdocTable);

        // Doctor      
        const doctorDiv = document.createElement('div');
        doctorDiv.className = 'position_name';
        doctorDiv.innerText = '博士後期課程';

        const doctorTable = document.createElement('table');
        doctorTable.className = 'setting-tab';
        doctorTable.border = '1';
        doctorTable.style = "border-collapse: collapse";
        
        active_members.appendChild(doctorDiv);
        active_members.appendChild(doctorTable);
        addToList(members_Doctor, doctorTable);

        // Master2      
        const master2Div = document.createElement('div');
        master2Div.className = 'position_name';
        master2Div.innerText = '博士前期課程2年生';

        const master2Table = document.createElement('table');
        master2Table.className = 'setting-tab';
        master2Table.border = '1';
        master2Table.style = "border-collapse: collapse";
        
        active_members.appendChild(master2Div);
        active_members.appendChild(master2Table);
        addToList(members_Master2, master2Table);

        // Master1      
        const master1Div = document.createElement('div');
        master1Div.className = 'position_name';
        master1Div.innerText = '博士前期課程1年生';

        const master1Table = document.createElement('table');
        master1Table.className = 'setting-tab';
        master1Table.border = '1';
        master1Table.style = "border-collapse: collapse";
        
        active_members.appendChild(master1Div);
        active_members.appendChild(master1Table);
        addToList(members_Master1, master1Table);

        // 学部生      
        const bachelorDiv = document.createElement('div');
        bachelorDiv.className = 'position_name';
        bachelorDiv.innerText = '学部生';

        const bachelorTable = document.createElement('table');
        bachelorTable.className = 'setting-tab';
        bachelorTable.border = '1';
        bachelorTable.style = "border-collapse: collapse";
        
        active_members.appendChild(bachelorDiv);
        active_members.appendChild(bachelorTable);
        addToList(members_Bachelor, bachelorTable);

        // 研究生      
        const researcherDiv = document.createElement('div');
        researcherDiv.className = 'position_name';
        researcherDiv.innerText = '研究生';

        const researcherTable = document.createElement('table');
        researcherTable.className = 'setting-tab';
        researcherTable.border = '1';
        researcherTable.style = "border-collapse: collapse";
        
        active_members.appendChild(researcherDiv);
        active_members.appendChild(researcherTable);
        addToList(members_Researcher, researcherTable);

        // 共同研究員     
        const collabDiv = document.createElement('div');
        collabDiv.className = 'position_name';
        collabDiv.innerText = '共同研究員';

        const collabTable = document.createElement('table');
        collabTable.className = 'setting-tab';
        collabTable.border = '1';
        collabTable.style = "border-collapse: collapse";
        
        active_members.appendChild(collabDiv);
        active_members.appendChild(collabTable);
        addToList(members_Collab, collabTable);
    }

    // 過去のメンバーを探す
    function findFormermembers(){
        table_former.innerHTML ='';  
        
        addToList(members_Former, table_former);
    }

    // 編集画面表示
    function showEditData(id, name, position, studentID, year, new_members){
        editArea.innerHTML = ''; // 複数表示されるのを防ぐ
        const edit_id = id;
        const now_position = position;
        const now_year = year;

        const memberDiv = document.createElement('div');
        memberDiv.className = 'edit-text';
        memberDiv.id = edit_id;

        const nameP = document.createElement('p');
        nameP.className = 'holder';
        nameP.innerText = 'Name';

        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.className = 'content';
        nameInput.id = 'edit-name';
        nameInput.value= name;

        const positionP = document.createElement('p');
        positionP.className = 'holder';
        positionP.innerText = 'Position';

        const positionSelect = document.createElement('select');
        positionSelect.className = 'content';
        positionSelect.name = 'position';
        positionSelect.id = 'position';
        
        positions.forEach((position) => {
            var positionOption = document.createElement('option');
            positionOption.value = position;
            positionOption.text = position;

            // 現在のpositionを初期値とする
            if(position == now_position){
                positionOption.setAttribute('selected', 'selected');
            }
                
            positionSelect.appendChild(positionOption);
        });

        if(positionSelect){
            positionSelect.addEventListener('change', changePosition);
        }
        

        const studentIDP = document.createElement('p');
        studentIDP.className = 'holder';
        studentIDP.innerText = '学籍番号';

        const studentIDInput = document.createElement('input');
        studentIDInput.type = 'text';
        studentIDInput.className = 'content';
        studentIDInput.id = 'edit-studentID';
        studentIDInput.value = studentID;
        studentIDInput.disabled = false;
        // positionによって入力制限
        if(!(position == 'D' || position == 'M2' || position == 'M1' || position == 'B')){
            studentIDInput.disabled = true;
        }

        const yearP = document.createElement('p');
        yearP.className = 'holder';
        yearP.innerText = '卒業年';

        const yearSelect = document.createElement('select');
        yearSelect.className = 'content';
        yearSelect.name = 'position';
        yearSelect.id = 'edit-year';
        yearSelect.required = true;
        yearSelect.disabled = false;
        
        years.forEach((year) => {
            var yearOption = document.createElement('option');
            yearOption.value = year;
            yearOption.text = year;

            // 現在の卒業年を初期値とする
            if(year == now_year){
                yearOption.setAttribute('selected', 'selected');
            }
                
            yearSelect.appendChild(yearOption);
        });
        // positionによって入力制限
        if(!(position == '過去メンバー')){
            yearSelect.required = false;
            yearSelect.disabled = true;
        }

        const buttonP = document.createElement('p');
        buttonP.className = 'buttonP';
        
        const editInput = document.createElement('input');
        editInput.type = 'button';
        editInput.className = 'submit-button edit-button';
        editInput.value = 'Edit';
        editInput.onclick = function(){
            edit(edit_id, nameInput.value, positionSelect.value, studentIDInput.value, yearSelect.value, new_members);
        }

        const delButton = document.createElement('button'); // 削除ボタン
        delButton.className = 'del-button';
        delButton.onclick = function() {
          disp(edit_id, new_members);
        }
        
        const delI = document.createElement('i'); // 削除ボタンアイコン
        delI.className = 'fas fa-trash worksicon fa-fw';

        memberDiv.appendChild(nameP);
        memberDiv.appendChild(nameInput);
        memberDiv.appendChild(positionP);
        memberDiv.appendChild(positionSelect);
        memberDiv.appendChild(studentIDP);
        memberDiv.appendChild(studentIDInput);
        memberDiv.appendChild(yearP);
        memberDiv.appendChild(yearSelect);
        buttonP.appendChild(editInput);
        delButton.appendChild(delI);
        buttonP.appendChild(delButton);
        memberDiv.appendChild(buttonP);
        editArea.appendChild(memberDiv);
    }

    // 編集
    function edit(edit_id, name, position, studentID, year, new_members){
        let goedit = true;
        if(name != '' && position != ''){
            if(position == 'Staff' || position == '博士研究員' || position == '研究生'){
                studentID = null;
                year = null;
            }else if(position == 'D' || position == 'M2' || position == 'M1' || position == 'B'){
                if(!(studentID == '')){
                    year = null;
                }else{
                    goedit = false;
                    alert('学籍番号を記入してください');
                }
            }else{
                if(!(year == '')){
                    studentID = null;
                }else{
                    goedit = false;
                    alert('卒業年を記入してください');
                }
            }
            if(goedit){
                if(confirm('これで登録して良いですか？') == true){
                    const url = './editData.php'; // 通信先
                    const req = new XMLHttpRequest(); // 通信用オブジェクト
            
                    if(studentID == ''){
                        studentID = null;
                    }
                    if(year == ''){
                        year = null;
                    }
                    const data = {id: parseInt(edit_id, 10), name: name, position: position, studentID: parseInt(studentID, 10), year: parseInt(year, 10)};
    
                    req.onreadystatechange = function() {
                        if(req.readyState == 4 && req.status == 200) {
                            alert("更新しました");
                            // 更新したら、このページ上のデータも更新する                    
                            new_members.forEach((member) =>{
                                if(member['id'] == edit_id){
                                    member['name'] = name;
                                    member['position'] = position;
                                    member['studentID'] = studentID;
                                    member['year'] = year;
                                }
                            })
                            // モーダルウィンドウを閉じる
                            edit_modal.checked = false;
                            // 表示を変更
                            const edit_member = document.getElementById('cell-' + edit_id);
                            const edit_element = edit_member.querySelector('td:nth-child(2)');
                            const edit_name = edit_element.firstElementChild;
                            edit_name.textContent = name;
                        }else if(req.readyState == 4 && req.status != 200) {
                            alert(req.response);
                        }
                    }
                req.open('POST', url, true);
                req.setRequestHeader('Content-Type', 'application/json');
                req.send(JSON.stringify(data)); // オブジェクトを文字列化して送信
                location.reload();
                }else{
                    alert('キャンセルが押されました。');
                }
            }            
        }else{
            alert('名前もしくはpositionが空欄です。');
        }        
    }

    // 削除
    function disp(edit_id, new_members){
        if(confirm('該当人物を削除します。データは復元できませんがよろしいですか？') == true){
            const url = './dispData.php'; // 通信先
            const req = new XMLHttpRequest(); // 通信用オブジェクト
            const data = {id: parseInt(edit_id, 10)};

            req.onreadystatechange = function() {
                if(req.readyState == 4 && req.status == 200) {
                    alert("削除しました");
                    // 削除したら、このページ上のデータからも削除する                    
                    var result = new_members.filter((member) => {
                        return (member.id != edit_id);
                    });
                    new_members = result;
                    // モーダルウィンドウを閉じる
                    edit_modal.checked = false;
                    // 該当人物を画面から削除
                    const disp_member = document.getElementById('cell-' + edit_id);
                    disp_member.remove(); 
                }else if(req.readyState == 4 && req.status != 200) {
                   alert(req.response);
                }
            }
            req.open('POST', url, true);
            req.setRequestHeader('Content-Type', 'application/json');
            req.send(JSON.stringify(data)); // オブジェクトを文字列化して送信
        }else{
            alert('キャンセルが押されました。');
        }   
    }
       
    // メンバー追加
    // フォーム追加
    function addMember_add(){        
        currentNumber++; // フォーム番号
        const new_id_number = currentNumber - 1 ; // このフォームでのid番号 
        const formerNumber = currentNumber - 2; // ひとつ前のフォーム（コピーしたフォーム）のid番号
        // 要素をコピーする
        let copied = parent_elements.firstElementChild.cloneNode(true);
        copied.id = 'add_form_area_id_' + new_id_number; // コピーした要素のidを変更
        // フォーム番号を変更する
        var firstChildP = copied.firstChild.nextElementSibling;
        firstChildP.innerHTML = currentNumber; // フォーム番号を変更
        copied.firstChild = firstChildP; // フォーム番号を変更した要素を最初の子要素に代入
        // コピーしてフォーム番号を変更した要素を親要素の一番最後の子要素にする
        parent_elements.appendChild(copied);
        // positionのnameを取得する
        const add_position = '#add_position_' + formerNumber;        
        var copied_position_names = document.getElementsByName('add_position[]'); // 一度name属性を取得して、最後の要素のidを書き換える
        // positionのidを変更する
        const new_position_id = 'add_position_' + new_id_number; // 新しいpositionのid、文字＋計算はできない
        copied_position_names[(copied_position_names.length)-1].id = new_position_id; // positionのidを変更
        // positionのvalueに基づいて入力制限をする
        add_position_number.push(document.getElementById(new_position_id)); 
        if(add_position_number[new_id_number]){
            add_position_number[new_id_number].addEventListener('change', changePosition);
        }
        // nameのidを変更する、初期化
        const add_name = '#add_name_' + formerNumber;        
        var copied_name_names = document.getElementsByName('add_name[]'); // 一度name属性を取得して、最後の要素のidを書き換える
        const new_name_id = 'add_name_' + new_id_number; // 新しい学籍番号のid
        copied_name_names[(copied_name_names.length)-1].id = new_name_id;
        copied_name_names[(copied_name_names.length)-1].value = '';
        // 学籍番号のidを変更する
        const add_studentID = '#add_studentID_' + formerNumber;        
        var copied_studentID_names = document.getElementsByName('add_studentID[]'); // 一度name属性を取得して、最後の要素のidを書き換える
        const new_studentID_id = 'add_studentID_' + new_id_number; // 新しい学籍番号のid
        copied_studentID_names[(copied_studentID_names.length)-1].id = new_studentID_id;
        copied_studentID_names[(copied_studentID_names.length)-1].required = false;
        copied_studentID_names[(copied_studentID_names.length)-1].disabled = false;
        copied_studentID_names[(copied_studentID_names.length)-1].value = '';
        // 卒業年のidを変更する
        const add_year = '#add_year_' + formerNumber;        
        var copied_year_names = document.getElementsByName('add_year[]'); // 一度name属性を取得して、最後の要素のidを書き換える
        const new_year_id = 'add_year_' + new_id_number; // 新しい学籍番号のid
        copied_year_names[(copied_year_names.length)-1].id = new_year_id;
        copied_year_names[(copied_year_names.length)-1].required = false;
        copied_year_names[(copied_year_names.length)-1].disabled = false;
        copied_year_names[(copied_year_names.length)-1].value = '';
    }
    // フォーム削除
    function addMember_disp(){
        if(currentNumber > 1){
            currentNumber--;
            const remove_element = parent_elements.children[currentNumber];
            parent_elements.removeChild(remove_element);
            add_position_number.pop();
        }else{
            alert('フォームが1つの場合には削除できません。');
        }
    }
    
</script>
</html>
