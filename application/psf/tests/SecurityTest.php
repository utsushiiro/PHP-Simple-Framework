<?php

use psf\lib\Security;

require_once "../../bootstrap.php";

$form_name = 'test';

$request = new \psf\core\Request();
$result_message = null;
if ($request->isPost()):
    try {
        $posted_token =  $request->getPostParam('token');
        Security::checkCsrfToken($form_name, $posted_token);
        $result_message = 'Valid Request!';
    }catch (Exception $e){
        $result_message = $e->getMessage();
    }
else:
    $csrf_token = Security::generateCsrfToken($form_name);
endif;


?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>ログイン画面</title>
	<link rel="stylesheet" href="">
</head>
<body>
  <h1>てーすと</h1>
  <form action="./SecurityTest.php" method="post" id="<?=$form_name?>">
      <p>
          <input type="submit" value="そーしん">
          <input type="hidden" name="token" value="<?=$csrf_token ?? '_undefined'?>">
      </p>
      <p>Result : <?=$result_message ?? ''?></p>
      <p>送信されたトークン : <?=$posted_token ?? ''?></p>
  </form>
</body>　
</html>
