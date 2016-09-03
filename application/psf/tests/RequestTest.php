<?php

require_once "../bootstrap.php";

$obj = new \psf\core\Request();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Requestクラステスト</title>
</head>
<body>

<pre>
<?php
echo "isPost:<br>";
var_dump($obj->isPost());
echo "<hr>" . "getGetParam:<br>";
var_dump($obj->getGetParam('get', 'default'));
echo "<hr>" . "getPostParam:<br>";
var_dump($obj->getPostParam('post', 'default'));
echo "<hr>" . "getHost:<br>";
var_dump($obj->getHost());
echo "<hr>" . "getRequestUri:<br>";
var_dump($obj->getRequestUri());
echo "<hr>" . "getBaseUri:<br>";
var_dump($obj->getBaseUri());
echo "<hr>" . "getPathInfo():<br>";
var_dump($obj->getPathInfo());
echo "<hr>";
?>
</pre>

  <p>Get送信</p>
  <form action="" method="get">
      <input type="text" name="get">
      <input type="submit" value="送信">
  </form>
  <p>Post送信</p>
  <form action="" method="post">
      <input type="text" name="post">
      <input type="submit" value="送信">
  </form>
</body>
</html>
