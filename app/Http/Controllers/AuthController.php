<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function login(Request $request){

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('authToken')->accessToken;
        return response()->json(['token' => $token], 200);

    }

    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        $token = $user->createToken('authToken')->accessToken;

        return response()->json(['token' => $token], 201);
    }

    public function getPermissions(){
        if (!auth('api')->check()){
            return response()->json(['error' => 'Not Authenticated.'], 401);
        }

        $permissions = auth('api')->user()->getAllPermissions()->pluck('name');

        return response()->json([
            'success' => true,
            'data' => $permissions,
            'message' => 'User permissions retrieved successfully.'
        ], 200);
    }

    public function refreshToken(Request $request){
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        return response()->json(['token' => 'new_access_token'], 200);
    }

    public function getUserDetails(){
        $user = auth('api')->user();

        if (!$user){
            return response()->json(['message' => 'Not Authenticated.'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User details retrieved succcessfully.'
        ], 200);
    }

    public function logout(Request $request){
        $user = auth('api')->user();

        if ($user){
            $user->token()->revoke();
            return response()->json(['message' => 'User successfully signed out.'], 200);
        }
        return response()->json(['message' => 'User not found.'], 404);
    }

    public function sendPasswordResetLink(Request $request){
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent to your email.'], 200)
            : response()->json(['message' => 'Unable to send reset link.'], 500);
    }

    public function resetPassword(Request $request){
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->tokens()->delete();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successfully.'], 200)
            : response()->json(['message' => 'Unable to reset password.'], 500);
    }

    public function updateProfile(Request $request){
        $user = auth('api')->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (isset($data['password'])){
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user
        ], 200);
    }

    public function sendEmailVerification(Request $request){
        if ($request->user()->hasVerifiedEmail()){
            return response()->json(['message' => 'Email already verified.'], 422);
        }
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Email verification link sent.'], 200);
    }

    public function verifyEmail(Request $request){
        if ($request->user()->hasVerifiedEmail()){
            return response()->json(['message' => 'Email already verified.'], 422);
        }

        $request->user()->markEmailAsVerified();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }

}
