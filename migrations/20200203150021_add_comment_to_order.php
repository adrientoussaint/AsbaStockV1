<?php

use \App\Migration\Migration;

class AddCommentToOrder extends Migration
{
    public function up() {
        $this->schema->table('orders', function(Illuminate\Database\Schema\Blueprint $table){ 
            $table->char('comment',255)->after('order_info');
        });
    }
    public function down() {
        $this->schema->table('orders', function(Illuminate\Database\Schema\Blueprint $table){ 
            $table->dropColumn('comment');
        });
    }
}
