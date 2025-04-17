<?php

declare(strict_types=1);

namespace Tests\Stubs;

use Predis\Client;
use Predis\ClientException;
use Predis\Command\CommandInterface;

final class PredisStub extends Client
{
    private bool $simulateConnectionLoss = false;

    public function setSimulateConnectionLoss(bool $state): void
    {
        $this->simulateConnectionLoss = $state;
    }

    /**
     * @throws ClientException
     */
    public function executeCommand(CommandInterface $command): mixed
    {
        if ($this->simulateConnectionLoss) {
            throw new ClientException('Connection loss');
        }

        return parent::executeCommand($command);
    }
}