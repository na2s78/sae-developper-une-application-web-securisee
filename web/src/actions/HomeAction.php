<?php

namespace touiteur\actions;

class HomeAction extends Action
{
    public function execute(): string
    {
        $gestion = new \touiteur\GestionTouite($this->pdo);
        $touites = $gestion->getTousLesTouites();

        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Touiteur</title>
</head>
<body>
    <h1>Accueil</h1>
    <p>Nombre de touites : ' . count($touites) . '</p>';

        if ($touites === []) {
            $html .= '<p>Aucun touite pour le moment.</p>';
            $html .= '</body></html>';
            return $html;
        }

        foreach ($touites as $touite) {
            $html .= '<article style="border:1px solid #ccc; margin:10px 0; padding:10px;">';
            $html .= '<strong>' . htmlspecialchars($touite['prenomUtil'] ?? '') . ' ' . htmlspecialchars($touite['nomUtil'] ?? '') . '</strong>';
            $html .= '<div>' . nl2br(htmlspecialchars($touite['texteTouite'] ?? '')) . '</div>';
            $html .= '<small>' . htmlspecialchars($touite['datePub'] ?? '') . '</small>';
            $html .= '</article>';
        }

        $html .= '</body></html>';

        return $html;
    }
}