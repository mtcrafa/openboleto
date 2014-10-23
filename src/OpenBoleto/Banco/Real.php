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
	protected $localPagamento = 'ATE O VENCIMENTO PAGUE PREFERENCIALMENTE NO REAL<BR>APOS VENCIMENTO PAGUE SOMENTE NO REAL';

	/**
	 * Define as carteiras disponíveis para este banco
	 * @var array
	 */
	protected $carteiras = array('057');

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

	/**
	 * Método para gerar o código da posição de 20 a 44
	 *
	 * @return string
	 * @throws \OpenBoleto\Exception
	 */
	public function getCampoLivre()
	{
		$agencia = substr($this->getAgencia(), 0, 4);

		// deixando o nosso numero com 13 digitos
		$nossoNumero = sprintf('%013d', $this->getNossoNumero());
		$contaCedenteBkp = substr($this->getConta(), 0, 7);

		$digitao = $nossoNumero . $agencia . $contaCedenteBkp;
		$digitao = $this->Modulo10($digitao);

		// Montagem da agencia e conta cedente
		$contaCedente 	= substr($this->getConta(), 0, 7);
		$contaCedente1 	= substr($this->getconta(), 0, 1);
		$DAC1 			= $this->modulo10($this->getCodigoBanco() . $this->getMoeda() . $agencia . $contaCedente1);

		$contaCedente2 	= substr($this->getConta(), 1, 7);
		$nossoNumero1 	= substr($nossoNumero, 0, 3);
		$DAC2 			= $this->modulo10($contaCedente2 . $digitao . $nossoNumero1);

		$nossoNumero2 	= substr($nossoNumero, 3, 13);
		$DAC3 			= $this->modulo10($nossoNumero2);

		// Calcula o digito verificador do codigo de barras
		$digitoVerificadorCB = $this->modulo11(
			$this->getCodigoBanco() .
			$this->getMoeda() .
			$this->getFatorVencimento() .
			$this->getValorZeroFill() .
			$agencia .
			$contaCedente .
			$digitao .
			$nossoNumero
		);

		// Numero para o codigo de barras (44 digitos)
		$numCodigoBarras =
			$this->getCodigoBanco() .
			$this->getMoeda() .
			$agencia .
			$contaCedente .
			$digitao .
			$nossoNumero .
			$digitoVerificadorCB .
			$this->getFatorVencimento() .
			$this->getValorZeroFill();

		$parte1 = substr( $numCodigoBarras, 0, 5 );
		$parte2 = substr( $numCodigoBarras, 5, 4 );
		$parte3 = substr( $numCodigoBarras, 9, 5 );
		$parte4 = substr( $numCodigoBarras, 14, 5 );
		$parte5 = substr( $numCodigoBarras, 19, 5 );
		$parte6 = substr( $numCodigoBarras, 24, 5 );
		$parte7 = substr( $numCodigoBarras, 29, 1 );
		$parte8 = substr( $numCodigoBarras, 30,14 );

		$digitaNumCodigoBarras = "$parte1$parte2$DAC1$parte3$parte4$DAC2$parte5$parte6$DAC3$parte7$parte8";

		return substr($digitaNumCodigoBarras, 4, 5) . substr($digitaNumCodigoBarras, 10, 10) . substr($digitaNumCodigoBarras, 21, 10);

		//$CodigoBarras = "$CodigoBanco$NumMoeda$digitoVerificadorCB$FatorVencimento$Valor$agencia$contaCedente$digitao$nossoNumero";
	}

	/**
     * Retorna o campo Agência/Cedente do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoCedente()
	{
		return $this->getAgencia() . '.' . $this->getConvenio() . '.' . $this->getConta();
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
