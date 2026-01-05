<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DocumentSeeder::class,
        ]);

        $this->command->info('=====================================');
        $this->command->info('Database seeding completed!');
        $this->command->info('=====================================');
        $this->command->info('Test Users:');
        $this->command->info('Email: admin@esigning.com | Password: password');
        $this->command->info('Email: john@esigning.com | Password: password');
        $this->command->info('Email: jane@esigning.com | Password: password');
        $this->command->info('=====================================');
    }
}
