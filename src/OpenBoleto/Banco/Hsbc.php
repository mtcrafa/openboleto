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
 * Classe boleto HSBC.
 *
 * @package    OpenBoleto
 * @author     Jerônimo Fagundes da Silva <http://github.com/jeronimofagundes>
 * @license    MIT License
 * @version    1.0
 */
class Hsbc extends BoletoAbstract
{
	/**
	 * Código do banco
	 * @var string
	 */
	protected $codigoBanco = '399';

	/**
	 * Localização do logotipo do banco, referente ao diretório de imagens
	 * @var string
	 */
	protected $logoBanco = 'hsbc.gif';

	/**
	 * Linha de local de pagamento
	 * @var string
	 */
	protected $localPagamento = 'ATE O VENCIMENTO PAGUE PREFERENCIALMENTE NO HSBC<BR>APOS VENCIMENTO PAGUE SOMENTE NO HSBC';

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
	 * Define o número do convênio
	 * @var string
	 */
    protected $codigo_cedente;

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
     * Define o código do cedente
     *
     * @param string $carteira
     * @return BoletoAbstract
     * @throws Exception
     */
    public function setCodigoCedente($codigo_cedente)
    {
        $this->codigo_cedente = $codigo_cedente;
        return $this;
    }

     /**
     * Retorna o código do cedente
     */
    public function getCodigoCedente()
    {
        return $this->codigo_cedente;
    }


	/**
	 * Define o número do convênio. Sempre use string pois a quantidade de caracteres é validada.
	 *
	 * @param string $convenio
	 * @return Hsbc
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
		$ndoc = $this->getSequencial().$this->modulo11invertido($this->getSequencial()).'4';
		$venc = 250515;
		$res = $ndoc + $this->getCodigoCedente() + $venc;
		$ndoc = $ndoc . $this->modulo11invertido($res);

		return static::zeroFill($ndoc, 13);
	}

	protected function modulo11invertido($num)
	{
    $ftini = 2;
		$ftfim = 9;
		$fator = $ftfim;
    $soma = 0;
	
    for ($i = strlen($num); $i > 0; $i--) {
			$soma += substr($num,$i-1,1) * $fator;
			if(--$fator < $ftini) $fator = $ftfim;
    }
	
    $digito = $soma % 11;
		if($digito > 9) $digito = 0;
	
		return $digito;
	}

	/**
	 * Método para gerar o código da posição de 20 a 44
	 * Formato Campo Livre HSBC:
	 * 	01234567    01234567890123  0123
	 * 	--------    --------------  ----
	 * 	Conta s/DV  Nosso número    Data Juliana
	 *
	 * @return string
	 * @throws \OpenBoleto\Exception
	 */
    public function getCampoLivre()
    {
		$dt = explode('/', $this->getDataVencimento()->format('d/m/Y'));

        return static::zeroFill($this->getConta(), 7) . static::zeroFill($this->getNossoNumero(), 13) . $this->_dataJuliana($dt[2], $dt[1], $dt[0]) . 2;

	}

	protected function _dataJuliana($ano, $mes, $dia){
		$stamp = strtotime("$ano-$mes-$dia");

			$dia = sprintf("%03d", intval( date("z",$stamp) + 1 ));
			$ano = substr($ano,-1);

		return $dia.$ano;
	}

	/**
     * Retorna o campo Agência/Cedente do boleto
     *
     * @return string
     */
    public function getAgenciaCodigoCedente()
    {
        return $this->getConta();
    }
}
