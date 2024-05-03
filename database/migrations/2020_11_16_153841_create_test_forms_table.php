<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_forms', function (Blueprint $table) {
            $table->id();
            $table->integer('subject_id')->nullable();
            $table->string('name')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_stop')->nullable();
            $table->integer('status')->nullable();
            $table->text('file_path')->nullable();
            $table->jsonb('answers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_forms');
    }
}
