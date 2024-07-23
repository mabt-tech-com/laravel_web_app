<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use App\Models\Permission;
use App\Models\QuizQuestionType;
use App\Models\Role;
use App\Models\Training;

class ConfigController extends Controller
{
    public function index()
    {
        try {
            $config = [];

            $config['roles'] = Role::all();
            $config['permissions'] = Permission::all();
            $config['order_status'] = OrderStatus::all();
            $config['trainings_levels'] = [Training::BEGINNER, Training::INTERMEDIATE, Training::EXPERT];
            $config['quiz_question_types'] = QuizQuestionType::all();

            return response()->json($config);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
