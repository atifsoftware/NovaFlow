<?php

use NovaFlow\Core\QueryBuilder\Schema;
use NovaFlow\Core\QueryBuilder\Blueprint;

/**
 * Migration for Background Jobs table
 */
class CreateJobsTable
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->tinyInt('attempts')->default(0);
            $table->dateTime('reserved_at')->nullable();
            $table->dateTime('available_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
