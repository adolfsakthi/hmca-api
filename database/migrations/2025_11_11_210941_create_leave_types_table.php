<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveTypesTable extends Migration
{
    public function up()
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('property_code', 50)->index();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->integer('yearly_limit')->default(0);
            $table->integer('carry_forward_limit')->default(0);
            $table->string('consider_as')->nullable()->comment('e.g. paid, unpaid, sick, etc.');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_code','name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_types');
    }
}