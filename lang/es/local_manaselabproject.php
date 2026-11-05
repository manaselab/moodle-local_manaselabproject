<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Spanish language strings for local_manaselabproject.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['backtoproject'] = 'Volver al proyecto';
$string['editproject'] = 'Editar mi proyecto';
$string['finishedstatuses'] = 'Estados que finalizan un proyecto';
$string['finishedstatuses_desc'] = 'Claves de las opciones del campo de curso projectstatus, separadas por comas. Un líder puede liderar un proyecto nuevo cuando el anterior tiene una de ellas. Un proyecto sin estado no ha finalizado.';
$string['leadconflict_course'] = '{$a->user} ha recibido el rol de líder en {$a->course}, que ya tiene líder.';
$string['leadconflict_howto'] = 'Un proyecto tiene un solo líder, y un líder lidera un solo proyecto hasta que este finaliza. Para traspasar un proyecto, quita primero el rol de líder a quien lidera hoy y después dáselo a la persona nueva. Participantes: {$a->url}';
$string['leadconflict_kept'] = 'La asignación viene de un método de matriculación que la mantiene sincronizada, así que no se ha quitado. Corrígela en los métodos de matriculación del proyecto.';
$string['leadconflict_subject'] = 'Rol de líder no permitido en {$a->course}';
$string['leadconflict_undone'] = 'Se ha quitado el rol de líder: {$a->user} sigue matriculado en el proyecto sin ese rol.';
$string['leadconflict_user'] = '{$a->user} ha recibido el rol de líder en {$a->course}, pero ya lidera un proyecto que no ha finalizado: {$a->others}.';
$string['leadership'] = 'Un líder por proyecto';
$string['leadership_desc'] = 'Un proyecto tiene un solo líder a la vez, y un líder no puede liderar otro proyecto hasta que el primero haya finalizado. Si se asigna el rol de líder en contra de esta norma, la asignación se deshace y se envía un aviso a quien la hizo y a quienes reciben los avisos.';
$string['leadrole'] = 'Rol de líder';
$string['leadrole_desc'] = 'Rol del líder de un proyecto.';
$string['manaselabproject:editproject'] = 'Editar la ficha de un proyecto';
$string['manaselabproject:receiveleadalerts'] = 'Recibir los avisos de la norma de un líder por proyecto';
$string['messageprovider:leadconflict'] = 'Asignaciones del rol de líder no permitidas por la norma de un líder por proyecto';
$string['nofields'] = 'No hay nada en la ficha de este proyecto que puedas editar.';
$string['notaproject'] = 'Este curso no es un proyecto.';
$string['option_projectsector_agrifood'] = 'Agroalimentación';
$string['option_projectsector_creative'] = 'Industrias creativas';
$string['option_projectsector_education'] = 'Educación y formación';
$string['option_projectsector_energy'] = 'Energía y medio ambiente';
$string['option_projectsector_fintech'] = 'Finanzas y fintech';
$string['option_projectsector_health'] = 'Salud y bienestar';
$string['option_projectsector_ict'] = 'TIC, software e IA';
$string['option_projectsector_industry'] = 'Industria y fabricación';
$string['option_projectsector_logistics'] = 'Transporte y logística';
$string['option_projectsector_other'] = 'Otros';
$string['option_projectsector_retail'] = 'Comercio y distribución';
$string['option_projectsector_socialimpact'] = 'Impacto social';
$string['option_projectsector_tourism'] = 'Turismo y patrimonio';
$string['option_projectstage_stage1_idea'] = 'Etapa 1 · Idea';
$string['option_projectstage_stage2_validation'] = 'Etapa 2 · Validación';
$string['option_projectstage_stage3_mvp'] = 'Etapa 3 · Prototipo / MVP';
$string['option_projectstage_stage4_traction'] = 'Etapa 4 · Tracción / piloto';
$string['option_projectstage_stage5_consolidation'] = 'Etapa 5 · Consolidación';
$string['pluginname'] = 'Proyecto Manas E-Lab';
$string['privacy:metadata'] = 'El plugin Proyecto Manas E-Lab no guarda datos personales: la ficha de un proyecto se guarda en los campos personalizados del curso.';
$string['projectsaved'] = 'Tu proyecto se ha actualizado.';
$string['savecontinue'] = 'Guardar y continuar';
$string['saveproject'] = 'Guardar';
$string['stepof'] = 'Paso {$a->step} de {$a->total} · {$a->name}';
