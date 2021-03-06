<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeacherTable extends Migration
{
    public function up()
    {
        Schema::create('teacher', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('status')->default(0);
            $table->unsignedTinyInteger('organization')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('teacher');
    }
}
