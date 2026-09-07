# WorkFlow（勤怠管理アプリ）

## アプリ概要
<img width="1355" height="630" alt="スクリーンショット (6546)" src="https://github.com/user-attachments/assets/c4522f10-306c-4c4b-9e30-38f4f99ef2e7" />

WorkFlowは、従業員の勤怠管理業務を効率化することを目的とした勤怠管理アプリです。

一般ユーザーは、出勤・退勤・休憩時間の記録や勤怠履歴の確認、勤務時間修正申請を行うことができます。

管理者は、従業員の勤怠情報や修正申請を管理し、申請内容の承認・却下、勤怠情報の代理修正、CSV出力などを行えます。

本アプリは Laravel を用いてバックエンドを構築し、Docker による開発環境で実装しました。

現在は Railway を利用して本番公開しており、メール認証・管理者機能・勤怠修正申請などの主要機能を実装しています。

- なお、フロントエンドは Next.js による SPA 化を進めており、ログイン機能を含む段階的な移行を行っています。
- フロントエンド(Next.js)：https://github.com/shoyama1010/attendance-frontend

## 作成した目的

想定ユーザー（一般従業員・管理者）として、勤怠打刻から勤怠修正申請と、管理者側では、勤怠の承認による管理及びスタッフ管理を目的としてます。


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

## 各種機能についての補足

１．ユーザー認証機能
<img width="1348" height="632" alt="スクリーンショット (6543)" src="https://github.com/user-attachments/assets/74b938d7-91c7-4df6-879c-6a0efea6e1b1" />

２．メール認証機能(ローカル環境：http://localhost:8025)
<img width="1306" height="616" alt="スクリーンショット (6552)" src="https://github.com/user-attachments/assets/905c194a-fb8b-4fe1-a8bd-766b17d6617a" />

３．打刻機能
<img width="1345" height="634" alt="スクリーンショット (6545)" src="https://github.com/user-attachments/assets/924eb90e-eebc-4382-8511-5d8ab4558d79" />

  3.1　① statusは見た目わかるように、色を変えてます。
  <img width="1347" height="671" alt="スクリーンショット (6772)" src="https://github.com/user-attachments/assets/cd854b87-ef9a-4c13-8b60-3d6fa39519a3" />

  3.2　②最初の出勤時のみ、誰かわかるように、ユーザー名を入れてます。
<img width="1353" height="671" alt="スクリーンショット (6773)" src="https://github.com/user-attachments/assets/c7d8ff83-e485-40ba-b22a-9b76c9ac1fd0" />

４． 管理側ログイン（email -> admin@example.com　　password -> password123）
<img width="1346" height="630" alt="スクリーンショット (6555)" src="https://github.com/user-attachments/assets/a3d17bea-ecd5-4050-8a39-9fb5f878cb2d" />

５．（CRUD）バリデーション機能（退勤及び休憩の両方に不適切な値になれば、両方バリデーション出すようにしてる）
<img width="1166" height="669" alt="Image" src="https://github.com/user-attachments/assets/e69f90fd-bf0e-46dc-8060-dd7a82448e6f" />

６．CSV出力機能
<img width="1286" height="671" alt="スクリーンショット (6777)" src="https://github.com/user-attachments/assets/8456c17d-aa9b-43b9-9d02-fbc57e532ef7" />
-出力イメージ
<img width="806" height="360" alt="Image" src="https://github.com/user-attachments/assets/24328e2a-f1ad-415e-963a-c9520a78a25d" />

７．休憩複数機能
<img width="1116" height="648" alt="スクリーンショット (6774)" src="https://github.com/user-attachments/assets/e8f4a971-c0dd-45ac-b224-398beb5e2f24" />
<img width="1221" height="682" alt="スクリーンショット (6775)" src="https://github.com/user-attachments/assets/013f0cbe-89df-4dcf-9710-09a6f8a56010" />

８．スタッフ一覧機能
<img width="1268" height="675" alt="スクリーンショット (6776)" src="https://github.com/user-attachments/assets/b72196ec-0987-487a-8dc2-a574a788fcf7" />

## 使用技術
・Laravel 8.83

・Nginx 1.21.1

・PHP 8.2

・html(Blade)

・css (レスポンシブ対応)

・Mysql 8.0.26

・Docker

・Fortfy（laravel認証）

・FormRequest（laravelバリデーション）

・MailHog（ローカル）

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

バックエンドを Railway にデプロイして公開してます。＊フロント開発後、Verselでも公開予定です。

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

メールアドレス：workflow-test@example.com

パスワード：password

【管理者】

メール：admin@example.com

パスワード：password123

## テスト

本アプリでは、Laravel標準の PHPUnit を使用し、勤怠修正機能および入力バリデーションについて Feature Test を実装しています。

### 実装済みテスト

#### 勤怠修正機能

- 出勤時間が退勤時間より後の場合、バリデーションエラーになること
- 備考が未入力の場合、バリデーションエラーになること
- 正常な入力内容で勤怠情報を更新できること

#### 勤怠入力バリデーション

- 出勤時間が退勤時間より後の場合、入力エラーになること

### テスト用データベース

通常の開発用DBとは分離し、テスト専用データベース `laravel_testing` を使用しています。

`.env.testing` では以下のように設定します。

env

APP_ENV=testing

DB_CONNECTION=mysql

DB_HOST=mysql

DB_PORT=3306

DB_DATABASE=laravel_testing

DB_USERNAME=root

DB_PASSWORD=root

CACHE_DRIVER=array

QUEUE_CONNECTION=sync

#### テスト専用DBを作成後、マイグレーションを実行

php artisan migrate --env=testing

#### 全テストを実行

php artisan test --env=testing

#### テスト結果

PASS Tests\Feature\AttendanceUpdateTest

  ✓ start time after end time returns validation error

  ✓ empty note returns validation error

  ✓ valid data can update attendance

PASS Tests\Feature\AttendanceValidationTest

  ✓ clock in after out fails validation

## 工夫した点
### 勤怠データと修正申請データを分離した設計
通常の勤怠データは `attendances`、修正申請は `correction_requests` に分けて管理しています。

修正申請時は元の勤怠データを直接更新せず、修正前・修正後の内容を申請データとして保存し、管理者が承認した時点で勤怠へ反映する構成にしました。

これにより、元データと申請内容を分離し、承認フローを明確に管理できるようにしています。

### 休憩時間の複数登録対応

休憩は1対多のリレーション（attendance : rests）で設計し、複数の休憩時間を登録できるようにしました。フォームから配列で送信し、Controllerでループ処理することで柔軟に対応しています。

当初は `attendances` に休憩開始・終了時刻を持たせていましたが、1日に複数回の休憩へ対応するため、`rests` テーブルへ分離しました。

`Attendance : Rest = 1 : N` のリレーションとし、1件の勤怠に複数の休憩時間を登録できる構成にしています。

### 勤務時間・休憩時間の集計

複数の休憩時間を合計し、出勤時刻から退勤時刻までの時間から休憩時間を差し引くことで、実働時間を算出しています。

### CSV出力時のデータ表示

月別勤怠をCSVとして出力できるようにし、複数休憩がある場合は表示形式を整えて1レコード内にまとめています。

### 承認状態による画面制御

「承認待ちの場合は編集不可」とし、条件分岐によってフォームと閲覧画面を切り替えることで、ユーザーの操作ミスを防ぐ設計にしました。

### 休憩時間の動的表示

休憩が複数ある場合は「休憩」「休憩2」「休憩3」と行ごとに表示することで、視認性を向上させました。また、常に1行分の空入力を表示することで追加入力しやすくしています。

### その他
- Railway MySQL と接続するため、DATABASE_URL や DB_HOST・DB_PORT などの環境変数を本番用に設定し、ローカル環境との差異を最小限にしました。
- GitHub と Railway を連携し、main ブランチへ push するだけで自動デプロイされる CI/CD 環境を構築しました。

## 苦労した点・解決したこと
### 配列入力フォームの実装(複数休憩への対応)

1日に複数回の休憩を扱うため、フォームから配列形式で休憩データを送信し、Controllerでループ処理する構成にしました。

また、`rests.0.break_start` のような配列形式の入力に対するバリデーションとエラーメッセージ表示の調整に時間がかかりました。

### 修正申請と元データの整合性
勤怠修正時に元の勤怠データを直接変更すると、申請前の情報が失われるため、修正内容を `correction_requests` に保存し、管理者承認後に勤怠へ反映する構成へ整理しました。

### 表示とデータの整合性
勤怠・休憩・修正申請を別テーブルで管理しているため、詳細画面や一覧画面で必要なデータを正しく組み合わせて表示する部分に苦労しました。

Eloquentのリレーションを利用し、必要な関連データをまとめて取得することで整理しました。

### CSSレイアウト調整
UI構築において、テーブル表示とフォーム表示を両立させるデザイン調整に苦労しました。

### CSV出力時の複数休憩の整形
複数の休憩時間をCSVの1レコード内でどのように表現するか悩みました。

最終的に、複数休憩を区切り文字でまとめる形に整理し、閲覧しやすい形式にしました。

## その他
- PHP8.2 対応に伴い、Composer の依存関係や Docker イメージの変更を行い、ライブラリの互換性を確認しながら移行しました。
- メール認証では、ローカル環境では MailHog、本番環境では SMTP を利用する構成に切り替え、それぞれの環境で送信できるよう設定を見直しました。

## 今後の改善

- 勤怠修正申請について、現在は休憩1件目を中心に扱っているため、複数休憩すべてを修正申請できる構成へ拡張する予定です。
- 承認処理全体をDBトランザクションで囲み、勤怠・休憩・申請状態の更新をより安全に行えるよう改善予定です。
