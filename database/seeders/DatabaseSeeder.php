<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\CalendarEvent;
use Database\Seeders\Authentication\AuthenticationSeeder;
use Database\Seeders\CalendarSeeder;
use Database\Seeders\Authentication\PermissionSeeder;
use Database\Seeders\Authentication\RoleSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->call([
      RoleSeeder::class,
      PermissionSeeder::class,
      AuthenticationSeeder::class,
      CalendarSeeder::class
    ]);
  }
}
