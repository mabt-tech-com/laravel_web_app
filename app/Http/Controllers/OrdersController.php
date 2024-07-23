<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;

class OrdersController extends Controller
{
    public function index()
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
            ]);

            $orders_query_builder = Order::where('company_id', request('company_id'))
                ->with('student', 'order_status', 'trainings.image', 'quizzes', 'coupon')
                ->latest();

            if (request('student_id')) {
                $orders_query_builder->where('student_id', request('student_id'));
            }
            if (request('order_status_id')) {
                $orders_query_builder->where('order_status_id', request('order_status_id'));
            }
            if (request('coupon_id')) {
                $orders_query_builder->where('coupon_id', request('coupon_id'));
            }
            if (request('type')) {
                $orders_query_builder->where('type', request('type'));
            }
            if (request('training_id')) {
                $orders_query_builder->whereHas('trainings', function ($query) {
                    $query->where('trainings.id', request('training_id'));
                })->get();
            }
            if (request('quiz_id')) {
                $orders_query_builder->whereHas('quizzes', function ($query) {
                    $query->where('quizzes.id', request('quiz_id'));
                })->get();
            }

            $orders = $orders_query_builder->paginate(config('custom_config.pagination_items'));

            return response()->json($orders);
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
                'student_id' => 'required|integer|exists:users,id',
                'order_status_id' => 'sometimes:integer|exists:order_status,id',
                'notes' => 'sometimes|string',
                'trainings' => 'sometimes|array|min:1',
                'trainings.*' => 'sometimes|integer|distinct|exists:trainings,id',
                'quizzes' => 'sometimes|array|min:1',
                'quizzes.*' => 'sometimes|integer|distinct|exists:quizzes,id',
            ]);

            $order = Order::create([
                'company_id' => request('company_id'),
                'student_id' => request('student_id'),
                'type' => Order::ORDER,
                'order_status_id' => request('order_status_id'),
                'notes' => request('notes'),
            ]);

            $order->trainings()->sync(request('trainings'));
            $order->quizzes()->sync(request('quizzes'));

            insert_in_history_table('created', $order->id, $order->getTable());

            return response()->json([
                'order_id' => $order->id,
                'message' => 'Order created successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $order = Order::with('student', 'order_status', 'trainings.image', 'quizzes', 'coupon')->findOrFail($id);

            return response()->json($order);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update_cart()
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'student_id' => 'required|integer|exists:users,id',
                'type' => 'required|integer|in:' . Order::WISHLIST . ',' . Order::CART,
                'notes' => 'sometimes|string',
                'trainings' => 'required|array|min:1',
                'trainings.*' => 'required|integer|distinct|exists:trainings,id',
                'quizzes' => 'required|array|min:1',
                'quizzes.*' => 'required|integer|distinct|exists:quizzes,id',
            ]);

            $order = Order::updateOrCreate(
                [
                    'company_id' => request('company_id'),
                    'student_id' => request('student_id'),
                    'type' => request('type'),
                ],
                [
                    'coupon_id' => request('coupon_id'),
                    'notes' => request('notes'),
                ]
            );

            $order->trainings()->sync(request('trainings'));
            $order->quizzes()->sync(request('quizzes'));

            insert_in_history_table('updated', $order->id, $order->getTable());

            return response()->json([
                'order_id' => $order->id,
                'message' => 'Cart updated successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function add_to_cart()
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'student_id' => 'required|integer|exists:users,id',
                'training_id' => 'prohibited_unless:quiz_id,null|required_without:quiz_id|integer|exists:trainings,id',
                'quiz_id' => 'prohibited_unless:training_id,null|required_without:training_id|integer|exists:quizzes,id',
            ]);

            $order = Order::updateOrCreate(
                [
                    'company_id' => request('company_id'),
                    'student_id' => request('student_id'),
                    'type' => 2,
                ],
                [
                    'notes' => request('notes'),
                ]
            );

            if (request('training_id')) {
                $training_exists = $order->trainings()->where('trainings.id', request('training_id'))->exists();

                if ($training_exists) {
                    return response()->json([
                        'message' => 'Training already exists.',
                    ], 403);
                }

                $order->trainings()->attach(request('training_id'));
            }
            if (request('quiz_id')) {
                $quiz_exists = $order->quizzes()->where('quizzes.id', request('quiz_id'))->exists();

                if ($quiz_exists) {
                    return response()->json([
                        'message' => 'Quiz already exists.',
                    ], 403);
                }

                $order->quizzes()->attach(request('quiz_id'));
            }

            insert_in_history_table('updated', $order->id, $order->getTable());

            return response()->json([
                'order_id' => $order->id,
                'message' => 'Cart updated successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function remove_from_cart()
    {
        try {
            request()->validate([
                'student_id' => 'required|integer|exists:users,id',
                'training_id' => 'prohibited_unless:quiz_id,null|required_without:quiz_id|integer|exists:trainings,id',
                'quiz_id' => 'prohibited_unless:training_id,null|required_without:training_id|integer|exists:quizzes,id',
            ]);

            $order = Order::updateOrCreate(
                [
                    'student_id' => request('student_id'),
                    'type' => 2,
                ],
                [
                    'notes' => request('notes'),
                ]
            );

            if (request('training_id')) {
                $order->trainings()->detach(request('training_id'));
            }
            if (request('quiz_id')) {
                $order->quizzes()->detach(request('quiz_id'));
            }

            insert_in_history_table('updated', $order->id, $order->getTable());

            return response()->json([
                'order_id' => $order->id,
                'message' => 'Cart updated successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }


    public function convert_cart_to_order($student_id)
    {

    try {

        $cart = Order::with('student', 'trainings', 'quizzes')->where('student_id', $student_id)->where('type', Order::CART)->first();

        if ($cart) {
            $order = Order::create([
                'company_id' => request('company_id'),
                'student_id' => $cart->student_id,
                'type' => Order::ORDER,
                'order_status_id' => OrderStatus::PENDING_ID,
                'coupon_id' => $cart->coupon_id,
                'voucher_id' => $cart->voucher_id,
            ]);

            $order->trainings()->sync($cart->trainings->pluck('id'));
            $cart->trainings()->detach();

            $order->quizzes()->sync($cart->quizzes->pluck('id'));
            $cart->quizzes()->detach();

            insert_in_history_table('created', $order->id, $order->getTable());

            // Reset the coupon applied to the cart, voucher & discount price
            $this->unapply_coupon($cart->id);


            return response()->json([
                'order_id' => $order->id,
                'message' => 'Order created successfully.',
            ]);
        }
        return response()->json([
            'message' => 'Cart does not exists.',
        ], 500);

    } catch (\Throwable $th) {
        // Output any other err msg
        report($th);
        return response()->json(['message' => $th->getMessage()], 500);
    }
  }


    public function update($id)
    {
        try {
            request()->validate([
                'order_status_id' => 'sometimes|integer|exists:order_status,id',
                'notes' => 'sometimes|string',
            ]);

            $order = Order::findOrFail($id);

            $order->order_status_id = request('order_status_id');
            $order->notes = request('notes');

            $order->save();

            insert_in_history_table('updated', $order->id, $order->getTable());

            return response()->json(['message' => 'Order updated successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);

            $order->trainings()->detach();
            $order->quizzes()->detach();
            $order->delete();

            insert_in_history_table('deleted', $order->id, $order->getTable());

            return response()->json(['message' => 'Order deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function apply_coupon($id)
    {
        try {
            request()->validate([
                'code' => 'required|string|exists:coupons,code',
            ]);

            $order = Order::findOrFail($id);

            $coupon = Coupon::where('code', request('code'))->firstOrFail();

            $result = $coupon->apply_coupon($order->total_price);

            if ($result === true) {
                $order->coupon_id = $coupon->id;
                $order->save();

                insert_in_history_table('applied_coupon', $order->id, $order->getTable());

                return response()->json(['message' => 'Coupon applied successfully.']);
            }
            return response()->json(['message' => $result], 403);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }


    // unapply coupon - Reset Coupon to null
    public function unapply_coupon($id)
    {
        try {
            // Find the order by ID
            $order = Order::findOrFail($id);

            if ($order->coupon_id) {
                $order->coupon_id = null;
                // Reset - Set the discounted_price to 0
                //$order->discounted_price = null;
                $order->save();

                // Log - unapply coupon in history table
                insert_in_history_table('unapplied_coupon', $order->id, $order->getTable());
                return response()->json(['message' => 'Coupon unapplied successfully.'], 200);
            }

            // 400
            return response()->json(['message' => 'No coupon was applied to this cart.'], 400);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // 404- Handle - cart is not found
            return response()->json(['message' => 'Cart not found.'], 404);
        } catch (\Throwable $th) {
            // 500 - Log - any other exception
            report($th);
            return response()->json(['message' => 'An error occurred while unapplying the coupon.'], 500);
        }
    }


    public function assign_trainings_to_students()
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'order_status_id' => 'required|integer|exists:order_status,id',
                'students' => 'required|array',
                'students.*' => 'required|integer|distinct|exists:users,id',
                'trainings' => 'required|array',
                'trainings.*' => 'required|integer|distinct|exists:trainings,id',
            ]);

            for ($i = 0; $i < count(request('students')); $i++) {
                $student = User::findOrFail(request('students')[$i]);

                $trainings_to_be_assigned = collect([]);
                $currentTrainings = $student->trainings()->pluck('id')->toArray();
                for ($j = 0; $j < count(request('trainings')); $j++) {
                    if (!in_array(request('trainings')[$j], $currentTrainings)) {
                        $trainings_to_be_assigned->push(request('trainings')[$j]);
                    }
                }

                // $quizzes_to_be_assigned = collect([]);
                // $currentQuizzes = $student->quizzes()->pluck('id')->toArray();
                // for ($k = 0; $k < count(request('quizzes')); $k++) {
                //     if (!in_array(request('quizzes')[$k], $currentQuizzes)) {
                //         $quizzes_to_be_assigned->push(request('quizzes')[$k]);
                //     }
                // }

                if (count($trainings_to_be_assigned) > 0) { // || count($quizzes_to_be_assigned) > 0
                    $order = Order::create([
                        'company_id' => request('company_id'),
                        'student_id' => request('students')[$i],
                        'type' => Order::ORDER,
                        'order_status_id' => request('order_status_id'),
                    ]);

                    $order->trainings()->sync($trainings_to_be_assigned);

                    insert_in_history_table('created', $order->id, $order->getTable());

                    return response()->json([
                        'order_id' => $order->id,
                        'message' => 'Trainings assigned to student successfully.',
                    ]);
                }
            }

            return response()->json(['message' => 'Nothing to assign.', 401]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function unassign_trainings_from_student()
    {
        try {
            request()->validate([
                'students' => 'required|array',
                'students.*' => 'required|integer|distinct|exists:users,id',
                'training_id' => 'required|integer|exists:trainings,id',
            ]);

            for ($i = 0; $i < count(request('students')); $i++) {

                $order = Order::where('student_id', request('students')[$i])->whereHas('trainings', function ($query) {
                    return $query->where('order_items.training_id', request('training_id'));
                })->first();

                if ($order) {
                    $order->trainings()->updateExistingPivot(request('training_id'), ['expires_at' => now()]);
                }
            }

            return response()->json([
                'message' => 'Trainings unassigned from students successfully.',
            ]);

        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
