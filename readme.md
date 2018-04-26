# Laravel CRUD Reverse Scaffold Generator

## About
Generates CRUD (Views, Controller, Model, Route and Lang files) from a database table.
This generator inspired from the scala skinny framework.

## Installation
`composer require ichikawa-y-ac/reverse-scaffold-generator`

## Usage
`php artisan make:reverse tablename [-f|--force]`

## Example
`php artisan make:reverse foo`

Generator will generate or add contents bellow files.
```
/app/Foo.php
/app/Http/FooController.php
/resources/views/foos/index.blade.php
/resources/views/foos/show.blade.php
/resources/views/foos/create.blade.php
/resources/views/foos/edit.blade.php
/resources/views/foos/_form.blade.php
/resources/lang/en/message.php
/routes/web.php
```


## Generated view
![sample image](https://user-images.githubusercontent.com/37093205/39172907-2e6c2418-47de-11e8-9263-ce5077f9b50b.png)
