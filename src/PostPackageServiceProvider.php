<?php


namespace Sohbatiha\PostPackage;


use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Nova;
use Sohbatiha\PostPackage\Nova\Post;

class PostPackageServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadTranslationsFrom(__DIR__.'/translations', 'post_package');
        $this->publishes([
            __DIR__.'/config/post_package.php' => config_path('post.php'),
        ]);

        /*Event::listen("PostPackage::Fields" , function(){
            return [
                11=>ID::make()->sortable(),
            ];
        });*/

    }

    public function register()
    {
        Nova::resources([Post::class]);

        $this->mergeConfigFrom(
            __DIR__.'/config/post_package.php', 'post_package'
        );
    }

}
