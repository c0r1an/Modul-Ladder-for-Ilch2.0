# Boxen einbinden

Das Modul liefert zwei Boxen:

- `nextladdermatches` (naechste Matches)
- `topladder` (Top-Ranking)

## Einbindung im Ilch-Backend

1. Adminbereich oeffnen
2. Layout/Boxen bearbeiten
3. Box auswaehlen und Position setzen
4. Speichern und Cache leeren

## Design-Hinweis

Die Boxen nutzen den gleichen visuellen Stil wie das Tournament-Modul. So bleibt das Frontend einheitlich.

## Troubleshooting

Wenn die Box nicht sichtbar ist:

- Modul ist installiert?
- Box ist einer sichtbaren Position zugewiesen?
- Es gibt Daten (naechste Matches / Teilnehmer mit Punkten)?
- Cache geleert?
