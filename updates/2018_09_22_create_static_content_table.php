<?php

namespace Newride\Headless\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateStaticContentTable extends Migration
{
    public function up()
    {
        Schema::create('newride_headless_staticcontent', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();

            $table->string('page_name')->unique();
            $table->json('data');
        });
    }

    public function down()
    {
        Schema::dropIfExists('newride_headless_staticcontent');
    }
}
