<?php

use \App\Migration\Migration;

class AddAlertToPieceTypes extends Migration
{
    public function up() {
        $this->schema->table('piece_types', function(Illuminate\Database\Schema\Blueprint $table){ 
            $table->integer('alert')->after('name');
        });
    }
    public function down() {
        $this->schema->table('piece_types', function(Illuminate\Database\Schema\Blueprint $table){ 
            $table->dropColumn('alert');
        });
    }
}
