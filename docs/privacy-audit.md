# Confidentialite : audit technique initial

Date : 2026-09-03. Perimetre : core HiddenCMS, theme Horizon et addon News,
dans leurs copies locales de developpement. Analyse de code, pas un audit
juridique ni un test exhaustif des flux reseau en production.

## Conclusion

La conformite ne peut pas encore etre annoncee. Les reglages ajoutes dans ce
lot facilitent l'information des personnes ; ils ne gerent ni le consentement
aux traceurs, ni l'effacement, ni les durees de conservation.

## Constats prioritaires

| Priorite | Constat et preuve | Action avant production |
| --- | --- | --- |
| Haute | `hiddencms/views/theme/main.tpl.php` charge `theme/analytics` des qu'un identifiant Analytics existe. Aucun controle central du consentement dans ce chemin. | Laisser Analytics non configure tant que le blocage prealable, le refus et le retrait ne sont pas implementes et testes. |
| Haute | `hiddencms/libraries/bbcode.php` transforme les videos en iframes YouTube directes ; les deux implementations de captcha utilisent Google reCAPTCHA. | Inventorier les appels effectifs ; remplacer ou conditionner les services selon leur fonctionnement et leur base legale. Une autorisation globale des conditions d'inscription n'est pas un consentement aux traceurs. |
| Haute | `modules/user/models/user.php::delete()` marque le compte supprime, detache ses sessions et libere son identifiant custom, mais conserve email, profil et valeurs personnalisees. L'interface d'effacement autonome dans `controllers/index.php::account()` est commentee. | Construire une procedure d'effacement/anonymisation verifiee, incluant fichiers, contributions, messages, champs custom et addons. Ne pas assimiler suppression logique et effacement RGPD. |
| Haute | `hiddencms/core/session.php::login()` ajoute IP, hostname, referent, user agent et donnees d'authentification a `session_history`. Pas de purge de cet historique identifiee dans le code examine. | Definir finalite et duree par categorie, puis implementer une purge planifiee avec mode simulation. |
| Moyenne | La suppression des sessions inactives ne vise que `remember = FALSE`. Le cookie de session est cree pour un an ; HttpOnly et Secure conditionnel sont presents, SameSite n'est pas explicite. | Revoir ensemble duree du cookie, sessions persistantes, rotation, connexions tierces et SameSite sans casser les parcours de connexion. |
| Haute | `modules/user/views/profile.tpl.php` affiche nom/prenom, sexe ou age, date de naissance dans une infobulle, localisation et liens, lorsque le profil est accessible. | Justifier la collecte et ajouter des controles de collecte et de publication, appliques cote serveur et dans chaque rendu. Ne pas seulement masquer les inputs. |
| Moyenne | Les champs personnalises possedent type/options/obligation mais pas de finalite, regle de publication ou conservation (`modules/user/models/fields.php`). | Ajouter la gouvernance des champs ; proteger le champ servant d'identifiant de connexion contre une desactivation qui bloquerait les comptes. |
| Haute | Pas de parcours d'export complet des donnees personnelles identifie. News conserve ses references d'auteur ; son affichage filtre les comptes supprimes, sans preuve d'effacement de la reference stockee. | Definir un contrat d'export/effacement pour les addons et tester le cas d'un addon desactive. |
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
3. Droits : demande, verification d'identite proportionnee, export et effacement,
   contrat addon, traces de traitement minimales et traitement des sauvegardes.
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
reglages. Le contrat global d'export/effacement reste a traiter ; le lot 3 couvre
une premiere partie des services tiers.

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

## References

- CNIL, guide RGPD du developpeur : https://www.cnil.fr/fr/guide-rgpd-du-developpeur
- CNIL, information des personnes : https://www.cnil.fr/fr/conformite-rgpd-information-des-personnes-et-transparence
- CNIL, cookies et traceurs : https://www.cnil.fr/fr/cookies-et-autres-traceurs/regles/cookies/FAQ
- CNIL, modalites de consentement : https://www.cnil.fr/fr/cookies-et-autres-traceurs/regles/cookies/comment-mettre-mon-site-web-en-conformite
- Google, controle du tag : https://developers.google.com/tag-platform/security/guides/privacy

References consultees le 2026-09-03. Les obligations exactes dependent des
traitements et de l'exploitant ; ce document n'est pas une certification.
