<?php

use \App\Migration\Migration;

class TicketTable extends Migration
{
    public function up() {
        $this->schema->create('tickets', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('name',255);
            $table->char('description',255);
            $table->boolean('type');
        });
    }
    public function down() {
        $this->schema->drop('tickets');
    }
}
