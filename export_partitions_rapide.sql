-- =====================================================
-- REQUÊTE D'EXPORT RAPIDE - PARTITIONS & MESSES
-- =====================================================
-- Cette requête exporte toutes les partitions liées aux messes
-- avec leurs fichiers et toutes les données essentielles
-- 
-- UTILISATION:
-- 1. Exécutez cette requête dans votre base de données source
-- 2. Exportez les résultats vers un fichier CSV ou SQL
-- 3. Utilisez les données pour l'import dans votre nouvelle base

-- REQUÊTE PRINCIPALE D'EXPORT
SELECT 
    -- Identifiants et relations
    p.id,
    p.category_id,
    p.messe_id,
    p.reference_id,
    p.rubrique_section_id,
    p.chorale_id,
    p.pupitre_id,
    
    -- Informations de base
    p.title,
    p.description,
    
    -- FICHIERS (champ principal - JSON)
    p.files AS fichiers_json,
    
    -- Informations de messe
    p.messe_part AS partie_messe_json,
    p.pupitre,
    
    -- Anciens champs fichiers (rétrocompatibilité)
    p.audio_path AS chemin_audio,
    p.pdf_path AS chemin_pdf,
    p.image_path AS chemin_image,
    p.audio_files AS fichiers_audio_json,
    p.pdf_files AS fichiers_pdf_json,
    p.image_files AS fichiers_image_json,
    
    -- Dates
    p.created_at,
    p.updated_at,
    
    -- Données liées (pour référence et vérification)
    c.name AS nom_categorie,
    m.name AS nom_messe,
    r.name AS nom_reference,
    rs.nom AS nom_section_rubrique,
    ch.name AS nom_chorale
    
FROM partitions p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN messes m ON p.messe_id = m.id
LEFT JOIN references r ON p.reference_id = r.id
LEFT JOIN rubrique_sections rs ON p.rubrique_section_id = rs.id
LEFT JOIN chorales ch ON p.chorale_id = ch.id

WHERE p.messe_id IS NOT NULL  -- Uniquement les partitions liées aux messes

ORDER BY 
    p.messe_id ASC,
    p.category_id ASC,
    p.created_at ASC;

-- =====================================================
-- VERSION POUR EXPORT CSV
-- =====================================================
-- Si vous voulez exporter vers CSV, utilisez cette commande:
-- SELECT ... FROM ... INTO OUTFILE '/chemin/vers/fichier.csv'
-- FIELDS TERMINATED BY ',' ENCLOSED BY '"' ESCAPED BY '\\'
-- LINES TERMINATED BY '\n';

-- =====================================================
-- VERSION POUR GÉNÉRER LES INSERT DIRECTEMENT
-- =====================================================
-- Cette version génère les instructions INSERT SQL prêtes à copier-coller

SELECT CONCAT(
    'INSERT INTO partitions (id, title, description, files, category_id, rubrique_section_id, messe_part, reference_id, messe_id, chorale_id, pupitre_id, pupitre, audio_path, pdf_path, image_path, audio_files, pdf_files, image_files, created_at, updated_at) VALUES (',
    p.id, ', ',
    IFNULL(CONCAT('''', REPLACE(COALESCE(p.title, ''), '''', ''''''), ''''), 'NULL'), ', ',
    IFNULL(CONCAT('''', REPLACE(COALESCE(p.description, ''), '''', ''''''), ''''), 'NULL'), ', ',
    IFNULL(CONCAT('''', REPLACE(COALESCE(p.files, ''), '''', ''''''), ''''), 'NULL'), ', ',
    IFNULL(CAST(p.category_id AS CHAR), 'NULL'), ', ',
    IFNULL(CAST(p.rubrique_section_id AS CHAR), 'NULL'), ', ',
    IFNULL(CONCAT('''', REPLACE(COALESCE(p.messe_part, ''), '''', ''''''), ''''), 'NULL'), ', ',
    IFNULL(CAST(p.reference_id AS CHAR), 'NULL'), ', ',
    IFNULL(CAST(p.messe_id AS CHAR), 'NULL'), ', ',
    IFNULL(CAST(p.chorale_id AS CHAR), 'NULL'), ', ',
    IFNULL(CAST(p.pupitre_id AS CHAR), 'NULL'), ', ',
    IFNULL(CONCAT('''', REPLACE(COALESCE(p.pupitre, ''), '''', ''''''), ''''), 'NULL'), ', ',
    IFNULL(CONCAT('''', REPLACE(COALESCE(p.audio_path, ''), '''', ''''''), ''''), 'NULL'), ', ',
    IFNULL(CONCAT('''', REPLACE(COALESCE(p.pdf_path, ''), '''', ''''''), ''''), 'NULL'), ', ',
    IFNULL(CONCAT('''', REPLACE(COALESCE(p.image_path, ''), '''', ''''''), ''''), 'NULL'), ', ',
    IFNULL(CONCAT('''', REPLACE(COALESCE(p.audio_files, ''), '''', ''''''), ''''), 'NULL'), ', ',
    IFNULL(CONCAT('''', REPLACE(COALESCE(p.pdf_files, ''), '''', ''''''), ''''), 'NULL'), ', ',
    IFNULL(CONCAT('''', REPLACE(COALESCE(p.image_files, ''), '''', ''''''), ''''), 'NULL'), ', ',
    CONCAT('''', DATE_FORMAT(p.created_at, '%Y-%m-%d %H:%i:%s'), ''''), ', ',
    CONCAT('''', DATE_FORMAT(p.updated_at, '%Y-%m-%d %H:%i:%s'), ''''),
    ');'
) AS insert_statements
FROM partitions p
WHERE p.messe_id IS NOT NULL
ORDER BY p.messe_id, p.category_id, p.created_at;

-- =====================================================
-- EXPORT DES DONNÉES LIÉES (pour import complet)
-- =====================================================

-- 1. Catégories
SELECT * FROM categories 
WHERE id IN (SELECT DISTINCT category_id FROM partitions WHERE messe_id IS NOT NULL)
ORDER BY id;

-- 2. Messes
SELECT * FROM messes 
WHERE id IN (SELECT DISTINCT messe_id FROM partitions WHERE messe_id IS NOT NULL)
ORDER BY id;

-- 3. Références
SELECT * FROM references 
WHERE id IN (SELECT DISTINCT reference_id FROM partitions WHERE messe_id IS NOT NULL AND reference_id IS NOT NULL)
ORDER BY id;

-- 4. Sections de rubrique
SELECT * FROM rubrique_sections 
WHERE id IN (SELECT DISTINCT rubrique_section_id FROM partitions WHERE messe_id IS NOT NULL AND rubrique_section_id IS NOT NULL)
ORDER BY id;

