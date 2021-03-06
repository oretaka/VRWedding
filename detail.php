<?php
  // セッションを使用。
  session_start();
  // DAOクラスの読み込み
  require_once("backphp/Dao.class.php");
  // 時刻チェッククラスの読み込み
  require_once("backphp/timecheck.php");

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
  // ログインボタン表示部分のHTML。ログインする必要がある場合、画面に自動的に表示される。
  $loginHTML = "";

  // 結婚式の参加結果。　1:結婚式の参加正常完了　2:システムエラー発生
  $result = 0;
  // 該当の結婚式の開始時間前の場合、trueとなるフラグ。開始時間を過ぎている場合はfalse。
  $weddingNotReady = false;
  // 該当の結婚式が開催当日以降の場合、trueとなるフラグ。まだ開催日当日を過ぎていない場合はfalse。
  $weddingFinished = false;

  // 比較用結婚式の開始日時。
  $datetime = null;
  // 比較用現在日時。
  $nowDatetime = null;
  // 比較用の結婚式終了日時。
  $finishTime = null;

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
    $weddingYN .= '<p class="smallinfo">';
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
    $datetime = new Datetime( str_replace('/', '-', $date) . ' ' . str_replace('～', ':00', $time) );
    // 結婚式の入室締切時刻を格納。
    $finishTime = new Datetime( $datetime->format('Y-m-d') . ' ' . '00:00:00' );
    $finishTime->modify('+1 days');
    $nowDatetime = new Datetime();
    if(empty($dbres))
    {
   	  if($nowDatetime >= $finishTime)
      {
        // 現在時刻が入室締切時刻を過ぎている場合、結婚式への参加不可とする。
        $weddingFinished = true;
      }
      // まだ該当の結婚式には参加していない場合
      $loginHTML .= '<form action="backphp/detail_db.php" id="attendForm" method="post">';
      $loginHTML .= '<input type="hidden" name="weddingID" value="' . $weddingID . '">';
      $loginHTML .= '<input type="hidden" name="userID" value="' . $id . '">';
      $loginHTML .= '<button type="button" name="attend" id="attend" class="btn btn-5"';
      if($weddingFinished)
      {
      	$loginHTML .= ' disabled';
      }
      $loginHTML .= '>参加する</button></form>';
      if($weddingFinished)
      {
        // 結婚式の入室締切時刻を過ぎていたら、その旨のメッセージを画面に表示させる。
        $weddingYN .= '<p class="smallinfo">この結婚式は既に終了しています。</p>';
      }
    }
    else
    {
    	if($nowDatetime < $datetime)
    	{
    		$weddingNotReady = true;
    	}
      else if($nowDatetime >= $finishTime)
      {
        // 現在時刻が入室締切時刻を過ぎている場合も、入室不可とする。
        $weddingFinished = true;
      }
    	$loginHTML .= '<button type="button" name="entry" class="btn btn-5" onclick="';
      $loginHTML .= "entryRoom('vrchat://launch/?id=wrld_679f8079-2408-494b-b35a-a54104356356:4587~public')";
      $loginHTML .= '" value="entry"';
    	if($weddingNotReady || $weddingFinished)
    	{
    		$loginHTML .= ' disabled';
    	}
    	$loginHTML .= '>ルームに入る</button>';	
    	
      if($weddingNotReady)
    	{
    		// まだ開始時間を過ぎていない場合、その旨のメッセージを画面に表示させる。
    		$weddingYN .= '<p class="smallinfo">開始時間までしばらくお待ちください。</p>';
    	}
      else if($weddingFinished)
      {
        // 結婚式の入室締切時刻を過ぎていたら、その旨のメッセージを画面に表示させる。
        $weddingYN .= '<p class"smallinfo">この結婚式は既に終了しています。</p>';
      }
      else
      {
        // ルームに入室可能な場合、入室締切時刻を表示する。
        $weddingYN .= '<p class="smallinfo">入室終了時刻:' . $finishTime->format('Y/m/d H:i:s') . '</p>';
      }
    }
 
  }
  else
  {
    // どのユーザーもログインしていない場合は、ログインを促すメッセージを表示させる。
    $weddingYN .= '<p class="smallinfo">まだログインしていません。結婚式に参加するにはログインしてください。</p>';
    $loginHTML .= '<button type="button" name="login" class="btn btn-5" onclick="';
    $loginHTML .= "location.href='login.php';";
    $loginHTML .= '">Login</button>';
  }

?>

<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title>html/css bridal shower invite</title>

      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
      <link rel="stylesheet" href="css/detail.css">
      <!-- ボタンの整形用css -->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
      <link rel="stylesheet" href="css/button.css">
  
</head>

<body>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
 <link href="https://fonts.googleapis.com/css?family=Oswald|Zilla+Slab:300i" rel="stylesheet">
  <!-- VRChatのルーム遷移用javascript -->
  <script src="js/detail.js" type="text/javascript"></script>
<div id="container">
  <div class="floral-background">
    <div class="text-block">
      <div class="words">
        <h3 class="zilla less-space">please join us for a</h3>
        <h1 class="oswald uppercase some-space bigger-text">Bridal Party</h1>
        <h4 class="zilla less-space">in honor<br> of the bride-to-be</h4>
        <h2 class="smallname oswald uppercase some-space"><span class="maleName"><?php echo $maleName; ?></span> <span class="heart">♡</span> <span class="femaleName"><?php echo $femaleName; ?></span></h2>
        <h3 class="zilla less-space"><?php echo $dotw . ", " . $date . " at " . $time; ?></h3>
        <h3 class="smallnote zilla less-space smallText"><?php echo $note; ?></h3>
        <!-- 参加・不参加ボタンの表示エリア -->
        <h4 class="zilla less-space"><?php echo $weddingYN; ?></h4>

        <div id="butArea">
          <?php echo $loginHTML; ?>
          <button type="button" name="home" class="btn btn-5" onclick="location.href='index.php';">HOME</button>
        </div>
        
      </div></div>
  </div>
</div>
  
<!-- モーダル・ダイアログ -->
<div class="modal fade" id="sampleModal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<img src="img/info.png" alt="確認アイコン">
				<h4 class="modal-title">結婚式参加の確認</h4>
				<button type="button" class="close" data-dismiss="modal"><span>×</span></button>
			</div>
			<div class="modal-body">
				この結婚式に参加します。よろしいですか？
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-5" onclick="enableEntry()" data-dismiss="modal">参加</button>
				<button type="button" class="btn btn-5" data-dismiss="modal">キャンセル</button>
			</div>
		</div>
	</div>
</div>
  

</body>

</html>
