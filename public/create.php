<?php
// DBの接続情報
// 自分が設定した値に応じて変更する
$dsn = 'pgsql:dbname=d873lq7vr9h1cu;host=ec2-52-207-93-32.compute-1.amazonaws.com;port=5432';
$user = 'mdqszkqozybzha';
$pass = 'b087e60faa71ce2816688af332702a467bca73c104f9062270e5aeeb8ff9615f';

try {
    // DBに接続する
    $dbh = new PDO($dsn, $user, $pass);

    // テーブルを作成
    $query = $dbh->prepare('create table users (
        id integer generated always as identity primary key,
        name varchar(30) not null,
        age integer not null
    );');
    $query->execute();

    // DBを切断する
    $dbh = null;
} catch (PDOException $e) {
    // 接続にエラーが発生した場合ここに入る
    print "DB ERROR: " . $e->getMessage() . "<br/>";
    die();
}