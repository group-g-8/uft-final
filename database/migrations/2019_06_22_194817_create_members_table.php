<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->bigIncrements('member_Id');
            $table->integer('memberDistrict');
            $table->string('fname');
            $table->string('LName');
            $table->string('recommender');
            $table->enum('gender',['F','M']);
            $table->boolean('status')->default('0');
            $table->varchar('districtNO');
            $table->integer('agentid');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
}
