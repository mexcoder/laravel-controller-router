this a router that extends laravel default router adding the old Controller &amp; controllers method deprecated on laravel/framework 5.2

Laravel decided to complety remove this feature on Laravel/Framework 5.3, i personally liked it and extracted it from the repo history, made this package and added some functionality to make it better
the router is still fully compatible with code using the existing ```Route::controller``` function right out of the box

# how to install

Add the following to your composer file:

```json
"repositories": [
   {
        "type": "vcs",
        "url": "https://github.com/mexcoder/laravel-controller-route.git"
    }
],
"require": {
    "mexcoder/laravel-controller-route": "dev-vainilla"
}
```

and in you bootstrap.php add the folowing

```php
/*
|--------------------------------------------------------------------------
| Bind the new Router to override the laravel default router
|--------------------------------------------------------------------------
|
*/
$app->singleton('router', Mexcoder\Routing\Router::Class);
```   
 
before the line

```php
return $app;
```

# Alternatives
Dont like messing with the laravel router? thats fine try [AdvancedRoute from Milan Lesichkov](https://github.com/lesichkovm/laravel-advanced-route), great package :simple_smile:
