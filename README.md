# Proyecto Manas E-Lab (`local_manaselabproject`)

Formulario móvil para que el líder de un proyecto edite la ficha de su
proyecto sin recibir `moodle/course:update` y sin pasar por
`course/edit.php`, y norma de un solo líder por proyecto. No modifica el
núcleo de Moodle ni el tema.

## Qué hace

- Página `/local/manaselabproject/edit.php?courseid=N`, con disposición
  `incourse`. Solo abre en cursos cuyo campo `spacetype` vale `project`
  (el menú guarda la posición de la opción, así que la clave se resuelve
  por posición).
- La ficha son los campos personalizados de curso. El *handler* del núcleo
  decide cuáles puede editar cada usuario: un campo **bloqueado** exige
  `moodle/course:changelockedcustomfields`. Por eso, bloqueando los campos
  que no son de la ficha (`spacetype`, `callstatus`, `callopens`,
  `callcloses`, `spacelangs`), el líder solo ve los de la ficha.
- Además, el formulario nunca ofrece `spacetype` ni los campos que el
  usuario no puede ver (visibilidad «nadie» o «profesores»).
- Un paso por cada categoría de campos, en el orden de
  `/course/customfield.php`. Cada paso guarda lo suyo y lleva al siguiente;
  el último vuelve al curso. Con una sola categoría hay un solo paso.
- Las opciones de los menús se muestran con la cadena
  `option_<shortname>_<clave>` del plugin si existe; si no, con la clave.
  Hoy hay cadenas para `projectstage` y `projectsector`. Un menú nuevo de la
  ficha necesita sus cadenas en `lang/`.
- Al guardar escribe solo los campos personalizados mediante el *handler*,
  purga las cachés de cursos (`changesincourse`, que incluye las bandas del
  tema) y lanza `\core\event\course_updated`.
- Añade «Editar mi proyecto» al menú del curso (en Boost, «Más») a quien
  tenga la capacidad.

## Un líder por proyecto (DC-02)

Un proyecto tiene un solo líder a la vez, y un líder no puede liderar otro
proyecto hasta que el primero haya finalizado.

- Un *observer* de `\core\eventole_assigned` (`classes/observer.php`)
  comprueba cada asignación del rol de líder en un curso proyecto
  (`classes/leadership.php`). Actúa **después** de guardarse la asignación:
  no la impide, la **deshace** con `role_unassign()`. La persona sigue
  matriculada, sin el rol.
- Se deshace si (a) el curso ya tiene otra persona con el rol de líder, o
  (b) la persona ya es líder de otro proyecto no finalizado.
- Vale para cualquier vía que llame a `role_assign()`: *Participantes*,
  matricular con rol, *Subir usuarios* (CSV) y restaurar un curso con
  usuarios. Copiar la plantilla sin usuarios no asigna líderes.
- Si la asignación la gestiona un plugin de matriculación (`component` no
  vacío: cohortes, metacurso), no se deshace, porque la sincronización la
  volvería a crear; solo se avisa.
- **Aviso**: mensaje `leadconflict` a quien hizo la asignación y a quien
  tenga `local/manaselabproject:receiveleadalerts` en el sistema.
- **Traspaso**: primero quitar el rol al líder saliente y después darlo al
  nuevo. En orden inverso se deshace.
- Lo que entre por otras vías (cambiar `spacetype` o reabrir un proyecto con
  líder) no pasa por el evento: lo detecta el informe de control del sitio.

### Ajustes

*Administración del sitio › Extensiones › Extensiones locales › Proyecto
Manas E-Lab*:

- `leadrole`: nombre corto del rol de líder (`projectlead`).
- `finishedstatuses`: claves de `projectstatus` que dan un proyecto por
  finalizado, separadas por comas (`closed,graduated`). Un proyecto sin
  estado no ha finalizado.

## Capacidades

- `local/manaselabproject:editproject` (contexto de curso, `RISK_PERSONAL`,
  sin arquetipos). Se asigna por configuración al rol `projectlead` y a Gestor.
- `local/manaselabproject:receiveleadalerts` (contexto de sistema, arquetipo
  gestor): recibir los avisos de la norma de un líder por proyecto.

## Requisitos

Moodle 5.2. PHP 8.3.

## Tests

```
php vendor/bin/phpunit --testsuite local_manaselabproject_testsuite
php vendor/bin/behat --tags @local_manaselabproject
```
