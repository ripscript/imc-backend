<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function actingAsSuperAdmin(): \App\Models\User
    {
        $user = \App\Models\User::factory()->superAdmin()->create();
        $token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($user);
        $this->withHeader('Authorization', 'Bearer '.$token);
        $this->actingAs($user, 'api');

        return $user;
    }

    protected function seedLayouts(): void
    {
        $layouts = [
            'text', 'card_link', 'image_link', 'image_carousel', 'video_carousel',
            'publication', 'publication_detail', 'gallery', 'gallery_image_detail',
            'gallery_video_detail', 'repository', 'repository_detail', 'accordion',
            'landing_page', 'agenda', 'organizational_structure', 'project', 'project_detail',
        ];

        foreach ($layouts as $type) {
            \Illuminate\Support\Facades\DB::table('ms_layout')->insert([
                'title' => 'Style '.$type,
                'type' => $type,
                'is_custom' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
