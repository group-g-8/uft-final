<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement( ' CREATE VIEW salaries AS
   SELECT DATE_FORMAT(date, "%m-%Y") AS Month, SUM(Amount) AS amount
   FROM treasuries
   WHERE   DATE_FORMAT(date, "%m-%Y")= DATE_FORMAT(CURRENT_DATE, "%m-%Y")
   GROUP BY DATE_FORMAT(date, "%m-%Y")');
        }
    
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            DB::statement("DROP salaries");
        }
}
