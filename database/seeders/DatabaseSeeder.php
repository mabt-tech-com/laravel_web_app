<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Company;
use App\Models\Coupon;
use App\Models\File;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\QuizQuestionOptionItem;
use App\Models\QuizQuestionType;
use App\Models\Review;
use App\Models\Role;
use App\Models\Tag;
use App\Models\Training;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $company1 = Company::create(['company_id' => null, 'label' => 'ESPRIT Ecole Sup Privée d\'Ingénierie et de Technologies', 'description' => 'Test Description']);
        // $company2 = Company::create(['company_id' => null, 'label' => 'Université de Carthage', 'description' => 'Test Description']);
        // $company3 = Company::create(['company_id' => null, 'label' => 'Université de Sfax', 'description' => 'Test Description']);
        // $company4 = Company::create(['company_id' => null, 'label' => 'Université de Monastir', 'description' => 'Test Description']);
        // $company5 = Company::create(['company_id' => null, 'label' => 'Université de Tunis El Manar', 'description' => 'Test Description']);

        // $company2 = Company::create(['company_id' => $company1->id, 'label' => 'Esprit Informatique']);
        // $company3 = Company::create(['company_id' => $company1->id, 'label' => 'Esprit School Of Buisness']);
        // $company4 = Company::create(['company_id' => $company1->id, 'label' => 'Esprit Génie Civil']);
        // $company5 = Company::create(['company_id' => $company1->id, 'label' => 'Esprit Électromécanique']);
        // $company10 = Company::create(['company_id' => $company1->id, 'label' => 'Faculté des Sciences']);
        // $company11 = Company::create(['company_id' => $company1->id, 'label' => 'Faculté des Lettres, des Arts et des Humanités']);
        // $company12 = Company::create(['company_id' => $company2->id, 'label' => 'Faculté des Sciences Économiques et de Gestion']);
        // $company13 = Company::create(['company_id' => $company2->id, 'label' => 'Faculté des Sciences Humaines et Sociales']);
        // $company14 = Company::create(['company_id' => $company3->id, 'label' => 'Faculté des Sciences']);
        // $company15 = Company::create(['company_id' => $company3->id, 'label' => 'Faculté de Médecine']);
        // $company16 = Company::create(['company_id' => $company4->id, 'label' => 'Faculté des Sciences Économiques et de Gestion']);
        // $company17 = Company::create(['company_id' => $company4->id, 'label' => 'Faculté des Sciences']);

        $role1 = Role::create(['label' => 'User']);
        $role2 = Role::create(['label' => 'Instructor']);
        $role3 = Role::create(['label' => 'Content Manager']);
        $role4 = Role::create(['label' => 'Admin']);
        $role5 = Role::create(['label' => 'Super-Admin']);

        $permission1 = Permission::create(['id' => Permission::USERS_LIST, 'label' => 'users-list']);
        $permission2 = Permission::create(['id' => Permission::USERS_CREATE, 'label' => 'users-create']);
        $permission3 = Permission::create(['id' => Permission::USERS_VIEW, 'label' => 'users-view']);
        $permission4 = Permission::create(['id' => Permission::USERS_UPDATE, 'label' => 'users-update']);
        $permission5 = Permission::create(['id' => Permission::USERS_DELETE, 'label' => 'users-delete']);
        $permission6 = Permission::create(['id' => Permission::USERS_RESTORE, 'label' => 'users-delete']);
        $permission7 = Permission::create(['id' => Permission::USERS_FORCE_DELETE, 'label' => 'users-force-delete']);

        $permission10 = Permission::create(['id' => Permission::COMPANIES_LIST, 'label' => 'companies-list']);
        $permission11 = Permission::create(['id' => Permission::COMPANIES_CREATE, 'label' => 'companies-create']);
        $permission12 = Permission::create(['id' => Permission::COMPANIES_VIEW, 'label' => 'companies-view']);
        $permission13 = Permission::create(['id' => Permission::COMPANIES_UPDATE, 'label' => 'companies-edit']);
        $permission14 = Permission::create(['id' => Permission::COMPANIES_DELETE, 'label' => 'companies-delete']);
        $permission15 = Permission::create(['id' => Permission::COMPANIES_RESTORE, 'label' => 'companies-restore']);
        $permission16 = Permission::create(['id' => Permission::COMPANIES_FORCE_DELETE, 'label' => 'companies-force-delete']);

        $i = 1;

        PermissionRole::create(['company_id' => $i, 'role_id' => $role1->id, 'permission_id' => $permission1->id]);

        PermissionRole::create(['company_id' => $i, 'role_id' => $role2->id, 'permission_id' => $permission1->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role2->id, 'permission_id' => $permission2->id]);

        PermissionRole::create(['company_id' => $i, 'role_id' => $role3->id, 'permission_id' => $permission1->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role3->id, 'permission_id' => $permission2->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role3->id, 'permission_id' => $permission3->id]);

        PermissionRole::create(['company_id' => $i, 'role_id' => $role4->id, 'permission_id' => $permission1->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role4->id, 'permission_id' => $permission2->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role4->id, 'permission_id' => $permission3->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role4->id, 'permission_id' => $permission12->id]);

        PermissionRole::create(['company_id' => $i, 'role_id' => $role5->id, 'permission_id' => $permission1->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role5->id, 'permission_id' => $permission2->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role5->id, 'permission_id' => $permission3->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role5->id, 'permission_id' => $permission10->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role5->id, 'permission_id' => $permission11->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role5->id, 'permission_id' => $permission12->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role5->id, 'permission_id' => $permission13->id]);
        PermissionRole::create(['company_id' => $i, 'role_id' => $role5->id, 'permission_id' => $permission14->id]);

        $user1 = User::create(['company_id' => $i, 'role_id' => $role1->id, 'first_name' => 'Student', 'last_name' => 'Student', 'email' => 'student@gmail.com', 'image_id' => null, 'password' => bcrypt('123456'), 'email_verified_at' => now()]);
        $user2 = User::create(['company_id' => $i, 'role_id' => $role2->id, 'first_name' => 'Instructor', 'last_name' => 'Instructor', 'email' => 'instructor@gmail.com', 'image_id' => null, 'password' => bcrypt('123456'), 'email_verified_at' => now()]);
        $user3 = User::create(['company_id' => $i, 'role_id' => $role3->id, 'first_name' => 'Content', 'last_name' => 'Manager', 'email' => 'content_manager@gmail.com', 'image_id' => null, 'password' => bcrypt('123456'), 'email_verified_at' => now()]);
        $user4 = User::create(['company_id' => $i, 'role_id' => $role4->id, 'first_name' => 'Admin', 'last_name' => 'Admin', 'email' => 'admin@gmail.com', 'image_id' => null, 'password' => bcrypt('123456'), 'email_verified_at' => now()]);
        $user5 = User::create(['company_id' => $i, 'role_id' => $role5->id, 'first_name' => 'Super', 'last_name' => 'Admin', 'email' => 'super_admin@gmail.com', 'image_id' => null, 'password' => bcrypt('123456'), 'email_verified_at' => now()]);
        $user6 = User::create(['company_id' => $i, 'role_id' => $role5->id, 'first_name' => 'Vmware', 'last_name' => 'Reporting', 'email' => 'vmwarereporting@gmail.com', 'image_id' => null, 'password' => bcrypt('123456'), 'email_verified_at' => now()]);

        Category::factory()->count(30)->create();
        Tag::factory()->count(30)->create();
        User::factory()->count(100)->create();
        File::factory()->count(500)->create();
        Training::factory()->count(100)->create();
        Chapter::factory()->count(300)->create();
        Lesson::factory()->count(800)->create();

        OrderStatus::create(['label' => OrderStatus::CANCELLED]);
        OrderStatus::create(['label' => OrderStatus::PENDING]);
        OrderStatus::create(['label' => OrderStatus::AWAITING_PAYMENT]);
        OrderStatus::create(['label' => OrderStatus::COMPLETED]);

        Coupon::factory()->count(30)->create();
        Voucher::factory()->count(30)->create();

        QuizQuestionType::create(['label' => QuizQuestionType::TRUE_OR_FALSE]);
        QuizQuestionType::create(['label' => QuizQuestionType::SINGLE_ANSWER]);
        QuizQuestionType::create(['label' => QuizQuestionType::MULTIPLE_ANSWER]);
        QuizQuestionType::create(['label' => QuizQuestionType::DRAG_AND_DROP]);

        Quiz::factory()->count(50)->create();
        QuizQuestion::factory()->count(200)->create();
        QuizQuestionOption::factory()->count(500)->create();
        QuizQuestionOptionItem::factory()->count(500)->create();

        Review::factory()->count(60)->create();

        Order::factory()->count(100)->create();
    }
}
