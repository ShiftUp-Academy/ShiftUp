CREATE TABLE Paniers (
    IdPanier BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT UNIQUE NOT NULL REFERENCES Utilisateurs(IdUtilisateur) ON DELETE CASCADE,
    Statut VARCHAR(50) DEFAULT 'actif' CHECK (Statut IN ('actif', 'validé', 'abandonné')),
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE PanierItems (
    IdPanierItem BIGSERIAL PRIMARY KEY,
    IdPanier BIGINT NOT NULL REFERENCES Paniers(IdPanier) ON DELETE CASCADE,
    IdProgrammeFormation BIGINT REFERENCES ProgrammeFormation(IdProgrammeFormation) ON DELETE CASCADE,
    IdOffre BIGINT REFERENCES "Offres"("IdOffre") ON DELETE CASCADE,
    PrixAuMomentDeLAjout DECIMAL(10, 2),
    DateAjout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT Check_Type_Article CHECK (
        (IdProgrammeFormation IS NOT NULL AND IdOffre IS NULL) OR 
        (IdProgrammeFormation IS NULL AND IdOffre IS NOT NULL)
    )
);

CREATE INDEX IdPanierUtilisateur ON Paniers(IdUtilisateur);
CREATE INDEX IdPanierItems ON PanierItems(IdPanier);


CREATE TABLE Commandes (
    IdCommande BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT NOT NULL REFERENCES Utilisateurs(IdUtilisateur) ON DELETE CASCADE,
    Reference VARCHAR(100) UNIQUE NOT NULL,
    MontantTotal DECIMAL(10, 2) NOT NULL,
    Statut VARCHAR(50) DEFAULT 'En attente' CHECK (Statut IN ('En attente', 'Payé', 'Echoué', 'Remboursé')),
    DateCommande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE CommandeItems (
    IdCommandeItem BIGSERIAL PRIMARY KEY,
    IdCommande BIGINT NOT NULL REFERENCES Commandes(IdCommande) ON DELETE CASCADE,
    IdProgrammeFormation BIGINT REFERENCES ProgrammeFormation(IdProgrammeFormation) ON DELETE SET NULL,
    IdOffre BIGINT REFERENCES "Offres"("IdOffre") ON DELETE SET NULL,
    PrixAchat DECIMAL(10, 2) NOT NULL,
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT Check_Type_Item_Commande CHECK (
        (IdProgrammeFormation IS NOT NULL AND IdOffre IS NULL) OR 
        (IdProgrammeFormation IS NULL AND IdOffre IS NOT NULL)
    )
);

CREATE INDEX IdCommandeUtilisateur ON Commandes(IdUtilisateur);
CREATE INDEX IdCommandeItem ON CommandeItems(IdCommande);
