<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view-courses',
            'create-courses',
            'update-courses',
            'delete-courses',
            'publish-courses',
            'manage-sections',
            'manage-modules',
            'manage-quizzes',
            'manage-users',
            'manage-roles',
            'manage-categories',
            'enroll-courses',
            'track-progress',
            'take-quizzes',
            'view-certificates',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm],
                ['display_name' => ucwords(str_replace('-', ' ', $perm))]
            );
        }

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator', 'description' => 'System Administrator']
        );

        $instructorRole = Role::firstOrCreate(
            ['name' => 'instructor'],
            ['display_name' => 'Instructor', 'description' => 'Course Instructor']
        );

        $learnerRole = Role::firstOrCreate(
            ['name' => 'learner'],
            ['display_name' => 'Learner', 'description' => 'Student / Learner']
        );

        // Sync permissions to roles
        $adminRole->syncPermissions(Permission::all());

        $instructorRole->syncPermissions(Permission::whereIn('name', [
            'view-courses',
            'create-courses',
            'update-courses',
            'delete-courses',
            'manage-sections',
            'manage-modules',
            'manage-quizzes',
        ])->get());

        $learnerRole->syncPermissions(Permission::whereIn('name', [
            'view-courses',
            'enroll-courses',
            'track-progress',
            'take-quizzes',
            'view-certificates',
        ])->get());

        // Create initial test users
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        if (! $adminUser->hasRole('admin')) {
            $adminUser->addRole('admin');
        }

        $instructorUser = User::firstOrCreate(
            ['email' => 'instructor@example.com'],
            [
                'name' => 'Instructor User',
                'password' => Hash::make('password'),
            ]
        );
        if (! $instructorUser->hasRole('instructor')) {
            $instructorUser->addRole('instructor');
        }

        $learnerUser = User::firstOrCreate(
            ['email' => 'learner@example.com'],
            [
                'name' => 'Learner User',
                'password' => Hash::make('password'),
            ]
        );
        if (! $learnerUser->hasRole('learner')) {
            $learnerUser->addRole('learner');
        }
    }
}
