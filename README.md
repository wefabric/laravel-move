# Laravel admin panel powered by Livewire and Jetstream

[![Latest Version on Packagist](https://img.shields.io/packagist/v/uteq/laravel-move.svg?style=flat-square)](https://packagist.org/packages/uteq/laravel-move)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/uteq/laravel-move/run-tests?label=tests)](https://github.com/uteq/laravel-move/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/uteq/laravel-move.svg?style=flat-square)](https://packagist.org/packages/uteq/laravel-move)

Move makes it very easy to create your own Admin Panel using Laravel and Livewire. 
This package was heavily inspired bij Laravel Nova. And works practically the same.
 


## Todo
- Translations
- Package dependencies
- Tests
- No more DTO's and Actions required (only when registered)

## Support us
The best support for now is improving this package. There is still a lot work to be done, every help is welcome.

## Installation

You can install the package via composer:

```bash
composer require uteq/laravel-move
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Uteq\Move\MoveServiceProvider" --tag="migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Uteq\Move\MoveServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

``` php
$laravel-move = new Uteq\Move();
echo $laravel-move->echoPhrase('Hello, Uteq!');
```
### Route prefix
Move out of the box adds a prefix to your resources, that way it will never interfere with your own routes.
The default is `move`.
You can change the default prefix by overwriting it:

```php
use Illuminate\Support\Facades\Route;

function boot()
{
    Route::move('my-prefix');
}
```

If you prefer to completely disable the prefix, change the `move` prefix to `null`:

```php
use Illuminate\Support\Facades\Route;

function boot()
{
    Route::move();
}
```

### Manually Registering Resource Namespaces
The default namespace for Move is App\Move. You are also able to register the Move Resources wherever you like.
You can Bootstrap this namespace in the following way

```php
use Uteq\Move\Facades\Move;

/**
 * Bootstrap your package's services.
 */
public function boot()
{
    Move::resourceNamespace('App\\Resources', 'resources');
}
```

This will automatically create the namespace for the routes.
The default route for this namespace will be:

```
https://move.test/move/resources/your-resource
```

The resource default name will than be:

```
resources.your-resource
```

### Resolving a resource
Resolving a resource means loading the concrete implementation of your Resource class.
You can do this by providing the name of your resource:

```php
use \Uteq\Move\Facades\Move;

Move::resolveResource('resources.your-resource');
```

### Overwriting the default $actionHandlers
Action handlers are classes that make it possible to store and delete your resources.
Move provides two default action handlers `Uteq\Move\DomainActions\StoreResource` and `Uteq\Move\DomainActions\DeleteResource`.
You are able to overwrite these handlers from your Resource and by default.

#### Overwriting the default $actionHandlers system wide
Overwrite the action handlers from a ServiceProvider
```php
use Uteq\Move\Resource;

public function register()
{
    Resource::$defaultActionHandlers = [
        'update' => StoreSystemWide::class,
        'create' => StoreSystemWide::class,
        'delete' => DestroySystemWide::class,
    ];
}
````

#### Overwriting the default $actionHandlers from your resource
Simply add the $actionHandlers to your resource:
```php
use Uteq\Move\Resource;

class CustomResource extends Resource
{
    public array $actionHandlers = [
        'update' => StoreCustomResource::class,
        'create' => StoreCustomResource::class,
        'delete' => DestroyCustomResource::class,
    ];
}
```  
This will overwrite the system wide action handlers.

### Hooks
### Before save
You can hook into the Store action by adding a beforeSave method that provides callables
```php
public function beforeSave()
{
    return [
        fn($resource, $model, $data) => $data['rand'] = rand(1, 99),
        function($resource, $model, $data) {
            return $data['rand'] = rand(1, 99);
        },
        new MyCustomBeforeSaveAction,
    ];
}
```

Another way to hook into the before save is using the default Laravel saving event. 

### After save

TODO see before save

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Nathan Jansen](https://github.com/uteq)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.