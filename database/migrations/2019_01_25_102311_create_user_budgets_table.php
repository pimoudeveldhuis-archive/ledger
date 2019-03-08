<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_budgets', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id')->index();
            
            $table->longText('name');
            $table->longText('description');
            $table->longText('icon')->nullable();

            $table->unsignedInteger('currency_id')->index();
            $table->bigInteger('default_amount');
            $table->longText('conditions')->nullable();

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
        Schema::dropIfExists('user_budgets');
    }
}
