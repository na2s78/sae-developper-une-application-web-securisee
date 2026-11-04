<?php

namespace touiteur;

use PDO;

class GestionTouite
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Fonctionnalité 1 : Récupérer tous les touites (ordre chronologique inverse)
     */
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

    /**
     * Fonctionnalité 2 : Récupérer un touite précis en détail (avec son image éventuelle)
     */
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

        return $touite !== false ? $touite : null;
    }

    /**
     * Extraire les hashtags d'un texte
     */
    private function extraireTags(string $texte): array
    {
        preg_match_all('/#([\p{L}\p{N}_]+)/u', $texte, $matches);

        return array_unique($matches[1]);
    }

    /**
     * Fonctionnalité 8 : Extraire et enregistrer les tags d'un touite
     */
    public function enregistrerTags(int $idTouite, string $texte): void
    {
        $tags = $this->extraireTags($texte);

        foreach ($tags as $tag) {
            // 1. Vérifier si le tag existe déjà
            $sql = "SELECT idTag
                    FROM Tag
                    WHERE LOWER(libelleTag) = LOWER(:libelleTag)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':libelleTag' => $tag
            ]);

            $idTag = $stmt->fetchColumn();

            // 2. Si le tag n'existe pas, on le crée et récupère son ID via PDO
            if ($idTag === false) {
                $sqlInsert = "INSERT INTO Tag (libelleTag, descTag)
                              VALUES (:libelleTag, NULL)";

                $stmtInsert = $this->pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    ':libelleTag' => $tag
                ]);

                $idTag = (int) $this->pdo->lastInsertId();
            } else {
                $idTag = (int) $idTag;
            }

            // 3. Vérifier si l'association Touite <-> Tag existe déjà
            $sqlCheck = "SELECT COUNT(*)
                         FROM TouiteTag
                         WHERE idTouite = :idTouite
                         AND idTag = :idTag";

            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                ':idTouite' => $idTouite,
                ':idTag'    => $idTag
            ]);

            // 4. Si l'association n'existe pas, on l'insère
            if ((int) $stmtCheck->fetchColumn() === 0) {
                $sqlLink = "INSERT INTO TouiteTag (idTouite, idTag)
                            VALUES (:idTouite, :idTag)";

                $stmtLink = $this->pdo->prepare($sqlLink);
                $stmtLink->execute([
                    ':idTouite' => $idTouite,
                    ':idTag'    => $idTag
                ]);
            }
        }
    }

    /**
     * Fonctionnalité 4 : Récupérer les touites d'un utilisateur
     */
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

    /**
     * Fonctionnalité 5 (par ID) : Récupérer les touites associés à un ID de tag
     */
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

    /**
     * Fonctionnalité 5 (par texte) : Récupérer les touites par le nom du tag (#php, #iut...)
     */
    public function getTouitesParLibelleTag(string $libelleTag): array
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
            WHERE LOWER(tg.libelleTag) = LOWER(:libelleTag)
            ORDER BY t.datePub DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':libelleTag' => $libelleTag
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}