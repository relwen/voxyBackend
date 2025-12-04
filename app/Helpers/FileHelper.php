<?php

namespace App\Helpers;

class FileHelper
{
    /**
     * Types de fichiers supportés
     */
    const TYPE_AUDIO = 'audio';
    const TYPE_PDF = 'pdf';
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_DOCUMENT = 'document';
    const TYPE_OTHER = 'other';

    /**
     * Extensions audio
     */
    const AUDIO_EXTENSIONS = ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac', 'wma'];

    /**
     * Extensions PDF
     */
    const PDF_EXTENSIONS = ['pdf'];

    /**
     * Extensions images
     */
    const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];

    /**
     * Extensions vidéo
     */
    const VIDEO_EXTENSIONS = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];

    /**
     * Extensions documents
     */
    const DOCUMENT_EXTENSIONS = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'odt', 'ods'];

    /**
     * Détecte le type de fichier à partir de son extension
     *
     * @param string $filename
     * @return string
     */
    public static function getFileType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($extension, self::AUDIO_EXTENSIONS)) {
            return self::TYPE_AUDIO;
        }

        if (in_array($extension, self::PDF_EXTENSIONS)) {
            return self::TYPE_PDF;
        }

        if (in_array($extension, self::IMAGE_EXTENSIONS)) {
            return self::TYPE_IMAGE;
        }

        if (in_array($extension, self::VIDEO_EXTENSIONS)) {
            return self::TYPE_VIDEO;
        }

        if (in_array($extension, self::DOCUMENT_EXTENSIONS)) {
            return self::TYPE_DOCUMENT;
        }

        return self::TYPE_OTHER;
    }

    /**
     * Retourne l'icône Font Awesome appropriée pour le type de fichier
     *
     * @param string $filename
     * @return string
     */
    public static function getFileIcon(string $filename): string
    {
        $type = self::getFileType($filename);

        return match($type) {
            self::TYPE_AUDIO => 'fa-music',
            self::TYPE_PDF => 'fa-file-pdf',
            self::TYPE_IMAGE => 'fa-image',
            self::TYPE_VIDEO => 'fa-video',
            self::TYPE_DOCUMENT => 'fa-file-word',
            default => 'fa-file',
        };
    }

    /**
     * Retourne la classe CSS de couleur appropriée pour le type de fichier
     *
     * @param string $filename
     * @return string
     */
    public static function getFileColorClass(string $filename): string
    {
        $type = self::getFileType($filename);

        return match($type) {
            self::TYPE_AUDIO => 'bg-green-100 text-green-800',
            self::TYPE_PDF => 'bg-red-100 text-red-800',
            self::TYPE_IMAGE => 'bg-blue-100 text-blue-800',
            self::TYPE_VIDEO => 'bg-purple-100 text-purple-800',
            self::TYPE_DOCUMENT => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Retourne le label du type de fichier
     *
     * @param string $filename
     * @return string
     */
    public static function getFileTypeLabel(string $filename): string
    {
        $type = self::getFileType($filename);

        return match($type) {
            self::TYPE_AUDIO => 'Audio',
            self::TYPE_PDF => 'PDF',
            self::TYPE_IMAGE => 'Image',
            self::TYPE_VIDEO => 'Vidéo',
            self::TYPE_DOCUMENT => 'Document',
            default => 'Autre',
        };
    }

    /**
     * Retourne le chemin de stockage approprié selon le type de fichier
     *
     * @param string $filename
     * @return string
     */
    public static function getStoragePath(string $filename): string
    {
        $type = self::getFileType($filename);

        return match($type) {
            self::TYPE_AUDIO => 'partitions/audio',
            self::TYPE_PDF => 'partitions/pdf',
            self::TYPE_IMAGE => 'partitions/images',
            self::TYPE_VIDEO => 'partitions/videos',
            self::TYPE_DOCUMENT => 'partitions/documents',
            default => 'partitions/files',
        };
    }

    /**
     * Valide si le type de fichier est accepté
     *
     * @param string $filename
     * @param array $allowedTypes
     * @return bool
     */
    public static function isFileTypeAllowed(string $filename, array $allowedTypes = []): bool
    {
        if (empty($allowedTypes)) {
            // Par défaut, accepter tous les types
            return true;
        }

        $fileType = self::getFileType($filename);
        return in_array($fileType, $allowedTypes);
    }

    /**
     * Retourne toutes les extensions acceptées pour l'upload
     *
     * @return array
     */
    public static function getAllAcceptedExtensions(): array
    {
        return array_merge(
            self::AUDIO_EXTENSIONS,
            self::PDF_EXTENSIONS,
            self::IMAGE_EXTENSIONS,
            self::VIDEO_EXTENSIONS,
            self::DOCUMENT_EXTENSIONS
        );
    }

    /**
     * Retourne la chaîne accept pour l'attribut HTML accept
     *
     * @return string
     */
    public static function getAcceptAttribute(): string
    {
        $extensions = self::getAllAcceptedExtensions();
        return '.' . implode(',.', $extensions);
    }

    /**
     * Génère un nom de fichier basé sur la messe, la partie et le pupitre
     * Format: messe_{nom_messe}_{partie}_{pupitre}_{type}.{extension}
     *
     * @param \Illuminate\Http\UploadedFile $file Le fichier uploadé
     * @param \App\Models\Partition|null $partition La partition (peut être null lors de la création)
     * @param string|null $messeNom Le nom de la messe (optionnel si partition fournie)
     * @param string|null $partie La partie de messe (optionnel si partition fournie)
     * @param string|null $subPartie La sous-partie (optionnel si partition fournie)
     * @param string|null $pupitreNom Le nom du pupitre (optionnel si partition fournie)
     * @return string Le nom de fichier généré
     */
    public static function generatePartitionFileName(
        $file,
        $partition = null,
        $messeNom = null,
        $partie = null,
        $subPartie = null,
        $pupitreNom = null
    ): string {
        // Récupérer les informations depuis la partition si fournie
        if ($partition) {
            // Charger les relations si nécessaire
            if (!$partition->relationLoaded('rubriqueSection')) {
                $partition->load('rubriqueSection');
            }
            if (!$partition->relationLoaded('pupitre')) {
                $partition->load('pupitre');
            }

            // Nom de la messe
            if (!$messeNom && $partition->rubriqueSection) {
                $messeNom = $partition->rubriqueSection->nom;
            }

            // Partie et sous-partie
            if (!$partie && $partition->messe_part) {
                $messePart = is_array($partition->messe_part) ? $partition->messe_part : json_decode($partition->messe_part, true);
                $partie = $messePart['part'] ?? null;
                $subPartie = $messePart['subPart'] ?? null;
            }

            // Nom du pupitre
            if (!$pupitreNom && $partition->pupitre) {
                $pupitreNom = $partition->pupitre->nom;
            }
        }

        // Normaliser les valeurs (minuscules, remplacer espaces et caractères spéciaux)
        $normalize = function($str) {
            if (empty($str)) {
                return '';
            }
            // Convertir en minuscules
            $str = mb_strtolower($str, 'UTF-8');
            // Remplacer les caractères accentués
            $str = self::removeAccents($str);
            // Remplacer les espaces et caractères spéciaux par des underscores
            $str = preg_replace('/[^a-z0-9]+/', '_', $str);
            // Supprimer les underscores en début et fin
            $str = trim($str, '_');
            return $str;
        };

        $messeNomNormalized = $normalize($messeNom ?? '');
        $partieNormalized = $normalize($partie ?? '');
        $subPartieNormalized = $normalize($subPartie ?? '');
        $pupitreNomNormalized = $normalize($pupitreNom ?? '');

        // Construire la partie
        $partieComplete = $partieNormalized;
        if (!empty($subPartieNormalized)) {
            $partieComplete .= '_' . $subPartieNormalized;
        }

        // Obtenir le type de fichier et l'extension
        $extension = strtolower($file->getClientOriginalExtension());
        $fileType = self::getFileType($file->getClientOriginalName());
        
        // Mapper le type vers un label court
        $typeLabel = match($fileType) {
            self::TYPE_AUDIO => 'audio',
            self::TYPE_PDF => 'pdf',
            self::TYPE_IMAGE => 'jpg', // Utiliser 'jpg' pour toutes les images
            self::TYPE_VIDEO => 'video',
            self::TYPE_DOCUMENT => 'doc',
            default => 'file',
        };

        // Construire le nom de fichier
        $parts = ['messe'];
        
        if (!empty($messeNomNormalized)) {
            $parts[] = $messeNomNormalized;
        }
        
        if (!empty($partieComplete)) {
            $parts[] = $partieComplete;
        }
        
        if (!empty($pupitreNomNormalized)) {
            $parts[] = $pupitreNomNormalized;
        }
        
        $parts[] = $typeLabel;
        
        $filename = implode('_', $parts) . '.' . $extension;
        
        return $filename;
    }

    /**
     * Génère un nom de fichier unique en ajoutant un suffixe si nécessaire
     *
     * @param string $baseFileName Le nom de fichier de base
     * @param array $existingFiles Les fichiers existants dans le même répertoire
     * @param string $storagePath Le chemin de stockage
     * @return string Le nom de fichier unique
     */
    public static function ensureUniqueFileName(string $baseFileName, array $existingFiles, string $storagePath): string
    {
        $extension = pathinfo($baseFileName, PATHINFO_EXTENSION);
        $nameWithoutExt = pathinfo($baseFileName, PATHINFO_FILENAME);
        
        // Extraire les chemins complets des fichiers existants
        $existingPaths = array_map(function($file) {
            // Gérer le cas où $file est un tableau ou une chaîne
            return is_array($file) ? ($file['path'] ?? $file['name'] ?? '') : $file;
        }, $existingFiles);
        
        // Extraire seulement les noms de fichiers (sans le chemin)
        $existingFileNames = array_map(function($path) {
            return basename($path);
        }, array_filter($existingPaths));
        
        $finalFileName = $baseFileName;
        $counter = 1;
        
        // Vérifier si le fichier existe déjà
        $storage = \Illuminate\Support\Facades\Storage::disk('public');
        while (in_array($finalFileName, $existingFileNames) || 
               $storage->exists($storagePath . '/' . $finalFileName)) {
            $finalFileName = $nameWithoutExt . '_' . $counter . '.' . $extension;
            $counter++;
        }
        
        return $finalFileName;
    }

    /**
     * Supprime les accents d'une chaîne
     *
     * @param string $str
     * @return string
     */
    private static function removeAccents(string $str): string
    {
        $accents = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ç' => 'c', 'ñ' => 'n',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y',
            'Ç' => 'C', 'Ñ' => 'N',
        ];
        
        return strtr($str, $accents);
    }
}

