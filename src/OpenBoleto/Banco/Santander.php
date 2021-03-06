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
 * Classe boleto Santander
 *
 * @package    OpenBoleto
 * @author     Daniel Garajau <http://github.com/kriansa>
 * @copyright  Copyright (c) 2013 Estrada Virtual (http://www.estradavirtual.com.br)
 * @license    MIT License
 * @version    1.0
 */
class Santander extends BoletoAbstract
{
	/**
	 * Código do banco
	 * @var string
	 */
	protected $codigoBanco = '033';

	/**
	 * Localização do logotipo do banco, referente ao diretório de imagens
	 * @var string
	 */
	protected $logoBanco = 'santander.jpg';

	/**
	 * Linha de local de pagamento
	 * @var string
	 */
	protected $localPagamento = 'ATE O VENCIMENTO PAGUE PREFERENCIALMENTE NO SANTANDER<BR>APOS O VENCIMENTO PAGUE SOMENTE NO SANTANDER';

	/**
	 * Define as carteiras disponíveis para este banco
	 * @var array
	 */
	protected $carteiras = array('101', '102', '201');

	/**
	 * Define os nomes das carteiras para exibição no boleto
	 * @var array
	 */
	protected $carteirasNomes = array('101' => 'Cobrança Simples ECR', '102' => 'Cobrança Simples CSR');

	/**
	 * Define o valor do IOS - Seguradoras (Se 7% informar 7. Limitado a 9%) - Demais clientes usar 0 (zero)
	 * @var int
	 */
	protected $ios;

	/**
	 * Define o valor do IOS
	 *
	 * @param int $ios
	 */
	public function setIos($ios)
	{
		$this->ios = $ios;
	}

	/**
	 * Retorna o atual valor do IOS
	 *
	 * @return int
	 */
	public function getIos()
	{
		return $this->ios;
	}

	/**
	 * Gera o Nosso Número.
	 *
	 * @return string
	 */
	protected function gerarNossoNumero()
	{
		return self::zeroFill($this->getSequencial(), 12) . ' ' . $this->calcDvNossoNumero();
	}

	/**
	 * Método para gerar o código da posição de 20 a 44
	 *
	 * @return string
	 * @throws \OpenBoleto\Exception
	 */
	public function getCampoLivre()
	{
		return '9' . self::zeroFill($this->getConta(), 7) .
			str_replace(' ', '', $this->getNossoNumero()) .
			self::zeroFill($this->getIos(), 1) .
			self::zeroFill($this->getCarteira(), 3);
	}

	/**
	 * Define variáveis da view específicas do boleto do Santander
	 *
	 * @return array
	 */
	public function getViewVars()
	{
		return array(
			'esconde_uso_banco' => true,
		);
	}

	public function calcDvNossoNumero(){
	    $soma = $this->calcMod11(static::zeroFill($this->getSequencial(), 12));
	    $resto = $soma % 11;
	    if($resto == 10) return 1;
	    if($resto == 1 or $resto == 0) return 0;
	    return 11 - $resto;
	}

	public function calcMod11($n){
	    $t = strlen ($n) - 1;
	    $fator = 2;
	    for ($i=$t;$i>=0;$i--) {
			$soma += $n{$i} * $fator;
			$fator = ($fator >= 9) ? 2 : ++$fator;
		}
		return $soma;
	}

	/**
     * Retorna o dígito verificador da conta
     *
     * @return int
     */
    public function getContaDv()
    {
        return null;
	}

	/**
     * Retorna o campo Agência/Cedente do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoCedente()
    {
        $conta = $this->getContaDv() !== null ? $this->getConta() . '-' . $this->getContaDv() : $this->getConta();
        return  $this->getAgencia() . ' / ' . $conta;
    }
}
