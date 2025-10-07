<?php

namespace App\Http\Controllers;

use App\Models\Data;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Data::latest()->paginate(10);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:data,email',
            'phone' => 'required|min:5|max:20',
        ]);

        $data = Data::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data created successfully',
            'data' => $data,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = Data::find($id);

        if (! $data) {
            return response()->json(['success' => false, 'message' => 'Data not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = Data::find($id);

        if (! $data) {
            return response()->json(['success' => false, 'message' => 'Data not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:data,email,'.$id,
            'phone' => 'required|min:5|max:20',
        ]);

        $data->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully',
            'data' => $data,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $data = Data::find($id);

        if (! $data) {
            return response()->json(['success' => false, 'message' => 'Data not found'], 404);
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data deleted successfully',
        ]);
    }
}
