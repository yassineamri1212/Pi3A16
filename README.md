# Symfony Web Application

This is a modular Symfony-based web application. It consists of multiple controllers handling different aspects of the platform, such as events, transport, public interfaces, and internal administration.

## 🗂 Project Structure

### 🔒 Admin Controllers
- `AdminReclamationController.php`
- `ReclamationController.php`

### 🧠 AI & Interaction
- `ChatbotController.php`

### 💬 Communication
- `CommentaireController.php`
- `ForumController.php`

### 📅 Event Management
- `EvenementController.php`
- `PublicEvenementController.php`

### 🚚 Delivery & Transport
- `LivraisonController.php`
- `MoyenDeTransportController.php`
- `PublicDeliveryController.php`
- `PublicTransportController.php`

### 🛍 Offers & Packages
- `OffreController.php`
- `PublicOfferController.php`
- `PackageController.php`

### 📦 Misc Modules
- `ParcourController.php`
- `PostController.php`
- `ProfileController.php`
- `PublicController.php`
- `HomeController.php`

### 🔁 Mercure Integration
- `MercureProxyController.php`
- `MercureTopicController.php`

## 🚀 Getting Started

### Requirements
- PHP 8.1+
- Symfony CLI
- Composer
- MySQL/PostgreSQL
- Node.js (for frontend assets)

### Installation

```bash
git clone <repo-url>
cd <project-folder>
composer install
yarn install
yarn dev
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
symfony server:start
