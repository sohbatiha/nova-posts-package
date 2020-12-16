# nova posts resource package

### A powerful package to add posts resource to nova panel.

### requirements:

1. laravel
2. laravel nova v2 or greater

### Installation:

```bash
composer require sohbatiha/nova-posts
```

Then you can publish and migrate to create the needed tags table

```bash
php artisan vendor:publish
php artisan migrate
```

### additional fields :

for add additional fields to posts resource add below code to your service provider :
* array key values uses for priority of each field.

```php
#use Illuminate\Support\Facades\Event;
#use Laravel\Nova\Fields\ID;

public function boot()
{
    //...
    Event::listen("PostPackage::Fields" , function(){
        return [
            11=>ID::make()->sortable(),
            //and other fields ...
        ];
    });
    //...
};
```

### Supported languages :
1. English
2. Persian




