<?php

namespace App\Http\Controllers;

use App\Models\Chapter;

class ChaptersController extends Controller
{
    public function index()
    {
        try {
            request()->validate([
                'training_id' => 'required|integer|exists:trainings,id',
            ]);

            $chapters = Chapter::where('training_id', request('training_id'))
                ->with('lessons', 'quiz.quiz_questions.quiz_question_options.quiz_question_option_items')
                ->paginate(config('custom_config.pagination_items'));

            return response()->json($chapters);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function reorder()
    {
        try {
            request()->validate([
                '*.id' => 'required|integer|exists:chapters,id',
                '*.position' => 'required|integer|min:1',
            ]);

            foreach (request()->all() as $chapter) {
                Chapter::findOrFail($chapter['id'])->update([
                    'position' => $chapter['position'],
                ]);
            }

            return response()->json(['message' => 'Chapters reordered successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            request()->validate([
                'training_id' => 'required|integer|exists:trainings,id',
                'label' => 'required|string|min:1|max:255',
                'position' => 'required|integer|min:1',
            ]);

            $chapter = Chapter::create([
                'training_id' => request('training_id'),
                'label' => request('label'),
                'position' => request('position'),
            ]);

            insert_in_history_table('created', $chapter->id, $chapter->getTable());

            return response()->json([
                'chapter_id' => $chapter->id,
                'message' => 'Chapter created successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $chapter = Chapter::with('quiz.quiz_questions.quiz_question_options.quiz_question_option_items')->findOrFail($id);

            return response()->json($chapter);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            request()->validate([
                'training_id' => 'required|integer|exists:trainings,id',
                'label' => 'required|string|min:1|max:255',
                'position' => 'required|integer|min:1',
            ]);

            $chapter = Chapter::where('training_id', request('training_id'))->findOrFail($id);

            $chapter->label = request('label');
            $chapter->position = request('position');

            $chapter->save();

            insert_in_history_table('updated', $chapter->id, $chapter->getTable());

            return response()->json(['message' => 'Chapter updated successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $chapter = Chapter::findOrFail($id);

            $chapter->lessons()->delete();
            $chapter->delete();

            insert_in_history_table('deleted', $chapter->id, $chapter->getTable());

            return response()->json(['message' => 'Chapter deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
