<?php

use psf\lib\Security;

require_once '../bootstrap.php';

$form_name = 'test';
$password = 'test';

$request = new \psf\core\Request();
$result_message = null;
if ($request->isPost()):
    // CSRF対策トークンのテスト
    try
    {
        $posted_token =  $request->getPostParam('token');
        Security::checkOneTimeCsrfToken($form_name, $posted_token);
        $result_message = 'Valid Request';
    }
    catch (Exception $e)
    {
        $result_message = $e->getMessage();
    }

    // リファラチェックのテスト
    if (Security::checkReferer()):
        $result_message .= ' , Valid Referer';
    else:
        $result_message .= ' , Invalid Referer';
    endif;

    // パスワードのテスト
    $hashed_password = Security::getPasswordHash('unique_key', 'test');
    if ($hashed_password ===  Security::getPasswordHash('unique_key', $request->getPostParam('password'))):
        $auth_message = 'Success';
    else:
        $auth_message = 'Failed';
    endif;
else:
    $csrf_token = Security::generateOneTimeCsrfToken($form_name);
endif;

// JS文字列リテラルエスケープのテスト
$script_tag_attack = '</script><script>alert(document.cookie)//';
$escaped_sta = Security::escapeJsString($script_tag_attack);

$event_handler_attack = '\');alert(document.cookie)//';
$escaped_eha = Security::escapeJsString($event_handler_attack);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>SecurityTest</title>
	<link rel="stylesheet" href="">
</head>
<body onload="alert('<?= $escaped_eha;?>')">
  <form action="./SecurityTest.php" method="post" id="<?= $form_name?>">
      <p>
          <label for="password">Password</label>
          <input type="password" name="password" id="password">
          <input type="submit" value="submit">
          <input type="hidden" name="token" value="<?= $csrf_token ?? '_undefined'?>">
      </p>
      <p>Result : <?= $result_message ?? ''?></p>
      <p>送信されたトークン : <?= $posted_token ?? ''?></p>
      <p>Authentication : <?= $auth_message ?? '';?></p>
  </form>
  <script>
      alert('<?= $escaped_sta;?>');
  </script>
</body>　
</html>
