# Proyecto Manas E-Lab (`local_manaselabproject`)

Formulario móvil para que el líder de un proyecto edite la ficha de su
proyecto sin recibir `moodle/course:update` y sin pasar por
`course/edit.php`. No modifica el núcleo de Moodle ni el tema.

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

## Capacidad

`local/manaselabproject:editproject` (contexto de curso, `RISK_PERSONAL`,
sin arquetipos). Se asigna por configuración al rol `projectlead` y a Gestor.

## Requisitos

Moodle 5.2. PHP 8.3.

## Tests

```
php vendor/bin/phpunit --testsuite local_manaselabproject_testsuite
php vendor/bin/behat --tags @local_manaselabproject
```
