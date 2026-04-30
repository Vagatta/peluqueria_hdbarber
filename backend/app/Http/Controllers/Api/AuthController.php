<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = User::create([
            'name' => strip_tags($data['name']),
            'email' => strtolower($data['email']),
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'role' => User::ROLE_CLIENT,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return response()->json(['message' => 'Credenciales inválidas'], 422);
        }

        $request->session()->regenerate();
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'ok']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'password' => ['sometimes', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (isset($data['name'])) $user->name = strip_tags($data['name']);
        if (array_key_exists('phone', $data)) $user->phone = $data['phone'];
        if (isset($data['password'])) $user->password = $data['password'];

        $user->save();
        return response()->json(['user' => $user]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Send the reset link via Laravel password broker.
        // Always respond OK to avoid leaking whether the email exists.
        PasswordBroker::sendResetLink(['email' => strtolower($request->string('email'))]);

        return response()->json([
            'message' => 'Si el correo existe, te hemos enviado un enlace para restablecer tu contraseña.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $status = PasswordBroker::reset(
            $data,
            function ($user, $password) {
                $user->forceFill(['password' => $password])->save();
            }
        );

        if ($status === PasswordBroker::PASSWORD_RESET) {
            return response()->json(['message' => 'Contraseña actualizada']);
        }

        return response()->json(['message' => __($status)], 422);
    }
}
