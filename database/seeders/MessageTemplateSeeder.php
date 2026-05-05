<?php

namespace Database\Seeders;

use App\Models\Firm;
use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $firms = Firm::all();

        foreach ($firms as $firm) {
            $templates = [
                [
                    'name' => 'Loan: Welcome Message',
                    'content' => "Hi {name}, I'm your dedicated loan assistant. Thank you for reaching out! When would be a good time to discuss your financial requirements?",
                ],
                [
                    'name' => 'Loan: Documents Required',
                    'content' => "Hi {name}, to proceed with your loan application, please share these documents:\n1. Latest 3 months Bank Statements\n2. Last 2 years ITR/Form 16\n3. KYC Documents (Aadhar & PAN)",
                ],
                [
                    'name' => 'Loan: Application Update',
                    'content' => "Hello {name}, your loan application is now under technical review. We expect an update from the credit team within 48 hours. I will keep you posted!",
                ],
                [
                    'name' => 'Loan: Follow-up (Proposal)',
                    'content' => "Hi {name}, just checking in to see if you have reviewed the loan proposal I shared yesterday. Feel free to ask if you have any doubts regarding the ROI or tenure.",
                ],
                [
                    'name' => 'Loan: Approval Congrats',
                    'content' => "Congratulations {name}! Your loan has been sanctioned. Please let me know when we can meet to complete the documentation and disbursement process.",
                ],
            ];

            foreach ($templates as $template) {
                MessageTemplate::firstOrCreate(
                    ['firm_id' => $firm->id, 'name' => $template['name']],
                    ['content' => $template['content']]
                );
            }
        }
    }
}
