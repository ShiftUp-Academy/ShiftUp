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
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_slot UNIQUE (DateDisponible, HeureDebut)
);

CREATE TABLE ReservationCoaching
(
    IdReservation BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT NOT NULL,
    IdTypeCoaching BIGINT REFERENCES TypeDeCoaching(IdTypeCoaching),
    IdDisponibilite BIGINT REFERENCES DisponibiliteCoaching(IdDisponibilite),
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

CREATE TABLE lienGoogle
{
    LienGoogleMeet TEXT,
}