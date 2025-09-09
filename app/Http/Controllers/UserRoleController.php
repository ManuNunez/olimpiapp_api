<?php

namespace App\Http\Controllers;

use App\Models\UserRole;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserRoleController extends Controller
{
    /**
     * Obtener todos los roles de un usuario
     * 
     * @param int $userId
     * @return JsonResponse
     */
    public function getUserRoles($userId): JsonResponse
    {
        try {
            // Verificar si el usuario existe
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Obtener roles del usuario con información completa del rol
            $userRoles = UserRole::where('user_id', $userId)
                ->with('role')
                ->get();

            // Extraer solo la información del rol
            $roles = $userRoles->map(function ($userRole) {
                return [
                    'id' => $userRole->role->id,
                    'name' => $userRole->role->name ?? null,
                    // Agrega más campos del rol según tu estructura
                ];
            });

            return response()->json([
                'success' => true,
                'user_id' => $userId,
                'roles' => $roles,
                'total_roles' => $roles->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los roles del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si un usuario tiene un rol específico
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkUserHasRole(Request $request): JsonResponse
    {
        try {
            // Validar los parámetros de entrada
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'role_id' => 'required|integer|exists:roles,id'
            ]);

            $userId = $request->input('user_id');
            $roleId = $request->input('role_id');

            // Verificar si existe la relación usuario-rol
            $userRole = UserRole::where('user_id', $userId)
                ->where('role_id', $roleId)
                ->first();

            $hasRole = !is_null($userRole);

            // Obtener información adicional si existe la relación
            $roleInfo = null;
            if ($hasRole) {
                $role = Role::find($roleId);
                $roleInfo = [
                    'id' => $role->id,
                    'name' => $role->name ?? null,
                    // Agrega más campos según tu estructura
                ];
            }

            return response()->json([
                'success' => true,
                'user_id' => $userId,
                'role_id' => $roleId,
                'has_role' => $hasRole,
                'role_info' => $roleInfo
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el rol del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método alternativo para verificar rol por parámetros de URL
     * 
     * @param int $userId
     * @param int $roleId
     * @return JsonResponse
     */
    public function checkUserHasRoleByParams($userId, $roleId): JsonResponse
    {
        try {
            // Verificar si el usuario existe
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Verificar si el rol existe
            $role = Role::find($roleId);
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            // Verificar si existe la relación usuario-rol
            $userRole = UserRole::where('user_id', $userId)
                ->where('role_id', $roleId)
                ->first();

            $hasRole = !is_null($userRole);

            return response()->json([
                'success' => true,
                'user_id' => $userId,
                'role_id' => $roleId,
                'has_role' => $hasRole,
                'user_name' => $user->name ?? null,
                'role_name' => $role->name ?? null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el rol del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}