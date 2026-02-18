<?php

namespace Database\Seeders;

use App\Models\FeatureLimit;
use App\Models\Module;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansAndModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Modules
        $modules = [
            [
                'name' => 'Client Management',
                'slug' => 'client-management',
                'icon' => 'users',
                'description' => 'Manage your clients and their information',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Service Management',
                'slug' => 'service-management',
                'icon' => 'briefcase',
                'description' => 'Manage services offered to clients',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Task & Compliance',
                'slug' => 'task-compliance',
                'icon' => 'check-circle',
                'description' => 'Track tasks and compliance requirements',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Invoice & Billing',
                'slug' => 'invoice-billing',
                'icon' => 'dollar-sign',
                'description' => 'Create invoices and manage billing',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Reports',
                'slug' => 'reports',
                'icon' => 'bar-chart',
                'description' => 'Generate reports and analytics',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Reminder System',
                'slug' => 'reminder-system',
                'icon' => 'bell',
                'description' => 'Set up reminders and notifications',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Staff Management',
                'slug' => 'staff-management',
                'icon' => 'user-plus',
                'description' => 'Manage staff members and permissions',
                'is_active' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::updateOrCreate(
                ['slug' => $moduleData['slug']],
                $moduleData
            );
        }

        // Create Plans
        $basicPlan = Plan::updateOrCreate(
            ['slug' => 'basic'],
            [
                'name' => 'Basic',
                'description' => 'Perfect for small businesses getting started',
                'member_limit' => 3,
                'price' => 29.00,
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $proPlan = Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'description' => 'For growing businesses with advanced needs',
                'member_limit' => 10,
                'price' => 99.00,
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $customPlan = Plan::updateOrCreate(
            ['slug' => 'custom'],
            [
                'name' => 'Custom',
                'description' => 'Tailored solution for enterprise needs',
                'member_limit' => null, // Unlimited
                'price' => 0.00, // Custom pricing
                'billing_cycle' => 'monthly',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        // Attach modules to plans
        $allModules = Module::all()->keyBy('slug');

        // Basic Plan - Limited modules
        $basicPlan->modules()->sync([
            $allModules['client-management']->id => ['is_enabled' => true],
            $allModules['service-management']->id => ['is_enabled' => true],
        ]);

        // Pro Plan - Most modules
        $proPlan->modules()->sync([
            $allModules['client-management']->id => ['is_enabled' => true],
            $allModules['service-management']->id => ['is_enabled' => true],
            $allModules['task-compliance']->id => ['is_enabled' => true],
            $allModules['invoice-billing']->id => ['is_enabled' => true],
            $allModules['reports']->id => ['is_enabled' => true],
            $allModules['reminder-system']->id => ['is_enabled' => true],
        ]);

        // Custom Plan - All modules
        $customPlan->modules()->sync(
            $allModules->mapWithKeys(function ($module) {
                return [$module->id => ['is_enabled' => true]];
            })->toArray()
        );

        // Create feature limits
        FeatureLimit::updateOrCreate(
            ['plan_id' => $basicPlan->id, 'feature_key' => 'clients_limit'],
            [
                'feature_name' => 'Maximum Clients',
                'limit_value' => 50,
                'limit_type' => 'count',
            ]
        );

        FeatureLimit::updateOrCreate(
            ['plan_id' => $proPlan->id, 'feature_key' => 'clients_limit'],
            [
                'feature_name' => 'Maximum Clients',
                'limit_value' => 500,
                'limit_type' => 'count',
            ]
        );

        FeatureLimit::updateOrCreate(
            ['plan_id' => $proPlan->id, 'feature_key' => 'invoices_per_month'],
            [
                'feature_name' => 'Invoices Per Month',
                'limit_value' => 100,
                'limit_type' => 'monthly',
            ]
        );

        // Custom plan has unlimited features (no limits created)
    }
}
