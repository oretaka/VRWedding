<?php
// DAOクラスの読み込み
require_once("Dao.class.php");

// ここから　変数宣言
// 結婚式ID。
$weddingID = -1;
// ユーザーID。
$userID = -1;

// エラーが発生した場合、結婚式詳細画面へ返すエラー番号。
// 0:情報なし　1:正常登録完了　2:その他のエラー
$result = 1;
// DBのDAOオブジェクト。
$dao = new Dao();
// データーベースの取得結果を格納する配列。
$dbres = null;
// ここまで　変数宣言

// POSTリクエストから結婚式ID、ユーザーIDを取得できる場合は、取得する。
if( isset($_POST["weddingID"]) )
{
	$weddingID = intval($_POST["weddingID"]);
}
if( isset($_POST["userID"]) )
{
	$userID = intval($_POST["userID"]);
}

// ログイン中のユーザーアカウントで結婚式への参加登録を行う。
$dbres = $dao->insert("invite",["weddingID","userID","propriety"],[$weddingID,$userID,"参加"]);
// DB登録処理で例外が発生した場合、その他のエラーとする。
if($dbres == false)
{
	// エラー番号を2に。
	$result = 2;
}

// 結婚式詳細情報ページへ、結果情報を含めてリダイレクトする。
header('Location:../detail.php?result=' . $result);
exit;
