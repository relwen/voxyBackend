<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vocalise;
use App\Models\Chorale;
use Illuminate\Support\Facades\Storage;

class VocaliseController extends Controller
{
    /**
     * Afficher la liste des vocalises
     */
    public function index()
    {
        $vocalises = Vocalise::with('chorale')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.vocalises.index', compact('vocalises'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $chorales = Chorale::all();
        $voiceParts = ['SOPRANE', 'TENOR', 'MEZOSOPRANE', 'ALTO', 'BASSE', 'BARITON'];
        
        return view('admin.vocalises.create', compact('chorales', 'voiceParts'));
    }

    /**
     * Enregistrer une nouvelle vocalise
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'voice_part' => 'required|in:SOPRANE,TENOR,MEZOSOPRANE,ALTO,BASSE,BARITON',
            'chorale_id' => 'required|exists:chorales,id',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240', // 10MB max
        ]);

        $data = $request->except('audio_file');
        
        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('vocalises', 'public');
            $data['audio_path'] = $path;
        }

        Vocalise::create($data);

        return redirect()->route('admin.vocalises.index')->with('success', 'Vocalise créée avec succès.');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $vocalise = Vocalise::with('chorale')->findOrFail($id);
        $chorales = Chorale::all();
        $voiceParts = ['SOPRANE', 'TENOR', 'MEZOSOPRANE', 'ALTO', 'BASSE', 'BARITON'];
        
        return view('admin.vocalises.edit', compact('vocalise', 'chorales', 'voiceParts'));
    }

    /**
     * Mettre à jour une vocalise
     */
    public function update(Request $request, $id)
    {
        $vocalise = Vocalise::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'voice_part' => 'required|in:SOPRANE,TENOR,MEZOSOPRANE,ALTO,BASSE,BARITON',
            'chorale_id' => 'required|exists:chorales,id',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
        ]);

        $data = $request->except('audio_file');
        
        if ($request->hasFile('audio_file')) {
            // Supprimer l'ancien fichier s'il existe
            if ($vocalise->audio_path) {
                Storage::disk('public')->delete($vocalise->audio_path);
            }
            
            $path = $request->file('audio_file')->store('vocalises', 'public');
            $data['audio_path'] = $path;
        }

        $vocalise->update($data);

        return redirect()->route('admin.vocalises.index')->with('success', 'Vocalise mise à jour avec succès.');
    }

    /**
     * Supprimer une vocalise
     */
    public function destroy($id)
    {
        $vocalise = Vocalise::findOrFail($id);
        
        // Supprimer le fichier audio s'il existe
        if ($vocalise->audio_path) {
            Storage::disk('public')->delete($vocalise->audio_path);
        }
        
        $vocalise->delete();

        return back()->with('success', 'Vocalise supprimée avec succès.');
    }

    /**
     * Afficher les vocalises par chorale
     */
    public function byChorale($choraleId)
    {
        $chorale = Chorale::with('vocalises')->findOrFail($choraleId);
        $vocalises = $chorale->vocalises()->paginate(20);
        
        return view('admin.vocalises.by-chorale', compact('chorale', 'vocalises'));
    }
}
