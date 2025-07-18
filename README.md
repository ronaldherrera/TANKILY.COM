# Acuanote0

**Aquanote** es una aplicación web diseñada para gestionar el seguimiento y mantenimiento de acuarios marinos o de agua dulce. Está pensada para funcionar de forma totalmente responsive (adaptada a móvil, tablet y ordenador), y permite a cualquier usuario llevar el control completo de su acuario desde cualquier lugar.

---

## 📦 Estructura del proyecto

```
aquanote/
│
├── index.php                → Panel principal (solo si hay sesión iniciada)
├── login.php                → Página de inicio de sesión
├── registro.php             → Página de registro de usuario
├── logout.php               → Cierre de sesión
├── config.php               → Configuración global y conexión con la base de datos
│
├── db/
│   └── database.sqlite      → Base de datos SQLite
│
├── inc/
│   ├── header.php           → Encabezado común
│   ├── footer.php           → Pie de página común
│   └── auth.php             → Verificación de sesión iniciada
│
├── usuarios/
│   └── datos/               → Carpeta para datos individuales de cada usuario (opcional)
│
├── css/
│   └── style.css            → Estilos base y diseño adaptable
│
├── js/
│   └── app.js               → Funciones en JavaScript (formularios, validaciones, etc.)
│
├── img/
│   └── logo.svg             → Logotipo o imágenes decorativas
│
└── README.md                → Información del proyecto: Aquanote
```

---

## ✨ Características principales

- Registro de parámetros del agua: pH, KH, amoníaco, nitrito, nitrato, salinidad, temperatura, etc.
- Historial con gráficas de evolución de los valores registrados
- Registro de tareas de mantenimiento (cambios de agua, limpieza de filtros, dosificaciones...)
- Configuración personalizada por usuario (nombre del acuario, rangos ideales, unidades)
- Gestión de cuentas con registro e inicio de sesión
- Posibilidad futura de exportar datos o compartir el acuario

---

## 🛠️ Tecnologías utilizadas

- **HTML, CSS y JavaScript** para el frontend
- **PHP** para la lógica de servidor y gestión de sesiones
- **SQLite** como base de datos ligera (sin necesidad de servidor externo)

---

## ☁️ Requisitos del servidor

- Servidor con **PHP 7.4** o superior
- Soporte para **SQLite** (activado por defecto en la mayoría de servidores)

---

## 🔒 Licencia

Este proyecto es de **uso privado**. No se permite su distribución, modificación ni publicación sin autorización expresa del autor.