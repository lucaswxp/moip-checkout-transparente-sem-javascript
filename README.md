Moip sem javascript
===

Essa classe foi criada para eliminar a necessidade que a Moip impõe de se utilizar
a API javascript deles para finalizar uma transação.

Por que eles mesmos não tem uma API completa? Não faço ideia! Mas isso não vai nos impedir ;)

Esse script deve trabalhar em conjunto com o PHP SDK oficial da moip: https://github.com/moiplabs/moip-php

Exemplos
===

Se você quer saber mais como essa classe funciona, leia as seções mais abaixo.

Aqui estão dois exemplos de código, um para cartão de crédito e outro para boleto:

Exemplo com Cartão de Crédito
-------
```php
require 'MoipCheckout.php';

// o $token você pega com a SDK que a própria Moip disponibiliza: https://github.com/moiplabs/moip-php
$checkout = new MoipCheckout($token);

$checkout->sandbox = true; // mude para false quando entrar em produção
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

$data = $checkout->getAnswer(); // faz a requisição http

if($data->StatusPagamento == 'Sucesso'){
  echo 'Pagamento processado';
}else{
  echo 'Algo deu errado: ';
  exit($data['Mensagem'])
}
```

Exemplo com Boleto Bancário
-------
```php
require 'MoipCheckout.php';

// o $token você pega com a SDK que a própria Moip disponibiliza: https://github.com/moiplabs/moip-php
$checkout = new MoipCheckout($token);

$checkout->sandbox = true; // mude para false quando entrar em produção
$checkout->set(array(
  'Forma' => 'BoletoBancario',
  'Instituicao' => 'BancoDoBrasil'
));

$data = $checkout->getAnswer(); // faz a requisição http

if($data->StatusPagamento == 'Sucesso'){
  // redireciona o usuário para a URL boleto
  header('Location: ' . $data->url);
}else{
  echo 'Algo deu errado: ';
  exit($data['Mensagem'])
}
```

Como funciona
===
É bem simples. Tudo o que a API Javascript da Moip faz é validar os dados que
você informa na função "MoipWidget" e então envia as informações para o servidor
através de uma requisição HTTP (ajax).

Então tudo que temos que fazer é uma requisição HTTP para o servidor da Moip que tudo funcionará!
Por isso será necessário que você tenha o curl instalado para que tudo funcione.

Diferença entre as API's
---
A única diferença entre a API javascript e este repositório é que a versão Javascript
irá VALIDAR seus dados antes de enviar para o servidor deles (cpf, datas, etc). Este comportamento
NÃO foi implementado nessa classe, então tenha certeza de enviar dados validados
para o objeto.

Se por acaso você enviar informações erradas para o servidor, nada de mais irá acontecer, você
ainda receberá um objeto com uma mensagem de erro na resposta, mas a mensagem não será
tão clara quanto a versão javascript.

Resumindo... tenha certeza de enviar dados válidos.
