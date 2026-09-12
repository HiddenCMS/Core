# HiddenCMS 0.7.7

- Classes CSS configurables sur chaque zone du live editor.
- Helpers no-padding, no-margin, parent-height et parent-width, avec classes personnalisees du theme.
- Conservation des classes lors des modifications de lignes, colonnes et widgets ou de la duplication des dispositions.
- Sauvegarde protegee par un jeton de formulaire et filtrage des classes CSS.

PHP >=8.1. Aucune migration SQL : schema 0.7.1 conserve. Les dispositions historiques restent compatibles ; les nouvelles metadonnees utilisent une enveloppe JSON uniquement lorsque des classes sont renseignees. Ne pas modifier ces dispositions avec une version anterieure du core.

Validation : huit tests de compatibilite, persistance et rendu front ; sauvegarde, reouverture et suppression des classes testees dans le live editor. Syntaxe PHP et diff verifies. Installation neuve et mise a jour complete non retestees pour cette release.
