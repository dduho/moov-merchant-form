<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeographicHierarchy;

class GeographicHierarchySeeder extends Seeder
{
    public function run()
    {
        // Define the geographic hierarchy for Togo
        $hierarchy = [
            'MARITIME' => [
                'Avé' => ['Aképé', 'Zolo', 'Djagblé', 'Agbélouvé', 'Assahoun'],
                'Lacs' => ['Aného', 'Vogan', 'Anfoin', 'Glidji', 'Fiata'],
                'Vo' => ['Vogan', 'Afagnan', 'Kpénou', 'Akoumapé'],
                'Yoto' => ['Tabligbo', 'Tagbligbo', 'Tchékpo', 'Gboto'],
                'Zio' => ['Tsévié', 'Kovié', 'Gapé', 'Mission Tové'],
                'Bas_Mono' => ['Afagnan', 'Agomé Glozou', 'Tokpli'],
                'Agoè_Nyivé' => ['Agoè', 'Baguida', 'Hédzranawoé', 'Légbassito'],
                'Golfe' => ['Lomé', 'Bè', 'Aflao', 'Tokoin', 'Adidogomé', 'Agoè', 'Akodessewa', 'Kpota', 'Nyékonakpoè', 'Zongo'],
            ],
            'PLATEAUX' => [
                'Agou' => ['Agou Gadzépé', 'Agou Nyogbo', 'Kpalimé', 'Agou Akplolo'],
                'Akébou' => ['Kougnohou', 'Badou', 'Efoukpa'],
                'Amou' => ['Amlamé', 'Amou Oblo', 'Kpatimé'],
                'Anié' => ['Anié', 'Elavagnon', 'Blitta'],
                'Danyi' => ['Danyi Apéyémé', 'Danyi Elavagnon', 'Dzogbégan'],
                'Est_Mono' => ['Elavagnon', 'Tohoun', 'Morétan'],
                'Haho' => ['Notsé', 'Kpékplémé', 'Davié', 'Asrama'],
                'Kloto' => ['Kpalimé', 'Kouma Konda', 'Tomé', 'Kpimé'],
                'Wawa' => ['Badou', 'Tomégbé', 'Danyigba'],
                'Amou_Oblo' => ['Amou Oblo', 'Gléi'],
                'Kpélé' => ['Kpélé Adéta', 'Kpélé Elé', 'Kpélé Govié'],
            ],
            'CENTRALE' => [
                'Blitta' => ['Blitta', 'Pagala', 'Langabou', 'Waragni'],
                'Mô' => ['Djarkpanga', 'Fazao', 'Aléhéridé'],
                'Sotouboua' => ['Sotouboua', 'Tchébébé', 'Kazaboua', 'Adjouda'],
                'Tchamba' => ['Tchamba', 'Kabou', 'Balanka', 'Goubi'],
                'Tchaoudjo' => ['Sokodé', 'Adjengré', 'Kparatao', 'Kolina', 'Komah'],
            ],
            'KARA' => [
                'Kozah' => ['Kara', 'Lama Kara', 'Pya', 'Landa', 'Kétao'],
                'Assoli' => ['Bafilo', 'Binaparba', 'Tchitchao'],
                'Bassar' => ['Bassar', 'Kabou', 'Bangeli', 'Dimori'],
                'Binah' => ['Pagouda', 'Kétao', 'Sirka', 'Kouka'],
                'Dankpen' => ['Guérin Kouka', 'Naki Est', 'Nagbéni'],
                'Doufelgou' => ['Niamtougou', 'Siou', 'Défale', 'Agbandé'],
                'Kéran' => ['Kantè', 'Ténaga', 'Nadoba', 'Ossacré'],
            ],
            'SAVANES' => [
                'Cinkassé' => ['Cinkassé', 'Mango Nord', 'Poissongui'],
                'Kpendjal' => ['Mandouri', 'Ogaro', 'Nadjoudi'],
                'Kpendjal_Ouest' => ['Naki Ouest', 'Pogno', 'Timbou'],
                'Oti' => ['Mango', 'Tchanaga', 'Galangashie', 'Sadori'],
                'Oti_Sud' => ['Takpamba', 'Koumondè', 'Kountouré'],
                'Tandjouaré' => ['Tandjouaré', 'Bombouaka', 'Nano'],
                'Tône' => ['Dapaong', 'Barkoissi', 'Pana', 'Tamongue', 'Korbongou'],
            ],
        ];

        foreach ($hierarchy as $regionName => $prefectures) {
            // Create Region
            $region = GeographicHierarchy::firstOrCreate(
                ['type' => 'region', 'name' => $regionName],
                ['code' => strtoupper(substr($regionName, 0, 3))]
            );

            foreach ($prefectures as $prefectureName => $communes) {
                // Create Prefecture
                $prefecture = GeographicHierarchy::firstOrCreate(
                    ['type' => 'prefecture', 'name' => $prefectureName, 'parent_id' => $region->id],
                    ['code' => strtoupper(substr($prefectureName, 0, 4))]
                );

                foreach ($communes as $communeName) {
                    // Create Commune (also used as Canton and Ville for simplicity)
                    GeographicHierarchy::firstOrCreate(
                        ['type' => 'commune', 'name' => $communeName, 'parent_id' => $prefecture->id],
                        ['code' => strtoupper(substr($communeName, 0, 4))]
                    );
                }
            }
        }

        $this->command->info('Geographic hierarchy seeded successfully with ' . GeographicHierarchy::count() . ' entries.');
    }
}
