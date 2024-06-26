<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            request()->validate([
                'email' => 'required|string|email',
                'password' => 'required|string|min:3|max:255',
            ]);

            $credentials = $request->only('email', 'password');

            $user = User::where('email', request('email'))->first();

            if (!$user) {
                return response()->json([
                    'error' => true,
                    'message' => trans('auth.failed'),
                ], 401);
            }
            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Email has not been verified, please verify your email.',
                ], 401);
            }
            if ($user->blocked_at) {
                return response()->json([
                    'error' => true,
                    'message' => 'User has been banned, Please contact the administrator.',
                ], 401);
            }

            $token = auth()->attempt($credentials);

            if (!$token) {
                return response()->json([
                    'error' => true,
                    'message' => trans('auth.failed'),
                ], 401);
            }

            return response()->json([
                'error' => false,
                'message' => 'Connected successfully.',
                'token' => $token,
            ]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function register()
    {
        try {
            request()->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'first_name' => 'required|string|min:1|max:255',
                'last_name' => 'required|string|min:1|max:255',
                'email' => 'required|string|email|unique:users,email',
                'password' => 'required|string|min:3|max:255',
                'image_id' => 'sometimes|integer|exists:files,id',
            ]);

            $user = User::create([
                'role_id' => ROLE::STUDENT,
                'company_id' => request('company_id'),
                'first_name' => request('first_name'),
                'last_name' => request('last_name'),
                'email' => request('email'),
                'phone_number' => request('phone_number'),
                'password' => bcrypt(request('password')),
                'image_id' => request('image_id'),
            ]);

            $user->sendEmailVerificationNotification();
            // $user->markEmailAsVerified();

            return response()->json([
                'error' => false,
                'message' => 'User created successfully, please verify your email before login.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function verify_email($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email is already verified.'], 401);
            }
            $user->markEmailAsVerified();

            return response()->json(['message' => 'Email has been verified.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function resend_verification_email()
    {
        try {
            request()->validate(['email' => 'required|string|email']);

            $user = User::where('email', request('email'))->firstOrFail();

            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email is already verified.'], 401);
            }
            $user->sendEmailVerificationNotification();

            return response()->json(['message' => 'Email verification link has been resent.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function auth_user()
    {
        try {
            $user = User::with('company', 'company.categories', 'company.tags', 'role.permissions', 'trainings_as_instructor', 'reviews', 'lessons', 'image', 'images_uploaded', 'wishlist', 'cart', 'orders.trainings', 'orders.quizzes')->find(auth()->user()->id);

            $user->role->permissions = $user->role->permissions()->where('company_id', $user->company_id)->get();

            return response()->json($user);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function send_forgot_password_email()
    {
        try {
            request()->validate(['email' => 'required|string|email']);

            $sentResetLink = Password::sendResetLink(['email' => request('email')]);

            if ($sentResetLink === Password::RESET_LINK_SENT) {
                return response()->json(['message' => 'Reset password link sent on your email.',
                ]);
            }
            return response()->json(['message' => 'An error occured, please try again later.'], 500);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function reset_password()
    {
        try {
            request()->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|string|min:3|max:255|confirmed',
            ]);

            $status = Password::reset(
                request()->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, $password) {
                    $user->forceFill([
                        'password' => bcrypt($password),
                    ]);
                    $user->save();
                }
            );

            return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Your password has been successfully reset.'])
            : response()->json(['message' => 'An error occured, please try again later.'], 500);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update_password()
    {
        try {
            request()->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:3|max:255|confirmed',
            ]);

            if (!Hash::check(request('current_password'), auth()->user()->password)) {
                return response()->json(['message' => 'Please verify your old password.'], 401);
            }

            $user = User::findOrFail(auth()->user()->id);

            $user->forceFill(['password' => bcrypt(request('new_password'))]);
            $user->save();

            return response()->json(['message' => 'Your password has been successfully reset.']);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out.']);
    }
}
