CREATE TABLE Categories (
    IdCategorie BIGSERIAL PRIMARY KEY,
    Statut VARCHAR(50) DEFAULT 'Dépublié' CHECK (Statut IN ('Dépublié', 'Publié')),
    Descriptions TEXT,
    Nom VARCHAR(100) NOT NULL
);

CREATE TABLE Tags (
    IdTag BIGSERIAL PRIMARY KEY,
    Nom VARCHAR(100) NOT NULL
);



CREATE TABLE ProgrammeFormation (
    IdProgrammeFormation BIGSERIAL PRIMARY KEY,
    Titre VARCHAR(155) NOT NULL,
    LienPhoto TEXT,
    Statut VARCHAR(50) DEFAULT 'Dépublié' CHECK (Statut IN ('Dépublié', 'Publié')),
    StatutVerrouillageProgression VARCHAR(50) DEFAULT 'Verrouillé' CHECK (StatutVerrouillageProgression IN ('Verrouillé', 'Déverrouillé')),
    Descriptions TEXT,
    Prix DECIMAL(10, 2),
    PointsOfferts INT DEFAULT 0,
    IdCategorie BIGINT REFERENCES Categories(IdCategorie) ON DELETE SET NULL,
    idAuteur BIGINT NOT NULL REFERENCES Utilisateurs(IdUtilisateur) ON DELETE CASCADE,
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE Themes (
    IdTheme BIGSERIAL PRIMARY KEY,
    IdProgramme BIGINT NOT NULL REFERENCES ProgrammeFormation(IdProgrammeFormation) ON DELETE CASCADE,
    Titre VARCHAR(255) NOT NULL,
    Descriptions TEXT,
    Statut VARCHAR(50) DEFAULT 'Dépublié' CHECK (Statut IN ('Dépublié', 'Publié')),
    Ordre INT DEFAULT 0,
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE Lecons (
    IdLecon BIGSERIAL PRIMARY KEY,
    IdProgramme BIGINT NOT NULL REFERENCES ProgrammeFormation(IdProgrammeFormation) ON DELETE CASCADE,
    IdTheme BIGINT REFERENCES Themes(IdTheme) ON DELETE CASCADE,
    Titre VARCHAR(155) NOT NULL,
    Statut VARCHAR(50) DEFAULT 'Dépublié' CHECK (Statut IN ('Dépublié', 'Publié')),
    Descriptions TEXT,
    TypeLecon VARCHAR(255) NOT NULL CHECK (TypeLecon IN ('Vidéo', 'PDF', 'Texte')),
    SourceType VARCHAR(255) NOT NULL CHECK (SourceType IN ('Youtube', 'Cloudinary', 'Local', 'Lien_Externe', 'Interne')),
    Contenu TEXT NOT NULL,
    DureeVideo INT,
    NombreDePages INT,
    Ordre INT DEFAULT 0,
    PointsOfferts INT DEFAULT 0,
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE Consultations (
    IdConsultation BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT NOT NULL REFERENCES Utilisateurs(IdUtilisateur) ON DELETE CASCADE,
    Titre VARCHAR(155),
    Question TEXT,
    Statut VARCHAR(255) DEFAULT 'Ouverte' CHECK (Statut IN ('Ouverte', 'En cours', 'Fermée')),
    IdLecon BIGINT REFERENCES Lecons(IdLecon) ON DELETE SET NULL,
    IdCategorie BIGINT REFERENCES Categories(IdCategorie) ON DELETE SET NULL,
    SourceType VARCHAR(50) DEFAULT 'Formulaire' CHECK (SourceType IN ('Lecon', 'Formulaire')),
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ReponseConsultations (
    IdReponseConsultation BIGSERIAL PRIMARY KEY,
    IdCategorie BIGINT REFERENCES Categories(IdCategorie) ON DELETE SET NULL,
    Titre VARCHAR(255),
    Descriptions TEXT,
    LienVideo TEXT,
    Statut VARCHAR(50) DEFAULT 'Publié' CHECK (Statut IN ('Publié', 'Dépublié')),
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ReponseConsultation_Items (
    IdReponseConsultation BIGINT REFERENCES ReponseConsultations(IdReponseConsultation) ON DELETE CASCADE,
    IdConsultation BIGINT REFERENCES Consultations(IdConsultation) ON DELETE CASCADE,
    PRIMARY KEY (IdReponseConsultation, IdConsultation)
);

CREATE TABLE QuestionsLibres (
    IdQuestionLibre BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT NOT NULL REFERENCES Utilisateurs(IdUtilisateur) ON DELETE CASCADE,
    IdProgramme BIGINT REFERENCES ProgrammeFormation(IdProgrammeFormation) ON DELETE CASCADE,
    IdLecon BIGINT REFERENCES Lecons(IdLecon) ON DELETE CASCADE,
    IdConsultation BIGINT REFERENCES Consultations(IdConsultation) ON DELETE CASCADE,
    Titre VARCHAR(255),
    ContenuQuestion TEXT NOT NULL,
    IdCategorie BIGINT REFERENCES Categories(IdCategorie) ON DELETE SET NULL,
    TypeReponse VARCHAR(255) CHECK (TypeReponse IN ('Texte', 'Vidéo', 'Consultation')),
    ContenuReponse TEXT,
    StatutReponse VARCHAR(255) DEFAULT 'En attente' CHECK (StatutReponse IN ('En attente', 'Répondu')),
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateReponse TIMESTAMP,
    CONSTRAINT Question_Cible CHECK (IdProgramme IS NOT NULL OR IdLecon IS NOT NULL OR IdConsultation IS NOT NULL)
);

CREATE TABLE QuestionsTags (
    IdQuestion BIGINT REFERENCES QuestionsLibres(IdQuestionLibre) ON DELETE CASCADE,
    IdTag BIGINT REFERENCES Tags(IdTag) ON DELETE CASCADE,
    PRIMARY KEY (IdQuestion, IdTag)
);

CREATE TABLE Etapes (
    IdEtape BIGSERIAL PRIMARY KEY,
    IdLecon BIGINT NOT NULL REFERENCES Lecons(IdLecon) ON DELETE CASCADE,
    Titre VARCHAR(155) NOT NULL,
    Descriptions TEXT,
    Statut VARCHAR(50) DEFAULT 'Dépublié' CHECK (Statut IN ('Dépublié', 'Publié')),
    TypeEtape VARCHAR(255) NOT NULL CHECK (TypeEtape IN ('Quiz', 'QuestionReponse', 'Cocher')),
    Ordre INT DEFAULT 0,
    PointsOfferts INT DEFAULT 0,
    NecessiteValidationAdmin BOOLEAN DEFAULT FALSE,
    DateCreation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateMiseAJour TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE QuestionsEtapes (
    IdQuestion BIGSERIAL PRIMARY KEY,
    IdEtape BIGINT NOT NULL REFERENCES Etapes(IdEtape) ON DELETE CASCADE,
    Intitule TEXT NOT NULL,
    TypeQuestion VARCHAR(255) NOT NULL CHECK (TypeQuestion IN ('Unique', 'Multiple', 'Ouverte')),
    Ordre INT DEFAULT 0
);

CREATE TABLE OptionsQuestions (
    IdOption BIGSERIAL PRIMARY KEY,
    IdQuestion BIGINT NOT NULL REFERENCES QuestionsEtapes(IdQuestion) ON DELETE CASCADE,
    TexteOption TEXT NOT NULL,
    EstCorrecte BOOLEAN DEFAULT FALSE,
    Ordre INT DEFAULT 0
);

CREATE TABLE ReponsesEtapesUtilisateur (
    IdReponse BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT NOT NULL REFERENCES Utilisateurs(IdUtilisateur),
    IdEtape BIGINT NOT NULL REFERENCES Etapes(IdEtape),
    StatutValidation VARCHAR(255) DEFAULT 'En attente' CHECK (StatutValidation IN ('En attente', 'Validé', 'Refusé')),
    ReponseAdmin TEXT,
    DateEnvoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateValidation TIMESTAMP,
    CONSTRAINT Unique_Reponse_Utilisateur UNIQUE (IdUtilisateur, IdEtape)
);

CREATE TABLE DetailsReponsesUtilisateur (
    IdDetail BIGSERIAL PRIMARY KEY,
    IdReponse BIGINT NOT NULL REFERENCES ReponsesEtapesUtilisateur(IdReponse) ON DELETE CASCADE,
    IdQuestion BIGINT NOT NULL REFERENCES QuestionsEtapes(IdQuestion),
    TexteReponse TEXT, 
    IdOptionChoisie BIGINT REFERENCES OptionsQuestions(IdOption)
);

CREATE TABLE ProgressionUtilisateur (
    IdProgression BIGSERIAL PRIMARY KEY,
    IdUtilisateur BIGINT NOT NULL REFERENCES Utilisateurs(IdUtilisateur),  
    IdProgramme BIGINT REFERENCES ProgrammeFormation(IdProgrammeFormation),
    IdLecon BIGINT REFERENCES Lecons(IdLecon),
    IdEtape BIGINT REFERENCES Etapes(IdEtape),  
    EstTermine BOOLEAN DEFAULT FALSE,
    PointsGagnes INT DEFAULT 0,
    DateCompletion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT Unique_Progression UNIQUE (IdUtilisateur, IdProgramme, IdLecon, IdEtape)
);

CREATE TABLE Reussite (
    IdReussite BIGSERIAL PRIMARY KEY,
    Nom VARCHAR(255) NOT NULL,
    Descriptions TEXT,
    LienIcone TEXT,
    TypeDeclencheur VARCHAR(255) CHECK (TypeDeclencheur IN ('PointsGlobal', 'FinProgramme', 'FinLecon')),
    SeuilPoints INT, 
    IdReference BIGINT,
    Statut VARCHAR(20) DEFAULT 'Publié'
);

CREATE TABLE BadgesUtilisateur (
    IdUtilisateur BIGINT NOT NULL REFERENCES Utilisateurs(IdUtilisateur),
    IdReussite BIGINT NOT NULL REFERENCES Reussite(IdReussite),
    DateObtention TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (IdUtilisateur, IdReussite)
);

CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INT NOT NULL
);

CREATE TABLE IF NOT EXISTS cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
);