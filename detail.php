<?php
  //                    ここから　変数宣言
  
  // 男の名前
  $maleName = "";
  // 女の名前
  $femaleName = "";
  // 結婚式の日付
  $date = "";
  // 結婚式の時間
  $time = "";
  // 結婚式の実施曜日(算出結果)
  $dotw = "";
  // 結婚式の備考
  $note = "";
  // 結婚式ID。
  $weddingID = 0;

  //                    ここまで　変数宣言

  // 一覧表示ページから送信された募集情報が取得できる場合、取得する。
  if( isset($_POST["weddingID"]) )
  {
    $weddingID = intval($_POST["weddingID"]);
    // echo $maleName;
  }
  if( isset($_POST["maleName"]) )
  {
    $maleName = $_POST["maleName"];
    // echo $maleName;
  }
  if( isset($_POST["femaleName"]) )
  {
    $femaleName = $_POST["femaleName"];
    // echo $femaleName;
  }
  if( isset($_POST["date"]) )
  {
    $date = $_POST["date"];
    // echo $date;
    // 実施日付が取得できた場合、その日付から曜日を算出する。
    $dotw = date('l',strtotime($date));
    // echo $dotw;

  }
  if( isset($_POST["time"]) )
  {
    $time = $_POST["time"];
  }
  if( isset($_POST["note"]) )
  {
    $note = $_POST["note"];
  }

?>

<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title>html/css bridal shower invite</title>
  
  
  
      <link rel="stylesheet" href="css/detail.css">

  
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
      </div></div>
  </div>
</div>
  
  

</body>

</html>
