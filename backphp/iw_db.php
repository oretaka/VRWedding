<?php
// セッションの使用
session_start();
// DAOクラスの読み込み
require_once("Dao.class.php");

// ここから　変数宣言
// 入力された新郎の名前。
$groom = "";
// 入力された新婦の名前。
$bride = "";
// ユーザーが選択した会場の会場id。
$venue = 0;
// 日付欄
$wdate = "";
// 時間欄
$wtime = "";

// 備考欄
$remarks = "";
// 現在ログイン中のユーザーid。
$userId = -1;

// エラーが発生した場合、結婚式募集画面へ返すエラー番号。
// 0:情報なし　1:不正なアクセス　2:システムエラー 3:ユーザーがログインしていない
$result = 0;
// DBのDAOオブジェクト。
$dao = new Dao();
// データーベースの取得結果を格納する配列。
$dbres = null;
// 入力フォームのうち、何個の項目がPOSTで受信できたかを格納する変数。少なくとも4つ受信できれば成功。
$in_count = 0;
// ここまで　変数宣言

// 入力フォームから、各種データを取得する。
// 新郎
if(isset($_POST["groom"]))
{
	$groom = $_POST["groom"];
	$in_count++;
}
// 新婦
if(isset($_POST["bride"]))
{
	$bride = $_POST["bride"];
	$in_count++;
}
// ユーザーが選択した会場の会場id。
if(isset($_POST["venue"]))
{
	$venue = intval($_POST["venue"]);
	$in_count++;
}
// 備考
if(isset($_POST["remarks"]))
{
	$remarks = $_POST["remarks"];
	$in_count++;
}
// 開催日付
if(isset($_POST["wdate"]))
{
	$wdate = $_POST["wdate"];
}
// 開催時間
if(isset($_POST["wtime"]))
{
	$wtime = $_POST["wtime"];
}


// 入力フォームから取得したPOSTデータが3以下の場合は、不正なアクセスとみなす。
if( $in_count <= 3 )
{
	$result = 1;
}
else
{
	// セッションからログイン中のユーザーIDを取得できる場合は取得。取得できない場合、エラーメッセージを表示する。
	if( isset($_SESSION["id"]) )
	{
		$userId = intval($_SESSION["id"]);

		// 入力されたデータを基に、結婚式募集情報をDBに登録する。
		$dbres = $dao->insert("wedding_register",["userID","placeID","twitterAccount","maleName","femaleName","date","time","note"],[$userId,$venue,"twittername",$groom,$bride,$wdate,$wtime,$remarks]);
		// 登録処理で例外が発生した場合、エラー番号を2とする。
		if($dbres == false)
		{
			// エラー番号を2(システムエラー)に。
			$result = 2;
		}
	}
	else
	{
		// エラー番号を3(ログインしていない)に。
		$result = 3;
	}

}


// エラーコードが0以外の場合は、結婚式募集画面へリダイレクトし、エラーメッセージを表示。
if( $result == 0 )
{
	// 登録処理が成功したら、トップページへ遷移させ、登録成功の旨をメッセージで表示させる。
	header('Location:../index.php?result=100');
	exit;
}
else
{
	// 登録処理失敗
	header('Location:../input_wedding.php?result=' . strval($result));
	exit;
}
