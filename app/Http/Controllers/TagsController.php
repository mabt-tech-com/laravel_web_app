<?php

namespace App\Http\Controllers;

use App\Models\Tag;

class TagsController extends Controller
{
    public function index()
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
            ]);

            $tags_query_builder = Tag::where('company_id', request('company_id'))->withCount('trainings');

            if (request('has_trainings') === 'true') {
                $tags_query_builder->has('trainings', '>', 0);
            }

            $tags = $tags_query_builder->paginate(config('custom_config.pagination_items'));

            return response()->json($tags);
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

            $tag = Tag::create([
                'company_id' => request('company_id'),
                'label' => request('label'),
            ]);

            insert_in_history_table('created', $tag->id, $tag->getTable());

            return response()->json([
                'message' => 'Tag created successfully.',
                'data' => $tag,
            ]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $tag = Tag::findOrFail($id);

            return response()->json($tag);
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

            $tag = Tag::where('company_id', request('company_id'))->findOrFail($id);

            $tag->label = request('label');

            $tag->save();

            insert_in_history_table('updated', $tag->id, $tag->getTable());

            return response()->json(['message' => 'Tag updated successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tag = Tag::with('trainings')->findOrFail($id);

            if ($tag->trainings->count() > 0) {
                return response()->json(['message' => 'Tag can\'t be deleted, used in ' . $tag->trainings->count() . ' trainings.'], 500);
            }
            $tag->delete();

            insert_in_history_table('deleted', $tag->id, $tag->getTable());

            return response()->json(['message' => 'Tag deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
