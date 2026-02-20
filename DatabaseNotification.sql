CREATE TABLE notifications (
    Id UUID PRIMARY KEY,
    Type VARCHAR(255) NOT NULL,
    TypeDestinataire VARCHAR(255) NOT NULL,
    IdDestinataire BIGINT NOT NULL,
    Donnees TEXT NOT NULL,
    DateLecture TIMESTAMP NULL,
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX notifications_destinataire_index 
ON notifications (TypeDestinataire, IdDestinataire);
