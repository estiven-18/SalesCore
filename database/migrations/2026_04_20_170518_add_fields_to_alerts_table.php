<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->string('alertable_type')->nullable()->after('id');
            $table->unsignedBigInteger('alertable_id')->nullable()->after('alertable_type');
            $table->index(['alertable_type', 'alertable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropIndex(['alertable_type', 'alertable_id']);
            $table->dropColumn(['alertable_type', 'alertable_id']);
        });
    }
};