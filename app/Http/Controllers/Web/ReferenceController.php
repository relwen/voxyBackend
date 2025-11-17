<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Reference;
use App\Models\Messe;
use App\Models\Partition;
use App\Models\Category;
use App\Models\Chorale;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    /**
     * Afficher la liste des références
     */
    public function index()
    {
        $references = Reference::with(['messe', 'partitions'])
            ->withCount('partitions')
            ->orderBy('messe_id')
            ->orderBy('order_position')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.references.index', compact('references'));
    }

    /**
     * Afficher le formulaire de création d'une référence
     */
    public function create()
    {
        $messes = Messe::orderBy('name')->get();
        return view('admin.references.create', compact('messes'));
    }

    /**
     * Enregistrer une nouvelle référence
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_position' => 'nullable|integer|min:1',
            'messe_id' => 'required|exists:messes,id',
        ]);

        Reference::create($request->all());

        return redirect()->route('admin.references.index')->with('success', 'Référence créée avec succès.');
    }

    /**
     * Afficher le formulaire d'édition d'une référence
     */
    public function edit($id)
    {
        $reference = Reference::with('messe')->findOrFail($id);
        $messes = Messe::orderBy('name')->get();
        return view('admin.references.edit', compact('reference', 'messes'));
    }

    /**
     * Mettre à jour une référence
     */
    public function update(Request $request, $id)
    {
        $reference = Reference::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_position' => 'nullable|integer|min:1',
            'messe_id' => 'required|exists:messes,id',
        ]);

        $reference->update($request->all());

        return redirect()->route('admin.references.index')->with('success', 'Référence mise à jour avec succès.');
    }

    /**
     * Supprimer une référence
     */
    public function destroy($id)
    {
        $reference = Reference::findOrFail($id);
        
        // Vérifier s'il y a des partitions associées
        if ($reference->partitions()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer cette référence car elle contient des partitions. Supprimez d\'abord les partitions associées.');
        }
        
        $reference->delete();

        return back()->with('success', 'Référence supprimée avec succès.');
    }

    /**
     * Obtenir les références d'une messe (pour AJAX)
     */
    public function getByMesse($messeId)
    {
        $references = Reference::where('messe_id', $messeId)
            ->ordered()
            ->get();

        return response()->json($references);
    }

    /**
     * Afficher les partitions d'une section
     */
    public function partitions($id)
    {
        $reference = Reference::with(['messe', 'partitions.chorale', 'partitions.category'])->findOrFail($id);
        $partitions = $reference->partitions()->with(['chorale', 'category'])->paginate(20);
        $categories = Category::orderBy('name')->get();
        $chorales = Chorale::orderBy('name')->get();

        return view('admin.references.partitions', compact('reference', 'partitions', 'categories', 'chorales'));
    }

    /**
     * Afficher le formulaire de création d'une partition pour une section
     */
    public function createPartition($id)
    {
        $reference = Reference::with('messe')->findOrFail($id);
        $categories = Category::orderBy('name')->get();
        $chorales = Chorale::orderBy('name')->get();

        return view('admin.references.create-partition', compact('reference', 'categories', 'chorales'));
    }

    /**
     * Enregistrer une nouvelle partition pour une section
     */
    public function storePartition(Request $request, $id)
    {
        $reference = Reference::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'chorale_id' => 'required|exists:chorales,id',
            'audio_files.*' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
            'pdf_files.*' => 'nullable|file|mimes:pdf|max:20480',
            'image_files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $data = $request->except(['audio_files', 'pdf_files', 'image_files']);
        $data['reference_id'] = $reference->id;

        // Traiter les fichiers audio multiples
        if ($request->hasFile('audio_files')) {
            $audioFiles = [];
            foreach ($request->file('audio_files') as $file) {
                $path = $file->store('partitions/audio', 'public');
                $audioFiles[] = $path;
            }
            $data['audio_files'] = $audioFiles;
        }

        // Traiter les fichiers PDF multiples
        if ($request->hasFile('pdf_files')) {
            $pdfFiles = [];
            foreach ($request->file('pdf_files') as $file) {
                $path = $file->store('partitions/pdf', 'public');
                $pdfFiles[] = $path;
            }
            $data['pdf_files'] = $pdfFiles;
        }

        // Traiter les fichiers image multiples
        if ($request->hasFile('image_files')) {
            $imageFiles = [];
            foreach ($request->file('image_files') as $file) {
                $path = $file->store('partitions/images', 'public');
                $imageFiles[] = $path;
            }
            $data['image_files'] = $imageFiles;
        }

        Partition::create($data);

        return redirect()->route('admin.references.partitions', $reference->id)->with('success', 'Partition créée avec succès.');
    }
}