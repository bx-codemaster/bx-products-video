# BX Products Video – Installation

## Vorbereitung

1. Den `admin`-Ordner umbenennen in das eigene Administrationsverzeichnis.
2. Die Dateien im Ordner `src` ins Hauptverzeichnis des eigenen Servers hochladen.

## Modul installieren

1. Im Shop einloggen.
2. Im Administrationsbereich unter **Module → System-Module** das Modul **BX Products Video** installieren.

## Templatedateien anpassen

Die folgende Datei muss im eigenen Template angepasst werden:

```
/templates/<Dein_Template>/module/product_info/<Deine_Products_Info>.php
```

### Beispieldateien

**Bootstrap 4**
```
/templates/bootstrap4/module/product_info/bx_video_info_tabs_v1.html
```

**TPL Modified Responsive**
```
/templates/tpl_modified_responsive/module/product_info/bx_video_info_tabs_v1.html
/templates/tpl_modified_responsive/javascript/extra/colorbox.js  (geringfügig ergänzt)
```

## Abschluss

In den Artikeldaten die **Vorlage für Artikeldetails** entsprechend umstellen.

---

Viel Spaß – benax
