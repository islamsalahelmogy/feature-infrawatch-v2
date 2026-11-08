<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('monitoring_schedules', function (Blueprint $table) {
        $table->string('frequency')->nullable();
        $table->json('times')->nullable();
        $table->unsignedTinyInteger('weekday')->nullable();
        $table->unsignedTinyInteger('monthday')->nullable();
    }); }
    public function down(): void { Schema::table('monitoring_schedules', fn (Blueprint $table) => $table->dropColumn(['frequency','times','weekday','monthday'])); }
};
