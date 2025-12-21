<?php

namespace Database\Seeders;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $user1 = User::where('email', 'user@example.com')->first();
        $user2 = User::where('email', 'john@example.com')->first();
        $user3 = User::where('email', 'jane@example.com')->first();

        if ($admin) {
            Task::create([
                'title' => 'Review team performance',
                'description' => 'Review quarterly performance metrics for all team members',
                'status' => TaskStatus::IN_PROGRESS,
                'user_id' => $admin->id,
            ]);

            Task::create([
                'title' => 'Update company policies',
                'description' => 'Review and update HR policies for the next quarter',
                'status' => TaskStatus::PENDING,
                'user_id' => $admin->id,
            ]);

            Task::create([
                'title' => 'Budget planning',
                'description' => 'Prepare budget proposal for Q2 2024',
                'status' => TaskStatus::COMPLETED,
                'user_id' => $admin->id,
            ]);
        }

        if ($user1) {
            Task::create([
                'title' => 'Finish API documentation',
                'description' => 'Complete documentation for the new API endpoints',
                'status' => TaskStatus::IN_PROGRESS,
                'user_id' => $user1->id,
            ]);

            Task::create([
                'title' => 'Code review',
                'description' => 'Review pull requests from team members',
                'status' => TaskStatus::PENDING,
                'user_id' => $user1->id,
            ]);

            Task::create([
                'title' => 'Write unit tests',
                'description' => 'Add unit tests for user authentication module',
                'status' => TaskStatus::COMPLETED,
                'user_id' => $user1->id,
            ]);

            Task::create([
                'title' => 'Database optimization',
                'description' => 'Optimize database queries for better performance',
                'status' => TaskStatus::PENDING,
                'user_id' => $user1->id,
            ]);
        }

        if ($user2) {
            Task::create([
                'title' => 'Design new dashboard',
                'description' => 'Create mockups for the new admin dashboard',
                'status' => TaskStatus::IN_PROGRESS,
                'user_id' => $user2->id,
            ]);

            Task::create([
                'title' => 'User interface improvements',
                'description' => 'Improve UX for mobile application',
                'status' => TaskStatus::PENDING,
                'user_id' => $user2->id,
            ]);

            Task::create([
                'title' => 'Logo redesign',
                'description' => 'Create new logo variations for brand refresh',
                'status' => TaskStatus::COMPLETED,
                'user_id' => $user2->id,
            ]);
        }

        if ($user3) {
            Task::create([
                'title' => 'Marketing campaign analysis',
                'description' => 'Analyze Q1 marketing campaign results',
                'status' => TaskStatus::IN_PROGRESS,
                'user_id' => $user3->id,
            ]);

            Task::create([
                'title' => 'Social media content',
                'description' => 'Prepare social media content for next month',
                'status' => TaskStatus::PENDING,
                'user_id' => $user3->id,
            ]);

            Task::create([
                'title' => 'Customer feedback report',
                'description' => 'Compile and analyze customer feedback from surveys',
                'status' => TaskStatus::COMPLETED,
                'user_id' => $user3->id,
            ]);

            Task::create([
                'title' => 'Website content update',
                'description' => 'Update website with new product information',
                'status' => TaskStatus::PENDING,
                'user_id' => $user3->id,
            ]);
        }
    }
}

