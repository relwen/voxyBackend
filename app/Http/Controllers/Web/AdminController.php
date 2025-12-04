<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Chorale;
use App\Models\Partition;
use App\Models\Category;
use App\Models\RubriqueSection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Helpers\FileHelper;

class AdminController extends Controller
{
    /**
     * Dashboard principal
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'approved_users' => User::where('status', 'approved')->count(),
            'total_chorales' => Chorale::count(),
            'total_partitions' => Partition::count()
        ];

        $recentUsers = User::with('chorale')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUsers'));
    }

    /**
     * Gestion des utilisateurs
     */
    public function users()
    {
        $users = User::with('chorale')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Gestion des chorales
     */
    public function chorales()
    {
        $chorales = Chorale::with('users')
            ->withCount('users')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.chorales', compact('chorales'));
    }

    /**
     * Gestion des partitions
     */
    public function partitions()
    {
        $partitions = Partition::with(['chorale', 'category', 'reference'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.partitions', compact('partitions'));
    }

    /**
     * Afficher les détails d'une partition
     */
    public function showPartition($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            if (request()->expectsJson() || request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé. Vous devez être connecté.'
                ], 401);
            }
            return redirect('/login');
        }
        
        $partition = Partition::with(['chorale', 'category', 'reference', 'pupitre'])->findOrFail($id);
        
        // Vérifier que l'utilisateur a accès à cette partition
        // Si c'est un maestro, vérifier que la partition appartient à sa chorale
        if ($user->role === 'maestro' && $user->chorale_id && $partition->chorale_id !== $user->chorale_id) {
            if (request()->expectsJson() || request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé. Cette partition n\'appartient pas à votre chorale.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Accès refusé. Cette partition n\'appartient pas à votre chorale.');
        }
        
        return view('admin.partitions.show', compact('partition'));
    }





    /**
     * Afficher le formulaire de création d'une chorale
     */
    public function createChorale()
    {
        return view('admin.chorales.create');
    }

    /**
     * Enregistrer une nouvelle chorale
     */
    public function storeChorale(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
        ]);

        Chorale::create($request->all());

        return redirect()->route('admin.chorales')->with('success', 'Chorale créée avec succès.');
    }

    /**
     * Afficher le formulaire d'édition d'une chorale
     */
    public function editChorale($id)
    {
        $chorale = Chorale::findOrFail($id);
        return view('admin.chorales.edit', compact('chorale'));
    }

    /**
     * Mettre à jour une chorale
     */
    public function updateChorale(Request $request, $id)
    {
        $chorale = Chorale::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
        ]);

        $chorale->update($request->all());

        return redirect()->route('admin.chorales')->with('success', 'Chorale mise à jour avec succès.');
    }

    /**
     * Supprimer une chorale
     */
    public function deleteChorale($id)
    {
        $chorale = Chorale::findOrFail($id);
        $chorale->delete();

        return back()->with('success', 'Chorale supprimée avec succès.');
    }

    /**
     * Afficher le formulaire de création d'une partition
     */
    public function createPartition()
    {
        $chorales = Chorale::all();
        // Récupérer les catégories globales et celles de la chorale de l'utilisateur
        $userChorale = Auth::user()->chorale;
        $categories = Category::where(function($query) use ($userChorale) {
            $query->whereNull('chorale_id')
                  ->orWhere('chorale_id', $userChorale?->id);
        })->orderBy('name')->get();
        $messes = \App\Models\Messe::orderBy('nom')->get();
        
        // Récupérer les pupitres de toutes les chorales (ou seulement de la chorale de l'utilisateur)
        $pupitres = collect();
        if ($userChorale) {
            $pupitres = $userChorale->pupitres;
        } else {
            // Si admin, récupérer tous les pupitres
            $pupitres = \App\Models\ChoralePupitre::with('chorale')->ordered()->get();
        }
        
        return view('admin.partitions.create', compact('chorales', 'categories', 'messes', 'pupitres'));
    }

    /**
     * Enregistrer une nouvelle partition
     */
    public function storePartition(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'reference_id' => 'nullable|exists:references,id',
            'messe_id' => 'nullable|exists:messes,id',
            'chorale_id' => 'required|exists:chorales,id',
            'pupitre_id' => 'nullable|exists:chorale_pupitres,id',
            'files.*' => 'nullable|file|max:20480', // 20MB max pour tous les types
        ]);

        // Si aucun pupitre n'est sélectionné, utiliser le pupitre par défaut de la chorale
        if (!$request->has('pupitre_id') || empty($request->pupitre_id)) {
            $chorale = Chorale::findOrFail($request->chorale_id);
            $defaultPupitre = $chorale->getDefaultPupitre();
            if ($defaultPupitre) {
                $request->merge(['pupitre_id' => $defaultPupitre->id]);
            }
        }

        $data = $request->except(['files']);

        // Préparer les données pour le nommage des fichiers
        $messeNom = null;
        $partie = null;
        $subPartie = null;
        $pupitreNom = null;

        // Récupérer le nom de la messe si rubrique_section_id est fourni
        if ($request->has('messe_id') && !empty($request->messe_id)) {
            $data['rubrique_section_id'] = $request->messe_id;
            $rubriqueSection = RubriqueSection::find($request->messe_id);
            if ($rubriqueSection) {
                $messeNom = $rubriqueSection->nom;
            }
        }

        // Récupérer la partie si messe_part est fourni
        if ($request->has('messe_part')) {
            $messePart = is_array($request->messe_part) ? $request->messe_part : json_decode($request->messe_part, true);
            if ($messePart) {
                $partie = $messePart['part'] ?? null;
                $subPartie = $messePart['subPart'] ?? null;
            }
        }

        // Récupérer le nom du pupitre
        if ($request->has('pupitre_id') && !empty($request->pupitre_id)) {
            $pupitre = \App\Models\ChoralePupitre::find($request->pupitre_id);
            if ($pupitre) {
                $pupitreNom = $pupitre->nom;
            }
        }

        // Traiter tous les fichiers de manière unifiée avec nommage personnalisé
        if ($request->hasFile('files')) {
            $filePaths = [];
            foreach ($request->file('files') as $file) {
                // Générer le nom de fichier personnalisé
                $customFileName = FileHelper::generatePartitionFileName(
                    $file,
                    null,
                    $messeNom,
                    $partie,
                    $subPartie,
                    $pupitreNom
                );
                
                // Déterminer le chemin de stockage selon le type de fichier
                $storagePath = FileHelper::getStoragePath($file->getClientOriginalName());
                
                // Stocker le fichier avec le nom personnalisé
                $path = $file->storeAs($storagePath, $customFileName, 'public');
                $filePaths[] = $path;
            }
            $data['files'] = $filePaths;
        }

        try {
            Partition::create($data);
            return redirect()->route('admin.partitions')->with('success', 'Partition créée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la partition: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Erreur lors de la création de la partition: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'édition d'une partition
     */
    public function editPartition($id)
    {
        $user = Auth::user();
        $partition = Partition::with(['chorale', 'category', 'reference', 'pupitre'])->findOrFail($id);
        
        // Vérifier que l'utilisateur a accès à cette partition
        // Si c'est un maestro, vérifier que la partition appartient à sa chorale
        if ($user->role === 'maestro' && $user->chorale_id && $partition->chorale_id !== $user->chorale_id) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé. Cette partition n\'appartient pas à votre chorale.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Accès refusé. Cette partition n\'appartient pas à votre chorale.');
        }
        
        // Initialiser $messes par défaut
        $messes = collect();
        
        // Pour les maestros, limiter aux données de leur chorale
        if ($user->role === 'maestro' && $user->chorale_id) {
            $chorales = Chorale::where('id', $user->chorale_id)->get();
            $categories = Category::where('chorale_id', $user->chorale_id)->get();
            $pupitres = $user->chorale->pupitres;
            // Récupérer les messes de la chorale (sections de la rubrique "Messes")
            $messesRubrique = Category::where('chorale_id', $user->chorale_id)
                ->where('name', 'Messes')
                ->first();
            if ($messesRubrique) {
                $messes = $messesRubrique->directSections()->orderBy('nom')->get();
            }
        } else {
            $chorales = Chorale::all();
            $categories = Category::all();
            $pupitres = \App\Models\ChoralePupitre::all();
            // Récupérer toutes les messes (sections de toutes les rubriques "Messes")
            $messesRubriques = Category::where('name', 'Messes')->get();
            foreach ($messesRubriques as $rubrique) {
                $messes = $messes->merge($rubrique->directSections()->orderBy('nom')->get());
            }
        }
        
        return view('admin.partitions.edit', compact('partition', 'chorales', 'categories', 'pupitres', 'messes'));
    }

    /**
     * Mettre à jour une partition
     */
    public function updatePartition(Request $request, $id)
    {
        $user = Auth::user();
        $partition = Partition::findOrFail($id);
        
        // Vérifier que l'utilisateur a accès à cette partition
        // Si c'est un maestro, vérifier que la partition appartient à sa chorale
        if ($user->role === 'maestro' && $user->chorale_id && $partition->chorale_id !== $user->chorale_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès refusé. Cette partition n\'appartient pas à votre chorale.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Accès refusé. Cette partition n\'appartient pas à votre chorale.');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'reference_id' => 'nullable|exists:references,id',
            'messe_id' => 'nullable|exists:rubrique_sections,id', // Les messes sont maintenant des RubriqueSection
            'chorale_id' => 'required|exists:chorales,id',
            'pupitre_id' => 'nullable|exists:chorale_pupitres,id',
            'files.*' => 'nullable|file|max:20480',
            'remove_files' => 'nullable|array', // IDs des fichiers à supprimer
        ]);

        // Si aucun pupitre n'est sélectionné, utiliser le pupitre par défaut de la chorale
        if (!$request->has('pupitre_id') || empty($request->pupitre_id)) {
            $chorale = Chorale::findOrFail($request->chorale_id);
            $defaultPupitre = $chorale->getDefaultPupitre();
            if ($defaultPupitre) {
                $request->merge(['pupitre_id' => $defaultPupitre->id]);
            }
        }

        $data = $request->except(['files', 'remove_files']);
        
        // Si messe_id est fourni, le mapper vers rubrique_section_id
        if ($request->has('messe_id') && !empty($request->messe_id)) {
            $data['rubrique_section_id'] = $request->messe_id;
            unset($data['messe_id']); // Retirer messe_id car on utilise rubrique_section_id
        }

        // Gérer la suppression de fichiers
        if ($request->has('remove_files') && $partition->files) {
            $filesToRemove = $request->input('remove_files');
            $currentFiles = $partition->files ?? [];
            
            foreach ($filesToRemove as $index) {
                if (isset($currentFiles[$index])) {
                    Storage::disk('public')->delete($currentFiles[$index]);
                    unset($currentFiles[$index]);
                }
            }
            
            $data['files'] = array_values($currentFiles); // Réindexer le tableau
        }

        // Ajouter de nouveaux fichiers avec nommage personnalisé
        if ($request->hasFile('files')) {
            // Charger les relations nécessaires pour le nommage
            $partition->load(['rubriqueSection', 'pupitre']);
            
            // Mettre à jour les données de la partition pour le nommage
            $partition->fill($data);
            
            // Récupérer les fichiers existants pour éviter les doublons
            $existingFiles = $data['files'] ?? $partition->files ?? [];
            
            $newFilePaths = [];
            foreach ($request->file('files') as $file) {
                // Générer le nom de fichier personnalisé basé sur la partition
                $customFileName = FileHelper::generatePartitionFileName($file, $partition);
                
                // Déterminer le chemin de stockage selon le type de fichier
                $storagePath = FileHelper::getStoragePath($file->getClientOriginalName());
                
                // S'assurer que le nom de fichier est unique
                $uniqueFileName = FileHelper::ensureUniqueFileName($customFileName, array_merge($existingFiles, $newFilePaths), $storagePath);
                
                // Stocker le fichier avec le nom personnalisé unique
                $path = $file->storeAs($storagePath, $uniqueFileName, 'public');
                
                // Vérifier que le fichier n'est pas déjà dans la liste (éviter les doublons)
                if (!in_array($path, array_merge($existingFiles, $newFilePaths))) {
                    $newFilePaths[] = $path;
                }
            }
            
            // Fusionner avec les fichiers existants (sans doublons)
            $data['files'] = array_merge($existingFiles, $newFilePaths);
        }

        $partition->update($data);

        return redirect()->route('admin.partitions')->with('success', 'Partition mise à jour avec succès.');
    }

    /**
     * Supprimer une partition
     */
    public function deletePartition($id)
    {
        $partition = Partition::findOrFail($id);
        
        // Supprimer tous les fichiers associés (nouveau système unifié)
        if ($partition->files) {
            foreach ($partition->files as $item) {
                // Gérer le cas où $item est un tableau ou une chaîne
                $path = is_array($item) ? ($item['path'] ?? $item['name'] ?? '') : $item;
                if (!empty($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }
        
        // Supprimer aussi les anciens fichiers pour rétrocompatibilité
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

        return back()->with('success', 'Partition supprimée avec succès.');
    }

    /**
     * Gestion des catégories
     */
    public function categories()
    {
        $categories = \App\Models\Category::withCount('partitions')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.categories', compact('categories'));
    }

    /**
     * Afficher le formulaire de création d'une catégorie
     */
    public function createCategory()
    {
        return view('admin.categories.create');
    }

    /**
     * Enregistrer une nouvelle catégorie
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:255',
        ]);

        \App\Models\Category::create($request->all());

        return redirect()->route('admin.categories')->with('success', 'Catégorie créée avec succès.');
    }

    /**
     * Afficher le formulaire d'édition d'une catégorie
     */
    public function editCategory($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Mettre à jour une catégorie
     */
    public function updateCategory(Request $request, $id)
    {
        $category = \App\Models\Category::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:255',
        ]);

        $category->update($request->all());

        return redirect()->route('admin.categories')->with('success', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Supprimer une catégorie
     */
    public function deleteCategory($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        
        // Vérifier s'il y a des partitions associées
        if ($category->partitions()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer cette catégorie car elle contient des partitions. Supprimez d\'abord les partitions associées.');
        }
        
        $category->delete();

        return back()->with('success', 'Catégorie supprimée avec succès.');
    }

    /**
     * Afficher le formulaire de création d'un utilisateur
     */
    public function createUser()
    {
        $chorales = Chorale::orderBy('name')->get();
        return view('admin.users.create', compact('chorales'));
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,moderator,admin',
            'chorale_id' => 'nullable|exists:chorales,id',
            'is_approved' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data = $request->except(['password_confirmation']);
        $data['password'] = Hash::make($request->password);
        $data['is_approved'] = $request->has('is_approved');
        $data['is_active'] = $request->has('is_active');

        User::create($data);

        return redirect()->route('admin.users')->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Afficher le formulaire d'édition d'un utilisateur
     */
    public function editUser($id)
    {
        $user = User::with('chorale')->findOrFail($id);
        $chorales = Chorale::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'chorales'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:user,moderator,admin',
            'chorale_id' => 'nullable|exists:chorales,id',
            'is_approved' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data = $request->except(['password_confirmation']);
        $data['is_approved'] = $request->has('is_approved');
        $data['is_active'] = $request->has('is_active');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Approuver un utilisateur
     */
    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'approved']);

        return back()->with('success', 'Utilisateur approuvé avec succès.');
    }

    /**
     * Rejeter un utilisateur
     */
    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'rejected']);

        return back()->with('success', 'Utilisateur rejeté.');
    }

    /**
     * Activer un utilisateur
     */
    public function activateUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => true]);

        return back()->with('success', 'Utilisateur activé avec succès.');
    }

    /**
     * Désactiver un utilisateur
     */
    public function deactivateUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => false]);

        return back()->with('success', 'Utilisateur désactivé.');
    }

    /**
     * Supprimer un utilisateur
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // Empêcher la suppression de son propre compte
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        $user->delete();

        return back()->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Gestion des utilisateurs pour les maestros (uniquement leur chorale)
     */
    public function maestroUsers()
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        if (!$chorale) {
            return redirect()->route('admin.chorale.config')->with('error', 'Vous n\'êtes associé à aucune chorale.');
        }
        
        $users = User::where('chorale_id', $chorale->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.users.maestro', compact('users', 'chorale'));
    }

    /**
     * Approuver un utilisateur (pour maestro - vérification de chorale)
     */
    public function maestroApproveUser($id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);
        
        // Vérifier que l'utilisateur appartient à la chorale du maestro
        if ($user->chorale_id !== $currentUser->chorale_id) {
            return back()->with('error', 'Vous n\'avez pas le droit de modifier cet utilisateur.');
        }
        
        $user->update(['status' => 'approved']);
        
        return back()->with('success', 'Utilisateur approuvé avec succès.');
    }

    /**
     * Rejeter un utilisateur (pour maestro - vérification de chorale)
     */
    public function maestroRejectUser($id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);
        
        // Vérifier que l'utilisateur appartient à la chorale du maestro
        if ($user->chorale_id !== $currentUser->chorale_id) {
            return back()->with('error', 'Vous n\'avez pas le droit de modifier cet utilisateur.');
        }
        
        // Empêcher le rejet de son propre compte
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Vous ne pouvez pas rejeter votre propre compte.');
        }
        
        $user->update(['status' => 'rejected']);
        
        return back()->with('success', 'Utilisateur rejeté.');
    }

    /**
     * Supprimer un utilisateur (pour maestro - vérification de chorale)
     */
    public function maestroDeleteUser($id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);
        
        // Vérifier que l'utilisateur appartient à la chorale du maestro
        if ($user->chorale_id !== $currentUser->chorale_id) {
            return back()->with('error', 'Vous n\'avez pas le droit de supprimer cet utilisateur.');
        }
        
        // Empêcher la suppression de son propre compte
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        $user->delete();
        
        return back()->with('success', 'Utilisateur supprimé avec succès.');
    }
} 