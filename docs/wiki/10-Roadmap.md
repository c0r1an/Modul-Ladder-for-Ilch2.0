# Roadmap

## Geplante Verbesserungen

- UI-Polish fuer Standings und Matchkarten
- Erweiterte Filter (Spiel, Zeitraum, Status)
- Optionales ELO/MMR Modell neben Punkten
- API-Endpunkte fuer externe Integrationen
- Live-Updates fuer Matchstatus (AJAX/Websocket)
- CSV/JSON Export fuer Rankings und Ergebnisse
- Automatische Saison-Archivierung
- Erweiterte Moderationshistorie im Audit

## Best Practices fuer weitere Entwicklung

- Business-Logik in Libraries/Mappern halten
- Statuswechsel zentral validieren
- Bei DB-Aenderungen immer `getUpdate()` erweitern
- Fuer neue Features zuerst ACL und UI-Sichtbarkeit definieren
- Jede kritische Aktion im Audit-Log erfassen
