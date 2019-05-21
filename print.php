<?php
require __DIR__ . '/vendor/mike42/escpos-php/autoload.php';
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

try {
    $connector = new NetworkPrintConnector("172.20.10.2", 9100);

    $printer = new Printer($connector);
    $printer->text("Hello Mimllenials!!\n");
    $printer->cut();

    /* Close printer */
    $printer->close();
} catch (Exception $e) {
    echo "Impossibile stampare, errore: " . $e->getMessage() . "\n";
}
