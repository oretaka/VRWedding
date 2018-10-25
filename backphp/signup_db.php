<?php
// DAOクラスの読み込み
require_once("Dao.class.php");

// ここから　変数宣言
// 入力メールアドレス
$in_address = "";
// 入力ニックネーム
$in_nickname = "";
// 入力パスワード
$in_password = "";
// 入力パスワードの確認
$in_repassword = "";

// エラーが発生した場合、ログイン画面へ返すエラー番号。
// 0:情報なし　1:入力されていない項目がある　2:メールアドレスが既に登録されている　3:パスワードとパスワードの確認が一致しない　4:その他のエラー
$result = 0;
// DBのDAOオブジェクト。
$dao = new Dao();
// データーベースの取得結果を格納する配列。
$dbres = null;

// ここまで　変数宣言

// 入力フォームの全ての項目が、POSTで受信できるかチェックする。
if( isset($_POST["email"]) && isset($_POST["nickname"]) && isset($_POST["password"]) && isset($_POST["repassword"]) )
{
	// 入力フォームに入力された情報を、POSTで受信する。
	$in_address = $_POST["email"];
	$in_nickname = $_POST["nickname"];
	$in_password = $_POST["password"];
	$in_repassword = $_POST["repassword"];

	// 入力項目のいずれかが空文字の場合は、エラー番号を1とする。
	if( strcmp($in_address, "") == 0 || strcmp($in_nickname, "") == 0 || strcmp($in_password, "") == 0 || strcmp($in_repassword, "") == 0)
	{
		// エラー番号を1に。
		$result = 1;
	}
	// そうでない場合で、パスワードと再入力パスワードが一致しなかった場合、エラー番号を3とする。
	else if( strcmp($in_password, $in_repassword) != 0 )
	{
		// エラー番号を3に。
		$result = 3;
	}
	else
	{
		// 入力されたメールアドレスが既にDBに登録されているかチェックする。
		$dbres = $dao->select("user_register",null,["mail_address = ?"],[$in_address],null,"ORDER BY id ASC");
		if(empty($dbres))
		{
			// 入力されたメールアドレスが、まだDBに登録されていない場合は、DBに新規ユーザーデータを登録する。
			$dbres = $dao->insert("user_register",["mail_address","nickname","password"],[$in_address,$in_nickname,$in_password]);
			// DB登録処理で例外が発生した場合、その他のエラーとする。
			if($dbres == false)
			{
				// エラー番号を4に。
				$result = 4;
			}
		}
		else
		{
			// 既に同じメールアドレスがDBに登録されている場合、エラー番号を2とする。
			$result = 2;
		}
	}

}
else
{
	// POSTからデータを正常に取得できなかった場合は、エラー番号を4とする。
	$result = 4;
}

// エラーコードが0以外の場合は、ログイン画面へリダイレクトし、エラーメッセージを表示させる。
if( $result == 0 )
{
	// ユーザー登録が成功した場合、自動的にログインページへ遷移させる。
	// ログイン画面には、ユーザー登録完了のメッセージを表示させる。
	header('Location:../login.php?suresult=100');
	exit;
}
else
{
	// 送信前に入力されていたメールアドレス・ニックネームの情報を、GETのパラメータに追加する。
	header('Location:../login.php?suresult=' . strval($result) . '&address=' . $in_address . '&nickname=' . $in_nickname);
	exit;
}
