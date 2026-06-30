<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $admin = Model::unguarded(fn () => User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Admin',
                'password' => Hash::make('112233'),
                'current_tenant_id' => 1,
            ],
        ));

        Model::unguarded(fn () => Tenant::firstOrCreate(
            ['id' => 1],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Tenant 1',
                'handle' => 'tenant-1',
                'user_id' => $admin->id,
                'active' => true,
                'status' => 'active',
            ],
        ));

    }
}
