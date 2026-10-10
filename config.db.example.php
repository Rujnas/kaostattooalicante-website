<?php
/**
 * Configuración de conexión a la base de datos MySQL (Hostinger)
 * 
 * Copia este archivo con el nombre `config.db.php` en este mismo directorio
 * y reemplaza 'TU_USUARIO' y 'TU_PASSWORD' con los datos de tu panel de Hostinger.
 * 
 * NOTA: config.db.php está en .gitignore para que tus contraseñas nunca se suban a Git.
 */

return [
    'host'     => 'localhost',
    'dbname'   => 'u589481109_DatosForm',
    'user'     => 'u589481109_TU_USUARIO',     // Ejemplo: u589481109_sergio o el usuario que creaste en Hostinger
    'password' => 'TU_PASSWORD_DE_HOSTINGER', // La contraseña asignada al crear la BBDD
    'charset'  => 'utf8mb4',
];
