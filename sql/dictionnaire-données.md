| Attribut    | Signification             | Type         |
|-------------|---------------------------|--------------|
| idUtil      | Numéro d'un utilisateur   | int          |
| nomUtil     | Nom de l'utilisateur      | VARCHAR(30)  |
| prenomUtil  | Prénom de l'utilisateur   | VARCHAR(30)  |
| emailUtil   | Adresse e-mail            | VARCHAR(60)  |
| mdpUtil     | Mot de passe haché de util| VARCHAR(255) |
| roleUtil    | Role de l'utilisateur     | int          |
| nbTouites   | Nombre de touites créés   | int          |
| idTouite    | Numéro d'un touite        | int          |
| texteTouite | Texte du touite           | VARCHAR(235) |
| datePub     | Date de publication       | DATETIME     |
| idTag       | Numéro d'un tag           | int          |
| libelleTag  | Libellé du tag            | VARCHAR(30)  |
| descTag     | Description du tag        | VARCHAR(100) |
| idImage     | Numéro d'une image        | int          |
| descImage   | Description de l'image    | VARCHAR(100) |
| cheminImage | Chemin du fichier image   | VARCHAR(255) |
| nbEval      | Nombre d'évaluation(like) | int          |
| valeur      | Évaluation d'un touite    | int          |  (1 = like, -1 = dislike)
