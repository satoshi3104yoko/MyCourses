<?php
session_start();

header("Content-type: text/html; charset=utf-8");

//データベース接続
require_once("db.php");
$pdo = db_connect();

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

// 表示形式の変換
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

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

$account = $_SESSION["account"];

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

if(!empty($_POST["login"])){
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
		$password_hide = str_repeat('*', strlen($password));
	endif;


  //エラーが無ければ実行する
  if(count($errors) === 0){
  	try{
  		//例外処理を投げる（スロー）ようにする
  		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  		//アカウントで検索
  		$sql = "SELECT * FROM member WHERE account=(:account)";
  		$stmt = $pdo->prepare($sql);
  		$stmt->bindValue(':account', $account, PDO::PARAM_STR);
  		$stmt->execute();

  		//アカウントが一致
  		if($row = $stmt->fetch()){
        $_SESSION['user_id'] = $row["user_id"];
  			$password_hash = $row["password"];

  			//パスワードが一致
  			if (password_verify($password,$password_hash)){

  				//セッションハイジャック対策
  				session_regenerate_id(true);

  				$_SESSION['account'] = $account;
  				header("Location: mypage.php");
  				exit();
  			}else{
  				$errors['password'] = "アカウント及びパスワードが一致しません。";
  			}
  		}else{
  			$errors['account'] = "アカウント及びパスワードが一致しません。";
  		}

  		//データベース接続切断
  		$pdo = null;

  	}catch (PDOException $e){
  		// print('Error:'.$e->getMessage());
  		die();
  	}
  }
}

?>

<!DOCTYPE html>
<html>
<head>
<title>MyCourses</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="stylesheet.css">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
</head>
<body>
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

    <h1 class="login_top">MyCoursesにログイン</h1>

    <div class="background">
    <?php if(count($errors) > 0): ?>
      <div class="errors">
        <?php foreach($errors as $value): ?>
          <?=$value ?><br>
        <?php endforeach ?>
      </div>
    <?php endif; ?>

    <div class="login_form">
      <form action="index.php" method="post" class="login_form0">
        <input type="text" name="account" class="login" placeholder="ユーザーネーム" size="50"><br>
        <input type="text" name="password" class="login" placeholder="パスワード" size="50"><br>
        <input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
        <input type="submit" name="login" value="ログイン" class="login_submit">
      </form>
    </div>

   </div>
    <h3 class="new_account">
      <a href="registration_form.php">アカウント作成</a>
    </h3>
    <!-- <h2 class="not_login_top">ログインせずに</h2>
    <h3 class="not_login">
      <a href="course_search.php">科目掲示板へ</a>
    </h3>
    <h3 class="not_login">
      <a href="user_search.php">ユーザー検索へ</a>
    </h3> -->
  </div>
</div>
</body>
</html>
