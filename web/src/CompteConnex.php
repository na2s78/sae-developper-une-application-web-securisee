<?php
namespace touiteur;

use PDO;

class CompteConnex
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function execute(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (($_GET['action'] ?? '') === 'deconnexion') {
            $_SESSION = [];
            session_destroy();
            header('Location: ?action=home', true, 303);
            exit;
        }

        $erreur = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) ($_POST['mail'] ?? ''));
            $motDePasse = (string) ($_POST['pw'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreur = 'Adresse e-mail invalide.';
            } else {
                $stmt = $this->pdo->prepare('SELECT idUtil, prenomUtil, nomUtil, mdpUtil FROM utilisateur WHERE LOWER(emailUtil) = LOWER(:email)');
                $stmt->execute([':email' => $email]);
                $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($utilisateur && password_verify($motDePasse, $utilisateur['mdpUtil'])) {
                    session_regenerate_id(true);
                    $_SESSION['idUtil'] = (int) $utilisateur['idUtil'];
                    $_SESSION['prenomUtil'] = $utilisateur['prenomUtil'];
                    $_SESSION['nomUtil'] = $utilisateur['nomUtil'];
                    header('Location: ?action=home', true, 303);
                    exit;
                }
                $erreur = 'Identifiants incorrects.';
            }
        }

        $message = $erreur === null ? '' : '<p class="alerte">' . htmlspecialchars($erreur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        return '<section class="carte formulaire-compte"><h1>Connexion</h1>' . $message . '<form method="post" action="?action=connexion"><label for="connexion-mail">E-mail</label><input id="connexion-mail" type="email" name="mail" required><label for="connexion-pw">Mot de passe</label><input id="connexion-pw" type="password" name="pw" required><button class="bouton" type="submit">Se connecter</button></form></section>';
    }
}
