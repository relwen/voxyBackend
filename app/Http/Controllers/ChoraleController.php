<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chorale;

class ChoraleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $chorales = Chorale::with('users')->get();

        return response()->json([
            'success' => true,
            'data' => $chorales
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255'
        ]);

        $chorale = Chorale::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Chorale créée avec succès',
            'chorale' => $chorale
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $chorale = Chorale::with('users', 'partitions')->findOrFail($id);

        return response()->json([
            'success' => true,
            'chorale' => $chorale
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $chorale = Chorale::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255'
        ]);

        $chorale->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Chorale mise à jour avec succès',
            'chorale' => $chorale
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $chorale = Chorale::findOrFail($id);
        $chorale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chorale supprimée avec succès'
        ]);
    }
}
