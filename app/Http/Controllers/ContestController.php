<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Campus;
use Illuminate\Http\Request;

class ContestController extends Controller
{
    // Listar todos los contests
    public function index()
    {
        $contests = Contest::with('campuses', 'classrooms')->get();
        return response()->json($contests);
    }

    // Mostrar un contest específico
    public function show($id)
    {
        $contest = Contest::with('campuses', 'classrooms')->findOrFail($id);
        return response()->json($contest);
    }
    // Dentro de ContestController
    public function getStatus($id)
    {
        $contest = Contest::findOrFail($id);
        return response()->json([
            'id' => $contest->id,
            'name' => $contest->name,
            'status' => $contest->status
        ]);
    }


    // Crear un contest
    public function store(Request $request)
    {
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

        return response()->json($contest, 201);
    }

    // Actualizar un contest
    public function update(Request $request, $id)
    {
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

        return response()->json($contest);
    }

    // Eliminar un contest
    public function destroy($id)
    {
        $contest = Contest::findOrFail($id);
        $contest->campuses()->detach(); // desvincular campus
        $contest->delete();

        return response()->json(['message' => 'Contest eliminado correctamente']);
    }
}
