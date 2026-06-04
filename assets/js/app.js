document.addEventListener('DOMContentLoaded', function () {
  var body = document.body;
  var boutonsTexte = document.querySelectorAll('[data-font-size]');
  var boutonsContraste = document.querySelectorAll('[data-contrast]');

  function choisirTailleTexte(valeur) {
    body.classList.remove('font-large');

    if (valeur == 'large') {
      body.classList.add('font-large');
    }

    localStorage.setItem('eillusion-font-size', valeur);
    afficherBoutonActif(boutonsTexte, 'data-font-size', valeur);
  }

  function choisirContraste(valeur) {
    body.classList.remove('contrast-high');

    if (valeur == 'high') {
      body.classList.add('contrast-high');
    }

    localStorage.setItem('eillusion-contrast', valeur);
    afficherBoutonActif(boutonsContraste, 'data-contrast', valeur);
  }

  function afficherBoutonActif(boutons, attribut, valeur) {
    var i;
    var bouton;

    for (i = 0; i < boutons.length; i = i + 1) {
      bouton = boutons[i];

      if (bouton.getAttribute(attribut) == valeur) {
        bouton.setAttribute('aria-pressed', 'true');
      } else {
        bouton.setAttribute('aria-pressed', 'false');
      }
    }
  }

  function clicTailleTexte() {
    var valeur = this.getAttribute('data-font-size');
    choisirTailleTexte(valeur);
  }

  function clicContraste() {
    var valeur = this.getAttribute('data-contrast');
    choisirContraste(valeur);
  }

  var tailleEnregistree = localStorage.getItem('eillusion-font-size');
  var contrasteEnregistre = localStorage.getItem('eillusion-contrast');
  var i;

  if (!tailleEnregistree) {
    tailleEnregistree = 'normal';
  }

  if (!contrasteEnregistre) {
    contrasteEnregistre = 'normal';
  }

  choisirTailleTexte(tailleEnregistree);
  choisirContraste(contrasteEnregistre);

  for (i = 0; i < boutonsTexte.length; i = i + 1) {
    boutonsTexte[i].addEventListener('click', clicTailleTexte);
  }

  for (i = 0; i < boutonsContraste.length; i = i + 1) {
    boutonsContraste[i].addEventListener('click', clicContraste);
  }
});
