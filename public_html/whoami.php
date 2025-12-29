<?php
header('Content-Type: text/plain; charset=utf-8');
echo "OK PHP\n";
echo "REQUEST_URI=" . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
echo "SCRIPT_FILENAME=" . ($_SERVER['SCRIPT_FILENAME'] ?? '') . "\n";
