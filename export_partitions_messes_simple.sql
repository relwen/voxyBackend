-- =====================================================
-- EXPORT RAPIDE DES PARTITIONS LIÉES AUX MESSES
-- =====================================================
-- Utilisez cette requête pour exporter rapidement toutes vos partitions
-- avec leurs fichiers et données liées (messes, catégories, références)

-- EXPORT PRINCIPAL : Toutes les partitions avec fichiers et relations
SELECT 
    -- Informations de la partition
    p.id AS partition_id,
    p.title,
    p.description,
    p.files,                          -- JSON avec tous les fichiers (audio, pdf, images)
    p.category_id,
    p.rubrique_section_id,
    p.messe_part,                     -- JSON avec part et subPart (ex: {"part": "Kyrié"})
    p.reference_id,
    p.messe_id,
    p.chorale_id,
    p.pupitre_id,
    p.pupitre,
    -- Anciens champs fichiers (pour compatibilité)
    p.audio_path,
    p.pdf_path,
    p.image_path,
    p.audio_files,                    -- JSON array
    p.pdf_files,                      -- JSON array
    p.image_files,                    -- JSON array
    p.created_at,
    p.updated_at,
    -- Informations liées (pour référence et compréhension)
    c.name AS category_name,
    m.name AS messe_name,
    r.name AS reference_name,
    rs.nom AS rubrique_section_nom,
    ch.name AS chorale_name
FROM partitions p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN messes m ON p.messe_id = m.id
LEFT JOIN references r ON p.reference_id = r.id
LEFT JOIN rubrique_sections rs ON p.rubrique_section_id = rs.id
LEFT JOIN chorales ch ON p.chorale_id = ch.id
WHERE p.messe_id IS NOT NULL          -- Uniquement les partitions liées aux messes
ORDER BY p.messe_id, p.category_id, p.created_at;

-- =====================================================
-- VERSION POUR GÉNÉRER LES REQUÊTES INSERT DIRECTEMENT
-- =====================================================
-- Cette requête génère des instructions INSERT prêtes à l'emploi
-- Copiez le résultat et exécutez-le dans votre nouvelle base de données

SELECT CONCAT(
    'INSERT INTO partitions (id, title, description, files, category_id, rubrique_section_id, messe_part, reference_id, messe_id, chorale_id, pupitre_id, pupitre, audio_path, pdf_path, image_path, audio_files, pdf_files, image_files, created_at, updated_at) VALUES (',
    p.id, ', ',
    QUOTE(COALESCE(p.title, '')), ', ',
    IFNULL(QUOTE(p.description), 'NULL'), ', ',
    IFNULL(QUOTE(JSON_UNQUOTE(JSON_EXTRACT(p.files, '$'))), 'NULL'), ', ',
    IFNULL(CAST(p.category_id AS CHAR), 'NULL'), ', ',
    IFNULL(CAST(p.rubrique_section_id AS CHAR), 'NULL'), ', ',
    IFNULL(QUOTE(JSON_UNQUOTE(JSON_EXTRACT(p.messe_part, '$'))), 'NULL'), ', ',
    IFNULL(CAST(p.reference_id AS CHAR), 'NULL'), ', ',
    IFNULL(CAST(p.messe_id AS CHAR), 'NULL'), ', ',
    IFNULL(CAST(p.chorale_id AS CHAR), 'NULL'), ', ',
    IFNULL(CAST(p.pupitre_id AS CHAR), 'NULL'), ', ',
    IFNULL(QUOTE(p.pupitre), 'NULL'), ', ',
    IFNULL(QUOTE(p.audio_path), 'NULL'), ', ',
    IFNULL(QUOTE(p.pdf_path), 'NULL'), ', ',
    IFNULL(QUOTE(p.image_path), 'NULL'), ', ',
    IFNULL(QUOTE(JSON_UNQUOTE(JSON_EXTRACT(p.audio_files, '$'))), 'NULL'), ', ',
    IFNULL(QUOTE(JSON_UNQUOTE(JSON_EXTRACT(p.pdf_files, '$'))), 'NULL'), ', ',
    IFNULL(QUOTE(JSON_UNQUOTE(JSON_EXTRACT(p.image_files, '$'))), 'NULL'), ', ',
    QUOTE(DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s')), ', ',
    QUOTE(DATE_FORMAT(p.updated_at, '%Y-%m-%d %H:%i:%s')),
    ');'
) AS insert_statements
FROM partitions p
WHERE p.messe_id IS NOT NULL
ORDER BY p.messe_id, p.category_id, p.created_at;

-- =====================================================
-- EXPORT DES DONNÉES LIÉES (pour import complet)
-- =====================================================

-- 1. Catégories utilisées par les partitions de messes
SELECT DISTINCT
    c.*
FROM categories c
INNER JOIN partitions p ON p.category_id = c.id
WHERE p.messe_id IS NOT NULL
ORDER BY c.id;

-- 2. Messes utilisées
SELECT DISTINCT
    m.*
FROM messes m
INNER JOIN partitions p ON p.messe_id = m.id
WHERE p.messe_id IS NOT NULL
ORDER BY m.id;

-- 3. Références utilisées
SELECT DISTINCT
    r.*
FROM references r
INNER JOIN partitions p ON p.reference_id = r.id
WHERE p.messe_id IS NOT NULL AND p.reference_id IS NOT NULL
ORDER BY r.id;

-- 4. Sections de rubrique utilisées
SELECT DISTINCT
    rs.*
FROM rubrique_sections rs
INNER JOIN partitions p ON p.rubrique_section_id = rs.id
WHERE p.messe_id IS NOT NULL AND p.rubrique_section_id IS NOT NULL
ORDER BY rs.id;

