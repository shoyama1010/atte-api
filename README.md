# atte-api（勤怠管理アプリ）

## アプリ概要

ATTEは、従業員の勤怠管理業務を効率化することを目的とした勤怠管理システムです。

一般ユーザーは、出勤・退勤・休憩時間の記録や勤怠履歴の確認、勤務時間修正申請を行うことができます。

管理者は、従業員の勤怠情報や修正申請を管理し、申請内容の承認・却下を行えます。

本アプリは Laravel を用いてバックエンドを構築し、Docker による開発環境で実装しました。

現在は Railway を利用して本番公開しており、メール認証・管理者機能・勤怠修正申請などの主要機能を実装しています。

- なお、フロントエンドは Next.js による SPA 化を進めており、ログイン機能を含む段階的な移行を行っています。
- フロントエンド(Next.js)：https://github.com/shoyama1010/attendance-frontend

## 作成した目的

想定ユーザー（一般従業員・管理者）として、勤怠打刻から勤怠修正申請と、管理者側では、勤怠の承認による管理及びスタッフ管理を目的としてます。

## アプリケーションURL

 ローカル環境：http://localhost/attendance
- 自動的にログイン画面に移動します。会員登録してない方は、会員登録⇒メール認証となります。

公開環境（Railway）:https://atte-api-production.up.railway.app/attendance


## 機能一覧

・ユーザー認証（登録＆ログイン）機能  （補足１にて記載）

・メール認証機能（応用機能　補足２にて記載）

・勤怠打刻機能（補足３にて記載）

・管理者認証機能（補足４にて記載）

・ユーザー側・ＣＲＵＤ機能（勤怠情報取得、月情報取得、詳細遷移）

・管理側・ＣＲＵＤ機能（勤怠情報取得、月情報取得、日時変更、詳細遷移）
　
 - ユーザー側＆管理側の詳細画面のバリデーション機能については補足５にて。

・ユーザー側・勤怠詳細⇒修正申請機能（承認待ち＝承認済情報取得、申請詳細表示）

・管理側・申請一覧⇒修正申請機能（承認待ち＝承認済情報取得、申請詳細表示

- 管理側は、ユーザーが行う申請を、代行した後（承認済）は、修正できないように設定している。

・CSVエクスポート機能（応用機能　補足４にて記載）

・休憩時間複数機能（補足６）

## 使用技術
・Laravel 8

・Nginx 1.21.1

・PHP 8.0

・html/css (レスポンシブ対応)

・Mysql 8.0.26

・Fortfy（laravel認証）

・FormRequest（laravelバリデーション）

・Sanctum　v2.14.1（API連携のため：version・downしてます⇒将来的にPHPのversion・upにより、Sanctumもversion・up予定）

## テーブル設計

<img width="396" height="519" alt="Image" src="https://github.com/user-attachments/assets/4143f58d-d6e3-4968-887a-053a66b90ccc" />

<img width="398" height="441" alt="Image" src="https://github.com/user-attachments/assets/e33a289b-f874-45ea-95c6-ba1ba519d727" />

## ER図

<img width="1536" height="1024" alt="Image" src="https://github.com/user-attachments/assets/e18749f6-d80f-43f0-bdc0-a47c0588cda7" />

## 環境構築

 1 Gitファイルをクローンする

git clone git@github.com:shoyama1010/atte-api.git

 2 Dockerコンテナを作成する

docker-compose up -d --build

 3 Laravelパッケージをインストールする

docker-compose exec php bash
でPHPコンテナにログインし

composer install

 4 .envファイルを作成する

PHPコンテナにログインした状態で

cp .env.example .env

作成した.envファイルの該当欄を下記のように変更

DB_HOST=mysql

DB_DATABASE=laravel_db

DB_USERNAME=laravel_user

DB_PASSWORD=laravel_pass

MAIL_MAILER=smtp

MAIL_HOST=mailhog

MAIL_PORT=1025

MAIL_FROM_ADDRESS=no-reply@atte.com

 5 テーブルの作成

docker-compose exec php bash

php artisan migrate

 6 ダミーデータ作成

（通常は、php artisan db:seedですが）

※ 本プロジェクトはカラム削除・ENUM変更を含むため、 環境構築時は必ず以下を実行してください。

php artisan migrate:fresh --seed

（理由：①break_start / break_end を削除→ rests テーブルへ分離、②status ENUM を拡張→editable, approved, on_break, left 追加したため）

＊UserSeeder.phpにて、Factoryを使ってランダム10人分 登録済

*AttendanceSeeder.phpにて、30日分自動生成済。

 7 アプリケーション起動キーの作成

PHPコンテナにログインした状態で

php artisan key:generate

## 公開環境

バックエンドを Railway にデプロイしています。

構成

- Laravel 11
- PHP 8.2
- MySQL（Railway）
- Docker
- Nginx

公開URL
- https://atte-api-production.up.railway.app/login

## デモアカウント
【一般ユーザー】

メール：junpei@yahoo.com

パスワード：********

【管理者】

メール：admin@example.com

パスワード：********

##　各種機能についての補足

１．ユーザー認証機能
<img width="1290" height="669" alt="Image" src="https://github.com/user-attachments/assets/f07e10b9-9c4b-4a96-b515-4cec692e9087" />

２．メール認証機能(ローカル環境：http://localhost:8025)
<img width="1190" height="675" alt="Image" src="https://github.com/user-attachments/assets/0f416545-107a-4715-bf93-7f205f1c0748" />

３．打刻機能
<img width="1277" height="670" alt="Image" src="https://github.com/user-attachments/assets/d3569c47-07de-448b-826e-82a5137ce2a2" />

  3.1　① statusは見た目わかるように、色を変えてます。
  ①<img width="1213" height="675" alt="Image" src="https://github.com/user-attachments/assets/f755c14d-846e-4639-94c8-cb577ced8b97" />
  
  3.2　②最初の出勤時のみ、誰かわかるように、ユーザー名を入れてます。
  <img width="1366" height="687" alt="Image" src="https://github.com/user-attachments/assets/a57febb7-7525-4833-8bda-d51947f1cce7" />

４． 管理側ログイン（email -> admin@example.com　　password -> password123）
<img width="1238" height="673" alt="Image" src="https://github.com/user-attachments/assets/1b4a7636-6912-463b-b964-745ad25bd30f" />

５．（CRUD）バリデーション機能（退勤及び休憩の両方に不適切な値になれば、両方バリデーション出すようにしてる）
<img width="1166" height="669" alt="Image" src="https://github.com/user-attachments/assets/e69f90fd-bf0e-46dc-8060-dd7a82448e6f" />

６．CSV出力機能
<img width="1176" height="679" alt="Image" src="https://github.com/user-attachments/assets/d882022e-6901-4e87-bbee-fb97deb44fd2" />
-出力イメージ
<img width="806" height="360" alt="Image" src="https://github.com/user-attachments/assets/24328e2a-f1ad-415e-963a-c9520a78a25d" />

７．休憩複数機能
<img width="1160" height="675" alt="Image" src="https://github.com/user-attachments/assets/327db922-8560-4486-8987-e01e10903d53" />

<img width="1169" height="681" alt="Image" src="https://github.com/user-attachments/assets/84203133-a777-4b0c-8d8a-f8ee07e630bb" />

８．スタッフ一覧機能
<img width="1150" height="677" alt="Image" src="https://github.com/user-attachments/assets/1ed364e0-0183-4976-916b-f4f160942dde" />

## テストの実行方法

本アプリでは Laravel 標準の PHPUnit を使用して、
勤怠情報修正機能（一般ユーザー）などの 自動テスト（Feature Test / Unit Test） を実装しています。

＊＊ ユニットテストは、今後見直しがあり、習得後、記載いたします。（「Target class [validator] does not exist.」などのエラーによりPASSの確認ができてない箇所あり）

### 1. テスト用データベースの準備

テストを実行する前に、以下の手順でテスト専用のMySQLデータベースをセットアップしてください。

1.  **MySQLデータベースの作成**: MySQLクライアントを使用して、`laravel.testing`という名前の新しいデータベースを作成します。

    ```bash
    mysql -u [your_username] -p
    # パスワードを入力
    CREATE DATABASE laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;;
    SHOW DATABASES;

    *configファイルの変更

    // mysql_test作成
        'mysql_test' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

2.  **.env.testing ファイルの設定**: プロジェクトのルートディレクトリにある `.env` ファイルを複製し、ファイル名を `.env.testing` に変更します。このファイルはテスト実行時に自動的に読み込まれます。

    ```bash
    cp .env .env.testing --env=testing
    ```

3.  **.env.testing の作成と設定**: `.env.testing`
4. プロジェクト直下に .env.testing ファイルを作成し、以下を設定します。（本番 .env とは完全に分離されたテスト専用設定）
5.  ファイルを開き、データベース接続情報をテスト用のものに変更します。

   APP_ENV=testing
APP_KEY=base64:4tANb4lpS8mAJoleyoDj3iRGhWzhWBm9hkRWN8=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_testing  # ← テスト専用DB
DB_USERNAME=root
DB_PASSWORD=root

CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array

.env.testing は PHPUnit 実行時に自動で読み込まれ、
開発用DB（laravel_db）を保護したままテストが行われます。

6.  **マイグレーションの実行**: テストデータベースにテーブルを作成するため、マイグレーションを実行します。

    php artisan migrate 
    
### 2. テストの実行

データベースの準備ができたら、以下のコマンドでテストスイート全体を実行できます。

php artisan test または

./vendor/bin/phpunit

## 工夫した点

### 勤怠データと修正申請データを分離した設計

通常の勤怠データ（attendance）と修正申請（correction_requests）を分けることで、承認フローに対応した構造にしました。これにより「元データ」と「申請データ」を明確に管理できるようにしています。

### 休憩時間の複数登録対応

休憩は1対多のリレーション（attendance : rests）で設計し、複数の休憩時間を登録できるようにしました。フォームから配列で送信し、Controllerでループ処理することで柔軟に対応しています。

### 配列データのバリデーション
rests[0][break_start] のような配列形式の入力に対して、バリデーションやエラーメッセージの表示を行うのに苦労しました。

### CSV出力時のデータ整形

複数の休憩時間をどのようにCSVに出力するか悩み、最終的に「/区切り」でまとめることで解決しました。

## 承認状態による画面制御

「承認待ちの場合は編集不可」とし、条件分岐によってフォームと閲覧画面を切り替えることで、ユーザーの操作ミスを防ぐ設計にしました。

## 休憩時間の動的表示

休憩が複数ある場合は「休憩」「休憩2」「休憩3」と行ごとに表示することで、視認性を向上させました。また、常に1行分の空入力を表示することで追加入力しやすくしています。

## 苦労した点
## 配列入力フォームの実装

name="rests[0][break_start]" のような構造を理解し、Controller側と連携させる部分で苦労しました。

## 表示とデータの整合性
Controllerで整形したデータとBlade側の表示を一致させるのに時間がかかりました。

## CSSレイアウト調整
UI構築において、テーブル表示とフォーム表示を両立させるデザイン調整に苦労しました。
