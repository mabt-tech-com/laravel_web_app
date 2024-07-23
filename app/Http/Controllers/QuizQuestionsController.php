<?php

namespace App\Http\Controllers;

use App\Models\QuizQuestion;

class QuizQuestionsController extends Controller
{
    public function index()
    {
        try {
            $quiz_questions = QuizQuestion::with('quiz_question_type', 'quiz_question_options')
                ->latest()
                ->paginate(config('custom_config.pagination_items'));

            return response()->json($quiz_questions);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            request()->validate([
                'quiz_id' => 'required|integer|exists:quizzes,id',
                'quiz_question_type_id' => 'required|integer|exists:quiz_question_types,id',
                'label' => 'required|string|min:1|max:255',
                'tip' => 'nullable|string|min:1|max:255',
                'explanation' => 'nullable|string|min:1|max:255',
            ]);

            $quiz_question = QuizQuestion::create([
                'quiz_id' => request('quiz_id'),
                'quiz_question_type_id' => request('quiz_question_type_id'),
                'label' => request('label'),
                'tip' => request('tip'),
                'explanation' => request('explanation'),
            ]);

            insert_in_history_table('created', $quiz_question->id, $quiz_question->getTable());

            return response()->json([
                'quiz_id' => $quiz_question->id,
                'message' => 'QuizQuestion created successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $quiz_question = QuizQuestion::with('quiz_question_type', 'quiz_question_options')->findOrFail($id);

            return response()->json($quiz_question);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            request()->validate([
                'quiz_question_type_id' => 'required|integer|exists:quiz_question_quiz_question_types,id',
                'label' => 'required|string|min:1|max:255',
                'tip' => 'nullable|string|min:1|max:255',
                'explanation' => 'nullable|string|min:1|max:255',
            ]);

            $quiz_question = QuizQuestion::findOrFail($id);

            $quiz_question->quiz_question_type_id = request('quiz_question_type_id');
            $quiz_question->label = request('label');
            $quiz_question->tip = request('tip');
            $quiz_question->explanation = request('explanation');

            $quiz_question->save();

            insert_in_history_table('updated', $quiz_question->id, $quiz_question->getTable());

            return response()->json(['message' => 'QuizQuestion updated successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $quiz_question = QuizQuestion::with('quiz_question_options')->findOrFail($id);

            $quiz_question->quiz_question_options()->delete();

            $quiz_question->delete();

            insert_in_history_table('deleted', $quiz_question->id, $quiz_question->getTable());

            return response()->json(['message' => 'QuizQuestion deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
