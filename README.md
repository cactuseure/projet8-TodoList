# ToDoList

L'application vous permettant de gérer l'ensemble de vos tâches sans effort !

## Prérequis

Avant de commencer, assurez-vous d'avoir installé les logiciels suivants :
- PHP 8.2 ou supérieur
- Composer
- Symfony CLI (facultatif mais recommandé)
- Serveur de base de données (MySQL, PostgreSQL, etc.)
- Git (pour le contrôle de version)

## Installation

Suivez ces instructions pour installer le projet localement pour le développement et les tests.

### Cloner le dépôt

Clonez le dépôt Git en utilisant cette commande :

```bash
git clone https://github.com/cactuseure/projet8-TodoList.git
```
### Installer les dépendances

Accédez au répertoire du projet cloné et installez les dépendances PHP avec Composer :

```bash
cd repository 
composer install
```
### Configurer l'environnement

Copiez le fichier .env en .env.local et configurez vos variables d’environnement, en particulier les paramètres de connexion à la base de données :

```bash
cp .env .env.local
```
Modifiez le fichier .env.local avec les informations de votre base de données et autres paramètres spécifiques.

### Créer la base de données

Créez la base de données en exécutant cette commande :

```bash 
php bin/console doctrine:database:create
```

### Exécuter les migrations

Appliquez les migrations pour mettre à jour la base de données avec les tables nécessaires :
```bash
php bin/console doctrine:migrations:migrate
```

### Générer des Fixtures
Pour remplir la base de données avec des données fictives, utilisez la commande suivante :
```bash
php bin/console doctrine:fixtures:load
```

Cela créer deux utilisateurs : 

- admin
- password

OU

- user
- password

### Démarrer le serveur de développement

Utilisez la commande suivante pour démarrer le serveur de développement intégré de Symfony :
```bash
symfony server:start
```
Si vous n’utilisez pas Symfony CLI :

```bash
php -S localhost:8000 -t public
```
Votre projet devrait maintenant être accessible à l’adresse http://localhost:8000.
## Contribution

Les contributions sont les bienvenues ! Suivez ces étapes pour contribuer :

	1.	Forkez le dépôt.
	2.	Créez une branche pour votre fonctionnalité (git checkout -b feature/ma-nouvelle-fonctionnalité).
	3.	Commitez vos modifications (git commit -m 'Ajout de ma nouvelle fonctionnalité').
	4.	Pushez vers la branche (git push origin feature/ma-nouvelle-fonctionnalité).
	5.	Créez une Pull Request.


