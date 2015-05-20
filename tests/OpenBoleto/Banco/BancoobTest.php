<?php

namespace Tests\OpenBoleto\Banco;
use OpenBoleto\Banco\Bancoob;

class BancoobTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiateWithoutArgumentsShouldWork()
    {
        $this->assertInstanceOf('OpenBoleto\\Banco\\Bancoob', new Bancoob());
    }

    public function testInstantiateShouldWork()
    {
        $instance = new Bancoob(array(
            // Parâmetros obrigatórios
            'dataVencimento' => new \DateTime('2013-05-14'),
            'valor' => 1.00,
            'sequencial' => 2, // Até 6 dígitos
            'agencia' => 3087, // Até 3 dígitos
            'conta' => 4593, // Até 7 dígitos
            'convenio' => 56235,
            'carteira' => 1
        ));

        $this->assertInstanceOf('OpenBoleto\\Banco\\Bancoob', $instance);
        $this->assertEquals('75691.30870 02005.623505 00000.289017 1 56980000000100', $instance->getLinhaDigitavel());
        $this->assertSame('00000028', (string) $instance->getNossoNumero());
    }
}
