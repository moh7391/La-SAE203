<?php
require_once 'connexion.php';
require_once 'eillusion-data.php';

$active_page = 'reservation';
$page_title = eillusion_page_title('Ma réservation');
$message = '';
$erreur = '';
$emailRecherche = '';
$codeRecherche = '';
$resultats = array();

function eillusion_reservations_by_email($CONNEXION, $email, $idInscription = 0) {
    $emailSql = mysqli_real_escape_string($CONNEXION, trim($email));
    $idInscription = (int) $idInscription;
    $whereId = $idInscription > 0 ? "AND inscription.id_inscription = $idInscription" : '';

    $sql = "SELECT inscription.id_inscription, participant.id_participant, participant.nom, participant.prenom, participant.email,
                   creneau.id_creneau, creneau.id_salle, creneau.date_creneau, creneau.heure_debut, creneau.heure_fin,
                   salle.nom_salle
            FROM inscription
            JOIN participant ON participant.id_participant = inscription.id_participant
            JOIN creneau ON creneau.id_creneau = inscription.id_creneau
            JOIN salle ON salle.id_salle = creneau.id_salle
            WHERE participant.email = '$emailSql' $whereId
            ORDER BY creneau.date_creneau, creneau.heure_debut";
    $liste = array();
    $res = mysqli_query($CONNEXION, $sql);
    if ($res) {
        while ($ligne = mysqli_fetch_assoc($res)) {
            $liste[] = $ligne;
        }
    }
    return $liste;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'rechercher';
    $emailRecherche = trim($_POST['email'] ?? '');
    $codeRecherche = trim($_POST['code'] ?? '');
    $idInscription = eillusion_id_from_reservation_code($codeRecherche);

    if ($action === 'annuler') {
        $id = (int) ($_POST['id_inscription'] ?? 0);
        $emailSql = mysqli_real_escape_string($CONNEXION, $emailRecherche);
        mysqli_query($CONNEXION,
            "DELETE inscription FROM inscription
             JOIN participant ON participant.id_participant = inscription.id_participant
             WHERE inscription.id_inscription = $id AND participant.email = '$emailSql'");
        $message = 'Inscription annulée.';
        $codeRecherche = '';
        $idInscription = 0;
    }

    if ($action === 'modifier') {
        $id = (int) ($_POST['id_inscription'] ?? 0);
        $nouveauCreneau = (int) ($_POST['nouveau_creneau'] ?? 0);
        $emailSql = mysqli_real_escape_string($CONNEXION, $emailRecherche);

        $creneau = eillusion_get_creneau($CONNEXION, $nouveauCreneau);
        if (!$creneau) {
            $erreur = 'Créneau introuvable.';
        } elseif ((int) $creneau['places_restantes'] <= 0) {
            $erreur = 'Ce créneau est complet.';
        } else {
            mysqli_query($CONNEXION,
                "UPDATE inscription
                 JOIN participant ON participant.id_participant = inscription.id_participant
                 SET inscription.id_creneau = $nouveauCreneau
                 WHERE inscription.id_inscription = $id AND participant.email = '$emailSql'");
            $message = 'Créneau modifié.';
            $idInscription = $id;
            $codeRecherche = eillusion_reservation_code($id);
        }
    }

    if ($emailRecherche === '') {
        $erreur = 'Merci de renseigner votre email.';
    } else {
        $resultats = eillusion_reservations_by_email($CONNEXION, $emailRecherche, $idInscription);
        if (count($resultats) === 0 && $erreur === '' && $message === '') {
            $erreur = 'Aucune réservation trouvée avec ces informations.';
        }
    }
}

require_once 'header.php';
?>
<main class="reservation-page">
  <section class="container">
    <p class="eyebrow">Gérer ma réservation</p>
    <h1 class="pixel-title page">Modifier ou<br>annuler</h1>
    <p class="lead">Entrez votre code de réservation et l'email utilisé pour retrouver votre inscription.</p>

    <?php if ($message !== '') { ?><div class="success"><?php echo e($message); ?></div><?php } ?>
    <?php if ($erreur !== '') { ?><div class="error"><?php echo e($erreur); ?></div><?php } ?>

    <form action="mon-espace.php" method="post" class="panel">
      <input type="hidden" name="action" value="rechercher">
      <div class="field">
        <label for="code">Code de réservation</label>
        <input class="input" type="text" id="code" name="code" placeholder="Ex: ELL-000123" value="<?php echo e($codeRecherche); ?>">
      </div>
      <div class="field" style="margin-top:16px;">
        <label for="email">Email</label>
        <input class="input" type="email" id="email" name="email" value="<?php echo e($emailRecherche); ?>" required>
      </div>
      <div style="margin-top:20px;">
        <button type="submit" class="btn">Rechercher</button>
      </div>
    </form>

    <?php if (count($resultats) > 0) { ?>
      <div class="reservation-result">
        <?php foreach ($resultats as $ins) {
            $listeCreneaux = eillusion_get_creneaux($CONNEXION, 0);
        ?>
          <article class="reservation-item">
            <p class="eyebrow">Réservation <?php echo e(eillusion_reservation_code($ins['id_inscription'])); ?></p>
            <h2><?php echo e($ins['prenom'] . ' ' . $ins['nom']); ?></h2>
            <p><strong>Salle :</strong> <?php echo e($ins['nom_salle']); ?></p>
            <p><strong>Date :</strong> <?php echo e(eillusion_date_label($ins['date_creneau'])); ?> — <?php echo e(eillusion_heure($ins['heure_debut'])); ?> à <?php echo e(eillusion_heure($ins['heure_fin'])); ?></p>

            <div class="reservation-actions">
              <form action="mon-espace.php" method="post">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="id_inscription" value="<?php echo (int) $ins['id_inscription']; ?>">
                <input type="hidden" name="email" value="<?php echo e($emailRecherche); ?>">
                <input type="hidden" name="code" value="<?php echo e(eillusion_reservation_code($ins['id_inscription'])); ?>">
                <label for="c<?php echo (int) $ins['id_inscription']; ?>">Changer de créneau</label>
                <select class="input" id="c<?php echo (int) $ins['id_inscription']; ?>" name="nouveau_creneau" required>
                  <option value="">-- Choisir --</option>
                  <?php foreach ($listeCreneaux as $c) {
                      $places = (int) $c['places_restantes'];
                      $current = (int) $c['id_creneau'] === (int) $ins['id_creneau'];
                      $texte = eillusion_date_courte($c['date_creneau']) . ' - ' . eillusion_heure($c['heure_debut']) . ' à ' . eillusion_heure($c['heure_fin']) . ' - ' . $c['nom_salle'];
                  ?>
                    <option value="<?php echo (int) $c['id_creneau']; ?>" <?php echo $current ? 'selected' : ''; ?> <?php echo ($places <= 0 && !$current) ? 'disabled' : ''; ?>>
                      <?php echo e($texte . ($places <= 0 && !$current ? ' — complet' : '')); ?>
                    </option>
                  <?php } ?>
                </select>
                <button type="submit" class="btn" style="margin-top:12px;">Modifier</button>
              </form>

              <form action="mon-espace.php" method="post">
                <input type="hidden" name="action" value="annuler">
                <input type="hidden" name="id_inscription" value="<?php echo (int) $ins['id_inscription']; ?>">
                <input type="hidden" name="email" value="<?php echo e($emailRecherche); ?>">
                <input type="hidden" name="code" value="<?php echo e(eillusion_reservation_code($ins['id_inscription'])); ?>">
                <button type="submit" class="btn danger">Annuler</button>
              </form>
            </div>
          </article>
        <?php } ?>
      </div>
    <?php } ?>
  </section>
</main>
<?php require_once 'footer.php'; ?>
