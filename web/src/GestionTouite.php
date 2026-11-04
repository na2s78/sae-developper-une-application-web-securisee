<?php

namespace touiteur;

use PDO;

class GestionTouite
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo =$pdo;
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

        $stmt =$this->pdo->prepare($sql);$stmt->execute([
            ':idTouite' => $idTouite
        ]);

        $touite =$stmt->fetch(PDO::FETCH_ASSOC);

        return $touite !== false ? $touite : null;
    }

    /**
     * Extraire les hashtags d'un texte
     */
    private function extraireTags(string $texte): array
    {
        preg_match_all('/#([\p{L}\p{N}_]+)/u', $texte,$matches);

        return array_unique($matches[1]);
    }

    /**
     * Fonctionnalité 8 : Extraire et enregistrer les tags d'un touite
     */
    public function enregistrerTags(int $idTouite, string$texte): void
    {
        $tags = $this->extraireTags($texte);

        foreach ($tags as$tag) {
            // 1. Vérifier si le tag existe déjà
            $sql = "SELECT idTag
                    FROM Tag
                    WHERE LOWER(libelleTag) = LOWER(:libelleTag)";

            $stmt =$this->pdo->prepare($sql);$stmt->execute([
                ':libelleTag' => $tag
            ]);

            $idTag =$stmt->fetchColumn();

            // 2. Si le tag n'existe pas, on le crée et récupère son ID via PDO
            if ($idTag === false) {$sqlInsert = "INSERT INTO Tag (libelleTag, descTag)
                              VALUES (:libelleTag, NULL)";

                $stmtInsert =$this->pdo->prepare($sqlInsert);$stmtInsert->execute([
                    ':libelleTag' => $tag
                ]);

                $idTag = (int)$this->pdo->lastInsertId();
            } else {
                $idTag = (int)$idTag;
            }