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
 * Classe boleto Real.
 *
 * @package    OpenBoleto
 * @author     Jerônimo Fagundes da Silva <http://github.com/jeronimofagundes>
 * @license    MIT License
 * @version    1.0
 */
class Real extends BoletoAbstract
{
	/**
	 * Código do banco
	 * @var string
	 */
	protected $codigoBanco = '356';

	/**
	 * Localização do logotipo do banco, referente ao diretório de imagens
	 * @var string
	 */
	protected $logoBanco = 'real.gif';

	/**
	 * Linha de local de pagamento
	 * @var string
	 */
	protected $localPagamento = 'ATE O VENCIMENTO PAGUE PREFERENCIALMENTE NO REAL SANTANDER<BR>APOS VENCIMENTO PAGUE SOMENTE NO REAL SANTANDER';

	/**
	 * Define as carteiras disponíveis para este banco
	 * @var array
	 */
	protected $carteiras = array('057');

	/**
     * @var array Nome espécie das moedas
     */
    protected static $especie = array(
        self::MOEDA_REAL => 'R$'
    );

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
	 * Gera o Nosso Número.
	 *
	 * @throws Exception
	 * @return string
	 */
	protected function gerarNossoNumero()
	{
		return static::zeroFill($this->getSequencial(), 13);
	}

	protected function getRealDv() {
		return $this->modulo10($this->gerarNossoNumero() . static::zeroFill($this->getAgencia(), 4) . static::zeroFill($this->getConta(), 7));
	}

	/**
	 * Método para gerar o código da posição de 20 a 44
	 *
	 * @return string
	 * @throws \OpenBoleto\Exception
	 */
	public function getCampoLivre() {
		return static::zeroFill($this->getAgencia(), 4) . static::zeroFill($this->getConta(), 7) . $this->getRealDv() . static::zeroFill($this->getSequencial(), 13);
	}

	/**
     * Retorna o campo Agência/Cedente do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoCedente()
	{
		return $this->getAgencia() . '/' . $this->getConta() . '/' . $this->getRealDv();
	}

    /**
     * Retorna o campo Número do documento
     *
     * @return int
     */
	public function getNumeroDocumento() {
		$this->getSequencial();
	}

}
