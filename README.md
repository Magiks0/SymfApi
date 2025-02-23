# SymfApi

## Installation et configuration

### 1. Cloner le repository

Commencez par cloner le projet depuis le repository Git :

```bash
git clone https://github.com/Magiks0/SymfApi.git
cd symfApi
```

Effectuez les commandes suivantes :

```bash
composer install
php bin/console doctrine:migration:migrate
php bin/console doctrine:fixtures:load
```

Commandes à lancer pour le mail de newsletter :
/!\ Vous devez modifier le MAILER_DSN dans le ficher d'environement
```bash
php bin/console app:send-email
```
