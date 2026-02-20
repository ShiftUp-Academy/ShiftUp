CREATE TABLE IF NOT EXISTS "Offres" (
    "IdOffre" BIGSERIAL PRIMARY KEY,
    "Titre" VARCHAR(255) NOT NULL,
    "Descriptions" TEXT,
    "LienPhoto" TEXT,
    "ReductionGlobal" DECIMAL(5, 2) DEFAULT 0,
    "DureeJours" INTEGER,
    "Statut" VARCHAR(50) DEFAULT 'Dépublié' CHECK ("Statut" IN ('Publié', 'Dépublié')),
    "DateCreation" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "DateMiseAJour" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "OffreProgrammes" (
    "IdOffreProgramme" BIGSERIAL PRIMARY KEY,
    "IdOffre" BIGINT NOT NULL REFERENCES "Offres"("IdOffre") ON DELETE CASCADE,
    "IdProgrammeFormation" BIGINT NOT NULL REFERENCES "ProgrammeFormation"("IdProgrammeFormation") ON DELETE CASCADE,
    "ReductionSpecifique" DECIMAL(5, 2) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "OffreCoachings" (
    "IdOffreCoaching" BIGSERIAL PRIMARY KEY,
    "IdOffre" BIGINT NOT NULL REFERENCES "Offres"("IdOffre") ON DELETE CASCADE,
    "IdTypeCoaching" BIGINT NOT NULL REFERENCES "TypeDeCoaching"("IdTypeCoaching") ON DELETE CASCADE,
    "ReductionSpecifique" DECIMAL(5, 2) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS "OffresUtilisateurs" (
    "IdOffreUtilisateur" BIGSERIAL PRIMARY KEY,
    "IdOffre" BIGINT NOT NULL REFERENCES "Offres"("IdOffre") ON DELETE CASCADE,
    "IdUtilisateur" BIGINT NOT NULL REFERENCES "Utilisateurs"("IdUtilisateur") ON DELETE CASCADE,
    "DateAttribution" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
