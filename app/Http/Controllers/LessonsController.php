<?php

namespace App\Http\Controllers;

use App\Models\Lesson;

class LessonsController extends Controller
{
    public function index()
    {
        try {
            request()->validate([
                'chapter_id' => 'required|integer|exists:chapters,id',
            ]);

            $lessons = Lesson::where('chapter_id', request('chapter_id'))
                ->latest()
                ->paginate(config('custom_config.pagination_items'));

            return response()->json($lessons);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function reorder()
    {
        try {
            request()->validate([
                '*.id' => 'required|integer|exists:lessons,id',
                '*.position' => 'required|integer|min:1',
            ]);

            foreach (request()->all() as $lesson) {
                Lesson::findOrFail($lesson['id'])->update([
                    'position' => $lesson['position'],
                ]);
            }

            return response()->json(['message' => 'Lessons reordered successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            request()->validate([
                'chapter_id' => 'required|integer|exists:chapters,id',
                'label' => 'required|string|min:1|max:255',
                'content' => 'present',
            ]);

            $lesson = Lesson::create([
                'chapter_id' => request('chapter_id'),
                'label' => request('label'),
                'content' => request('content'),
            ]);

            insert_in_history_table('created', $lesson->id, $lesson->getTable());

            return response()->json(['message' => 'Lesson created successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $lesson = Lesson::findOrFail($id);

            return response()->json($lesson);
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
                'position' => 'required|integer|min:1',
                'content' => 'required|string',
            ]);

            $lesson = Lesson::findOrFail($id);

            $lesson->label = request('label');
            $lesson->position = request('position');
            $lesson->content = request('content');

            $lesson->save();

            insert_in_history_table('updated', $lesson->id, $lesson->getTable());

            return response()->json(['message' => 'Lesson updated successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $lesson = Lesson::findOrFail($id);

            $lesson->delete();

            insert_in_history_table('deleted', $lesson->id, $lesson->getTable());

            return response()->json(['message' => 'Lesson deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function toggle_lesson_validation($id)
    {
        try {
            request()->validate([
                'user_id' => 'required|integer|exists:users,id',
                'toggle_lesson_validation' => 'required|boolean',
            ]);

            $lesson = Lesson::findOrFail($id);

            if (request('toggle_lesson_validation')) {
                if (!$lesson->students()->where('student_lesson.id', request('user_id'))->exists()) {
                    $lesson->students()->attach(request('user_id'));
                }
            } else {
                $lesson->students()->detach(request('user_id'));
            }

            insert_in_history_table('toggled_lesson_validation', $lesson->id, 'lesson_user');

            return response()->json(['message' => 'The relationship between user and lesson is successfully updated.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
