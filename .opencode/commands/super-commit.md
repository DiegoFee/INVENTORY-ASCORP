# SUPER-COMMIT.md - Comando Personalizado para OpenCode

## Rol y Propósito
Este archivo actúa como un **comando personalizado** llamado `super-commit`. 

Cuando el agente reciba la instrucción `super-commit`, `ejecutar super-commit` o similar, debe realizar un proceso completo, profesional y seguro de análisis, commit y subida de cambios a GitHub.

## Comportamiento del Comando

Al activarse `super-commit`, el agente **debe seguir estrictamente** los siguientes pasos:

### 1. Análisis Inicial del Repositorio
- Verificar si existe un repositorio Git en la carpeta actual (`git status`).
- Si **no existe** repositorio:
  - Preguntar al usuario el nombre del repositorio y si desea crearlo en GitHub (si todavía no lo ha especificado).
  - Ejecutar `git init`.
  - Crear un `.gitignore` adecuado para proyectos web (Node.js, Vite, etc.).
  - Hacer commit inicial.

- Si **ya existe** repositorio:
  - Detectar la rama actual (`main`, `master`, `develop`, etc.) y preguntar en que rama desea subir los archivos (si todavía no lo ha especificado).
  - Mostrar estado actual del repositorio (`git status`).

### 2. Análisis de Cambios
Realizar un análisis detallado de los archivos modificados, añadidos o eliminados:
- Archivos modificados
- Nuevos archivos relevantes
- Posibles archivos sensibles que no deberían subirse
- Calidad general de los cambios

### 3. Preparación del Commit
- Generar un mensaje de commit profesional y descriptivo siguiendo Conventional Commits:
  - Ejemplos: `feat:`, `fix:`, `docs:`, `style:`, `refactor:`, `chore:`
- El mensaje debe ser claro y resumir los cambios principales.

### 4. Ejecución del Commit y Push
Ejecutar secuencialmente (mostrando cada paso):
1. `git add .` (o archivos específicos si es necesario)
2. `git commit -m "mensaje_profesional"`
3. `git push origin <rama_actual> o <rama_especificada>` 

Si la rama no tiene upstream, configurarlo automáticamente.

### 5. Flujo cuando no hay Repositorio
Si aún no se ha inicializado Git:
- Preguntar:
  - Nombre del repositorio en GitHub
  - Descripción (opcional)
  - Rama principal (`main` recomendada)
  - ¿Es repositorio público o privado?
- Crear el repositorio en GitHub (si el agente tiene capacidad).
- Conectar el repositorio local con el remoto.
- Realizar primer commit y push.

## Instrucciones Obligatorias

- **Siempre** confirmar con el usuario antes de ejecutar comandos destructivos o de push.
- Mostrar claramente qué archivos se van a commitear.
- Sugerir mejoras menores si detecta problemas obvios antes de commitear.
- Incluir en el commit archivos importantes como `AGENTS.md`, `README.md`, etc.
- Ignorar automáticamente carpetas como `node_modules`, `.env`, `dist`, etc.

## Formato de Respuesta Recomendado

```markdown
# SUPER-COMMIT EXECUTED - ejemplo

## Estado Actual del Repositorio
- Rama: `main`
- Archivos modificados: 12
- Archivos nuevos: 3

## Análisis de Cambios
...

## Mensaje de Commit Propuesto
`feat: diseño completo de la página web con hero y sección de contacto`

## Archivos a Commitear
- `index.html`
- `css/style.css`
- ...
