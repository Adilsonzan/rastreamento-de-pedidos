<?php
/**
* 
* Classe de Rastreamento de Pedidos dos Correios. PHP + SOAP
*
* Esta é uma classe estatica simples criada com intuito de rastrear objetos dos correios 
* Utilizando para isso PHP e a API SOAP fornecida pelo sistema dos Correios.
* Fique a vontade para usar como desejar, em vosso sistema, seja 
* ele pessoal ou comercial. 
*
* O único intuito aqui é apresentar de forma simples o 
* funcionamento da API fornecida pelos correios.
* 
* Para mais detalhes verifique a documentação fornecida pelos Correios:
* @link - http://www.correios.com.br/para-voce/correios-de-a-a-z/pdf/rastreamento-de-objetos/Manual_RastreamentoObjetosWS.pdf
*
* @since - 2016.08.21 22:45
* @author Wanderlei Santana <sans.pds@gmail.com>
*/
class Rastrear
{
    /** 
     * URL de acesso a API dos Correios. 
     * @var string 
     */
    private static $wsdl = null ; 

    /** 
     * Seu nome único de usuário para acesso a API
     * Normalmente obtido na agência de sua cidade
     * @var string
     */
    private static $user = null ; 

    /** 
     * Sua senha unica de acesso a API dos correios.
     * Deve ser obtida junto ao nome de usuario
     * @var string
     */
    private static $pass = null ; 

    /** 
     * L ou F - Sendo: 
     * L - usado para uma Lista de Objetos; e
     * F - usado para um intervalo de Objetos.
     * @var Char
     */
    private static $tipo = null ; 

    /** 
     * Delimita o escopo de resposta de cada objeto.
     * T - Retorna todos os eventos do Objeto 
     * U - Será retornado apenas o último evento do Objeto
     * @var Char
     */
    private static $resultado = null ; 

    /** 
     * Deve ser um valor do tipo integer, sendo 
     * 101 - para o retorno em idioma Portugues do Brasil 
     * 102 - para o retorno em idioma Inglês
     * @var integer
     */
    private static $idioma = null ; 

    /** 
     * flag que indica se este objeto foi ou nao inicializado.
     * Apenas para uso interno desta classe
     * @var boolean
     */
    private static $inicializado = false ;

    /**
     * Inicializa este objeto.
     * 
     * É  obrigatorio a chamada deste metodo antes de iniciar 
     * o rastreamento de Objetos.
     *
     * Passe os parametros para login no sistema dos correios.
     * Caso não possua dados de login, entre em contato com a 
     * agencia mais proxima e solicite as credências para utilizar 
     * o sistema.
     *
     * Mesmo que não tenha os dados de login, esta classe irpa funcionar 
     * com Credenciais que são utilizadas como teste.
     *
     * @param array $_param - Matriz contendo os dados de login e 
     * demais dados de acesso a API dos Correios.
     * Caso nada seja informado, a Classe usará os dados Default. 
     * 
     * Dados Experados: 
     *    array['wsdl'] - URL de acesso a API
     *    array['user'] - Nome do Seu usuario de acesso a API dos Correios 
     *    array['pass'] - Sua senha de acesso a API
     *    array['tipo'] - L ou F (normalmente L)
     *    array['resultado'] - T ou U (normalmente T)
     *    array['idioma'] - Padrão é o 101, que indica o idioma Português do Brasil
     *  
     * @return Void
     */
    public static function init( $_params = array() )
    {
        self::$wsdl        = isset($_params['wsdl'])      ? $_params['wsdl']      : "http://webservice.correios.com.br/service/rastro/Rastro.wsdl" ; 
        self::$user        = isset($_params['user'])      ? $_params['user']      : "ECT" ;
        self::$pass        = isset($_params['pass'])      ? $_params['pass']      : "SRO" ;
        self::$tipo        = isset($_params['tipo'])      ? $_params['tipo']      : "L" ;
        self::$resultado   = isset($_params['resultado']) ? $_params['resultado'] : "T" ;
        self::$idioma      = isset($_params['idioma'])    ? $_params['idioma']    : "101" ;
        self::$inicializado= true;
    }

    /**
     * Método que realiza o rastreamento de Objetos dos Correios 
     * espera receber como parametro um CODIGO de rastreamento 
     * devidamente Valido e existente na database dos Correios.
     * EX: PJ012345678BR
     *
     * Para mais do que um Objeto, passaro todos os codigos um após 
     * o outro, sem simbolos especiais ou espaços.
     * EX: PJ012345678BRPJ912345678BRPJ812345678BR
     * 
     * @param  string $__codigo__ - Codigo ou lista de codigos de objetos a ser(em) rastreado(s)
     * @return Object 
     */
    public static function get( $__codigo__ = null )
    {
        # verificacoes simples para validar o codigo. Adicione 
        # outros metodos a seu gosto 
        if(!self::$inicializado)
            return self::erro( "Primeiro acesse o metodo Rastrear::init() com os devidos parametros." );

        if( is_null( $__codigo__ ) )
            return self::erro( "Nenhum código de rastreamento recebido." );

        $_evento = array(
            'usuario'   => self::$user,
            'senha'     => self::$pass,
            'tipo'      => self::$tipo,
            'resultado' => self::$resultado,
            'lingua'    => self::$idioma,
            'objetos'   => trim($__codigo__)
        );

        $client = new SoapClient( self::$wsdl );
        $eventos = $client->buscaEventos( $_evento );

        // sempre retorna objeto por padrao, mesmo em caso de erros.
        return $eventos->return->objeto;
    }

    /**
     * Metodo para retorno de erros no formato de objetos 
     * para manter o padrao de retorno.
     * @param  string $__mensagem - Mensagem de erro a ser retornado
     * @return stdClass Object
     */
    private static function erro( $__mensagem = null ){
        $obj = new stdClass;
        $obj -> erro = $__mensagem ; 
        return $obj ;
    }

} // fim da classe Rastrear



/**
* Abaixo segue exemplo de uso desta classe. 
* Usando como parametros um codigo de rastreamento 
* hipotetico, e os dados de conexao que são encontrados 
* nas documentacoes do sistema.
*/

# setando os parametros de inicialização
$_params = array( 'user' => 'ECT', 'pass' => 'SRO', 'tipo' => 'L', 'resultado' => 'T', 'idioma' => 101 );

# iniciando objeto. 
# note que: mesmo que nao sejam passados parametros, 
# a classe deve funcionar corretamente com os parametros defaults.
Rastrear::init( $_params );

# rastreando um objeto hipotetico
$obj = Rastrear::get( 'JF598971235BR' );

# verificando se retornou erro 
# os erros normalmente indicam um objeto nao encontrado
if(isset($obj->erro))
    die( $obj->erro );

# Visualizando dados basicos do objeto
echo "NUMERO: "    . $obj -> numero . "<br>" ;
echo "SIGLA: "     . $obj -> sigla . "<br>" ;
echo "NOME: "      . $obj -> nome . "<br>" ;
echo "CATEGORIA: " . $obj -> categoria . "<br>" ;

# percorrendo os eventos ocorridos com o objeto
foreach( $obj -> evento as $ev ):

    echo "TIPO: "   . $ev -> tipo   . "<br>" ;
    echo "STATUS: " . $ev -> status . "<br>" ;
    echo "DATA: "   . $ev -> data   . "<br>" ;
    echo "HORA: "   . $ev -> hora   . "<br>" ;
    echo "DESCRICAO: " . $ev -> descricao . "<br>" ;
    if( isset( $ev -> detalhe ) ) 
        echo "DETALHE: " . $ev -> detalhe . "<br>" ;
    echo "LOCAL: "  . $ev -> local  . "<br>" ;
    echo "CODIGO: " . $ev -> codigo . "<br>" ;
    echo "CIDADE: " . $ev -> cidade . "<br>" ;
    echo "UF: "     . $ev -> uf     . "<br>" ;

    if( isset( $ev -> destino ) ):
        echo " DESTINO (LOCAL): "  . $ev -> destino -> local . "<br>" ;
        echo " DESTINO (CODIGO): " . $ev -> destino -> codigo . "<br>" ;
        echo " DESTINO (CIDADE): " . $ev -> destino -> cidade . "<br>" ;
        echo " DESTINO (BAIRRO): " . $ev -> destino -> bairro . "<br>" ;
        echo " DESTINO (UF): "     . $ev -> destino -> uf . "<br>" ;
    endif;

    echo "<hr>";

endforeach;
