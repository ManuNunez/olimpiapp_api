<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Obtiene el estudiante del usuario autenticado o retorna null si no existe
     */
    public function getStudentByUser(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $student = Student::where('user_id', $user->id)->first();

            if ($student) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estudiante encontrado',
                    'data' => $student->load(['user', 'school'])
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No tienes un perfil de estudiante asignado',
                'data' => null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estudiante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea un nuevo estudiante para el usuario autenticado
     */
    public function createStudent(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Verificar si el usuario ya tiene un estudiante asignado
            $existingStudent = Student::where('user_id', $user->id)->first();
            
            if ($existingStudent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes un perfil de estudiante creado. Solo puedes modificar el existente.',
                    'data' => $existingStudent->load(['user', 'school'])
                ], 400);
            }

            // Validar los datos de entrada
            $validator = Validator::make($request->all(), [
                'school_id' => 'required|integer|exists:schools,id',
                'code' => 'required|string|max:255|unique:students,code'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Crear el nuevo estudiante
            $student = Student::create([
                'user_id' => $user->id,
                'school_id' => $request->school_id,
                'code' => $request->code
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perfil de estudiante creado exitosamente',
                'data' => $student->load(['user', 'school'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el estudiante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza el estudiante del usuario autenticado
     */
    public function updateStudent(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Buscar el estudiante del usuario
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un perfil de estudiante para actualizar. Debes crear uno primero.'
                ], 404);
            }

            // Validar los datos de entrada
            $validator = Validator::make($request->all(), [
                'school_id' => 'sometimes|required|integer|exists:schools,id',
                'code' => 'sometimes|required|string|max:255|unique:students,code,' . $student->id
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar solo los campos proporcionados
            $updateData = [];
            if ($request->has('school_id')) {
                $updateData['school_id'] = $request->school_id;
            }
            if ($request->has('code')) {
                $updateData['code'] = $request->code;
            }

            if (!empty($updateData)) {
                $student->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Perfil de estudiante actualizado exitosamente',
                'data' => $student->fresh()->load(['user', 'school'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estudiante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método combinado que maneja la lógica completa:
     * - Si existe estudiante, lo retorna
     * - Si no existe, permite crear uno nuevo
     * - Si ya existe, no permite crear pero sí actualizar
     */
    public function handleStudent(Request $request): JsonResponse
    {
        $method = $request->method();
        
        switch ($method) {
            case 'GET':
                return $this->getStudentByUser();
            
            case 'POST':
                return $this->createStudent($request);
            
            case 'PUT':
            case 'PATCH':
                return $this->updateStudent($request);
            
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Método HTTP no permitido'
                ], 405);
        }
    }
}