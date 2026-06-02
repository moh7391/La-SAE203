<?php
// Données et fonctions partagées pour les pages visiteurs.
// Ce fichier ne modifie pas la base : il sert uniquement à garder une mise en page propre
// tout en continuant à utiliser les tables existantes salle / creneau / participant / inscription.

function e($valeur) {
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}

function eillusion_lower($texte) {
    if (function_exists('mb_strtolower')) {
        return mb_strtolower((string) $texte, 'UTF-8');
    }
    return strtolower((string) $texte);
}

function eillusion_salles() {
    return array(
        '001' => array(
            'code' => '001',
            'titre' => 'Bla Bla',
            'nom_long' => 'Reflets & Miroirs',
            'description' => "Plongez dans un labyrinthe de miroirs où la perception se brouille. Une installation immersive qui questionne notre rapport à l'image et au double numérique.",
            'resume' => "Plongez dans un labyrinthe de miroirs où la perception se brouille. Une installation immersive...",
            'tags' => array('Installation miroirs interactifs', 'Projection mapping', 'Reflet augmenté'),
            'duration' => '12 MIN',
            'capacity' => '8 personnes',
            'theme' => 'Immersif'
        ),
        '002' => array(
            'code' => '002',
            'titre' => 'Ble Ble',
            'nom_long' => 'Lumières Liquides',
            'description' => "Des projections fluides, mouvantes et hypnotiques. Un espace où la lumière devient matière, et où le visiteur devient acteur de l'œuvre.",
            'resume' => "Des projections fluides, mouvantes et hypnotiques. Un espace où la lumière devient matière...",
            'tags' => array('Projection fluide générative', 'Sculpture lumineuse', 'Son spatialisé'),
            'duration' => '12 MIN',
            'capacity' => '8 personnes',
            'theme' => 'Immersif'
        ),
        '005' => array(
            'code' => '005',
            'titre' => 'Bli Bli',
            'nom_long' => 'Pixels & Glitch',
            'description' => "L'esthétique pixelisée et le glitch art prennent vie. Une exploration de l'erreur numérique comme matière artistique brute et poétique.",
            'resume' => "L'esthétique pixelisée et le glitch art prend vie. Une exploration de l'erreur numérique...",
            'tags' => array('Glitch art temps réel', 'Pixel installations', 'Sound design 8-bit'),
            'duration' => '12 MIN',
            'capacity' => '8 personnes',
            'theme' => 'Immersif'
        ),
        '021' => array(
            'code' => '021',
            'titre' => 'Blo Blo',
            'nom_long' => 'Réalité Augmentée',
            'description' => "Superposez le réel et le virtuel grâce à des dispositifs en réalité augmentée. L'illusion devient palpable, l'imaginaire prend forme devant vos yeux.",
            'resume' => "Superposez le réel et le virtuel grâce à des dispositifs en réalité augmentée. L'illusion...",
            'tags' => array('Expérience AR mobile', 'Hologrammes', 'Capteurs de mouvement'),
            'duration' => '12 MIN',
            'capacity' => '8 personnes',
            'theme' => 'Immersif'
        )
    );
}

function eillusion_salle($code) {
    $salles = eillusion_salles();
    $code = str_pad(preg_replace('/\D/', '', (string) $code), 3, '0', STR_PAD_LEFT);
    if (isset($salles[$code])) {
        return $salles[$code];
    }
    return $salles['001'];
}

function eillusion_db_salles($CONNEXION) {
    $salles = array();
    if (!$CONNEXION) {
        return $salles;
    }
    $res = mysqli_query($CONNEXION, "SELECT id_salle, nom_salle FROM salle ORDER BY id_salle");
    if ($res) {
        while ($ligne = mysqli_fetch_assoc($res)) {
            $salles[] = $ligne;
        }
    }
    return $salles;
}

function eillusion_salle_db_id($CONNEXION, $code) {
    $code = str_pad(preg_replace('/\D/', '', (string) $code), 3, '0', STR_PAD_LEFT);
    $salle = eillusion_salle($code);
    $dbSalles = eillusion_db_salles($CONNEXION);

    foreach ($dbSalles as $db) {
        if ((int) $db['id_salle'] === (int) $code) {
            return (int) $db['id_salle'];
        }
    }

    foreach ($dbSalles as $db) {
        $nom = eillusion_lower($db['nom_salle']);
        if (strpos($nom, eillusion_lower($code)) !== false
            || strpos($nom, eillusion_lower($salle['titre'])) !== false
            || strpos($nom, eillusion_lower($salle['nom_long'])) !== false) {
            return (int) $db['id_salle'];
        }
    }

    $ordre = array('001' => 0, '002' => 1, '005' => 2, '021' => 3);
    if (isset($ordre[$code]) && isset($dbSalles[$ordre[$code]])) {
        return (int) $dbSalles[$ordre[$code]]['id_salle'];
    }

    return 0;
}

function eillusion_code_from_db_salle($CONNEXION, $idSalle) {
    $idSalle = (int) $idSalle;
    $codes = array('001', '002', '005', '021');
    $dbSalles = eillusion_db_salles($CONNEXION);

    foreach ($codes as $code) {
        if (eillusion_salle_db_id($CONNEXION, $code) === $idSalle) {
            return $code;
        }
    }

    if ($idSalle === 1) return '001';
    if ($idSalle === 2) return '002';
    if ($idSalle === 5) return '005';
    if ($idSalle === 21) return '021';

    foreach ($dbSalles as $index => $db) {
        if ((int) $db['id_salle'] === $idSalle && isset($codes[$index])) {
            return $codes[$index];
        }
    }

    return '001';
}

function eillusion_get_creneaux($CONNEXION, $idSalle = 0) {
    $idSalle = (int) $idSalle;
    $where = '';
    if ($idSalle > 0) {
        $where = "WHERE creneau.id_salle = $idSalle";
    }

    $sql = "SELECT creneau.id_creneau, creneau.id_salle, creneau.date_creneau,
                   creneau.heure_debut, creneau.heure_fin, creneau.jauge, salle.nom_salle,
                   (SELECT COUNT(*) FROM inscription WHERE inscription.id_creneau = creneau.id_creneau) AS nb_inscrits
            FROM creneau
            JOIN salle ON salle.id_salle = creneau.id_salle
            $where
            ORDER BY creneau.date_creneau, creneau.heure_debut";

    $liste = array();
    $res = mysqli_query($CONNEXION, $sql);
    if ($res) {
        while ($c = mysqli_fetch_assoc($res)) {
            $c['places_restantes'] = (int) $c['jauge'] - (int) $c['nb_inscrits'];
            $liste[] = $c;
        }
    }
    return $liste;
}

function eillusion_get_creneau($CONNEXION, $idCreneau) {
    $idCreneau = (int) $idCreneau;
    if ($idCreneau <= 0) {
        return null;
    }
    $sql = "SELECT creneau.id_creneau, creneau.id_salle, creneau.date_creneau,
                   creneau.heure_debut, creneau.heure_fin, creneau.jauge, salle.nom_salle,
                   (SELECT COUNT(*) FROM inscription WHERE inscription.id_creneau = creneau.id_creneau) AS nb_inscrits
            FROM creneau
            JOIN salle ON salle.id_salle = creneau.id_salle
            WHERE creneau.id_creneau = $idCreneau";
    $res = mysqli_query($CONNEXION, $sql);
    if (!$res) {
        return null;
    }
    $c = mysqli_fetch_assoc($res);
    if (!$c) {
        return null;
    }
    $c['places_restantes'] = (int) $c['jauge'] - (int) $c['nb_inscrits'];
    return $c;
}

function eillusion_date_label($dateSql) {
    $timestamp = strtotime($dateSql);
    $jours = array('Sunday' => 'Dimanche', 'Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi');
    $mois = array('January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril', 'May' => 'mai', 'June' => 'juin', 'July' => 'juillet', 'August' => 'août', 'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre');
    $jour = $jours[date('l', $timestamp)] ?? date('l', $timestamp);
    $moisNom = $mois[date('F', $timestamp)] ?? date('F', $timestamp);
    return $jour . ' ' . date('j', $timestamp) . ' ' . $moisNom;
}

function eillusion_date_courte($dateSql) {
    return date('d/m/Y', strtotime($dateSql));
}

function eillusion_heure($heureSql) {
    return substr((string) $heureSql, 0, 5);
}

function eillusion_reservation_code($idInscription) {
    return 'ELL-' . str_pad((string) ((int) $idInscription), 6, '0', STR_PAD_LEFT);
}

function eillusion_id_from_reservation_code($code) {
    $digits = preg_replace('/\D/', '', (string) $code);
    if ($digits === '') {
        return 0;
    }
    return (int) $digits;
}

function eillusion_page_title($titre) {
    return $titre . ' — E-LLUSION';
}
?>