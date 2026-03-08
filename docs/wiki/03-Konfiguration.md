# Konfiguration

## Ladder-Felder

Wichtige Felder in Admin > Ladder bearbeiten:

- `title`: Anzeigename
- `slug`: Optional fuer saubere URLs
- `banner`: Bildpfad aus Medienmodul
- `game`: Spielname (frei)
- `team_size`: Mindestspielerzahl pro Team
- `max_participants`: Teilnehmerlimit
- `start_at` und `end_at`: Laufzeit
- `rules`: Regeln (HTML/Text)

## Punktemodell

Konfigurierbar pro Ladder:

- `points_win`
- `points_draw`
- `points_loss`

Best Practice:

- Klassisch: `3 / 1 / 0`
- Defensiver: `2 / 1 / 0`
- Kein Draw erlaubt: `3 / 0 / 0`

## Ladder-Status

- `draft`: Entwurf, nicht produktiv
- `registration_open`: Anmeldung offen
- `registration_closed`: Anmeldung zu
- `running`: Ladder laeuft
- `finished`: Abgeschlossen
- `archived`: Nur Archiv

Hinweis: Im Frontend werden archivierte Ladders standardmaessig ausgeblendet und nur bei aktivem Filter gezeigt.

## Match-Status

- `pending`
- `scheduled`
- `ready`
- `reported`
- `dispute`
- `done`

Diese Stati steuern, ob `Ergebnis melden`, `Bestaetigen` oder `Dispute` sichtbar sind.
