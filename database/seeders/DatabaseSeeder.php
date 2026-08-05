<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormVersion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo user — use these credentials to log in
        $user = User::firstOrCreate(['email' => 'demo@example.com'], [
            'name'     => 'Demo User',
            'password' => bcrypt('password'),
        ]);

        // Sample Form 1: Job Application
        $form1 = Form::create([
            'user_id'     => $user->id,
            'title'       => 'Job Application Form',
            'slug'        => 'job-application-' . Str::random(6),
            'description' => 'Apply for open positions at our company.',
            'status'      => 'published',
            'schema'      => [
                'fields' => [
                    ['id' => (string) Str::uuid(), 'type' => 'heading',  'label' => 'Personal Information', 'key' => 'sec_personal', 'order' => 0, 'required' => false, 'options' => [], 'validation' => [], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'text',     'label' => 'Full Name',             'key' => 'full_name',    'order' => 1, 'required' => true,  'placeholder' => 'Enter your full name', 'options' => [], 'validation' => ['min_length' => 2, 'max_length' => 100], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'email',    'label' => 'Email Address',         'key' => 'email',        'order' => 2, 'required' => true,  'placeholder' => 'you@example.com', 'options' => [], 'validation' => [], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'phone',    'label' => 'Phone Number',          'key' => 'phone',        'order' => 3, 'required' => true,  'placeholder' => '+91 9876543210', 'options' => [], 'validation' => [], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'date',     'label' => 'Date of Birth',         'key' => 'dob',          'order' => 4, 'required' => false, 'options' => [], 'validation' => [], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'radio',    'label' => 'Gender',                'key' => 'gender',       'order' => 5, 'required' => false, 'options' => [['label' => 'Male', 'value' => 'male'], ['label' => 'Female', 'value' => 'female'], ['label' => 'Other', 'value' => 'other']], 'validation' => [], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'heading',  'label' => 'Professional Details',  'key' => 'sec_prof',     'order' => 6, 'required' => false, 'options' => [], 'validation' => [], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'dropdown', 'label' => 'Years of Experience',   'key' => 'experience',   'order' => 7, 'required' => true, 'options' => [['label' => '0-1 years', 'value' => '0-1'], ['label' => '2-4 years', 'value' => '2-4'], ['label' => '5+ years', 'value' => '5+']], 'validation' => [], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'textarea', 'label' => 'Cover Letter',          'key' => 'cover_letter', 'order' => 8, 'required' => true, 'placeholder' => 'Tell us why you are a great fit...', 'options' => [], 'validation' => ['min_length' => 100], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'file',     'label' => 'Upload Resume',         'key' => 'resume',       'order' => 9, 'required' => true, 'options' => [], 'validation' => ['file_types' => ['pdf', 'doc', 'docx'], 'max_file_size_mb' => 5], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'checkbox', 'label' => 'I agree to the terms',  'key' => 'terms',        'order' => 10, 'required' => true, 'options' => [['label' => 'Yes, I agree', 'value' => 'yes']], 'validation' => [], 'conditions' => []],
                ],
            ],
        ]);

        FormVersion::create([
            'form_id'        => $form1->id,
            'schema'         => $form1->schema,
            'version_number' => 1,
            'label'          => 'Version 1',
        ]);

        // Sample submissions for form 1
        foreach (['Alice Johnson', 'Bob Smith', 'Carol White'] as $name) {
            FormSubmission::create([
                'form_id'      => $form1->id,
                'submitter_ip' => '127.0.0.1',
                'data'         => [
                    'full_name'    => $name,
                    'email'        => Str::slug($name) . '@example.com',
                    'phone'        => '+91 9876543210',
                    'experience'   => '2-4',
                    'cover_letter' => "I am excited to apply for this position. My background in software development makes me a strong candidate.",
                    'terms'        => 'yes',
                ],
            ]);
        }

        // Sample Form 2: Customer Feedback
        $form2 = Form::create([
            'user_id'     => $user->id,
            'title'       => 'Customer Feedback Survey',
            'slug'        => 'customer-feedback-' . Str::random(6),
            'description' => 'Help us improve our services.',
            'status'      => 'published',
            'schema'      => [
                'fields' => [
                    ['id' => (string) Str::uuid(), 'type' => 'text',     'label' => 'Your Name',          'key' => 'name',        'order' => 0, 'required' => false, 'placeholder' => 'Optional', 'options' => [], 'validation' => [], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'email',    'label' => 'Email',              'key' => 'email',       'order' => 1, 'required' => false, 'placeholder' => 'Optional', 'options' => [], 'validation' => [], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'rating',   'label' => 'Overall Rating',     'key' => 'rating',      'order' => 2, 'required' => true,  'options' => [], 'validation' => [], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'dropdown', 'label' => 'How did you hear about us?', 'key' => 'source', 'order' => 3, 'required' => false, 'options' => [['label' => 'Google', 'value' => 'google'], ['label' => 'Social Media', 'value' => 'social'], ['label' => 'Friend', 'value' => 'friend'], ['label' => 'Other', 'value' => 'other']], 'validation' => [], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'textarea', 'label' => 'Your Feedback',      'key' => 'feedback',    'order' => 4, 'required' => true,  'placeholder' => 'Share your experience...', 'options' => [], 'validation' => ['min_length' => 20], 'conditions' => []],
                    ['id' => (string) Str::uuid(), 'type' => 'radio',    'label' => 'Would you recommend us?', 'key' => 'recommend', 'order' => 5, 'required' => true, 'options' => [['label' => 'Yes', 'value' => 'yes'], ['label' => 'No', 'value' => 'no'], ['label' => 'Maybe', 'value' => 'maybe']], 'validation' => [], 'conditions' => []],
                ],
            ],
        ]);

        FormVersion::create([
            'form_id'        => $form2->id,
            'schema'         => $form2->schema,
            'version_number' => 1,
            'label'          => 'Version 1',
        ]);

        foreach (['Great service!', 'Could be better.', 'Absolutely loved it!'] as $fb) {
            FormSubmission::create([
                'form_id'      => $form2->id,
                'submitter_ip' => '127.0.0.1',
                'data'         => [
                    'name'      => 'Anonymous',
                    'rating'    => rand(3, 5),
                    'feedback'  => $fb . ' Will definitely recommend to friends.',
                    'recommend' => 'yes',
                ],
            ]);
        }

        $this->command->info('Seeded! Login: demo@example.com / password');
    }
}
