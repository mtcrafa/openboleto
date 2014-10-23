<?php

/*
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
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
 * Classe boleto Banrisul.
 *
 * @package    OpenBoleto
 * @author     Jerônimo Fagundes da Silva <http://github.com/jeronimofagundes>
 * @license    MIT License
 * @version    1.0
 */
class Banrisul extends BoletoAbstract
{
	/**
	 * Código do banco
	 * @var string
	 */
	protected $codigoBanco = '041';

	/**
	 * Localização do logotipo do banco, referente ao diretório de imagens
	 * @var string
	 */
	protected $logoBanco = 'banrisul.gif';

	/**
	 * Linha de local de pagamento
	 * @var string
	 */
	protected $localPagamento = 'ATE O VENCIMENTO PAGUE PREFERENCIALMENTE NO BANRISUL<BR>APOS VENCIMENTO PAGUE SOMENTE NO BANRISUL';

	/**
	 * Espécie do documento
	 * @var string
	 */
	protected $especieDoc = 'RC';

	/**
	 * Define as carteiras disponíveis para este banco
	 * @var array
	 */
	protected $carteiras = array();

	/**
	 * Define o número do convênio
	 * @var string
	 */
	protected $convenio;

	/**
	 * Define o código da carteira (Com ou sem registro)
	 *
	 * @param string $carteira
	 * @return BoletoAbstract
	 * @throws Exception
	 */
	public function setCarteira($carteira)
	{
		$this->carteira = $carteira;
		return $this;
	}


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
	 * Gera o Nosso Número.
	 *
	 * @throws Exception
	 * @return string
	 */
	protected function gerarNossoNumero()
	{
		return $this->_calcNC(static::zeroFill($this->getSequencial(), 8));
	}

	/**
	 * Método para gerar o código da posição de 20 a 44
	 *
	 * @return string
	 * @throws \OpenBoleto\Exception
	 */
	public function getCampoLivre()
	{
		return $this->_calcNC(
			'21' .
			static::zeroFill($this->getAgencia(), 4) .
			static::zeroFill($this->getConta() . $this->getContaDV(), 7) .
			static::zeroFill($this->getSequencial(), 8) .
			'40'
		);
	}

	protected function _calcMod10 ($n) {
		$t = strlen ($n) - 1;
		$fator = 2;
		for ($i=$t;$i>=0;$i--) {
			$v = $n{$i} * $fator;
			if ($v > 9) $v = $v - 9;
			$fator = ($fator == 2) ? 1 : 2;
			$soma += $v;
		}
		$resto = $soma % 10;
		$d = 10 - $resto;
		if ($resto == 0) $d = 0;
		return $d;
	}

	protected function _calcMod11 ($n, $maxFator = 7) {
		$t = strlen ($n) - 1;
		$fator = 2;
		for ($i=$t;$i>=0;$i--) {
			$v = $n{$i} * $fator;
			$fator = ($fator >= $maxFator) ? 2 : ++$fator;
			$soma += $v;
		}
		if ($soma < 11) $resto = $soma;
		else $resto = $soma % 11;
		if (($maxFator == 9) and ($resto == 0 or $resto == 10 or $resto == 1)) return 1;
		if ($resto == 0) return 0;
		$d = 11 - $resto;
		return $d;
	}

	protected function _calcNC ($n) {
		$mod10 = $this->_calcMod10 ($n);
		$mod11 = $this->_calcMod11 ($n.$mod10);
		if ($mod11 == 10) {
			if ($mod10 == 9) $mod10 = 0;
			$mod10++;
			$n .= $mod10.$this->_calcMod11 ($n.$mod10);
		} else $n .= $mod10.$mod11;
		return $n;
	}
}
