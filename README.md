# atte-api（バックエンド）

# 作成した目的



# アプリケーションURL
ローカル環境
http://localhost/login
＊会員登録してない方は、会員登録⇒メール認証となります

# 機能一覧

・ユーザー認証（登録＆ログイン）機能

・メール認証機能

・（勤怠）打刻機能

・管理者認証機能

・ユーザー側ＣＲＵＤ機能

・管理側ＣＲＵＤ機能

・勤怠一覧＆詳細＆申請修正機能（ユーザー側と管理側）

# 使用技術
・Laravel 8

・nginx 1.21.1

・php 8.0

・html

・css

・mysql 8.0.26

・

# テーブル設計




# ER図

<img width="1024" height="1024" alt="Image" src="https://github.com/user-attachments/assets/59dc6945-2823-4fd4-b136-50065e953d02" />

# 環境構築
## 1 Gitファイルをクローンする

git clone https://github.com/shoyama1010/atte-api.git

## 2 Dockerコンテナを作成する

docker-compose up -d --build

## 3 Laravelパッケージをインストールする

docker-compose exec php bash
でPHPコンテナにログインし

composer install

## 4 .envファイルを作成する

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

MAIL_USERNAME=null

MAIL_PASSWORD=null

MAIL_ENCRYPTION=null

MAIL_FROM_ADDRESS=noreply@example.com 

MAIL_FROM_NAME="laravel"

## 5 テーブルの作成

docker-compose exec php bash

でPHPコンテナにログインし(ログインしたままであれば上記コマンドは実行しなくて良いです。)

php artisan migrate

## 6 ダミーデータ作成

PHPコンテナにログインした状態で

php artisan db:seed

## 7 アプリケーション起動キーの作成

PHPコンテナにログインした状態で

php artisan key:generate
