<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->integer('school_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('first_name_kana', 100);
            $table->string('last_name_kana', 100);
            $table->string('email', 100);
            $table->char('password', 64);
            $table->tinyInteger('role')->default('2');
            $table->tinyInteger('is_verified')->default('0');
            $table->string('updated_by', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teachers');
    }
}
