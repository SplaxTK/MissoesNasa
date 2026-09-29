<?php
require __DIR__ . '/../vendor/autoload.php';
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$missoes = [
        ["id"=> 1, 
        "nome"=> "Apollo 11", 
        "ano"=> 1969, 
        "agencia"=> "NASA", 
        "status"=> "Concluída"],

        ["id"=> 2, 
        "nome"=> "Apollo 12", 
        "ano"=> 1969, 
        "agencia"=> "NASA", 
        "status"=> "Concluída"],
        
        ["id"=> 3,
        "nome"=> "Apollo 13", 
        "ano"=> 1970, 
        "agencia"=> "NASA", 
        "status"=> "Andamento"]
];

// status
$app->get('/status', function ($request, $response) {
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

//GET
$app->get('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
$missao = current(array_filter($missoes, fn ($item) => $item['id'] === (int) $args['id'])) ?: null;
if (!$missao) {
$response->getBody()->write(json_encode(['erro' => 'Missão não encontrada']));
return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
}
$response->getBody()->write(json_encode($missao));
return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});
 
//GET com filtro
$app->get('/missoes', function ($request, $response) use (&$missoes) {
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;
 
    if($nome) {
        $missoesFiltradas = array_filter($missoes, fn($item) => str_contains(mb_strtolower($item['nome']), mb_strtolower($nome)));
    } else {
        $missoesFiltradas = $missoes;
    }

    $response->getBody()->write(json_encode(array_values($missoesFiltradas)));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

//POST
$app->post('/missoes', function ($request, $response) use (&$missoes) {
$dados = $request->getParsedBody();
$novaMissao = ['id' => count($missoes) + 1, 'nome' => $dados['nome']];
$missoes[] = $novaMissao;
$response->getBody()->write(json_encode($novaMissao));
return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

//PUT
$app->put('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
    $missao = current(array_filter($missoes, fn ($item) => $item['id'] === (int) $args['id'])) ?: null;
    if (!$missao) {
        $response->getBody()->write(json_encode(['erro' => 'Missão não encontrada']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $dados = $request->getParsedBody();
    $missao['nome'] = $dados['nome'];
    $response->getBody()->write(json_encode($missao));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

//DELETE
$app->delete('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
    $missao = current(array_filter($missoes, fn ($item) => $item['id'] === (int) $args['id'])) ?: null;
    if (!$missao) {
        $response->getBody()->write(json_encode(['erro' => 'Missão não encontrada']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    $missoes = array_filter($missoes, fn ($item) => $item['id'] !== (int) $args['id']);
    $response->getBody()->write(json_encode(['mensagem' => 'Missão removida com sucesso']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->run();