<?php
session_start();

header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

// 表示形式の変換
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

//エラーメッセージの初期化
$errors = array();

// 変数定義
$account = $_SESSION['account'];
$user_id = $_SESSION["user_id"];

	// 科目の時間割への追加
if(!empty($_POST["insert"]) && !empty($_POST["insert_id"])){

	//データベース接続
	require_once("db.php");
  $pdo = db_connect();

	foreach($_POST['insert_id'] as $insert_id ){
		$tt_id = "tt".$insert_id;
		$color_id ="color".$insert_id;
		$tt_name = $_POST[$tt_id];
		$color = $_POST[$color_id];
		try{
			//例外処理を投げる（スロー）ようにする
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//トランザクション開始
			$pdo->beginTransaction();

			// 時間割に既に保存されているかを確認。
			$sql ="SELECT * FROM timetable WHERE user_id=:user_id AND tt_name=:tt_name";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
			$stmt->bindValue(':tt_name', $tt_name, PDO::PARAM_STR);
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
					$day_period[$row["day"]][$row["period"]] = $row["day"].$row["period"];
			}

		 // 科目データを取得
			$sql ="SELECT * FROM courses WHERE course_id = :insert_id";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(":insert_id", $insert_id,  PDO::PARAM_STR);
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				$course_id0 = $row["course_id"];
				$course0 = $row["course"];
				$instructor0 = $row["instructor"];
				$day0 = $row["day"];
				$period0 = $row["period"];
				$room0 = $row["room"];
			}


			//timetableテーブルに本登録する
			if(empty($day_period[$day0][$period0]) || $day_period[$day0][$period0] == $day_period["無"]["１"]){
				if($day == "無"){
					if(!empty($day_period[$day][$period])){
						$period = "２";
					}
					if(!empty($day_period[$day][$period])){
						$period = "３";
					}
					if(!empty($day_period[$day][$period])){
						$period = "４";
					}
					if(!empty($day_period[$day][$period])){
						$period = "５";
					}
					if(!empty($day_period[$day][$period])){
						$period = "６";
					}
					if(!empty($day_period[$day][$period])){
						$period = "７";
					}
					if(!empty($day_period[$day][$period])){
						$res_insert1 = "これ以上オンデマンド授業は追加できません";
					}
				}
				if(empty($res_insert1)){
					$sql = "INSERT INTO timetable (course,course_id,instructor,day,period,room,user_id,tt_name,color) VALUES (:course,:course_id,:instructor,:day,:period,:room,:user_id,:tt_name,:color)";
					$stmt = $pdo->prepare($sql);
					//プレースホルダへ実際の値を設定する
					$stmt->bindValue(':course', $course0, PDO::PARAM_STR);
					$stmt->bindValue(':course_id', $course_id0, PDO::PARAM_STR);
					$stmt->bindValue(':instructor', $instructor0, PDO::PARAM_STR);
					$stmt->bindValue(':day', $day0, PDO::PARAM_STR);
					$stmt->bindValue(':period', $period0, PDO::PARAM_STR);
					$stmt->bindValue(':room', $room0, PDO::PARAM_STR);
					$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
					$stmt->bindValue(':tt_name', $tt_name, PDO::PARAM_STR);
					$stmt->bindValue(':color', $color, PDO::PARAM_STR);
					$stmt->execute();
					$res_insert .= $course0."を".$tt_name."に追加しました<br>" ;
				}
			}else{
				$res_no_insert .= $course0."は".$tt_name."に追加できません<br>";
			}
			// トランザクション完了（コミット）
			$pdo->commit();
		}catch(Exception $e){
			$errors['error'] = "もう一度やりなおして下さい。";
			echo 'Error:'.$e->getMessage();
		}
	}
	//データベース接続切断
	$pdo = null;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses 科目追加</title>
<meta charset="utf-8">
<link rel="stylesheet" href="stylesheet.css">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
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
			<!-- データの出力 -->
			<?php if (count($errors) === 0 ): ?>
			  <h1 class="course_insert_top">追加科目一覧</h1>
				  <p class="course_check0"><?=$res_insert ?></p>
				  <p class="course_check0"><?=$res_no_insert ?></p>
				  <h3 class="course_check0"><?=$res_insert1 ?></h3>
					<form action="course_search.php" method="post">
							<input type="submit" name="insert" value="科目掲示板" class="mypage">
					</form>

			  <!-- もしエラーがあった場合は -->
		  <?php elseif(count($errors) > 0): ?>
			  <!-- エラー変数に格納された配列を取り出して出力 -->
				<?php foreach($errors as $value): ?>
					<?=$value ?>
				<?php endforeach ?>
			  <input type="button" class="next" value="戻る" onClick="history.back()">
			<?php else: ?>
				<h4 class="course_check0"一致する科目がありません</h4>
				<input type="button" class="next" value="戻る" onClick="history.back()">
			<?php endif; ?>

  </div>
</div>
</body>
</html>
