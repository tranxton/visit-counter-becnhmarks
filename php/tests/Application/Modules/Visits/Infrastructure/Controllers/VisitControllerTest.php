<?php

declare(strict_types=1);

namespace Tests\Application\App\Modules\Visits\Infrastructure\Controllers;

use JsonException;
use Predis\ClientInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Tests\Stubs\PredisStub;

class VisitControllerTest extends WebTestCase
{
    private const REGISTER_VISIT_METHOD = 'POST';
    private const GET_VISIT_METHOD = 'GET';
    private const URL = '/visits';

    private KernelBrowser $client;
    private Container $container;
    private PredisStub $redis;

    /**
     * @return array<string, array{country: string, visits: int}>
     */
    public static function countriesDataProvider(): array
    {
        return [
            'USA' => [
                'country' => 'RU',
                'visits' => 1
            ],
            'France' => [
                'country' => 'FR',
                'visits' => 1
            ],
            'Germany' => [
                'country' => 'DE',
                'visits' => 1
            ],
            'Italy' => [
                'country' => 'IT',
                'visits' => 1
            ],
            'Japan' => [
                'country' => 'JP',
                'visits' => 1
            ],
        ];
    }

    /**
     * @dataProvider countriesDataProvider
     * @throws JsonException
     */
    public function testRegisterVisitSuccess(string $country, int $visits): void
    {
        $this->client->request(self::REGISTER_VISIT_METHOD, sprintf('%s/%s', self::URL, $country));
        $response = $this->client->getResponse();
        /** @var array{visits: array{string: mixed}} $decodedBody */
        $decodedBody = json_decode(json: (string) $response->getContent(), associative: true,
            flags: JSON_THROW_ON_ERROR);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertArrayHasKey('visits', $decodedBody);
        self::assertArrayHasKey($country, $decodedBody['visits']);
        self::assertIsNumeric($decodedBody['visits'][$country]);
        self::assertEquals($visits, $decodedBody['visits'][$country]);
    }

    /**
     * @throws JsonException
     */
    public function testRegisterVisitFailsWithValidationError(): void
    {
        $this->client->request('POST', sprintf('%s/%s', self::URL, 'RUS'));

        $response = $this->client->getResponse();
        /** @var array{error: string} $decodedBody */
        $decodedBody = json_decode(json: (string) $response->getContent(), associative: true,
            flags: JSON_THROW_ON_ERROR);

        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertArrayHasKey('error', $decodedBody);
        self::assertStringContainsString("This value is not a valid country.", $decodedBody['error']);
    }

    /**
     * @throws JsonException
     */
    public function testRegisterVisitFailsWithRedisError(): void
    {
        $this->redis->setSimulateConnectionLoss(true);

        $this->client->request('POST', sprintf('%s/%s', self::URL, 'RU'));

        $response = $this->client->getResponse();
        /** @var array{error: string} $decodedBody */
        $decodedBody = json_decode(json: (string) $response->getContent(), associative: true,
            flags: JSON_THROW_ON_ERROR);

        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertArrayHasKey('error', $decodedBody);
        self::assertStringContainsString("Something went wrong while registering visits.", $decodedBody['error']);
    }

    /**
     * @dataProvider countriesDataProvider
     * @throws JsonException
     */
    public function testGetVisitsSuccess(string $country, int $visits): void
    {
        $this->client->request(self::REGISTER_VISIT_METHOD, sprintf('%s/%s', self::URL, $country));
        $this->client->request(self::GET_VISIT_METHOD, self::URL);

        $response = $this->client->getResponse();
        $decodedBody = (array) json_decode(json: (string) $response->getContent(), associative: true,
            flags: JSON_THROW_ON_ERROR);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertArrayHasKey('visits', $decodedBody);
        self::assertIsArray($decodedBody['visits']);
        self::assertArrayHasKey($country, $decodedBody['visits']);
        self::assertIsNumeric($decodedBody['visits'][$country]);
        self::assertEquals($visits, $decodedBody['visits'][$country]);
    }

    /**
     * @throws JsonException
     */
    public function testGetVisitsFailsWithRedisError(): void
    {
        $this->redis->setSimulateConnectionLoss(true);

        $this->client->request(self::GET_VISIT_METHOD, self::URL);

        $response = $this->client->getResponse();
        /** @var array{error: string} $decodedBody */
        $decodedBody = json_decode(json: (string) $response->getContent(), associative: true,
            flags: JSON_THROW_ON_ERROR);

        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertArrayHasKey('error', $decodedBody);
        self::assertStringContainsString("Something went wrong getting visits statistics.", $decodedBody['error']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->container = static::getContainer();

        /** @var PredisStub $redis */
        $redis = $this->container->get(ClientInterface::class);
        $this->redis = $redis;
        $this->redis->flushdb();
    }
}
