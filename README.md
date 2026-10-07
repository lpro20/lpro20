# 📦 Boutique-Stock

> Une solution robuste et moderne de gestion de stock et de point de vente (POS) propulsée par Laravel 12 et Filament, conçue pour optimiser le suivi des inventaires, des ventes et des flux d'achats.

## 🧰 Technologies Utilisées

* **Backend :** PHP 8.2+ & **Laravel 12**
* **Interface Admin / Dashboard :** **Filament v3** (TALL Stack : Tailwind CSS, Alpine.js, Laravel Livewire)
* **Base de données :** MySQL / PostgreSQL
* **Rapports & Exports :** Maatwebsite Excel & Laravel PDF

## ✨ Fonctionnalités Clés

* **📊 Tableaux de bord personnalisés :** Un dashboard unique et adapté à chaque profil utilisateur dès la connexion.
* **📦 Gestion du Stock :** Suivi des articles en temps réel, alertes de stock critique et historique des mouvements.
* **💰 Gestion des Ventes :** Enregistrement des transactions clients et facturation rapide.
* **🤝 Fournisseurs & Achats :** Suivi complet du catalogue des fournisseurs et enregistrement des nouveaux approvisionnements.
* **📈 Rapports Avancés :** Génération de statistiques par période (journalière, mensuelle, personnalisée).
* **📥 Exports Multi-formats :** Téléchargement instantané des rapports au format **PDF** ou **Excel**.

## 🔐 Gestion des Rôles & Autorisations

L'application intègre une gestion stricte des accès (ACL) pour sécuriser vos opérations :

* **👑 Admin :** Accès total à l'application, gestion complète des utilisateurs et configuration globale du système.
* **📦 Gestionnaire de Stock (`gestionnaire_stock`) :** Responsable du catalogue, des fournisseurs, des achats et du suivi des niveaux de stock. *Accès restreint aux ventes et à la gestion des utilisateurs.*
* **💼 Caissier (`caissier`) :** Dédié exclusivement à la saisie des ventes et à la facturation. *Aucun accès aux achats ni à la configuration système.*

## 🚀 Installation et Démarrage

Suivez ces étapes pour lancer le projet dans votre environnement de développement local.

### Prérequis

* PHP >= 8.2
* Composer
* Node.js & NPM
* Un serveur de base de données (MySQL, MariaDB)

### Configuration Locale

1. **Cloner le dépôt :**
   ```bash
   git clone https://github.com
   cd Boutique-Stock
   ```

2. **Installer les dépendances PHP :**
   ```bash
   composer install
   ```

3. **Installer les dépendances Frontend :**
   ```bash
   npm install && npm run dev
   ```

4. **Configurer l'environnement :**
   Dupliquez le fichier `.env.example` et renommez-le en `.env`.
   ```bash
   cp .env.example .env
   ```
   *Ouvrez le fichier `.env` et configurez vos accès à la base de données (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).*

5. **Générer la clé d'application :**
   ```bash
   php artisan key:generate
   ```

6. **Exécuter les migrations et les seeders :** (Si vous avez configuré des comptes de test)
   ```bash
   php artisan migrate --seed
   ```

7. **Lancer le serveur de développement :**
   ```bash
   php artisan serve
   ```
   L'application sera accessible sur `http://127.0.0`.

## 📄 Licence

Ce projet est sous licence MIT.
