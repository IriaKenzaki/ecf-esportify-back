# Guide de déploiment en local 
Ce readme est la pour vous guidez a l'installation et au déploiement de l'application Esortify en local pour teste et utiliser le backend.

# README - Déploiement local du backend

## Prérequis

- PHP 8.4.2 installé et configuré dans les variables d’environnement
- Composer installé
- Symfony CLI installé
- MySQL installé et en cours d’exécution
- MongoDB installé et en cours d’exécution
- Apache installé et en cours d'exécution

---

## Installation du backend en local

1. **Cloner le projet**

```bash
  git clone git@github.com:IriaKenzaki/ecf-esportify-back.git
  cd ecf-final-back
```
- ecf-esportify-back : Ce dépôt contient le backend du projet, il utilise Php, Symfony, Composer et Swagger.

2. **Installer les dépendances PHP**

```bash
  composer install
```

3. **Créer et configurer les bases de données**

- **MySQL** :

```bash
php bin/console d:d:c Esportify
```

- **MongoDB** :

```bash
mongosh
use esportify
```

4. **Configurer les fichiers `.env`**

Copier le fichier `.env` en `.env.local` et modifier les variables suivantes :

```
DATABASE_URL="mysql://root:password@127.0.0.1:3306/esportify"
MONGODB_URL="mongodb://localhost:27017"
MONGODB_DB=esportify
```

5. **Créer le schéma de la base de données MySQL**
   
```bash
  php bin/console doctrine:migrations:migrate
```
   
7. **Lancé des DataFixture**

```bash
  php bin/console d:f:l
```
Ce qui vas crée 3 compte utilisateur 

Joueur: Pseudo : Andréa
        Email : andi@player.com
        Mot de passe : @ndyA23!
Organisateur: Pseudo : Camille
              Email : camille@orga.com
              Mot de passe : C@mpa$$w0rd
Administrateur: Pseudo : Armin
                Email : armin@admin.com
                Mot de passe : Arm1npa$$

6. **Démarrer le serveur Symfony**

```bash
  symfony server:start -d
```

7. **Tester l'API**

Les routes sont documentées via Nelmio API Doc :

- Accéder à la documentation de l’API : `http://127.0.0.1:8000/api/doc`

8. **Notes supplémentaires**
   La base de données MySql peut être géré via MySql Workbench en local.
   La base de données MongoDb est disponible avec MongoDb Compass en local.

---
