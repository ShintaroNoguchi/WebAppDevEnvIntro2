<?php
// DBの接続情報
// 自分が設定した値に応じて変更する
$dsn = 'pgsql:dbname=default;host=192.168.99.100;port=54320';
$user = 'default';
$pass = 'secret';

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

// トップページへリダイレクト。実行環境ごとに変更する
header('Location: http://192.168.99.100');
exit;