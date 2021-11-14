# QR-innsjekk for scoutnet

## Beskrivelse
Løsningen ble opprinnelig brukt under Norges speiderforbunds roverleir, Slagplan, i 2021.
Idèen går ut på at deltakerne logger inn i løsningen og får opp en QR-kode de kan bruke i innsjekken.
Arrangørene med innsjekksrettighet i medlemsregisteret får ikke opp en QR-kode, men et kamera med mulighet til å skanne QR-kodene. Arrangørene får også en liste over alle som er påmeldt, og kan sjekke inn manuelt.

## API-nøkler
Det er behov for å benytte API-nøklene som er knyttet til arrangementet i Min speiding. De som er brukt i denne kodebasen er ikke i bruk, og må endres. Stedene det trengs å endres er for øyeblikket i mappa `./public/api/*.php`.

## Installasjon
```
npm install
```

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```