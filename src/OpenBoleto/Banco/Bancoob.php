<?php

/*
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Copyright (C) 2013 Estrada Virtual
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace OpenBoleto\Banco;

use OpenBoleto\BoletoAbstract;
use OpenBoleto\Exception;

/**
 * Classe boleto Bancoob.
 *
 * @package    OpenBoleto
 * @author     Paulo Vítor Reis <http://github.com/paulovitin>
 * @license    MIT License
 * @version    1.0
 */
class Bancoob extends BoletoAbstract
{
  /**
   * Código do banco
   * @var string
   */
  protected $codigoBanco = '756';

  /**
   * Localização do logotipo do banco, referente ao diretório de imagens
   * @var string
   */
  protected $logoBanco = 'bancoob.png';

  protected $modalidadeCobranca = '02';

  protected $numeroParcela = '901';   

  /**
   * Linha de local de pagamento
   * @var string
   */
  protected $localPagamento = 'ATE O VENCIMENTO PAGUE PREFERENCIALMENTE NO BANCOOB<BR>APOS VENCIMENTO PAGUE SOMENTE NO BANCOOB';

  /**
   * Define as carteiras disponíveis para este banco
   * @var array
   */
  protected $carteiras = array('1');

  /**
   * Define o número do convênio
   * @var string
   */
  protected $convenio;

  /**
   * Define o número do convênio. Sempre use string pois a quantidade de caracteres é validada.
   *
   * @param string $convenio
   * @return Banrisul
   */
  public function setConvenio($convenio)
  {
    $this->convenio = $convenio;
    return $this;
  }

  /**
   * Retorna o número do convênio
   *
   * @return string
   */
  public function getConvenio()
  {
    return $this->convenio;
  }

  /**
   * Método para gerar o código da posição de 20 a 44
   *
   * @return string
   * @throws \OpenBoleto\Exception
   */
  public function getCampoLivre()
  {
    $campo = $this->modalidadeCobranca.self::zeroFill($this->getConvenio(), 7);
    $campo .= self::zeroFill($this->gerarNossoNumero(), 8).$this->numeroParcela;

    return $campo;
  }

  public function getLinhaDigitavel()
  {
    $chave = $this->getCampoLivre();

    // Break down febraban positions 20 to 44 into 3 blocks of 5, 10 and 10
    // characters each.
    $blocks = array(
        '20-29' => substr($chave, 0, 10),
        '30-40' => substr($chave, 10, 10),
    );

    // Concatenates bankCode + currencyCode + first block of 5 characters +
    // checkDigit.
    $part1 = $this->getCodigoBanco(). $this->getMoeda() . $this->getCarteira() . $this->getAgencia();
    $part1 .= static::modulo10($part1);
    $part1 = substr_replace($part1, '.', 5, 0);

    $part2 = $blocks['20-29'] . static::modulo10($blocks['20-29']);
    $part2 = substr_replace($part2, '.', 5, 0);

    // As part2, we do the same process again for part3.
    $part3 = $blocks['30-40'] . static::modulo10($blocks['30-40']);
    $part3 = substr_replace($part3, '.', 5, 0);

    // Check digit for the human readable number.
    $cd = $this->getDigitoVerificador();

    // Put part4 together.
    $part4  = $this->getFatorVencimento() . $this->getValorZeroFill();

    // Now put everything together.
    return "$part1 $part2 $part3 $cd $part4";
  }


  protected function gerarNossoNumero()
  {
    $conta = $this->getConta();
    $agencia = $this->getAgencia();
    $sequencial = $this->getSequencial();
    $convenio = $this->getConvenio();

    $numero = self::zeroFill($sequencial, 7);

    $sequencia = self::zeroFill($agencia,4).self::zeroFill(str_replace("-","",$convenio),10).self::zeroFill($numero,7);

    $cont = 0;
    $calculoDv = '';

    for($num = 0; $num <= strlen($sequencia); $num++) {
      
      $cont++;
      if($cont == 1)  {
        // constante fixa Sicoob » 3197 
        $constante = 3;
      }

      if($cont == 2) {
        $constante = 1;
      }

      if($cont == 3) {
        $constante = 9;
      }

      if($cont == 4) {
        $constante = 7;
        $cont = 0;
      }

      $calculoDv = $calculoDv + (substr($sequencia,$num,1) * $constante);
    }

    $resto = $calculoDv % 11;

    $dv = 11 - $resto;

    if ($dv == 0) {
      $dv = 0;
    }

    if ($dv == 1) {
      $dv = 0;
    }

    if ($dv > 9) {
      $dv = 0;
    }

    return $numero . $dv;

  }
}