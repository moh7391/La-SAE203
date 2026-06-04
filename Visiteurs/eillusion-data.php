<?php
// Fonctions simples utilisees par les pages visiteurs.
// Elles evitent de recopier les memes requetes partout.

function e($valeur) {
    // Protege le HTML quand on affiche une valeur.
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}

function eillusion_lire_toutes_les_lignes($resultat) {
    $lignes = array();

    if (!$resultat) {
        return $lignes;
    }

    while ($ligne = mysqli_fetch_assoc($resultat)) {
        $lignes[] = $ligne;
    }

    return $lignes;
}

function eillusion_get_salles($CONNEXION) {
    $sql = "SELECT * FROM salle ORDER BY id_salle";
    $resultat = mysqli_query($CONNEXION, $sql);

    return eillusion_lire_toutes_les_lignes($resultat);
}

function eillusion_get_salle($CONNEXION, $idSalle) {
    $idSalle = (int) $idSalle;

    $sql = "SELECT * FROM salle WHERE id_salle = $idSalle";
    $resultat = mysqli_query($CONNEXION, $sql);

    return mysqli_fetch_assoc($resultat);
}

function eillusion_get_elements($CONNEXION, $idSalle) {
    $idSalle = (int) $idSalle;

    $sql = "SELECT element_expo.titre, element_expo.description
            FROM contenir
            JOIN element_expo ON element_expo.id_element = contenir.id_element
            WHERE contenir.id_salle = $idSalle";

    $resultat = mysqli_query($CONNEXION, $sql);

    return eillusion_lire_toutes_les_lignes($resultat);
}

function eillusion_get_creneaux($CONNEXION, $idSalle = 0) {
    $idSalle = (int) $idSalle;
    $filtreSalle = "";

    if ($idSalle > 0) {
        $filtreSalle = "WHERE creneau.id_salle = $idSalle";
    }

    $sql = "SELECT creneau.*, salle.nom_salle,
                   (SELECT COUNT(*)
                    FROM inscription
                    WHERE inscription.id_creneau = creneau.id_creneau) AS nb_inscrits
            FROM creneau
            JOIN salle ON salle.id_salle = creneau.id_salle
            $filtreSalle
            ORDER BY creneau.date_creneau, creneau.heure_debut";

    $resultat = mysqli_query($CONNEXION, $sql);
    $creneaux = eillusion_lire_toutes_les_lignes($resultat);

    // On ajoute le nombre de places restantes dans chaque creneau.
    foreach ($creneaux as $numero => $creneau) {
        $jauge = (int) $creneau['jauge'];
        $nbInscrits = (int) $creneau['nb_inscrits'];
        $placesRestantes = $jauge - $nbInscrits;

        $creneaux[$numero]['places_restantes'] = $placesRestantes;
    }

    return $creneaux;
}

function eillusion_get_creneau($CONNEXION, $idCreneau) {
    $idCreneau = (int) $idCreneau;

    $sql = "SELECT creneau.*, salle.nom_salle,
                   (SELECT COUNT(*)
                    FROM inscription
                    WHERE inscription.id_creneau = creneau.id_creneau) AS nb_inscrits
            FROM creneau
            JOIN salle ON salle.id_salle = creneau.id_salle
            WHERE creneau.id_creneau = $idCreneau";

    $resultat = mysqli_query($CONNEXION, $sql);
    $creneau = mysqli_fetch_assoc($resultat);

    if ($creneau) {
        $jauge = (int) $creneau['jauge'];
        $nbInscrits = (int) $creneau['nb_inscrits'];
        $creneau['places_restantes'] = $jauge - $nbInscrits;
    }

    return $creneau;
}

function eillusion_date_label($dateSql) {
    $timestamp = strtotime($dateSql);

    $jours = array(
        'Sunday' => 'Dimanche',
        'Monday' => 'Lundi',
        'Tuesday' => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday' => 'Jeudi',
        'Friday' => 'Vendredi',
        'Saturday' => 'Samedi'
    );

    $mois = array(
        'January' => 'janvier',
        'February' => 'fevrier',
        'March' => 'mars',
        'April' => 'avril',
        'May' => 'mai',
        'June' => 'juin',
        'July' => 'juillet',
        'August' => 'aout',
        'September' => 'septembre',
        'October' => 'octobre',
        'November' => 'novembre',
        'December' => 'decembre'
    );

    $jourAnglais = date('l', $timestamp);
    $moisAnglais = date('F', $timestamp);

    $jourFrancais = $jours[$jourAnglais];
    $moisFrancais = $mois[$moisAnglais];

    return $jourFrancais . ' ' . date('j', $timestamp) . ' ' . $moisFrancais . ' ' . date('Y', $timestamp);
}

function eillusion_date_courte($dateSql) {
    $timestamp = strtotime($dateSql);
    return date('d/m/Y', $timestamp);
}

function eillusion_heure($heureSql) {
    return substr($heureSql, 0, 5);
}

function eillusion_page_title($titre) {
    return $titre . ' - E-LLUSION';
}
?>
