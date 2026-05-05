<?php

namespace Database\Seeders;

use App\Models\CustomField;
use App\Models\CustomFieldCategory;
use App\Models\Firm;
use Illuminate\Database\Seeder;

class CustomFieldSeeder extends Seeder
{
    public function run(): void
    {
        $firm = Firm::first();

        $categories = [
            [
                'name' => 'Lead Info',
                'for'  => 'lead',
                'fields' => [
                    ['label' => 'Budget', 'type' => 'number', 'is_required' => false],
                    ['label' => 'Industry', 'type' => 'text', 'is_required' => false],
                    [
                        'label'       => 'Lead Rating',
                        'type'        => 'radio',
                        'is_required' => false,
                        'options'     => json_encode(['Hot', 'Warm', 'Cold']),
                    ],
                ],
            ],
            [
                'name' => 'Contact Details',
                'for'  => 'contact',
                'fields' => [
                    ['label' => 'LinkedIn URL', 'type' => 'url', 'is_required' => false],
                    ['label' => 'Company Name', 'type' => 'text', 'is_required' => false],
                    ['label' => 'Designation', 'type' => 'text', 'is_required' => false],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = CustomFieldCategory::create([
                'name'    => $categoryData['name'],
                'firm_id' => $firm->id,
                'for'     => $categoryData['for'],
            ]);

            foreach ($categoryData['fields'] as $field) {
                CustomField::create(array_merge($field, [
                    'custom_field_category_id' => $category->id,
                    'firm_id'                  => $firm->id,
                ]));
            }
        }
    }
}
