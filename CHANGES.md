# Changelog — local_manaselabproject

## 0.2.0 — 2026-11-04 (`2026110400`)

### Added

- Rule of one lead per project (DC-02). An observer of
  `\core\eventole_assigned` undoes a lead role assignment in a project
  when the project already has another lead, or when the user leads another
  project that has not finished. The user stays enrolled without the role.
- An assignment managed by an enrolment plugin (`component` not empty) is not
  undone, since the next sync would make it again; it is only alerted.
- Message provider `leadconflict`: the user who made the assignment and the
  users with the new capability `local/manaselabproject:receiveleadalerts`
  (archetype manager, system context) get an alert with the reason.
- Settings `leadrole` (short name of the lead role, default `projectlead`) and
  `finishedstatuses` (keys of `projectstatus` that finish a project, default
  `closed,graduated`). A project without status has not finished.
- Class `leadership` and PHPUnit tests for the rule.

## 0.1.0 — 2026-10-23 (`2026102200`)

First release.

### Added

- `edit.php`: mobile form that lets the lead of a project edit the custom
  fields of its course, one step per category of fields.
- Capability `local/manaselabproject:editproject`.
- «Edit my project» in the course menu for users with the capability.
- Translated labels for the options of `projectstage` and `projectsector`.
- Strings in en and es; ru, ky and tr as drafts.
