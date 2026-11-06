<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentDetailOrganizationalStructureGroup;
use App\Models\ContentDetailOrganizationalStructureMember;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationalStructureContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizational_structure_returns_unhidden_id_and_parent_id()
    {
        $menu = Menu::factory()->create([
            'slug' => 'struktur-organisasi',
            'has_sub' => false,
            'is_active' => true,
        ]);

        $content = Content::factory()->create([
            'parent_type' => Menu::class,
            'parent_id' => $menu->id,
            'type' => 'organizational_structure',
            'is_section' => false,
            'is_accessible' => true,
        ]);

        $group = ContentDetailOrganizationalStructureGroup::create([
            'content_id' => $content->id,
            'name' => 'BTIM',
            'order' => 1,
        ]);

        $parentMember = ContentDetailOrganizationalStructureMember::create([
            'content_detail_organizational_structure_group_id' => $group->id,
            'name' => 'Budi Santoso',
            'position' => 'Menteri Perindustrian',
            'level' => 1,
            'is_child' => false,
            'have_child' => true,
            'parent_id' => null,
            'order' => 1,
        ]);

        $childMember = ContentDetailOrganizationalStructureMember::create([
            'content_detail_organizational_structure_group_id' => $group->id,
            'name' => 'Aditya Wijaya',
            'position' => 'Dirjen ILMATE',
            'level' => 2,
            'is_child' => true,
            'have_child' => false,
            'parent_id' => $parentMember->id,
            'order' => 2,
        ]);

        $response = $this->getJson('/api/content?type=menu&slug[]=struktur-organisasi');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $groups = $response->json('data.content');
        $this->assertNotEmpty($groups);

        $members = $groups[0]['members'];
        $this->assertCount(2, $members);

        // Verify id and parent_id are unhidden and present in public response
        $this->assertEquals($parentMember->id, $members[0]['id']);
        $this->assertNull($members[0]['parent_id']);
        $this->assertEquals(1, $members[0]['level']);

        $this->assertEquals($childMember->id, $members[1]['id']);
        $this->assertEquals($parentMember->id, $members[1]['parent_id']);
        $this->assertEquals(2, $members[1]['level']);
    }
}
