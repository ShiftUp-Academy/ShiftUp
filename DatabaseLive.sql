CREATE TABLE Lives (
    IdLive BIGSERIAL PRIMARY KEY,
    Titre VARCHAR(155) NOT NULL,
    IdCategorie BIGINT REFERENCES Categories(IdCategorie) ON DELETE SET NULL,
    Descriptions TEXT,
    LienPhoto TEXT,
    DateDebut TIMESTAMP NOT NULL,
    DateFin TIMESTAMP NOT NULL,
    LienGoogleMeet TEXT,
    LienReplay TEXT,
    IdAuteur BIGINT NOT NULL REFERENCES Utilisateurs(IdUtilisateur) ON DELETE CASCADE,
    Statut VARCHAR(50) DEFAULT 'Dépublié' CHECK (Statut IN ('Dépublié', 'Publié')),
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);
