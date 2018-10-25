<?php
// DAOクラスの読み込み
require_once("Dao.class.php");
// セッションの使用
session_start();

// ここから　変数宣言
// 入力メールアドレス
$in_address = "";
// 入力パスワード
$in_password = "";
// エラーが発生した場合、ログイン画面へ返すエラー番号。
// 0:情報なし　1:メールアドレスが登録されていない　2:パスワードが間違っている　3:入力されていない項目がある　4:その他のエラー
$result = 0;
// DBのDAOオブジェクト。
$dao = new Dao();
// データーベースの取得結果を格納する配列。
$dbres = null;

// ログインが成功した場合の、現在ログイン中のユーザーID。デフォルト値は-1。
$id = -1;
// ログインが成功した場合の、現在ログイン中のユーザーニックネーム。
$nickname = "";

// ここまで　変数宣言

// メールアドレスとパスワードが、POSTで正常に取得できるかチェックする。
if( isset($_POST["emailIn"]) && isset($_POST["passwordIn"]) )
{
	// 入力メールアドレスと、入力パスワードを取得。
	$in_address = $_POST["emailIn"];
	$in_password = $_POST["passwordIn"];

	// 入力項目のいずれかが空文字列の場合、エラーメッセージを表示する。
	if( strcmp($in_address, "") == 0 || strcmp($in_password, "") == 0 )
	{
		$result = 3;
	}
	else
	{
		// メールアドレス・パスワードがどちらも入力されている
		echo $in_address;
		echo $in_password;

		// DBへ接続し、DB内に入力メールアドレス・入力パスワードが存在するか検索する。
		// まずはデータベース中に、入力メールアドレスと一致したものがあるか検索する。
		$dbres = $dao->select("user_register",null,["mail_address = ?"],[$in_address],null,"ORDER BY id ASC");
		if(empty($dbres))
		{
			// 入力されたメールアドレスが、まだDBに登録されていない
			// エラーコードを1にする。
			$result = 1;
		}
		else
		{
			// メールアドレスがDBに登録済み
			// パスワードが入力されたものと一致するかチェック。
			$result = 2;
			foreach ($dbres as $val) {
				if( strcmp($in_password, $val["password"]) == 0 )
				{
					// パスワードが一致した場合、ログイン成功とする。
					$result = 0;
					// セッションに、現在ログイン中のユーザーID、ニックネームをセットする。
					$_SESSION["id"] = $val["id"];
					$_SESSION["nickname"] = $val["nickname"];
				}
			}
			// パスワードが一致しない場合、エラーコードを2のままとし、エラーメッセージを表示させる。
		}
	}
	
}
else
{
	// メールアドレス・パスワード情報を取得できなかった場合(直接アクセスなど)は、エラーコードを4にする。
	$result = 4;
}

// エラーコードが0以外の場合は、ログイン画面へリダイレクトし、エラーメッセージを表示させる。
if( $result == 0 )
{
	// ログインが成功した場合、トップページへ遷移させる。
	header('Location:../index.php');
	exit;
}
else
{
	// メールアドレスが入力されていた(空欄ではない)場合、入力されたメールアドレスをgetパラメータに追加する。
	if(isset($_POST["emailIn"]))
	{
		$in_address = $_POST["emailIn"];
		if(strcmp($in_address, "") != 0)
		{
			header('Location:../login.php?result=' . strval($result) . '&address=' . $in_address);
		}
		else
		{
			header('Location:../login.php?result=' . strval($result));		
		}
	}
	else
	{
		header('Location:../login.php?result=' . strval($result));
	}
	exit;
}