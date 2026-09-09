# Confidentialite : audit technique initial

Date : 2026-09-03, mise a jour le 2026-09-10. Perimetre : core HiddenCMS, theme Horizon et addon News,
dans leurs copies locales de developpement. Analyse de code, pas un audit
juridique ni un test exhaustif des flux reseau en production.

## Conclusion

La conformite ne peut pas encore etre annoncee. L'information, la minimisation
de certains champs, le consentement aux services couverts, l'export personnel et
l'anonymisation du core sont implementes. L'adoption par les addons, les durees
de conservation et la validation des traitements reels en production restent a
traiter.

## Constats prioritaires

| Priorite | Constat et preuve | Action avant production |
| --- | --- | --- |
| Haute | `hiddencms/views/theme/main.tpl.php` charge `theme/analytics` des qu'un identifiant Analytics existe. Aucun controle central du consentement dans ce chemin. | Laisser Analytics non configure tant que le blocage prealable, le refus et le retrait ne sont pas implementes et testes. |
| Haute | `hiddencms/libraries/bbcode.php` transforme les videos en iframes YouTube directes ; les deux implementations de captcha utilisent Google reCAPTCHA. | Inventorier les appels effectifs ; remplacer ou conditionner les services selon leur fonctionnement et leur base legale. Une autorisation globale des conditions d'inscription n'est pas un consentement aux traceurs. |
| Haute | `modules/user/models/user.php::delete()` reste une suppression logique utilisee par l'administration. Le parcours autonome RGPD utilise desormais une procedure distincte d'anonymisation, mais les addons desactives et sauvegardes restent hors de son perimetre. | Ne pas presenter la suppression administrative comme un effacement RGPD. Faire adopter les contrats par les addons et definir le traitement des sauvegardes. |
| Haute | `hiddencms/core/session.php::login()` ajoute IP, hostname, referent, user agent et donnees d'authentification a `session_history`. Pas de purge de cet historique identifiee dans le code examine. | Definir finalite et duree par categorie, puis implementer une purge planifiee avec mode simulation. |
| Moyenne | La suppression des sessions inactives ne vise que `remember = FALSE`. Le cookie de session est cree pour un an ; HttpOnly et Secure conditionnel sont presents, SameSite n'est pas explicite. | Revoir ensemble duree du cookie, sessions persistantes, rotation, connexions tierces et SameSite sans casser les parcours de connexion. |
| Haute | `modules/user/views/profile.tpl.php` affiche nom/prenom, sexe ou age, date de naissance dans une infobulle, localisation et liens, lorsque le profil est accessible. | Justifier la collecte et ajouter des controles de collecte et de publication, appliques cote serveur et dans chaque rendu. Ne pas seulement masquer les inputs. |
| Moyenne | Les champs personnalises possedent type/options/obligation mais pas de finalite, regle de publication ou conservation (`modules/user/models/fields.php`). | Ajouter la gouvernance des champs ; proteger le champ servant d'identifiant de connexion contre une desactivation qui bloquerait les comptes. |
| Haute | L'export du core est implemente et News adopte desormais ses contrats. Les autres addons doivent encore declarer explicitement leur traitement ; les addons desactives restent hors du parcours. | Implementer et tester l'export puis l'effacement dans chaque nouvel addon qui conserve des donnees personnelles, y compris lorsqu'il est desactive. |
| Moyenne | `hiddencms/libraries/core_updater.php` cree des sauvegardes SQL/fichiers sous `backups/updates`. Pas de politique de retention trouvee sur ce chemin. | Definir retention, restrictions d'acces, suppression et procedure de restauration sans reactivation de donnees effacees. Verifier aussi les sauvegardes hebergeur. |
| Moyenne | L'admin charge Fomantic depuis jsDelivr ; TinyMCE est egalement charge depuis un CDN. Horizon utilise des assets locaux mais herite du template principal et de ses integrations. | Inventorier destinataires et transferts ; privilegier les assets auto-heberges. Un appel CDN n'est pas automatiquement un traceur soumis a consentement. |

## Cartographie initiale

| Donnees | Stockage ou flux | Usage a confirmer avec l'exploitant |
| --- | --- | --- |
| Pseudo, email, mot de passe hache, statut | `user` | Gestion et authentification des comptes |
| Profil, sexe, naissance, photos, liens | `user_profile`, fichiers | Profil communautaire ; necessite de chaque champ a justifier |
| Champs personnalises, index de connexion | `user_field_value`, `user_login` | Finalite definie par chaque site ; index unique si identifiant custom |
| Sessions et connexions | `session`, `session_history` | Authentification, securite, administration |
| Contributions et messages | Tables des modules ; News ; fichiers | Publication ou echanges prives ; droits des autres participants a concilier |
| Donnees recopiees | Sauvegardes du core, logs serveur et sauvegardes hebergeur | Reprise et securite ; perimetre hebergeur non audite |
| Requetes tierces | Analytics, reCAPTCHA, videos, CDN, authentificateurs configures | A mesurer sur l'installation cible, avant et apres choix utilisateur |

## Lot implemente

- Parametres > Confidentialite : responsable du traitement, contact email,
  selection d'une page publiee accessible aux visiteurs dans la langue courante.
- Lien d'information et contact dans le formulaire d'inscription, sans case
  obligatoire d'acceptation de la politique et sans les confondre avec la charte.
- Horizon affiche ces liens dans son footer, meme si la region footer est vide.
- Une page depubliee, supprimee ou interdite aux visiteurs n'est plus proposee
  comme lien. Le contenu de la politique reste a rediger et valider par le site.
- Aucun compte modifie, aucune purge, aucune duree arbitraire activee. Pas de
  migration SQL necessaire : utilisation des parametres existants.

## Mise en service de ce lot

1. Rediger une page contenant les traitements effectivement realises :
   responsable/contact, finalites et bases legales, donnees requises/facultatives,
   destinataires, conservation, transferts eventuels, droits et reclamation CNIL.
2. Publier la page et autoriser les visiteurs. Verifier egalement son acces avec
   les differents groupes connectes et les langues utilisees.
3. Selectionner cette page et renseigner le contact dans Confidentialite.
4. Verifier les liens dans l'inscription et le footer. Aucun texte juridique
   ou responsable fictif n'est publie automatiquement.
5. Le lot 3 ci-dessous ajoute le gestionnaire pour Analytics et les videos.
   Recenser et integrer explicitement les autres contenus riches et addons.

## Prochains lots

1. Completer la minimisation : champs personnalises, photos, liens et limitation des journaux.
2. Completer les services tiers : alternative a reCAPTCHA, autres integrations
   et audit reseau de l'installation de production.
3. Droits : adoption du contrat d'effacement par les addons, validation des
   contenus conserves et traitement des sauvegardes.
4. Retention : decisions de l'exploitant, simulation puis taches programmees.
5. Validation de production : hebergeur/sous-traitants, registre, acces admin,
   HTTPS, restauration, gestion des incidents et revue juridique adaptee.

## Lot 2 : champs du profil (implemente)

Les six champs prenom, nom, naissance, sexe, pays et localisation disposent
chacun d'un mode dans Parametres > Confidentialite :

- `Desactive` : absent des formulaires front et admin. Une soumission fabriquee
  ne peut pas mettre a jour ce champ via le formulaire de profil.
- `Prive` : modifiable par la personne et les administrateurs autorises ; absent
  des profils publics et des apercus fournis par le core.
- `Public` : modifiable et affiche dans le profil, sous reserve des droits
  d'acces habituels a cette page. La naissance ne publie que l'age, jamais la
  date complete dans une infobulle.

Une desactivation conserve les valeurs existantes en base. Ce n'est pas un
effacement ; leur conservation reste a justifier et traiter dans un futur lot.
Les fonctions de rendu ne retournent pas de valeur publique en mode prive ou
desactive, meme pour un administrateur consultant un profil public. Le nom
compact et l'avatar par defaut suivent ces regles ; un avatar importe demeure
inchange. Le pays vide n'est plus preselectionne sur le premier pays de la liste.

Pour une installation existante sans ces parametres, le comportement public
anterieur est conserve jusqu'au choix de l'administrateur. Les nouvelles
installations creees depuis `install/DATABASE.sql` desactivent les six champs
par defaut. Aucune migration de donnees ni purge automatique n'est effectuee.

Horizon herite des vues de profil du core, et News utilise son rendu d'avatar.
Un theme ou addon qui accede directement aux colonnes de `user_profile` doit
appliquer les memes regles : `privacy_profile_collects($field)` pour la collecte,
`privacy_profile_value($profile, $field)` pour le rendu public. Ces helpers ne
constituent pas une barriere aux requetes SQL directes d'une extension.

Les champs personnalises, le pseudo, l'email, les liens sociaux, citations,
signatures, photos et donnees de connexion ne sont pas couverts par ces six
reglages. Leur export est couvert par le lot 4 et leur effacement par le lot 5 ;
leur minimisation reste a traiter. Le lot 3 couvre une premiere partie des
services tiers.

Verification : `php tools/test-user-fields.php --isolated-database` teste les
trois modes, les rendus public/compact, la non-divulgation via l'avatar et le
rejet effectif des modifications de champs desactives par POST forge.

## Lot 3 : consentement aux services tiers (implemente)

- Un gestionnaire commun au core, sans dependance JavaScript tierce, propose
  Tout refuser / Tout accepter / Personnaliser avec le meme style visuel.
- Choix independants pour YouTube et Analytics. Aucune case preselectionnee ;
  fermer la fenetre ne cree pas de consentement. L'absence de JavaScript bloque
  les integrations couvertes par ce lot.
- Le choix est conserve 180 jours dans `localStorage` sur cette origine, pour
  le refus comme pour l'accord. Il n'est pas associe au compte. La version des
  informations et les dates sont enregistrees avec les choix, sans identifiant
  visiteur ni nouvelle table. Ce stockage seul n'est pas un dispositif complet
  de preuve du consentement : archiver les versions du mecanisme et valider la
  strategie de preuve avec l'exploitant avant production.
- Donnees invalides, choix expire, nouvelle version ou stockage inaccessible :
  aucun chargement initial autorise. Expiration, retour sur une page conservee
  et changements dans les autres onglets sont pris en compte.
- Horizon expose Gerer mes cookies dans son footer. Le core propose un bouton
  de secours pour les themes sans cette integration. La banniere ne s'impose
  pas sur une page sans service optionnel utilise ; les preferences restent
  accessibles, meme sur une page sans video.
- GA4 accepte les identifiants `G-...`, et ne charge jamais le tag en admin.
  Les anciennes valeurs `UA-...` restent editables mais ne sont plus chargees.
  Pas de tag Google ni de ping de consentement avant accord. Les finalites
  publicitaires restent refusees et Google Signals est desactive.
- Le retrait Analytics active `ga-disable`, supprime les cookies Analytics
  accessibles depuis la page, puis recharge pour eliminer le code deja execute.
  La preference de refus est enregistree avant ce rechargement. Cela peut faire
  perdre une saisie non enregistree ; les cookies de session sont preserves.
- Les videos BBCode et les iframes YouTube des contenus passant par `bbcode()`
  sont remplacees cote serveur par des emplacements sans iframe ni miniature
  distante. Le retrait detruit les iframes, y compris ajoutees dynamiquement.
  Le serveur du site ne peut pas effacer les cookies du domaine YouTube.

### Contrat pour themes et addons

Declarer les services de facon stable, sur chaque page, avant le rendu de
`theme/privacy` (meme liste et meme ordre). Changer cette declaration modifie
la version du consentement et invalide les choix precedents.

```php
privacy_services('example-media', [
    'title' => 'Nom du service',
    'description' => 'Fournisseur, finalite, traceurs et effet du refus.',
    'hosts' => ['media.example.test']
]);
echo privacy_embed('example-media', 'https://media.example.test/embed/123');
```

`privacy_embed()` ne produit aucune ressource distante active. Les URL doivent
etre HTTPS et le nom d'hote doit correspondre exactement a la declaration.
Le filtrage HTML utilise DOMDocument et ne remplace pas un assainisseur XSS.
Un theme personnalise doit conserver `theme/privacy` et `privacy.css` du
template principal, et peut utiliser `privacy_preferences_link()` au footer.

Pour un service JavaScript, attendre que le gestionnaire soit disponible
(script `defer` apres `privacy.js`, ou `DOMContentLoaded`), puis appeler :

```js
window.HiddenCMSPrivacy.register('example-media', {
    enable: function () { /* Charger le service ici, jamais avant. */ },
    disable: function () { /* Arreter le service et retirer ses ressources. */ },
    reloadOnRevoke: true
});
```

`hasConsent(id)` et l'evenement `hiddencms:privacychange` permettent de lire les
choix. Un service non declare est refuse. L'addon reste responsable de retirer
ses traceurs ; une exception dans l'arret force un rechargement. Ne pas inserer
un iframe/script actif puis esperer qu'un observateur le bloque apres coup.

### Limites et mise en production

- reCAPTCHA reste inchange : sa suppression sans alternative pourrait affaiblir
  les formulaires. La CNIL recommande des alternatives ou protections
  complementaires quand le CAPTCHA necessite un consentement. Ce point reste
  ouvert et est rappele dans Parametres > Confidentialite.
- Images externes, autres lecteurs, scripts injectes par le HTML libre,
  authentificateurs, CDN et rendus d'addons contournant les helpers ne sont pas
  automatiquement bloques. Inventorier leurs destinations et finalites.
- Un nouvel addon ne doit pas lancer son service avant l'enregistrement du
  gestionnaire. Utiliser une declaration globale pour eviter les changements
  de version a chaque navigation.
- Rediger la politique, verifier le fournisseur et les transferts, archiver les
  versions du bandeau et tester les requetes reelles de l'installation cible.
  Aucune activation d'Analytics ni modification de contenu en base n'a ete faite.
- Le controle des cookies existants est limite aux noms/domaines/chemins
  accessibles depuis la page : il ne purge pas les donnees chez les fournisseurs.

### Verification

- `php tools/test-privacy.php` : generation HTML, BBCode, contenu riche,
  identifiants, absence de chargement en admin, echappement et contrat addon.
- `node tools/test-privacy.cjs` avec jsdom 26 accessible via `NODE_PATH` :
  refus, accord granulaire, retrait, cookies, expiration, onglets, stockage,
  contenus dynamiques. Les ressources distantes sont interceptees en test,
  aucune donnee n'est envoyee a Google ; ce n'est pas un audit reseau reel GA4.
- `php tools/test-user-fields.php --isolated-database` : non-regression profil
  et inscription, dans une base temporaire, sans modifier les comptes du site.
- Verification visuelle sur Horizon : panneau, fermeture et acces footer.

## Lot 4 : export des donnees personnelles (implemente)

- La personne connectee peut demander une archive depuis son compte, section
  `Mes donnees personnelles`. Le mot de passe actuel et le jeton CSRF sont
  verifies avant toute generation.
- L'archive ZIP contient un manifeste JSON structure : compte, profil, champs
  personnalises, groupes, comptes externes, historique de connexion, droits,
  contributions du core, messagerie, fichiers et suivi. Les fichiers dont la
  personne est proprietaire sont joints lorsqu'ils sont encore lisibles.
- Les mots de passe, identifiants de session, donnees brutes de session et
  chemins de stockage serveur sont exclus. Le telechargement est servi avec des
  en-tetes interdisant sa mise en cache et le fichier temporaire est supprime.
- Chaque module dispose de `personal_data_export($user)`. `NULL` signale un
  contrat non implemente ; un tableau vide declare explicitement l'absence de
  donnees. Le manifeste indique `included`, `not_implemented` ou `error` pour
  rendre les lacunes visibles sans bloquer l'export du core.

Limites : l'archive est generee de facon synchrone et n'est pas chiffree. Elle
doit donc etre testee avec des comptes volumineux et transmise uniquement en
HTTPS. News fournit les actualites redigees et toutes leurs traductions. Les
addons desactives ne sont pas charges par ce premier contrat ; leur stockage
doit etre inventorie lors de l'effacement. Cet export ne constitue ni une purge
ni une procedure d'anonymisation.

Verification : `php tools/test-user-fields.php --isolated-database` controle le
contenu structure, l'exclusion des secrets et la creation d'une archive ZIP
lisible sans modifier les comptes du site.

## Lot 5 : effacement et anonymisation (implemente)

- La personne peut demander la suppression depuis son compte apres verification
  du mot de passe et confirmation explicite du caractere irreversible.
- Le delai de retractation est configurable dans Parametres > Confidentialite
  a 7, 14 ou 30 jours. Une demande en attente est visible et annulable depuis
  le meme ecran, avec une nouvelle verification du mot de passe.
- A echeance, l'identite du compte est anonymisee et son mot de passe remplace
  par une valeur aleatoire inconnue. Profil, champs personnalises, comptes
  externes, groupes, permissions individuelles, jetons, sessions, historique
  de connexion, suivi, boite de reception et fichiers personnels sont supprimes.
- Les commentaires et messages deja partages sont conserves pour ne pas retirer
  le contenu appartenant aussi aux autres participants ; ils sont attribues au
  compte `Utilisateur supprime`. Cette conservation doit correspondre a une
  finalite et une base legale documentees par l'exploitant.
- Le dernier administrateur actif est protege. Chaque execution conserve sur le
  compte anonymise un rapport technique minimal, sans l'ancienne identite.
- Les modules disposent de `personal_data_erase($user)`. Un retour `NULL` rend
  l'absence d'implementation visible dans le rapport ; un tableau confirme que
  le module a traite la demande. Une exception annule la transaction SQL.

La commande suivante traite jusqu'a 25 demandes arrivees a echeance :

```sh
php tools/core.php privacy-purge
```

Elle doit etre executee regulierement par le cron de l'hebergement. Un nombre
maximal de demandes peut etre passe en second argument, entre 1 et 100. Les
fichiers sont retires apres la transaction SQL ; un echec de suppression est
consigne dans le rapport afin de permettre une reprise manuelle.

News conserve les actualites comme contributions editoriales et les rattache au
compte anonymise par le core. Elles restent visibles sous le nom `Utilisateur
supprime`, sans lien vers un profil. L'export et ce comportement sont verifies
par `php tests/privacy-contract.php /chemin/vers/hiddencms` dans le depot News.

Limites : les autres addons desactives et les sauvegardes ne sont pas effaces
par cette commande. Avant production, chaque addon conservant des donnees doit
adopter le contrat, et la politique de sauvegarde doit garantir que la
restauration ne reactive pas silencieusement une identite effacee.

## Lot 6 : politique de conservation (implemente)

- Parametres > Confidentialite permet de definir une duree pour l'historique
  de connexion, les sessions, les journaux en base, les rapports d'effacement,
  les sauvegardes de mise a jour et les fichiers de journalisation. Toutes les
  regles sont desactivees par defaut (`0`) : l'exploitant doit choisir des
  durees adaptees a ses finalites et a ses obligations avant toute purge.
- Une simulation inventorie les elements arrives a echeance sans les supprimer.
  La purge reelle exige une action distincte et une confirmation dans
  l'administration. Le dernier resultat, son mode et sa date sont conserves
  dans un rapport technique afin de faciliter le controle d'exploitation.
- Les suppressions SQL sont transactionnelles. Les repertoires de sauvegarde et
  les fichiers de log ne sont retires qu'apres validation de la transaction.
  Les trois sauvegardes de mise a jour les plus recentes sont toujours
  preservees, meme lorsqu'elles depassent la duree configuree.
- La regle relative aux comptes inactifs est volontairement un audit : elle
  compte les comptes concernes, mais ne les supprime ni ne les anonymise. Une
  suppression automatique demanderait au prealable une procedure d'information,
  de contestation et de conservation des contributions clairement definie.
- Le format actuel des journaux ne permet pas de retirer proprement des lignes
  anciennes dans un fichier actif. La regle ne supprime donc que des fichiers
  entiers dont la date de modification est anterieure a l'echeance.

Simulation sans suppression :

```sh
php tools/core.php privacy-retention
```

Purge effective, a planifier par cron uniquement apres validation des durees :

```sh
php tools/core.php privacy-retention --execute
```

Verification : `php tools/test-user-fields.php --isolated-database` cree des
donnees recentes et expirees dans une base temporaire, controle que la
simulation ne modifie rien, puis que la purge ne retire que les donnees arrivees
a echeance. Le test verifie aussi la conservation des comptes inactifs, le
rapport d'execution et le comportement ferme des valeurs non autorisees.

Limites : les durees doivent encore etre validees traitement par traitement par
l'exploitant et inscrites dans son registre. Les donnees propres aux addons,
les sauvegardes externes a HiddenCMS et les journaux geres par l'hebergeur ne
sont pas couverts automatiquement. Les rapports de conservation eux-memes ne
font pas encore l'objet d'une duree configurable.

## References

- CNIL, guide RGPD du developpeur : https://www.cnil.fr/fr/guide-rgpd-du-developpeur
- CNIL, information des personnes : https://www.cnil.fr/fr/conformite-rgpd-information-des-personnes-et-transparence
- CNIL, cookies et traceurs : https://www.cnil.fr/fr/cookies-et-autres-traceurs/regles/cookies/FAQ
- CNIL, modalites de consentement : https://www.cnil.fr/fr/cookies-et-autres-traceurs/regles/cookies/comment-mettre-mon-site-web-en-conformite
- Google, controle du tag : https://developers.google.com/tag-platform/security/guides/privacy

References consultees le 2026-09-03. Les obligations exactes dependent des
traitements et de l'exploitant ; ce document n'est pas une certification.
