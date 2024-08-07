<?php

namespace App\Http\Controllers;

use App\Models\Coupon;

class CouponsController extends Controller
{
    public function index()
    {
        try {
            request()->validate([

                'title' => 'sometimes|string|min:1|max:255',
                'code' => 'sometimes|string|min:1|max:255',
                'active' => 'sometimes|boolean',
                'min_discount' => 'sometimes|integer|min:0',
                'max_discount' => 'sometimes|integer|min:0',
            ]);

            $coupons_query_builder = Coupon::latest();

            if (request('title')) {
                $coupons_query_builder->where('title', 'like', '%' . request('title') . '%');
            }

            if (request('code')) {
                $coupons_query_builder->where('code', 'like', '%' . request('code') . '%');
            }

            if (request('active') !== null) {
                $coupons_query_builder->where('active', request('active'));
            }

            // more specific filters :

            if (request('min_discount')) {
                $coupons_query_builder->where(function($query) {
                    $query->where('discount_percentage', '>=', request('min_discount'))
                        ->orWhere('discount_value', '>=', request('min_discount'));
                });
            }

            if (request('max_discount')) {
                $coupons_query_builder->where(function($query) {
                    $query->where('discount_percentage', '<=', request('max_discount'))
                        ->orWhere('discount_value', '<=', request('max_discount'));
                });
            }

            $coupons = $coupons_query_builder->paginate(config('custom_config.pagination_items'));

            return response()->json($coupons);
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
                'code' => 'required|string',
                'title' => 'required|string',
                'description' => 'required|string',
                'discount_percentage' => 'prohibited_unless:discount_value,null|required_without:discount_value|integer',
                'discount_value' => 'prohibited_unless:discount_percentage,null|required_without:discount_percentage|integer',
                'applicable_if_total_is_above' => 'required|integer|min:1',
                'max_usage' => 'required|integer|min:1',
                'active' => 'required|boolean',
                'starts_at' => 'required|date',
                'expires_at' => 'required|date|after_or_equal:start_date',
            ]);

            $coupon = Coupon::create([
                'company_id' => request('company_id'),
                'code' => request('code'),
                'title' => request('title'),
                'description' => request('description'),
                'discount_percentage' => request('discount_percentage'),
                'discount_value' => request('discount_value'),
                'applicable_if_total_is_above' => request('applicable_if_total_is_above'),
                'max_usage' => request('max_usage'),
                'active' => request('active'),
                'starts_at' => request('starts_at'),
                'expires_at' => request('expires_at'),
            ]);

            insert_in_history_table('created', $coupon->id, $coupon->getTable());

            return response()->json([
                'coupon_id' => $coupon->id,
                'message' => 'Coupon created successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            return response()->json($coupon);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            request()->validate([
                'code' => 'required|string',
                'title' => 'required|string',
                'description' => 'required|string',
                'discount_percentage' => 'prohibited_unless:discount_value,null|required_without:discount_value|integer',
                'discount_value' => 'prohibited_unless:discount_percentage,null|required_without:discount_percentage|integer',
                'applicable_if_total_is_above' => 'required|integer|min:1',
                'max_usage' => 'required|integer|min:1',
                'active' => 'required|boolean',
                'starts_at' => 'required|date',
                'expires_at' => 'required|date|after_or_equal:start_date',
            ]);

            $coupon = Coupon::findOrFail($id);

            $coupon->code = request('code');
            $coupon->title = request('title');
            $coupon->description = request('description');
            $coupon->discount_percentage = request('discount_percentage');
            $coupon->discount_value = request('discount_value');
            $coupon->applicable_if_total_is_above = request('applicable_if_total_is_above');
            $coupon->max_usage = request('max_usage');
            $coupon->active = request('active');
            $coupon->starts_at = request('starts_at');
            $coupon->expires_at = request('expires_at');

            $coupon->save();

            insert_in_history_table('updated', $coupon->id, $coupon->getTable());

            return response()->json(['message' => 'Coupon updated successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }




    public function destroy($id)
    {
        try {
            $coupon = Coupon::withCount('orders')->findOrFail($id);

            if ($coupon->orders_count > 0) {
                return response()->json([
                    'message' => 'Coupon cannot be deleted, used in ' . $coupon->orders_count . ' orders.',
                ], 401);
            }
            $coupon->delete();

            insert_in_history_table('deleted', $coupon->id, $coupon->getTable());

            return response()->json(['message' => 'Coupon deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }




}
