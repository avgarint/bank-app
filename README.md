# bank-app

Ce projet est une application web de gestion bancaire développée avec le framework Symfony. Elle permet aux utilisateurs de créer un compte, d’effectuer des virements entre utilisateurs, et d’autres opérations bancaires.

Installation et Configuration

1. Clonez le dépôt GitHub

Commencez par cloner le projet depuis son dépôt GitHub. Exécutez la commande suivante dans votre terminal :
```
git clone https://github.com/avgarint/bank-app
```
```
cd src/
```
2. Installez les dépendances

Le projet utilise Symfony et ses dépendances. Vous devez exécuter la commande suivante pour installer toutes les dépendances nécessaires :
```
composer install
```
3. Configurez la base de données

Le projet nécessite PostgreSQL pour fonctionner.
	1.	Assurez-vous que PostgreSQL est installé et configuré sur votre machine.
	2.	Créez une nouvelle base de données PostgreSQL pour le projet. Par exemple :
```
php/bin/console doctrine:database:create nom_de_la_base_de_donnees;
```	
3.	Configurez les informations de connexion à la base de données dans le fichier .env à la racine du projet. Recherchez la ligne commençant par DATABASE_URL et mettez-la à jour comme suit :
```
DATABASE_URL="postgresql://<username>:<password>@127.0.0.1:5432/<nom_de_la_base_de_donnees>?serverVersion=14&charset=utf8"
```
•	Remplacez <username> par votre nom d’utilisateur PostgreSQL.
•	Remplacez <password> par votre mot de passe PostgreSQL.
•	Remplacez <nom_de_la_base_de_donnees> par le nom de votre base.
	
4.	Appliquez les migrations pour générer les tables dans la base de données :
```
php bin/console doctrine:migration:migrate
```
5. Démarrez le serveur

Pour démarrer le serveur de développement Symfony, exécutez :
```
symfony server:start
```
L’application sera accessible à l’adresse par défaut : http://127.0.0.1:8000.

Fonctionnalités

•	Création de comptes utilisateur.
•	Connexion sécurisée.
•	Virements entre utilisateurs.

Prérequis

•	PHP >= 8.1
•	Composer
•	PostgreSQL >= 14

Auteur

groupe 

Cela fournit un guide clair pour les utilisateurs souhaitant configurer et lancer le projet sur leur machine.

# Wire_frame :

![wireframe projet php v2-1](https://github.com/user-attachments/assets/1915628c-493e-4182-a56b-121204ab9d71)

# MCD :

![MCD serv WEB](https://github.com/user-attachments/assets/b27bb185-8acf-4da9-a419-b80d8f4497c8)
