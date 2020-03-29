<?php
// DBの接続情報
// 自分が設定した値に応じて変更する
$dsn = 'pgsql:dbname=d873lq7vr9h1cu;host=ec2-52-207-93-32.compute-1.amazonaws.com;port=5432';
$user = 'mdqszkqozybzha';
$pass = 'b087e60faa71ce2816688af332702a467bca73c104f9062270e5aeeb8ff9615f';

try {
    // DBに接続する
    $dbh = new PDO($dsn, $user, $pass);

    // usersテーブルの有無を確認
    $query = $dbh->prepare('SELECT tablename FROM pg_tables WHERE tablename = ?');
    $query->execute(array('users'));
    $tablename = $query->fetchAll();

    // usersテーブルが無い場合、usersテーブルを作成
    if (is_null($tablenames[0]['tablename'])) {
        $query = $dbh->prepare('create table users (
            id integer generated always as identity primary key,
            name varchar(30) not null,
            age integer not null
        );');
        $query->execute();
    }

    // 登録済みのユーザを取得
    $query = $dbh->prepare('INSERT INTO users (name, age) VALUES (?, ?)');
    $name = $_POST['name'];
    $age = $_POST['age'];
    $query->execute(array($name, $age));

    // DBを切断する
    $dbh = null;
} catch (PDOException $e) {
    // 接続にエラーが発生した場合ここに入る
    print "DB ERROR: " . $e->getMessage() . "<br/>";
    die();
}

header('Location: https://noguchi0408-web-app-sample.herokuapp.com/'); // トップページへリダイレクト。実行環境ごとに変更する
exit;