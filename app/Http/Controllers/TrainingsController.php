<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Training;

class TrainingsController extends Controller
{
    public function index()
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'level' => 'sometimes|string|min:1|max:255|in:' . Training::BEGINNER . ',' . Training::INTERMEDIATE . ',' . Training::EXPERT,
                'min_price' => 'sometimes|integer',
                'max_price' => 'sometimes|integr',
            ]);

            $trainings_query_builder = Training::where('company_id', request('company_id'))
                ->with('categories', 'tags', 'instructor.role.permissions', 'chapters.lessons', 'reviews', 'image', 'video', 'quiz.quiz_questions.quiz_question_options.quiz_question_option_items', 'chapters.quiz.quiz_questions.quiz_question_options.quiz_question_option_items')
                ->withAvg('reviews', 'rating');

            if (request('instructor_id')) {
                $trainings_query_builder->where('instructor_id', request('instructor_id'));
            }

            if (request('level')) {
                $trainings_query_builder->where('level', request('level'));
            }

            if (request('min_price')) {
                $trainings_query_builder->where('price', '>', request('min_price'));
            }

            if (request('max_price')) {
                $trainings_query_builder->where('price', '<', request('max_price'));
            }

            if (request('categories')) {
                $trainings_query_builder->whereHas('categories', function ($query) {
                    $query->whereIn('categories.id', json_decode(request('categories'), true));
                });
            }

            if (request('tags')) {
                $trainings_query_builder->whereHas('tags', function ($query) {
                    $query->whereIn('tags.id', json_decode(request('tags'), true));
                });
            }

            if (request('reviews')) {
                $trainings_query_builder->havingBetween('reviews_avg_rating', json_decode(request('reviews'), true));
            }

            $trainings = $trainings_query_builder->paginate(config('custom_config.pagination_items'));

            return response()->json($trainings);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'instructor_id' => 'required|integer|exists:users,id',
                'label' => 'required|string|min:1|max:255',
                'description' => 'required|string|min:1|max:255',
                'level' => 'required|string|min:1|max:255',
                'duration' => 'required|string|min:1|max:255',
                'price' => 'required|numeric|between:0,9999999.999',
                'discounted_price' => 'required|numeric|between:0,9999999.999',
                'image_id' => 'required|integer|exists:files,id',
                'video_id' => 'required|integer|exists:files,id',
            ]);

            $training = Training::create([
                'company_id' => request('company_id'),
                'instructor_id' => request('instructor_id'),
                'label' => request('label'),
                'description' => request('description'),
                'level' => request('level'),
                'duration' => request('duration'),
                'price' => request('price'),
                'discounted_price' => request('discounted_price'),
                'image_id' => request('image_id'),
                'video_id' => request('video_id'),
            ]);

            insert_in_history_table('created', $training->id, $training->getTable());

            return response()->json([
                'training_id' => $training->id,
                'message' => 'Training created successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function assign_catgories_to_training($id)
    {
        try {
            request()->validate([
                'categories' => 'present|array|min:1',
                'categories.*' => 'required|integer|distinct|exists:categories,id',
            ]);

            $training = Training::findOrFail($id);

            $training->categories()->sync(request('categories'));

            insert_in_history_table('assigned_categories', $training->id, 'category_training');

            return response()->json(['message' => 'Categories assigned to training successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function assign_tags_to_training($id)
    {
        try {
            request()->validate([
                'tags' => 'present|array|min:1',
                'tags.*' => 'required|integer|distinct|exists:tags,id',
            ]);

            $training = Training::findOrFail($id);

            $training->tags()->sync(request('tags'));

            insert_in_history_table('assigned_tags', $training->id, 'tag_training');

            return response()->json(['message' => 'Tags assigned to training successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
    public function show($id)
    {
        try {
            $training = Training::with('categories', 'tags', 'instructor', 'chapters.lessons', 'reviews', 'image', 'video', 'quiz.quiz_questions.quiz_question_options.quiz_question_option_items', 'chapters.quiz.quiz_questions.quiz_question_options.quiz_question_option_items')->findOrFail($id);

            $training->views_count += 1;
            $training->save();

            return response()->json($training);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'instructor_id' => 'required|integer|exists:users,id',
                'label' => 'required|string|min:1|max:255',
                'description' => 'required|string|min:1|max:255',
                'level' => 'required|string|min:1|max:255',
                'duration' => 'required|string|min:1|max:255',
                'price' => 'required|numeric|between:0,9999999.999',
                'discounted_price' => 'nullable|numeric|between:0,9999999.999',
                'image_id' => 'nullable|integer|exists:files,id',
                'video_id' => 'nullable|integer|exists:files,id',
            ]);

            $training = Training::where('company_id', request('company_id'))->findOrFail($id);

            $training->instructor_id = request('instructor_id');
            $training->label = request('label');
            $training->description = request('description');
            $training->level = request('level');
            $training->duration = request('duration');
            $training->price = request('price');
            $training->discounted_price = request('discounted_price');
            $training->image_id = request('image_id');
            $training->video_id = request('video_id');
            $training->is_public = request('is_public');

            $training->save();

            insert_in_history_table('updated', $training->id, $training->getTable());

            return response()->json(['message' => 'Training updated successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $training = Training::with('chapters.lessons', 'image', 'video')->findOrFail($id);

            foreach ($training->chapters as $chapter) {
                $chapter->lessons()->delete();
            }
            $training->chapters()->delete();
            $training->categories()->detach();
            $training->tags()->detach();
            $training->reviews()->delete();
            $training->delete_image();
            $training->delete_video();

            $training->delete();

            insert_in_history_table('deleted', $training->id, $training->getTable());

            return response()->json(['message' => 'Training deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function students_by_training($id)
    {
        try {
            $orders = Training::findOrFail($id)->orders()->where('order_items.training_id', $id)->where('type', Order::ORDER)->get();

            $students = (new Order)->students_from_orders($orders);

            return response()->json($students);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
