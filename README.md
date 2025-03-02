# watch-tv
What to watch


## Initial Setup

### Database
1. Connect to db container : `docker compose exec db mysql -u root -p`
2. Use system database : `USE mysql;`
3. Create user : `CREATE USER 'dev'@'%' IDENTIFIED BY 'dev';`
4. Grant privileges : `GRANT ALL PRIVILEGES ON symfony.* TO 'dev'@'%' WITH GRANT OPTION;`
5. Flush privileges : `FLUSH PRIVILEGES;`

