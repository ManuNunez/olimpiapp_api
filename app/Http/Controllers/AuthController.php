<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Registrar usuario
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'date_of_birth' => ['nullable', 'date'],
            'curp' => ['nullable', 'string', 'max:18'],
            'status' => ['nullable', 'integer'],
            'role_id' => ['required', 'integer', 'exists:roles,id'], // Validar que el rol exista
        ]);

        // Usar transacción para asegurar integridad de datos
        DB::beginTransaction();
        
        try {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'curp' => $validated['curp'] ?? null,
                'status' => $validated['status'] ?? 1, // Activo por defecto
            ]);

            // Crear la relación en la tabla user_roles
            DB::table('user_roles')->insert([
                'user_id' => $user->id,
                'role_id' => $validated['role_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'User registered successfully.',
                'user' => $user,
                'role_id' => $validated['role_id'],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Registration failed.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Login de usuario
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])
            ->orWhere('username', $credentials['email'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->delete(); // Opcional: revocar tokens anteriores
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Obtener usuario actual
     */
    public function me(): JsonResponse
    {
        return response()->json(Auth::user());
    }
}