<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CertificationsController;
use App\Http\Controllers\ChaptersController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\CouponsController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\QuizzesController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\TrainingsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VouchersController;
use App\Models\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    try {
        $items = [];

        // $items = Company::with('trainings.chapters.lessons', 'trainings.reviews')->get();
        // $items = Training::with('instructor', 'students', 'chapters.lessons', 'reviews')->get();
        // $items = Training::with('instructor', 'students', 'chapters.lessons')->get();
        // $items = Training::with('categories', 'tags', 'instructor', 'students', 'chapters.lessons', 'reviews', 'image', 'video')->get();
        // $items = Order::findOrFail(1);
        // $items = User::with('cart', 'orders')->findOrFail(1);
        // $items = User::with('quizzes.quiz_questions.quiz_question_options.quiz_question_option_items', 'quiz_questions.quiz_question_options.quiz_question_option_items', 'quiz_question_options')->get();
        // $items = QuizQuestionOption::with('students')->get();
        // $items = User::findOrFail(1)->quizzes->pivot->attempt;
        // $items = Quiz::isQuiz()->get();
        // $items = Order::with('trainings', 'quizzes')->get();
        // $items = Quiz::with('certified_students')->get();

        return response()->json($items);
    } catch (\Throwable $th) {
        throw $th;
    }
});

Route::get('/refresh_database', function () {
    logger('-- Refreshing DB --');

    return Artisan::call('migrate:fresh --seed --force');
});

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::get('/email/verify_email/{id}', 'verify_email')->middleware('signed')->name('verification.verify');
    Route::get('/email/resend_verification_email', 'resend_verification_email');
    Route::get('/email/send_forgot_password_email', 'send_forgot_password_email');
    Route::post('/email/reset_password', 'reset_password')->name('password.reset');

    Route::middleware(['auth:api'])->group(function () {
        Route::get('/user', 'auth_user');
        Route::post('/update_password', 'update_password');
        Route::post('/logout', 'logout');
    });
});

Route::middleware(['auth:api', 'check_if_user_is_blocked'])->group(function () {
    Route::get('/config', [ConfigController::class, 'index']);

    Route::controller(FilesController::class)->group(function () {
        Route::post('/upload_file', 'upload_file');
        Route::delete('/delete_file/{id}', 'delete_file');
    });

    Route::controller(CompaniesController::class)->group(function () {
        Route::get('/companies', 'index');
        Route::post('/company', 'store');
        Route::get('/company/{id}', 'show');
        Route::post('/company/{id}', 'update');
        Route::delete('/company/{id}', 'destroy');
    });

    Route::controller(UsersController::class)->group(function () {
        Route::get('/users', 'index');
        Route::get('/user/{id}', 'show');
        Route::post('/user/{id}', 'update');
        Route::delete('/user/{id}', 'destroy');
        Route::get('/user/{instructor_id}/trainings_by_instructor', 'trainings_by_instructor');
        Route::get('/user/{id}/trainings_by_student', 'trainings_by_student');
        Route::get('/user/{id}/quizzes_by_student', 'quizzes_by_student');
        Route::get('/user/{student_id}/cart/{type}', 'cart');
        Route::get('/user/{student_id}/orders', 'orders');
    });

    Route::controller(RolesController::class)->group(function () {
        Route::get('/roles', 'index');
        Route::get('/role/{id}', 'show');
        Route::post('/role/{id}', 'update');
    });

    Route::controller(PermissionsController::class)->group(function () {
        Route::get('/permissions', 'index');
        Route::get('/permission/{id}', 'show');
        Route::post('/permission/{id}', 'update');
    });

    Route::controller(CategoriesController::class)->group(function () {
        Route::get('/categories', 'index');
        Route::post('/category', 'store');
        Route::get('/category/{id}', 'show');
        Route::post('/category/{id}', 'update');
        Route::delete('/category/{id}', 'destroy');
    });

    Route::controller(TagsController::class)->group(function () {
        Route::get('/tags', 'index');
        Route::post('/tag', 'store');
        Route::get('/tag/{id}', 'show');
        Route::post('/tag/{id}', 'update');
        Route::delete('/tag/{id}', 'destroy');
    });

    Route::controller(TrainingsController::class)->group(function () {
        Route::get('/trainings', 'index');
        Route::post('/training', 'store');
        Route::get('/training/{id}', 'show');
        Route::get('/training/{id}/students_by_training', 'students_by_training');
        Route::post('/training/{id}', 'update');
        Route::delete('/training/{id}', 'destroy');
        Route::post('/training/{id}/assign_catgories_to_training', 'assign_catgories_to_training');
        Route::post('/training/{id}/assign_tags_to_training', 'assign_tags_to_training');
    });

    Route::controller(ChaptersController::class)->group(function () {
        Route::get('/chapters', 'index');
        Route::post('/chapters/reorder', 'reorder');
        Route::post('/chapter', 'store');
        Route::get('/chapter/{id}', 'show');
        Route::post('/chapter/{id}', 'update');
        Route::delete('/chapter/{id}', 'destroy');
    });

    Route::controller(LessonsController::class)->group(function () {
        Route::get('/lessons', 'index');
        Route::post('/lessons/reorder', 'reorder');
        Route::post('/lesson', 'store');
        Route::get('/lesson/{id}', 'show');
        Route::post('/lesson/{id}', 'update');
        Route::delete('/lesson/{id}', 'destroy');
        Route::post('/lesson/{id}/toggle_lesson_validation', 'toggle_lesson_validation');
    });

    Route::controller(ReviewsController::class)->group(function () {
        Route::post('/review', 'store');
        Route::get('/review/{id}', 'show');
        Route::post('/review/{id}', 'update');
        Route::delete('/review/{id}', 'destroy');
    });

    Route::controller(OrdersController::class)->group(function () {
        Route::get('/orders', 'index');
        Route::post('/order', 'store');
        Route::get('/order/{id}', 'show');
        Route::post('/order/update_cart', 'update_cart');
        Route::post('/order/add_to_cart', 'add_to_cart');
        Route::post('/order/remove_from_cart', 'remove_from_cart');
        Route::post('/order/convert_cart_to_order/{student_id}', 'convert_cart_to_order');

        Route::post('/order/assign_trainings_to_students', 'assign_trainings_to_students');
        Route::post('/order/unassign_trainings_from_student', 'unassign_trainings_from_student');

        Route::post('/order/{id}', 'update');
        Route::delete('/order/{id}', 'destroy');

        Route::post('/cart/{id}/apply_coupon', 'apply_coupon');
        Route::post('/cart/{id}/unapply_coupon','unapply_coupon');


    });

    Route::controller(CouponsController::class)->group(function () {
        Route::get('/coupons', 'index');
        Route::post('/coupon', 'store');
        Route::get('/coupon/{id}', 'show');
        Route::post('/coupon/{id}', 'update');
        Route::delete('/coupon/{id}', 'destroy');
    });

    Route::controller(VouchersController::class)->group(function () {
        Route::get('/vouchers', 'index');
        Route::post('/voucher', 'store');

        Route::post('/voucher/apply_voucher', 'apply_voucher');

        Route::get('/voucher/{id}', 'show');
        Route::post('/voucher/{id}', 'update');
        Route::delete('/voucher/{id}', 'destroy');
    });

    Route::controller(QuizzesController::class)->group(function () {
        Route::get('/quizzes', 'index');
        Route::post('/quiz', 'store');

        Route::post('/quiz_with_questions_and_options', 'quiz_with_questions_and_options');

        Route::post('/quiz/start_student_quiz', 'start_student_quiz');
        Route::post('/quiz/finish_student_quiz', 'finish_student_quiz');
        Route::get('/quiz/show_quiz_student_answers', 'show_quiz_student_answers');

        Route::post('/quiz/{id}/assign_catgories_to_quiz', 'assign_catgories_to_quiz');

        Route::post('/quiz/update_quiz_with_questions_and_options/{id}', 'update_quiz_with_questions_and_options');

        Route::get('/quiz/{id}', 'show');
        Route::post('/quiz/{id}', 'update');
        Route::delete('/quiz/{id}', 'destroy');
    });

    Route::controller(CertificationsController::class)->group(function () {
        // Route::get('/certified_students_by_training/{id}', 'certified_students_by_training');
        // Route::get('/certified_students_by_quiz/{id}', 'certified_students_by_quiz');
        // Route::get('/certified_trainings_by_student/{id}', 'certified_trainings_by_student');
        // Route::get('/certified_quizzes_by_student/{id}', 'certified_quizzes_by_student');
        Route::post('/certification', 'store');
        Route::get('/certification/{id}', 'show');
        Route::delete('/certification/{id}', 'destroy');
    });


    Route::controller(NotificationController::class)->group(function () {
        Route::get('/notifications', 'index');
        Route::get('/notifications/{id}', 'show');
        Route::post('/notifications', 'store');
        Route::put('/notifications/{id}', 'update');
        Route::delete('/notifications/{id}', 'destroy');
    });



});
