<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ひと言掲示板</title>
</head>

<?php
//4-1 データベースへの接続を行う
$dsn = 'mysql:dbname=***;host=localhost';
$user = 'user name';
$password = 'PASSWORD';
                                      //データベース操作で発生したエラーを警告として表示してくれる設定をするための要素
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));


//4-2 データベース内にテーブルを作成する
//INT（整数）,DOUBLE（少数）,VARCHAR（可変長の文字列）,TEXT（文章用の長い文字列）,TIMESTAMP（日付時刻型）
//CREATE TABLE IF NOT EXISTS = もしテーブルが無い場合だけ作成する
$sql = "CREATE TABLE IF NOT EXISTS comment_board"
." ("

//https://qiita.com/ryosuketter/items/713c7046314ecdf1a4a9
. "id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,"
. "name char(32),"
. "comment TEXT,"
. "now_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,"
. "pw char(32)"
.");";
//アロー演算子( -> )は左辺から右辺を取り出す演算子
//query()は指定したSQL文をデータベースに対して発行してくれる役割を持っています
//queryで取得した値は配列で返ってくる
$stmt = $pdo->query($sql);


/*
//4-3 テーブル一覧を表示するコマンドを使って作成が出来たか確認する
$sql ='SHOW TABLES';
$result = $pdo -> query($sql);
foreach ($result as $row){
	echo $row[0];
	echo '<br>';
}
echo "<hr>";


//4-4 テーブルの中身を確認するコマンドを使って、意図した内容のテーブルが作成されているか確認する
$sql ='SHOW CREATE TABLE comment_board';
$result = $pdo -> query($sql);
foreach ($result as $row){
	echo $row[1];
}
echo "<hr>";

*/

//「フォーム内が空の時は動かない」ようにする
if( !empty($_POST['name']) && !empty($_POST['comment']) && !empty($_POST['pass'])) {

  //編集番号指定用ホームが空かどうか確認する
  if( empty($_POST['no'])) {       //空なら新規/////////////////////////////////////

    //4-5 作成したテーブルに、insertを行ってデータを入力する
    $sql = $pdo -> prepare("INSERT INTO comment_board (name, comment,pw) VALUES (:name, :comment, :pw)");
    $sql -> bindParam(':name', $name, PDO::PARAM_STR);
    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
    $sql -> bindParam(':pw', $pw, PDO::PARAM_STR);
    $name = $_POST['name'];
    $comment = $_POST['comment'];
    $pw = $_POST['pass'];
    $sql -> execute();
  }
  else{
    //4-7 入力したデータをupdateによって編集する。
    $newid = $_POST['no'];
    $newname = $_POST['name'];
    $newcom = $_POST['comment'];
    $newpw = $_POST['pass'];
    $date = date('Y-m-d H:i:s');
    $sql = 'update comment_board set name=:name,comment=:comment,pw=:pw,now_date=:date where id=:id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $newname, PDO::PARAM_STR);
    $stmt->bindParam(':comment', $newcom, PDO::PARAM_STR);
    $stmt->bindParam(':id', $newid, PDO::PARAM_INT);
    $stmt->bindParam(':pw', $newpw, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
  }
}



//削除////////////////////////////////////////////////////////////////////////////////////
if( !empty($_POST['delsub']) && !empty($_POST['delpass'])) {
  $delpass = $_POST['delpass'];
  $delid = $_POST['delete'];

  //「パスワードが違います」を表示
  //テーブルにある全てのデータを取得するSQL文を、変数に格納
  $sql = 'SELECT * FROM comment_board';

  //SQL文を実行するコードを、変数に格納
  $stmt = $pdo->query($sql);
  $results = $stmt->fetchAll();//fetchAll()→該当する全てのデータを配列として返す

  //foreach文でデータベースより取得したデータを1行ずつループ処理
  foreach ($results as $row){

    //パスワードの確認
    if($row['id'] == $delid && $row['pw'] != $delpass){
       $error = "パスワードが違います";
       break;
    }
  }

  //削除
  //データを削除するDELETE文を変数に格納する
  $sql = 'delete from comment_board where id=:id AND pw=:pw';

  //値が空のままのSQL文を prepare() にセットし、SQL実行のための準備を行う
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':id',$delid, PDO::PARAM_INT);
  $stmt->bindParam(':pw',$delpass, PDO::PARAM_INT);
  $stmt->execute();

/*MySqlの自動採番の値をリセットする
$sql = 'ALTER TABLE comment_board AUTO_INCREMENT = 1;';
$stmt = $pdo->query($sql);
$stmt->execute();
*/

}



//編集元のテキストを、投稿フォームに表示させる/////////////////////////////////////////////////////////////////
if( !empty($_POST['editsub']) && !empty($_POST['editpass']) ) {
  $editid = $_POST['edit'];
  $editpass = $_POST['editpass'];

  //「パスワードが違います」を表示
  $sql = 'SELECT * FROM comment_board';
  $stmt = $pdo->query($sql);
  $results = $stmt->fetchAll();
  foreach ($results as $row){

    //パスワードの確認
    if($row['id'] == $editid && $row['pw'] != $editpass){
       $error = "パスワードが違います";
       break;
    }
  }


  //編集元のテキストを、投稿フォームに表示させる
  //テーブルにある全てのデータを取得するSQL文を、変数に格納
  $sql = 'SELECT * FROM comment_board';

  //SQL文を実行するコードを、変数に格納
  $stmt = $pdo->query($sql);
  $results = $stmt->fetchAll();//fetchAll()→該当する全てのデータを配列として返す

  //foreach文でデータベースより取得したデータを1行ずつループ処理
  foreach ($results as $row){

    //投稿番号と編集対象番号を比較。イコールの場合はその投稿の「名前」と「コメント」と「番号」を取得
    if($row['id'] == $editid && $row['pw'] == $editpass){
      $editid = $row['id'];
      $editname = $row['name'];
      $editcom = $row['comment'];
    }
  }
}
?>



<h1>ひと言掲示板</h1>
<FONT SIZE="5">この掲示板のテーマ：<B>今日の晩ごはん</B></FONT>
<br>
<h3>【　投稿フォーム　】</h3>
<form action="5-1.php" method="post">
  <div>
    <label for="name">　　　　名前：</label>
    <input id="name" type="text" name="name" value="<?php if(isset($editname)){echo $editname;}?>" placeholder="名前" >
  </div>

  <div>
    <label for="comment">　　コメント：</label>
    <textarea id="comment" name="comment" rows="2" cols="22" placeholder="コメント" ><?php if(isset($editcom)){echo $editcom;}?></textarea>
  </div>

  <div>
    <label for="no"></label>
                   <!type属性をhiddenに変更して見えなくする>
    <input id="no" type="hidden" name="no" value="<?php if(isset($_POST['edit'])){echo $editid;}?>">
  </div>

  <div>
    <label for="pass">　パスワード：</label>
    <input id="pass" type="password" name="pass" value="" placeholder="パスワード" >
    <input type="submit" name="submit" value="送信">
  </div>
</form>

<br>
<h3>【　削除フォーム　】</h3>
<!削除番号指定用フォーム>
<form action="5-1.php" method="post">
  <div>
    <label for="delete">削除対象番号：</label>
    <input id="delete" type="number" name="delete" value="" placeholder="削除対象番号">
  </div>

  <div>
    <label for="delpass">　パスワード：</label>
    <input id="delpass" type="password" name="delpass" value="" placeholder="パスワード">
    <input type="submit" name="delsub" value="削除">
  </div>
</form>

<br>
<h3>【　編集フォーム　】</h3>
<!編集番号指定用フォーム>
<form action="5-1.php" method="post">
  <div>
    <label for="edit">編集対象番号：</label>
    <input id="edit" type="number" name="edit" value="" placeholder="編集対象番号">
  </div>

  <div>
    <label for="editpass">　パスワード：</label>
    <input id="editpass" type="password" name="editpass" value="" placeholder="パスワード">
    <input type="submit" name="editsub" value="編集">
  </div>
</form>



<?php
if(!empty($error)){
  echo "<br>";
  echo "!--------------------!"."<br>".$error."<br>"."!--------------------!";
  echo "<br>";
}
?>



<br>
<FONT SIZE="5"><B>【投稿内容】</B></FONT>
<br>



<?php
//4-6 入力したデータをselectによって表示する
$sql = 'SELECT * FROM comment_board';
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();//fetchAll()→該当する全てのデータを配列として返す
foreach ($results as $row){
	//$rowの中にはテーブルのカラム名が入る
	echo $row['id'].'.';
	echo $row['name'].'「';
	echo $row['comment'].'」';
        echo $row['now_date'].'<br>';
echo "<hr>";
}

?>

</body>
</html>