<?php

namespace App\Http\Controllers\helpers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class SchoolsHelperController extends Controller
{
    /**
     * Handle GET request to find the 6 closest schools by CCT and name.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClosestSchool(Request $request)
    {
        $cct = $request->query('cct');
        if (!$cct) {
            return response()->json(['error' => 'Missing cct parameter'], 400);
        }

        // Find the 6 schools with the closest CCT and name
        $schools = DB::table('schools')
            ->select('id', 'cct', 'name')
            ->orderByRaw("ABS(CAST(cct AS SIGNED) - CAST(? AS SIGNED))", [$cct])
            ->orderBy('name')
            ->limit(6)
            ->get();

        if ($schools->isEmpty()) {
            return response()->json(['error' => 'No schools found'], 404);
        }

        return response()->json($schools);
    }

    /**
     * Get school name by CCT
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSchoolByCct(Request $request)
    {
        try {
            // Validar que se proporcione el CCT
            $request->validate([
                'cct' => 'required|string'
            ]);

            $cct = $request->query('cct');

            // Buscar la escuela por CCT exacto
            $school = DB::table('schools')
                ->select('id', 'cct', 'name')
                ->where('cct', $cct)
                ->first();

            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'Escuela no encontrada',
                    'cct' => $cct
                ], 404);
            }

            return response()->json([
                'success' => true,
                'school' => [
                    'id' => $school->id,
                    'cct' => $school->cct,
                    'name' => $school->name
                ]
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
                'message' => 'Error al buscar la escuela',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get school name by CCT (alternative method using route parameter)
     *
     * @param string $cct
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSchoolByCctParam($cct)
    {
        try {
            // Validar que el CCT no esté vacío
            if (empty($cct)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CCT es requerido'
                ], 400);
            }

            // Buscar la escuela por CCT exacto
            $school = DB::table('schools')
                ->select('id', 'cct', 'name')
                ->where('cct', $cct)
                ->first();

            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'Escuela no encontrada',
                    'cct' => $cct
                ], 404);
            }

            return response()->json([
                'success' => true,
                'school' => [
                    'id' => $school->id,
                    'cct' => $school->cct,
                    'name' => $school->name
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar la escuela',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search schools by CCT pattern (partial match)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchSchoolsByCct(Request $request)
    {
        try {
            $request->validate([
                'cct' => 'required|string|min:1'
            ]);

            $cct = $request->query('cct');

            // Buscar escuelas que contengan el CCT parcial
            $schools = DB::table('schools')
                ->select('id', 'cct', 'name')
                ->where('cct', 'LIKE', '%' . $cct . '%')
                ->orderBy('cct')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'schools' => $schools,
                'total' => $schools->count(),
                'search_term' => $cct
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
                'message' => 'Error al buscar escuelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}