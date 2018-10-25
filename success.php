<?php
// 現在ログイン中のユーザー名
$nickname = "";
// ログイン中のユーザー名がpostで受信できる場合は取得。
if( isset($_POST["nickname"]) )
{
	$nickname = $_POST["nickname"];
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>ログイン成功</title>
</head>

<body>
  <h1>ログイン成功</h1>
  <p><?php echo $nickname ?>さんでログイン中</p>

</body>

</html>
