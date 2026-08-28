# Formulario de contacto - Documentacion tecnica

## Servicio de envio

El formulario de contacto (`id="contacto-form"` en `index.html`) no tiene backend propio.
Se envia mediante **FormSubmit.co**, un servicio externo de terceros que recibe datos de
formularios via HTTP y los reenvia como correo electronico.

- **Endpoint:** `https://formsubmit.co/ajax/kaostattooalc@gmail.com`
- **Metodo:** `POST` (AJAX, `fetch` con `FormData`)
- **Formato de respuesta:** JSON (`{ "success": true/false }`)

## Datos enviados

Cada envio incluye los siguientes campos:

| Campo            | Descripcion                                      |
|------------------|--------------------------------------------------|
| `_captcha`       | `false` (captcha desactivado en FormSubmit)       |
| `_template`      | `table` (formato del email recibido)              |
| `_subject`       | `Nueva consulta de [Tipo] - [Nombre]`             |
| `Tipo`           | Tatuaje o Piercing                                |
| `Zona`           | Zona del cuerpo seleccionada                      |
| `Idea / Estilo`  | Descripcion libre (solo para tatuajes)            |
| `Nombre`         | Nombre del cliente                                |
| `Email`          | Email del cliente                                 |
| `Telefono`       | Telefono del cliente                              |

## Correo destinatario

- **Buzon:** `kaostattooalc@gmail.com`
- **Propietario del buzon:** Kaos Tattoo (equipo del estudio)
- **Quien revisa:** El equipo de Kaos Tattoo gestiona las consultas directamente desde ese buzon de Gmail.

## Almacenamiento de datos

- **No existe base de datos ni CRM.** Los datos del formulario no se persisten en ningun sistema propio.
- **FormSubmit.co** actua unicamente como intermediario de reenvio. Segun su politica, no almacena los datos de los envios de forma permanente.
- **Gmail** es el unico lugar donde quedan registradas las consultas, en forma de correos recibidos en `kaostattooalc@gmail.com`.
- **Acceso:** Solo quienes tengan credenciales de la cuenta de Gmail `kaostattooalc@gmail.com`.
- **Retencion:** Los correos permanecen en Gmail indefinidamente salvo que se eliminen manualmente. No hay politica de purgado automatico configurada.

## Flujo completo

```
Usuario rellena formulario
        |
        v
JavaScript valida campos (nombre, email, telefono, privacidad)
        |
        v
fetch POST -> https://formsubmit.co/ajax/kaostattooalc@gmail.com
        |
        v
FormSubmit.co reenvia los datos como email
        |
        v
Email llega a kaostattooalc@gmail.com
        |
        v
Equipo de Kaos Tattoo revisa y responde manualmente
```

## Notas adicionales

- No se usa captcha (`_captcha=false`). FormSubmit tiene su propio filtro anti-spam basico.
- Al enviar correctamente, se dispara un evento de Google Tag Manager (`form_submitted`) con el tipo de servicio y el idioma de la pagina.
- No hay confirmacion por email al usuario. La unica respuesta es el mensaje de exito en pantalla.
