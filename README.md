# watch-tv
What to watch


## Initial Setup

### Database
1. Connect to db container : `docker compose exec db mysql -u root -p`
2. Use system database : `USE mysql;`
3. Create user : `CREATE USER 'dev'@'%' IDENTIFIED BY 'dev';`
4. Grant privileges : `GRANT ALL PRIVILEGES ON symfony.* TO 'dev'@'%' WITH GRANT OPTION;`
5. Flush privileges : `FLUSH PRIVILEGES;`

### Packages
* doctrine/doctrine-bundle <https://github.com/doctrine/DoctrineBundle>
* doctrine/doctrine-migrations-bundle <https://github.com/doctrine/DoctrineMigrationsBundle>
* symfony/twig-bundle <https://github.com/symfony/twig-bundle>
* symfony/http-client <https://github.com/symfony/http-client>
* cocur/slugify <https://github.com/cocur/slugify>
* easycorp/easyadmin-bundle <https://github.com/EasyCorp/EasyAdminBundle>

### Dev packages
* symfony/maker-bundle <https://github.com/symfony/maker-bundle>
* friendsofphp/php-cs-fixer <https://github.com/PHP-CS-Fixer/PHP-CS-Fixer>

### Commands
* Migrate database : `symfony console make:migration`
* Execute migration : `symfony console doctrine:migrations:migrate`