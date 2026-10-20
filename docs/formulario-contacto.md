# Formulario de contacto - Documentacion tecnica

## Servicio de envio

## Flujo y procesamiento

El formulario de contacto (`id="contacto-form"` en `index.php`) se envía a un endpoint PHP propio (`/guardar-consulta.php`), el cual:
1. Valida los datos y aplica filtros antispam (honeypot).
2. Procesa y almacena las imágenes de referencia en el servidor (`uploads/consultas/`).
3. Guarda la consulta en la base de datos MySQL de Hostinger (tabla `consultas`).
4. Reenvía la consulta y los archivos adjuntos por correo electrónico a `kaostattooalc@gmail.com` vía **FormSubmit.co** (con fallback a `mail()` si fuese necesario).

- **Endpoint cliente:** `/guardar-consulta.php`
- **Método:** `POST` (AJAX, `fetch` con `FormData`)
- **Formato de respuesta:** JSON (`{ "success": true, "db_saved": bool, "email_sent": bool }`)

## Datos enviados y guardados

| Campo | Base de Datos | Descripción |
|---|---|---|
| `Tipo` | `tipo` | Tatuaje o Piercing (VARCHAR 50) |
| `Zona` | `zona` | Zona del cuerpo seleccionada (VARCHAR 100) |
| `Idea / Estilo` | `idea` | Descripción libre del proyecto (TEXT) |
| `Nombre` | `nombre` | Nombre del cliente (VARCHAR 150) |
| `Email` | `email` | Email del cliente (VARCHAR 150) |
| `Teléfono` | `telefono` | Teléfono del cliente (VARCHAR 50) |
| `attachment[]` | `imagenes` | Hasta 4 fotos guardadas en `uploads/consultas/` (rutas en JSON) |
| `idioma` | `idioma` | Idioma de navegación (`es` o `en`) |
| Consentimiento | `politica_aceptada` | `1` tras aceptar la política de privacidad (RGPD) |
| IP | `ip` | Dirección IP del cliente para trazabilidad RGPD |
| Fecha | `fecha` | Marca temporal automática (`CURRENT_TIMESTAMP`) |

## Almacenamiento de datos

- **Base de Datos MySQL:** Alojada en Hostinger (`u589481109_DatosForm`, tabla `consultas`). Configurada en `config.db.php` (ignorado en Git por seguridad).
- **Archivos e imágenes:** Guardados físicamente en `uploads/consultas/` con nombres únicos aleatorios y protegidos contra ejecución de scripts mediante `.htaccess`.
- **Buzón de correo:** `kaostattooalc@gmail.com` recibe copia por email vía FormSubmit con los archivos adjuntos y los enlaces web directos a las imágenes guardadas.

## Flujo completo

```
Usuario rellena formulario y adjunta fotos (máx. 4, 5MB c/u)
        |
        v
JavaScript valida campos y privacidad
        |
        v
fetch POST -> /guardar-consulta.php
        |
        +---> 1. Guarda imágenes físicas en uploads/consultas/
        |
        +---> 2. Inserta registro en MySQL (tabla 'consultas')
        |        (Si falla, marca el email con [PENDIENTE BBDD])
        |
        +---> 3. Reenvía a FormSubmit vía cURL con adjuntos y enlaces
        |           |
        |           v
        |       Email llega a kaostattooalc@gmail.com
        |
        v
Respuesta JSON { success: true } -> Pantalla de éxito en web (+ dataLayer)
(En caso de fallo de conexión con el endpoint local, el frontend cuenta con fallback directo a FormSubmit incluyendo la etiqueta [PENDIENTE BBDD] en el asunto)
```
