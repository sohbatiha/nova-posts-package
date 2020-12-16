<?php

namespace Sohbatiha\PostPackage\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Event;
use Schema;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use SoftDeletes , InteractsWithMedia;

    /**
     * @var mixed
     */
    private static $columns;
    protected $casts = [
        'publish_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // here we get additional fields and save all of them in data field as json .

        static::saving(function ($model) {
            $attributes = $model->attributes;
            $columns = self::getColumns();

            $data = array_filter($attributes, function ($key) use (&$attributes, $columns) {
                if (!in_array($key, $columns)) {
                    unset($attributes[$key]);
                    return true;
                }
                return false;
            }, ARRAY_FILTER_USE_KEY);

            $attributes['data'] = json_encode($data);
            $attributes['slug'] = str_replace(" ", "_", $attributes["slug"]);
            $attributes['user_id'] = auth()->user()->id;
            $model->attributes = $attributes;

            return $model;
        });

        //here we extract the json as model property and unset data field from model .
        static::retrieved(function ($model) {
            if ($model->data) {
                $data = json_decode($model->data, true);
                foreach ($data as $key => $value) {
                    $model->attributes[$key] = $value;
                }
                unset($model->attributes['data']);
            }
            return $model;
        });
    }

    public static function getColumns()
    {
        return Schema::getColumnListing((new self())->getTable());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
