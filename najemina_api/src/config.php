<?php
declare(strict_types=1);

return [
  'db' => [
    'host' => 'localhost',
    'name' => 'hdo31441_najemnina',
    'user' => 'hdo31441_najem',
    'pass' => 'najem1234',
    'charset' => 'utf8mb4',
  ],

  // ZAMENJAJ z dolgim random stringom (vsaj 32+ znakov)
  'jwt' => [
    'secret' => 'TU_NOTRI_DAJ_RES_DOLG_RANDOM_32+_STRING',
    'ttl_seconds' => 60 * 60 * 24, // 24h
    'issuer' => 'moja-najemnina',
  ],

  // dovoli CORS za testiranje (kasneje lahko zožimo)
  'cors' => [
    'allow_origin' => '*',
  ],
];
