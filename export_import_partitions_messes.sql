-- =====================================================
-- REQUÊTE SQL D'EXPORT DES PARTITIONS LIÉES AUX MESSES
-- =====================================================
-- Cette requête exporte toutes les données essentielles des partitions
-- liées aux messes avec leurs catégories, références et fichiers

-- 1. EXPORT DES CATÉGORIES LIÉES AUX MESSES
-- (Exporte les catégories qui ont des partitions liées aux messes)
SELECT 
    c.id,
    c.name,
    c.description,
    c.color,
    c.icon,
    c.chorale_id,
    c.structure_type,
    c.structure_config,
    c.created_at,
    c.updated_at
FROM categories c
WHERE c.id IN (
    SELECT DISTINCT p.category_id 
    FROM partitions p 
    WHERE p.messe_id IS NOT NULL
)
INTO OUTFILE '/tmp/categories_messes_export.sql'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';

-- 2. EXPORT DES MESSES
SELECT 
    m.id,
    m.name,
    m.description,
    m.color,
    m.icon,
    m.active,
    m.created_at,
    m.updated_at
FROM messes m
WHERE m.id IN (
    SELECT DISTINCT p.messe_id 
    FROM partitions p 
    WHERE p.messe_id IS NOT NULL
)
INTO OUTFILE '/tmp/messes_export.sql'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';

-- 3. EXPORT DES RÉFÉRENCES LIÉES
SELECT 
    r.id,
    r.name,
    r.description,
    r.order_position,
    r.category_id,
    r.created_at,
    r.updated_at
FROM references r
WHERE r.id IN (
    SELECT DISTINCT p.reference_id 
    FROM partitions p 
    WHERE p.messe_id IS NOT NULL AND p.reference_id IS NOT NULL
)
INTO OUTFILE '/tmp/references_export.sql'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';

-- 4. EXPORT DES SECTIONS DE RUBRIQUE
SELECT 
    rs.id,
    rs.category_id,
    rs.dossier_id,
    rs.nom,
    rs.description,
    rs.order,
    rs.type,
    rs.structure,
    rs.created_at,
    rs.updated_at
FROM rubrique_sections rs
WHERE rs.id IN (
    SELECT DISTINCT p.rubrique_section_id 
    FROM partitions p 
    WHERE p.messe_id IS NOT NULL AND p.rubrique_section_id IS NOT NULL
)
INTO OUTFILE '/tmp/rubrique_sections_export.sql'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';

-- 5. EXPORT COMPLET DES PARTITIONS AVEC TOUTES LES INFORMATIONS
SELECT 
    p.id,
    p.title,
    p.description,
    p.files,                      -- JSON contenant tous les fichiers
    p.category_id,
    p.rubrique_section_id,
    p.messe_part,                 -- JSON avec part et subPart
    p.reference_id,
    p.messe_id,
    p.chorale_id,
    p.pupitre_id,
    p.pupitre,
    -- Anciens champs pour compatibilité
    p.audio_path,
    p.pdf_path,
    p.image_path,
    p.audio_files,                -- JSON array
    p.pdf_files,                  -- JSON array
    p.image_files,                -- JSON array
    p.created_at,
    p.updated_at,
    -- Informations liées pour référence
    c.name AS category_name,
    m.name AS messe_name,
    r.name AS reference_name,
    rs.nom AS rubrique_section_nom
FROM partitions p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN messes m ON p.messe_id = m.id
LEFT JOIN references r ON p.reference_id = r.id
LEFT JOIN rubrique_sections rs ON p.rubrique_section_id = rs.id
WHERE p.messe_id IS NOT NULL
ORDER BY p.messe_id, p.category_id, p.created_at;

-- =====================================================
-- VERSION SIMPLIFIÉE POUR EXPORT RAPIDE (UNE SEULE REQUÊTE)
-- =====================================================
-- Cette requête combine toutes les données en une seule vue
SELECT 
    'PARTITION' AS record_type,
    p.id,
    p.title,
    p.description,
    p.files,
    p.category_id,
    p.rubrique_section_id,
    p.messe_part,
    p.reference_id,
    p.messe_id,
    p.chorale_id,
    p.pupitre_id,
    p.pupitre,
    p.audio_path,
    p.pdf_path,
    p.image_path,
    p.audio_files,
    p.pdf_files,
    p.image_files,
    p.created_at,
    p.updated_at,
    c.name AS category_name,
    m.name AS messe_name,
    r.name AS reference_name,
    rs.nom AS rubrique_section_nom
FROM partitions p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN messes m ON p.messe_id = m.id
LEFT JOIN references r ON p.reference_id = r.id
LEFT JOIN rubrique_sections rs ON p.rubrique_section_id = rs.id
WHERE p.messe_id IS NOT NULL
ORDER BY p.messe_id, p.category_id, p.created_at;

-- =====================================================
-- REQUÊTES D'IMPORT (INSERT)
-- =====================================================

-- IMPORT DES CATÉGORIES (si elles n'existent pas déjà)
INSERT INTO categories (id, name, description, color, icon, chorale_id, structure_type, structure_config, created_at, updated_at)
SELECT 
    c.id,
    c.name,
    c.description,
    c.color,
    c.icon,
    c.chorale_id,
    c.structure_type,
    c.structure_config,
    c.created_at,
    c.updated_at
FROM (
    -- Remplacez cette sous-requête par vos données réelles
    SELECT 1 as id, 'Messe' as name, NULL as description, '#FF5733' as color, 'church' as icon, NULL as chorale_id, NULL as structure_type, NULL as structure_config, NOW() as created_at, NOW() as updated_at
) c
WHERE NOT EXISTS (
    SELECT 1 FROM categories WHERE categories.id = c.id
);

-- IMPORT DES MESSES (si elles n'existent pas déjà)
INSERT INTO messes (id, name, description, color, icon, active, created_at, updated_at)
SELECT 
    m.id,
    m.name,
    m.description,
    m.color,
    m.icon,
    m.active,
    m.created_at,
    m.updated_at
FROM (
    -- Remplacez cette sous-requête par vos données réelles
    SELECT 1 as id, 'Messe St Gabriel' as name, NULL as description, '#3498db' as color, 'music_note' as icon, 1 as active, NOW() as created_at, NOW() as updated_at
) m
WHERE NOT EXISTS (
    SELECT 1 FROM messes WHERE messes.id = m.id
);

-- IMPORT DES RÉFÉRENCES (si elles n'existent pas déjà)
INSERT INTO references (id, name, description, order_position, category_id, created_at, updated_at)
SELECT 
    r.id,
    r.name,
    r.description,
    r.order_position,
    r.category_id,
    r.created_at,
    r.updated_at
FROM (
    -- Remplacez cette sous-requête par vos données réelles
    SELECT 1 as id, 'Kyrié' as name, NULL as description, '1' as order_position, 1 as category_id, NOW() as created_at, NOW() as updated_at
) r
WHERE NOT EXISTS (
    SELECT 1 FROM references WHERE references.id = r.id
);

-- IMPORT DES SECTIONS DE RUBRIQUE (si elles n'existent pas déjà)
INSERT INTO rubrique_sections (id, category_id, dossier_id, nom, description, `order`, type, structure, created_at, updated_at)
SELECT 
    rs.id,
    rs.category_id,
    rs.dossier_id,
    rs.nom,
    rs.description,
    rs.order,
    rs.type,
    rs.structure,
    rs.created_at,
    rs.updated_at
FROM (
    -- Remplacez cette sous-requête par vos données réelles
    SELECT 1 as id, 1 as category_id, NULL as dossier_id, 'Section 1' as nom, NULL as description, 1 as `order`, 'section' as type, NULL as structure, NOW() as created_at, NOW() as updated_at
) rs
WHERE NOT EXISTS (
    SELECT 1 FROM rubrique_sections WHERE rubrique_sections.id = rs.id
);

-- IMPORT DES PARTITIONS (INSERT ou UPDATE selon l'ID)
INSERT INTO partitions (
    id, title, description, files, category_id, rubrique_section_id, messe_part,
    reference_id, messe_id, chorale_id, pupitre_id, pupitre,
    audio_path, pdf_path, image_path, audio_files, pdf_files, image_files,
    created_at, updated_at
)
SELECT 
    p.id,
    p.title,
    p.description,
    p.files,
    p.category_id,
    p.rubrique_section_id,
    p.messe_part,
    p.reference_id,
    p.messe_id,
    p.chorale_id,
    p.pupitre_id,
    p.pupitre,
    p.audio_path,
    p.pdf_path,
    p.image_path,
    p.audio_files,
    p.pdf_files,
    p.image_files,
    p.created_at,
    p.updated_at
FROM (
    -- Remplacez cette sous-requête par vos données réelles exportées
    SELECT 
        1 as id,
        'Titre de la partition' as title,
        'Description de la partition' as description,
        '["path/to/file1.pdf", "path/to/file2.mp3"]' as files,
        1 as category_id,
        NULL as rubrique_section_id,
        '{"part": "Kyrié", "subPart": null}' as messe_part,
        1 as reference_id,
        1 as messe_id,
        1 as chorale_id,
        NULL as pupitre_id,
        'TUTTI' as pupitre,
        NULL as audio_path,
        NULL as pdf_path,
        NULL as image_path,
        NULL as audio_files,
        NULL as pdf_files,
        NULL as image_files,
        NOW() as created_at,
        NOW() as updated_at
) p
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    files = VALUES(files),
    category_id = VALUES(category_id),
    rubrique_section_id = VALUES(rubrique_section_id),
    messe_part = VALUES(messe_part),
    reference_id = VALUES(reference_id),
    messe_id = VALUES(messe_id),
    chorale_id = VALUES(chorale_id),
    pupitre_id = VALUES(pupitre_id),
    pupitre = VALUES(pupitre),
    audio_path = VALUES(audio_path),
    pdf_path = VALUES(pdf_path),
    image_path = VALUES(image_path),
    audio_files = VALUES(audio_files),
    pdf_files = VALUES(pdf_files),
    image_files = VALUES(image_files),
    updated_at = NOW();

-- =====================================================
-- ALTERNATIVE: EXPORT EN FORMAT SQL INSERT DIRECT
-- =====================================================
-- Cette version génère directement les INSERT statements

SELECT CONCAT(
    'INSERT INTO partitions (id, title, description, files, category_id, rubrique_section_id, messe_part, reference_id, messe_id, chorale_id, pupitre_id, pupitre, audio_path, pdf_path, image_path, audio_files, pdf_files, image_files, created_at, updated_at) VALUES (',
    p.id, ', ',
    QUOTE(p.title), ', ',
    IFNULL(QUOTE(p.description), 'NULL'), ', ',
    IFNULL(QUOTE(p.files), 'NULL'), ', ',
    IFNULL(p.category_id, 'NULL'), ', ',
    IFNULL(p.rubrique_section_id, 'NULL'), ', ',
    IFNULL(QUOTE(p.messe_part), 'NULL'), ', ',
    IFNULL(p.reference_id, 'NULL'), ', ',
    IFNULL(p.messe_id, 'NULL'), ', ',
    IFNULL(p.chorale_id, 'NULL'), ', ',
    IFNULL(p.pupitre_id, 'NULL'), ', ',
    IFNULL(QUOTE(p.pupitre), 'NULL'), ', ',
    IFNULL(QUOTE(p.audio_path), 'NULL'), ', ',
    IFNULL(QUOTE(p.pdf_path), 'NULL'), ', ',
    IFNULL(QUOTE(p.image_path), 'NULL'), ', ',
    IFNULL(QUOTE(p.audio_files), 'NULL'), ', ',
    IFNULL(QUOTE(p.pdf_files), 'NULL'), ', ',
    IFNULL(QUOTE(p.image_files), 'NULL'), ', ',
    QUOTE(p.created_at), ', ',
    QUOTE(p.updated_at),
    ');'
) AS insert_statement
FROM partitions p
WHERE p.messe_id IS NOT NULL
ORDER BY p.messe_id, p.category_id, p.created_at;

