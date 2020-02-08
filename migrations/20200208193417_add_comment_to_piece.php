<?php

use \App\Migration\Migration;

class AddCommentToPiece extends Migration
{
    public function up() {
        $this->schema->table('pieces', function(Illuminate\Database\Schema\Blueprint $table){ 
            $table->char('comment',255)->default(null);
        });
        $this->schema->table('tirants', function(Illuminate\Database\Schema\Blueprint $table){ 
            $table->char('comment',255)->default(null);
        });
    }
    public function down() {
        $this->schema->table('pieces', function(Illuminate\Database\Schema\Blueprint $table){ 
            $table->dropColumn('comment');
        });
        $this->schema->table('tirants', function(Illuminate\Database\Schema\Blueprint $table){ 
            $table->dropColumn('comment');
        });
    }
}
