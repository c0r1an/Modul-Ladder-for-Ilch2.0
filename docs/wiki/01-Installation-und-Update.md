# Installation und Update

## Voraussetzungen

- Ilch Core: `>= 2.2.0`
- PHP: `>= 7.3`
- Schreibrechte auf: `application/modules/ladder/storage`

## Neuinstallation

1. Modulordner nach `application/modules/ladder` kopieren.
2. Im Ilch-Backend das Modul `Ladder` installieren.
3. Rechte (ACL) fuer Admin/Gruppen vergeben.
4. Testweise eine Ladder anlegen.

## Update von alter Version

1. Aktuelle Dateien ueber die bestehenden Moduldateien kopieren.
2. Im Backend Modulseite einmal oeffnen, damit `getUpdate()` ausgefuehrt wird.
3. Funktionen pruefen: Ladder bearbeiten, Matches, Disputes, Teamseiten.

## Typische Update-Checks

- Tabelle vorhanden: `ilch_ladder_ladders`
- Spalte vorhanden: `ilch_ladder_ladders.banner`
- Tabellen fuer Teamseiten vorhanden:
  - `ilch_ladder_team_members`
  - `ilch_ladder_member_profiles`

## Manuelle SQL-Fixes (nur falls noetig)

### Banner-Spalte fehlt

```sql
ALTER TABLE `ilch_ladder_ladders`
ADD COLUMN `banner` VARCHAR(255) NULL DEFAULT NULL AFTER `slug`;
```

### Team-Member Tabelle fehlt

```sql
CREATE TABLE `ilch_ladder_team_members` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `participant_id` INT(11) NOT NULL,
  `user_id` INT(11) NULL DEFAULT NULL,
  `nickname` VARCHAR(255) NULL DEFAULT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'member',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Hinweis zu Encoding

Falls Umlaute in Uebersetzungen falsch angezeigt werden, alle Moduldateien als `UTF-8 (ohne BOM)` speichern.
