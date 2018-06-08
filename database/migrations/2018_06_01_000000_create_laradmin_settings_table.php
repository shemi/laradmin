<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaradminSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('la_settings', function (Blueprint $table) {
            $table->increments('id');

            $table->string('key', 100);
            $table->json('value')->nullable();
            $table->string('bucket', 100);
            $table->string('type', 100);

            $table->timestamps();

            $table->index(['key']);
            $table->index(['key', 'bucket']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
}
