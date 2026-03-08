# Workflows

## 1) Team zur Ladder anmelden

Voraussetzungen:

- Ladder-Status: `registration_open`
- Team ist vollstaendig (`team_size`)
- Teilnehmerlimit nicht erreicht

Ablauf:

1. Captain waehlt Ladder aus.
2. Captain klickt `Teilnehmen`.
3. Eintrag wird als Teilnehmer gespeichert.

## 2) Ergebnis melden

Voraussetzungen:

- Match-Status: `ready` oder `scheduled`
- User ist Captain eines Match-Teams

Ablauf:

1. `Ergebnis melden`
2. Score + Kommentar
3. Beweise als Upload oder Link
4. Match wechselt auf `reported`

## 3) Ergebnis bestaetigen

Voraussetzungen:

- Match-Status: `reported`
- Gegnerischer Captain bestaetigt

Ablauf:

1. `Ergebnis bestaetigen`
2. Match auf `done`
3. Punkteberechnung und Ranking-Update

## 4) Dispute eroeffnen

Voraussetzungen:

- Match-Status: `reported`
- Team ist am Match beteiligt
- User hat passende Berechtigung

Ablauf:

1. `Dispute oeffnen`
2. Grund angeben
3. Match auf `dispute`
4. Admin entscheidet im Dispute-Backend

## 5) Dispute loesen (Admin)

Route: `/index.php/admin/ladder/disputes/view/id/{id}`

Moegliche Aktionen:

- `resolved`: Match final setzen
- `rejected`: Dispute abweisen
- `open`: Wieder auf offen setzen

Alle relevanten Schritte werden im Audit-Log dokumentiert.
