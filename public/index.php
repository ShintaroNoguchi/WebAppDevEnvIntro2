<?php
// DBの接続情報
// 自分が設定した値に応じて変更する
$dsn = 'pgsql:dbname=d873lq7vr9h1cu;host=ec2-52-207-93-32.compute-1.amazonaws.com;port=5432';
$user = 'mdqszkqozybzha';
$pass = 'b087e60faa71ce2816688af332702a467bca73c104f9062270e5aeeb8ff9615f';

try {
    // DBに接続する
    $dbh = new PDO($dsn, $user, $pass);

    // 登録済みのユーザを取得
    $query = $dbh->prepare('SELECT name, age FROM users');
    $query->execute();
    $users = $query->fetchAll();

    // DBを切断する
    $dbh = null;
} catch (PDOException $e) {
    // 接続にエラーが発生した場合ここに入る
    print "DB ERROR: " . $e->getMessage() . "<br/>";
    die();
}
?>

<form action="insert.php" method="post">
    <p>
        <label>名前：<input type="text" name="name" size="40" required></label>
    </p>
    <p>
        <label>年齢：<input type="number" name="age" size="40" min="0" required></label>
    </p>
    <input type="submit" value="送信">
</form>

<hr>

<table>
    <thead>
        <tr>
            <th>名前</th>
            <th>年齢</th>
        </tr>
    </thead>
    <tbody>
        <?php
            foreach($users as $user) {
                print "<tr>";
                print "<td>" . $user["name"] . "</td>";
                print "<td>" . $user["age"] . "</td>";
                print "</tr>";
            }
        ?>
    </tbody>
</table>