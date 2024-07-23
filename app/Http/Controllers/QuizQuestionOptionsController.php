<?php

namespace App\Http\Controllers;

use App\Models\QuizQuestionOption;

class QuizQuestionOptionsController extends Controller
{
    public function index()
    {
        try {
            $quiz_question_options = QuizQuestionOption::latest()
                ->paginate(config('custom_config.pagination_items'));

            return response()->json($quiz_question_options);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            request()->validate([
                'quiz_question_id' => 'required|integer|exists:quiz_questions,id',
                'label' => 'required|string|min:1|max:255',
            ]);

            $quiz_question_option = QuizQuestionOption::create([
                'quiz_question_id' => request('quiz_question_id'),
                'label' => request('label'),
                'is_correct' => request('is_correct'),
            ]);

            insert_in_history_table('created', $quiz_question_option->id, $quiz_question_option->getTable());

            return response()->json([
                'quiz_question_option_id' => $quiz_question_option->id,
                'message' => 'Quiz Question Option created successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $quiz_question_option = QuizQuestionOption::findOrFail($id);

            return response()->json($quiz_question_option);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            request()->validate([
                'label' => 'required|string|min:1|max:255',
            ]);

            $quiz_question_option = QuizQuestionOption::findOrFail($id);

            $quiz_question_option->label = request('label');
            $quiz_question_option->is_correct = request('is_correct');

            $quiz_question_option->save();

            insert_in_history_table('updated', $quiz_question_option->id, $quiz_question_option->getTable());

            return response()->json(['message' => 'Quiz Question Option updated successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $quiz_question_option = QuizQuestionOption::findOrFail($id);

            $quiz_question_option->delete();

            insert_in_history_table('deleted', $quiz_question_option->id, $quiz_question_option->getTable());

            return response()->json(['message' => 'Quiz Question Option deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
