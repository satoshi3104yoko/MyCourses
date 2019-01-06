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

//データベース接続
require_once("db.php");
$pdo = db_connect();

//エラーメッセージの初期化
$errors = array();

// 表示形式の変換
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

if(!empty($_POST["registration"])){
	//前後にある半角全角スペースを削除する関数
	function spaceTrim ($str) {
		// 行頭
		$str = preg_replace('/^[ 　]+/u', '', $str);
		// 末尾
		$str = preg_replace('/[ 　]+$/u', '', $str);
		return $str;
	}

	//エラーメッセージの初期化
	$errors = array();

	//POSTされたデータを各変数に入れる
	$account = isset($_POST['account']) ? $_POST['account'] : NULL;
	$password = isset($_POST['password']) ? $_POST['password'] : NULL;
	//前後にある半角全角スペースを削除
	$account = spaceTrim($account);
	$password = spaceTrim($password);
	//アカウント入力判定
	if ($account == ''):
		$errors['account'] = "アカウントが入力されていません。";
	elseif(strlen($account)>30):
		$errors['account_length'] = "アカウントは10文字以内で入力して下さい。";
	endif;

	//パスワード入力判定
	if ($password == ''):
		$errors['password'] = "パスワードが入力されていません。";
	elseif(!preg_match('/^[0-9a-zA-Z]{5,30}$/', $_POST["password"])):
		$errors['password_length'] = "パスワードは半角英数字の5文字以上30文字以下で入力して下さい。";
	else:
		//パスワードの長さだけ*を返す
		$password_hide = str_repeat('*', strlen($password));
	endif;

	//エラーが無ければセッションに登録
	if(count($errors) === 0){
	  try{
			$sql = "SELECT account FROM member";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		    $account_all[] = $row["account"];
			}
		}catch(Exception $e){
			$errors['error'] = "もう一度やりなおして下さい。";
			echo 'Error:'.$e->getMessage();
		}

    if($account_all != null){
  	  foreach($account_all as $accounts){
  			if($account != $accounts){
  				$_SESSION['account'] = $account;
  				$_SESSION['password'] = $password;
          //データベース接続切断
          $pdo = null;
  	  	}else{
  				$errors["acoount"] = "そのユーザー名は既に使われています";
  			}
  		}
    }else{
      $_SESSION['account'] = $account;
      $_SESSION['password'] = $password;
      //データベース接続切断
      $pdo = null;
    }
		if(count($errors) === 0){
			header("Location: registration_check.php");
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses アカウント作成</title>
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

    <h1 class="registration_top">アカウントを作成</h1>

    <?php if(count($errors) > 0): ?>
      <div class="errors">
        <?php foreach($errors as $value): ?>
          <?=$value ?><br>
        <?php endforeach ?>
      </div>
    <?php endif; ?>

    <form action="registration_form.php" method="post">
      <div class="registration_form">
        <input type="text" name="account" class="registration" placeholder="ユーザーネーム" size="50"><br>
        <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
      </div>
      <div class="registration_form">
        <input type="text" name="password" class="registration" placeholder="パスワード" size="50"><br>
        <i class="fa fa-key fa-lg fa-fw" aria-hidden="true"></i>
      </div>
      <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
      <input type="submit" name="registration" value="次へ" class="next">
    </form>
  </div>
</div>
</body>
</html>
