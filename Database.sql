-- Table Principale : Utilisateurs
-- Gère l'authentification (Admin et Utilisateurs standards)
CREATE TABLE Utilisateurs (
    IdUtilisateur BIGSERIAL PRIMARY KEY,
    Email VARCHAR(255) UNIQUE NOT NULL,
    MotDePasseHash VARCHAR(255) NULL, -- Null pour les connexions Google pures
    GoogleId VARCHAR(255) UNIQUE NULL, -- Identifiant unique Google
    Role VARCHAR(50) DEFAULT 'utilisateur' CHECK (Role IN ('utilisateur', 'admin')),
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table Secondaire : ProfilsUtilisateurs
CREATE TABLE ProfilsUtilisateurs (
    IdProfil BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT UNIQUE NOT NULL REFERENCES Utilisateurs(IdUtilisateur) ON DELETE CASCADE,
    Prenom VARCHAR(100),
    Nom VARCHAR(100),
    Metier VARCHAR(150),
    PhotoProfil TEXT, 
    NumeroTelephone VARCHAR(50),
    Adresse TEXT,
    Biographie TEXT,
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Index pour optimiser les performances de recherche
CREATE INDEX IdxEmailUtilisateur ON Utilisateurs(Email);
CREATE INDEX IdxGoogleIdUtilisateur ON Utilisateurs(GoogleId);