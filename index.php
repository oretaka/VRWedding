<?php
  // セッションを使用。
  session_start();
  // DAOクラスの読み込み
  require_once("backphp/Dao.class.php");

  //    ここから　変数宣言・初期化
  
  // 現在ログイン中のユーザーID。
  $id = -1;
  // 現在ログイン中のユーザーニックネーム。
  $nickname = "";
  // 仮ログアウトボタン。現在ログイン中の場合は表示させる。
  $logout_but = "";
  // バック処理からのメッセージコードを格納。　100:結婚式登録の成功
  $result = 0;
  // トップ画面に表示させるメッセージの内容。
  $msg = "";
  // DBのDAOオブジェクト。
  $dao = new DAO();
  // DBからの取得結果を格納する配列。
  $dbres = null;
  // DBから取得した、結婚式募集情報のHTMLデータを格納。
  $bpHtml = '';

  // 結婚式の開始時間を、ユーザーに分かりやすい形式に変換したテキスト。
  $startTimeShow = '';

  //    ここまで　変数宣言・初期化

  // 最新の結婚式情報の取得を行う。
  $dbres = $dao->select("wedding_register",null,null,null,null,"ORDER BY date, time");
  // 取得した結婚式の情報数分、HTMLを生成し、BridalParty欄に表示させる(横は最大2件表示)。
  for($i=0;$i<count($dbres);$i++)
  {
    if($i % 2 == 0)
    {
      // $iが偶数
      // profileHolderの開始
      $bpHtml .= '<div class="profileHolder">';
    }

    $bpHtml .= '<div class="width50"><img class="profileImage" src="img/userimg.jpg">';

    // 結婚式のタイトル(相手の名前・自分の名前を表示させます)
    $bpHtml .= '<h3>' . $dbres[$i]["maleName"] . '　♡　' . $dbres[$i]["femaleName"] . '</h3>';

    // 結婚式の時間(開始時間のみ)・備考など
    $bpHtml .= '<p>';
    // 時間
    // DB上の時間を、ユーザーが分かりやすい表示形式に変換する。
    $startTimeShow =  date("Y/m/j", strtotime( $dbres[$i]["date"] )) . "　" . date("H:i～", strtotime( $dbres[$i]["time"] ));
    $bpHtml .= '<b>' . $startTimeShow .  '</b><br>';

    // 備考欄
    $bpHtml .= $dbres[$i]["note"];
    $bpHtml .= '</p>';
    $bpHtml .= '</div>';

    if($i % 2 == 1)
    {
      // $iが奇数
      // profileHolderの終了
      $bpHtml .= '</div>';
    }
  }



  // セッションからユーザーID・ニックネームが取得できる場合は取得し、ログイン中の表示を行う。
  if( isset($_SESSION["id"]) )
  {
    $id = intval( $_SESSION["id"] );
    $nickname = $_SESSION["nickname"];
    $logout_but = "<a href=\"backphp/logout.php\">ログアウト</a>";
  }
  // 
  // GETリクエストからメッセージコードを取得できる場合は取得する。
  if( isset($_GET["result"]) )
  {
    $result = intval($_GET["result"]);
    $msg = "<p>";
    switch($result)
    {
      case 100:
        $msg .= "結婚式の募集が完了しました。";
        break;
    }
    $msg .= "</p>";
  }

?>

<!DOCTYPE html>
<html lang="en" >

<head>
  <meta charset="UTF-8">
  <title>ViRtUaL WeDdInG</title>
  
  
  <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'>

      <link rel="stylesheet" href="css/style.css">
      <!-- リンクボタンの大きさを修正 -->
      <link rel="stylesheet" href="css/index.css">

</head>

<body>

  <div class="homepage">
  <div class="leftHalf">
    <img class="weddingLogo" src="https://i.pinimg.com/736x/5d/5e/31/5d5e31ad71713d93c7b354912231793f--wedding-logo-monogram-logo-wedding.jpg">
  </div>
  <!-- Right -->
  <div class="rightHalf">
    <div class="contentHalfWrap">
      <h1 class="big">Virtual♡<span></span>Wedding</h1>
      <!-- ログインユーザーの情報表示 -->
     <?php
        if(strcmp($nickname, "") != 0)
        {
          echo "<p>こんにちは　" . $nickname . "さん</p>";
          echo $logout_but;
        }
        // メッセージ情報がある場合は表示。
        if( strcmp($msg, "") != 0 )
        {
          echo $msg;
        }
      ?>

      <div id="countDownWrapper">
        <div class="dateInfo">
          <p>Wednesday Octorber 10, 2018</p>
        </div>
        <div id="countdownTimer">
          <div class="timeHolder">
            <p>{{countdown_days}}</p>
            <h2>DAYS</h2>
          </div>
          <div class="timeHolder">
            <p>{{countdown_hours}}</p>
            <h2>HOURS</h2>
          </div>
          <div class="timeHolder">
            <p>{{countdown_min}}</p>
            <h2>MINUTES</h2>
          </div>
          <div class="timeHolder">
            <p>{{countdown_sec}}</p>
            <h2>SECONDS</h2>
          </div>
        </div>  
      </div>
      <!--  Timer End   -->
      
      <hr>
      
      <h1>VR CHAT in WEDDING</h1>
      <h2><a href="" target="_blank">1700 Ferguson Falls Rd <br>Lanark, ON K0G 1K0</a></h2>
      
    <div class="buttons">
      <a href="login.php" class="linkBut"><div class="linkText">Login / Registry</div></a>
      <a href="input_wedding.php" class="linkBut"><div class="linkText">Wedding Recruitment</div></a>
      <a href="#bridalParty" class="openHomePage linkBut"><div class="linkText">Bridal Party</div></a>
      <a href="#accomodations" class="openHomePage linkBut"><div class="linkText">Accomodations</div></a> 
      <a href="#faq" class="openHomePage linkBut"><div class="linkText">FAQ</div></a>
      <a href="#" class='nulled openHomePage linkBut'><div class="linkText">Gallery</div></a>
      
      <p><em>Check back after the event for pictures of the wedding</em></p>
      
      
      
    </div>
<!-- Buttons End   -->
    </div>

  </div>
</div>
<!-- Homepage End -->

<div class="mainPageWrapper" id="topOfPage">
  <p class="menuToggle"><i class="fa fa-bars" aria-hidden="true"></i><i class="fa fa-times" aria-hidden="true"></i></p>
  <nav>
    <a class="goHome">Home</a>
      <a href="login.php" class="openHomePage">Registry/Login <i class="fa fa-external-link" aria-hidden="true"></i></a>
      <a href="input_wedding.php">Wedding Recruitment<i class="fa fa-external-link" aria-hidden="true"></i></a>
      <a href="#bridalParty" class="openHomePage">Bridal Party</a>
      <a href="#venu" class="openHomePage">Venu</a>
      <a href="#faq" class="openHomePage">FAQ</a>
      <a href="#" class='nulled openHomePage'>Gallery</a>
      
    <p></p>
      
  </nav>
  
  
  
  
  
   <h1 class="big">Virtual♡<span></span>Wedding</h1>
  
   <div class="section" id="bridalParty">
      <div class="contentArea">
        <h2>Bridal Party</h2>
        <p>Schedule list</p>
        <?php echo $bpHtml; ?>
        
        <!-- <div class="profileHolder">
          <div class="width50">
            <img class="profileImage" src="img/userimg.jpg">
            <h3>〇〇〇 ♡ ▲▲▲ </h3>
            <p><b>11：00～14：00</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam id ante et mi gravida vestibulum nec vel tortor. Vivamus tincidunt ligula tristique magna ultrices ultricies. Praesent neque nulla, ornare in posuere quis, commodo ut mauris. Ut porttitor id purus nec ullamcorper. Donec eget rhoncus nunc. Aliquam blandit eget mi eget tempus. Duis et nunc tortor. Quisque odio ante, convallis vel dui sed, vulputate iaculis leo. Aenean mollis lacus purus, nec feugiat risus rhoncus a. Aenean egestas elit sit amet odio lobortis euismod. Praesent vel enim nibh. Vestibulum vitae euismod lorem.  </p>
           </div>
                
             <div class="width50">
            <img class="profileImage" src="img/userimg.jpg">
            <h3>〇〇〇 ♡ ▲▲▲ </h3>
            <p><b>14：00～18：00</b><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam id ante et mi gravida vestibulum nec vel tortor. Vivamus tincidunt ligula tristique magna ultrices ultricies. Praesent neque nulla, ornare in posuere quis, commodo ut mauris. Ut porttitor id purus nec ullamcorper. Donec eget rhoncus nunc. Aliquam blandit eget mi eget tempus. Duis et nunc tortor. Quisque odio ante, convallis vel dui sed, vulputate iaculis leo. Aenean mollis lacus purus, nec feugiat risus rhoncus a. Aenean egestas elit sit amet odio lobortis euismod. Praesent vel enim nibh. Vestibulum vitae euismod lorem. </p>
           </div>   
          </div> -->
<!--   profile holder end      -->
       <!--  <div class="profileHolder">
          <div class="width50">
            <img class="profileImage" src="img/userimg.jpg">
            <h3>Maid of Honour Name</h3>
            <p><b>Maid of Honour</b><br>Aenean mollis lacus purus, nec feugiat risus rhoncus a. Aenean egestas elit sit amet odio lobortis euismod. Praesent vel enim nibh. Vestibulum vitae euismod lorem.</p>
           </div>
                
             <div class="width50">
            <img class="profileImage" src="img/userimg.jpg">
            <h3>Best Man Name</h3>
            <p><b>Best Man</b><br>Aenean mollis lacus purus, nec feugiat risus rhoncus a. Aenean egestas elit sit amet odio lobortis euismod. Praesent vel enim nibh. Vestibulum vitae euismod lorem. </p>
           </div>   
          </div> -->
<!--   profile holder end      -->
       <!--  <div class="profileHolder">
          <div class="width50">
            <img class="profileImage" src="img/userimg.jpg">
            <h3>Bridemaid Name </h3>
            <p><b>Bridemaid</b><br>Aenean mollis lacus purus, nec feugiat risus rhoncus a. Aenean egestas elit sit amet odio lobortis euismod. Praesent vel enim nibh. Vestibulum vitae euismod lorem.</p>
           </div>
                
             <div class="width50">
            <img class="profileImage" src="img/userimg.jpg">
            <h3>Groomsman Name </h3>
            <p><b>Groomsman</b><br>Aenean mollis lacus purus, nec feugiat risus rhoncus a. Aenean egestas elit sit amet odio lobortis euismod. Praesent vel enim nibh. Vestibulum vitae euismod lorem.</p>
           </div>   
          </div> -->
<!--   profile holder end      -->
       
   
     </div>
<!--   Content Area End    -->
  </div>
<!--Section end    -->
  <div class="breaker"></div>
  
  
     <div class="section" id="venu">
      <div class="contentArea">
        <h2>Venu</h2>
        
        <div class="halfImage">
          <img src="img/churchroom.jpg">
          <div class="info">
            <h3>VR CHAT Church</h3>
            <p>1700 Ferguson Falls Rd <br>Lanark, ON K0G 1K0</p>
            <p>
  <a target="_blank" href="http://www.templessugarbush.ca/"><i class="fa fa-map-marker" aria-hidden="true"></i> Website</a></p>
             <!--<p><a target="_blank" href="https://www.google.ca/maps/dir/''/temple's+sugar+bush/@45.0377293,-76.3555751,12z/data=!3m1!4b1!4m8!4m7!1m0!1m5!1m1!1s0x4cd26d0722c94e01:0xb2ea76f2e0a686cc!2m2!1d-76.2855353!2d45.0377507"><i class="fa fa-globe" aria-hidden="true"></i> Open Width Google Maps</a></p> -->

</div>
</div>
<!--         Half Image End -->
      </div>
<!--   Content Area End    -->
  </div>
<!--Section end    -->
  


  <div class="breaker"></div>
  
  
     <div class="section" id="accomodations">
      <div class="contentArea">
        <h2>Accomodations</h2>
        <p>When booking please ensure to notify the desk that you would like to reserve under the M&D wedding.</p>
        <div class="halfImage">
          <img src="img/VRCHAT_img.jpg">
          <div class="info">
            <h3>Best Western Perth</h3>
            <p>1700 Ferguson Falls Rd <br>Lanark, ON K0G 1K0</p>
             <p><a target="_blank" href="tel:98289298623"><i class="fa fa-phone" aria-hidden="true"></i> (846) 599-0984</a></p>
            <p>
  <a target="_blank" href="http://www.templessugarbush.ca/"><i class="fa fa-map-marker" aria-hidden="true"></i> Website</a></p>
            <p><a target="_blank" href="https://www.google.ca/maps/dir/''/temple's+sugar+bush/@45.0377293,-76.3555751,12z/data=!3m1!4b1!4m8!4m7!1m0!1m5!1m1!1s0x4cd26d0722c94e01:0xb2ea76f2e0a686cc!2m2!1d-76.2855353!2d45.0377507"><i class="fa fa-globe" aria-hidden="true"></i> Open Width Google Maps</a></p>

</div>
</div>
<!--         Half Image End -->
        
        <div class="halfImage">
          <img src="img/VRChat_Shinto_shrine.png">
          <div class="info">
            <h3>Best Western Kanata</h3>
            <p>1700 Ferguson Falls Rd <br>Lanark, ON K0G 1K0</p>
             <p><a target="_blank" href="tel:98289298623"><i class="fa fa-phone" aria-hidden="true"></i> (846) 599-0984</a></p>
            <p>
  <a target="_blank" href="http://www.templessugarbush.ca/"><i class="fa fa-map-marker" aria-hidden="true"></i> Website</a></p>
            <p><a target="_blank" href="https://www.google.ca/maps/dir/''/temple's+sugar+bush/@45.0377293,-76.3555751,12z/data=!3m1!4b1!4m8!4m7!1m0!1m5!1m1!1s0x4cd26d0722c94e01:0xb2ea76f2e0a686cc!2m2!1d-76.2855353!2d45.0377507"><i class="fa fa-globe" aria-hidden="true"></i> Open Width Google Maps</a></p>

</div>
</div>
<!--         Half Image End -->
      </div>
<!--   Content Area End    -->
  </div>
<!--Section end    -->




 <div class="breaker"></div>
  
  
     <div class="section" id="faq">
      <div class="contentArea">
        <div class="accordian">
          <p class="trigger">What is the dress code? <i class="fa fa-plus-circle" aria-hidden="true"></i><i class="fa fa-minus-circle" aria-hidden="true"></i></p>
          <p class="expandable">The dress code will be formal wedding attire.</p>
        </div>
<!--         Accordian End -->
      </div>
<!--   Content Area End    -->
  </div>
<!--Section end    -->




<a href="#topOfPage" class="toTop"><i class="fa fa-arrow-up" aria-hidden="true"></i><br>To Top</a>
<footer>Bride & Groom 2018</footer>
</div>
<!--Main Page Wrapper End-->
  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.2/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/vue/2.3.4/vue.min.js'></script>

  

    <script  src="js/index.js"></script>




</body>

</html>