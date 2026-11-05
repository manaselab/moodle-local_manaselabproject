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
 * Russian language strings for local_manaselabproject.
 *
 * Draft translation, pending validation by a native reviewer.
 *
 * @package    local_manaselabproject
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['backtoproject'] = 'Вернуться к проекту';
$string['editproject'] = 'Редактировать мой проект';
$string['finishedstatuses'] = 'Статусы, завершающие проект';
$string['finishedstatuses_desc'] = 'Ключи вариантов поля курса projectstatus через запятую. Лидер может возглавить новый проект, когда предыдущий имеет один из них. Проект без статуса не завершён.';
$string['leadconflict_course'] = '{$a->user} получил(а) роль лидера в {$a->course}, где уже есть лидер.';
$string['leadconflict_howto'] = 'У проекта один лидер, и лидер возглавляет один проект до его завершения. Чтобы передать проект, сначала снимите роль лидера с текущего лидера, а затем назначьте её новому. Участники: {$a->url}';
$string['leadconflict_kept'] = 'Назначение создано способом записи, который его синхронизирует, поэтому оно не снято. Исправьте его в способах записи проекта.';
$string['leadconflict_subject'] = 'Роль лидера не разрешена в {$a->course}';
$string['leadconflict_undone'] = 'Роль лидера снята: {$a->user} остаётся записан(а) в проект без этой роли.';
$string['leadconflict_user'] = '{$a->user} получил(а) роль лидера в {$a->course}, но уже возглавляет незавершённый проект: {$a->others}.';
$string['leadership'] = 'Один лидер на проект';
$string['leadership_desc'] = 'У проекта одновременно только один лидер, и лидер не может возглавить другой проект, пока первый не завершён. Если роль лидера назначена в нарушение этого правила, назначение отменяется, а уведомление отправляется тому, кто его сделал, и получателям уведомлений.';
$string['leadrole'] = 'Роль лидера';
$string['leadrole_desc'] = 'Роль лидера проекта.';
$string['manaselabproject:editproject'] = 'Редактировать карточку проекта';
$string['manaselabproject:receiveleadalerts'] = 'Получать уведомления о правиле одного лидера на проект';
$string['messageprovider:leadconflict'] = 'Назначения роли лидера, не разрешённые правилом одного лидера на проект';
$string['nofields'] = 'В карточке этого проекта нет полей, которые вы можете редактировать.';
$string['notaproject'] = 'Этот курс не является проектом.';
$string['option_projectsector_agrifood'] = 'Агропищевой сектор';
$string['option_projectsector_creative'] = 'Креативные индустрии';
$string['option_projectsector_education'] = 'Образование и обучение';
$string['option_projectsector_energy'] = 'Энергетика и экология';
$string['option_projectsector_fintech'] = 'Финансы и финтех';
$string['option_projectsector_health'] = 'Здоровье и благополучие';
$string['option_projectsector_ict'] = 'ИТ, ПО и ИИ';
$string['option_projectsector_industry'] = 'Промышленность и производство';
$string['option_projectsector_logistics'] = 'Транспорт и логистика';
$string['option_projectsector_other'] = 'Другое';
$string['option_projectsector_retail'] = 'Торговля и ритейл';
$string['option_projectsector_socialimpact'] = 'Социальное воздействие';
$string['option_projectsector_tourism'] = 'Туризм и наследие';
$string['option_projectstage_stage1_idea'] = 'Этап 1 · Идея';
$string['option_projectstage_stage2_validation'] = 'Этап 2 · Валидация';
$string['option_projectstage_stage3_mvp'] = 'Этап 3 · Прототип / MVP';
$string['option_projectstage_stage4_traction'] = 'Этап 4 · Рост / пилот';
$string['option_projectstage_stage5_consolidation'] = 'Этап 5 · Консолидация';
$string['pluginname'] = 'Проект Manas E-Lab';
$string['privacy:metadata'] = 'Плагин «Проект Manas E-Lab» не хранит персональные данные: карточка проекта хранится в настраиваемых полях курса.';
$string['projectsaved'] = 'Ваш проект обновлён.';
$string['savecontinue'] = 'Сохранить и продолжить';
$string['saveproject'] = 'Сохранить';
$string['stepof'] = 'Шаг {$a->step} из {$a->total} · {$a->name}';
