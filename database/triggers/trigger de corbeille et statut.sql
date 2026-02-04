-- Fonction qui supprime définitivement les éléments soft-deleted depuis plus de 30 jours
CREATE OR REPLACE FUNCTION cleanup_soft_deleted_records()
RETURNS void AS $$
BEGIN
    -- Nettoyage de la table ProgrammeFormation
    DELETE FROM "ProgrammeFormation"
    WHERE "deleted_at" IS NOT NULL 
    AND "deleted_at" < NOW() - INTERVAL '30 days';

    -- Nettoyage de la table Lecons
    DELETE FROM "Lecons"
    WHERE "deleted_at" IS NOT NULL 
    AND "deleted_at" < NOW() - INTERVAL '30 days';

    -- Nettoyage de la table Etapes
    DELETE FROM "Etapes"
    WHERE "deleted_at" IS NOT NULL 
    AND "deleted_at" < NOW() - INTERVAL '30 days';

    RAISE NOTICE 'Nettoyage de la corbeille effectué: éléments supprimés depuis plus de 30 jours';
END;
$$ LANGUAGE plpgsql;


-- ========================================
-- TRIGGER 2: Changement automatique du statut des offres expirées
-- ========================================

-- Fonction qui vérifie et met à jour le statut des offres expirées
CREATE OR REPLACE FUNCTION check_and_expire_offers()
RETURNS TRIGGER AS $$
BEGIN
    -- Si le statut est "Publié" et que la durée est dépassée
    IF NEW."Statut" = 'Publié' AND NEW."DureeJours" IS NOT NULL THEN
        -- Calculer la date d'expiration
        IF (NEW."DateCreation" + (NEW."DureeJours" || ' days')::INTERVAL) < NOW() THEN
            -- Changer automatiquement le statut en "Dépublié"
            NEW."Statut" := 'Dépublié';
            RAISE NOTICE 'Offre % expirée et dépubliée automatiquement', NEW."IdOffre";
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger BEFORE UPDATE et BEFORE INSERT pour vérifier l'expiration
CREATE TRIGGER trigger_check_offer_expiration_on_update
BEFORE UPDATE ON "Offres"
FOR EACH ROW
EXECUTE FUNCTION check_and_expire_offers();

CREATE TRIGGER trigger_check_offer_expiration_on_insert
BEFORE INSERT ON "Offres"
FOR EACH ROW
EXECUTE FUNCTION check_and_expire_offers();

-- ========================================
-- Fonction pour mettre à jour les offres existantes expirées
-- ========================================

-- Fonction qui met à jour toutes les offres expirées
CREATE OR REPLACE FUNCTION update_expired_offers()
RETURNS void AS $$
BEGIN
    UPDATE "Offres"
    SET "Statut" = 'Dépublié',
        "DateMiseAJour" = NOW()
    WHERE "Statut" = 'Publié'
    AND "DureeJours" IS NOT NULL
    AND ("DateCreation" + ("DureeJours" || ' days')::INTERVAL) < NOW();

    RAISE NOTICE 'Offres expirées mises à jour';
END;
$$ LANGUAGE plpgsql;
