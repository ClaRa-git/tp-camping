# Site de gestion d'un camping

Base de données pour gérer un campings avec les biens et les réservations.
On y trouve les utilisateurs, les réservations, les locations, les équipements, les types de location, les disponibilités (dans la base les dates rentrées sont celles de NON disponibilité) et les prix.

## Pour mettre en place le projet

## Prérequis

### BIEN LIRE TOUTE LA DOCUMENTATION

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

⚠️ **Attention** : Vérifiez votre .env avec les valeurs de vos variables d'environnement définies précédemment.

## ENJOY :)

## SI LE PROJET N'A PAS ETE CONFIGURE

### METHODO
Après avoir lancé le docker, Faire :

- ccomposer install
- ccomposer create-project symfony/skeleton:"7.3.x-dev" ./ si le www est entièrement vide
- ccomposer require symfony/webpack-encore-bundle

DANS nnpm :
- nnpm (rentrer dans le container)
- npm install
- npm i bootstrap
- npm run build
- puis on lance npm run watch

## POUR REMETTRE A ZERO LA BASE :
- cconsole d:d:d --force
- cconsole d:d:c
- cconsole d:m:m
- cconsole d:f:l

## CREDENTIALS :
database : camping
user : admin
mdp : admin
port : 80 et 3306

## FONCTIONNE AVEC CAMPING-JS