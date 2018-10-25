<?php
  // エラーメッセージの内容を格納する変数。
  $errmsg = "";
  // GETから受信したエラー番号を格納する。
  $errnum = 0;
  // 入力されていたメールアドレス情報。
  $address = "";
  // 入力されていたニックネーム情報。
  $nickname = "";

  // GETリクエストでエラー情報を取得できる場合は、取得する。
  if( isset($_GET["result"]) )
  {
    $errnum = intval($_GET["result"]);
    $errmsg .= "エラー:　";
    switch ($errnum)
    {
      case 1:
        $errmsg .= "未入力の項目があります。";
        break;
      case 2:
        $errmsg .= "そのメールアドレスは、既に登録されています。";
        break;
      case 3:
        $errmsg .= "パスワードとパスワードの確認が一致していません。もう一度入力してください。";
        break;
      case 4:
        $errmsg .= "予期しないエラーが発生しました。";
        break;
    }
  }
  // GETリクエストでメールアドレス情報、ニックネーム情報が取得できる場合は、取得する。
  if( isset($_GET["address"]) && isset($_GET["nickname"]) )
  {
    $address = $_GET["address"];
    $nickname = $_GET["nickname"];
  }


?>

<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title>Login Page</title>
</head>
<body>

  <form action="backphp/signup_db.php" method="post">
    <label for="address">登録メールアドレス:</label>
    <input type="email" name="address" id="address" size="50" value="<?php echo $address; ?>"><br>
    <label for="nickname">ニックネーム:</label>
    <input type="text" name="nickname" id="nickname" size="50" value="<?php echo $nickname; ?>"><br>
    <label for="password">パスワード:</label>
    <input type="password" name="password" id="password" size="50"><br>
    <label for="repassword">パスワードの確認:</label>
    <input type="password" name="repassword" id="repassword" size="50"><br>
    <input type="submit" value="登録">
    <?php echo $errmsg; ?>

  </form>

</body>

</html>
