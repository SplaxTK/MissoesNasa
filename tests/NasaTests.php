<?php
// tests/MissoesTest.php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class MissoesTest extends TestCase
{
    private function criarApp()
    {
        return require __DIR__ . '/../src/app.php';
    }

    private function criarRequest($metodo, $uri, $dados = null)
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest($metodo, $uri);

        if ($dados !== null) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withParsedBody($dados);
        }

        return $request;
    }

    public function testGetStatus(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('GET', '/status');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('ok', $corpo['status']);
    }

    public function testGetListaMissoes(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('GET', '/missoes');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertCount(3, $corpo);
        $this->assertEquals('Apollo 11', $corpo[0]['nome']);
    }

    public function testGetMissaoPorId(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('GET', '/missoes/2');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(2, $corpo['id']);
        $this->assertEquals('Apollo 12', $corpo['nome']);
        $this->assertEquals(1969, $corpo['ano']);
        $this->assertEquals('NASA', $corpo['agencia']);
    }

    public function testGetMissaoInexistente(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('GET', '/missoes/99');
        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'Missão não encontrada',
            $corpo['erro']
        );
    }

    public function testPostCriaMissao(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest(
            'POST',
            '/missoes',
            ['nome' => 'Artemis I']
        );

        $response = $app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Artemis I', $corpo['nome']);
        $this->assertEquals(4, $corpo['id']);
    }

    public function testGetFiltraMissoesPorNome(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest(
            'GET',
            '/missoes?nome=apollo+13'
        );

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $corpo);
        $this->assertEquals('Apollo 13', $corpo[0]['nome']);
    }

    public function testPutAtualizaMissao(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest(
            'PUT',
            '/missoes/1',
            ['nome' => 'Apollo 11 (Revisada)']
        );

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Apollo 11 (Revisada)', $corpo['nome']);
        $this->assertEquals(1, $corpo['id']);
    }

    public function testPutMissaoInexistente(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest(
            'PUT',
            '/missoes/99',
            ['nome' => 'Fantasma']
        );

        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Missão não encontrada', $corpo['erro']);
    }

    public function testDeleteRemoveMissao(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('DELETE', '/missoes/1');
        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals(
            'Missão removida com sucesso',
            $corpo['mensagem']
        );

        $request = $this->criarRequest('GET', '/missoes/1');
        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteMissaoInexistente(): void
    {
        $app = $this->criarApp();

        $request = $this->criarRequest('DELETE', '/missoes/99');
        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());

        $corpo = json_decode((string) $response->getBody(), true);

        $this->assertEquals('Missão não encontrada', $corpo['erro']);
    }
}