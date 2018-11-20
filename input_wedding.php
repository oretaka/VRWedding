<?php
	// DAOクラスの読み込み
	require_once("backphp/Dao.class.php");

	// ここから　変数宣言・初期化スペース
	// バックのphpから受け取ったエラーコード。　0:異常なし　1:不正なアクセス　2:システムエラー
	$result = 0;
	// DBでエラーが発生した場合のエラーメッセージを格納。
	$errmsg = "";

	// DBのDAOオブジェクト。
	$dao = new Dao();
	// データベースから取得した、会場一覧情報。
	$venueList = null;
	// データベースから取得した、会場一覧のHTML情報。
	$venueHTML = '';
	// ここまで　変数宣言・初期化スペース

	// DBから、現在の会場一覧情報を取得。
	$venueList = $dao->select("place",null);
	// 取得結果の行数分繰り返し。
	for($i=0;$i<count($venueList);$i++)
	{
		$venueHTML .= '<option value="' . $venueList[$i]["placeID"] . '">' . $venueList[$i]["placeName"] . '</option>';
	}


	// エラーコードを受け取った場合は、エラー内容に応じてエラーメッセージを表示する。
	if( isset($_GET["result"]) )
	{
		$result = intval($_GET["result"]);
		$errmsg .= "<p>エラー:　";
		switch($result)
		{
			case 1:
				$errmsg .= "不正なアクセスです。";
				break;
			case 2:
				$errmsg .= "システムエラーが発生しました。";
				break;
			case 3:
				$errmsg .= "まだログインしていません。結婚式の募集を行うにはログインしてください。";
				break;
		}
		$errmsg .= "</p>";
	}

?>

<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title>Input Wedding Day</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" type="text/javascript"></script>


  
  <link rel='stylesheet' href='http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css'>
<link rel='stylesheet' href='http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css'>
<link rel='stylesheet' href='http://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.0/css/bootstrapValidator.min.css'>
 <!-- ボタンの整形用css -->
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
<link rel="stylesheet" href="css/button.css"> -->

      <!-- 時刻指定ライブラリ用cssの読み込み -->
      <link rel="stylesheet" href="css/default.css">
      <link rel="stylesheet" href="css/default.time.css">
      <link rel="stylesheet" href="css/default.date.css">
      <link rel="stylesheet" href="css/input_wedding.css">

  
</head>

<body>

  <div class="container">

    <form class="well form-horizontal" action="backphp/iw_db.php" method="post"  id="contact_form">
<fieldset>

<!-- Form Name -->
<legend>Wedding Day Form</legend>
<!-- DB処理でシステムエラーが発生した場合に、エラーメッセージを表示するスペース。 -->
<?php echo $errmsg; ?>

<!-- Text input-->

<div class="form-group">
  <label class="col-md-4 control-label">Groom</label>  
  <div class="col-md-4 inputGroupContainer">
  <div class="input-group">
  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
  <input  name="groom" placeholder="Groom" class="form-control"  type="text">
    </div>
  </div>
</div>

<!-- Text input-->

<div class="form-group">
  <label class="col-md-4 control-label" >Bride</label> 
    <div class="col-md-4 inputGroupContainer">
    <div class="input-group">
  <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
  <input name="bride" placeholder="Bride" class="form-control"  type="text">
    </div>
  </div>
</div>

   
<div class="form-group"> 
  <label class="col-md-4 control-label">Venue</label>
    <div class="col-md-4 selectContainer">
    <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-list"></i></span>
    <select name="venue" class="form-control selectpicker" >
      <option value=" " >Please select your Venue</option>
      <?php echo $venueHTML; ?>
    </select>
  </div>
</div>
</div>
    
<div class="form-group"> 
  <label class="col-md-4 control-label">Wedding Date</label>
    <div class="col-md-4 selectContainer">
    <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-list"></i></span>
        <input id='wdate' name='wdate' class="fieldset__input js__datepicker" type="text" placeholder="Please select Wedding Date">
  </div>
</div>
</div>

<div class="form-group"> 
  <label class="col-md-4 control-label">Wedding Time</label>
    <div class="col-md-4 selectContainer">
    <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-list"></i></span>
        <input id='wtime' name='wtime' class="fieldset__input js__datepicker" type="text" placeholder="Please select Wedding Time">
  </div>
</div>
</div>
    

<!-- radio checks -->
 <!-- <div class="form-group">
                        <label class="col-md-4 control-label">Do you have hosting?</label>
                        <div class="col-md-4">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="hosting" value="yes" /> Yes
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="hosting" value="no" /> No
                                </label>
                            </div>
                        </div>
                    </div>
-->

<!-- Text area -->
  
<div class="form-group">
  <label class="col-md-4 control-label">Remarks</label>
    <div class="col-md-4 inputGroupContainer">
    <div class="input-group">
        <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
        	<textarea class="form-control" name="remarks" placeholder="Remarks"></textarea>
  </div>
  </div>
</div>

<!-- Success message -->
<div class="alert alert-success" role="alert" id="success_message">Success <i class="glyphicon glyphicon-thumbs-up"></i> Thanks for contacting us, we will get back to you shortly.</div>

<!-- Button -->
<div class="form-group" id="homeSend">
  <label class="col-md-4 control-label"></label>
  <div class="col-md-1">
      <button type="button" name="home" class="btn btn-5" onclick="location.href='index.php';">HOME</button>
  </div>
  <div class="col-md-1">
    <button type="submit" class="btn btn-warning" >Send <span class="glyphicon glyphicon-send"></span></button>
  </div>
</div>
</fieldset>
</form>
</div>
    </div><!-- /.container -->
  <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js'></script>
<script src='http://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.4.5/js/bootstrapvalidator.min.js'></script>

  
    <!-- 日付選択フォームのjavascriptライブラリの読み込み -->
    <script  src="js/picker.js"></script>
    <script  src="js/legacy.js"></script>
    <script  src="js/picker.date.js"></script>
    <script  src="js/picker.time.js"></script>

    <script  src="js/input_wedding.js"></script>





</body>

</html>
