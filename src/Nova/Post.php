<?php


namespace Sohbatiha\PostPackage\Nova;


use App\Nova\Resource;
use App\Nova\User;
use Benjaminhirsch\NovaSlugField\Slug;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use Ctessier\NovaAdvancedImageField\AdvancedImage;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;
use Froala\NovaFroalaField\Froala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Sohbatiha\PostPackage\Model\Post as PostModel;

class Post extends Resource
{

    public static $model = PostModel::class;

    public static $title = 'title';

    public static $search = [
        'id', 'title', 'slug',
    ];

    /**
     * @param string $title
     */
    public static function label()
    {
        return __("post_package::post.post");
    }

    private $event_name = "PostPackage";

    public function fields(Request $request)
    {
        return $this->preparingFields([
            ID::make()->sortable(),

            TextWithSlug::make(__("post_package::post.title"), "title")
                ->sortable()
                ->slug('slug')
                ->rules('required', 'max:120'),

            BelongsTo::make(__("post_package::post.user"), 'user', User::class)
                ->onlyOnDetail()
                ->onlyOnIndex()
                ->sortable()
                ->rules('required'),

            Slug::make(__("post_package::post.slug"), "slug")
                ->sortable()
                ->rules('required', 'max:70')
                ->showUrlPreview(env('APP_URL'))
                ->creationRules('unique:posts,slug')
                ->slugifyOptions([
                    "separator" => "_"
                ])
                ->updateRules('unique:posts,slug,{{resourceId}}'),

            Froala::make(__("post_package::post.content"), "content")->options([
                'height' => 400,
                'tuiEnable' => true
            ])
                ->withFiles('public')
                ->attach(function ($request) {
                    return $this->storeImage($request->all()["attachment"]);
                }),

            AdvancedImage::make(__("post_package::post.thumb"), 'thumb')
                ->croppable(config('post_package.thumb.ratio'))
                ->resize(config('post_package.thumb.max_width'))
                ->store(function (Request $request, $model) {
                    return [
                        'thumb' => $this->storeImage($request->thumb)
                    ];
                })->thumbnail(function ($value, $disk) {
                    return url($value);
                })->preview(function ($value, $disk) {
                    return url($value);
                }),

            Select::make(__("post_package::post.status"), "status")
                ->options([
                    0 => __("post_package::post.draft"),
                    1 => __("post_package::post.publish"),
                    2 => __("post_package::post.scheduled"),
                ])
                ->displayUsingLabels()
                ->rules('required'),

            NovaDependencyContainer::make([
                DateTime::make(__("post_package::post.publish_at"), "publish_at")
                    ->sortable(),
            ])->dependsOn('status', 2),


        ]);
    }

    public function storeImage($input_file)
    {
        $file_extension = $input_file->clientExtension();

        $client_file_name = str_replace(".$file_extension", "", $input_file->getClientOriginalName());
        $client_file_name = str_replace(" ", "_", $client_file_name);
        $client_file_name = strtolower($client_file_name);

        $path = config('post_package.store_files_path') . now()->format("Y/m");

        $file_name = $client_file_name . '.' . $file_extension;

        if (Storage::exists($path . '/' . $file_name)) {
            $number = 1;
            do {
                $file_name = $client_file_name . '_' . $number . '.' . $file_extension;
                $number++;
            } while (Storage::exists($path . '/' . $file_name));
        }

        $res = Storage::disk(config('post_package.disk'))->putFileAs($path, $input_file, $file_name);

        return Storage::disk(config('post_package.disk'))->url($res);
    }

    public function preparingFields($input_fields)
    {
        $fields = [];
        foreach ($input_fields as $key => $input_field) {
            $fields[$key * 5][] = $input_field;
        }
        //dispatching [PostPackage::Fields] event
        $event_results = Event::dispatch($this->event_name . '::Fields');

        //put received fields from events to $field with priority;
        foreach ($event_results as $event_result) {
            foreach ($event_result as $key => $field) {
                $fields[$key][] = $field;
            }
        }

        //sort $fields array with keys ;
        ksort($fields);

        //convert 2D $fields array to 1D ;
        $final_fields = [];
        foreach ($fields as $fields_array) {
            foreach ($fields_array as $ky => $field_array) {
                $final_fields[] = $field_array;
            }
        }

        return $final_fields;
    }

    public function cards(Request $request)
    {
        return [];
    }

    public function filters(Request $request)
    {
        return [];
    }

    public function lenses(Request $request)
    {
        return [];
    }

    public function actions(Request $request)
    {
        return [];
    }
}
