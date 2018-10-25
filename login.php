<?php
	// ここから　変数宣言
	// ログインの認証結果など、GETリクエストで送られたメッセージを格納する変数
	// ログインページからのGETリクエストの場合:
	// 0:情報なし　1:メールアドレスが登録されていない　2:パスワードが間違っている　3:入力されていない項目がある　4:その他のエラー　100:ユーザー登録の正常完了
	// 新規ユーザー登録からのGETリクエストの場合:
	// 0:情報なし　1:未入力の項目あり　2:メールアドレスが既に登録　3:2つのパスワードの不一致　4:その他のエラー
	$result = 0;

	// GETから受信したエラー番号を格納する。
  	$errnum = 0;
	// DB送信前に入力されていた、メールアドレス
	$address = "";
	// DB送信前に入力されていた、フルネーム。
	$nickname = "";
	// ページ上に表示させるエラーメッセージ。
	$errmsg = "";
	// GETで送られたメールアドレスの情報がlogindbの情報か、signup_dbの情報かを格納する変数。0:不明　1:logindb　2:signup_db
	$get_type = 0;
	// ここまで　変数宣言
	// URLのパラメータから、リクエストを取得できる場合、getで取得。
	// ログイン画面からのリクエスト
	if( isset($_GET["result"]) )
	{
		$get_type = 1;
		$result = intval($_GET["result"]);
		$errmsg .= "エラー:　";
		switch($result)
		{
			case 1:
				$errmsg = "そのメールアドレスは、現在登録されていません。";
				break;
			case 2:
				$errmsg = "入力されたパスワードが間違っています。";
				break;
			case 3:
				$errmsg = "未入力の項目があります。";
				break;
			case 4:
				$errmsg = "予期しないエラーが発生しました。";
				break;
			case 100:
				$errmsg = "ユーザー登録が正常に完了しました。";
				break;
		}
	}
	// 新規ユーザー登録からのリクエスト
	else if( isset($_GET["suresult"]) )
	{
		$get_type = 2;
		$result = intval($_GET["suresult"]);
	    switch ($result)
	    {
	      case 1:
	      	$errmsg .= "エラー:　";
	        $errmsg .= "未入力の項目があります。";
	        break;
	      case 2:
	      	$errmsg .= "エラー:　";
	        $errmsg .= "そのメールアドレスは、既に登録されています。";
	        break;
	      case 3:
	      	$errmsg .= "エラー:　";
	        $errmsg .= "パスワードとパスワードの確認が一致していません。もう一度入力してください。";
	        break;
	      case 4:
	      	$errmsg .= "エラー:　";
	        $errmsg .= "予期しないエラーが発生しました。";
	        break;
	      case 100:
	      	$errmsg .= "ユーザー登録が正常に完了しました。";
	      	break;
	    }
	}

	// GETリクエストから、メールアドレスが取得できる場合、getで取得。
	if( isset($_GET["address"]) )
	{
		$address = $_GET["address"];
	}
	// GETリクエストから、フルネームが取得できる場合は取得。
	if( isset($_GET["nickname"]) )
	{
		$nickname = $_GET["nickname"];
	}
?>
<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title>Day 001 Login Form</title>
  
  
  <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Open+Sans:600'>

      <link rel="stylesheet" href="css/login.css">

  
</head>

<body>

  <div class="login-wrap">
	<div class="login-html">
		<input id="tab-1" type="radio" name="tab" class="sign-in" checked><label for="tab-1" class="tab">Sign In</label>
		<input id="tab-2" type="radio" name="tab" class="sign-up"><label for="tab-2" class="tab">Sign Up</label>
		<div class="login-form">
			<div class="sign-in-htm">
				<form action="backphp/logindb.php" method="post">
					<div class="group">
						<label for="emailIn" class="label">Email address</label>
						<input id="emailIn" name="emailIn" type="text" class="input" value="<?php if($get_type == 1){ echo $address; }?>" >
					</div>
					<div class="group">
						<label for="passwordIn" class="label">Password</label>
						<input id="passwordIn" name="passwordIn" type="password" class="input" data-type="password">
					</div>
					<div class="group">
						<input id="check" type="checkbox" class="check" checked>
						<label for="check"><span class="icon"></span> Keep me Signed in</label>
					</div>
					<div class="group">
						<input type="submit" class="button" value="Sign In">
					</div>
					<div class="group">
						<p><?php echo $errmsg; ?></p>
					</div>
					<div class="hr"></div>
					<div class="foot-lnk">
						<a href="#forgot">Forgot Password?</a>
					</div>
				</form>
			</div>
			<div class="sign-up-htm">
				<form action="backphp/signup_db.php" method="post">
					<div class="group">
						<label for="nickname" class="label">Nickname</label>
						<input id="nickname" name="nickname" type="text" class="input" value="<?php echo $nickname; ?>">
					</div>
					<div class="group">
						<label for="password" class="label">Password</label>
						<input id="password" name="password" type="password" class="input" data-type="password">
					</div>
					<div class="group">
						<label for="repassword" class="label">Repeat Password</label>
						<input id="repassword" name="repassword" type="password" class="input" data-type="password">
					</div>
					<div class="group">
						<label for="email" class="label">Email Address</label>
						<input id="email" name="email" type="text" class="input" value="<?php if($get_type == 2){ echo $address; } ?>" >
					</div>
					<div class="group">
						<input type="submit" class="button" value="Sign Up">
					</div>
					<div class="hr"></div>
					<div class="foot-lnk">
						<label for="tab-1">Already Member?</label>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
  
  

</body>

</html>
