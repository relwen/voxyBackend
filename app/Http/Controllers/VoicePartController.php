<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoicePart;
use Illuminate\Support\Facades\Storage;

class VoicePartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $voiceParts = VoicePart::with('partition')->get();

        return response()->json([
            'success' => true,
            'voice_parts' => $voiceParts
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
            'partition_id' => 'required|exists:partitions,id'
        ]);

        $voicePart = VoicePart::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Partie vocale créée avec succès',
            'voice_part' => $voicePart
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $voicePart = VoicePart::with('partition')->findOrFail($id);

        return response()->json([
            'success' => true,
            'voice_part' => $voicePart
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
        $voicePart = VoicePart::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'partition_id' => 'required|exists:partitions,id'
        ]);

        $voicePart->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Partie vocale mise à jour avec succès',
            'voice_part' => $voicePart
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $voicePart = VoicePart::findOrFail($id);
        
        // Supprimer les fichiers associés
        if ($voicePart->pdf_path) {
            Storage::disk('public')->delete($voicePart->pdf_path);
        }
        if ($voicePart->audio_path) {
            Storage::disk('public')->delete($voicePart->audio_path);
        }
        
        $voicePart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Partie vocale supprimée avec succès'
        ]);
    }

    /**
     * Mettre à jour la partition voix d'une partie vocale
     */
    public function updatePartitionVoix(Request $request, string $id)
    {
        $voicePart = VoicePart::findOrFail($id);

        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240'
        ]);

        // Supprimer l'ancien fichier s'il existe
        if ($voicePart->pdf_path) {
            Storage::disk('public')->delete($voicePart->pdf_path);
        }

        $pdfPath = $request->file('pdf_file')->store('voice_parts', 'public');
        $voicePart->update(['pdf_path' => $pdfPath]);

        return response()->json([
            'success' => true,
            'message' => 'Partition voix mise à jour avec succès',
            'voice_part' => $voicePart
        ]);
    }

    /**
     * Mettre à jour la partition musique d'une partie vocale
     */
    public function updatePartitionMusique(Request $request, string $id)
    {
        $voicePart = VoicePart::findOrFail($id);

        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240'
        ]);

        // Supprimer l'ancien fichier s'il existe
        if ($voicePart->pdf_path) {
            Storage::disk('public')->delete($voicePart->pdf_path);
        }

        $pdfPath = $request->file('pdf_file')->store('voice_parts', 'public');
        $voicePart->update(['pdf_path' => $pdfPath]);

        return response()->json([
            'success' => true,
            'message' => 'Partition musique mise à jour avec succès',
            'voice_part' => $voicePart
        ]);
    }

    /**
     * Uploader un fichier audio pour une partie vocale
     */
    public function uploadAudio(Request $request, string $id)
    {
        $voicePart = VoicePart::findOrFail($id);

        $request->validate([
            'audio_file' => 'required|file|mimes:mp3,wav,ogg|max:20480'
        ]);

        // Supprimer l'ancien fichier s'il existe
        if ($voicePart->audio_path) {
            Storage::disk('public')->delete($voicePart->audio_path);
        }

        $audioPath = $request->file('audio_file')->store('voice_parts_audio', 'public');
        $voicePart->update(['audio_path' => $audioPath]);

        return response()->json([
            'success' => true,
            'message' => 'Fichier audio uploadé avec succès',
            'voice_part' => $voicePart
        ]);
    }
}
