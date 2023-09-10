# laravel-fireflies


> A Laravel Package for working with fireflies seamlessly

## Installation

[PHP](https://php.net) 5.4+ or [HHVM](http://hhvm.com) 3.3+, and [Composer](https://getcomposer.org) are required.

To get the latest version of Laravel Fireflies, simply require it

```bash
composer require sleemkeen/laravel-fireflies
```

Or add the following line to the require block of your `composer.json` file.

```
"sleemkeen/laravel-fireflies": "1.0.*"
```

You'll then need to run `composer install` or `composer update` to download it and have the autoloader updated.



Once Laravel Fireflies is installed, you need to register the service provider. Open up `config/app.php` and add the following to the `providers` key.

```php
'providers' => [
    ...
    Sleemkeen\Fireflies\FirefliesServiceProvider::class,
    ...
]
```

> If you use **Laravel >= 5.5** you can skip this step and go to [**`configuration`**](https://github.com/sleemkeen/laravel-fireflies#configuration)

* `Sleemkeen\Fireflies\FirefliesServiceProvider::class`

Also, register the Facade like so:

```php
'aliases' => [
    ...
    'Fireflies' => Sleemkeen\Fireflies\Facades\Fireflies::class,
    ...
]
```

## Configuration

You can publish the configuration file using this command:

```bash
php artisan vendor:publish --provider="Sleemkeen\Fireflies\FirefliesServiceProvider"
```

A configuration-file named `fireflies.php` with some sensible defaults will be placed in your `config` directory:

```php
<?php

return [

     /**
     * Api Token Generated From Fireflies Dashboard
     *
     */
    'secretKey' => env('FIREFLIES_API_TOKEN'),


];
```


## General api flow

Users are required to natigate to their fireflies integration page via https://app.fireflies.ai/integrations/custom/fireflies to generate api token


## Usage

Open your .env file and add your api token:

```php
FIREFLIES_API_TOKEN=xxxxxxxxxxxxx
```

```php
// Laravel 5.1.17 and above
Route::post('/transcript', 'FirefliesController@fetchTranscript')->name('fireflies');
```

OR

```php
Route::post('/transcript', [
    'uses' => 'FirefliesController@fetchTranscript',
    'as' => 'fireflies'
]);
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Fireflies;

class FireFliesController extends Controller
{
    public function fetchTranscript(){
        $params = ['title','date','duration','transcript_url','id'];        
        return Fireflies::handleTranscriptsQuery($params);

    }

    public function fetchUsers(){
        $arr = ['name', 'email', 'is_admin','integrations'];
        return Fireflies::handleUsersQuery($arr);
    }

     public function fetchUser(){
        $arr = ['name', 'email', 'is_admin','integrations'];
        $id = "TS6MS4upoau74JEmi";
        return Fireflies::handleUserQuery($arr, $id);

    }

}


## Contributing

Please feel free to fork this package and contribute by submitting a pull request to enhance the functionalities.

## How can I thank you?

Why not star the github repo? I'd love the attention! Why not share the link for this repository on Twitter or HackerNews? Spread the word!

Don't forget to [follow me on twitter](https://twitter.com/sleemkeen)!

Thanks!
Haruna Ahmadu.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
