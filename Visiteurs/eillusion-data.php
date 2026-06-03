<?php
// Fonctions partagees pour les pages visiteurs.
// Tout vient de la BASE DE DONNEES : aucune information ecrite en dur.

// Affiche un texte sans casser le HTML (securite).
function e($valeur) {
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}

// Toutes les salles (table salle).
function eillusion_get_salles($CONNEXION) {
    $salles = array();
    $res = mysqli_query($CONNEXION, "SELECT * FROM salle ORDER BY id_salle");
    while ($s = mysqli_fetch_assoc($res)) {
        $salles[] = $s;
    }
    return $salles;
}

// Une salle a partir de son id.
function eillusion_get_salle($CONNEXION, $idSalle) {
    $idSalle = (int) $idSalle;
    $res = mysqli_query($CONNEXION, "SELECT * FROM salle WHERE id_salle = $idSalle");
    return mysqli_fetch_assoc($res);
}

// Les oeuvres exposees dans une salle (tables contenir + element_expo).
function eillusion_get_elements($CONNEXION, $idSalle) {
    $idSalle = (int) $idSalle;
    $liste = array();
    $res = mysqli_query($CONNEXION,
        "SELECT element_expo.titre, element_expo.description
         FROM contenir
         JOIN element_expo ON element_expo.id_element = contenir.id_element
         WHERE contenir.id_salle = $idSalle");
    while ($el = mysqli_fetch_assoc($res)) {
        $liste[] = $el;
    }
    return $liste;
}

// Les creneaux avec le nombre de places restantes.
// Si $idSalle > 0, on ne prend que les creneaux de cette salle.
function eillusion_get_creneaux($CONNEXION, $idSalle = 0) {
    $idSalle = (int) $idSalle;
    $where = $idSalle > 0 ? "WHERE creneau.id_salle = $idSalle" : "";
    $liste = array();
    $res = mysqli_query($CONNEXION,
        "SELECT creneau.*, salle.nom_salle,
                (SELECT COUNT(*) FROM inscription WHERE inscription.id_creneau = creneau.id_creneau) AS nb_inscrits
         FROM creneau
         JOIN salle ON salle.id_salle = creneau.id_salle
         $where
         ORDER BY creneau.date_creneau, creneau.heure_debut");
    while ($c = mysqli_fetch_assoc($res)) {
        $c['places_restantes'] = (int) $c['jauge'] - (int) $c['nb_inscrits'];
        $liste[] = $c;
    }
    return $liste;
}

// Un creneau a partir de son id (avec places restantes).
function eillusion_get_creneau($CONNEXION, $idCreneau) {
    $idCreneau = (int) $idCreneau;
    $res = mysqli_query($CONNEXION,
        "SELECT creneau.*, salle.nom_salle,
                (SELECT COUNT(*) FROM inscription WHERE inscription.id_creneau = creneau.id_creneau) AS nb_inscrits
         FROM creneau
         JOIN salle ON salle.id_salle = creneau.id_salle
         WHERE creneau.id_creneau = $idCreneau");
    $c = mysqli_fetch_assoc($res);
    if ($c) {
        $c['places_restantes'] = (int) $c['jauge'] - (int) $c['nb_inscrits'];
    }
    return $c;
}

// Mise en forme des dates et heures.
// Affiche le jour en francais, ex : "Jeudi 18 juin 2026".
function eillusion_date_label($dateSql) {
    $jours = array('Sunday'=>'Dimanche','Monday'=>'Lundi','Tuesday'=>'Mardi','Wednesday'=>'Mercredi','Thursday'=>'Jeudi','Friday'=>'Vendredi','Saturday'=>'Samedi');
    $mois = array('January'=>'janvier','February'=>'février','March'=>'mars','April'=>'avril','May'=>'mai','June'=>'juin','July'=>'juillet','August'=>'août','September'=>'septembre','October'=>'octobre','November'=>'novembre','December'=>'décembre');
    $t = strtotime($dateSql);
    return $jours[date('l', $t)] . ' ' . date('j', $t) . ' ' . $mois[date('F', $t)] . ' ' . date('Y', $t);
}
function eillusion_date_courte($dateSql) {
    return date('d/m/Y', strtotime($dateSql));
}
function eillusion_heure($heureSql) {
    return substr((string) $heureSql, 0, 5);
}

// Code de reservation affiche au visiteur (ex: ELL-000012).
function eillusion_reservation_code($idInscription) {
    return 'ELL-' . str_pad((string) ((int) $idInscription), 6, '0', STR_PAD_LEFT);
}
function eillusion_id_from_reservation_code($code) {
    $chiffres = preg_replace('/\D/', '', (string) $code);
    return $chiffres === '' ? 0 : (int) $chiffres;
}

function eillusion_page_title($titre) {
    return $titre . ' — E-LLUSION';
}
?>
