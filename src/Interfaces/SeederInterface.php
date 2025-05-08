<?php declare(strict_types=1);

namespace Asterios\Core\Interfaces;

interface SeederInterface
{
    /**
     * @param bool $truncateTables
     * @return bool
     */
    public function seed(bool $truncateTables = true): bool;

    /**
     * @return string|null
     */
    public function getSeederPath(): ?string;

    /**
     * @param array<string, array<int, string>> $seeder
     * @return void
     */
    public function setSeeder(array $seeder): void;

    /**
     * @return string[]
     */
    public function getErrors(): array;

    /**
     * @return string[]
     */
    public function getMessages(): array;

}