<?php

namespace Database\Seeders;

use App\Models\DeviceType;
use App\Models\MonitoringProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'description' => 'Full platform administration']);
        Role::firstOrCreate(['slug' => 'operator'], ['name' => 'Operator', 'description' => 'Inventory and monitoring operations']);
        Role::firstOrCreate(['slug' => 'viewer'], ['name' => 'Viewer', 'description' => 'Read-only dashboard access']);
        foreach ([['Firewall', 'firewall', 'network'], ['Switch', 'switch', 'network'], ['Server', 'server', 'server'], ['Standard Device', 'endpoint', 'endpoint'], ['Access Point', 'access-point', 'network'], ['NVR/DVR', 'nvr-dvr', 'security']] as [$name, $slug, $category]) {
            DeviceType::firstOrCreate(['slug' => $slug], compact('name', 'category'));
        }
        MonitoringProfile::firstOrCreate(['name' => 'Critical ICMP'], ['check_type' => 'icmp', 'interval_seconds' => 30, 'timeout_seconds' => 3, 'retry_count' => 2, 'failure_threshold' => 3, 'is_active' => true]);
        MonitoringProfile::firstOrCreate(['name' => 'Standard ICMP'], ['check_type' => 'icmp', 'interval_seconds' => 120, 'timeout_seconds' => 5, 'retry_count' => 2, 'failure_threshold' => 3, 'is_active' => true]);
        $user = User::firstOrCreate(['email' => env('INFRAWATCH_ADMIN_EMAIL', 'admin@infrawatch.local')], ['name' => 'InfraWatch Administrator', 'password' => Hash::make(env('INFRAWATCH_ADMIN_PASSWORD', 'ChangeMeBeforeProduction!'))]);
        $user->roles()->syncWithoutDetaching([$admin->id]);
    }
}
