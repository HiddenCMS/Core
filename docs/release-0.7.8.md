# HiddenCMS 0.7.8

- Nouveau tableau de bord : frequentation sur 30 jours, membres, contenus recents, elements a traiter et etat du site.
- Configuration SMTP depuis les parametres, mot de passe chiffre et email de test.
- Statistiques : visites, visiteurs par jour, pages les plus vues, filtres et export CSV.
- Collecte de frequentation uniquement apres consentement ; identifiants quotidiens conserves deux jours et compteurs agreges treize mois.
- Affichage initial du panneau de consentement en absence de choix.
- Retrait des helpers sur les zones ; les dispositions utilisant les anciennes metadonnees restent lisibles.

PHP >=8.1. Migration automatique du schema vers 0.7.2 pour les tables de statistiques. Les helpers restent disponibles sur les widgets. Altitude 0.3.3 propose une zone Slider sans padding.

Validation : tests SMTP, statistiques, consentement, tableau de bord et compatibilite des dispositions ; rendu du tableau de bord controle sur ordinateur et telephone. Installation neuve, mise a jour complete et envoi SMTP reel non retestes. La suite JavaScript historique du consentement necessite jsdom, absent de cet environnement.
