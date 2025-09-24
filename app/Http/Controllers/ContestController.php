<?php
namespace App\Http\Controllers;
use App\Models\Contest;
use App\Models\Campus;
use Illuminate\Http\Request;

class ContestController extends Controller
{
    // Listar todos los contests con filtros y paginación
    public function index(Request $request)
    {
        try {
            $query = Contest::with('campuses', 'classrooms');
            
            // Filtrar por status si viene el parámetro
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }
            
            // Filtrar por búsqueda si viene el parámetro query
            if ($request->has('query') && $request->input('query')) {
                $searchTerm = $request->input('query');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            // Ordenar por fecha de creación descendente
            $query->orderBy('id', 'desc');
            
            // Verificar si se requiere paginación
            if ($request->has('per_page')) {
                $perPage = min($request->input('per_page', 10), 100); // Máximo 100 por página
                $contests = $query->paginate($perPage);
                
                return response()->json([
                    'success' => true,
                    'contests' => $contests
                ]);
            } else {
                // Devolver todos sin paginación
                $contests = $query->get();
                
                return response()->json([
                    'success' => true,
                    'contests' => $contests
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error in ContestController@index: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar contests',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    
    // Mostrar un contest específico
    public function show($id)
    {
        try {
            $contest = Contest::with('campuses', 'classrooms')->findOrFail($id);
            return response()->json([
                'success' => true,
                'contest' => $contest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Contest no encontrado'
            ], 404);
        }
    }
    
    // Obtener solo el estado de un contest
    public function getStatus($id)
    {
        try {
            $contest = Contest::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $contest->id,
                    'name' => $contest->name,
                    'status' => $contest->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Contest no encontrado'
            ], 404);
        }
    }
    
    // Crear un contest
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'duration' => 'required|integer',
                'number_of_questions' => 'required|integer',
                'status' => 'integer|in:0,1',
                'campus_ids' => 'array', // array de ids de campus
                'campus_ids.*' => 'integer|exists:campuses,id',
            ]);
            
            $contest = Contest::create($validated);
            
            // Asociar campus si se proporcionan
            if (!empty($validated['campus_ids'])) {
                $contest->campuses()->sync($validated['campus_ids']);
            }
            
            return response()->json([
                'success' => true,
                'contest' => $contest->load('campuses', 'classrooms'),
                'message' => 'Contest creado exitosamente'
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating contest: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el contest'
            ], 500);
        }
    }
    
    // Actualizar un contest
    public function update(Request $request, $id)
    {
        try {
            $contest = Contest::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'sometimes|required|date',
                'duration' => 'sometimes|required|integer',
                'number_of_questions' => 'sometimes|required|integer',
                'status' => 'integer|in:0,1',
                'campus_ids' => 'array',
                'campus_ids.*' => 'integer|exists:campuses,id',
            ]);
            
            $contest->update($validated);
            
            if (isset($validated['campus_ids'])) {
                $contest->campuses()->sync($validated['campus_ids']);
            }
            
            return response()->json([
                'success' => true,
                'contest' => $contest->load('campuses', 'classrooms'),
                'message' => 'Contest actualizado exitosamente'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating contest: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el contest'
            ], 500);
        }
    }
    
    // Eliminar un contest
    public function destroy($id)
    {
        try {
            $contest = Contest::findOrFail($id);
            $contest->campuses()->detach(); // desvincular campus
            $contest->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Contest eliminado correctamente'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error deleting contest: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el contest'
            ], 500);
        }
    }
}