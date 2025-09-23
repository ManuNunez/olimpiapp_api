<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::query();

            // Filtros opcionales
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('username', 'like', "%{$searchTerm}%");
                });
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Validaciones
            $rules = [
                'name' => 'sometimes|required|string|max:255',
                'username' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
                'date_of_birth' => 'sometimes|nullable|date',
                'curp' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'size:18',
                    Rule::unique('users')->ignore($user->id)
                ],
                'status' => 'sometimes|required|integer|in:0,1',
                'phone' => 'sometimes|nullable|string|max:20',
                'address' => 'sometimes|nullable|string|max:500',
                'bio' => 'sometimes|nullable|string|max:1000',
                'gender' => 'sometimes|nullable|string|in:masculino,femenino,otro',
                'occupation' => 'sometimes|nullable|string|max:255',
                'website' => 'sometimes|nullable|url|max:255',
            ];

            $request->validate($rules);

            // Actualizar solo los campos presentes en la request
            $updateData = $request->only([
                'name', 'username', 'email', 'date_of_birth', 'curp', 'status',
                'phone', 'address', 'bio', 'gender', 'occupation', 'website'
            ]);

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => $user->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user from storage (desactivate instead of delete)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // No permitir eliminarse a sí mismo
            if (Auth::id() === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propio usuario'
                ], 403);
            }

            // Desactivar en lugar de borrar
            $user->update(['status' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario desactivado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore deactivated user
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            $user->update(['status' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario reactivado correctamente',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}