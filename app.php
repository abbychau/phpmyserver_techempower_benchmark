<?php
$action = $_GET['action'];

if (in_array($action, ['db', 'query', 'update', 'fortune'])) {
  $pdo = new PDO('mysql:host=tfb-database;dbname=hello_world', 'benchmarkdbuser', 'benchmarkdbpass',
      [PDO::ATTR_PERSISTENT => true,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false]
  );
} else {
  echo 'Unknown action: ' . $action;
  exit;
}

if ($action === 'db') {
  header('Content-Type: application/json');
  $statement = $pdo->query( 'SELECT id,randomNumber FROM World WHERE id = '. mt_rand(1, 10000) );
  echo json_encode($statement->fetch(PDO::FETCH_ASSOC), JSON_NUMERIC_CHECK);

} elseif ($action === 'query') {
  header('Content-Type: application/json');
  $query_count = 1;
  if ((int) $_GET['queries'] > 1) {
    $query_count = min($_GET['queries'], 500);
  }
  $statement = $pdo->prepare('SELECT id,randomNumber FROM World WHERE id = ?');

  while ($query_count--) {
    $statement->execute( [mt_rand(1, 10000)] );
    
    $arr[] = $statement->fetch(PDO::FETCH_ASSOC);
  }
  echo json_encode($arr, JSON_NUMERIC_CHECK);

} elseif ($action === 'update') {
  header('Content-Type: application/json');
  $query_count = 1;
  if ((int) $_GET['queries'] > 1) {
    $query_count = min($_GET['queries'], 500);
  }
  $statement = $pdo->prepare('SELECT id,randomNumber FROM World WHERE id=?');
  $updateStatement = $pdo->prepare('UPDATE World SET randomNumber=? WHERE id=?');
  while ($query_count--) {
    $id = mt_rand(1, 10000);
    $statement->execute([$id]);
    $world = $statement->fetch();
    $updateStatement->execute(
      [$world['randomNumber'] = mt_rand(1, 10000), $id]
    );
    $arr[] = $world;
  }
  echo json_encode($arr, JSON_NUMERIC_CHECK);

} elseif ($action === 'fortune') {
  header('Content-Type: text/html;charset=UTF-8');

  $arr = $pdo->query( 'SELECT id, message FROM Fortune' )->fetchAll(PDO::FETCH_KEY_PAIR); 
  $arr[0] = 'Additional fortune added at request time.';

  asort($arr);
  echo '<!DOCTYPE html><html><head><title>Fortunes</title></head><body><table><tr><th>id</th><th>message</th></tr>';

  foreach ($arr as $id => $fortune) {
    echo "<tr><td>$id</td><td>" . htmlspecialchars($fortune, ENT_QUOTES, 'UTF-8') . "</td></tr>";
  }

  echo '</table></body></html>';

} elseif ($action === 'plaintext') {
  header('Content-Type: text/plain');
  echo 'Hello, World!';

} elseif ($action === 'json') {
  header('Content-Type: application/json');
  echo json_encode(['message' => 'Hello, World!']);

} else {
  echo 'Unknown action: ' . $action;

}
