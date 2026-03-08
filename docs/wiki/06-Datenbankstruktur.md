# Datenbankstruktur

## Kern-Tabellen

- `ilch_ladder_ladders`
  - Ladder-Metadaten, Status, Punktemodell
- `ilch_ladder_participants`
  - Teams pro Ladder inkl. Rankingwerte
- `ilch_ladder_team_members`
  - Teammitglieder (registriert oder frei)
- `ilch_ladder_member_profiles`
  - Vorstellungsseiten pro Mitglied

## Match-Tabellen

- `ilch_ladder_matches`
  - Paarungen, Scores, Gewinner, Status
- `ilch_ladder_match_reports`
  - Gemeldete Ergebnisse pro Match
- `ilch_ladder_match_evidence`
  - Beweisdaten (Datei/Link)
- `ilch_ladder_match_disputes`
  - Konfliktfaelle + Admin-Entscheidung

## Punkte und Audit

- `ilch_ladder_points_events`
  - Punktebuchungen je Match und Teilnehmer
- `ilch_ladder_audit_log`
  - Nachvollziehbarkeit von Aktionen

## Wichtige Beziehungen

- Ladder -> Participants (`ladder_id`)
- Participant -> TeamMembers (`participant_id`)
- Match -> Reports (`match_id`)
- Report -> Evidence (`match_report_id`)
- Match -> Disputes (`match_id`)

Alle Kernbeziehungen sind per Foreign Keys abgesichert.
