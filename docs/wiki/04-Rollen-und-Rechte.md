# Rollen und Rechte

## Empfohlene Rollen

- Admin/Ausrichter
- Team-Captain
- Team-Member
- Gast

## ACL-Keys

- `ladder_admin`
- `ladder_manage`
- `ladder_dispute`
- `ladder_team_manage`
- `ladder_report`

## Rechte-Matrix (empfohlen)

- Admin:
  - alle Rechte
- Captain:
  - `ladder_team_manage`
  - `ladder_report`
- Team-Member:
  - optional nur Leserechte
- Gast:
  - nur Ansicht (keine Teamverwaltung)

## Technischer Hinweis

Im Modul wird fuer Vollzugriff auch `module_ladder` bzw. Admin-Status beruecksichtigt. Pruefe nach Installation die Gruppenrechte im Backend.
