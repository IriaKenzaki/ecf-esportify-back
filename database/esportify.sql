-- Ce fichier crée la base de données 'esportify' et y insère des données pour les utilisateurs et les événements.
-- Déplacez vous dans votre dossier Esportify/database .
-- Pour exécuter ce fichier, lancez la commande suivante dans un terminal avec MySQL :
-- mysql -u votre_utilisateur_mysql -p (cela vous demanderas votre mot de passe MySql) 
-- SOURCE esportify.sql
-- Entrez vos informations de connexion MySQL et ce fichier sera exécuté pour créer la base et insérer les données.
--
-- Ce fichier est destiné à une utilisation dans un environnement de développement local.


CREATE DATABASE esportify;
USE esportify;


CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    roles JSON NOT NULL, 
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL,
    username VARCHAR(64) UNIQUE DEFAULT NULL,
    api_token VARCHAR(255) UNIQUE NOT NULL,
    last_login DATETIME NULL DEFAULT NULL
);

CREATE TABLE event (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(64) NOT NULL,
    description VARCHAR(255) NOT NULL,
    players INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_at DATETIME NULL DEFAULT NULL,
    date_time_start DATETIME NOT NULL,
    date_time_end DATETIME NOT NULL,
    created_by VARCHAR(255) NOT NULL,
    image VARCHAR(255) NULL DEFAULT NULL,
    visibility BOOLEAN NOT NULL DEFAULT FALSE,
    game VARCHAR(64) NOT NULL,
    started BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (created_by) REFERENCES user(username)
);

CREATE TABLE list_participant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL, 
    FOREIGN KEY (event_id) REFERENCES event(id) ON DELETE CASCADE
);

CREATE TABLE list_participant_user (
    list_participant_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (user_id, list_participant_id),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (list_participant_id) REFERENCES list_participant(id) ON DELETE CASCADE
);

CREATE TABLE blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL, 
    user_id INT NOT NULL,
    FOREIGN KEY (event_id) REFERENCES event(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);

CREATE TABLE score (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    score INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES event(id) ON DELETE CASCADE
);

CREATE TABLE message (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    texte VARCHAR(255) NOT NULL,
    FOREIGN KEY (user) REFERENCES user(username)
);

INSERT INTO user (email, roles, password, username, api_token)
VALUES ('armin@admin.com', '["ROLE_ADMIN"]', '$2y$13$oJRvd5ZppZ45SMntENhwnOWlN3K2k/bjSu91E7Be6AE8JQK1r/NvW', 'Armin', '67000ff3d2ea255ea9bb3b343036251899f73c98');

INSERT INTO user (email, roles, password, username, api_token)
VALUES ('camille@orga.com', '["ROLE_ORGANISATEUR"]', '$2y$13$MXrHTgSIsOKTloC2WLrMb.q4eUkToa7NqbfJCsVsjlOzZfz43r2v6', 'Camille', 'f8946fbea5011c3ccff4a7692d40947867c9be78');

INSERT INTO user (email, roles, password, username, api_token)
VALUES ('andi@player.com', '["ROLE_USER"]', '$2y$13$5gqYAz20T7tBYnx9VqaXJuo.eNCHbYs.0dEbHCGshEHivt2idBrIW', 'Andréa', 'cc2f5d5ec0690be2c079d8ec0e3a1abeafa62676');


INSERT INTO event (title, description, players, created_at, date_time_start, date_time_end, created_by, image, visibility, game, started)
VALUES
('Jeux du Yams', 'Partie amical de Yams pour les passionnés du jeu.', 2, NOW(), '2025-02-15 10:00:00', '2025-02-15 14:00:00', 'Camille', 'tournoi_yams.jpg', TRUE, 'Yams', FALSE);

INSERT INTO event (title, description, players, created_at, date_time_start, date_time_end, created_by, image, visibility, game, started)
VALUES
('Pendu seras', 'Une partie pour tester votre réflexion au jeu du Pendu.', 3, NOW(), '2025-02-08 15:00:00', '2025-02-20 18:00:00', 'Armin', 'concours_pendu.jpg', TRUE, 'Pendu', TRUE);


INSERT INTO list_participant (event_id) 
VALUES (2); 

INSERT INTO list_participant_user (list_participant_id, user_id)
VALUES (1, (SELECT id FROM user WHERE username = 'Andréa')); 

INSERT INTO list_participant (event_id) 
VALUES (1);

INSERT INTO list_participant_user (list_participant_id, user_id)
VALUES (2, (SELECT id FROM user WHERE username = 'Camille'));

INSERT INTO blacklist (event_id, user_id)
VALUES (2, (SELECT id FROM user WHERE username = 'Andréa'));

INSERT INTO score (event_id, user_id, score)
VALUES (2, (SELECT id FROM user WHERE username = 'Andréa'), 100);

INSERT INTO message (user, title, texte)
VALUES ('Camille', 'Lancement du jeux du Yams', "Es ce que le jeux du Yams peu commencer plustôt?");

INSERT INTO message (user, title, texte)
VALUES ('Andréa', 'Mise à jour Pendu', "C'est possible de modifier le nombre de mot à trouver?");

