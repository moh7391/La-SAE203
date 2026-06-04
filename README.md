# La-SAE203 — Service web d'inscription à l'exposition E-LLUSION

Site PHP/HTML/CSS + base MySQL (SAÉ 203, MMI1).

## Arborescence

```
La-SAE203/
├── index.php              → redirige vers l'accueil (Visiteurs/index.php)
│
├── Visiteurs/             → ESPACE PUBLIC (les visiteurs)
│   ├── connexion.php          (connexion a la base)
│   ├── eillusion-data.php     (fonctions communes : salles, creneaux...)
│   ├── header.php / footer.php (haut et bas de page communs)
│   ├── css.css                (style du site)
│   ├── index.php              (accueil + liste des salles)
│   ├── salles.php             (toutes les salles)
│   ├── salle.php              (detail d'une salle + ses oeuvres)
│   ├── inscription.php        (formulaire d'inscription en 3 etapes)
│   ├── merci.php              (confirmation d'inscription)
│   ├── mon-espace.php         (gerer sa reservation avec son email)
│   └── contact.php            (contacts / referents)
│
├── Administrateur/        → ESPACE ADMIN (protege par mot de passe)
│   ├── connexion.php          (connexion a la base)
│   ├── login.php              (connexion admin)
│   ├── verif.php              (verifie la session admin)
│   ├── deconnexion.php
│   ├── accueil.php            (tableau de bord)
│   ├── creneaux.php           (gestion des creneaux)
│   ├── salles.php             (gestion des salles / oeuvres)
│   ├── inscrits.php           (liste des inscrits)
│   ├── header.php / footer.php / admin.css
│   └── eillusion-data.php
│
├── assets/                → ressources communes
│   ├── css/style.css
│   ├── fonts/  (polices)
│   └── js/app.js
│
├── sql/                   → fichiers de base de donnees
│   └── bdd_ovh.sql            (structure + donnees)
│
└── test/                  → scripts de test (A SUPPRIMER avant le rendu final)
```

## Comment ca marche
- Le visiteur passe par **Visiteurs/** ; l'administrateur par **Administrateur/**.
- `connexion.php` choisit automatiquement la base : **locale (XAMPP)** sur localhost,
  **OVH** en ligne.
- Tout le contenu (salles, oeuvres, creneaux) vient de la **base de donnees**.

## Base de donnees (7 tables)
administrateur, salle, element_expo, creneau, participant, inscription, contenir.

## Identifiant admin de test
login : `admin` — mot de passe : `admin123`
