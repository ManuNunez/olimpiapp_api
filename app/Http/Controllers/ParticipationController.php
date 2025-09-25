<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use App\Models\Classroom;
use Illuminate\Http\Request;


class ParticipationController extends Controller
{
    /**
     * Crear un nuevo participation
     */

    public function store(Request $request)
    {
        try {
            // 1. Validar datos
            $validated = $request->validate([
                'student_id' => 'required|integer|exists:students,id',
                'contest_id' => 'required|integer|exists:contests,id',
                'campus_id'  => 'required|integer|exists:campuses,id',
                'level'      => 'nullable|integer',
            ]);

            // 2. Obtener salones (classrooms) del campus
            $classrooms = Classroom::where('campus_id', $validated['campus_id'])
                ->orderBy('id')
                ->get();

            if ($classrooms->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay salones disponibles para este campus'
                ], 422);
            }

            $totalClassrooms = $classrooms->count();

            // 3. Contar inscritos en este contest + campus
            $count = Participation::where('contest_id', $validated['contest_id'])
                ->where('campus_id', $validated['campus_id'])
                ->count();

            // 4. Calcular classroom_id según lógica de pares
            $capacityPerClassroom = 30;
            $capacityPerPair = $capacityPerClassroom * 2; // 60

            $pairIndex = intdiv($count, $capacityPerPair); // qué par toca
            $posInPair = $count % $capacityPerPair;        // posición dentro del par

            if ($totalClassrooms % 2 == 0 || $pairIndex < floor($totalClassrooms / 2)) {
                // Salones en pares normales
                $firstInPair = $pairIndex * 2;
                if ($posInPair < $capacityPerClassroom) {
                    $classroom = $classrooms[$firstInPair];
                } else {
                    $classroom = $classrooms[$firstInPair + 1];
                }
            } else {
                // Número impar de salones: sobrantes van al último salón
                $classroom = $classrooms->last();
            }

            // 5. Generar participation_code usando el nombre del aula
            $participationNumber = Participation::where('contest_id', $validated['contest_id'])->count() + 1;
            $roomNumber = $classroom->name; // ahora usamos el nombre de aula
            $participationCode = $validated['campus_id'] . '_' . $roomNumber . '_' . ($validated['level'] ?? 0) . '_' . $participationNumber;

            // 6. Crear participación
            $participation = Participation::create([
                'student_id'         => $validated['student_id'],
                'contest_id'         => $validated['contest_id'],
                'campus_id'          => $validated['campus_id'],
                'level'              => $validated['level'],
                'classroom_id'       => $classroom->id,      // guardamos el id real
                'participation_code' => $participationCode, // código con número de aula
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Participation creado correctamente',
                'data'    => $participation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
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
