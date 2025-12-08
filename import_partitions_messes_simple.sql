-- =====================================================
-- IMPORT RAPIDE DES PARTITIONS LIÉES AUX MESSES
-- =====================================================
-- IMPORTEZ D'ABORD LES DONNÉES LIÉES (catégories, messes, etc.)
-- puis exécutez les INSERT des partitions

-- =====================================================
-- ÉTAPE 1: IMPORT DES CATÉGORIES (si nécessaire)
-- =====================================================
-- Remplacez les valeurs entre {} par vos données réelles
-- Utilisez INSERT IGNORE pour éviter les doublons

INSERT IGNORE INTO categories (id, name, description, color, icon, chorale_id, structure_type, structure_config, created_at, updated_at)
VALUES
    -- Exemple: (1, 'Messe', 'Catégorie pour les messes', '#FF5733', 'church', NULL, NULL, NULL, NOW(), NOW()),
    -- Ajoutez vos catégories ici
;

-- =====================================================
-- ÉTAPE 2: IMPORT DES MESSES (si nécessaire)
-- =====================================================

INSERT IGNORE INTO messes (id, name, description, color, icon, active, created_at, updated_at)
VALUES
    -- Exemple: (1, 'Messe St Gabriel', 'Description de la messe', '#3498db', 'music_note', 1, NOW(), NOW()),
    -- Ajoutez vos messes ici
;

-- =====================================================
-- ÉTAPE 3: IMPORT DES RÉFÉRENCES (si nécessaire)
-- =====================================================

INSERT IGNORE INTO references (id, name, description, order_position, category_id, created_at, updated_at)
VALUES
    -- Exemple: (1, 'Kyrié', NULL, '1', 1, NOW(), NOW()),
    -- Ajoutez vos références ici
;

-- =====================================================
-- ÉTAPE 4: IMPORT DES SECTIONS DE RUBRIQUE (si nécessaire)
-- =====================================================

INSERT IGNORE INTO rubrique_sections (id, category_id, dossier_id, nom, description, `order`, type, structure, created_at, updated_at)
VALUES
    -- Exemple: (1, 1, NULL, 'Section 1', NULL, 1, 'section', NULL, NOW(), NOW()),
    -- Ajoutez vos sections ici
;

-- =====================================================
-- ÉTAPE 5: IMPORT DES PARTITIONS
-- =====================================================
-- IMPORTANT: Remplacez les valeurs entre {} par vos données exportées
-- Le champ 'files' doit être un JSON valide (ex: '["path/to/file1.pdf", "path/to/file2.mp3"]')
-- Le champ 'messe_part' doit être un JSON valide (ex: '{"part": "Kyrié", "subPart": null}')

INSERT INTO partitions (
    id, 
    title, 
    description, 
    files, 
    category_id, 
    rubrique_section_id, 
    messe_part, 
    reference_id, 
    messe_id, 
    chorale_id, 
    pupitre_id, 
    pupitre, 
    audio_path, 
    pdf_path, 
    image_path, 
    audio_files, 
    pdf_files, 
    image_files, 
    created_at, 
    updated_at
) VALUES
    -- FORMAT D'EXEMPLE:
    -- (
    --     1,                                                      -- id
    --     'Titre de la partition',                                -- title
    --     'Description de la partition',                          -- description
    --     '["path/to/file1.pdf", "path/to/file2.mp3"]',          -- files (JSON array)
    --     1,                                                      -- category_id
    --     NULL,                                                   -- rubrique_section_id (ou ID si existe)
    --     '{"part": "Kyrié", "subPart": null}',                  -- messe_part (JSON)
    --     1,                                                      -- reference_id (ou NULL)
    --     1,                                                      -- messe_id
    --     1,                                                      -- chorale_id (ou NULL)
    --     NULL,                                                   -- pupitre_id (ou ID si existe)
    --     'TUTTI',                                                -- pupitre (SOPRANE, TENOR, MEZOSOPRANE, ALTO, BASSE, BARITON, TUTTI)
    --     NULL,                                                   -- audio_path (ancien format, peut être NULL)
    --     NULL,                                                   -- pdf_path (ancien format, peut être NULL)
    --     NULL,                                                   -- image_path (ancien format, peut être NULL)
    --     NULL,                                                   -- audio_files (JSON, ancien format, peut être NULL)
    --     NULL,                                                   -- pdf_files (JSON, ancien format, peut être NULL)
    --     NULL,                                                   -- image_files (JSON, ancien format, peut être NULL)
    --     '2025-01-01 00:00:00',                                  -- created_at
    --     '2025-01-01 00:00:00'                                   -- updated_at
    -- ),
    -- Ajoutez toutes vos partitions ici, une par ligne
;

-- =====================================================
-- VERSION AVEC ON DUPLICATE KEY UPDATE
-- =====================================================
-- Utilisez cette version si vous voulez mettre à jour les partitions existantes

INSERT INTO partitions (
    id, title, description, files, category_id, rubrique_section_id, messe_part,
    reference_id, messe_id, chorale_id, pupitre_id, pupitre,
    audio_path, pdf_path, image_path, audio_files, pdf_files, image_files,
    created_at, updated_at
) VALUES
    -- Ajoutez vos partitions ici (même format que ci-dessus)
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
-- NOTES IMPORTANTES
-- =====================================================
-- 1. Les fichiers doivent exister physiquement dans le dossier storage/app/public/
-- 2. Les chemins dans le champ 'files' doivent être relatifs au dossier storage/app/public/
-- 3. Le format JSON pour 'files': ["path1", "path2", "path3"]
-- 4. Le format JSON pour 'messe_part': {"part": "Nom de la partie", "subPart": null ou "Nom sous-partie"}
-- 5. Vérifiez que tous les IDs référencés (category_id, messe_id, etc.) existent dans leurs tables respectives
-- 6. Si vous utilisez INSERT IGNORE, les partitions existantes seront ignorées
-- 7. Si vous utilisez ON DUPLICATE KEY UPDATE, les partitions existantes seront mises à jour

