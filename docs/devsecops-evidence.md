# Evidencia de implementación DevSecOps

**Proyecto:** webapp Sunmee  
**Fecha:** 26 de octubre de 2026  
**Rama de trabajo:** `devsecops/ci-pipeline`  
**Commit de línea base:** `1160e0b`

## Línea base

- El repositorio no tenía un workflow de GitHub Actions.
- Pint estaba instalado como dependencia de desarrollo, pero no existía un comando de lint para el frontend.
- `npm audit` encontró 13 vulnerabilidades: 2 críticas, 9 altas y 2 moderadas.
- `composer audit` encontró 55 avisos asociados a 15 paquetes.
- El test de feature esperaba una respuesta HTTP 200 aunque la ruta raíz redirige a `/dashboard`.
- El build de frontend usaba Node 20, versión que alcanzó fin de soporte el 24 de marzo de 2026.

## Controles incorporados

- CI para PR hacia `main` y push a `main`: Pint, pruebas Laravel, ESLint para Vue/JavaScript, auditorías de Composer/npm, build de Vite y Gitleaks.
- Acciones de GitHub fijadas por SHA completo, permisos mínimos de lectura y sin credenciales de Railway en los jobs.
- Script de instalación de `esbuild` autorizado únicamente para la versión fijada `0.28.2` mediante `allowScripts` de npm.
- Protección de estilo PHP mediante `composer run lint`; reglas esenciales de ESLint/Vue con advertencias tratadas como error.
- Actualizaciones de dependencias dentro de los rangos definidos en `composer.json` y `package.json`.
- Node 24 para CI y etapas de compilación; Node 22.13 o 24 admitido para desarrollo local.
- Guía de activación de protección de `main` y **Wait for CI** en Railway en [devsecops-setup.md](devsecops-setup.md).

## Resultados locales

| Control | Resultado observado |
| --- | --- |
| Pint (`composer run lint`) | Pasa; se normalizó formato en 33 archivos PHP, sin cambios de lógica intencionales. |
| ESLint (`npm run lint`) | Pasa con cero errores y cero advertencias. |
| Pruebas Laravel | 2 pruebas y 3 aserciones aprobadas. |
| Build Vite | Pasa; queda una advertencia de bundle JavaScript de aproximadamente 1,13 MB, que es una mejora de rendimiento pendiente. |
| Auditoría npm | 0 vulnerabilidades conocidas tras actualizar el lockfile. |
| Auditoría Composer | 0 avisos tras actualizar el lockfile. |
| Gitleaks sobre historial y fuentes de trabajo | 31 commits y aproximadamente 898 KB de archivos del proyecto revisados; sin hallazgos. |
| Prueba controlada de Gitleaks | Detectó el secreto sintético de prueba y devolvió estado de fallo; el archivo temporal fue retirado. |
| Configuración | YAML del workflow parseado y `composer validate --strict` aprobado. |

## Límites y tareas pendientes

Los resultados corresponden a validaciones locales. El workflow todavía no se ha ejecutado en GitHub porque no se ha publicado la rama ni se ha abierto un PR. Tampoco están activadas las reglas de protección de `main` ni **Wait for CI** en Railway. La guía operativa describe el orden de activación sin desplegar un commit fallido.

Esta implementación se limita al pipeline y a las dependencias y reglas de estilo necesarias para que los controles pasen. No cambia la lógica de negocio ni corrige hallazgos de autorización de la aplicación. La ausencia de middleware de autenticación visible en varias rutas API y la ejecución de migraciones al iniciar Nixpacks quedan para analizar y recomendar en el informe; todavía no son conclusiones de vulnerabilidad.
