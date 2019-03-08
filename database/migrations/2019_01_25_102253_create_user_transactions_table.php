<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('user_account_id')->index();
            $table->unsignedInteger('user_import_id')->index();

            $table->unsignedInteger('user_category_id')->index()->nullable();
            $table->unsignedInteger('user_budget_id')->index()->nullable();

            $table->unsignedInteger('currency_id')->index();

            $table->date('book_date')->index();
            $table->string('type', 2)->index();
            $table->enum('dw', ['deposit', 'withdrawal'])->default('deposit');

            $table->longText('description')->nullable();
            $table->longText('reference')->nullable();

            $table->longText('contra_account')->nullable();
            $table->string('contra_account_hash', 128)->nullable()->index();
            $table->longText('contra_account_name')->nullable();

            $table->bigInteger('amount');

            $table->string('duplicate_hash', 128)->index();
            $table->boolean('duplicate')->default(false)->index();

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
        Schema::dropIfExists('user_transactions');
    }
}
