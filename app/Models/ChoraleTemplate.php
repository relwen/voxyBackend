<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ChoralePupitre;
use App\Models\Category;

class ChoraleTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'default_pupitres',
        'default_categories',
        'is_system',
    ];

    protected $casts = [
        'default_pupitres' => 'array',
        'default_categories' => 'array',
        'is_system' => 'boolean',
    ];

    /**
     * Appliquer le template à une chorale
     */
    public function applyToChorale($choraleId)
    {
        // Créer les pupitres (seulement s'ils n'existent pas déjà)
        if ($this->default_pupitres) {
            foreach ($this->default_pupitres as $index => $pupitre) {
                ChoralePupitre::firstOrCreate(
                    [
                        'chorale_id' => $choraleId,
                        'nom' => $pupitre['nom'],
                    ],
                    [
                        'description' => $pupitre['description'] ?? null,
                        'color' => $pupitre['color'] ?? null,
                        'icon' => $pupitre['icon'] ?? null,
                        'order' => $pupitre['order'] ?? $index,
                        'is_default' => $pupitre['is_default'] ?? false,
                    ]
                );
            }
        }

        // Créer les catégories (seulement si elles n'existent pas déjà)
        if ($this->default_categories) {
            foreach ($this->default_categories as $index => $category) {
                // Vérifier que l'icône existe
                $icon = $category['icon'] ?? null;
                if ($icon && !\App\Helpers\IconHelper::iconExists($icon)) {
                    $icon = 'music_note'; // Icône par défaut
                }
                
                Category::firstOrCreate(
                    [
                        'chorale_id' => $choraleId,
                        'name' => $category['name'],
                    ],
                    [
                        'description' => $category['description'] ?? null,
                        'color' => $category['color'] ?? null,
                        'icon' => $icon,
                    ]
                );
            }
        }
    }
}
