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
        // Lien du menu en surbrillance pour la page courante
        $accueil = in_array($this->action, ['default', 'home']) ? ' class="actif"' : '';

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
        <img src="ressources/images_css/touiteur_g.svg" alt="Touiteur">
    </a>

    <form class="recherche" method="GET" action="index.php">
        <input type="hidden" name="action" value="tag">
        <input name="tag" type="search" placeholder="Rechercher un #tag...">
    </form>

    <form class="connexion" method="POST" action="?action=connexion">
        <input type="email" name="mail" placeholder="Email" required>
        <input type="password" name="pw" placeholder="Mot de passe" required>
        <button type="submit" class="bouton">Connexion</button>
    </form>
</header>

<div class="conteneur">

    <nav class="menu">
        <a href="?action=home"{$accueil}>Accueil</a>
        <a href="?action=mur">Mon mur</a>
        <a href="?action=formulaire">Publier</a>
        <a href="?action=profil">Mon profil</a>
    </nav>

    <main class="fil">
        {$html}
    </main>

    <aside class="cote">
        <div class="carte">
            <h3>S'abonner à un tag</h3>
            <form method="POST" action="?action=follow" class="abonner">
                <input type="hidden" name="type" value="tag">
                <input type="text" name="libelleTag" placeholder="#tag">
                <button type="submit" class="bouton">OK</button>
            </form>
        </div>
    </aside>

</div>

<footer class="pied">Touiteur.app &copy; 2026 - SAE BUT Informatique</footer>

</body>
</html>
HTML;
    }
}
