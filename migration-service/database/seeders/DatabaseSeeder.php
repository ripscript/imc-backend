<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(TemplateEmailSeeder::class);
        $this->call(LayoutSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(ClassificationPemohonSeeder::class);
        $this->call(PermohonanSeeder::class);
        $this->call(KeberatanSeeder::class);
        $this->call(SiteSettingSeeder::class);
        $this->call(ChatSettingSeeder::class);
        $this->call(SkalaLikertSeeder::class);
        $this->call(AlertSeeder::class);
        $this->call(GlobalSearchIndexSeeder::class);
    }
}
