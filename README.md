<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Sistema Gestión de Música

Es un sistema híbrido que pueda ser usado en aplicación web como en una aplicación para teléfonos inteligentes Android, su finalidad es una aplicación que de publicidad a grupos musicales y puedan ser contratados por cualquier persona a través del sistema, obteniendo ganancias por cada contratación.

Para tener una buena ejecución se deben de introducir los siguientes comandos:

## composer install

Este sirve como un manejador de paquetes para PHP que proporciona un estándar para administrar, descargar e instalar dependencias y librerías.

## cp .env.example .env

Sirve para general el archivo .env.

## php artisan storage:link

Sirve para que los archivos sean accesibles desde la web, para eso se debe crear un enlace simbólico, mantendrá sus archivos de acceso público en un directorio que se puede compartir fácilmente entre implementaciones cuando se utilizan sistemas de implementación sin tiempo de inactividad.

## php artisan key:generate

Nos ayuda a generar la clave de encriptación de nuestro proyecto Web, muestra el listado de rutas y la generación de código.

## php artisan migrate:fresh --seed

Nos ayuda para subir las migraciones y ademas agregar las informaciones que están en el seeder.

## php artisan jwt:secret

Funciona para generar el JSON Web Token Secret de la aplicación

## Agregar la variable FRONTEND_APP la url que se le asigna al Front end por defecto (http://localhost:8080)

## Configurar las credenciales de el login de Google en https://console.cloud.google.com/apis/dashboard con las siguientes URI, activando Google+ como API y configurando las credenciales Secret, ClientID y pasando la pantalla de consentimiento, además, de agregar las credenciales obtenidas al .env

URI 1:
http://127.0.0.1:8000

URI 2:
http://127.0.0.1:8000/signin-google

URI 3:
http://127.0.0.1:8000/authorize/google/callback


## php artisan serve

Nos ayuda solo para probar y comenzar su proyecto fácilmente.


