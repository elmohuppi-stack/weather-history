# Projektplan: Weather History Deutschland

## Stand: 19. April 2026 - **PHASE E ABGESCHLOSSEN** ✅

## 🎉 STATUS UPDATE - PROJEKT ERFOLGREICH PRODUKTIONSREIF

### Aktueller Stand:

- ✅ **Phase A-E: 100% Complete**
- ✅ **20 Stationen**: Berlin, Hamburg, München, Köln, Frankfurt, Bremen, Dresden, Düsseldorf, Essen, Hannover, Leipzig, Nürnberg, Stuttgart, Saarbrücken, Rostock, Karlsruhe, Borkum, Potsdam, Trier, Zugspitze
- ✅ **458,707 historische Messungen**: 1890-2026 (136+ Jahre)
- ✅ **91 Jahresaggregate + 1,068 Monatsaggregate**: Vollständig berechnet
- ✅ **20+ API Endpoints**: Alle operational
- ✅ **Vollständiges Frontend**: Dashboard, Stationen, Karten, Suche, Trends, Trends, Export/Import, Rankings
- ✅ **Production Ready**: TypeScript, Docker, Monitoring ready

### Was wird gerade im System angeboten:

1. **Phase E.1**: DWD Data Import - 458,707 echte Messungen
2. **Phase E.2**: Trends Visualization - Chart.js mit linearer Regression
3. **Phase E.3**: Export/Import UI - CSV/JSON/Excel/SQL
4. **Phase E.4**: Leaflet Map - 20 Stationen interaktiv auf Karte
5. **Phase E.5**: Advanced Search - Full-text + 6 Filter + 4 Sort-Optionen

### Nächste mögliche Schritte (Phase F):

- [ ] Production Deployment (Server, SSL, Backups)
- [ ] Monitoring & Alerting (APM, Error Tracking)
- [ ] Automated Daily Updates (Cronjob ETL)
- [ ] Performance Optimization
- [ ] Public API & Documentation

**Siehe auch: [PROJEKTPLAN_AKTUELL.md](./PROJEKTPLAN_AKTUELL.md) für vollständige Roadmap**

---

## 1. Zielbild

Dieses Projekt wird als fokussierte Website für historische Wetter- und Klimadaten aus Deutschland neu ausgerichtet.

Der Schwerpunkt liegt zunächst nicht auf einer maximalen Anzahl von Stationen, sondern auf einer fachlich starken, gut verständlichen und stabilen Darstellung von tiefen historischen Zeitreihen für etwa 20 ausgewählte DWD-Stationen.

Das Produkt soll lokal und später online zeigen:

- wie sich Temperatur, Niederschlag und Sonnenscheindauer an einzelnen Orten historisch entwickelt haben
- wie sich Monate und Jahre miteinander vergleichen lassen
- welche Rekorde, Trends und Abweichungen vom langjährigen Mittel sichtbar werden
- wie unterschiedlich Regionen in Deutschland klimatisch geprägt sind

## 2. Strategische Neuausrichtung

Die frühere Planung war breiter angelegt. Die neue Planung priorisiert fachlichen Nutzen und Umsetzbarkeit.

### Kernentscheidung für Phase 1

Wir konzentrieren uns auf:

- etwa 20 kuratierte DWD-Stationen
- möglichst tiefe historische Reihen statt Vollabdeckung aller Stationen
- tägliche Beobachtungsdaten als Primärquelle
- daraus abgeleitete Monats- und Jahresstatistiken
- robuste Visualisierung und Vergleichbarkeit

Wir verschieben bewusst nach hinten:

- deutschlandweite Vollabdeckung
- hochaufgelöste Radarverarbeitung
- komplexe Modellvorhersagen
- Benutzerkonten, Personalisierung und Mobile App

## 3. Fachliches Produktziel

Die Website soll eine verständliche historische Wetterplattform für Deutschland werden.

Sie soll keine reine Rohdatenablage sein, sondern ein Analysewerkzeug mit klarem Mehrwert.

### Zentrale Nutzungsfragen

Ein Nutzer soll zum Beispiel beantworten können:

- Wie warm war ein bestimmter Monat in Berlin im Vergleich zum langjährigen Mittel?
- Welche Jahre waren in Hamburg besonders trocken oder besonders nass?
- Welche Station hatte die höchsten Jahresmitteltemperaturen?
- Wie stark hat sich die mittlere Temperatur seit 1950 verändert?
- Wie unterscheiden sich Karlsruhe, München und Rostock im Jahresverlauf?

## 4. Zielgruppe

Die erste Version richtet sich an:

- wetterinteressierte Privatnutzer
- lokal und regional interessierte Nutzer
- Journalisten und Blogger
- Schulen und Bildungsprojekte
- datenaffine Hobbyanwender

Damit ergibt sich ein klarer Produktfokus: verständliche historische Klima- und Wetterstatistiken statt Spezialsoftware für Profis.

## 5. DWD-Datenbasis

### Primäre Quelle für das Projekt

Die wichtigste Datenbasis ist der DWD Open Data Server im Bereich Climate Data Center.

Für dieses Projekt sind vor allem diese Inhalte relevant:

- Beobachtungsdaten deutscher Stationen
- tägliche Klimadaten im KL-Bereich
- historische und aktuelle Stationsdateien
- Stationslisten und Metadaten
- Qualitätskennzeichen und Datenabdeckung

### Fachlich nutzbare Parameter für den Start

Für Phase 1 werden diese Parameter priorisiert:

- Tagesmitteltemperatur
- Tagesmaximum der Temperatur
- Tagesminimum der Temperatur
- Tagesniederschlag
- Sonnenscheindauer
- optional Schneehöhe, wenn ausreichend vorhanden

### Warum tägliche Daten die beste Basis sind

Die tägliche DWD-Beobachtung ist für den ersten Produktfokus ideal, weil sie:

- sehr lange historische Reihen bietet
- für viele Stationen über Jahrzehnte bis über 100 Jahre verfügbar ist
- fachlich leicht nachvollziehbar ist
- aus einer Quelle konsistente Monats- und Jahreswerte ermöglicht
- besser zum aktuellen Projektaufbau passt als Radar oder Modellprodukte

### Sekundäre Quellen für spätere Phasen

Später sinnvoll, aber nicht Teil des ersten Schwerpunkts:

- stündliche Daten für feinere Zeitreihen
- Warnungen im CAP-Format
- MOSMIX-Vorhersagen
- Radarprodukte wie RADOLAN
- Modellprodukte wie ICON

## 6. Produktumfang für Phase 1

Die erste ernsthafte Produktversion fokussiert auf historische Auswertung statt Live-Wetter.

### Muss-Funktionen

1. Stationsübersicht

- Liste der kuratierten Stationen
- Suche und Filter nach Region oder Name
- sichtbare Datenabdeckung je Station

2. Stationsdetailseite

- Stammdaten der Station
- Zeitreihen der wichtigsten Parameter
- Monats- und Jahresübersicht
- Hinweise auf Datenlücken und Qualitätsstatus

3. Monatsstatistiken

- durchschnittliche Monatstemperatur
- Niederschlagssumme pro Monat
- Sonnenscheindauer pro Monat
- heißester, kältester, trockenster und nassester Monat
- Vergleich mit langjährigem Mittel

4. Jahresstatistiken

- Jahresmitteltemperatur
- Jahresniederschlag
- Jahressumme der Sonnenscheindauer
- Rekordjahre und Ranglisten
- Trenddarstellung über Jahrzehnte

5. Stationsvergleiche

- Jahr gegen Jahr
- Station gegen Station
- Monatsprofile mehrerer Orte nebeneinander

6. Export

- CSV und JSON für ausgewählte Zeiträume und Parameter

### Bewusst nicht im ersten Schwerpunkt

- Minutendaten und Live-Nowcasting
- deutschlandweite flächige Wetterkarte mit komplexen Layern
- Nutzerkonten
- Vorhersageportal
- Push-Alerts

## 7. Fokus auf etwa 20 Stationen

### Grundidee

Wir setzen auf eine kleine, aber starke Auswahl an Langzeitstationen mit guter regionaler Verteilung.

Die Stationen sollen drei Bedingungen erfüllen:

- möglichst lange historische Datenreihen
- gute geografische Streuung über Deutschland
- fachlich interessante Vergleichbarkeit zwischen Küste, Binnenland, Mittelgebirge und Süden

### Bereits im Projekt vorhandenes Kernset

Der aktuelle Importer ist bereits auf 16 wichtige Stationen ausgerichtet:

- Berlin-Tempelhof
- Hamburg-Fuhlsbüttel
- München-Stadt
- Köln-Bonn
- Frankfurt am Main
- Bremen
- Dresden-Klotzsche
- Düsseldorf
- Essen
- Hannover
- Leipzig
- Nürnberg
- Stuttgart-Echterdingen
- Saarbrücken-Ensheim
- Rostock-Warnemünde
- Karlsruhe-Rheinstetten

### Erweiterung auf rund 20 Stationen

Für den nächsten Schritt soll dieses Kernset um etwa 4 weitere Langzeitstationen ergänzt werden.

Empfohlene Ergänzungskategorien:

- eine Küstenstation im Norden
- eine Station in Thüringen oder Mitteldeutschland
- eine Station im Südwesten mit mildem Klima
- eine Hochlagen- oder Alpen-nahe Station

Ziel ist nicht eine mathematisch perfekte Verteilung, sondern ein aussagekräftiger Deutschland-Querschnitt.

## 8. Fachlicher Schwerpunkt: tiefe historische Daten

Die neue Priorität ist nicht mehr nur 1990 bis heute.

Stattdessen gilt:

- pro ausgewählter Station sollen möglichst alle sinnvoll verfügbaren historischen DWD-Jahre importiert werden
- das Frontend darf standardmäßig verkürzte Zeitfenster zeigen, die Datenbasis selbst soll aber tief sein
- Monats- und Jahresstatistiken werden aus der vollständigen historischen Reihe berechnet

### Vorteile dieser Entscheidung

- höherer fachlicher Mehrwert
- glaubwürdigere Trendanalysen
- bessere Rekord- und Vergleichsauswertungen
- stärkere regionale Klimageschichten
- langfristig wertvolleres Produkt

## 9. Welche Kennzahlen lokal angezeigt werden sollen

### Monatsansicht

Pro Station und Monat sollen mindestens sichtbar sein:

- mittlere Temperatur
- Monatsmaximum und Monatsminimum der Tageswerte
- Niederschlagssumme
- Sonnenscheinsumme
- Anzahl der Tage mit Messwerten
- Abweichung vom Referenzmittel
- Einordnung im historischen Ranking

### Jahresansicht

Pro Station und Jahr sollen mindestens sichtbar sein:

- Jahresmitteltemperatur
- Gesamtniederschlag
- gesamte Sonnenscheindauer
- Anzahl heißer Tage
- Anzahl Frosttage
- Anzahl Sommertage
- Anzahl Regentage
- Vergleich zum langjährigen Mittel
- Ranking innerhalb der Stationshistorie

### Langfristige Analysen

- Trendlinie über Jahrzehnte
- Vergleich von Klimaperioden
- Rekordtabellen
- Anomalien gegenüber 1961 bis 1990 und 1991 bis 2020
- Sichtbarkeit von Datenlücken

## 10. Fachliche Methodik

Damit die Statistik belastbar bleibt, gelten diese Regeln:

- Monats- und Jahreswerte werden primär aus den täglichen DWD-Daten berechnet
- fehlende Werte werden nicht stillschweigend aufgefüllt
- Datenabdeckung wird sichtbar gemacht
- Qualitätskennzeichen werden gespeichert und später auswertbar gehalten
- langjährige Referenzwerte werden getrennt gespeichert

### Wichtige fachliche Ableitungen

Aus den täglichen Daten lassen sich direkt erzeugen:

- Monatsmittel der Temperatur
- Jahresmittel der Temperatur
- Monats- und Jahressummen des Niederschlags
- Sonnensummen
- Extremtage und Schwellenwerte
- Anomalien gegenüber Referenzperioden

## 11. Datenmodell und Speicherung

Für die neue Planungsrichtung braucht das Projekt vier zentrale Ebenen:

### 1. Stationen

Enthält Metadaten wie Name, Koordinaten, Höhe, Aktivstatus und Datenverfügbarkeit.

### 2. Tagesmessungen

Dies bleibt die fachliche Rohbasis.

### 3. Monatsaggregate

Vorbearbeitete Statistik je Station, Jahr und Monat für schnelle Frontend-Abfragen.

### 4. Jahresaggregate

Vorbearbeitete Jahreskennzahlen für Vergleiche und Rankings.

### 5. Importprotokolle

Jeder historische und aktuelle Import soll nachvollziehbar gespeichert werden.

## 12. ETL-Strategie

Die Python-Importstrecke bleibt das Herzstück der Datenbeschaffung.

### Zielbild des Imports

1. Historischer Backfill

- lädt für die etwa 20 ausgewählten Stationen alle verfügbaren historischen DWD-Dateien
- prüft Dateiformat, Zeiträume und Datenabdeckung
- speichert Roh- und Normalisierungsstatus nachvollziehbar

2. Laufende Aktualisierung

- lädt regelmäßig aktuelle Daten aus dem recent-Bereich nach
- ergänzt die bestehenden Zeitreihen inkrementell

3. Aggregationslauf

- erzeugt nach jedem Import Monats- und Jahresstatistiken neu oder inkrementell

4. Logging und Monitoring

- protokolliert Erfolg, Dauer, Datensatzanzahl und Fehlerursachen

## 13. API-Planung

Das Backend soll fachlich stärker auf historische Auswertung ausgerichtet werden.

### Wichtige Endpunktgruppen

- Stationen und Metadaten
- Tageswerte pro Station
- Monatsstatistiken pro Station
- Jahresstatistiken pro Station
- Stationsvergleiche
- Rekorde und Rankings
- Exportfunktionen
- Importstatus und Importhistorie

### Neue fachliche Priorität für die API

Statistik-Endpunkte sind wichtiger als zusätzliche Mock- oder Komfort-Endpunkte.

Die Reihenfolge lautet:

1. echte Monatsstatistik
2. echte Jahresstatistik
3. Vergleich und Ranking
4. danach Karten- und Exportveredelung

## 14. Frontend-Planung

Die vorhandene Vue-Basis wird nicht neu erfunden, sondern fachlich geschärft.

### Zentrale Ansichten für die erste starke Version

1. Startseite

- kurze Erklärung des Projekts
- Einstieg über Station oder Region
- Highlights wie Rekorde oder aktuelle Vergleichswerte

2. Stationsseite

- Zeitreihe
- Monatsprofil
- Jahresprofil
- Rekordübersicht

3. Vergleichsseite

- zwei bis vier Stationen direkt gegenüberstellen
- Vergleich von Jahreswerten und Monatsmustern

4. Statistikseite

- Top- und Flop-Rankings
- Trends über Jahrzehnte
- Klimaperiodenvergleich

5. Exportseite

- Daten für ausgewählte Stationen und Zeiträume herunterladen

## 15. Kartenstrategie

Karten bleiben im Produkt wichtig, aber nicht als erstes technisches Schwergewicht.

In Phase 1 dienen Karten vor allem zur Navigation:

- Stationen auf Deutschlandkarte anzeigen
- Station anklicken und zur Detailseite wechseln
- einfache Farbgebung nach Jahresmittel oder Niederschlag

Komplexe Heatmaps und flächige Auswertungen kommen später.

## 16. Nicht-Ziele der ersten Ausbauphase

Um Fokus zu halten, gehören diese Punkte nicht in den ersten Kern:

- Radar-Animationen
- minutengenaue Live-Wetterdaten
- aufwendige Vorhersagemodelle
- vollständige Bundesabdeckung aller DWD-Stationen
- Benutzerkonten und Social Features

## 17. Konkrete Roadmap

### Phase A: fachlichen Scope festziehen

Dauer: 2 bis 3 Tage

- finales 20-Stationen-Set festlegen
- Kriterien für Datenabdeckung dokumentieren
- Referenzperioden und Kennzahlen definieren
- Sicht auf Monats- und Jahresstatistik fachlich vereinheitlichen

### Phase B: historische Datenbasis absichern

Dauer: 3 bis 5 Tage

- Langzeitimporte für alle Fokusstationen vervollständigen
- Metadaten sauber ziehen und vereinheitlichen
- Datenlücken und Qualitätsprobleme sichtbar machen
- tägliche Werte auf Plausibilität prüfen

### Phase C: Aggregationen aufbauen

Dauer: 3 bis 4 Tage

- Monatsaggregate berechnen
- Jahresaggregate berechnen
- Rekorde, Anomalien und Rankings vorberechnen
- API für Statistiken stabilisieren

### Phase D: Frontend auf Statistikfokus umbauen

Dauer: 4 bis 6 Tage

- Stationsdetail auf Monats- und Jahresübersichten ausrichten
- Vergleichsansichten ergänzen
- Statistik- und Rankingseiten mit echten Daten anbinden
- Mock-Reste entfernen

### Phase E: Export, Qualität und Rollout

Dauer: 3 bis 4 Tage

- CSV- und JSON-Export abschließen
- Importmonitoring sichtbar machen
- Performance prüfen
- lokalen und späteren Serverbetrieb absichern

## 18. Prioritätenliste für die Umsetzung

### Sofort priorisieren

1. 20 Fokusstationen fachlich festlegen
2. vollständige historische Reihen importieren
3. Monatsaggregate und Jahresaggregate zuverlässig berechnen
4. Statistik-API auf echte Daten umstellen
5. Statistikseiten im Frontend sichtbar machen

### Danach priorisieren

6. Rankings und Vergleiche verbessern
7. Export stabilisieren
8. Karten veredeln
9. Import-Logs und Betriebsmonitoring ausbauen

### Später priorisieren

10. Warnungen
11. Vorhersagen
12. Radarprodukte

## 19. Erfolgskriterien

Die neue erste Produktstufe ist erfolgreich, wenn:

- etwa 20 Stationen mit tiefen historischen Reihen verfügbar sind
- Monats- und Jahresstatistiken fachlich plausibel und schnell abrufbar sind
- Nutzer zwei oder mehr Stationen sauber vergleichen können
- Trends, Rekorde und langjährige Mittel sichtbar sind
- die Seite lokal stabil und verständlich nutzbar ist

## 20. Aktueller Projektstatus im Verhältnis zur neuen Planung

Die technische Basis ist bereits vorhanden:

- Laravel-Backend steht
- Vue-Frontend steht
- Python-ETL für DWD ist vorhanden
- reale Stationsdaten wurden bereits importiert
- Stationsdetail und Charts sind angebunden

Der nächste große Schritt ist deshalb nicht ein weiterer technischer Neubau, sondern die fachliche Schärfung auf historische Monats- und Jahresanalysen.

## 21. Nächste konkrete Arbeitspakete

### Paket 1

Fokusstationen finalisieren und auf rund 20 erweitern

### Paket 2

Langzeitimporte prüfen und fehlende Stationen ergänzen

### Paket 3

Monats- und Jahresaggregationen als verlässliche Datenbasis einführen

### Paket 4

Statistik-API und Frontend auf echte historische Kennzahlen ausrichten

### Paket 5

Produktkommunikation schärfen: historische Wetter- und Klimastatistik für Deutschland

## 22. Konkreter Plan für die nächsten Schritte

### Schritt 1: Fokus auf rund 20 Stationen festziehen

Als erstes wird das aktuelle Kernset von 16 Stationen auf rund 20 Fokusstationen erweitert.

Dafür werden folgende Punkte festgelegt:

- finale Stationsliste mit regionaler Verteilung
- Priorisierung nach historischer Tiefe und Datenqualität
- Kennzeichnung von Referenzstationen für Vergleiche
- saubere Dokumentation der Datenabdeckung je Station

### Schritt 2: Historische Datenbasis fachlich absichern

Danach wird geprüft, ob für jede Fokusstation die historischen DWD-Reihen vollständig und plausibel importiert sind.

Konkret bedeutet das:

- fehlende Langzeitstationen ergänzen
- Start- und Endjahre pro Station dokumentieren
- Datenlücken sichtbar machen
- Qualitätskennzeichen sauber mitführen
- Metadaten nicht nur vereinfacht, sondern möglichst DWD-nah speichern

### Schritt 3: Monats- und Jahresstatistiken technisch aufbauen

Das ist der wichtigste nächste Entwicklungsschritt.

Aus den täglichen Messwerten werden verlässliche Aggregationen erzeugt für:

- Monatsmitteltemperatur
- Jahresmitteltemperatur
- Monats- und Jahressummen beim Niederschlag
- Sonnenscheinsummen
- heiße Tage, Frosttage, Sommertage und Regentage
- Rekorde und Anomalien gegenüber Referenzperioden

### Schritt 4: Statistik-API auf echte Daten umstellen

Die API muss jetzt stärker von einer Rohdaten-API zu einer echten Statistik-API werden.

Zuerst umzusetzen:

- echte climate normals statt Demo-Werte
- echte Trendberechnung statt Platzhalter
- Monats- und Jahresendpunkte für Stationen
- Vergleichs- und Rankingendpunkte

### Schritt 5: Frontend fachlich nachziehen

Nach der API folgt die fokussierte Darstellung im Frontend:

- Stationsdetail mit Monats- und Jahresblöcken erweitern
- Vergleichsansichten für mehrere Stationen aufbauen
- Statistikseite für Rekorde, Ranglisten und Trends ergänzen
- Kartenansicht zunächst mit echten Stationen statt Platzhalterdarstellung betreiben

### Schritt 6: Export und Import-Monitoring ehrlich machen

Sobald die Statistikdaten stabil sind, werden die Betriebsfunktionen bereinigt:

- Exporte real erzeugen statt nur simulieren
- Importe tatsächlich auslösbar machen
- Import-Historie und Status sauber anzeigen
- Fehler- und Erfolgsfälle nachvollziehbar protokollieren

## 23. Was an der jetzigen Implementierung konkret geändert werden muss

### Bereits gut nutzbar

- echte Stationsdaten sind bereits im System
- Stationsdetailseiten arbeiten mit realen Daten
- die Chart-Ansicht nutzt bereits echte Messreihen
- der ETL-Import für historische DWD-Daten funktioniert grundsätzlich

### Noch notwendige Backend-Änderungen

1. Statistiklogik vervollständigen

- Die Gesamt- und Stationsstatistiken sind schon datenbankbasiert.
- Die Funktionen für Klimanormalwerte und Trends liefern aber noch Demo-Daten und müssen auf echte Aggregationen umgestellt werden.

2. Kartenlogik fertigstellen

- Die Stationsausgabe ist vorhanden.
- Bounding-Box-Filter, Heatmap-Daten und Clusterlogik sind noch Platzhalter.

3. Exportfunktion real machen

- Exporterstellung, Status und Download sind aktuell noch simuliert.
- Hier muss eine echte Datei-Generierung auf Basis der gewählten Filter folgen.

4. Importsteuerung anbinden

- Das Import-Backend protokolliert zwar Einträge, stößt aber noch keinen echten ETL-Lauf an.
- Statt Zufallswerten muss später ein echter Importjob oder Python-Prozess gestartet werden.

### Noch notwendige ETL-Änderungen

- Stationsset von 16 auf rund 20 erweitern
- Stationsmetadaten stärker aus DWD-Beschreibungsdateien ableiten
- Monats- und Jahresaggregate nach Importläufen berechnen
- Importlogs direkt aus dem ETL schreiben

### Noch notwendige Frontend-Änderungen

- Kartenansicht von Placeholder auf echte Kartenkomponente umstellen
- Statistikseiten mit Monats- und Jahreskennzahlen ausbauen
- Exportansicht an echte Exportjobs anbinden
- Importansicht an echte Importläufe koppeln

## 24. Praktische Reihenfolge für die Umsetzung

### Priorität A

- 20 Fokusstationen final festlegen
- fehlende historische Importe ergänzen
- Datenabdeckung und Qualität je Station dokumentieren

### Priorität B

- Monatsaggregate und Jahresaggregate einführen
- Statistik-API mit echten Berechnungen ergänzen
- Referenzperioden und Anomalien definieren

### Priorität C

- Frontend-Statistikseiten aufbauen
- Kartenansicht mit echten Stationspunkten ausstatten
- Vergleiche und Rankings sichtbar machen

### Priorität D

- Export und Importmonitoring produktiv machen
- Performance und Caching optimieren
- README, Bedienung und Betriebsabläufe weiter schärfen

---

Diese neue Planung ersetzt die bisherige breite MVP-Ausrichtung durch einen klaren historischen Statistikfokus mit hoher fachlicher Aussagekraft und realistischer Umsetzbarkeit.
