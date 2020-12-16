<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 120);
            $table->unsignedBigInteger('user_id');
            $table->string('slug', 70);
            $table->longText('content')->nullable();
            $table->tinyInteger('status')->default(0);

            $table->json('data')->nullable();

            $table->timestamp('publish_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('slug');
            $table->index('user_id');
            $table->index('status');
            $table->index('publish_at');
            $table->index('deleted_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
