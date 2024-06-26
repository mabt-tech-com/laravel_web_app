<?php

namespace App\Http\Controllers;

use App\Models\Review;

class ReviewsController extends Controller
{
    public function store()
    {
        try {
            request()->validate([
                'training_id' => 'required|integer|exists:trainings,id',
                'rating' => 'required|numeric|between:0,5',
                'comment' => 'required|string',
            ]);

            $review = Review::updateOrCreate(
                [
                    'user_id' => auth()->user()->id,
                    'training_id' => request('training_id'),
                ],
                [
                    'rating' => request('rating'),
                    'comment' => request('comment'),
                ]
            );

            insert_in_history_table('created', $review->id, $review->getTable());

            return response()->json(['message' => 'Review created successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $review = Review::findOrFail($id);

            return response()->json($review);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            request()->validate([
                'rating' => 'required|numeric|between:0,5',
                'comment' => 'required|string',
            ]);

            $review = Review::findOrFail($id);

            $review->rating = request('rating');
            $review->comment = request('comment');

            $review->save();

            insert_in_history_table('updated', $review->id, $review->getTable());

            return response()->json(['message' => 'Review updated successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $review = Review::findOrFail($id);

            $review->delete();

            insert_in_history_table('deleted', $review->id, $review->getTable());

            return response()->json(['message' => 'Review deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
