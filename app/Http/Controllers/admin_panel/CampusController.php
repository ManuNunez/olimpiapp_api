<?php

namespace App\Http\Controllers\admin_panel;

use App\Models\Campus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CampusController extends Controller
{
   

    public function CreateCampus(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'ubication' => 'required|string|max:255',
        ]);

        // Create a new campus record in the database
        $campus = Campus::create($validatedData);

        // Return a response indicating success
        return response()->json([
            'message' => 'Campus created successfully.',
            'campus' => $campus,
        ], 201);
    }

    public function UpdateCampus(Request $request, $id)
    {
        // Find the campus by ID
        $campus = Campus::findOrFail($id);

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'ubication' => 'sometimes|required|string|max:255',
        ]);

        // Update the campus record with the validated data
        $campus->update($validatedData);

        // Return a response indicating success
        return response()->json([
            'message' => 'Campus updated successfully.',
            'campus' => $campus,
        ], 200);
    }

    public function DeleteCampus($id)
    {
        // Find the campus by ID
        $campus = Campus::findOrFail($id);

        // Delete the campus record
        $campus->delete();

        // Return a response indicating success
        return response()->json([
            'message' => 'Campus deleted successfully.',
        ], 200);
    }

    public function ListCampuses()
    {
        // Retrieve all campuses from the database
        $campuses = Campus::all();

        // Return a response with the list of campuses
        return response()->json([
            'campuses' => $campuses,
        ], 200);
    }

    public function GetCampus($id)
    {
        // Find the campus by ID
        $campus = Campus::findOrFail($id);

        // Return a response with the campus details
        return response()->json([
            'campus' => $campus,
        ], 200);
    }

    public function SearchCampuses(Request $request)
    {
        // Validate the search query
        $validatedData = $request->validate([
            'query' => 'required|string|max:255',
        ]);

        // Search for campuses based on the query
        $campuses = Campus::where('name', 'like', '%' . $validatedData['query'] . '%')
            ->orWhere('address', 'like', '%' . $validatedData['query'] . '%')
            ->orWhere('ubication', 'like', '%' . $validatedData['query'] . '%')
            ->get();

        // Return a response with the search results
        return response()->json([
            'campuses' => $campuses,
        ], 200);
    }
}
