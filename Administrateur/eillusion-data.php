<?php
// Fonctions simples utilisees par les pages administrateur.

function eillusion_lire_lignes_admin($resultat) {
    $lignes = array();

    if (!$resultat) {
        return $lignes;
    }

    while ($ligne = mysqli_fetch_assoc($resultat)) {
        $lignes[] = $ligne;
    }

    return $lignes;
}

function eillusion_db_salles($CONNEXION) {
    $sql = "SELECT id_salle, nom_salle FROM salle ORDER BY id_salle";
    $resultat = mysqli_query($CONNEXION, $sql);

    return eillusion_lire_lignes_admin($resultat);
}

function eillusion_get_creneaux($CONNEXION, $idSalle = 0) {
    $idSalle = (int) $idSalle;
    $filtreSalle = "";

    if ($idSalle > 0) {
        $filtreSalle = "WHERE creneau.id_salle = $idSalle";
    }

    $sql = "SELECT creneau.id_creneau, creneau.id_salle, creneau.date_creneau,
                   creneau.heure_debut, creneau.heure_fin, creneau.jauge,
                   salle.nom_salle,
                   (SELECT COUNT(*)
                    FROM inscription
                    WHERE inscription.id_creneau = creneau.id_creneau) AS nb_inscrits
            FROM creneau
            JOIN salle ON salle.id_salle = creneau.id_salle
            $filtreSalle
            ORDER BY creneau.date_creneau, creneau.heure_debut";

    $resultat = mysqli_query($CONNEXION, $sql);
    $creneaux = eillusion_lire_lignes_admin($resultat);

    foreach ($creneaux as $numero => $creneau) {
        $jauge = (int) $creneau['jauge'];
        $nbInscrits = (int) $creneau['nb_inscrits'];
        $placesRestantes = $jauge - $nbInscrits;

        $creneaux[$numero]['places_restantes'] = $placesRestantes;
    }

    return $creneaux;
}

function eillusion_date_courte($dateSql) {
    $timestamp = strtotime($dateSql);
    return date('d/m/Y', $timestamp);
}

function eillusion_heure($heureSql) {
    return substr($heureSql, 0, 5);
}
?>
