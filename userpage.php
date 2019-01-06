<?php
session_start();

header("Content-type: text/html; charset=utf-8");

function h($s) {
	return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
//データベース接続
require_once("db.php");
$pdo = db_connect();

// 変数定義
$account = $_SESSION['account'];
$user_id = $_GET["user_id"];

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
	$tt_name = $_POST["tt_name"];
}else{
	$tt_name = $tt_dselect[0];
}

// ユーザー情報取得
try{
	$sql = "SELECT * FROM member WHERE user_id = :user_id";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
	$stmt->execute();
	 while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		$user_name = $row["account"];
	}
}catch(Exception $e){
	$errors['error'] = "もう一度やりなおして下さい。";
	echo 'Error:'.$e->getMessage();
}

// 時間割情報取得
try{
	//例外処理を投げるようにする
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	if(isset($tt_name)){
		$sql ="SELECT * FROM timetable WHERE user_id=:user_id AND tt_name=:tt_name";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
		$stmt->bindValue(':tt_name', $tt_name, PDO::PARAM_STR);
		$stmt->execute();
	}else{
		$sql ="SELECT * FROM timetable WHERE user_id=:user_id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
		$stmt->execute();
	}
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if(empty($day_period[$row["day"]][$row["period"]])){
		  	$day_period[$row["day"]][$row["period"]] = "<a href =' course_bbs.php?course_id=".$row['course_id']."'>".$row["course"]."</a><br>".$row["instructor"]."<br>".$row["room"];
				$color[$row["day"]][$row["period"]] = $row["color"];
	    }
		}
	$mon = "月";
	$tue = "火";
	$web = "水";
	$thu = "木";
	$fri = "金";
	$sat = "土";
	$ond = "無";
	$res1 .= '<tr height="60px"><td>'.'1'.'</td><td bgcolor='.$color[$mon][１].'>'.$day_period[$mon][１].'</td><td bgcolor='.$color[$tue][１].'>'.$day_period[$tue][１].'</td><td bgcolor='.$color[$web][１].'>'.$day_period[$web][１].'</td><td bgcolor='.$color[$thu][１].'>'.$day_period[$thu][１].'</td><td bgcolor='.$color[$fri][１].'>'.$day_period[$fri][１].'</td><td bgcolor='.$color[$sat][１].'>'.$day_period[$sat][１].'</td><td bgcolor='.$color[$ond][１].'>'.$day_period[$ond][１].'</td></tr>';
	$res2 .= '<tr height="60px"><td>'.'2'.'</td><td bgcolor='.$color[$mon][２].'>'.$day_period[$mon][２].'</td><td bgcolor='.$color[$tue][２].'>'.$day_period[$tue][２].'</td><td bgcolor='.$color[$web][２].'>'.$day_period[$web][２].'</td><td bgcolor='.$color[$thu][２].'>'.$day_period[$thu][２].'</td><td bgcolor='.$color[$fri][２].'>'.$day_period[$fri][２].'</td><td bgcolor='.$color[$sat][２].'>'.$day_period[$sat][２].'</td><td bgcolor='.$color[$ond][２].'>'.$day_period[$ond][２].'</td></tr>';
	$res3 .= '<tr height="60px"><td>'.'3'.'</td><td bgcolor='.$color[$mon][３].'>'.$day_period[$mon][３].'</td><td bgcolor='.$color[$tue][３].'>'.$day_period[$tue][３].'</td><td bgcolor='.$color[$web][３].'>'.$day_period[$web][３].'</td><td bgcolor='.$color[$thu][３].'>'.$day_period[$thu][３].'</td><td bgcolor='.$color[$fri][３].'>'.$day_period[$fri][３].'</td><td bgcolor='.$color[$sat][３].'>'.$day_period[$sat][３].'</td><td bgcolor='.$color[$ond][３].'>'.$day_period[$ond][３].'</td></tr>';
	$res4 .= '<tr height="60px"><td>'.'4'.'</td><td bgcolor='.$color[$mon][４].'>'.$day_period[$mon][４].'</td><td bgcolor='.$color[$tue][４].'>'.$day_period[$tue][４].'</td><td bgcolor='.$color[$web][４].'>'.$day_period[$web][４].'</td><td bgcolor='.$color[$thu][４].'>'.$day_period[$thu][４].'</td><td bgcolor='.$color[$fri][４].'>'.$day_period[$fri][４].'</td><td bgcolor='.$color[$sat][４].'>'.$day_period[$sat][４].'</td><td bgcolor='.$color[$ond][４].'>'.$day_period[$ond][４].'</td></tr>';
	$res5 .= '<tr height="60px"><td>'.'5'.'</td><td bgcolor='.$color[$mon][５].'>'.$day_period[$mon][５].'</td><td bgcolor='.$color[$tue][５].'>'.$day_period[$tue][５].'</td><td bgcolor='.$color[$web][５].'>'.$day_period[$web][５].'</td><td bgcolor='.$color[$thu][５].'>'.$day_period[$thu][５].'</td><td bgcolor='.$color[$fri][５].'>'.$day_period[$fri][５].'</td><td bgcolor='.$color[$sat][５].'>'.$day_period[$sat][５].'</td><td bgcolor='.$color[$ond][５].'>'.$day_period[$ond][５].'</td></tr>';
	$res6 .= '<tr height="60px"><td>'.'6'.'</td><td bgcolor='.$color[$mon][６].'>'.$day_period[$mon][６].'</td><td bgcolor='.$color[$tue][６].'>'.$day_period[$tue][６].'</td><td bgcolor='.$color[$web][６].'>'.$day_period[$web][６].'</td><td bgcolor='.$color[$thu][６].'>'.$day_period[$thu][６].'</td><td bgcolor='.$color[$fri][６].'>'.$day_period[$fri][６].'</td><td bgcolor='.$color[$sat][６].'>'.$day_period[$sat][６].'</td><td bgcolor='.$color[$ond][６].'>'.$day_period[$ond][６].'</td></tr>';
	$res7 .= '<tr height="60px"><td>'.'7'.'</td><td bgcolor='.$color[$mon][７].'>'.$day_period[$mon][７].'</td><td bgcolor='.$color[$tue][７].'>'.$day_period[$tue][７].'</td><td bgcolor='.$color[$web][７].'>'.$day_period[$web][７].'</td><td bgcolor='.$color[$thu][７].'>'.$day_period[$thu][７].'</td><td bgcolor='.$color[$fri][７].'>'.$day_period[$fri][７].'</td><td bgcolor='.$color[$sat][７].'>'.$day_period[$sat][７].'</td><td bgcolor='.$color[$ond][７].'>'.$day_period[$ond][７].'</td></tr>';


	}catch(Exception $e){
	  $errors['error'] = "もう一度やりなおして下さい。";
		echo 'Error:'.$e->getMessage();
	}
?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses ユーザーページ</title>
<meta charset="utf-8">
<link rel="stylesheet" href="stylesheet.css">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

<div class="cp_cont">
	<?php if(isset($account)): ?>
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
	<?php else :?>
		<div class="cp_offcm01">
			<input type="checkbox" id="cp_toggle01">
			<label for="cp_toggle01"><span></span></label>
			<div class="cp_menu">
				<ul>
				<li><a href="index.php">トップ</a></li>
				<li><a href="registration_form.php">アカウント作成</a></li>
				<li><a href="course_search.php">科目掲示板</a></li>
				<li><a href="user_search.php">ユーザー検索</a></li>
				</ul>
			</div>
		</div>
	<?php endif; ?>

	<div class="cp_contents">
		<?php require_once("header.php"); ?>
		<div class="page_top">
		 <?php if(isset($tt_name)): ?>
			<h1 class="userpage_top"><?=$user_name?>さんの時間割</h1>
	  	<form action="userpage.php?user_id=<?=$user_id?>" method="post" class="form">
			 	<select name="tt_name" class="userpage_tt_select">
					<?=$tt_select?>
				</select>
				<input type="submit" name="tt_select" value="表示" class="userpage_tt_btn"><br>
	  	</form>
  	</div>
		<?php endif; ?>
		<div class="tt">
			<table border="2">
			<tr>
			<th width="5%">  </font></th>
			<th width="10%">月</font></th>
			<th width="10%">火</font></th>
			<th width="10%">水</font></th>
			<th width="10%">木</font></th>
			<th width="10%">金</font></th>
			<th width="10%">土</font></th>
			<th width="10%">オンデマンド</font></th>
			</tr>
			<?php echo $res1.$res2.$res3.$res4.$res5.$res6.$res7; ?><br><br>
			</table><br><br>
		</div>
	</div>
</div>
</body>
</html>
