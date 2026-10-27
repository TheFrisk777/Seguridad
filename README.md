# Bugambilia Seguridad de Unidad II

Revisión académica de la aplicación web PHP del ZIP AndreyGarcia_U2Act1(1).zip. Referencia: OWASP MASVS **2.1.0**, adaptada a una aplicación web. No es un proyecto Android/iOS ni una certificación MASVS.

## Historial

- `unidad-ii-base`: los 63 archivos originales, sin correcciones funcionales.
- `unidad-ii-corregida`: código corregido, pruebas e informe en `auditoria/`.
- Destino autorizado: https://github.com/TheFrisk777/Seguridad.git
- Las ramas están conservadas en el archivo `Seguridad.bundle` entregado con el informe. El informe distingue el estado local de la publicación remota.

## Requisitos

PHP 8.2 o posterior con mysqli y mbstring; MariaDB/MySQL; navegador actual. El laboratorio usó PHP 8.2.12 y MariaDB 10.4.32 de XAMPP. Esos binarios no se distribuyen y deben actualizarse antes de producción. No se realizó un escaneo CVE completo del entorno.

## Base de datos

Trabajar solo en una instancia y base de pruebas. No importar ni migrar sobre una base real sin respaldo.

1. Para una base vacía, importar `Bugambilia/BD/bugambilia.sql`.
2. Para una base creada con el SQL original, importar **solo** `Bugambilia/BD/migrar_base.sql`. No ejecutar ambos. La migración conserva cuentas existentes y requiere resolver correos duplicados o nulos previamente.
3. Crear una cuenta de aplicación exclusiva, con contraseña aleatoria, y conceder solo `SELECT, INSERT, UPDATE, DELETE` sobre `bugambilia.*`. No usar `root` para ejecutar el sitio. La aplicación rechaza esa configuración.
4. Configurar `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` en el entorno del proceso. `.env.example` documenta las variables, pero **no se carga automáticamente**. No subir contraseñas a Git ni colocarlas en capturas.

En PowerShell, después de configurar las variables de DB:

```powershell
./Start-Local.ps1 -PhpPath C:/xampp/php/php.exe -Port 8766
```

Abrir `http://127.0.0.1:8766/Bugambilia/index.html`. Para la cuenta usar `/Bugambilia/paginas/inde.php`. El nombre de cuenta y correo deben ser ficticios. El acceso antiguo `inde.html` redirige a PHP. Las contraseñas tienen de 12 a 72 bytes y solo se guardan como bcrypt.

## Configuración de entrega

El servidor local debe usar **router.php**. Este controlador aplica las cabeceras y bloquea SQL, archivos ocultos, runtime y endpoints privados. No ejecutar un servidor estático ni servir directamente `Bugambilia/`: esas alternativas omiten el bloqueo de archivos de instalación. El script local usa loopback y habilita una excepción explícita para HTTP de laboratorio.

En producción, configurar un servidor web con TLS válido y equivalente enrutamiento de todas las peticiones a `router.php`, preservando `REQUEST_URI`. Fijar `ALLOW_LOCAL_HTTP=0`. La aplicación no confía en `X-Forwarded-Proto`: si TLS termina en un proxy, el servidor de aplicación debe recibir una indicación HTTPS confiable configurada por el administrador. No aceptar encabezados de origen sin verificar.

Fijar `APP_RUNTIME` a una carpeta privada fuera del contenido público con permisos limitados al proceso PHP. Allí se guardan archivos de sesión y un log técnico sin entradas del usuario. Configurar `display_errors=Off` también en php.ini. Proteger y cifrar las copias del servidor según el despliegue.

Validar antes de usar datos reales: certificado/cadena/nombre/protocolo TLS, cookie `Secure`, HSTS, ACL, configuración de logs y versiones de PHP/MariaDB/navegador. No se afirma que esas verificaciones de infraestructura hayan sido completadas en el laboratorio.

## Funciones y límites

Registro, inicio de sesión, cuenta, cierre y eliminación funcionan con la base de pruebas. Se requiere CSRF en todos los POST sensibles, se renueva el ID al entrar y se invalidan sesiones tras 15 minutos de inactividad o una hora total. La eliminación pide la contraseña actual.

El acceso acepta hasta cinco intentos por IP en una ventana de 15 minutos, compartidos entre sesiones y cuentas. Es un límite sencillo para la práctica: redes compartidas pueden bloquearse mutuamente y un atacante distribuido necesita mitigaciones adicionales. Las filas de control vencidas se limpian al atender nuevos intentos; la retención máxima de una fila sin tráfico no está garantizada sin una tarea de purga.

Programar en el servidor la purga de `auth_attempts` vencidos, sesiones de más de una hora y logs de más de siete días. La aplicación no instala tareas programadas ni cambia la configuración global del equipo.

El carrito es una demostración visual, sin venta ni precio autorizado por servidor. Donaciones, redes sociales y contacto no procesan operaciones reales. Se eliminó el envío aparente de consultas sin backend. No se implementaron pagos, OAuth, recuperación de contraseña ni correo de verificación. Antes de un servicio público se necesitan verificación de correo, recuperación segura y revisión de abuso del registro y de operaciones sensibles.

## Pruebas reproducibles

`auditoria/resultados_base.json` recoge siete observaciones del original; `auditoria/resultados_corregida.json` contiene 32 comprobaciones satisfactorias. `auditoria/checklist_masvs.csv` incluye los 24 controles. `C*` significa cumple en el alcance web indicado, sin afirmar conformidad móvil completa. `NC` incluye comprobaciones incompletas. Las capturas E12 a E21 son visores de extractos reales de código y E22 es el visor de resultados guardados; se distinguen de las capturas de pantallas ejecutadas E01 a E11.

La suite `tests/verify.cjs` requiere Playwright y Chrome. Ejecutarla **solo en un laboratorio desechable y aislado**: crea y elimina una cuenta ficticia, borra contadores de `auth_attempts` y envejece sus propios archivos de sesión para comprobar expiración. El puerto de base por defecto del laboratorio es 13316; nunca apunta al MySQL habitual en 3306 por defecto. Configurar `TEST_DB_PORT`, `MYSQL_BIN`, `PHP_BIN`, `TEST_RUNTIME`, `CHROME_BIN`, `TEST_BASE_URL`, `TEST_OUTPUT` y `TEST_RESULTS` si difieren.

Levantar tres procesos PHP con el mismo código: 8766 con credenciales válidas y `ALLOW_LOCAL_HTTP=1`; 8767 con `ALLOW_LOCAL_HTTP=0`; 8768 con `ALLOW_LOCAL_HTTP=1` y `DB_USER=root` para la prueba de error controlado. Cada proceso necesita su propio `APP_RUNTIME`; `TEST_RUNTIME` debe coincidir con el de 8766. El usuario root sin contraseña de la suite es exclusivamente el administrador de la instancia aislada de laboratorio. Para otras credenciales, adaptar el helper `sql` sin escribirlas en el repositorio.

```powershell
cd tests
npm install
cd ..
$env:NODE_PATH = "$PWD/tests/node_modules"
node tests/verify.cjs
```

La versión de Playwright registrada en package.json corresponde al runtime con el que se ejecutó el laboratorio. La suite puede requerir ajustar la ruta de Chrome en otros sistemas. Ejecutar además `php -l` para cada archivo PHP.

## Restaurar y publicar las dos ramas

```powershell
git clone ./Seguridad.bundle Seguridad
cd Seguridad
git remote set-url origin https://github.com/TheFrisk777/Seguridad.git
git switch -c unidad-ii-base origin/unidad-ii-base
git switch unidad-ii-corregida
git push origin unidad-ii-base unidad-ii-corregida
```

Si el clon ya crea una rama local, usar `git switch` en lugar de crearla otra vez. También se incluye `Publish-Branches.ps1`, que evita force-push y comprueba los hashes remotos. Es necesario autenticar GitHub con una cuenta autorizada. No pegar tokens en el chat ni incorporarlos al archivo.

## Referencias

- OWASP MASVS 2.1.0: https://github.com/OWASP/masvs/releases/tag/v2.1.0
- Controles: https://mas.owasp.org/MASVS/
- MASTG: https://mas.owasp.org/MASTG/
- Rúbrica aportada: Tarea Movil Integral.pptx, diapositivas 48 a 58.
