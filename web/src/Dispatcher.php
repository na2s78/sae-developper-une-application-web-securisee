<?php

namespace touiteur;

use PDO;

class Dispatcher
{
    private PDO $pdo;
    private string $action;
    private array $tab = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        // Si il n'y a pas de query action, on prend l'action par défaut
        $this->action = $_GET['action'] ?? 'default';
    }

    public function addRoute(string $action, string $class): self
    {
        $this->tab[$action] = $class;
        return $this;
    }

    public function run(): void
    {
        // Action inconnue : on revient à l'action par défaut
        if (!array_key_exists($this->action, $this->tab)) {
            $this->action = 'default';
        }

        $classe = $this->tab[$this->action];
        $obj = new $classe($this->pdo);
        $this->renderPage($obj->execute());
    }

    private function renderPage(string $html): void
    {
        $accueil = in_array($this->action, ['default', 'home'], true) ? ' class="actif"' : '';
        $idUtil = (int) ($_SESSION['idUtil'] ?? 0);
        $lienAdmin = '';
        if ($idUtil > 0) {
            $connecte = (new GestionTouite($this->pdo))->getUtilisateurById($idUtil);
            if ($connecte !== null && (int) $connecte['roleUtil'] === 100) {
                $actifAdmin = $this->action === 'influenceurs' ? ' class="actif"' : '';
                $lienAdmin = '<a href="?action=influenceurs"' . $actifAdmin . '>Top touiteurs</a>';
            }
        }

        $profilHeader = '';
        $connexionHeader = '<form class="connexion" method="POST" action="?action=connexion">'
            . '<input type="email" name="mail" placeholder="Email" required>'
            . '<input type="password" name="pw" placeholder="Mot de passe" required>'
            . '<button type="submit" class="bouton">Connexion</button></form>';
        if ($idUtil > 0) {
            $prenom = (string) ($_SESSION['prenomUtil'] ?? '');
            $nom = (string) ($_SESSION['nomUtil'] ?? '');
            $initiales = htmlspecialchars(
                mb_strtoupper(mb_substr($prenom, 0, 1) . mb_substr($nom, 0, 1)),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );
            $profilHeader = '<a class="profil-rond" href="?action=profil" title="Mon profil" aria-label="Mon profil">'
                . $initiales . '</a><a class="profil-lien" href="?action=profil">Mon profil</a>'
                . '<a class="deconnexion-lien" href="?action=deconnexion" title="Se déconnecter" aria-label="Se déconnecter">'
                . '<img src="ressources/images_css/log_out.svg" alt=""></a>';
            $connexionHeader = '';
        }
        $tagsSuivisHtml = '<div class="carte tags-suivis"><h3>Mes tags suivis</h3>';
        if ($idUtil <= 0) {
            $tagsSuivisHtml .= '<p class="date">Connectez-vous pour voir vos tags suivis.</p>';
        } else {
            $tagsSuivis = (new GestionTouite($this->pdo))->getTagsSuivis($idUtil);
            if ($tagsSuivis === []) {
                $tagsSuivisHtml .= '<p class="date">Aucun tag suivi.</p>';
            } else {
                $tagsSuivisHtml .= '<ul class="liste-tags">';
                foreach ($tagsSuivis as $tag) {
                    $libelle = htmlspecialchars((string) $tag['libelleTag'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $url = rawurlencode((string) $tag['libelleTag']);
                    $retour = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '?action=home', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $tagsSuivisHtml .= '<li><a class="tag" href="?action=tag&amp;tag=' . $url . '">#' . $libelle . '</a>'
                        . '<form method="post" action="?action=follow" class="desabonner-tag">'
                        . '<input type="hidden" name="type" value="tag"><input type="hidden" name="idTag" value="' . (int) $tag['idTag'] . '">'
                        . '<input type="hidden" name="mode" value="desabonner"><input type="hidden" name="retour" value="' . $retour . '">'
                        . '<button type="submit" title="Ne plus suivre ce tag" aria-label="Ne plus suivre ce tag">×</button></form></li>';
                }
                $tagsSuivisHtml .= '</ul>';
            }
        }
        $tagsSuivisHtml .= '</div>';

        echo <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Touiteur.app</title>
    <link rel="stylesheet" href="ressources/style.css">
</head>
<body>

<header class="entete">
    <a href="?action=home" class="logo">
        <img src="ressources/images_css/touiteur_txt.svg" alt="Touiteur.app">
    </a>

    <form class="recherche" method="GET" action="index.php">
        <input type="hidden" name="action" value="tag">
        <label class="visuellement-cache" for="recherche-tag">Rechercher un tag</label>
        <input id="recherche-tag" name="q" type="search" maxlength="60" placeholder="Rechercher sur Touiteur...">
    </form>

    {$connexionHeader}
    {$profilHeader}
</header>

<div class="conteneur">

    <nav class="menu">
        <a href="?action=home"{$accueil}>Accueil</a>
        <a href="?action=mur">Mon mur</a>
        <a href="?action=formulaire">Publier</a>
        {$lienAdmin}
    </nav>

    <main class="fil">
        {$html}
    </main>

    <aside class="cote">
        <div class="carte">
            <h3>S'abonner à un tag</h3>
            <form method="POST" action="?action=follow" class="abonner">
                <input type="hidden" name="type" value="tag">
                <label class="visuellement-cache" for="abonnement-tag">Tag à suivre</label>
                <input id="abonnement-tag" type="text" name="libelleTag" maxlength="30" placeholder="#tag" required>
                <button type="submit" class="bouton">OK</button>
            </form>
        </div>
        {$tagsSuivisHtml}
    </aside>

</div>

<footer class="pied">Touiteur.app 2026 - SAE BUT Informatique - NA,RF,KP,AHO,GRN</footer>

</body>
</html>
HTML;
    }
}
