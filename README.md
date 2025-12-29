# Najemnina – upravljanje najemniških stanovanj

## Opis projekta
Najemnina je web aplikacija za upravljanje najemniških stanovanj, najemnikov in stroškov.  
Projekt je zasnovan kot **client–server aplikacija** z REST API-jem in relacijsko podatkovno bazo.

Aplikacija omogoča:
- upravljanje več stanovanj,
- dodajanje več najemnikov na posamezno stanovanje,
- beleženje in pregled stroškov po stanovanjih,
- avtentikacijo uporabnikov z uporabo JWT.

Projekt predstavlja **MVP (Minimum Viable Product)** in je primeren za nadaljnjo nadgradnjo (frontend, mobilna aplikacija, dodatne funkcionalnosti).

---

## Uporabljene tehnologije
- **Backend:** PHP (REST API)
- **Podatkovna baza:** MySQL / MariaDB
- **Avtentikacija:** JWT (JSON Web Token)
- **Frontend (osnova):** PHP / React (v pripravi)
- **Testiranje API-ja:** Postman
- **Gostovanje:** shared hosting (hitrost.com)

---

## Arhitektura sistema
Frontend (PHP / React)
|
v
REST API (PHP – index.php router)
|
v
MySQL / MariaDB


Backend uporablja enoten vstopni endpoint (`index.php`), ki skrbi za usmerjanje zahtev (routing), preverjanje avtentikacije in obdelavo podatkov.

---

## Avtentikacija
Sistem uporablja **JWT (JSON Web Token)** za preverjanje identitete uporabnika.

Postopek:
1. Uporabnik se prijavi z uporabniškim imenom in geslom.
2. Strežnik vrne JWT token.
3. Token se pošilja pri vsakem nadaljnjem zahtevku v HTTP headerju:

Authorization: Bearer <JWT_TOKEN>

Vsi zaščiteni API endpointi brez veljavnega tokena vrnejo napako `401 Unauthorized`.

---

## API Endpointi

### Auth (avtentikacija)
| Metoda | Endpoint | Opis |
|------|---------|------|
| POST | `/api/auth/register` | Registracija novega uporabnika |
| POST | `/api/auth/login` | Prijava uporabnika |
| GET | `/api/auth/me` | Podatki o prijavljenem uporabniku |

---

### Apartments (stanovanja)
| Metoda | Endpoint | Opis |
|------|---------|------|
| GET | `/api/apartments` | Seznam stanovanj |
| POST | `/api/apartments` | Dodaj novo stanovanje |
| GET | `/api/apartments/{id}` | Podrobnosti stanovanja |
| PUT | `/api/apartments/{id}` | Uredi stanovanje |
| DELETE | `/api/apartments/{id}` | Izbriši stanovanje |

---

### Tenants (najemniki)
| Metoda | Endpoint | Opis |
|------|---------|------|
| GET | `/api/apartments/{id}/tenants` | Seznam najemnikov stanovanja |
| POST | `/api/apartments/{id}/tenants` | Dodaj najemnika |
| GET | `/api/tenants/{id}` | Podrobnosti najemnika |
| PUT | `/api/tenants/{id}` | Uredi najemnika |
| DELETE | `/api/tenants/{id}` | Izbriši najemnika |

Dodatno je podprt filter aktivnih najemnikov:
GET /api/apartments/{id}/tenants?active=1


---

### Expenses (stroški)
| Metoda | Endpoint | Opis |
|------|---------|------|
| GET | `/api/apartments/{id}/expenses` | Seznam stroškov |
| POST | `/api/apartments/{id}/expenses` | Dodaj strošek |
| GET | `/api/expenses/{id}` | Podrobnosti stroška |
| PUT | `/api/expenses/{id}` | Uredi strošek |
| DELETE | `/api/expenses/{id}` | Izbriši strošek |

Podprti so tudi filtri:
?from=YYYY-MM-DD
&to=YYYY-MM-DD
&type=electricity

---

## CRUD funkcionalnosti
Aplikacija implementira vse osnovne CRUD operacije:
- **Create** – dodajanje novih zapisov,
- **Read** – prikaz seznama in posameznih zapisov,
- **Update** – urejanje obstoječih zapisov,
- **Delete** – brisanje zapisov.

---

## Testiranje
API je testiran s pomočjo **Postman** orodja.  
Priložena je Postman Collection, ki omogoča:
- samodejno shranjevanje JWT tokena,
- testiranje vseh API endpointov,
- enostavno preverjanje CRUD operacij.

---

## Nadaljnji razvoj
Možne nadgradnje projekta:
- polni frontend (React ali Vue),
- mobilna aplikacija,
- grafični prikaz stroškov,
- vloge uporabnikov (lastnik, najemnik),
- izvoz podatkov (PDF, Excel).

---

## Avtor
Projekt je izdelan kot zaključni projekt pri predmetu Spletne tehnologije.
