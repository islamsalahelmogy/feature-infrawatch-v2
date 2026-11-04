<?php

namespace Database\Seeders;

use App\Models\DeviceType;
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
        $user = User::firstOrCreate(['email' => env('INFRAWATCH_ADMIN_EMAIL', 'admin@infrawatch.local')], ['name' => 'InfraWatch Administrator', 'password' => Hash::make(env('INFRAWATCH_ADMIN_PASSWORD', 'ChangeMeBeforeProduction!'))]);
        $user->roles()->syncWithoutDetaching([$admin->id]);
    }
}
