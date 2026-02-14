CREATE TABLE TypeDeCoaching 
(
    IdTypeCoaching BIGSERIAL PRIMARY KEY,
    NomDeType VARCHAR(100) NOT NULL,
    Descriptions TEXT,
    Prix DECIMAL(10, 2) DEFAULT 0,
    Statut VARCHAR(50) DEFAULT 'Dépublié' CHECK (Statut IN ('Dépublié', 'Publié')),
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE DisponibiliteCoaching
(
    IdDisponibilite BIGSERIAL PRIMARY KEY,
    DateDisponible DATE NOT NULL,
    HeureDebut TIME NOT NULL,
    HeureFin TIME NOT NULL,
    EstReserve BOOLEAN DEFAULT FALSE,
    BlockedByReservationId BIGINT,
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_slot UNIQUE (DateDisponible, HeureDebut)
);

CREATE INDEX idx_dispo_heure_debut ON DisponibiliteCoaching(HeureDebut);

CREATE TABLE ReservationCoaching
(
    IdReservation BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT NOT NULL,
    IdTypeCoaching BIGINT REFERENCES TypeDeCoaching(IdTypeCoaching),
    IdDisponibilite BIGINT REFERENCES DisponibiliteCoaching(IdDisponibilite) ON DELETE SET NULL,
    StatutReservation VARCHAR(50) DEFAULT 'En attente' CHECK (StatutReservation IN ('En attente', 'Confirmé', 'Annulé', 'Terminé')),
    NoteUtilisateur TEXT,
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ReplayCoaching
(
    IdReplay BIGSERIAL PRIMARY KEY,
    IdReservation BIGINT REFERENCES ReservationCoaching(IdReservation),
    Titre VARCHAR(255),
    LienVideo TEXT,
    DateUpload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS lienGoogle
(
    LienGoogleMeet TEXT
);


-- Supprimer l'ancienne contrainte unique (nom exact défini à la ligne 21)
ALTER TABLE "DisponibiliteCoaching" 
DROP CONSTRAINT IF EXISTS "unique_slot";

-- Créer un index unique composite qui permet plusieurs créneaux le même jour
-- mais empêche les doublons exactes (même date + même début + même fin)
CREATE UNIQUE INDEX IF NOT EXISTS idx_disponibilite_unique 
ON "DisponibiliteCoaching" ("DateDisponible", "HeureDebut", "HeureFin");

-- OU si vous voulez juste permettre plusieurs créneaux sans contrainte :
-- Ne pas recréer de contrainte unique, juste un index pour les performances
CREATE INDEX IF NOT EXISTS idx_disponibilite_date 
ON "DisponibiliteCoaching" ("DateDisponible");


-- Fonction pour nettoyer les disponibilités expirées
CREATE OR REPLACE FUNCTION clean_expired_availabilities()
RETURNS TABLE(deleted_count BIGINT, message TEXT) AS $$
DECLARE
    deleted BIGINT;
BEGIN
    -- Supprime les disponibilités dont la date est passée
    DELETE FROM "DisponibiliteCoaching" 
    WHERE "DateDisponible" < CURRENT_DATE;
    
    -- Récupère le nombre de lignes supprimées
    GET DIAGNOSTICS deleted = ROW_COUNT;
    
    -- Retourne le résultat
    RETURN QUERY SELECT deleted, 
        CASE 
            WHEN deleted = 0 THEN 'Aucune disponibilité expirée trouvée'
            WHEN deleted = 1 THEN '1 disponibilité expirée supprimée'
            ELSE deleted || ' disponibilités expirées supprimées'
        END;
END;
$$ LANGUAGE plpgsql;

-- Pour l'utiliser :
SELECT * FROM clean_expired_availabilities();


-- Fonction pour mettre à jour les statuts des réservations passées
-- Confirmé -> Terminé, En attente -> Terminé
CREATE OR REPLACE FUNCTION update_reservation_statuses() RETURNS text AS $$
DECLARE
    termines_count INT;
BEGIN
    -- Mettre à jour les réservations passées (Confirmé ou En attente) en 'Terminé'
    WITH updated AS (
        UPDATE "ReservationCoaching" RC
        SET "StatutReservation" = 'Terminé'
        FROM "DisponibiliteCoaching" DC
        WHERE RC."IdDisponibilite" = DC."IdDisponibilite"
        AND DC."DateDisponible" < CURRENT_DATE
        AND RC."StatutReservation" IN ('Confirmé', 'En attente')
        RETURNING 1
    )
    SELECT COUNT(*) INTO termines_count FROM updated;

    RETURN 'Mise à jour effectuée : ' || termines_count || ' réservations passées passées à Terminé.';
END;
$$ LANGUAGE plpgsql;

-- Exemple d'utilisation :
SELECT update_reservation_statuses();




CREATE OR REPLACE FUNCTION update_expired_reservation_status() RETURNS text AS $$
DECLARE
    terminated_count INT;
    cancelled_count INT;
BEGIN
    -- Update Confirmed -> Terminé
    WITH rows AS (
        UPDATE "ReservationCoaching" rc
        SET "StatutReservation" = 'Terminé'
        FROM "DisponibiliteCoaching" dc
        WHERE rc."IdDisponibilite" = dc."IdDisponibilite"
        AND dc."DateDisponible" < CURRENT_DATE
        AND rc."StatutReservation" = 'Confirmé'
        RETURNING 1
    )
    SELECT count(*) INTO terminated_count FROM rows;

    -- Update En attente -> Terminé (formerly Annulé)
    WITH rows AS (
        UPDATE "ReservationCoaching" rc
        SET "StatutReservation" = 'Terminé'
        FROM "DisponibiliteCoaching" dc
        WHERE rc."IdDisponibilite" = dc."IdDisponibilite"
        AND dc."DateDisponible" < CURRENT_DATE
        AND rc."StatutReservation" = 'En attente'
        RETURNING 1
    )
    SELECT count(*) INTO cancelled_count FROM rows;
    
    RETURN 'Mise à jour: ' || (terminated_count + cancelled_count) || ' terminés.';
END;
$$ LANGUAGE plpgsql;