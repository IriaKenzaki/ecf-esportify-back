/* Ce fichier crée la base de données 'esportify' et y insèrer une collection et des documents .
Déplacez vous dans votre dossier Esportify/database .
Pour exécuter ce fichier, lancez la commande suivante dans un terminal avec Mongodb :
mongosh 
use esportify;
load ("setup.js");
db.avis.find().pretty(); pour afficher les documents.
Le fichier sera exécuté pour créer la base et insérer les données.
Ce fichier est destiné à une utilisation dans un environnement de développement local. */ 

db.createCollection("avis"); 

db.avis.insertMany([
  {
    title: "Premier avis",
    content: "Contenu du premier avis.",
    rating: 4,
    createdAt: new ISODate("2025-02-01T10:00:00Z"),
    user: "Andréa"
  },
  {
    title: "Deuxième avis",
    content: "Contenu du deuxième avis.",
    rating: 5,
    createdAt: new ISODate("2025-02-02T12:30:00Z"),
    user: "Camille"
  }
]);
