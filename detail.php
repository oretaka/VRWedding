<?php
  // セッションを使用。
  session_start();
  // DAOクラスの読み込み
  require_once("backphp/Dao.class.php");

  //                    ここから　変数宣言
  
  // 現在ログイン中のユーザーID。ログイン中のユーザー情報がない場合、-1がセットされる。
  $id = -1;
  // 男の名前
  $maleName = "";
  // 女の名前
  $femaleName = "";
  // 結婚式の日付
  $date = "2000/1/1";
  // 結婚式の時間
  $time = "00:00:00";
  // 結婚式の実施曜日(算出結果)
  $dotw = "";
  // 結婚式の備考
  $note = "";
  // 結婚式ID。
  $weddingID = 0;
  // 参加不参加ボタン表示部分のHTML。ただし、ログインしていない場合はログインを促すメッセージを表示する。
  $weddingYN = "";

  // 結婚式の参加結果。　1:結婚式の参加正常完了　2:システムエラー発生
  $result = 0;
  // 該当の結婚式の開始時間前の場合、trueとなるフラグ。開始時間を過ぎている場合はfalse。
  $weddingNotReady = false;

  // 比較用結婚式の開始日時。
  $datetime = null;
  // 比較用現在日時。
  $nowDatetime = null;

  // DBのDAOオブジェクト。
  $dao = new DAO();
  // DBからの取得結果を格納する配列。
  $dbres = null;

  //                    ここまで　変数宣言

  // セッションから、ログイン中のユーザーIDが取得できる場合は取得する。
  if( isset($_SESSION["id"]) )
  {
    $id = intval( $_SESSION["id"] );
  }

  // 一覧表示ページから送信された募集情報が取得できる場合、取得する。
  // POSTに値がある場合は、POSTからの取得を優先。ない場合は、セッションからの取得を試みる。
  // まだセッションに値を保存していない場合、セッションに値をセットし、更新させて結婚式詳細情報が消えないようにする。
  if( isset($_POST["weddingID"]) )
  {
    $weddingID = intval($_POST["weddingID"]);
    $_SESSION["weddingID"] = $_POST["weddingID"];
  }
  else if( isset($_SESSION["weddingID"]) )
  {
    $weddingID = intval($_SESSION["weddingID"]);
  }

  if( isset($_POST["maleName"]) )
  {
    $maleName = $_POST["maleName"];
    $_SESSION["maleName"] = $_POST["maleName"];
  }
  else if( isset($_SESSION["maleName"]) )
  {
    $maleName = $_SESSION["maleName"];
  }

  if( isset($_POST["femaleName"]) )
  {
    $femaleName = $_POST["femaleName"];
    $_SESSION["femaleName"] = $_POST["femaleName"];
  }
  else if( isset($_SESSION["femaleName"]) )
  {
    $femaleName = $_SESSION["femaleName"];
  }

  if( isset($_POST["date"]) )
  {
    $date = $_POST["date"];
    $_SESSION["date"] = $_POST["date"];
  }
  else if( isset($_SESSION["date"]) )
  {
    $date = $_SESSION["date"];
  }
  // 実施日付が取得できた場合、その日付から曜日を算出する。
  $dotw = date('l',strtotime($date));

  if( isset($_POST["time"]) )
  {
    $time = $_POST["time"];
    $_SESSION["time"] = $_POST["time"];
  }
  else if( isset($_SESSION["time"]) )
  {
    $time = $_SESSION["time"];
  }

  if( isset($_POST["note"]) )
  {
    $note = $_POST["note"];
    $_SESSION["note"] = $_POST["note"];
  }
  else if( isset($_SESSION["note"]) )
  {
    $note = $_SESSION["note"];
  }


  // 結婚式に参加済みの場合は、参加したことを示すメッセージを表示させる。
  if( isset($_GET["result"]) )
  {
    $result = intval($_GET["result"]);
    $weddingYN .= '<p>';
    switch($result)
    {
      case 1:
        $weddingYN .= "この結婚式の参加登録が完了しました。";
        break;
      case 2:
        $weddingYN .= "申し訳ありません。システムエラーが発生しました。";
        break;
    }
    $weddingYN .= "</p>";
  }
  // 現在いずれかのユーザーでログイン中の場合で、まだ結婚式に参加していない場合、「参加」「不参加」のボタンを表示する。
  else if($id != -1)
  {

    // 現在ログイン中のユーザーが該当の結婚式に参加済みかチェック。既に参加している場合、その旨のメッセージを表示させる。
    $dbres = $dao->select("invite",null,["weddingID = ?","userID = ?"],[$weddingID,$id],"AND");
    if(empty($dbres))
    {
      // まだ該当の結婚式には参加していない場合
      $weddingYN .= '<form action="backphp/detail_db.php" method="post">';
      $weddingYN .= '<input type="hidden" name="weddingID" value="' . $weddingID . '">';
      $weddingYN .= '<input type="hidden" name="userID" value="' . $id . '">';
      $weddingYN .= '<input type="submit" name="attend" class="btn btn-5" value="参加する"></form>';
    }
    else
    {
      	// 既に該当の結婚式に参加している場合、結婚式の開始時間前ならばルームに入るボタンを無効に、開始時間後ならばルームに入るボタンを有効にする。
      	$datetime = new Datetime( str_replace('/', '-', $date) . ' ' . str_replace('～', ':00', $time) );
      	$nowDatetime = new Datetime();

      	if($nowDatetime < $datetime)
      	{
      		// 現在時刻がまだ開始時間を過ぎていないなら、入室不可とする。
      		$weddingNotReady = true;
      	}
    	$weddingYN .= '<button type="button" name="entry" value="entry"';
    	if($weddingNotReady)
    	{
    		$weddingYN .= ' disabled';
    	}
    	$weddingYN .= '>ルームに入る</button>';	
    	if($weddingNotReady)
    	{
    		// まだ開始時間を過ぎていない場合、その旨のメッセージを画面に表示させる。
    		$weddingYN .= '<p>開始時間までしばらくお待ちください。</p>';
    	}
    }
 
  }
  else
  {
    // どのユーザーもログインしていない場合は、ログインを促すメッセージを表示させる。
    $weddingYN .= '<p>まだログインしていません。結婚式に参加するにはログインしてください。</p>';
  }

?>

<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title>html/css bridal shower invite</title>
  
  
  
      <link rel="stylesheet" href="css/detail.css">
       <!-- ボタンの整形用css -->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
      <link rel="stylesheet" href="css/button.css">

  
</head>

<body>

 <link href="https://fonts.googleapis.com/css?family=Oswald|Zilla+Slab:300i" rel="stylesheet">
<div id="container">
  <div class="floral-background">
    <div class="text-block">
      <div class="words">
        <h3 class="zilla less-space">please join us for a</h3>
        <h1 class="oswald uppercase some-space bigger-text">Bridal Shower</h1>
        <h4 class="zilla less-space">in honor<br> of the bride-to-be</h4>
        <h2 class="smallname oswald uppercase some-space"><span class="maleName"><?php echo $maleName; ?></span> <span class="heart">♡</span> <span class="femaleName"><?php echo $femaleName; ?></span></h2>
        <h3 class="zilla less-space"><?php echo $dotw . ", " . $date . " at " . $time; ?></h3>
        <h3 class="smallnote zilla less-space"><?php echo $note; ?></h3>
        <!-- 参加・不参加ボタンの表示エリア -->
        <h4 class="zilla less-space"><?php echo $weddingYN; ?></h4>
        <button type="button" name="home" class="btn btn-5" onclick="location.href='index.php';">HOME</button>
        
      </div></div>
  </div>
</div>
  
  

</body>

</html>
