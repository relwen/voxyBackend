<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Chorale;
use App\Models\Partition;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        $partition = Partition::with(['chorale', 'category', 'reference'])->findOrFail($id);
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
        $categories = \App\Models\Category::all();
        return view('admin.partitions.create', compact('chorales', 'categories'));
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
            'chorale_id' => 'required|exists:chorales,id',
            'audio_files.*' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
            'pdf_files.*' => 'nullable|file|mimes:pdf|max:20480',
            'image_files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $data = $request->except(['audio_files', 'pdf_files', 'image_files']);

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

        Partition::create($data);

        return redirect()->route('admin.partitions')->with('success', 'Partition créée avec succès.');
    }

    /**
     * Afficher le formulaire d'édition d'une partition
     */
    public function editPartition($id)
    {
        $partition = Partition::with(['chorale', 'category', 'reference'])->findOrFail($id);
        $chorales = Chorale::all();
        $categories = \App\Models\Category::all();
        return view('admin.partitions.edit', compact('partition', 'chorales', 'categories'));
    }

    /**
     * Mettre à jour une partition
     */
    public function updatePartition(Request $request, $id)
    {
        $partition = Partition::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'reference_id' => 'nullable|exists:references,id',
            'chorale_id' => 'required|exists:chorales,id',
            'audio_files.*' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
            'pdf_files.*' => 'nullable|file|mimes:pdf|max:20480',
            'image_files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $data = $request->except(['audio_files', 'pdf_files', 'image_files']);

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

        $partition->update($data);

        return redirect()->route('admin.partitions')->with('success', 'Partition mise à jour avec succès.');
    }

    /**
     * Supprimer une partition
     */
    public function deletePartition($id)
    {
        $partition = Partition::findOrFail($id);
        
        // Supprimer tous les fichiers associés
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
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        $user->delete();

        return back()->with('success', 'Utilisateur supprimé avec succès.');
    }
} 