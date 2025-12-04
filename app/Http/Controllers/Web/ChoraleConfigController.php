<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Chorale;
use App\Models\ChoralePupitre;
use App\Models\ChoraleTemplate;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ChoraleConfigController extends Controller
{
    /**
     * Afficher la page de configuration de la chorale
     */
    public function index()
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        if (!$chorale) {
            return redirect()->route('admin.dashboard')->with('error', 'Vous n\'êtes associé à aucune chorale.');
        }

        $pupitres = $chorale->pupitres;
        $categories = $chorale->categories()->with('sections')->orderBy('name')->get();
        $templates = ChoraleTemplate::where('is_system', true)->get();

        return view('admin.chorales.config', compact('chorale', 'pupitres', 'categories', 'templates'));
    }

    /**
     * Créer un nouveau pupitre
     */
    public function storePupitre(Request $request)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        if (!$chorale) {
            return response()->json(['success' => false, 'message' => 'Chorale non trouvée'], 404);
        }

        $request->validate([
            'nom' => 'required|string|max:255|unique:chorale_pupitres,nom,NULL,id,chorale_id,' . $chorale->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'order' => 'nullable|integer|min:0',
            'is_default' => 'nullable|boolean',
        ]);

        // Si c'est le pupitre par défaut, désactiver les autres
        if ($request->is_default) {
            ChoralePupitre::where('chorale_id', $chorale->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $pupitre = ChoralePupitre::create([
            'chorale_id' => $chorale->id,
            'nom' => $request->nom,
            'description' => $request->description,
            'color' => $request->color,
            'icon' => $request->icon,
            'order' => $request->order ?? 0,
            'is_default' => $request->is_default ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pupitre créé avec succès',
            'data' => $pupitre
        ]);
    }

    /**
     * Afficher un pupitre pour édition
     */
    public function showPupitre($id)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $pupitre = ChoralePupitre::where('id', $id)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        return response()->json($pupitre);
    }

    /**
     * Mettre à jour un pupitre
     */
    public function updatePupitre(Request $request, $id)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $pupitre = ChoralePupitre::where('id', $id)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        $request->validate([
            'nom' => 'required|string|max:255|unique:chorale_pupitres,nom,' . $id . ',id,chorale_id,' . $chorale->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'order' => 'nullable|integer|min:0',
            'is_default' => 'nullable|boolean',
        ]);

        // Si c'est le pupitre par défaut, désactiver les autres
        if ($request->is_default) {
            ChoralePupitre::where('chorale_id', $chorale->id)
                ->where('id', '!=', $id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $pupitre->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Pupitre mis à jour avec succès',
            'data' => $pupitre
        ]);
    }

    /**
     * Supprimer un pupitre
     */
    public function destroyPupitre($id)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $pupitre = ChoralePupitre::where('id', $id)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        // Vérifier s'il y a des partitions associées
        if ($pupitre->partitions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer ce pupitre car il est utilisé par des partitions'
            ], 422);
        }

        $pupitre->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pupitre supprimé avec succès'
        ]);
    }

    /**
     * Créer une nouvelle rubrique (catégorie)
     */
    public function storeCategory(Request $request)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        if (!$chorale) {
            return response()->json(['success' => false, 'message' => 'Chorale non trouvée'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,NULL,id,chorale_id,' . $chorale->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'required|string|max:50',
            'structure_type' => 'required|in:simple,with_sections,with_dossiers',
            'structure_config' => 'nullable|array',
        ]);

        // Vérifier que l'icône existe dans la liste des icônes disponibles
        if (!\App\Helpers\IconHelper::iconExists($request->icon)) {
            return response()->json([
                'success' => false,
                'message' => 'Icône non valide. Veuillez sélectionner une icône de la liste.'
            ], 422);
        }

        $category = Category::create([
            'chorale_id' => $chorale->id,
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'icon' => $request->icon,
            'structure_type' => $request->structure_type ?? 'simple',
            'structure_config' => $request->structure_config ?? [],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rubrique créée avec succès',
            'data' => $category
        ]);
    }

    /**
     * Afficher une rubrique pour édition
     */
    public function showCategory($id)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $category = Category::where('id', $id)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        return response()->json($category);
    }

    /**
     * Mettre à jour une rubrique
     */
    public function updateCategory(Request $request, $id)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $category = Category::where('id', $id)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id . ',id,chorale_id,' . $chorale->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'required|string|max:50',
            'structure_type' => 'required|in:simple,with_sections,with_dossiers',
            'structure_config' => 'nullable|array',
        ]);

        // Vérifier que l'icône existe dans la liste des icônes disponibles
        if (!\App\Helpers\IconHelper::iconExists($request->icon)) {
            return response()->json([
                'success' => false,
                'message' => 'Icône non valide. Veuillez sélectionner une icône de la liste.'
            ], 422);
        }

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Rubrique mise à jour avec succès',
            'data' => $category
        ]);
    }

    /**
     * Supprimer une rubrique
     */
    public function destroyCategory($id)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $category = Category::where('id', $id)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        // Vérifier s'il y a des partitions associées
        if ($category->partitions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette rubrique car elle est utilisée par des partitions'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rubrique supprimée avec succès'
        ]);
    }

    /**
     * Appliquer un template à la chorale
     */
    public function applyTemplate(Request $request)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        if (!$chorale) {
            return response()->json(['success' => false, 'message' => 'Chorale non trouvée'], 404);
        }

        $request->validate([
            'template_id' => 'required|exists:chorale_templates,id',
        ]);

        $template = ChoraleTemplate::findOrFail($request->template_id);
        
        try {
            $template->applyToChorale($chorale->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Template appliqué avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'application du template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'application du template'
            ], 500);
        }
    }
}
