# Attendance_Management アプリ（本番環境版）

## <<アプリ概要>>  
Attendance_Management アプリは来校ログ管理のためのアプリ です。  
現在私の研究室ではコロナ対策の一環として、来校日時、来校場所の記録を取っています。  
しかしエクセルに直接記入する方式であるため、スマートフォンからは記録しづらく、記録漏れも見られます。  
そこで、来校登録時にはスマートフォン上で見やすく、管理についてはパソコンで見やすいアプリがあれば良いのではと思いました。  
なお、これは本番環境版におけるREADMEです。

## <<アプリ機能>>  

### ホーム画面（Home）について   
・「登録」ボタン：登録画面（Register）へ遷移します。  
・「閲覧」ボタン：閲覧画面（Management）へ遷移します。  
・「設定」ボタン：設定画面（Setting）へ遷移します。パスワードは「password」です。

### 登録画面（Register）について  
#### page1（登録）  
・「ホーム」ボタン：ホーム画面へ戻ります。  
・「学年等を選択してください」：Staff、博士研究員、博士後期課程（D）、博士前期課程2年（M2）、博士前期課程1年（M1）、学部生（B）、研究生、共同研究員、過去メンバーから選んでください。    
  学年等を選択しないと名前を選べません。　  
・「名前を選択してください」：各学年等に登録された名前が表示されます。選んでください。
・日付、来校、下校日時を選択してください。  
・健康チェックを行ってください。
・該当する来校場所がある場合はチェックをつけ、ない場合はその他の欄に記入してください。
・「確認する」ボタン：page2へ遷移します。    
・名前、健康チェック欄、日付（登録忘れ防止のため2021年10月1日は登録できません）が登録されていない場合は次の画面に進めません。  
#### page2（確認）  
・名前や日時、来校場所が正しいか確認します。  
・「戻る」ボタン：page1へ戻ります。  
・「登録する」ボタン：page3へ遷移します。  
#### page3（完了）  
・登録されました。  
・「閲覧」ボタン：閲覧画面へ遷移します。  
・「登録」ボタン：page1へ遷移します。  

### 閲覧画面（Management）について  
・「ホーム」ボタン：ホーム画面へ戻ります。  
・来校ログが日付順に表示されます。  
・「ホーム」ボタン下のドロップダウンメニューでは表示期間の選択ができます。  
・スクロールすると、上2行、左2列が追従します。

### 設定画面（Setting）について
・「現在のメンバー」タブ：Staff、博士研究員、博士後期課程（D）、博士前期課程2年（M2）、博士前期課程1年（M1）、学部生（B）、研究生、共同研究員のメンバーについて変更・削除が行えます。  
・「過去のメンバー」タブ：過去メンバーについて変更・削除が行えます。  
・「メンバー追加」タブ：メンバー追加が行えます。


## <<こだわりポイント>>   
### 1.登録画面と閲覧画面を分ける  
使いづらい要因は登録画面と閲覧画面が同じ場所にあり、新しいセルまでスクロールしなければならないこと、横スクロールとスマートフォンの相性が悪いことにあると考えました。  
また帰宅後PCを開いて登録することが登録忘れに繋がるため、帰宅中車内で来校ログ登録を行うためにはスマートフォンで活用できる必要があると考えました。
### 2.入力箇所を少なくする  
現在のシステムは名前や日付、来校場所を入力する必要があり、時間がかかります。  
そこでできるだけ入力箇所を少なくするために、チェック方式にしました。  
また研究室には50人近く在籍しており、名前一覧から選択することは煩雑さに繋がると考え、予め学年等を選択することで、絞り込むようにしました。  
