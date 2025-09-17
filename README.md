## Dockerビルド
1. git clone git@github.com:maho4649/attend.git
2. cd attend
3. docker-compose up -d --build


## Laravel環境構築
1. docker-compose exec php bash
2. composer install
3. cp .env.exampleファイルから.envを作成し、環境変数を変更
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed
7. php artisan storage:link
8. vendor/bin/phpunit


## 使用技術
php:7.4.9-fpm
Laravel Framework 8.83.29  
mysql  Ver 9.2.0 for macos15.2 on arm64 (Homebrew)  
  
URL  
開発環境:http://localhost/  
phpMyAdmin:http://localhost:8080  

会員登録画面（一般ユーザー）. 
/register. 
ログイン画面（一般ユーザー）. 
/login. 
出勤登録画面（一般ユーザー）. 
/attendance. 
勤怠一覧画面（一般ユーザー）. 
/attendance/list. 
勤怠詳細画面（一般ユーザー）. 
/attendance/detail/{id}. 
申請一覧画面（一般ユーザー）. 
/stamp_correction_request/list. 
ログイン画面（管理者）. 
/admin/login. 
勤怠一覧画面（管理者）. 
/admin/attendances. 
勤怠詳細画面（管理者）. 
/admin/attendances/{id}. 
スタッフ一覧画面（管理者）. 
/admin/users. 
スタッフ別勤怠一覧画面（管理者）. 
/admin/users/{user}/attendances. 
申請一覧画面（管理者）. 
/admin/requests. 
修正申請承認画面（管理者）. 
/admin/requests/{id}. 