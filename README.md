# Touiteur.app - SAE Développer une application web sécurisée

## Groupe de TD :
    - ALIOUANE Nassim
    - PASHO Kevin
    - FANARA Romain
    - HAMED OMER Adnan
    - RODRIGUES-NETO Gabriel

## Lien

- Dépôt git : https://github.com/na2s78/sae-developper-une-application-web-securisee


## Installation

1. Créer une base MySQL nommée `touiteur`

2. Exécuter dans l'ordre les scripts du dossier `sql/` :
   1. `script-SQL` 
   2. `script-PLSQL`
   3. `script-donnees`

3. Créer le fichier `web/conf/db.ini` (modèle dans `web/conf/db.ini.md`) :

   ```
   host = localhost
   dbname = touiteur
   user = ...
   password = ...
   ```

4. Ouvrir le dossier `http://localhost/<dossier-du-projet>/web/` dans le navigateur

## Comptes de test

|            Email               |    MDP   |                  Rôle                      |
|--------------------------------|----------|--------------------------------------------|
| nassim.aliouane@touiteur.app   | user1234 |            utilisateur                     |
| kevin.pasho@touiteur.app       | user1234 |            utilisateur                     |
| romain.fanara@touiteur.app     | user1234 |            utilisateur                     |
| adnan.hamedomer@touiteur.app   | user1234 |            utilisateur                     |
| gabriel.rodrigues@touiteur.app | user1234 |            utilisateur                     |
| admin@touiteur.app             | admin1234| administrateur (accès à « Top touiteurs ») |



## Tableau de bord

### Fonctionnalités


| N° | Fonctionnalité                              | Type    | État     | Réalisée par           |
|----|---------------------------------------------|---------|----------|------------------------|
| 1  | Afficher la liste des touites               | Base    | Fait     | Gabriel, Romain        |
| 2  | Afficher un touite en détail                | Base    | Fait     | Gabriel, Romain        |
| 3  | Paginer la liste des touites                | Étendue | Non fait |                        |
| 4  | Afficher les touites d'un utilisateur       | Base    | Fait     | Gabriel                |
| 5  | Afficher les touites d'un tag (+ recherche) | Base    | Fait     | Gabriel, Romain, Kevin |
| 6  | Publier un touite                           | Base    | Fait     | Adnan, Romain          |
| 7  | Évaluer un touite (like / dislike)          | Étendue | Fait     | Adnan, Romain, Nassim  |
| 8  | Gestion des tags                            | Base    | Fait     | Gabriel, Romain        |
| 9  | Le mur d'un utilisateur                     | Base    | Fait     | Kevin                  |
| 10 | Suivre des utilisateurs                     | Base    | Fait     | Adnan, Romain          |
| 11 | S'abonner à un tag                          | Base    | Fait     | Adnan, Romain          |
| 12 | L'utilisateur narcissique                   | Étendue | Partiel  | Romain                 |
| 13 | Associer une image à un touite              | Étendue | Fait     | Romain, Kevin          |
| 14 | Les influenceurs (page « Top touiteurs »)   | Base    | Fait     | Nassim                 |
| 15 | Les tendances                               | Étendue | Non fait |                        |
| 16 | Le back-office                              | Étendue | Non fait |                        |

Remarques :
- 12 : le profil affiche le nombre d'abonnés et de likes, mais pas les noms des abonnés ni le score moyen des touites.
- 15 : non réalisée mais un bloc « Tags les plus suivis » (classés par nombre d'abonnés) est affiché à la place.



### Répartition des tâches

    - Kevin et Nassim Partie globale BD :

        - Conception de la base tables et relations
        - Script SQL de creation et triggers
        - Donnees de test et rapport   
        - Choix d'une fonctionnalité PHP en plus chacun

    - Romain Adnan et Gabriel Partie gloable PHP
    
        - Liste des touites et page de detail
        - Formulaire pour poster tags et upload d'images
        - Connexion des utilisateurs et mise en ligne sur webetu

### En détail

    - Nassim (na2s78) :
        - Base de données avec Kevin : contraintes, triggers, mot de passe et rôle des utilisateurs, table des évaluations
        - Script de données de test
        - Maquette HTML
        - Classe Connexion (lecture de `db.ini`) et dossier `conf`
        - Autoloader des classes
        - Dispatcher : routes et mise en page commune
        - Protection des dossiers `conf`, `src` et `ressources` avec `.htaccess`
        - Fonctionnalité 14 : influenceurs (page « Top touiteurs »)
        - Correction du score des évaluations (like +1, dislike -1)


    - Romain (ryoasis) :

        - Peaufinage d'un design cohérent avec le thème (maquette)
        - Création d'une architecture correcte et minimale pour répartir le code
        - Mise en place de sécutités sur le format des touites
        - Travail sur le touite détaillé, le formulaire d'envoi, la mise en place des vues
            donc les fonctionnalités 2, 6, 7, 10, 13
        - Mise en place d'une branche personnelle de développement pour le commit principal du dépôt
        - Collaboration sur le dispatcher, la connexion, la recherche et le like
        - Vérification et optimissation globale pour Linux


    - Gabriel (RodriguesNetoGab) :

        - Création des premières versions des classes Action, HomeAction, Dispatcher avec un premier index
        - Création de GestionTouite.php qui a été corrigé par Romain par la suite
        - Travail sur les fonctionnalités 1, 2, 4, 5 et 8
        - Première version de la classe Connexion.php
        


    - Kevin (kevinpasK) :

        - création de la base de données 
        - 9. Mur (Avoir un mur selon les abonnements (personnes ou tags présents))
        - Correction de la recherche par tag et des images, qui ne s’affichent plus que dans le détail
        - Correction des redirections publier depuis le mur et bouton retour du détail
    

    - Adnan (Adnan Hamed Omer) :

        - création de la pages html au début
        - création des premières versions des fonctionnalités 6,7,10,11
        - correction des fichiers index.php, Action.php et dispatcher.php pour faire correctement fonctionner les fonctionnalités                     précédentes
        - nouveaux codes pour ces fonctionnalités suite à la modification des fichiers index.php, dispatcher.php et Action.php
