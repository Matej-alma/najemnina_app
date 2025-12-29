<?php
return [
  'db' => [
    'host' => 'localhost',
    'name' => 'hdo31441_najemnina',
    'user' => 'hdo31441_najem',
    'pass' => 'najem1234',
    'charset' => 'utf8mb4',
  ],
  'jwt' => [
    'secret' => 'ZAMENJAJ_Z_DOLGIM_RANDOM_SECRETOM_pllus_nekja_znakov',
    'ttl' => 60 * 60 * 24,
    'issuer' => 'najemnina',
  ],
  'cors' => [
    'origin' => '*',
  ],
];
