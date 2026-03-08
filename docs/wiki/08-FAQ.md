# FAQ

## Warum kann ich kein Team anmelden?

Pruefe:

- Ladder steht auf `registration_open`
- Team hat genug Spieler (`team_size`)
- Max. Teilnehmer nicht erreicht

## Wann kann ein Dispute geoeffnet werden?

Nur wenn Match auf `reported` steht und ein gueltiger Match-Teilnehmer mit Berechtigung die Aktion ausfuehrt.

## Warum fehlt der Dispute-Button?

Der Button wird ausgeblendet, wenn:

- keine Berechtigung vorhanden ist
- Match-Status nicht passt
- User nicht zum Match gehoert

## Fehler: Unknown column ...

Meist ist die Datenbankstruktur nicht auf aktuellem Stand. Modulupdate ausfuehren und fehlende Spalten/Tabellen pruefen.

## Umlaute werden falsch angezeigt

Dateien als `UTF-8 (ohne BOM)` speichern und Browser/Server-Cache leeren.
