<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use Illuminate\Http\Request;

class ParticipationController extends Controller
{
    /**
     * Crear un nuevo participation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'contest_id' => 'required|integer|exists:contests,id',
            'classroom_id' => 'nullable|integer|exists:contest_classrooms,id',
            'score' => 'nullable|numeric',
            'level' => 'nullable|integer',
            'participation_code' => 'nullable|string|max:255',
            'campus_id' => 'required|integer|exists:campuses,id',
        ]);

        $participation = Participation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Participation creado correctamente',
            'data' => $participation
        ]);
    }

    /**
     * Listar todos los participations de un contest
     */
    public function getByContest($contestId)
    {
        $participations = Participation::with(['student', 'campus'])
            ->where('contest_id', $contestId)
            ->get();

        return response()->json($participations);
    }

    /**
     * Listar todos los participations de un contest y campus
     */
    public function getByContestAndCampus($contestId, $campusId)
    {
        $participations = Participation::with(['student', 'campus'])
            ->where('contest_id', $contestId)
            ->where('campus_id', $campusId)
            ->get();

        return response()->json($participations);
    }

    /**
     * Obtener participations de un estudiante
     */
    public function getByStudent($studentId)
    {
        $participations = Participation::with(['contest', 'campus'])
            ->where('student_id', $studentId)
            ->get();

        return response()->json($participations);
    }

    /**
     * Verificar si un estudiante está inscrito en un contest
     */
    public function isEnrolled($contestId, $studentId)
    {
        $exists = Participation::where('contest_id', $contestId)
            ->where('student_id', $studentId)
            ->exists();

        return response()->json([
            'contest_id' => $contestId,
            'student_id' => $studentId,
            'is_enrolled' => $exists
        ]);
    }
    /**
     * Actualizar un participation
     */
    public function update(Request $request, $id)
    {
        $participation = Participation::findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'sometimes|integer|exists:students,id',
            'contest_id' => 'sometimes|integer|exists:contests,id',
            'classroom_id' => 'nullable|integer|exists:contest_classrooms,id',
            'score' => 'nullable|numeric',
            'level' => 'nullable|integer',
            'participation_code' => 'nullable|string|max:255',
            'campus_id' => 'sometimes|integer|exists:campuses,id',
        ]);

        $participation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Participation actualizado correctamente',
            'data' => $participation
        ]);
    }
}
