<?php
require __DIR__ . '/vendor/mike42/escpos-php/autoload.php';
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

try {
    $data = $_GET;
    // exit($data['usr_internal_code']);

    $connector = new NetworkPrintConnector("172.20.10.2", 9100);

    $printer = new Printer($connector);

    /* Ricevuta n e data */
    $printer->setEmphasis(true);
    $printer->text("RICEVUTA #" . $data['number'] . "\n");
    $printer->text("Data: " . date('d/m/Y', strtotime($data['created_at'])) . "\n");
    $printer->setEmphasis(false);
    $printer->feed();

    /* Ordine e transazione */
    $printer->text("Ordine #" . $data['order_number'] . "\n");
    $printer->text("Transazione #" . $data['tr_number'] . "\n");
    $printer->feed();

    /* Destinatario */
    $printer->text("Destinatario:\n");
    $printer->setEmphasis(true);
    $printer->text($data['company'] . "\n");
    $printer->setEmphasis(false);
    $printer->text($data['address'] . "\n");
    $printer->text($data['zip'] . " - " . $data['city'] . " (" . $data['district'] . ")\n");
    $printer->text("P.IVA: " . $data['PIVA'] . "\nCF: " . $data['CF'] . "\n");
    $printer->text("----------------------\n");
    $printer->feed();

    $printer->setEmphasis(true);
    $printer->text(new Item($data['prod_description'] . " di Eur. " . $data['prod_price']));
    $printer->setEmphasis(false);
    $printer->feed();

    $printer->text("Questa transazione: ");
    $printer->setEmphasis(true);
    $printer->text($data['tr_price'] . " Eur.\n(" . $data['tr_status_word'] . ")\n");
    $printer->setEmphasis(false);
    $printer->feed();

    $printer->text("Pagamento effettuato tramite:\n");
    $printer->setEmphasis(true);
    $printer->text($data['tr_payment_word'] . "\n");
    $printer->setEmphasis(false);
    $printer->feed();

    if ($data['tr_payment'] == 'check') {
        $printer->text("Banca: ");
        $printer->setEmphasis(true);
        $printer->text($data['tr_bank'] . "\n");
        $printer->setEmphasis(false);

        $printer->text("Numero Assegno: ");
        $printer->setEmphasis(true);
        $printer->text($data['tr_check_number'] . "\n\n");
        $printer->setEmphasis(false);
    }

    $printer->text("Commenti/Note\n");
    $printer->text("(nessuna nota)\n");
    $printer->text("----------------------\n");
    $printer->feed();

    $printer->setEmphasis(true);
    $printer->text($data['branch_name'] . "\n");
    $printer->setEmphasis(false);
    $printer->text($data['branch_address'] . "\n");
    $printer->text($data['branch_zip'] . "-" . $data['branch_city'] . " (" . $data['branch_district'] . ")\n");
    $printer->text("P.IVA:" . $data['branch_PIVA'] . "\nCF:" . $data['branch_CF'] . "\n");
    $printer->text("Agente: " . $data['usr_internal_code'] . "\n");
    $printer->feed();

    $printer->cut();

    /* Close printer */
    $printer->close();
} catch (Exception $e) {
    echo "Impossibilestampare, errore:" . $e->getMessage() . "\n";
}

class item
{
    private $name;
    private $price;
    private $moneySign;
    public function __construct($name = '', $price = '', $moneySign = false)
    {
        $this->name      = $name;
        $this->price     = $price;
        $this->moneySign = $moneySign;
    }

    public function __toString()
    {
        $rightCols = 10;
        $leftCols  = 38;
        if ($this->moneySign) {
            $leftCols = $leftCols / 2 - $rightCols / 2;
        }
        $left = str_pad($this->name, $leftCols);

        $sign  = ($this->moneySign ? ' Eur' : '');
        $right = str_pad($this->price . $sign, $rightCols, ' ', STR_PAD_LEFT);
        return "$left$right\n";
    }
}
