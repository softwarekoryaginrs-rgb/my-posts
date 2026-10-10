# 🏗️ My Blog

A simple website for writing my personal articles on programming and development.

### Koryagin Roman

**👨‍💻 GitHub:** [@softwarekoryaginrs-rgb](https://github.com/softwarekoryaginrs-rgb)  

## 📋 Technologies used

| Layer                       | Stack                          |
| ----------------------------| ------------------------------ |
| Backend                     | PHP, Symfony, Symfony Security |
| Admin panel                 | EasyAdmin Bundle               |
| Templating                  | Twig                           |
| Frontend                    | TypeScript, SCSS               |
| Bundler                     | Rspack                         |
| Backend Dependency manager  | Composer 2                     |
| Fronted Dependency manager  | NPM                            |
| Local development           | Docker                         |
| Logging                     | Monolog                        |

## 🚀 Common commands

### 🐳 Docker

```bash
docker compose up -d

docker compose down

docker compose exec php bash

php bin/console make:migration

php bin/console doctrine:migrations:migrate

php bin/console doctrine:migrations:status

php bin/console doctrine:migrations:migrate prev