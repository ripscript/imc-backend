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
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);

        // Project-management master data (clients, partners, categories,
        // document types, default workflow + stages)
        $this->call(PmProjectManagementSeeder::class);
        $this->call(TemplateEmailSeeder::class);
        $this->call(LayoutSeeder::class);
        $this->call(MenuSeeder::class);

        // Master data
        $this->call(MsKategoriContentSeeder::class);
        $this->call(MsTagSeeder::class);
        $this->call(QuickLinkSeeder::class);

        // Content detail seeders
        $this->call(ContentDetailTextSeeder::class);
        $this->call(ContentDetailCardLinkSeeder::class);
        $this->call(ContentDetailImageLinkSeeder::class);
        $this->call(ContentDetailImageCarouselSeeder::class);
        $this->call(ContentAdditionalTextSeeder::class);
        $this->call(ContentDetailVideoCarouselSeeder::class);
        $this->call(ContentDetailAccordionSeeder::class);
        $this->call(ContentDetailRepositorySeeder::class);
        $this->call(ContentDetailOrganizationalStructureSeeder::class);
        $this->call(ContentDetailProjectSeeder::class);
        $this->call(ContentDetailShowcaseSeeder::class);
        $this->call(ContentDetailGallerySeeder::class);
        $this->call(ContentDetailPublicationSeeder::class);
        $this->call(ContentTagSeeder::class);
        $this->call(ContentDetailAgendaSeeder::class);
        $this->call(ContentDetailConsumeSeeder::class);
        $this->call(LatestInformationSeeder::class);

        $this->call(SiteSettingSeeder::class);
        $this->call(GlobalSearchIndexSeeder::class);
    }
}
