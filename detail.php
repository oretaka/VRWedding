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
  //urlを取得
  $url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
  //                    ここまで　変数宣言
  // セッションから、ログイン中のユーザーIDが取得できる場合は取得する。
  if( isset($_SESSION["id"]) )
  {
    $id = intval( $_SESSION["id"] );
  }
  // 一覧表示ページから送信された募集情報が取得できる場合、取得する。
  // GETに値がある場合は、GETからの取得を優先。ない場合は、セッションからの取得を試みる。
  // まだセッションに値を保存していない場合、セッションに値をセットし、更新させて結婚式詳細情報が消えないようにする。
  if( isset($_GET["weddingID"]) )
  {
    $weddingID = intval($_GET["weddingID"]);
    $_SESSION["weddingID"] = $_GET["weddingID"];
  }
  else if( isset($_SESSION["weddingID"]) )
  {
    $weddingID = intval($_SESSION["weddingID"]);
  }
  if( isset($_GET["maleName"]) )
  {
    $maleName = $_GET["maleName"];
    $_SESSION["maleName"] = $_GET["maleName"];
  }
  else if( isset($_SESSION["maleName"]) )
  {
    $maleName = $_SESSION["maleName"];
  }
  if( isset($_GET["femaleName"]) )
  {
    $femaleName = $_GET["femaleName"];
    $_SESSION["femaleName"] = $_GET["femaleName"];
  }
  else if( isset($_SESSION["femaleName"]) )
  {
    $femaleName = $_SESSION["femaleName"];
  }
  if( isset($_GET["date"]) )
  {
    $date = $_GET["date"];
    $_SESSION["date"] = $_GET["date"];
  }
  else if( isset($_SESSION["date"]) )
  {
    $date = $_SESSION["date"];
  }
  // 実施日付が取得できた場合、その日付から曜日を算出する。
  $dotw = date('l',strtotime($date));
  if( isset($_GET["time"]) )
  {
    $time = $_GET["time"];
    $_SESSION["time"] = $_GET["time"];
  }
  else if( isset($_SESSION["time"]) )
  {
    $time = $_SESSION["time"];
  }
  if( isset($_GET["note"]) )
  {
    $note = $_GET["note"];
    $_SESSION["note"] = $_GET["note"];
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
      $loginHTML .= '<form action="backphp/detail_db.php" method="post">';
      $loginHTML .= '<input type="hidden" name="weddingID" value="' . $weddingID . '">';
      $loginHTML .= '<input type="hidden" name="userID" value="' . $id . '">';
      $loginHTML .= '<input type="submit" name="attend" class="btn btn-5" value="参加する"></form>';
    }
    else
    {
      // 既に該当の結婚式に参加している場合、結婚式の開始時間前ならばルームに入るボタンを無効に、開始時間後ならばルームに入るボタンを有効にする。
      $datetime = new Datetime( str_replace('/', '-', $date) . ' ' . str_replace('～', ':00', $time) );
      // 結婚式の入室締切時刻を格納。
      $finishTime = new Datetime( $datetime->format('Y-m-d') . ' ' . '00:00:00' );
      $finishTime->modify('+1 days');
      $nowDatetime = new Datetime();
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
    		$weddingYN .= '<p>開始時間までしばらくお待ちください。</p>';
    	}
      else if($weddingFinished)
      {
        // 結婚式の入室締切時刻を過ぎていたら、その旨のメッセージを画面に表示させる。
        $weddingYN .= '<p>この結婚式は既に終了しています。</p>';
      }
      else
      {
        // ルームに入室可能な場合、入室締切時刻を表示する。
        $weddingYN .= '<p>入室終了時刻:' . $finishTime->format('Y/m/d H:i:s') . '</p>';
      }
    }
  }
  else
  {
    // どのユーザーもログインしていない場合は、ログインを促すメッセージを表示させる。
    $weddingYN .= '<p>まだログインしていません。結婚式に参加するにはログインしてください。</p>';
    $loginHTML .= '<button type="button" name="login" class="btn btn-5" onclick="';
    $loginHTML .= "location.href='login.php';";
    $loginHTML .= '">Login</button>';
  }

?>

<!DOCTYPE html>
<html lang="en" >

  <head>
    <title><?php  echo $maleName."&#9825;". $femaleName." |bridal shower invite";  ?></title>  
    <meta charset="UTF-8">
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@Ms2019D" />
    <meta property="og:url" content="<?php echo $url ?>" />
    <meta property="og:title" content="<?php  echo $maleName."&#9825;". $femaleName." |bridal shower invite";  ?>" />
    <meta property="og:description" content="<?php  echo $maleName."さんと". $femaleName."さんのBRIDAL　PARTYです。";  ?>" />
    <meta property="og:image" content="http://vrwedding.php.xdomain.jp/img/userimg.jpg" />
  
    <link rel="stylesheet" href="css/detail.css">
      <!-- ボタンの整形用css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link rel="stylesheet" href="css/button.css">
    <!-- VRChatのルーム遷移用javascript -->
    <script src="js/detail.js" type="text/javascript"></script>
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
            <div id="butArea">
              <?php echo $loginHTML; ?>
              <button type="button" name="home" class="btn btn-5" onclick="location.href='index.php';">HOME</button>
            </div>
            <a href="https://twitter.com/share?ref_src=twsrc%5Etfw" class="twitter-share-button" data-hashtags="VRWedding" data-lang="ja" data-show-count="false"
            data-url="<?php $url?>"
            data-via=""
            data-text="<?php echo "BRIDAL PARTY ". $maleName."♡". $femaleName; ?>"
            data-related=""
            data-count=""
            data-lang="ja"
            data-counturl="<?php $url?>"
            data-hashtags=""
            data-size=""
            >Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
