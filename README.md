# Like_and_dislike — parandatud versioon

## Käivitamine

Projekt vajab PHP serverit, sest tagasiside salvestatakse faili `data/feedback.json`.

1. Ava terminal projekti kaustas.
2. Käivita server:

```bash
php -S localhost:8000
```

3. Ava brauseris:

```text
http://localhost:8000
```

## Failid

- `index.html` — rakenduse HTML.
- `style.css` — kujundus.
- `script.js` — like/dislike nupud, modaalaknad ja AJAX päringud.
- `feedback.php` — serveripoolne salvestamine.
- `data/feedback.json` — salvestatud tagasiside.

## Mis sai parandatud?

- PHP-kood eemaldati `index.html` failist, sest `.html` faili sees PHP tavaliselt ei käivitu.
- Lisati puuduv `feedback.php`, kuhu JavaScript päringu saadab.
- Ühtlustati tegevuse nimi: `save_feedback`.
- Eraldati HTML, CSS, JavaScript ja PHP eri failidesse.
- Parandati modaalakende avamine/sulgemine.
- Lisati tühja tagasiside kontroll.
- Lisati failipõhine salvestamine, mis ei vaja SQLite/PDO seadistust.
