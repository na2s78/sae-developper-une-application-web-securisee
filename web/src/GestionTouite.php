<?php
namespace touiteur;

use PDO;

class GestionTouite
{
    // Un tag : un # au début d'un mot (début de texte ou après un espace), suivi de lettres, chiffres ou _
    // $m[1] = l'espace avant le #, $m[2] = le nom du tag
    public const REGEX_TAG = '/(^|\s)#([\p{L}\p{N}_]+)/u';

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getTousLesTouites(): array
    {
        return $this->listeTouites('');
    }

    public function getTouiteParId(int $idTouite): ?array
    {
        $sql = 'SELECT t.idTouite, t.texteTouite, t.datePub, t.nbEval,
                       u.idUtil, u.nomUtil, u.prenomUtil, u.roleUtil,
                       i.idImage, i.descImage, i.cheminImage
                FROM touite t
                JOIN utilisateur u ON u.idUtil = t.idUtil
                LEFT JOIN image i ON i.idImage = t.idImage
                WHERE t.idTouite = :idTouite';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idTouite' => $idTouite]);
        $touite = $stmt->fetch(PDO::FETCH_ASSOC);
        return $touite === false ? null : $touite;
    }

    public function getTouitesParUtilisateur(int $idUtil): array
    {
        return $this->listeTouites('WHERE u.idUtil = :idUtil', [':idUtil' => $idUtil]);
    }

    public function getTouitesParLibelleTag(string $libelleTag): array
    {
        $sql = 'SELECT t.idTouite, t.texteTouite, t.datePub, t.nbEval,
                       u.idUtil, u.nomUtil, u.prenomUtil, u.roleUtil,
                       i.cheminImage, i.descImage
                FROM touite t
                JOIN utilisateur u ON u.idUtil = t.idUtil
                JOIN touitetag tt ON tt.idTouite = t.idTouite
                JOIN tag tg ON tg.idTag = tt.idTag
                LEFT JOIN image i ON i.idImage = t.idImage
                WHERE LOWER(tg.libelleTag) = LOWER(:libelleTag)
                ORDER BY t.datePub DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':libelleTag' => $libelleTag]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUtilisateurById(int $idUtil): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.idUtil, u.nomUtil, u.prenomUtil, u.emailUtil, u.roleUtil, u.nbTouites,
                    (SELECT COUNT(*) FROM touite t0 WHERE t0.idUtil = u.idUtil) AS totalTouites,
                    (SELECT COUNT(*)
                     FROM evaluation e
                     JOIN touite t1 ON t1.idTouite = e.idTouite
                     WHERE t1.idUtil = u.idUtil AND e.valeur = 1) AS totalLikes,
                    (SELECT COUNT(*) FROM suivre s WHERE s.idSuivi = u.idUtil) AS nbAbonnes,
                    (SELECT COUNT(*) FROM suivre s WHERE s.idSuiveur = u.idUtil) AS nbAbonnements
             FROM utilisateur u
             WHERE u.idUtil = :idUtil'
        );
        $stmt->execute([':idUtil' => $idUtil]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
        return $utilisateur === false ? null : $utilisateur;
    }

    public function getUtilisateurByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT idUtil, nomUtil, prenomUtil, emailUtil, mdpUtil, roleUtil FROM utilisateur WHERE LOWER(emailUtil) = LOWER(:email)');
        $stmt->execute([':email' => $email]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
        return $utilisateur === false ? null : $utilisateur;
    }

    public function publierTouite(int $idUtil, string $texte, ?string $cheminImage = null): int
    {
        $this->pdo->beginTransaction();
        try {
            $idImage = null;
            if ($cheminImage !== null) {
                $image = $this->pdo->prepare('INSERT INTO image (descImage, cheminImage) VALUES (:description, :chemin)');
                $image->execute([':description' => 'Image associée à un touite', ':chemin' => $cheminImage]);
                $idImage = (int) $this->pdo->lastInsertId();
            }
            $sql = 'INSERT INTO touite (texteTouite, datePub, idUtil, idImage)
                    VALUES (:texte, :datePub, :idUtil, :idImage)';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':texte', $texte, PDO::PARAM_STR);
            $stmt->bindValue(
                ':datePub',
                (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))->format('Y-m-d H:i:s'),
                PDO::PARAM_STR
            );
            $stmt->bindValue(':idUtil', $idUtil, PDO::PARAM_INT);
            $idImage === null ? $stmt->bindValue(':idImage', null, PDO::PARAM_NULL) : $stmt->bindValue(':idImage', $idImage, PDO::PARAM_INT);
            $stmt->execute();
            $idTouite = (int) $this->pdo->lastInsertId();
            $this->pdo->prepare('UPDATE utilisateur SET nbTouites = nbTouites + 1 WHERE idUtil = :idUtil')->execute([':idUtil' => $idUtil]);
            $this->enregistrerTags($idTouite, $texte);
            $this->pdo->commit();
            return $idTouite;
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function enregistrerTags(int $idTouite, string $texte): void
    {
        preg_match_all(self::REGEX_TAG, $texte, $resultats);

        foreach ($resultats[2] as $libelle) {
            $libelle = mb_strtolower($libelle);

            if (mb_strlen($libelle) > 30) {
                continue;
            }

            // On cherche le tag, et on le crée s'il n'existe pas encore
            $tag = $this->getTagByLibelle($libelle);
            if ($tag === null) {
                $insert = $this->pdo->prepare('INSERT INTO tag (libelleTag, descTag) VALUES (:libelle, NULL)');
                $insert->execute([':libelle' => $libelle]);
                $idTag = (int) $this->pdo->lastInsertId();
            } else {
                $idTag = (int) $tag['idTag'];
            }

            // On lie le tag au touite (INSERT IGNORE : pas d'erreur si le lien existe déjà)
            $lien = $this->pdo->prepare('INSERT IGNORE INTO touitetag (idTouite, idTag) VALUES (:idTouite, :idTag)');
            $lien->execute([':idTouite' => $idTouite, ':idTag' => $idTag]);
        }
    }

    public function getTagsSimilaires(string $recherche, int $limite = 5): array
    {
        $stmt = $this->pdo->prepare('SELECT libelleTag FROM tag WHERE libelleTag LIKE :motif ORDER BY libelleTag LIMIT :limite');
        $stmt->bindValue(':motif', '%' . $recherche . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getTagByLibelle(string $libelle): ?array
    {
        $stmt = $this->pdo->prepare('SELECT idTag, libelleTag FROM tag WHERE LOWER(libelleTag) = LOWER(:libelle)');
        $stmt->execute([':libelle' => $libelle]);
        $tag = $stmt->fetch(PDO::FETCH_ASSOC);
        return $tag === false ? null : $tag;
    }

    public function suivreUtilisateur(int $idSuiveur, int $idSuivi): void
    {
        if ($idSuiveur === $idSuivi) {
            return;
        }
        $stmt = $this->pdo->prepare('INSERT IGNORE INTO suivre (idSuiveur, idSuivi) VALUES (:suiveur, :suivi)');
        $stmt->execute([':suiveur' => $idSuiveur, ':suivi' => $idSuivi]);
    }

    public function nePlusSuivreUtilisateur(int $idSuiveur, int $idSuivi): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM suivre WHERE idSuiveur = :suiveur AND idSuivi = :suivi');
        $stmt->execute([':suiveur' => $idSuiveur, ':suivi' => $idSuivi]);
    }

    public function abonnerTag(int $idUtil, int $idTag): void
    {
        $stmt = $this->pdo->prepare('INSERT IGNORE INTO abonnementtag (idUtil, idTag) VALUES (:idUtil, :idTag)');
        $stmt->execute([':idUtil' => $idUtil, ':idTag' => $idTag]);
    }

    public function desabonnerTag(int $idUtil, int $idTag): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM abonnementtag WHERE idUtil = :idUtil AND idTag = :idTag');
        $stmt->execute([':idUtil' => $idUtil, ':idTag' => $idTag]);
    }

    /** Retourne les tags suivis, triés par libellé à droite */
    public function getTagsSuivis(int $idUtil): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT tg.idTag, tg.libelleTag
             FROM abonnementtag abo
             JOIN tag tg ON tg.idTag = abo.idTag
             WHERE abo.idUtil = :idUtil
             ORDER BY tg.libelleTag ASC'
        );
        $stmt->execute([':idUtil' => $idUtil]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function idTagParLibelle(string $libelle): ?int
    {
        $tag = $this->getTagByLibelle(ltrim($libelle, '#'));
        return $tag === null ? null : (int) $tag['idTag'];
    }

    private function listeTouites(string $where, array $params = []): array
    {
        $sql = 'SELECT t.idTouite, t.texteTouite, t.datePub, t.nbEval,
                       u.idUtil, u.nomUtil, u.prenomUtil, u.roleUtil,
                       i.cheminImage
                FROM touite t
                JOIN utilisateur u ON u.idUtil = t.idUtil
                LEFT JOIN image i ON i.idImage = t.idImage
                ' . $where . ' ORDER BY t.datePub DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Recherche textuelle générale des touites, avec requête préparée. */
    public function rechercherTouites(string $mot): array
    {
        return $this->listeTouites(
            'WHERE t.texteTouite LIKE :mot',
            [':mot' => '%' . $mot . '%']
        );
    }

    // Fonctionnalité 14 : les utilisateurs classés par nombre de suiveurs
    // nous suivons les consignes mais l'ordre devrait être décroissant logiquement
    public function getInfluenceurs(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.idUtil, u.prenomUtil, u.nomUtil, COUNT(s.idSuiveur) AS nbSuiveurs
            FROM utilisateur u
            LEFT JOIN suivre s ON s.idSuivi = u.idUtil
            GROUP BY u.idUtil, u.prenomUtil, u.nomUtil
            ORDER BY nbSuiveurs ASC, u.nomUtil ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
