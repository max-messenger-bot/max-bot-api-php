<?php

declare(strict_types=1);

namespace MaxMessenger\Bot;

use LogicException;
use MaxMessenger\Bot\Contracts\CommandClassInterface;
use MaxMessenger\Bot\Contracts\MaxApiConfigInterface;
use MaxMessenger\Bot\Models\Response\Update;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function sprintf;

final class MaxBot
{
    private readonly MaxApiClient $apiClient;
    /**
     * @var array<non-empty-string, class-string<CommandClassInterface>|callable|CommandClassInterface>
     */
    private array $commands;
    private ?ContainerInterface $container = null;

    public function __construct(
        string|MaxApiConfigInterface|MaxApiClient $accessTokenOrConfig
    ) {
        $this->apiClient = $accessTokenOrConfig instanceof MaxApiClient
            ? $accessTokenOrConfig
            : new MaxApiClient($accessTokenOrConfig);
    }

    /**
     * @param non-empty-string $name
     * @param class-string<CommandClassInterface>|callable|CommandClassInterface $handler
     * @api
     */
    public function addCommand(string $name, string|callable|CommandClassInterface $handler): self
    {
//        if (is_string($handler)) {
//            $handler = static function (mixed $command, self $maxBot) use ($handler): mixed {
//                return $maxBot->makeCommandHandler($handler)->handle($command, $maxBot);
//            };
//        } elseif (!is_callable($handler)) {
//            $handler = $handler->handle(...);
//        }
//
        $this->commands[$name] = $handler;

        return $this;
    }

    /**
     * @api
     */
    public function getApiClient(): MaxApiClient
    {
        return $this->apiClient;
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    public function handle(Update $update): bool
    {
        return false;
    }

    public function handleFromGlobal(): bool
    {
        return false;
    }

    public function handleFromUpdates(): bool
    {
        return false;
    }

    public function removeCommand(string $name): self
    {
        unset($this->commands[$name]);
    }

    /**
     * @return $this
     */
    public function setContainer(?ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function makeCommandHandler(string $className): CommandClassInterface
    {
        if ($this->container) {
            $handler = $this->container->get($className);
        } else {
            if (!class_exists($className)) {
                throw new LogicException(sprintf('Class "%s" does not exist.', $className));
            }

            $handler = new $className();
        }

        if (!$handler instanceof CommandClassInterface) {
            throw new LogicException(sprintf('Class "%s" does not implement CommandClassInterface.', $className));
        }

        return $handler;
    }
}
