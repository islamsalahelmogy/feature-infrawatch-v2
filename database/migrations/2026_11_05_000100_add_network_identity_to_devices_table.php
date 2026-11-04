<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('mac_address', 17)->nullable()->after('management_ip');
            $table->string('default_gateway', 45)->nullable()->after('mac_address');
            $table->string('subnet_mask', 45)->nullable()->after('default_gateway');
            $table->string('ip_assignment', 20)->default('static')->after('subnet_mask');
            $table->unique(['site_id', 'mac_address']);
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropUnique(['site_id', 'mac_address']);
            $table->dropColumn(['mac_address', 'default_gateway', 'subnet_mask', 'ip_assignment']);
        });
    }
};
