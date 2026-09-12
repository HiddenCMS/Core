# HiddenCMS 0.7.5

## Corrections

- La liste des addons se recharge automatiquement apres une installation Composer reussie. La fermeture de la modale ne court-circuite plus le rafraichissement.
- Les filtres Type et Etat sont conserves dans l'onglet apres un rechargement ou un retour sur la page des addons.
- Un filtre sauvegarde invalide est ignore ; la page reste utilisable si le stockage du navigateur est indisponible.

## Compatibilite et migrations

- PHP >=8.1 conserve. Aucune modification des contraintes des addons.
- Aucune migration SQL ; schema 0.7.1 conserve.
- Aucune action manuelle apres la mise a jour.

Validation : syntaxe PHP et JavaScript, verification des differences et test local de conservation des filtres apres rechargement. Installation neuve, installation reelle d'un addon et mise a jour complete non retestees pour cette release.
