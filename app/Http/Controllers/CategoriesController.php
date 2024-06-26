<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoriesController extends Controller
{
    public function index()
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
            ]);

            $categories_query_builder = Category::where('company_id', request('company_id'))
                ->withCount('trainings')
                ->withCount('quizzes');

            if (request('has_trainings') === 'true') {
                $categories_query_builder->has('trainings', '>', 0);
            }

            $categories = $categories_query_builder->paginate(config('custom_config.pagination_items'));

            return response()->json($categories);
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
                'label' => 'required|string|min:1|max:255',
            ]);

            $category = Category::create([
                'company_id' => request('company_id'),
                'label' => request('label'),
            ]);

            insert_in_history_table('created', $category->id, $category->getTable());

            return response()->json([
                'message' => trans('category.created'),
                'data' => $category,
            ]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);

            return response()->json($category);
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
                'label' => 'required|string|min:1|max:255',
            ]);

            $category = Category::where('company_id', request('company_id'))->findOrFail($id);

            $category->label = request('label');

            $category->save();

            insert_in_history_table('updated', $category->id, $category->getTable());

            return response()->json(['message' => trans('category.updated')]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::with('trainings')->findOrFail($id);

            if ($category->trainings->count() > 0) {
                return response()->json(['message' => 'Category can\'t be deleted, used in ' . $category->trainings->count() . ' trainings.'], 500);
            }
            $category->delete();

            insert_in_history_table('deleted', $category->id, $category->getTable());

            return response()->json(['message' => trans('category.deleted')]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
