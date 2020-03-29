# Webアプリケーション環境構築2

## 目標
- DBを利用したWebアプリを作成
- Webアプリをサーバにデプロイ

## 事前準備

[前回の内容](https://github.com/ShintaroNoguchi/WebAppDevEnvIntroduction1)を参考に、`index.php`の作成までやっておく。

## DBの利用準備

今回はDBにはPostgreSQLを利用する。PostgreSQLを利用した理由は後述。

### .envの修正

`laradock`ディレクトリ内の`.env`を修正する。

```bash:/project/laradock/.env
# データパスを設定
- DATA_PATH_HOST=~/.laradock/data
+ DATA_PATH_HOST=../db
# MySQLは使わないためfalseに
- PHP_FPM_INSTALL_MYSQLI=true
+ PHP_FPM_INSTALL_MYSQLI=false
# PostgreSQLを利用するための設定
- WORKSPACE_INSTALL_PG_CLIENT=false
+ WORKSPACE_INSTALL_PG_CLIENT=true
- PHP_FPM_INSTALL_PGSQL=false
+ PHP_FPM_INSTALL_PGSQL=true
- PHP_FPM_INSTALL_PG_CLIENT=false
+ PHP_FPM_INSTALL_PG_CLIENT=true
- PHP_WORKER_INSTALL_PGSQL=false
+ PHP_WORKER_INSTALL_PGSQL=true
# PostgreSQLの設定（ポート番号のみ変更。その他は任意の値でOK）
POSTGRES_DB=default
POSTGRES_USER=default
POSTGRES_PASSWORD=secret
- POSTGRES_PORT=5432
+ POSTGRES_PORT=54320
# pgadminの設定（任意の値でOK）
PGADMIN_DEFAULT_EMAIL=pgadmin4@pgadmin.org
PGADMIN_DEFAULT_PASSWORD=admin
# .envの最終行に追加
+ DB_HOST=postgres
```

### docker-compose.ymlの修正

`laradock`ディレクトリ内の`docker-compose.yml`を修正する。

```bash:/project/laradock/docker-compose.yml
- - ${DATA_PATH_HOST}/postgres:/var/lib/postgresql/data
+ - ${DATA_PATH_HOST}/postgres:/var/lib/postgresql
```

### PostgreSQL、pgAdminの起動

`laradock`ディレクトリ直下で以下のコマンドを実行

```bash:/project/laradock
$ docker-compose up -d postgres pgadmin
```

コンテナの起動状況を確認する。下記のようなコンテナ稼働状態になっていればOK。

```bash:/project/laradock
$ docker-compose ps
           Name                          Command              State                                  Ports
------------------------------------------------------------------------------------------------------------------------------------------
laradock_docker-in-docker_1   dockerd-entrypoint.sh           Up      2375/tcp, 2376/tcp
laradock_nginx_1              /bin/bash /opt/startup.sh       Up      0.0.0.0:443->443/tcp, 0.0.0.0:80->80/tcp, 0.0.0.0:81->81/tcp
laradock_pgadmin_1            /entrypoint.sh                  Up      443/tcp, 0.0.0.0:5050->80/tcp
laradock_php-fpm_1            docker-php-entrypoint php-fpm   Up      9000/tcp
laradock_postgres_1           docker-entrypoint.sh postgres   Up      0.0.0.0:54320->5432/tcp
laradock_workspace_1          /sbin/my_init                   Up      0.0.0.0:2222->22/tcp, 0.0.0.0:8001->8000/tcp, 0.0.0.0:8080->8080/tcp
```

## pgAdminを用いたDB構築

pgAdminはPostgreSQLサーバをウェブブラウザで管理するためのデータベース接続クライアントツールの一種。これを利用する事で、視覚的にわかりやすい形でデータベースを操作することが可能になる。

### pgAdminを開く
ブラウザを開いて、Windowsであれば「http://192.168.99.100:5050」に、Macであれば「http://localhost:5050」にアクセスする。

アクセスするとログイン画面が出る。メールアドレスとパスワードの入力欄には.envで設定した`PGADMIN_DEFAULT_EMAIL`と`PGADMIN_DEFAULT_PASSWORD`をそれぞれ入力する。

### pgAdminにDBサーバを登録する

左側のServerを選択してから上部のタブにある「Object＞Create＞Server」をクリック。
クリックすると「Create - Server」というタイトルのフォームが出てくる。

フォームの以下の項目を入力する。

|入力項目|入力内容|説明|
|General>Name|任意の名前|pgAdminで使う値なので自由|
|Connection＞Host name/address|192.168.99.100|DBが動いているIPアドレスやドメイン|
|Connection＞Port|`.env`の`POSTGRES_PORT`で設定したポート番号||
|Connection＞Maintenance database|`.env`の`POSTGRES_DB`で設定したデータベース名||
|Connection＞Username|`.env`の`POSTGRES_USER`で設定したユーザ名||
|Connection＞Password|`.env`の`POSTGRES_PASSWORD`で設定したパスワード||

入力したら「Save」ボタンを押して完了。

### テーブルを作成

左側の「Server>(自分で設定したpgAdmin表示用DB名)>Databeses>(.envで設定したDB名)」を選択してから上部のタブにある「Tools＞Query Tool」をクリック。

Query Editor画面が出てくるので、以下のSQLを入力する。

```bash:Query Editor
create table users (
    id integer generated always as identity primary key,
    name varchar(30) not null,
    age integer not null
);
```

入力したら、入力欄上部の実行ボタン（右向き△）を押す。
「Query returned seccessfully in xx msec.」と表示が出れば成功。テーブルが作成された。

## PHPでデータベースを操作する

### index.phpの修正

```bash:/project/src/public/index.php
<?php
// DBの接続情報
// 自分が設定した値に応じて変更する
$dsn = 'pgsql:dbname=default;host=192.168.99.100;port=54320';
$user = 'default';
$pass = 'secret';

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
```

### insert.phpを作成

`project/src/public`に`insert.php`を作成。

```bash:/project/src/public/insert.php
<?php
// DBの接続情報
// 自分が設定した値に応じて変更する
$dsn = 'pgsql:dbname=default;host=192.168.99.100;port=54320';
$user = 'default';
$pass = 'secret';

try {
    // DBに接続する
    $dbh = new PDO($dsn, $user, $pass);

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

header('Location: http://192.168.99.100'); // トップページへリダイレクト。実行環境ごとに変更する
exit;
```

### 動作確認

ブラウザを開いて、Windowsであれば「http://192.168.99.100」に、Macであれば「http://localhost」にアクセスする。
名前と年齢を入力して送信ボタンを押すと、入力フォームの下に入力した内容が表示される。

## herokuに公開


### Git for Windows

インストール＆設定参考
https://haniwaman.com/git-for-windows/

## 作業ディレクトリにLaradockをクローンする

作業ディレクトリ名をprojectとする。

```bash:/project
git init
git submodule add https://github.com/Laradock/laradock.git
```

## Laradockの初期設定

projectディレクトリ直下にsrcディレクトリを作成

```bash:/project
mkdir src
```

laradockディレクトリ内に.envファイルを作成

```bash:/project
cd laradock
```
```bash:/project/laradock
cp env-example .env
```

先程作成した.envファイル(設定ファイル)のAPP_CODE_PATH_HOSTの部分を書き換える。
これでproject/srcの下の階層がコードを格納するディレクトリとなる。

```bash:/project/laradock/.env
- APP_CODE_PATH_HOST=../
+ APP_CODE_PATH_HOST=../src
```

## Dockerでworkspaceを立ち上げる

laradockディレクトリ直下で以下のコマンドを実行

```bash:/project/laradock
$ docker-compose up -d workspace nginx
```

コンテナの起動状況を確認する。下記のようなコンテナ稼働状態になっていればOK。

```bash:/project/laradock
$ docker-compose ps
Name                          Command              State                     Ports
---------------------------------------------------------------------------------------------------------------
laradock_docker-in-docker_1   dockerd-entrypoint.sh           Up       2375/tcp
laradock_nginx_1              nginx                           Up       0.0.0.0:443->443/tcp, 0.0.0.0:80->80/tcp
laradock_php-fpm_1            docker-php-entrypoint php-fpm   Up       9000/tcp
laradock_workspace_1          /sbin/my_init                   Up       0.0.0.0:2222->22/tcp
```

## 公開領域のディレクトリを作成

srcディレクトリ直下に公開領域のディレクトリを作成。
公開領域のパスはデフォルトでは/var/www/publicとなっている。
変更する場合はproject/laradock/nginx/sites/defalt.confのrootを変更する。

```bash:/project/src
mkdir public
```

## 公開領域に公開したいファイルを作成

公開したいファイルを公開領域に作成する。

公開したいファイルにindex.php、index.html、index.htmのいずれかが含まれていると、それらはリクエストのURIが"/"で終わっている（つまりディレクトリになっている）ときにインデックスとして使われるファイルとなる。
インデックスとして使われるファイルの名前を変更したい場合はproject/laradock/nginx/sites/defalt.confのindexを変更する。

## 動作確認

ブラウザを開いて、Windowsであれば「http://192.168.99.100」に、Macであれば「http://localhost」にアクセスする。
公開したファイルが正しく表示できてれば成功。