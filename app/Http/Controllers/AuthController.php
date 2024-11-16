<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);

        $user->save();
        $uploadedAvatar = $this->uploadImage($request->file("avatar"));
        $user->image()->create([
            "path" => $uploadedAvatar,
        ]);

        $token = $user->createToken("register")->plainTextToken;

        return response()->json([
            'message' => 'success',
            "token" => $token,
        ], 201);
    }
    public function login(LoginRequest $request)
    {
        $user = User::where("email", $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            abort(404);
        }
        $token = $user->createToken("login")->plainTextToken;
        return response()->json([
            "message" => "success",
            "token" => $token,
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            "message" => "user logged out successfully",
        ], 200);
    }
}
