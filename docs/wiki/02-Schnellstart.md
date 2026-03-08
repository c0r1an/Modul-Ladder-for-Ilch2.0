# Schnellstart

## Ziel

In wenigen Minuten eine laufende Ladder mit Punkten und Matches bereitstellen.

## Schritt 1: Ladder erstellen (Admin)

Route: `/index.php/admin/ladder/ladders/treat`

Pflichtfelder:

- Titel
- Spiel
- Teamgroesse
- Max. Teilnehmer
- Startdatum
- Punktesystem (Sieg/Unentschieden/Niederlage)

Status fuer Start:

- `registration_open`

## Schritt 2: Teilnehmer anmelden (Frontend)

Route: `/index.php/ladder/ladders/view/id/{ladderId}`

- Captain klickt auf `Teilnehmen`
- Teamname, Tag, optional Logo eintragen
- Mitglieder ergaenzen (registriert oder nur Nickname)

## Schritt 3: Teilnehmer pruefen (Admin)

Route: `/index.php/admin/ladder/ladders/participants/id/{ladderId}`

- Status je Teilnehmer setzen (`accepted`, `rejected`, `pending`)
- Ladder-Status bei Bedarf auf `registration_closed` stellen

## Schritt 4: Matches erzeugen (Admin)

Route: `/index.php/admin/ladder/ladders/matches/id/{ladderId}`

- `Matches generieren` ausfuehren
- Optional: Zeit, Map, Best-of je Match setzen

## Schritt 5: Match-Workflow (Frontend)

Route: `/index.php/ladder/matches/view/id/{matchId}`

- Captain meldet Ergebnis + Beweise
- Gegner bestaetigt oder eroeffnet Dispute
- Bei Bestaetigung wird Match auf `done` gesetzt und Punkte werden verbucht

## Schritt 6: Disputes bearbeiten (Admin)

Route: `/index.php/admin/ladder/disputes/index`

- Dispute oeffnen
- Ergebnis bestaetigen/ablehnen/neu setzen
- Notiz hinterlegen
