<?php
// Fonctionnalité 6 : publier un touite ; GestionTouite extrait et associe les #tags.
// Fonctionnalité 13 facultative : joindre une image vérifiée au touite.
namespace touiteur\actions;

use PDOException;
use RuntimeException;
use Throwable;
use touiteur\GestionTouite;

class FormulaireAction extends Action
{
    private const TAILLE_MAX_IMAGE = 2 * 1024 * 1024; // 2 Mo
    private const TYPES_IMAGE = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function execute(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['idUtil'])) {
            $this->redirect('?action=connexion');
        }

        $erreur = null;
        $texte = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $texte = trim((string) ($_POST['texteTouite'] ?? ''));
            $idUtil = (int) $_SESSION['idUtil'];

            if ($texte === '') {
                $erreur = 'Le touite ne peut pas être vide.';
            } elseif (mb_strlen($texte, 'UTF-8') > 235) {
                $erreur = 'Le touite ne doit pas dépasser 235 caractères.';
            } else {
                $cheminImage = null;
                try {
                    $cheminImage = $this->enregistrerImage();
                    $gestion = new GestionTouite($this->pdo);
                    $gestion->publierTouite($idUtil, $texte, $cheminImage);

                    // GestionTouite incrémente nbTouites et le trigger
                    // trg_maj_nb_touites l'incrémente aussi. Comme on ne modifie
                    // pas GestionTouite.php, on remet le compteur à sa valeur
                    // dérivée réelle après insertion (plutôt que le laisser doublé).
                    $stmt = $this->pdo->prepare(
                        'UPDATE utilisateur
                         SET nbTouites = (SELECT COUNT(*) FROM touite WHERE idUtil = :idUtilCompte)
                         WHERE idUtil = :idUtil'
                    );
                    $stmt->execute([':idUtilCompte' => $idUtil, ':idUtil' => $idUtil]);

                    $this->redirect('?action=home');
                } catch (PDOException $exception) {
                    $this->supprimerImage($cheminImage);
                    error_log('FormulaireAction : ' . $exception->getMessage());
                    $erreur = (string) $exception->getCode() === '45000'
                        ? 'Vous avez atteint la limite de 50 touites par jour.'
                        : 'La publication a échoué. Réessayez dans un instant.';
                } catch (RuntimeException $exception) {
                    $this->supprimerImage($cheminImage);
                    $erreur = $exception->getMessage();
                } catch (Throwable $exception) {
                    $this->supprimerImage($cheminImage);
                    error_log('FormulaireAction : ' . $exception->getMessage());
                    $erreur = 'La publication a échoué. Réessayez dans un instant.';
                }
            }
        }

        $alerte = $erreur === null ? '' : '<p class="alerte">' . $this->e($erreur) . '</p>';
        return '<section class="carte publier">'
            . '<h1>Publier un touite</h1>' . $alerte
            . '<form method="post" action="?action=formulaire" enctype="multipart/form-data">'
            . '<label class="visuellement-cache" for="texteTouite">Votre touite</label>'
            . '<textarea id="texteTouite" name="texteTouite" maxlength="235" required'
            . ' placeholder="Quoi de neuf ? Ajoutez des #tags...">' . $this->e($texte) . '</textarea>'
            . '<p class="date">235 caractères maximum. Les #tags sont automatiquement reconnus.</p>'
            . '<div class="publier-bas">'
            . '<label class="bouton-image" for="image">Ajouter une image'
            . '<input id="image" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">'
            . '</label><button class="bouton" type="submit">Touiter</button>'
            . '</div><img id="apercu-image-formulaire" class="apercu-image" alt="Aperçu de l’image sélectionnée" hidden>'
            . '</form></section>'
            . '<script>document.getElementById("image")?.addEventListener("change", function () {'
            . 'const apercu = document.getElementById("apercu-image-formulaire");'
            . 'const fichier = this.files && this.files[0];'
            . 'if (!fichier || !fichier.type.startsWith("image/")) {'
            . 'apercu.hidden = true; apercu.removeAttribute("src"); return; }'
            . 'apercu.src = URL.createObjectURL(fichier); apercu.hidden = false; });</script>';
    }

    /** Enregistre une image téléversée et renvoie son chemin relatif, ou null. */
    private function enregistrerImage(): ?string
    {
        if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
            return null;
        }
        $fichier = $_FILES['image'];
        if (!isset($fichier['error']) || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Le téléversement de l’image a échoué.');
        }
        if (!is_uploaded_file((string) $fichier['tmp_name'])) {
            throw new RuntimeException('Fichier image invalide.');
        }
        if ((int) $fichier['size'] <= 0 || (int) $fichier['size'] > self::TAILLE_MAX_IMAGE) {
            throw new RuntimeException('L’image ne doit pas dépasser 2 Mo.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new RuntimeException('Impossible de vérifier le format de l’image.');
        }
        $mime = finfo_file($finfo, (string) $fichier['tmp_name']);
        finfo_close($finfo);
        if (!is_string($mime) || !isset(self::TYPES_IMAGE[$mime])
            || @getimagesize((string) $fichier['tmp_name']) === false) {
            throw new RuntimeException('Format accepté : image JPEG, PNG, GIF ou WebP valide.');
        }

        $dossier = dirname(__DIR__, 2) . '/ressources/images_upload';
        if (!is_dir($dossier) && !mkdir($dossier, 0755, true) && !is_dir($dossier)) {
            throw new RuntimeException('Impossible d’enregistrer l’image.');
        }
        $nom = bin2hex(random_bytes(16)) . '.' . self::TYPES_IMAGE[$mime];
        if (!move_uploaded_file((string) $fichier['tmp_name'], $dossier . '/' . $nom)) {
            throw new RuntimeException('Impossible d’enregistrer l’image.');
        }
        @chmod($dossier . '/' . $nom, 0644);
        return 'ressources/images_upload/' . $nom;
    }

    /** Supprime l'image déposée si la publication échoue. */
    private function supprimerImage(?string $chemin): void
    {
        if ($chemin === null || $chemin === '') {
            return;
        }
        $absolu = dirname(__DIR__, 2) . '/' . $chemin;
        if (is_file($absolu)) {
            @unlink($absolu);
        }
    }
}
