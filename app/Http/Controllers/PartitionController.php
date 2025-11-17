<?php

namespace App\Http\Controllers;

use App\Models\Partition;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class PartitionController extends Controller
{
    /**
     * Display a listing of the partitions.
     */
    public function index(Request $request)
    {
        $query = Partition::with(['category', 'chorale'])->orderBy('created_at', 'desc');
        
        // Filtrer par catégorie si spécifiée
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $partitions = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $partitions->map(function ($partition) {
                return array_merge($partition->toArray(), [
                    'audio_url' => $partition->audio_url,
                    'pdf_url' => $partition->pdf_url,
                    'image_url' => $partition->image_url,
                    'category_name' => $partition->category->name ?? null,
                    'chorale_name' => $partition->chorale->name ?? null,
                ]);
            })
        ]);
    }

    /**
     * Store a newly created partition in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'chorale_id' => 'required|exists:chorales,id',
            'audio_files.*' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240', // 10MB max
            'pdf_files.*' => 'nullable|file|mimes:pdf|max:20480', // 20MB max
            'image_files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            // Support pour les anciens champs uniques
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
            'pdf_file' => 'nullable|file|mimes:pdf|max:20480',
            'image_file' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Vérifier qu'au moins un fichier est fourni
        $hasFiles = $request->hasFile('audio_file') || $request->hasFile('pdf_file') || $request->hasFile('image_file') ||
                    $request->hasFile('audio_files') || $request->hasFile('pdf_files') || $request->hasFile('image_files');
        
        if (!$hasFiles) {
            return response()->json([
                'success' => false,
                'message' => 'Au moins un fichier (audio, PDF ou image) est requis.'
            ], 422);
        }

        $data = $request->except(['audio_file', 'pdf_file', 'image_file', 'audio_files', 'pdf_files', 'image_files']);

        // Traiter les fichiers audio multiples
        if ($request->hasFile('audio_files')) {
            $audioPaths = [];
            foreach ($request->file('audio_files') as $file) {
                $path = $file->store('partitions/audio', 'public');
                $audioPaths[] = $path;
            }
            $data['audio_files'] = $audioPaths;
        }

        // Traiter les fichiers PDF multiples
        if ($request->hasFile('pdf_files')) {
            $pdfPaths = [];
            foreach ($request->file('pdf_files') as $file) {
                $path = $file->store('partitions/pdf', 'public');
                $pdfPaths[] = $path;
            }
            $data['pdf_files'] = $pdfPaths;
        }

        // Traiter les images multiples
        if ($request->hasFile('image_files')) {
            $imagePaths = [];
            foreach ($request->file('image_files') as $file) {
                $path = $file->store('partitions/images', 'public');
                $imagePaths[] = $path;
            }
            $data['image_files'] = $imagePaths;
        }

        // Support pour les anciens champs uniques (rétrocompatibilité)
        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('partitions/audio', 'public');
            $data['audio_path'] = $path;
        }

        if ($request->hasFile('pdf_file')) {
            $path = $request->file('pdf_file')->store('partitions/pdf', 'public');
            $data['pdf_path'] = $path;
        }

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('partitions/images', 'public');
            $data['image_path'] = $path;
        }

        $partition = Partition::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Partition created successfully.',
            'data' => array_merge($partition->toArray(), [
                'audio_url' => $partition->audio_url,
                'pdf_url' => $partition->pdf_url,
                'image_url' => $partition->image_url,
                'category_name' => $partition->category->name ?? null,
                'chorale_name' => $partition->chorale->name ?? null,
            ])
        ], 201);
    }

    /**
     * Display the specified partition.
     */
    public function show(Partition $partition)
    {
        return response()->json([
            'success' => true,
            'data' => array_merge($partition->toArray(), [
                'audio_url' => $partition->audio_url,
                'pdf_url' => $partition->pdf_url,
                'image_url' => $partition->image_url,
                'category_name' => $partition->category->name ?? null,
                'chorale_name' => $partition->chorale->name ?? null,
            ])
        ]);
    }

    /**
     * Update the specified partition in storage.
     */
    public function update(Request $request, Partition $partition)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'chorale_id' => 'required|exists:chorales,id',
            'audio_files.*' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
            'pdf_files.*' => 'nullable|file|mimes:pdf|max:20480',
            'image_files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
            // Support pour les anciens champs uniques
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
            'pdf_file' => 'nullable|file|mimes:pdf|max:20480',
            'image_file' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $data = $request->except(['audio_file', 'pdf_file', 'image_file', 'audio_files', 'pdf_files', 'image_files']);

        // Traiter les fichiers audio multiples
        if ($request->hasFile('audio_files')) {
            // Supprimer les anciens fichiers audio
            if ($partition->audio_files) {
                foreach ($partition->audio_files as $path) {
                    Storage::disk('public')->delete($path);
                }
            }
            
            $audioPaths = [];
            foreach ($request->file('audio_files') as $file) {
                $path = $file->store('partitions/audio', 'public');
                $audioPaths[] = $path;
            }
            $data['audio_files'] = $audioPaths;
        }

        // Traiter les fichiers PDF multiples
        if ($request->hasFile('pdf_files')) {
            // Supprimer les anciens fichiers PDF
            if ($partition->pdf_files) {
                foreach ($partition->pdf_files as $path) {
                    Storage::disk('public')->delete($path);
                }
            }
            
            $pdfPaths = [];
            foreach ($request->file('pdf_files') as $file) {
                $path = $file->store('partitions/pdf', 'public');
                $pdfPaths[] = $path;
            }
            $data['pdf_files'] = $pdfPaths;
        }

        // Traiter les images multiples
        if ($request->hasFile('image_files')) {
            // Supprimer les anciennes images
            if ($partition->image_files) {
                foreach ($partition->image_files as $path) {
                    Storage::disk('public')->delete($path);
                }
            }
            
            $imagePaths = [];
            foreach ($request->file('image_files') as $file) {
                $path = $file->store('partitions/images', 'public');
                $imagePaths[] = $path;
            }
            $data['image_files'] = $imagePaths;
        }

        // Support pour les anciens champs uniques (rétrocompatibilité)
        if ($request->hasFile('audio_file')) {
            if ($partition->audio_path) {
                Storage::disk('public')->delete($partition->audio_path);
            }
            $path = $request->file('audio_file')->store('partitions/audio', 'public');
            $data['audio_path'] = $path;
        }

        if ($request->hasFile('pdf_file')) {
            if ($partition->pdf_path) {
                Storage::disk('public')->delete($partition->pdf_path);
            }
            $path = $request->file('pdf_file')->store('partitions/pdf', 'public');
            $data['pdf_path'] = $path;
        }

        if ($request->hasFile('image_file')) {
            if ($partition->image_path) {
                Storage::disk('public')->delete($partition->image_path);
            }
            $path = $request->file('image_file')->store('partitions/images', 'public');
            $data['image_path'] = $path;
        }

        $partition->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Partition updated successfully.',
            'data' => array_merge($partition->toArray(), [
                'audio_url' => $partition->audio_url,
                'pdf_url' => $partition->pdf_url,
                'image_url' => $partition->image_url,
                'category_name' => $partition->category->name ?? null,
                'chorale_name' => $partition->chorale->name ?? null,
            ])
        ]);
    }

    /**
     * Remove the specified partition from storage.
     */
    public function destroy(Partition $partition)
    {
        // Supprimer les fichiers associés (anciens champs uniques)
        if ($partition->audio_path) {
            Storage::disk('public')->delete($partition->audio_path);
        }
        if ($partition->pdf_path) {
            Storage::disk('public')->delete($partition->pdf_path);
        }
        if ($partition->image_path) {
            Storage::disk('public')->delete($partition->image_path);
        }

        // Supprimer les fichiers multiples
        if ($partition->audio_files) {
            foreach ($partition->audio_files as $path) {
                Storage::disk('public')->delete($path);
            }
        }
        if ($partition->pdf_files) {
            foreach ($partition->pdf_files as $path) {
                Storage::disk('public')->delete($path);
            }
        }
        if ($partition->image_files) {
            foreach ($partition->image_files as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $partition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Partition deleted successfully.'
        ], 200);
    }

    /**
     * Get partitions for synchronization, optionally newer than a given timestamp.
     */
    public function getForSync(Request $request)
    {
        $lastSync = $request->get('last_sync', '1970-01-01 00:00:00');
        
        $partitions = Partition::with(['category', 'chorale'])
            ->where('updated_at', '>', $lastSync)
            ->orderBy('updated_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $partitions->map(function ($partition) {
                return array_merge($partition->toArray(), [
                    'audio_url' => $partition->audio_url,
                    'pdf_url' => $partition->pdf_url,
                    'image_url' => $partition->image_url,
                    'category_name' => $partition->category->name ?? null,
                    'chorale_name' => $partition->chorale->name ?? null,
                ]);
            }),
            'last_sync' => now()->toDateTimeString()
        ]);
    }

    /**
     * Download a specific file for a partition.
     */
    public function downloadFile($id, $type)
    {
        $partition = Partition::findOrFail($id);
        
        $path = null;
        $filename = null;

        switch ($type) {
            case 'audio':
                if (!$partition->audio_path || !Storage::disk('public')->exists($partition->audio_path)) {
                    return response()->json(['success' => false, 'message' => 'Audio file not found.'], 404);
                }
                $path = Storage::disk('public')->path($partition->audio_path);
                $filename = basename($partition->audio_path);
                break;
                
            case 'pdf':
                if (!$partition->pdf_path || !Storage::disk('public')->exists($partition->pdf_path)) {
                    return response()->json(['success' => false, 'message' => 'PDF file not found.'], 404);
                }
                $path = Storage::disk('public')->path($partition->pdf_path);
                $filename = basename($partition->pdf_path);
                break;
                
            case 'image':
                if (!$partition->image_path || !Storage::disk('public')->exists($partition->image_path)) {
                    return response()->json(['success' => false, 'message' => 'Image file not found.'], 404);
                }
                $path = Storage::disk('public')->path($partition->image_path);
                $filename = basename($partition->image_path);
                break;
                
            default:
                return response()->json(['success' => false, 'message' => 'Invalid file type.'], 400);
        }

        return Response::download($path, $filename);
    }
}