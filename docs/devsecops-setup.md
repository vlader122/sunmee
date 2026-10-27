# Activación de los controles DevSecOps

El workflow `CI` valida cada Pull Request hacia `main` y cada push a `main`. Los controles incluyen Pint, ESLint para JavaScript y Vue, pruebas de Laravel, compilación de Vite, auditoría de Composer y npm, y análisis de secretos con Gitleaks. La compilación usa Node 24, con Node 22.13 o 24 admitido para desarrollo local.

## Configuración de GitHub

En la configuración del repositorio, crea una regla de protección para `main`:

1. Exige que los cambios lleguen mediante Pull Request.
2. Exige que pasen estos checks del workflow `CI`:
   - `Backend quality, audit, and tests`
   - `Frontend lint, audit, and build`
   - `Secret scan`
3. Mantén deshabilitados el force push y la eliminación de `main`.

La rama de implementación debe ejecutar el workflow y publicar sus checks antes de seleccionarlos como requeridos.

## Configuración de Railway

Para evitar un despliegue de producción durante la activación inicial:

1. Deshabilita temporalmente el autodeploy del servicio Sunmee.
2. Integra el workflow en `main` mediante un Pull Request y confirma que CI termine correctamente.
3. En Railway, verifica que la rama de despliegue sea `main` y activa **Wait for CI**.
4. Habilita la protección de rama de GitHub con los checks anteriores.
5. Vuelve a habilitar el autodeploy. Despliega el commit de `main` validado por CI.

Después de la activación, Railway debe esperar a que los workflows de GitHub Actions finalicen correctamente antes de desplegar. No configures un token de producción en el workflow de CI: Railway conserva la responsabilidad del despliegue.

## Límites del alcance

Los hallazgos de seguridad en la lógica de la aplicación, las rutas API y las migraciones al inicio del proceso de Railway se deben registrar y evaluar en el informe. Este pipeline no modifica el comportamiento funcional ni corrige esos hallazgos.
