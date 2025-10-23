<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workshop;
use App\Models\Project;

class WorkshopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project1 = Project::where('name', 'مجمع الواحة السكني')->first();
        $project2 = Project::where('name', 'برج الأفق التجاري')->first();
        $project3 = Project::where('name', 'مدرسة الأجيال الجديدة')->first();

        $workshopsData = [
            [
                'name' => 'ورشة الهياكل الخرسانية',
                'description' => 'متخصصة في أعمال صب الخرسانة المسلحة للأعمدة والأسقف.',
                'project_id' => $project1 ? $project1->id : null,
            ],
            [
                'name' => 'ورشة التشطيبات الداخلية',
                'description' => 'مسؤولة عن أعمال الدهان، البلاط، والتشطيبات النهائية للشقق.',
                'project_id' => $project1 ? $project1->id : null,
            ],
            [
                'name' => 'ورشة التمديدات الكهربائية',
                'description' => 'تختص بجميع أعمال التمديدات الكهربائية، الإنارة، وأنظمة التيار المنخفض.',
                'project_id' => $project2 ? $project2->id : null,
            ],
            [
                'name' => 'ورشة أنظمة السباكة',
                'description' => 'تركيب وصيانة شبكات المياه والصرف الصحي، وتركيب الأدوات الصحية.',
                'project_id' => $project2 ? $project2->id : null,
            ],
            [
                'name' => 'ورشة أعمال الجبس والديكور',
                'description' => 'تنفيذ الأسقف المستعارة، الجدران الجبسية، وأعمال الديكور.',
                'project_id' => $project1 ? $project1->id : null,
            ],
            [
                'name' => 'ورشة الحدادة واللحام',
                'description' => 'تصنيع وتركيب الهياكل المعدنية، أعمال التسليح، واللحام.',
                'project_id' => $project3 ? $project3->id : null,
            ],
            [
                'name' => 'ورشة أعمال النجارة',
                'description' => 'تصنيع وتركيب الأبواب، النوافذ، والخزائن المدمجة.',
                'project_id' => $project3 ? $project3->id : null,
            ],
        ];

        foreach ($workshopsData as $workshopData) {
            Workshop::firstOrCreate(['name' => $workshopData['name']], $workshopData);
        }
        $this->command->info('Workshops seeded successfully!');
    }
}