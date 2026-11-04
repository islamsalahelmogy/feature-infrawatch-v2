<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\DeviceType;
use App\Models\Site;
use Illuminate\Database\Seeder;
use RuntimeException;

class IpRangesSeeder extends Seeder
{
    public function run(): void
    {
        $gateway = env('INFRAWATCH_MAIN_FACTORY_DEFAULT_GATEWAY');
        $subnetMask = env('INFRAWATCH_MAIN_FACTORY_SUBNET_MASK');
        if (! filter_var($gateway, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || ! filter_var($subnetMask, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new RuntimeException('Set INFRAWATCH_MAIN_FACTORY_DEFAULT_GATEWAY and INFRAWATCH_MAIN_FACTORY_SUBNET_MASK before importing IP range devices.');
        }

        $site = Site::firstOrCreate(['code' => 'MAIN'], ['name' => 'Main Factory', 'timezone' => 'Africa/Cairo', 'is_active' => true]);
        $types = collect(['switch' => ['name' => 'Switch', 'category' => 'network'], 'endpoint' => ['name' => 'Standard Device', 'category' => 'endpoint']])->mapWithKeys(fn (array $type, string $slug) => [$slug => DeviceType::firstOrCreate(['slug' => $slug], $type)]);

        foreach ($this->devices() as [$name, $ip, $mac, $type]) {
            Device::updateOrCreate(['site_id' => $site->id, 'management_ip' => $ip], ['device_type_id' => $types[$type]->id, 'name' => $name, 'mac_address' => $mac, 'default_gateway' => $gateway, 'subnet_mask' => $subnetMask, 'ip_assignment' => 'static', 'criticality' => $type === 'switch' ? 'high' : 'medium', 'is_active' => true, 'notes' => 'Imported from IP ranges.xlsx; only rows with a verified MAC address were included.']);
        }
    }

    private function devices(): array
    {
        return [
            ['Switch 001', '192.168.0.1', '1C:98:EC:A6:F6:C0', 'switch'], ['Switch 002', '192.168.0.2', 'E0:07:1B:43:CC:C0', 'switch'], ['Switch 003', '192.168.0.3', 'E0:07:1B:43:EC:E0', 'switch'], ['Switch 004', '192.168.0.4', 'E0:07:1B:43:0D:C0', 'switch'], ['Switch 005', '192.168.0.5', 'E0:07:1B:43:6C:40', 'switch'], ['Switch 006', '192.168.0.6', '9C:DC:71:24:6C:00', 'switch'], ['Switch 007', '192.168.0.7', 'E0:07:1B:43:1D:A0', 'switch'], ['Switch 008', '192.168.0.8', 'E0:07:1B:43:3D:40', 'switch'], ['Switch 009', '192.168.0.9', '00:FD:45:07:43:C0', 'switch'], ['Switch 010', '192.168.0.10', 'E0:07:1B:93:1F:00', 'switch'],
            ['Switch 011', '192.168.0.11', 'E0:07:1B:43:5D:60', 'switch'], ['Switch 012', '192.168.0.12', 'E0:07:1B:43:FC:80', 'switch'], ['Switch 013', '192.168.0.13', 'E0:07:1B:43:EC:00', 'switch'], ['Switch 014', '192.168.0.14', 'E0:07:1B:43:2D:60', 'switch'], ['Switch 015', '192.168.0.15', '00:FD:45:07:72:00', 'switch'], ['Switch 016', '192.168.0.16', 'E0:07:1B:43:0D:20', 'switch'], ['Switch 017', '192.168.0.17', '00:FD:45:AD:A8:E0', 'switch'], ['Switch 018', '192.168.0.18', '00:FD:45:07:72:80', 'switch'], ['Switch 019', '192.168.0.19', '00:FD:45:AD:B8:40', 'switch'], ['Switch 020', '192.168.0.20', '00:FD:45:A8:76:80', 'switch'],
            ['Switch 021', '192.168.0.21', 'E0:07:1B:43:3D:A0', 'switch'], ['Switch 022', '192.168.0.22', '9C:DC:71:24:3C:40', 'switch'], ['Switch 023', '192.168.0.23', 'E0:07:1B:43:AC:00', 'switch'], ['Switch 024', '192.168.0.24', 'E0:07:1B:43:1D:80', 'switch'], ['Switch 025', '192.168.0.25', '00:FD:45:06:7E:00', 'switch'], ['Switch 026', '192.168.0.26', '54:80:28:E5:8F:A0', 'switch'], ['Switch 027', '192.168.0.27', '54:80:28:E6:61:40', 'switch'], ['Switch 028', '192.168.0.28', '88:3A:30:1F:25:A0', 'switch'], ['Switch 029', '192.168.0.29', '10:4F:58:8B:D7:00', 'switch'],
            ['Cephalo ServerR Switch1', '192.168.0.36', '24:BE:05:58:3B:80', 'switch'], ['Cephalo ServerR Switch2', '192.168.0.37', '24:BE:05:58:69:80', 'switch'], ['Cephalo ServerR Switch3', '192.168.0.38', '24:BE:05:58:87:00', 'switch'], ['ITX Agregation (Offline Machine) - 2', '192.168.0.51', '00:E0:4C:68:7C:FC', 'endpoint'], ['ITX Agregation (Offline Machine) - 1', '192.168.0.52', '00:E0:4C:68:53:7D', 'endpoint'],
            ['Switch 028', '192.168.0.135', '88:3A:30:20:C0:C0', 'switch'], ['Switch 029', '192.168.0.136', 'F8:60:F0:9C:14:20', 'switch'], ['Switch 030', '192.168.0.137', '10:4F:58:8D:10:40', 'switch'], ['Switch 031', '192.168.0.138', '10:4F:58:8D:45:00', 'switch'], ['Switch 032', '192.168.0.139', 'F8:60:F0:9C:08:A0', 'switch'], ['Switch 033', '192.168.0.140', '88:3A:30:20:80:60', 'switch'], ['Switch 034', '192.168.0.141', 'F8:60:F0:9C:16:20', 'switch'], ['Switch 035', '192.168.0.142', '10:4F:58:8C:63:40', 'switch'], ['Switch 036', '192.168.0.143', '10:4F:58:8B:ED:40', 'switch'], ['Switch 037', '192.168.0.144', '88:3A:30:1F:94:60', 'switch'],
            ['Switch 040', '192.168.0.147', '8C:85:C1:91:2B:4E', 'switch'], ['Switch 041', '192.168.0.148', '64:E8:81:29:DA:9C', 'switch'], ['Switch 042', '192.168.0.149', '8C:85:C1:91:2B:C2', 'switch'], ['Switch 043', '192.168.0.150', '64:E8:81:2A:5E:D0', 'switch'], ['Switch 044', '192.168.0.151', '8C:85:C1:91:3E:78', 'switch'], ['Switch 045', '192.168.0.152', '64:E8:81:29:0C:FB', 'switch'], ['Switch 046', '192.168.0.153', '64:E8:81:2A:CE:28', 'switch'],
        ];
    }
}
