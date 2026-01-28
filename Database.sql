CREATE TABLE Utilisateurs 
(
    IdUtilisateur BIGSERIAL PRIMARY KEY,
    Email VARCHAR(255) UNIQUE NOT NULL,
    MotDePasseHash VARCHAR(255) NULL,
    GoogleId VARCHAR(255) UNIQUE NULL, 
    Role VARCHAR(50) DEFAULT 'utilisateur' CHECK (Role IN ('utilisateur', 'admin')),
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ProfilsUtilisateurs 
(
    IdProfil BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT UNIQUE NOT NULL REFERENCES Utilisateurs(IdUtilisateur) ON DELETE CASCADE,
    Prenom VARCHAR(100),
    Nom VARCHAR(100),
    Metier VARCHAR(150),
    EmailContact VARCHAR(100),
    PhotoProfil TEXT, 
    NumeroTelephone VARCHAR(50),
    Adresse TEXT,
    Biographie TEXT,
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ReseauxSociaux 
(
    IdRéseauSocial BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT NOT NULL REFERENCES ProfilsUtilisateurs(IdProfil) ON DELETE CASCADE,
    Nom VARCHAR(30),
    Lien TEXT
);

CREATE INDEX IdxEmailUtilisateur ON Utilisateurs(Email);
CREATE INDEX IdxGoogleIdUtilisateur ON Utilisateurs(GoogleId);



