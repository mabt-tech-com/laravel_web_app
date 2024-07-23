<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Training;
use App\Models\User;

class UsersController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('viewAny', User::class);

            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
            ]);

            $users_query_builder = User::where('company_id', request('company_id'))
                ->with('role.permissions', 'image', 'lessons', 'trainings_as_instructor')
                ->latest();

            if (request('role_id')) {
                $users_query_builder = $users_query_builder->where('role_id', request('role_id'));
            }

            $users = $users_query_builder->paginate(config('custom_config.pagination_items'));

            return response()->json($users);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::with('role.permissions', 'image', 'lessons', 'trainings_as_instructor')->findOrFail($id);

            $this->authorize('view', $user);

            return response()->json($user);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            request()->validate([
                'role_id' => 'required|integer|exists:roles,id',
                'first_name' => 'required|string|min:1|max:255',
                'last_name' => 'required|string|min:1|max:255',
                'phone_number' => 'nullable|string|min:1',
                'password' => 'nullable|string|confirmed|min:6',
                'image_id' => 'nullable|integer|exists:files,id',
                'birthday' => 'nullable|date|before:today',
                'is_blocked' => 'nullable|boolean',
            ]);

            $user = User::findOrFail($id);

            $this->authorize('update', $user);

            $user->role_id = request('role_id');
            $user->first_name = request('first_name');
            $user->last_name = request('last_name');
            $user->phone_number = request('phone_number');
            // $user->password = bcrypt(request('password'));
            $user->birthday = request('birthday');
            $user->bio = request('bio');
            $user->image_id = request('image_id');

            if (request('is_blocked') === true) {
                $user->blocked_at = now();
            } elseif (request('is_blocked') === false) {
                $user->blocked_at = null;
            }

            $user->save();

            return response()->json(['message' => 'User updated successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::with('image', 'images_uploaded')->findOrFail($id);

            $this->authorize('delete', $user);

            $user->trainings_as_instructor()->delete();
            $user->orders()->delete();
            $user->reviews()->delete();
            $user->delete_image();
            $user->delete_uploaded_images();

            $user->delete();

            return response()->json(['message' => 'User deleted successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function trainings_by_instructor($instructor_id)
    {
        try {
            $trainings = Training::where('instructor_id', $instructor_id)
                ->with('trainings_as_instructor.categories', 'trainings_as_instructor.tags', 'trainings_as_instructor.instructor', 'trainings_as_instructor.students', 'trainings_as_instructor.chapters.lessons', 'trainings_as_instructor.reviews', 'trainings_as_instructor.image', 'trainings_as_instructor.video')
                ->get();

            return response()->json($trainings);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function trainings_by_student($id)
    {
        try {
            $orders = User::findOrFail($id)->orders()->with('trainings.instructor', 'trainings.reviews', 'trainings.chapters.lessons')->where('orders.student_id', $id)->where('type', Order::ORDER)->get();

            $trainings = (new Order())->trainings_from_orders($orders);

            return response()->json($trainings);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function quizzes_by_student($id)
    {
        try {
            $orders = User::findOrFail($id)->orders()->with('quizzes.training', 'quizzes.quiz_questions.quiz_question_type', 'quizzes.quiz_questions.quiz_question_options')->where('orders.student_id', $id)->where('type', Order::ORDER)->get();

            $quizzes = (new Order())->quizzes_from_orders($orders);

            return response()->json($quizzes);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function cart($student_id, $type)
    {
        try {
            $orders = Order::where('student_id', $student_id)
                ->filterType($type)
                ->with('order_status', 'trainings.instructor', 'trainings.image', 'trainings.video', 'quizzes')
                ->latest()
                ->first();

            return response()->json($orders);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function orders($student_id)
    {
        try {
            $orders = Order::where('student_id', $student_id)
                ->filterType(Order::ORDER)
                ->with('order_status', 'trainings.instructor', 'trainings.image', 'trainings.video', 'quizzes')
                ->get();

            return response()->json($orders);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
