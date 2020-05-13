<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperationLoggerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hp_operation_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trigger_class', 96)->default('')->comment('触发类');
            $table->integer('associated_id', 11)->default('')->comment('关联主键');
            $table->string('associated_value', 32)->default('')->comment('主表与子表关联字段值');
            $table->string('user_id', 32)->default('')->comment('用户识别id');
            $table->string('client_ip', 32)->default('')->comment('客户端ip');
            $table->dateTime('trigger_time')->comment('触发时间');
            $table->string('event_desc', 256)->default('')->comment('时间描述');
            $table->string('change_content', 2048)->default('')->comment('变化内容');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hp_operation_logger', function (Blueprint $table) {
            //
        });
    }
}
