<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Voucher;

class VouchersController extends Controller
{
    public function index()
    {
        try {
            $vouchers = Voucher::with('trainings', 'quizzes')
                ->withCount('orders')
                ->paginate(config('custom_config.pagination_items'));

            return response()->json($vouchers);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store()
    {
        try {
            request()->validate([
                // 'code' => 'required|string|unique:vouchers,code',
                'title' => 'required|string',
                'description' => 'required|string',
                'active' => 'required|boolean',
                'expires_at' => 'required|date',
                'trainings' => 'array',
                'trainings.*' => 'nullable|integer|distinct|exists:trainings,id',
                'quizzes' => 'array',
                'quizzes.*' => 'nullable|integer|distinct|exists:quizzes,id',
            ]);

            $voucher = Voucher::create([
                'code' => str()->random(13), // request('code'),
                'title' => request('title'),
                'description' => request('description'),
                'active' => request('active'),
                'expires_at' => request('expires_at'),
            ]);

            $voucher->trainings()->sync(request('trainings'));
            $voucher->quizzes()->sync(request('quizzes'));

            insert_in_history_table('created', $voucher->id, $voucher->getTable());

            return response()->json([
                'voucher_id' => $voucher->id,
                'message' => 'Voucher created successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $voucher = Voucher::with('trainings', 'quizzes')
                ->withCount('orders')
                ->findOrFail($id);

            return response()->json($voucher);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            request()->validate([
                // 'code' => 'required|string',
                'title' => 'required|string',
                'description' => 'required|string',
                'active' => 'required|boolean',
                'expires_at' => 'required|date',
                'trainings' => 'sometimes|array|min:1',
                'trainings.*' => 'sometimes|integer|distinct|exists:trainings,id',
                'quizzes' => 'sometimes|array|min:1',
                'quizzes.*' => 'sometimes|integer|distinct|exists:quizzes,id',
            ]);

            $voucher = Voucher::findOrFail($id);

            // $voucher->code = request('code');
            $voucher->title = request('title');
            $voucher->description = request('description');
            $voucher->active = request('active');
            $voucher->expires_at = request('expires_at');

            $voucher->save();

            $voucher->trainings()->sync(request('trainings'));
            $voucher->quizzes()->sync(request('quizzes'));

            insert_in_history_table('updated', $voucher->id, $voucher->getTable());

            return response()->json(['message' => 'Voucher updated successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $voucher = Voucher::withCount('orders')->findOrFail($id);

            if ($voucher->orders_count > 0) {
                return response()->json(['message' => 'Voucher cannot be deleted.']);
            }
            $voucher->delete();

            insert_in_history_table('deleted', $voucher->id, $voucher->getTable());

            return response()->json(['message' => 'Voucher deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function apply_voucher()
    {
        try {
            request()->validate([
                'code' => 'required|string|exists:vouchers,code',
                'student_id' => 'required|integer|exists:users,id',
            ]);

            $voucher = Voucher::with('orders')->where('code', request('code'))->firstOrFail();

            if ($voucher->orders()->exists()) {
                return response()->json(['message' => 'Voucher already applied.'], 401);
            }

            $order = Order::create([
                'student_id' => request('student_id'),
                'type' => Order::ORDER,
                'order_status_id' => OrderStatus::COMPLETED_ID,
                'voucher_id' => $voucher->id,
            ]);

            $order->trainings()->sync($voucher->trainings->pluck('id'));
            $order->quizzes()->sync($voucher->quizzes->pluck('id'));

            insert_in_history_table('applied_voucher', $voucher->id, $voucher->getTable());

            return response()->json(['message' => 'Voucher applied successfully.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
