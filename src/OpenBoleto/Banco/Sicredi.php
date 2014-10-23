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
 * Classe boleto Sicredi.
 *
 * @package    OpenBoleto
 * @author     Jerônimo Fagundes da Silva <http://github.com/jeronimofagundes>
 * @license    MIT License
 * @version    1.0
 */
class Sicredi extends BoletoAbstract
{
	/**
	 * Código do banco
	 * @var string
	 */
	protected $codigoBanco = '748';

	/**
	 * Localização do logotipo do banco, referente ao diretório de imagens
	 * @var string
	 */
	protected $logoBanco = 'sicredi.gif';

	/**
	 * Linha de local de pagamento
	 * @var string
	 */
	protected $localPagamento = 'ATE O VENCIMENTO PAGUE PREFERENCIALMENTE NO SICREDI<BR>APOS VENCIMENTO PAGUE SOMENTE NO SICREDI';

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
		$nn = date('y') . 2 . static::zeroFill($this->getSequencial(), 5);

		$mod11 = static::modulo11(
			static::zeroFill($this->getAgencia(), 4) .
			static::zeroFill($this->getConvenio(), 2) .
			static::zeroFill($this->getConta(), 5) .
			$nn
		)['digito'];
		$mod11 = ($mod11 == 1) ? 0 : $mod11;

		$nn .= $mod11;

		return $nn;
	}

	/**
	 * Método para gerar o código da posição de 20 a 44
	 *
	 * @return string
	 * @throws \OpenBoleto\Exception
	 */
	public function getCampoLivre()
	{
		$cl =
			'31' .
			$this->gerarNossoNumero() .
			static::zeroFill($this->getAgencia(), 4) .
			static::zeroFill($this->getConvenio(), 2) .
			static::zeroFill($this->getConta(), 5) .
			'00';
		$mod11 = static::modulo11($cl)['digito'];
		$mod11 = ($mod11 == 1) ? 0 : $mod11;

		$cl .= $mod11;

		return $cl;
	}
}
