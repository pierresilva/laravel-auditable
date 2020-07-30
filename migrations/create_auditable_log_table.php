<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditableLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auditable_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('auditable_type');
            $table->integer('auditable_id');
            $table->integer('user_id')->nullable();
            $table->string('key');
            $table->longText('old_value')->nullable();
            $table->longText('new_value')->nullable();
            $table->timestamps();

            $table->index(['auditable_id', 'auditable_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auditable_log');
    }
}
