## Vroum
Dossiers utiles:

* `Vroum/designdev` pages html pure
* `Vroum/css` css des pages
* `Vroum/js` code des pages

## Guide d'installation

###Pré-requis

Notre site fonctionne avec PHP 7.3.
Nous utilisons MySQL et MariaDB v10 pour notre base de données.

###Connexion à la base de données

Pour installer le site sur votre serveur, vous devez modifier le fichier `Vroum/config/config-template.php` et le renommer en`config.php`.

####Utiliser son propre serveur de mails


Si vous souhaitez utiliser votre propre serveur de mails, vous devez modifier le second champ de cette ligne avec la clé de votre propre API de mail.
Il sera aussi nécessaire de réécrire le fichier `Vroum/src/controler/MailManager` afin de ne pas utiliser l'API fournie par défaut (il est possible d'utiliser la fonction [`mail`](https://www.php.net/manual/en/function.mail.php) fournie par PHP si le serveur SMTP est local, ou la bibliothèque [PHPMailer](https://github.com/PHPMailer/PHPMailer) s'il est nécessaire de se connecter au serveur SMTP à distance).

###Vérifications

Il ne reste plus qu'à vérifier que tout fonctionne !

Voici une liste d'actions à effectuer pour s'assurer que le site est opérationnel.

####Vérifier la base de données

Pour cela, il vous suffit de créer des comptes, des trajets et 
des listes d'amis, puis de tester les différentes fonctionnalités du site.

####Vérifier l'envoi des mails

Pour vérifier l'envoi des mails, veuillez vous diriger vers la page de connexion
 et cliquez sur `"Mot de passe oublié ? Cliquez ici :"`.
Entrez votre adresse mail et validez. Si vous recevez un mail après cela, félicitation, le serveur de mails 
est correctement configuré ! :tada: :tada:

