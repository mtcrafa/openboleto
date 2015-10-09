<?php

require '../autoloader.php';

use OpenBoleto\Banco\Bancoob;
use OpenBoleto\Agente;

$sacado = new Agente('Fernando Maia', '023.434.234-34', 'ABC 302 Bloco N', '72000-000', 'Brasília', 'DF');
$cedente = new Agente('Empresa de cosméticos LTDA', '02.123.123/0001-11', 'CLS 403 Lj 23', '71000-000', 'Brasília', 'DF');

$boleto = new Bancoob(array(
    // Parâmetros obrigatórios
    'dataVencimento' => new DateTime('2013-01-24'),
    'valor' => 1.00,
    'sacado' => $sacado,
    'cedente' => $cedente,
    'sequencial' => 2,
    'agencia' => 3087, // Até 4 dígitos
    'carteira' => 1,
    'conta' => 4593, // Até 8 dígitos
    'convenio' => 56235 // 4, 6 ou 7 dígitos

));

echo $boleto->getOutput();
