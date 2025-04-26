<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


# Laravel Shop API

Este proyecto es una API RESTful construida con Laravel para gestionar una tienda con múltiples vendedores, productos, carritos de compra y órdenes.

## Características

- Registro e inicio de sesión con autenticación via Sanctum
- CRUD de tiendas y productos por parte de vendedores
- Gestión de carritos de compra para clientes
- Generación de órdenes y visualización del historial de compras
- Seeder preconfigurado para poblar datos de prueba

## Instalación

1. Clona el repositorio:

```bash
git clone https://github.com/Tinchomay/restAPIStore.git
cd tiendaRest-API

2.- composer install

3.- cp .env.example .env

4.- php artisan key:generate

5.- Conectarse a BD, o ejecutar docker-compose.yaml -d para crear el contenedor con la BD y poner user:root, contraseña: prueba. Ejecutar php artisan migrate --seed

La autenticaciones por Authorization: Bearer {token}
