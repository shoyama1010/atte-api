# atte-api（勤怠管理：バックエンド）
＊フロントエンドもログイン機能以外は、Next.jsにて実装。API連携にて、SPA化。

フロントエンド(Next.js)：https://github.com/shoyama1010/attendance-frontend

# 作成した目的

ユーザーの勤怠打刻記録と、管理者での勤怠管理及びスタッフ管理を目的としてます。

# アプリケーションURL
ローカル環境
http://localhost/attendance

＊自動的にログイン画面に移動します。会員登録してない方は、会員登録⇒メール認証となります

# 機能一覧

・ユーザー認証（登録＆ログイン）機能  ＊フロント側への補足で、最下位箇所に記載。

・メール認証機能（応用機能　＊詳細は、補足で最下位箇所に記載）

・（勤怠）打刻機能（＊補足で最下位箇所に記載）

・管理者認証機能

・ユーザー側・ＣＲＵＤ機能（勤怠情報取得、月情報取得、詳細遷移）

・管理側・ＣＲＵＤ機能（勤怠情報取得、月情報取得、日時変更、詳細遷移）
＊バリデーション機能については、補足にて。

・ユーザー側・勤怠詳細⇒修正申請機能（承認待ち＝承認済情報取得、申請詳細表示）

・管理側・勤怠詳細⇒修正申請機能（承認待ち＝承認済情報取得、申請詳細表示
＊管理側は、ユーザーの承認代行をした時（承認済）は、修正できないように設定している。

・CSVエクスポート機能（応用機能　＊詳細は、補足で最下位箇所に記載）

# 使用技術
・Laravel 8

・nginx 1.21.1

・php 8.0

・html

・css

・mysql 8.0.26

・fortfy（laravel認証）

・formrequest（laravelバリデーション）

・Sanctum　v2.14.1

# テーブル設計

<img width="396" height="519" alt="Image" src="https://github.com/user-attachments/assets/4143f58d-d6e3-4968-887a-053a66b90ccc" />

<img width="398" height="441" alt="Image" src="https://github.com/user-attachments/assets/e33a289b-f874-45ea-95c6-ba1ba519d727" />

# ER図

<img width="1536" height="1024" alt="Image" src="https://github.com/user-attachments/assets/e18749f6-d80f-43f0-bdc0-a47c0588cda7" />

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

PHPコンテナにログインし(ログインしたままであれば上記コマンドは実行しなくて良いです。)

php artisan migrate

## 6 ダミーデータ作成

PHPコンテナにログインした状態で

php artisan db:seed

## 7 アプリケーション起動キーの作成

PHPコンテナにログインした状態で

php artisan key:generate

## 8　各種機能についての補足

＊Next側へのログイン：マルチログインページの「ログイン」からユーザーで入る⇒メインページ(打刻画面)のヘッダー部の「勤怠一覧（Next）」にて遷移。

http://localhost:3000/attendances
<img width="1290" height="669" alt="Image" src="https://github.com/user-attachments/assets/f07e10b9-9c4b-4a96-b515-4cec692e9087" />

<img width="1277" height="670" alt="Image" src="https://github.com/user-attachments/assets/d3569c47-07de-448b-826e-82a5137ce2a2" />

バリデーション機能（退勤及び休憩の両方に不適切な値になれば、両方バリデーション出すようにしてる）
<img width="1166" height="669" alt="Image" src="https://github.com/user-attachments/assets/e69f90fd-bf0e-46dc-8060-dd7a82448e6f" />

・メール認証機能
<img width="1190" height="675" alt="Image" src="https://github.com/user-attachments/assets/0f416545-107a-4715-bf93-7f205f1c0748" />

・CSV出力機能
<img width="1176" height="679" alt="Image" src="https://github.com/user-attachments/assets/d882022e-6901-4e87-bbee-fb97deb44fd2" />

・打刻機能（status:①状態は、見た目わかるように、色を変えてます。②最初の出勤時のみ、誰かわかるように、ユーザー名を入れてます。）
①<img width="1213" height="675" alt="Image" src="https://github.com/user-attachments/assets/f755c14d-846e-4639-94c8-cb577ced8b97" />
②<img width="1366" height="687" alt="Image" src="https://github.com/user-attachments/assets/a57febb7-7525-4833-8bda-d51947f1cce7" />
