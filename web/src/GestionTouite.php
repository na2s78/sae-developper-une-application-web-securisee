
<?php

class GestionTouite
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Fonctionnalité 1 : récupérer tous les touites
    public function getTousLesTouites(): array
    {
        $sql = "
            SELECT
                t.idTouite,
                t.texteTouite,
                t.datePub,
                t.nbEval,
                u.idUtil,
                u.nomUtil,
                u.prenomUtil
            FROM Touite t
            JOIN Utilisateur u
                ON t.idUtil = u.idUtil
            ORDER BY t.datePub DESC
        ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Fonctionnalité 2 : récupérer un touite précis
    public function getTouiteParId(int $idTouite): ?array
    {
        $sql = "
            SELECT
                t.idTouite,
                t.texteTouite,
                t.datePub,
                t.nbEval,
                u.idUtil,
                u.nomUtil,
                u.prenomUtil,
                i.idImage,
                i.descImage,
                i.cheminImage
            FROM Touite t
            JOIN Utilisateur u
                ON t.idUtil = u.idUtil
            LEFT JOIN Image i
                ON t.idImage = i.idImage
            WHERE t.idTouite = :idTouite
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':idTouite' => $idTouite
        ]);

        $touite = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($touite === false) {
            return null;
        }

        return $touite;
    }

    // Fonctionnalité 8 : Enregistrer les tags d'un touite


    public function enregistrerTags(int $idTouite, string $texte): void
    {
        $tags = $this->extraireTags($texte);

        foreach ($tags as $tag) {

            // On vérifie si le tag existe deja.
            $sql = "SELECT idTag
                    FROM Tag
                    WHERE LOWER(libelleTag) = LOWER(:libelleTag)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'libelleTag' => $tag
            ]);

            $idTag = $stmt->fetchColumn();

            // Si le tag n'existe pas, on le crée.
            if ($idTag === false) {

                $sql = "INSERT INTO Tag (libelleTag, descTag)
                        VALUES (:libelleTag, NULL)";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'libelleTag' => $tag
                ]);

                // On récupère l'identifiant du tag qui vient d'être crée.
                $sql = "SELECT idTag
                        FROM Tag
                        WHERE LOWER(libelleTag) = LOWER(:libelleTag)";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'libelleTag' => $tag
                ]);

                $idTag = $stmt->fetchColumn();
            }

            // On vérifie si l'association existe déjà.
            $sql = "SELECT COUNT(*)
                    FROM TouiteTag
                    WHERE idTouite = :idTouite
                    AND idTag = :idTag";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'idTouite' => $idTouite,
                'idTag' => $idTag
            ]);

            $associationExiste = $stmt->fetchColumn();

            // Si l'association n'existe pas, on la crée.
            if ($associationExiste == 0) {

                $sql = "INSERT INTO TouiteTag (idTouite, idTag)
                        VALUES (:idTouite, :idTag)";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'idTouite' => $idTouite,
                    'idTag' => $idTag
                ]);
            }
        }
    }



    // Fonctionnalité 4 : récupérer les touites d'un utilisateur
    public function getTouitesParUtilisateur(int $idUtil): array
    {
        $sql = "
            SELECT
                t.idTouite,
                t.texteTouite,
                t.datePub,
                t.nbEval,
                u.idUtil,
                u.nomUtil,
                u.prenomUtil
            FROM Touite t
            JOIN Utilisateur u
                ON t.idUtil = u.idUtil
            WHERE u.idUtil = :idUtil
            ORDER BY t.datePub DESC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':idUtil' => $idUtil
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Fonctionnalité 5 : récupérer les touites associés à un tag
    public function getTouitesParTag(int $idTag): array
    {
        $sql = "
            SELECT
                t.idTouite,
                t.texteTouite,
                t.datePub,
                t.nbEval,
                u.idUtil,
                u.nomUtil,
                u.prenomUtil,
                tg.idTag,
                tg.libelleTag
            FROM Touite t
            JOIN Utilisateur u
                ON t.idUtil = u.idUtil
            JOIN TouiteTag tt
                ON t.idTouite = tt.idTouite
            JOIN Tag tg
                ON tt.idTag = tg.idTag
            WHERE tg.idTag = :idTag
            ORDER BY t.datePub DESC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':idTag' => $idTag
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
