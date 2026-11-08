<?php

namespace Tests\Feature;

use App\Models\DeviceType;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\IpRangesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseOneAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $slug): User
    {
        $role = Role::create(['name' => ucfirst($slug), 'slug' => $slug]);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_a_valid_user_can_sign_in(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_administrator_can_create_a_site(): void
    {
        $user = $this->userWithRole('administrator');
        $this->actingAs($user)->post('/sites', ['name' => 'Main Factory', 'code' => 'MAIN', 'timezone' => 'Africa/Cairo', 'is_active' => 1])->assertRedirect('/sites');
        $this->assertDatabaseHas('sites', ['code' => 'MAIN']);
    }

    public function test_viewer_cannot_manage_sites(): void
    {
        $this->actingAs($this->userWithRole('viewer'))->get('/sites')->assertForbidden();
    }

    public function test_device_requires_a_valid_ip_address(): void
    {
        $user = $this->userWithRole('operator');
        $site = Site::create(['name' => 'Factory 02', 'code' => 'F02', 'timezone' => 'Africa/Cairo']);
        $type = DeviceType::create(['name' => 'Switch', 'slug' => 'switch', 'category' => 'network']);
        $this->actingAs($user)->post('/devices', ['name' => 'Core', 'site_id' => $site->id, 'device_type_id' => $type->id, 'management_ip' => 'not-an-ip', 'mac_address' => '00:11:22:33:44:55', 'default_gateway' => '192.168.1.1', 'subnet_mask' => '255.255.255.0', 'ip_assignment' => 'static', 'criticality' => 'critical', 'is_active' => 1])->assertSessionHasErrors('management_ip');
    }

    public function test_device_requires_a_mac_address_and_network_configuration(): void
    {
        $user = $this->userWithRole('operator');
        $site = Site::create(['name' => 'Factory 02', 'code' => 'F02', 'timezone' => 'Africa/Cairo']);
        $type = DeviceType::create(['name' => 'Switch', 'slug' => 'switch', 'category' => 'network']);

        $this->actingAs($user)->post('/devices', ['name' => 'Core', 'site_id' => $site->id, 'device_type_id' => $type->id, 'management_ip' => '192.168.1.10', 'ip_assignment' => 'static', 'criticality' => 'critical', 'is_active' => 1])->assertSessionHasErrors(['mac_address', 'default_gateway', 'subnet_mask']);
    }

    public function test_operator_can_delete_a_device(): void
    {
        $user = $this->userWithRole('operator');
        $site = Site::create(['name' => 'Factory 02', 'code' => 'F02', 'timezone' => 'Africa/Cairo']);
        $type = DeviceType::create(['name' => 'Switch', 'slug' => 'switch', 'category' => 'network']);
        $device = \App\Models\Device::create(['site_id' => $site->id, 'device_type_id' => $type->id, 'name' => 'Core', 'management_ip' => '192.168.1.10', 'mac_address' => '00:11:22:33:44:55', 'default_gateway' => '192.168.1.4', 'subnet_mask' => '255.255.248.0', 'ip_assignment' => 'static', 'criticality' => 'high']);

        $this->actingAs($user)->delete("/devices/{$device->id}")->assertRedirect('/devices');
        $this->assertDatabaseMissing('devices', ['id' => $device->id]);
    }

    public function test_ip_range_seeder_imports_only_devices_with_verified_mac_addresses(): void
    {
        putenv('INFRAWATCH_MAIN_FACTORY_DEFAULT_GATEWAY=192.168.0.254');
        putenv('INFRAWATCH_MAIN_FACTORY_SUBNET_MASK=255.255.255.0');

        $this->seed(IpRangesSeeder::class);

        $this->assertDatabaseCount('devices', 51);
        $this->assertDatabaseHas('devices', ['management_ip' => '192.168.0.1', 'mac_address' => '1C:98:EC:A6:F6:C0', 'ip_assignment' => 'static']);
        $operator = $this->userWithRole('operator');
        $this->actingAs($operator)->get('/dashboard')->assertOk()->assertSee('Devices by type')->assertSee('Switch')->assertSee('49');
        $this->actingAs($operator)->get('/devices?type=switch')->assertOk()->assertSee('Switch 001')->assertDontSee('ITX Agregation (Offline Machine) - 2');
        putenv('INFRAWATCH_MAIN_FACTORY_DEFAULT_GATEWAY');
        putenv('INFRAWATCH_MAIN_FACTORY_SUBNET_MASK');
    }
}
