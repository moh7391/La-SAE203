<?php
require_once 'data.php';
$pageTitle = 'Accueil - Exposition SAE 203';
require_once 'includes/header.php';
?>

<section class="hero">
    <p class="eyebrow">Prototype local</p>
    <h1>Service Web d’inscription à l’exposition</h1>
    <p>
        Ce site présente une exposition et permet de simuler une inscription
        sans utiliser de base de données SQL.
    </p>
    <div class="hero-actions">
        <a class="btn btn-primary" href="inscription.php">S’inscrire</a>
        <a class="btn btn-secondary" href="exposition.php">Voir l’exposition</a>
    </div>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <span><?= count($elements) ?></span>
        <p>Éléments exposés</p>
    </article>
    <article class="stat-card">
        <span><?= count($participants) ?></span>
        <p>Participants exemples</p>
    </article>
    <article class="stat-card">
        <span><?= count($inscriptions) ?></span>
        <p>Inscriptions exemples</p>
    </article>
</section>

<section class="card">
    <div class="section-title">
        <h2>Présentation</h2>
    </div>
    <p>
        Ce prototype correspond à la partie codage du site en PHP/HTML/CSS.
        Il est fait pour être ouvert rapidement en local avec XAMPP.
    </p>
</section>

<?php require_once 'includes/footer.php'; ?>
