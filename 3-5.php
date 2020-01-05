<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ひと言掲示板</title>
</head>

<body>
<?php
$filename = "3-5.txt";
$nofile = "3-5no.txt";
date_default_timezone_set('Asia/Tokyo');// タイムゾーン設定

//「フォーム内が空の時は動かない」ようにする
if( !empty($_POST['name']) && !empty($_POST['comment']) && !empty($_POST['pass'])) {

  $now_date = date("Y-m-d H:i:s");  // 書き込み日時を取得
  $comment=$_POST['comment'];       //コメントを習得
  $name=$_POST['name'];             //名前を習得
  $pw=$_POST['pass'];             //パスワードを習得

  //編集番号指定用ホームが空かどうか確認する
  if( empty($_POST['no'])) {       //空なら新規/////////////////////////////////////


      //投稿番号を別ファイルから習得
      $num = (int)count(file($nofile))+1;
      file_put_contents($nofile, $num. PHP_EOL, FILE_APPEND);//PHP_EOL=改行、FILE_APPEND=追記

      // 書き込むデータを作成
      $data=$num."<>".$name."<>".$comment."<>".$now_date."<>".$pw."<>"."\n";

      //ファイルへ追加で書き込まれるようにする
      $fp = fopen($filename, 'a');

      //書き込み
      fwrite($fp, $data);
      fclose($fp);
    

  }else{  //編集機能//空じゃなかった時////////////////////////////////////////////////////
    $editno = (int)$_POST['no'];
    $newcom = file($filename);

      //テキストファイルの中身を空にする
      $fp=fopen($filename,"w");

      //先ほどの配列の要素数（＝行数）だけループさせる
      foreach ($newcom as $n){

        //区切り文字「<>」で分割
        $newdata = explode("<>",$n);

        //投稿番号と編集対象番号を比較。一致した時のみ、編集のフォームから送信された値と差し替える。
        if($newdata[0] == $editno){

           // 書き込むデータを作成
           $ndata=$editno."<>".$name."<>".$comment."<>".$now_date."<>".$pw."<>"."\n";

           //編集のフォームから送信された値と差し替えて上書き
           fwrite($fp, $ndata);
        }
        else{
           //一致しなかったところはそのまま書き込む
           fwrite($fp,$n);
        }
      }
    fclose($fp);
  }
}

//削除用//////////////////////////////////////////////////////////////////////////////
if( !empty($_POST['delsub']) && !empty($_POST['delpass'])) {
  $delete = (int)$_POST['delete'];
  $delpass = $_POST['delpass'];
  $delcon = file($filename);
  
  //「パスワードが違います」を表示
  foreach ($delcon as $d){
    $deldata = explode("<>",$d);
    if($deldata[0] == $delete && $delpass != $deldata[4]){
         echo "パスワードが違います";
         break;
    }
  }

    //テキストファイルの中身を空にする
    $fp=fopen($filename,"w");
    fclose($fp);

    //先ほどの配列の要素数（＝行数）だけループさせる
    foreach ($delcon as $d){

      //区切り文字「<>」で分割
      $deldata = explode("<>",$d);
        
      //パスワードを確認
      if($delpass == $deldata[4]){

        //投稿番号と削除対象番号を比較。等しくない場合は、ファイルに追加書き込みを行う
        if($deldata[0] != $delete){
           file_put_contents($filename, $d, FILE_APPEND);
        }
      }
      
      //パスワードが違ったらそのまま書き込む
      else{
        file_put_contents($filename, $d, FILE_APPEND);
      }
    }
}


//編集用//////////////////////////////////////////////////////////////////////////////
if( !empty($_POST['editsub']) ) {
  $edit = (int)$_POST['edit'];
  $editpass = $_POST['editpass'];
  $editcon = file($filename);

  //「パスワードが違います」を表示
  foreach ($editcon as $e){
    $editdata = explode("<>",$e);
    if($editdata[0] == $edit && $editpass != $editdata[4]){
         echo "パスワードが違います";
         break;
    }
  }

  //先ほどの配列の要素数（＝行数）だけループさせる
  foreach ($editcon as $e){

    //区切り文字「<>」で分割
    $editdata = explode("<>",$e);

    //パスワードを確認
    if($editpass == $editdata[4]){

      //投稿番号と編集対象番号を比較。イコールの場合はその投稿の「名前」と「コメント」と「番号」を取得
      if($editdata[0] == $edit){
        $editno = $editdata[0];
        $editname = $editdata[1];
        $editcom = $editdata[2];
      }
    }
  }
}
//////////////////////////////////////////////////////////////////////////////////////////////

?>


<h1>ひと言掲示板</h1>
<FONT SIZE="5">この掲示板のテーマ：<B>今日の晩ごはん</B></FONT>
<form action="3-5.php" method="post">
  <div>
    <label for="name">　　　　名前：</label>
    <input id="name" type="text" name="name" value="<?php if(isset($editname)){echo $editname;}?>">
  </div>

  <div>
    <label for="comment">　　コメント：</label>
    <textarea id="comment" name="comment" rows="2" cols="22"><?php if(isset($editcom)){echo $editcom;}?></textarea>
  </div>

  <div>
    <label for="no"></label>
                   <!type属性をhiddenに変更して見えなくする>
    <input id="no" type="hidden" name="no" value="<?php if(isset($_POST['edit'])){echo $editno;}?>">
  </div>

  <div>
    <label for="pass">　パスワード：</label>
    <input id="pass" type="password" name="pass" value="">
    <input type="submit" name="submit" value="送信">
  </div>
</form>

<br>
<!削除番号指定用フォーム>
<form action="3-5.php" method="post">
  <div>
    <label for="delete">削除対象番号：</label>
    <input id="delete" type="number" name="delete" value="">
  </div>

  <div>
    <label for="delpass">　パスワード：</label>
    <input id="delpass" type="password" name="delpass" value="">
    <input type="submit" name="delsub" value="削除">
  </div>
</form>

<br>
<!編集番号指定用フォーム>
<form action="3-5.php" method="post">
  <div>
    <label for="edit">編集対象番号：</label>
    <input id="edit" type="number" name="edit" value="">
  </div>

  <div>
    <label for="editpass">　パスワード：</label>
    <input id="editpass" type="password" name="editpass" value="">
    <input type="submit" name="editsub" value="編集">
  </div>
</form>
<br>
<br>


<?php
//ファイルを1行ずつ読み込み、配列変数に代入する
$lines = file($filename); 

//ファイルを読み込んだ配列を、配列の数（＝行数）だけループさせる
foreach ($lines as $l) {

    //区切り文字「<>」で分割し、それぞれの値を取得
    $get_data = explode("<>",$l);

    //上記で取得した値をecho等を用いて表示
    echo $get_data[0]." ".$get_data[1]." ".$get_data[2]." ".$get_data[3]." "."<br>";
}

//https://gray-code.com/php/make-the-board-vol5/
//https://teratail.com/questions/198287?link=qa_related_pc
?>

</body>
</html>