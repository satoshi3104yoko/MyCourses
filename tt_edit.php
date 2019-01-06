<?php
session_start();

header("Content-type: text/html; charset=utf-8");

// CSRF対策
function setToken() {
  $token = sha1(uniqid(mt_rand(), true));
  $_SESSION['token'] = $token;
}

function checkToken() {
  if (empty($_SESSION['token']) || ($_SESSION['token'] != $_POST['token'])) {
    header("Location: {$_SERVER['REQUEST_URI']}");
		exit();
    }
}

// POST以外でアクセスされたとき
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    setToken();
}

//POSTでアクセスされたとき
else{
    checkToken();
		$token = sha1(uniqid(mt_rand(), true));
		$_SESSION['token'] = $token;
}

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');


function h($s) {
	return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

//データベース接続
require_once("db.php");
$pdo = db_connect();

// ログイン状態のチェック
if (!isset($_SESSION["account"])) {
	header("Location: index.php");
	exit();
}

// 変数定義
$account = $_SESSION['account'];
$user_id = $_SESSION["user_id"];
$delete_id = $_POST["delete_id"];
$edit_id = $_POST["edit_id"];
$edit_name = $_POST["course_name"];
$edit_instructor = $_POST["instructor"];
$edit_day = $_POST["day"];
$edit_period = $_POST["period"];
$edit_room = $_POST["room"];
$edit_color = $_POST["color"];

// 時間割名取得
try{
	$sql ="SELECT DISTINCT tt_name FROM timetable WHERE user_id=:user_id";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$stmt->execute();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		if($row["tt_name"] == $_POST["tt_name"]){
			$tt_select .="<option value=" .$row['tt_name']. " selected>".$row['tt_name']."</option>\r\n";
			$tt_dselect[] = $row["tt_name"];
	  }else{
			$tt_select .="<option value=".$row['tt_name'].">".$row['tt_name']."</option>\r\n";
			$tt_dselect[] = $row["tt_name"];
		}
	}
}catch(Exception $e){
	$errors['error'] = "もう一度やりなおして下さい。";
	echo 'Error:'.$e->getMessage();
}

if(!empty($_POST["tt_select"])){
	$_SESSION["tt_name"] = $_POST["tt_name"];
	$tt_name = $_SESSION["tt_name"];
}elseif(!empty($_SESSION["tt_name"])){
	$tt_name = $_SESSION["tt_name"];
}else{
	$tt_name = $tt_dselect[0];
}

//セッション変数を解除
unset($_SESSION["tt_name"]);

// 科目消去
if(!empty($_POST["delete"])){
	try{
		$sql ="DELETE FROM timetable WHERE user_id=:user_id AND timetable_id=:delete_id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
		$stmt->bindValue(':delete_id', $delete_id, PDO::PARAM_STR);
		$stmt->execute();
	}catch(Exception $e){
		$errors['error'] = "もう一度やりなおして下さい。";
		// echo 'Error:'.$e->getMessage();
	}
}

// 科目編集
if(!empty($_POST["edit"])){
	try{
		$sql ="SELECT * FROM timetable WHERE user_id=:user_id AND timetable_id=:edit_id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
		$stmt->bindValue(':edit_id', $edit_id, PDO::PARAM_STR);
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$edit_course_name = $row["course"];
			$edit_course_instructor = $row["instructor"];
			$edit_course_day = $row["day"];
			$edit_course_period = $row["period"];
			$edit_course_room = $row["room"];
      $edit_course_color = $row["color"];
		}
	}catch(Exception $e){
		$res = $e->getMessage();
	}
}

if(!empty($_POST["course_edit"]) && !empty($_POST["course_name"]) && !empty($_POST["day"]) && !empty($_POST["period"])){
	try{
		//例外処理を投げる（スロー）ようにする
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//トランザクション開始
		$pdo->beginTransaction();
		//timetableテーブルに登録する
	  $sql = "UPDATE timetable SET course=:course,instructor=:instructor,day=:day,period=:period,room=:room, color=:color WHERE user_id=:user_id AND timetable_id=:edit_id";
		$stmt = $pdo->prepare($sql);
		//プレースホルダへ実際の値を設定する
		$stmt->bindValue(':course', $edit_name, PDO::PARAM_STR);
		$stmt->bindValue(':instructor', $edit_instructor, PDO::PARAM_STR);
		$stmt->bindValue(':day', $edit_day, PDO::PARAM_STR);
		$stmt->bindValue(':period', $edit_period, PDO::PARAM_STR);
		$stmt->bindValue(':room', $edit_room, PDO::PARAM_STR);
		$stmt->bindValue(':color', $edit_color, PDO::PARAM_STR);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
		$stmt->bindValue(':edit_id', $edit_id, PDO::PARAM_STR);
		$stmt->execute();

		// トランザクション完了（コミット）
		$pdo->commit();

	}catch (PDOException $e){
		//トランザクション取り消し（ロールバック）
		$pdo->rollBack();
		$errors['error'] = "もう一度やりなおして下さい。";
		// echo 'Error:'.$e->getMessage();
	}

}

// 科目追加
if(!empty($_POST["course_registration"]) && !empty($_POST["course_name"]) && !empty($_POST["day"]) && !empty($_POST["period"])){
	try{
		//例外処理を投げる（スロー）ようにする
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//トランザクション開始
		$pdo->beginTransaction();
		//timetableテーブルに登録する
		$sql = "INSERT INTO timetable (course,course_id,instructor,day,period,room,user_id,tt_name,color) VALUES (:course,:course_id,:instructor,:day,:period,:room,:user_id,:tt_name,:color)";
		$stmt = $pdo->prepare($sql);
		//プレースホルダへ実際の値を設定する
		$stmt->bindValue(':course', $edit_name, PDO::PARAM_STR);
		$stmt->bindValue(':course_id', 0, PDO::PARAM_STR);
		$stmt->bindValue(':instructor', $edit_instructor, PDO::PARAM_STR);
		$stmt->bindValue(':day', $edit_day, PDO::PARAM_STR);
		$stmt->bindValue(':period', $edit_period, PDO::PARAM_STR);
		$stmt->bindValue(':room', $edit_room, PDO::PARAM_STR);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
		$stmt->bindValue(':tt_name', $tt_name, PDO::PARAM_STR);
		$stmt->bindValue(':color', $edit_color, PDO::PARAM_STR);
		$stmt->execute();

		// トランザクション完了（コミット）
		$pdo->commit();

	}catch (PDOException $e){
		//トランザクション取り消し（ロールバック）
		$pdo->rollBack();
		$errors['error'] = "もう一度やりなおして下さい。";
		// echo 'Error:'.$e->getMessage();
	}
}

// 時間割情報取得
if(isset($tt_name)){
	try{
		//例外処理を投げるようにする
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$sql ="SELECT * FROM timetable WHERE user_id=:user_id  AND tt_name=:tt_name ORDER BY
	  CASE day
	  WHEN '月' THEN 1
	  WHEN '火' THEN 2
	  WHEN '水' THEN 3
	  WHEN '木' THEN 4
	  WHEN '金' THEN 5
	  WHEN '土' THEN 6
	  WHEN '無' THEN 7
	  ELSE 99 END " ;
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
		$stmt->bindValue(':tt_name', $tt_name, PDO::PARAM_STR);
		$stmt->execute();
	  $i = 1;

		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
      switch ($row["color"]){
        case "#a9a9a9":
        $color0 = "灰";
        break;
        case "#7fbfff":
        $color0 = "青";
        break;
        case "#ff7f7f";
        $color0 = "赤";
        break;
        case "#ffff84":
        $color0 = "黄";
        break;
        case "#7fff7f":
        $color0 = "青";
        break;
      }
			if($row["course"] != "0"){
			  $res .= '<tr height="60px"><td>'.$i.'</td><td>'.$row["course"].'</td><td>'.$row["instructor"].'</td><td>'.$row["day"].$row["period"].'</td><td>'.$row["room"].'</td><td>'.$color0.'</td><td><form action="tt_edit.php" method="post"><input type="hidden" name="edit_id" value='.$row["timetable_id"].'><input type="hidden" name="token" value="'.$_SESSION["token"].'"><input type="submit" name="edit" value="編集"  class="edit_btn"></form></td><td><form action="" method="post"><input type="hidden" name="delete_id" value='.$row["timetable_id"].'><input type="hidden" name="token" value="'.$_SESSION["token"].'"><input type="submit" name="delete" value="消去"  class="edit_btn"></form></td><tr>';
	      $i++;
			}
		}

	}catch(Exception $e){
		  $errors['error'] = "もう一度やりなおして下さい。";
			// echo 'Error:'.$e->getMessage();
	}
}

?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses 時間割編集</title>
<meta charset="utf-8">
<link rel="stylesheet" href="stylesheet.css">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<!-- <script type="text/javascript">
function check(){
	if(window.confirm('本当に消去しますか？')){ // 確認ダイアログを表示
		return true; // 「OK」時は送信を実行
	}else{ // 「キャンセル」時の処理
		return false; // 送信を中止
	}
}
</script> -->
<meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

<div class="cp_cont">
	<div class="cp_offcm01">
		<input type="checkbox" id="cp_toggle01">
	  <label for="cp_toggle01"><span></span></label>
		<div class="cp_menu">
			<ul>
				<li><a href="mypage.php">マイページ</a></li>
				<li><a href="course_search.php">科目掲示板</a></li>
				<li><a href="user_search.php">ユーザー検索</a></li>
				<li><a href="logout.php">ログアウト</a></li>
			</ul>
		</div>
  </div>

	<div class="cp_contents">
		<?php require_once("header.php"); ?>
    <div class="page_top">
      <?php if(isset($tt_name)): ?>
          <h1 class="mypage_tt_name">【<?=$tt_name?>】</h1>
      </div>
      <div class="edit_header">
        <h1 class="kamoku_insert">時間割・科目の追加・編集</h1>
        <form action="tt_edit_form.php" method="post" class="tt_name_edit">
          <input type="hidden" name="tt_name" value="<?=$tt_name?>">
          <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
          <input type="submit" name="tt_edit" value="時間割の名前を変更" class="tt_name_edit1"><br>
        </form>
        <form action="tt_registration.php" method="post" class="tt_name_insert">
          <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
          <input type="submit" name="tt_registration" value="時間割を追加" class="tt_name_insert1"><br>
        </form>
      </div>
      <div class="insert_edit">
        <form action="tt_edit.php" method="post">
          <table class="items0">
        		<tr><td class="item10">科目名：</td><td><input type="text" name="course_name" value="<?=$edit_course_name?>" class="item_form0"></td></tr>
        		<tr><td class="item0">教員名：</td><td class="item_value0"><input type="text" name="instructor" value="<?=$edit_course_instructor?>" class="item_form0"></td></tr>
        		<tr><td class="item0">　曜日：</td><td class="item_value0"><select name="day" class="item_form0">
        		<option value=""></option>
        		<option value="月" <?= $edit_course_day == "月" ? "selected" : "" ?>>月</option>
        		<option value="火" <?= $edit_course_day == "火" ? "selected" : "" ?>>火</option>
        		<option value="水" <?= $edit_course_day == "水" ? "selected" : "" ?>>水</option>
        		<option value="木" <?= $edit_course_day == "木" ? "selected" : "" ?>>木</option>
        		<option value="金" <?= $edit_course_day == "金" ? "selected" : "" ?>>金</option>
        		<option value="土" <?= $edit_course_day == "土" ? "selected" : "" ?>>土</option>
        		<option value="無" <?= $edit_course_day == "無" ? "selected" : "" ?>>無</option>
        	  </select></td></tr>
        		<tr><td class="item0">　時限：</td><td class="item_value0"><select name="period" class="item_form0">
        		<option value=""></option>
        		<option value="１" <?= $edit_course_period == "１" ? "selected" : "" ?>>１</option>
        		<option value="２" <?= $edit_course_period == "２" ? "selected" : "" ?>>２</option>
        		<option value="３" <?= $edit_course_period == "３" ? "selected" : "" ?>>３</option>
        		<option value="４" <?= $edit_course_period == "４" ? "selected" : "" ?>>４</option>
        		<option value="５" <?= $edit_course_period == "５" ? "selected" : "" ?>>５</option>
        		<option value="６" <?= $edit_course_period == "６" ? "selected" : "" ?>>６</option>
        		<option value="７" <?= $edit_course_period == "７" ? "selected" : "" ?>>７</option>
            </select></td></tr>
          	<tr><td class="item0">　教室：</td><td><input type="text" name="room" value="<?=$edit_course_room?>" class="item_form0"></td></tr>
            <tr><td class="item0">　　色：</td><td class="item_value0"><select name="color" class="item_form0">
            <option value=""></option>
    				<option value="#a9a9a9" <?= $edit_course_color == "#a9a9a9" ? "selected" : "" ?>>灰</option>
    				<option value="#7fbfff" <?= $edit_course_color == "#7fbfff" ? "selected" : "" ?>>青</option>
    				<option value="#ff7f7f" <?= $edit_course_color == "#ff7f7f" ? "selected" : "" ?>>赤</option>
    				<option value="#ffff84" <?= $edit_course_color == "#ffff84" ? "selected" : "" ?>>黄</option>
    				<option value="#7fff7f" <?= $edit_course_color == "#7fff7f" ? "selected" : "" ?>>緑</option>
    				</select></td></tr>
          </table>
          <p class="kamoku_insert_link"><a href="course_search.php" class="course_search_insert">科目一覧から追加</a></p>
      		<?php if(!empty($_POST["edit"])): ?>
      			<input type="hidden" name="edit_id" value='<?=$edit_id?>'>
      			<input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
      	  	<input type="submit" name="course_edit" value="編集" class="course_insert_tt"><br>
        	<?php else: ?>
            <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
        		<input type="submit" name="course_registration" value="追加" class="course_insert_tt">
          <?php endif; ?>
      	</form>
      <?php endif; ?>
    </div>
    <div class="edit_page">
    <h1 class="tt_edit_top">登録科目一覧</h1>
    <form action="tt_edit.php" method="post" class="form1">
      <select name="tt_name" class="mypage_tt_select">
        <?=$tt_select?>
      </select>
      <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
      <input type="submit" name="tt_select" value="表示" class="mypage_tt_btn">
    </form>
    </div>
    <?php if($res != null):?>
    	<table border="1" class="course_table">
    	<tr>
    	<th width="2%">  </th>
    	<th width="35%">科目名</th>
    	<th width="35%">担当教員</th>
    	<th width="8%">曜日時限</th>
    	<th width="20%">教室</th>
    	<th width="2%">色</th>
    	</tr>
    	<?php echo $res; ?>
    	</table>
    <?php else: ?>
    <div class="insert_edit">
    	<h3 class="course_search_insert">登録している科目がありません</h3>
      <h3 class="course_search_insert">
        <a href="course_search.php">科目を追加する</a>
      </h3>
    </div>
    <?php endif;?>
  </div>
</div>
</body>
</html>
