# Laravel CRUD Reverse Scaffold Generator

## About
Laravel5.6用のCRUD (Views, Controller, Model, Route, Lang)をDBスキーマから生成するジェネレーターです。
ViewはBootstrap4ベースで生成します。

## Installation
`composer require ichikawa-y-ac/reverse-scaffold-generator`

## Usage
`php artisan make:reverse tablename [-f|--force]`

## Example
```
$php artisan make:reverse users
Target table is 'users'

 input the Model name [User]:
 >

 input the Controller name [UserController]:
 >

 input the View dirctory name [user]:
 >

 input the Route name [user]:
 >

 input the Lang name [user]:
 >

[generated] App\User.php
[generated] App\Http\Controllers\UserController.php
[generated] resources\views\user\index.blade.php
[generated] resources\views\user\create.blade.php
[generated] resources\views\user\edit.blade.php
[generated] resources\views\user\show.blade.php
[generated] resources\views\user\_form.blade.php
[generated] resources\views\user\layout.blade.php
[modified]  routes\web.php
[generated] resources\lang\en\user\message.php
```

## Generated view
![sample image](https://user-images.githubusercontent.com/37093205/39172907-2e6c2418-47de-11e8-9263-ce5077f9b50b.png)
