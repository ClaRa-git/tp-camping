# Site de gestion de stock de livres

Base de données pour gérer des séries littéraires.
On y trouve les auteurs, les séries, les livres, les genres, les éditeurs


## Pour mettre en place le projet

## Prérequis

### BIEN LIRE TOUTE LA DOCUMENTATION

## 🔩 Configuration de la base de données

Dans le fichier \`docker-compose.yml\`, redéfinissez les valeurs de la base de données :

```yml
services:
  mariadb:
    environment:
      - MYSQL_ROOT_PASSWORD=mot_de_passe_root
      - MYSQL_DATABASE=database_name
      - MYSQL_USER=user_name
      - MYSQL_PASSWORD=user_password
```

## 🔩 Configuration du fichier .htaccess

Une fois votre projet monté, le point d'entrée de l'application sera dans /public/index.php :
Récupérer le .htaccess à la racine et placé le dans le dossier public.

## 🚀 Démarrage de Docker

Pour démarrer les conteneurs Docker, exécutez :

```bash
docker compose up
```

## ⚙️ Configuration du fichier d'alias

1. Ouvrez le fichier de configuration de votre terminal :

```bash
nano ~/.bashrc
```

1. Ajoutez le script suivant pour charger les alias dynamiquement :

```bash
load_aliases() {
  if [ -f "$(pwd)/aliases.sh" ]; then
      . "$(pwd)/aliases.sh"
  fi
}

# Appeler la fonction chaque fois que le répertoire est changé
cd() {
  builtin cd "$@" && load_aliases
}

# Charger les alias au démarrage du shell si le fichier existe dans le répertoire actuel
load_aliases
```

1. Rechargez votre fichier \`.bashrc\` :

```bash
source ~/.bashrc
```

1. Configurez le fichier \`.bash_profile\` (ou \`.profile\`) :

```bash
nano ~/.bash_profile
```

1. Ajoutez cette ligne si elle n'existe pas :

```bash
if [ -f ~/.bashrc ]; then
    source ~/.bashrc
fi
```

1. Rechargez le fichier \`.bash_profile\` :

```bash
source ~/.bash_profile
```

1. Dans le fichier \`aliases.sh\`, redéfinissez les alias comme souhaité.

## 🛠 Technologies utilisées

- ![PHP](https://img.shields.io/badge/PHP-8.x-787CB5?logo=php) PHP 8.x
- ![Symfony](https://img.shields.io/badge/Symfony-7-black?logo=symfony) Symfony 7
- ![MySQL](https://img.shields.io/badge/MySQL-5.7-4479A1?logo=mysql) MySQL
- ![Composer](https://img.shields.io/badge/Composer-2.x-885630?logo=composer) Composer pour la gestion des dépendances
- ![Node.js](https://img.shields.io/badge/Node.js-20.x-339933?logo=node.js) Node pour la gestion des librairies

## Installation du projet Symfony

```bash
ccomposer install
```

```bash
cconsole d:m:m
```

⚠️ **Attention** : Vérifiez votre .env avec les valeurs de vos variables d'environnement définies précédemment.

## ENJOY :)

## SI LE PROJET N'A PAS ETE CONFIGURE

### METHODO
Après avoir lancé le docker, Faire :

enlever le - dans le "docker-compose" (3 dans le dossier) dans aliases

commenter tout le contenu du fichier assets/bootstrp.js

- ccomposer install
- ccomposer create-project symfony/skeleton:"7.3.x-dev" ./ si le www est entièrement vide
- ccomposer require symfony/webpack-encore-bundle
- dans webpack.config.json on va décommenter « enableSassLoader»
DANS nnpm :
- nnpm (rentrer dans le container)
- npm install
- npm i bootstrap
- npm install sass-loader node-sass --save-dev
- npm run build
- renommer app.css en .scss
- dans app.js on ajoute :
    - import './bootstrap.js';
    - import { Tooltip, Toast, Popover } from 'bootstrap';
    - import './bootstrap';
- dans app.js on renomme :
    - app.css en app.scss
- npm run build
- on ajoute dans base.html.twig
    		{# Librairie font awesome #}
        <!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.3.0/css/all.css">
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>⚫️</text><text y=%221.3em%22 x=%220.2em%22 font-size=%2276%22 fill=%22%23fff%22>sf</text></svg>"> -->
- puis on lance npm run watch

## POUR REMETTRE A ZERO LA BASE :
- cconsole d:d:d --force
- cconsole d:d:c
- cconsole d:m:m
- cconsole d:f:l

## CREDENTIALS :
database : livres
user : admin
mdp : admin
port : 8082 et 3309