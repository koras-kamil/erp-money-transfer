<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // 🟢 Added this to securely hash the password!

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 🟢 Create your Admin user directly to bypass the Faker error in production
        User::updateOrCreate(
            ['email' => 'test@example.com'], // Your login email
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'), // Your login password
            ]
        );
        
        // Note: If you have a Spatie Role seeder, you would call it here like this:
        // $this->call(RolesAndPermissionsSeeder::class);
    }
}