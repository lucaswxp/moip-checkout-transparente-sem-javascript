<?php
/**
  * Depende das funções: json_encode, json_decode & curl
  *
  * Esse script deve trabalhar em conjunto com o PHP SDK oficial da moip: https://github.com/moiplabs/moip-php.
  *
  * Utilize a biblioteca oficial da Moip para pegar o token da transação e em seguida
  * utilize essa classe para finalizar a transação SEM a necessidade de utilizar Javascript,
  * na etapa final do proceso de checkout.
  *
  * Todas as informações que você passaria para a api JS você pode passar nesta versão PHP através do método ->set()
  * e em seguida efetuar a requisição com ->getAnswer().
  *
  *
  * Como usar:

    $checkout = new MoipCheckout($meuToken);
    $checkout->sandbox = true;
    $checkout->set(array(
      'Forma' => 'CartaoCredito',
      'Parcelas' => 1,
      'Instituicao' => 'Visa',
      'CartaoCredito' => array(
        'Numero' => '4073020000000002',
        'Expiracao' => '12/15',
        'CodigoSeguranca' => '123',
        'Portador' => array(
          'Nome' => 'Nome Sobrenome',
          'DataNascimento' => '30/12/1987',
           'Telefone' => '(11) 3165-6584',
           'Identidade' => '222.222.222-22' // CPF!
         )
      )
    ));

    $data = $checkout->getAnswer();

    if($data->StatusPagamento == 'Sucesso'){
      echo 'Pagamento processado';
    }else{
      echo 'Algo deu errado: ';
      exit($data['Mensagem'])
    }

  */

class MoipCheckout {
  public $sandbox;

  private $token;

  private $data = array();

  const SANDBOX_ENDPOINT = 'https://desenvolvedor.moip.com.br/sandbox';
  const PRODUCTION_ENDPOINT = 'https://www.moip.com.br';

  public function __construct($token, $sandbox = false){
    $this->token = $token;
    $this->sandbox = $sandbox;
  }

  public function set(array $data){
    $this->data = $data;
  }

  public function getAnswer(){
    $curl = $this->getCurl();
    $response = curl_exec($curl);
    $response = json_decode(substr($response, 1, -1)); // substr remove parenteses

    if($response->StatusPagamento === 'Sucesso'){
      $response->url = $this->resolveUrl('/Instrucao.do?token=' . $this->token);
    }

    return $response;
  }

  public function getParcAnswer(){
    $curl = $this->getParcCurl();
    $response = curl_exec($curl);
    $response = json_decode(substr($response, 1, -1)); // substr remove parenteses

    return $response;
  }

  public function getParcCurl(){
    $qs = $this->getQuery('parc');
    $url = $this->resolveUrl('/rest/pagamento/consultarparcelamento?' . http_build_query($qs));
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
    return $ch;
  }

  public function getCurl(){
    $qs = $this->getQuery();
    $url = $this->resolveUrl('/rest/pagamento?' . http_build_query($qs));
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    return $ch;
  }

  public function getQuery($type = null){
    if (empty($type))
    {
      return array(
        'callback' => '',
        'pagamentoWidget' => json_encode(array(
          'pagamentoWidget' => array(
            'token' => $this->token,
            'dadosPagamento' => $this->data,
            'referer' => $this->getReferer()
          )
        ))
      );
    } else if ($type == 'parc'){
      return array(
        'callback' => '',
        'token' => $this->token,
        'instituicao' => 'Visa',
        );
    }
  }

  private function resolveUrl($url){
    return ($this->sandbox ? self::SANDBOX_ENDPOINT : self::PRODUCTION_ENDPOINT) . $url;
  }

  private function getReferer(){
    if(@$_SERVER['HTTP_REFERER']){
      return $_SERVER['HTTP_REFERER'];
    }else{
      return ((stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    }
  }
}
