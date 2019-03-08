<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');

            $table->longText('name');
            $table->longText('email');
            $table->string('email_hash', 128)->index();

            $table->longText('twofactor')->nullable();
            $table->enum('language', ['nl'])->default('nl');

            $table->date('oldest_transaction')->nullable();
            $table->date('latest_transaction')->nullable();
            
            $table->longText('publickey');
            $table->longText('secretkey');
            $table->longText('recoverykey');

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
        Schema::dropIfExists('users');
    }
}
