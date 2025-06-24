<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca todos os IDs reais da tabela users
        $userIds = DB::table('users')->pluck('id');

        foreach ($userIds as $userId) {
            Permission::create([
                'user_id' => $userId,
                'create_scale' => fake()->boolean(40),
                'read_scale' => true,
                'update_scale' => fake()->boolean(30),
                'delete_scale' => fake()->boolean(10),
                'create_music' => fake()->boolean(50),
                'read_music' => true,
                'update_music' => fake()->boolean(40),
                'delete_music' => fake()->boolean(15),
                'manage_users' => false,
                'manage_church_settings' => false,
                'manage_app_settings' => false,
            ]);
        }
    }
}
