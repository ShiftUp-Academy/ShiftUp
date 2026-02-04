CREATE TABLE Temoignage
(
    IdTemoignage BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT NOT NULL REFERENCES Utilisateurs(IdUtilisateur),
    Type VARCHAR(50) NOT NULL CHECK (Type IN ('Texte', 'Photo', 'Video')),
    ContenuTexte TEXT,
    CheminFichier TEXT,
    Statut VARCHAR(50) DEFAULT 'Publié' CHECK (Statut IN ('Publié', 'Dépublié')),
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
