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
}