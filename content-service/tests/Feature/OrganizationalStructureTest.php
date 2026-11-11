<?php

namespace Tests\Feature;

use App\Models\ContentDetailOrganizationalStructureGroup;
use App\Models\ContentDetailOrganizationalStructureMember;
use App\Models\Menu;
use App\Models\MsLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationalStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLayouts();
    }

    public function test_can_create_menu_with_multi_tier_organizational_structure_using_temp_id()
    {
        $this->actingAsSuperAdmin();
        $layout = MsLayout::where('type', 'organizational_structure')->first() ?? MsLayout::first();

        $data = [
            'title' => 'Struktur Organisasi',
            'slug' => 'struktur-organisasi',
            'order' => 1,
            'is_active' => true,
            'has_sub' => false,
            'is_show_front' => false,
            'type' => 'organizational_structure',
            'layout_id' => $layout->id,
            'content' => [
                'groups' => [
                    [
                        'id' => null,
                        'name' => 'Balai Teknologi Industri Manufaktur (BTIM)',
                        'order' => 1,
                        'members' => [
                            [
                                'temp_id' => 'menteri',
                                'name' => 'Budi Santoso',
                                'position' => 'MENTERI PERINDUSTRIAN',
                                'description' => 'Menteri Perindustrian',
                                'image_path' => '/images/budi.jpg',
                                'parent_id' => null,
                                'parent_temp_id' => null,
                                'order' => 1,
                            ],
                            [
                                'temp_id' => 'dirjen',
                                'name' => 'Aditya Wijaya',
                                'position' => 'DIRJEN ILMATE',
                                'description' => 'Dirjen ILMATE',
                                'image_path' => '/images/aditya.jpg',
                                'parent_temp_id' => 'menteri',
                                'order' => 2,
                            ],
                            [
                                'temp_id' => 'ka_balai',
                                'name' => 'Farhan Maulana',
                                'position' => 'KEPALA BALAI (BTIM)',
                                'description' => 'Kepala Balai BTIM',
                                'image_path' => '/images/farhan.jpg',
                                'parent_temp_id' => 'dirjen',
                                'order' => 3,
                            ],
                            [
                                'name' => 'Siti Nurhaliza',
                                'position' => 'Koordinator Tim Inovasi',
                                'description' => 'Koordinator',
                                'image_path' => '/images/siti.jpg',
                                'parent_temp_id' => 'ka_balai',
                                'order' => 4,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/menu', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);

        $group = ContentDetailOrganizationalStructureGroup::first();
        $this->assertNotNull($group);
        $this->assertEquals('Balai Teknologi Industri Manufaktur (BTIM)', $group->name);

        $members = ContentDetailOrganizationalStructureMember::where('content_detail_organizational_structure_group_id', $group->id)
            ->orderBy('order')
            ->get();

        $this->assertCount(4, $members);

        $menteri = $members[0];
        $dirjen = $members[1];
        $kaBalai = $members[2];
        $siti = $members[3];

        // Level 1: Menteri
        $this->assertEquals('Budi Santoso', $menteri->name);
        $this->assertNull($menteri->parent_id);
        $this->assertEquals(1, $menteri->level);
        $this->assertFalse((bool) $menteri->is_child);
        $this->assertTrue((bool) $menteri->have_child);

        // Level 2: Dirjen
        $this->assertEquals('Aditya Wijaya', $dirjen->name);
        $this->assertEquals($menteri->id, $dirjen->parent_id);
        $this->assertEquals(2, $dirjen->level);
        $this->assertTrue((bool) $dirjen->is_child);
        $this->assertTrue((bool) $dirjen->have_child);

        // Level 3: Ka Balai
        $this->assertEquals('Farhan Maulana', $kaBalai->name);
        $this->assertEquals($dirjen->id, $kaBalai->parent_id);
        $this->assertEquals(3, $kaBalai->level);
        $this->assertTrue((bool) $kaBalai->is_child);
        $this->assertTrue((bool) $kaBalai->have_child);

        // Level 4: Siti
        $this->assertEquals('Siti Nurhaliza', $siti->name);
        $this->assertEquals($kaBalai->id, $siti->parent_id);
        $this->assertEquals(4, $siti->level);
        $this->assertTrue((bool) $siti->is_child);
        $this->assertFalse((bool) $siti->have_child);

        // Test Eloquent relationships
        $this->assertEquals($menteri->id, $dirjen->parent->id);
        $this->assertCount(1, $menteri->children);
        $this->assertEquals($dirjen->id, $menteri->children->first()->id);
    }

    public function test_can_update_menu_reassigning_parent_and_deleting_members()
    {
        $this->actingAsSuperAdmin();
        $layout = MsLayout::where('type', 'organizational_structure')->first() ?? MsLayout::first();

        // 1. Create initial menu with 3 tiers
        $createData = [
            'title' => 'Struktur Organisasi',
            'slug' => 'struktur-organisasi',
            'order' => 1,
            'is_active' => true,
            'has_sub' => false,
            'is_show_front' => false,
            'type' => 'organizational_structure',
            'layout_id' => $layout->id,
            'content' => [
                'groups' => [
                    [
                        'name' => 'Direksi',
                        'order' => 1,
                        'members' => [
                            [
                                'temp_id' => 'dirut',
                                'name' => 'Direktur Utama',
                                'position' => 'Dirut',
                                'parent_id' => null,
                                'order' => 1,
                            ],
                            [
                                'temp_id' => 'dirops',
                                'name' => 'Direktur Operasional',
                                'position' => 'Dirops',
                                'parent_temp_id' => 'dirut',
                                'order' => 2,
                            ],
                            [
                                'temp_id' => 'staff',
                                'name' => 'Staff Operasional',
                                'position' => 'Staff',
                                'parent_temp_id' => 'dirops',
                                'order' => 3,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $createResponse = $this->postJson('/api/menu', $createData);
        $createResponse->assertStatus(201);
        $menuId = $createResponse->json('data.id');

        $group = ContentDetailOrganizationalStructureGroup::first();
        $members = ContentDetailOrganizationalStructureMember::orderBy('order')->get();
        $dirut = $members[0];
        $dirops = $members[1];
        $staff = $members[2];

        // 2. Update: Reassign staff to report directly to dirut, and delete dirops
        $updateData = [
            'title' => 'Struktur Organisasi (Updated)',
            'slug' => 'struktur-organisasi',
            'order' => 1,
            'is_active' => true,
            'has_sub' => false,
            'is_show_front' => false,
            'type' => 'organizational_structure',
            'layout_id' => $layout->id,
            'content' => [
                'groups' => [
                    [
                        'id' => $group->id,
                        'name' => 'Direksi & Manajemen',
                        'order' => 1,
                        'members' => [
                            [
                                'id' => $dirut->id,
                                'name' => 'Direktur Utama (Senior)',
                                'position' => 'Dirut',
                                'parent_id' => null,
                                'order' => 1,
                            ],
                            [
                                'id' => $staff->id,
                                'name' => 'Staff Operasional',
                                'position' => 'Staff Khusus Dirut',
                                'parent_id' => $dirut->id, // Reassigned to Dirut
                                'order' => 2,
                            ],
                        ],
                    ],
                ],
            ],
            'deleted_content_detail_list_id' => [$dirops->id],
        ];

        $updateResponse = $this->putJson("/api/menu/{$menuId}", $updateData);
        $updateResponse->assertStatus(200);

        // Assert dirops was deleted
        $this->assertDatabaseMissing('content_detail_organizational_structure_members', ['id' => $dirops->id]);

        // Assert staff is now child of dirut and level recalculated to 2
        $updatedStaff = ContentDetailOrganizationalStructureMember::find($staff->id);
        $this->assertEquals($dirut->id, $updatedStaff->parent_id);
        $this->assertEquals(2, $updatedStaff->level);
        $this->assertTrue((bool) $updatedStaff->is_child);
        $this->assertFalse((bool) $updatedStaff->have_child);

        // Assert dirut is still level 1
        $updatedDirut = ContentDetailOrganizationalStructureMember::find($dirut->id);
        $this->assertEquals(1, $updatedDirut->level);
        $this->assertTrue((bool) $updatedDirut->have_child);
    }

    public function test_independent_roots_within_same_group_do_not_force_parent_on_each_other()
    {
        $this->actingAsSuperAdmin();
        $layout = MsLayout::where('type', 'organizational_structure')->first() ?? MsLayout::first();

        // Testing direct array format without "groups" wrapper
        $data = [
            'title' => 'Dewan Pembina',
            'slug' => 'dewan-pembina',
            'order' => 1,
            'is_active' => true,
            'has_sub' => false,
            'is_show_front' => false,
            'type' => 'organizational_structure',
            'layout_id' => $layout->id,
            'content' => [
                [
                    'name' => 'Dewan Pembina Bersama',
                    'order' => 1,
                    'members' => [
                        [
                            'name' => 'Pembina A',
                            'position' => 'Ketua Dewan Pembina',
                            'parent_id' => null,
                            'order' => 1,
                        ],
                        [
                            'name' => 'Pembina B',
                            'position' => 'Wakil Dewan Pembina',
                            'parent_id' => null,
                            'order' => 2,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/menu', $data);
        $response->assertStatus(201);

        $members = ContentDetailOrganizationalStructureMember::orderBy('order')->get();
        $this->assertCount(2, $members);

        // Both must be root (level 1, parent_id null, is_child false)
        $this->assertNull($members[0]->parent_id);
        $this->assertEquals(1, $members[0]->level);
        $this->assertFalse((bool) $members[0]->is_child);

        $this->assertNull($members[1]->parent_id);
        $this->assertEquals(1, $members[1]->level);
        $this->assertFalse((bool) $members[1]->is_child);
    }
}
