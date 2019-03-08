<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_imports', function (Blueprint $table) {
            $table->increments('id');

            $table->uuid('uuid')->index();

            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('import_configuration_id')->index();
            $table->longText('filename');
            $table->longText('errors');

            $table->boolean('processed')->default(false);

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
        Schema::dropIfExists('user_imports');
    }
}
