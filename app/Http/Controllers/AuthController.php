<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Str;


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
            'avatar_url' => '/images/default-avatar.png'
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

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        $token = Str::random(60);

        $user->update([
            'password_reset_token' => Hash::make($token),
        ]);

        $resetLink = url("http://192.168.1.12:5173/reset-password/update?token={$token}&email={$user->email}");

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->Port = env('MAIL_PORT');

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));

            $mail->addAddress($user->email);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->Body = "
    <div style='font-family: Arial, sans-serif; background-color: #111827; color: #d1d5db; padding: 20px; border-radius: 8px; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #4f46e5; text-align: center;'>Réinitialisation de votre mot de passe</h2>
        <p>Bonjour <strong>{$user->name}</strong>,</p>
        <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton ci-dessous pour procéder :</p>
        <div style='text-align: center; margin: 20px 0;'>
            <a href='{$resetLink}'
                style='background: linear-gradient(to right, #4f46e5, #3b82f6); color: #ffffff; padding: 12px 24px; text-decoration: none; font-weight: bold; border-radius: 8px; display: inline-block;'
                target='_blank'>
                Réinitialiser mon mot de passe
            </a>
        </div>
        <p style='font-size: 0.9rem;'>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.</p>
        <hr style='border: 0; height: 1px; background: #374151; margin: 20px 0;'>
        <footer style='text-align: center; font-size: 0.8rem; color: #9ca3af;'>
            &copy; 2024 Codovia. Tous droits réservés.
        </footer>
    </div>
";


            $mail->send();

            return response()->json(['message' => 'Reset link sent to your email.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Unable to send reset link.', 'error' => $mail->ErrorInfo], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        if (!Hash::check($request->token, $user->password_reset_token)) {
            return response()->json(['message' => 'Invalid token.'], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_reset_token' => null,
        ]);

        return response()->json(['message' => 'Password reset successfully.'], 200);
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
