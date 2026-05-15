# Sistema de Gestión de Eventos

Sistema web para la gestión de eventos, inscripciones, asistencias y credenciales.

## Características

- 📅 Gestión de eventos y agenda
- 👥 Sistema de inscripciones (individual y por delegación)
- 💳 Gestión de pagos y comprobantes
- 🎫 Generación de credenciales con QR
- ✅ Control de asistencias
- 📄 Gestión de documentos del evento
- 📧 Notificaciones por correo electrónico

## Estructura del Proyecto

```
├── app/
│   ├── Controllers/    # Controladores de la aplicación
│   ├── Models/         # Modelos de datos
│   └── Views/          # Vistas (templates)
├── config/             # Archivos de configuración
├── core/               # Núcleo del framework (MVC, helpers, librerías)
├── public/             # Archivos públicos (index, assets, uploads)
├── routes/             # Definición de rutas
└── storage/            # Logs y archivos temporales
```

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache/Nginx con mod_rewrite habilitado
- Extensiones PHP: pdo_mysql, mbstring, gd

## Instalación

1. Clonar el repositorio:
```bash
git clone [URL_DEL_REPOSITORIO]
cd event.sikerjapp
```

2. Configurar la base de datos:
   - Crear una base de datos MySQL
   - Importar el esquema de la base de datos (si existe un archivo SQL)

3. Configurar las credenciales:
   - Copiar `config/config.example.php` a `config/config.php`
   - Copiar `config/database.example.php` a `config/database.php`
   - Editar los archivos con tus credenciales

4. Configurar permisos:
```bash
chmod -R 755 public/uploads
chmod -R 755 storage/logs
```

5. Acceder a la aplicación en tu navegador

## Tecnologías Utilizadas

- **Backend:** PHP (MVC personalizado)
- **Base de datos:** MySQL
- **PDF:** FPDF
- **QR:** PHPQRCode
- **Email:** PHPMailer
- **Frontend:** Bootstrap 5, JavaScript

## Licencia

[Especificar licencia]

## Autor

[Tu nombre o nombre del equipo]
