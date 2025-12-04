<?php

namespace App\Helpers;

class IconHelper
{
    /**
     * Liste des icônes Material Icons disponibles pour les rubriques
     * Ces icônes sont compatibles avec les apps mobiles (Material Design)
     */
    public static function getAvailableIcons(): array
    {
        return [
            // Musique et chant
            'music_note' => ['name' => 'Note de musique', 'icon' => 'music_note', 'category' => 'Musique'],
            'library_music' => ['name' => 'Bibliothèque musicale', 'icon' => 'library_music', 'category' => 'Musique'],
            'queue_music' => ['name' => 'File musicale', 'icon' => 'queue_music', 'category' => 'Musique'],
            'audiotrack' => ['name' => 'Piste audio', 'icon' => 'audiotrack', 'category' => 'Musique'],
            'headphones' => ['name' => 'Écouteurs', 'icon' => 'headphones', 'category' => 'Musique'],
            'radio' => ['name' => 'Radio', 'icon' => 'radio', 'category' => 'Musique'],
            
            // Vocalises et entraînement
            'mic' => ['name' => 'Microphone', 'icon' => 'mic', 'category' => 'Vocal'],
            'record_voice_over' => ['name' => 'Enregistrement vocal', 'icon' => 'record_voice_over', 'category' => 'Vocal'],
            'graphic_eq' => ['name' => 'Égaliseur', 'icon' => 'graphic_eq', 'category' => 'Vocal'],
            'hearing' => ['name' => 'Écoute', 'icon' => 'hearing', 'category' => 'Vocal'],
            
            // Religieux
            'church' => ['name' => 'Église', 'icon' => 'church', 'category' => 'Religieux'],
            'temple_hindu' => ['name' => 'Temple', 'icon' => 'temple_hindu', 'category' => 'Religieux'],
            'mosque' => ['name' => 'Mosquée', 'icon' => 'mosque', 'category' => 'Religieux'],
            'celebration' => ['name' => 'Célébration', 'icon' => 'celebration', 'category' => 'Religieux'],
            'candle' => ['name' => 'Bougie', 'icon' => 'candle', 'category' => 'Religieux'],
            
            // Chants et hymnes
            'favorite' => ['name' => 'Favori', 'icon' => 'favorite', 'category' => 'Chants'],
            'favorite_border' => ['name' => 'Favori (contour)', 'icon' => 'favorite_border', 'category' => 'Chants'],
            'star' => ['name' => 'Étoile', 'icon' => 'star', 'category' => 'Chants'],
            'star_border' => ['name' => 'Étoile (contour)', 'icon' => 'star_border', 'category' => 'Chants'],
            'flag' => ['name' => 'Drapeau', 'icon' => 'flag', 'category' => 'Chants'],
            'emoji_events' => ['name' => 'Trophée', 'icon' => 'emoji_events', 'category' => 'Chants'],
            
            // Organisation
            'folder' => ['name' => 'Dossier', 'icon' => 'folder', 'category' => 'Organisation'],
            'folder_music' => ['name' => 'Dossier musique', 'icon' => 'folder_music', 'category' => 'Organisation'],
            'collections' => ['name' => 'Collections', 'icon' => 'collections', 'category' => 'Organisation'],
            'category' => ['name' => 'Catégorie', 'icon' => 'category', 'category' => 'Organisation'],
            'book' => ['name' => 'Livre', 'icon' => 'book', 'category' => 'Organisation'],
            'menu_book' => ['name' => 'Livre de menu', 'icon' => 'menu_book', 'category' => 'Organisation'],
            
            // Événements
            'event' => ['name' => 'Événement', 'icon' => 'event', 'category' => 'Événements'],
            'event_note' => ['name' => 'Note d\'événement', 'icon' => 'event_note', 'category' => 'Événements'],
            'calendar_today' => ['name' => 'Calendrier', 'icon' => 'calendar_today', 'category' => 'Événements'],
            'festival' => ['name' => 'Festival', 'icon' => 'festival', 'category' => 'Événements'],
            
            // Général
            'library_books' => ['name' => 'Bibliothèque', 'icon' => 'library_books', 'category' => 'Général'],
            'description' => ['name' => 'Description', 'icon' => 'description', 'category' => 'Général'],
            'article' => ['name' => 'Article', 'icon' => 'article', 'category' => 'Général'],
            'note' => ['name' => 'Note', 'icon' => 'note', 'category' => 'Général'],
            'sticky_note_2' => ['name' => 'Note adhésive', 'icon' => 'sticky_note_2', 'category' => 'Général'],
        ];
    }

    /**
     * Obtenir les icônes groupées par catégorie
     */
    public static function getIconsByCategory(): array
    {
        $icons = self::getAvailableIcons();
        $grouped = [];

        foreach ($icons as $key => $icon) {
            $category = $icon['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$key] = $icon;
        }

        return $grouped;
    }

    /**
     * Obtenir le nom d'une icône
     */
    public static function getIconName(string $iconKey): string
    {
        $icons = self::getAvailableIcons();
        return $icons[$iconKey]['name'] ?? $iconKey;
    }

    /**
     * Vérifier si une icône existe
     */
    public static function iconExists(string $iconKey): bool
    {
        $icons = self::getAvailableIcons();
        return isset($icons[$iconKey]);
    }
}

