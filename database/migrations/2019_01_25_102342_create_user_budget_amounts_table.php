<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBudgetAmountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_budget_amounts', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_budget_id')->index();
            $table->unsignedInteger('currency_id')->index();
            $table->bigInteger('amount');

            $table->unsignedInteger('year')->index();
            $table->unsignedInteger('month')->index();

            $table->timestamps();

            $table->unique(['user_budget_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_budget_amounts');
    }
}
