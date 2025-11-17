<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Messe;
use App\Models\Reference;
use Illuminate\Http\Request;

class MesseController extends Controller
{
    /**
     * Afficher la liste des messes
     */
    public function index()
    {
        $messes = Messe::withCount('references')
            ->orderBy('nom')
            ->paginate(20);

        return view('admin.messes.index', compact('messes'));
    }

    /**
     * Afficher les détails d'une messe
     */
    public function show($id)
    {
        $messe = Messe::with(['references.partitions'])->findOrFail($id);
        
        // Calculer les statistiques
        $totalPartitions = $messe->references->sum(function($reference) {
            return $reference->partitions->count();
        });
        
        $totalFiles = $messe->references->sum(function($reference) {
            return $reference->partitions->sum(function($partition) {
                return count($partition->audio_files ?? []) + 
                       count($partition->pdf_files ?? []) + 
                       count($partition->image_files ?? []);
            });
        });
        
        return view('admin.messes.show', compact('messe', 'totalPartitions', 'totalFiles'));
    }

    /**
     * Afficher le formulaire de création d'une messe
     */
    public function create()
    {
        return view('admin.messes.create');
    }

    /**
     * Enregistrer une nouvelle messe
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:messes,nom',
            'description' => 'nullable|string',
            'couleur' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icone' => 'nullable|string|max:255',
        ]);

        Messe::create($request->all());

        return redirect()->route('admin.messes.index')->with('success', 'Messe créée avec succès.');
    }

    /**
     * Afficher le formulaire d'édition d'une messe
     */
    public function edit($id)
    {
        $messe = Messe::findOrFail($id);
        return view('admin.messes.edit', compact('messe'));
    }

    /**
     * Mettre à jour une messe
     */
    public function update(Request $request, $id)
    {
        $messe = Messe::findOrFail($id);
        
        $request->validate([
            'nom' => 'required|string|max:255|unique:messes,nom,' . $id,
            'description' => 'nullable|string',
            'couleur' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icone' => 'nullable|string|max:255',
        ]);

        $messe->update($request->all());

        return redirect()->route('admin.messes.index')->with('success', 'Messe mise à jour avec succès.');
    }

    /**
     * Supprimer une messe
     */
    public function destroy($id)
    {
        $messe = Messe::findOrFail($id);
        
        // Vérifier s'il y a des références associées
        if ($messe->references()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer cette messe car elle contient des références. Supprimez d\'abord les références associées.');
        }
        
        $messe->delete();

        return back()->with('success', 'Messe supprimée avec succès.');
    }

    /**
     * Afficher les références d'une messe
     */
    public function references($id)
    {
        $messe = Messe::findOrFail($id);
        $references = $messe->references()->withCount('partitions')->get();

        return view('admin.messes.references', compact('messe', 'references'));
    }

    /**
     * Servir un fichier de partition
     */
    public function serveFile($partitionId, $fileType, $fileIndex)
    {
        $partition = \App\Models\Partition::findOrFail($partitionId);
        
        $files = [];
        switch($fileType) {
            case 'audio':
                $files = $partition->audio_files ?? [];
                break;
            case 'pdf':
                $files = $partition->pdf_files ?? [];
                break;
            case 'image':
                $files = $partition->image_files ?? [];
                break;
        }
        
        if (!isset($files[$fileIndex])) {
            abort(404, 'Fichier non trouvé');
        }
        
        $filePath = $files[$fileIndex];
        
        if (!\Storage::disk('public')->exists($filePath)) {
            abort(404, 'Fichier non trouvé sur le serveur');
        }
        
        return \Storage::disk('public')->response($filePath);
    }
}