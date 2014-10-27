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
use OpenBoleto\Agente;

/**
 * Classe boleto Caixa Economica Federal - Modelo NÃO SIGCB.
 *
 * @package    OpenBoleto
 * @author     Jerônimo Fagundes da Silva <jeronimofagundesdasilva@gmail.com>
 * @license    MIT License
 * @version    1.0
 */
class CaixaComum extends BoletoAbstract
{
    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = '104';

    /**
     * Localização do logotipo do banco, referente ao diretório de imagens
     * @var string
     */
    protected $logoBanco = 'caixa.png';

    /**
     * Linha de local de pagamento
     * @var string
     */
    protected $localPagamento = 'ATE O VENCIMENTO PAGUE PREFERENCIALMENTE NA CEF OU NAS CASAS LOTERICAS<br>APOS O VENCIMENTO PAGUE SOMENTE NA CEF OU NAS CASAS LOTERICAS';

    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = array('SR', 'SR16', 'CR');

    /**
     * Nome do arquivo de template a ser usado
     *
     * A Caixa obriga-nos a usar campos não presentes no projeto original, além de alterar cedente
     * para beneficiário e sacado para pagador. Segundo o banco, estas regras muitas vezes não são
     * observadas na homologação, mas, considerando o caráter subjetivo de quem vai analisar na Caixa,
     * preferi incluir todos de acordo com o manual. Para conhecimento, foi copiado o modelo 3.5.1 adaptado
     * Também removi os campos Espécie, REAL, Quantidade e Valor por considerar desnecessários e não obrigatórios
     *
     * @var string
     */
    protected $layout = 'caixa.phtml';

    /**
     * Define o número da conta
     *
     * Overrided porque o cedente da Caixa TEM QUE TER 7 posições, senão não é válido
     *
     * @param int $conta
     * @return BoletoAbstract
     */
    public function setConta($conta)
    {
        $this->conta = self::zeroFill($conta, 7);
        return $this;
    }

    /**
     * Gera o Nosso Número.
     *
     * @throws Exception
     * @return string
     */
    protected function gerarNossoNumero()
    {
		switch($this->getCarteira()) {
			case 'SR':
				return '82' . static::zeroFill($this->getSequencial(), 8);
			case 'SR16':
				return '80' . static::zeroFill($this->getSequencial(), 8);
			case 'CR':
				return '9' . static::zeroFill($this->getSequencial(), 9);
			default:
				return $this->getSequencial();
		}
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws \OpenBoleto\Exception
     */
    public function getCampoLivre()
    {
		return $this->gerarNossoNumero() . static::zeroFill($this->getConvenio(), 3) . static::zeroFill($this->getConta(), 8);
	}
}
