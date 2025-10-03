<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Random\RandomException;

#[Command(
    name: 'fake:uuid',
    description: 'Generate a random UUID',
    group: 'Testdata',
    aliases: ['--fuuid']
)]
class FakeUuidCommand extends BaseCommand
{
    /**
     * @inheritDoc
     */
    public function handle(?string $argument): void
    {

        $this->printDataTable([
            'UUID Testdaten' => [
                'UUID' => $this->generateUuidV4(),
            ]
        ]);
    }

    /**
     * @return string
     */
    private function generateUuidV4(): string
    {
        try
        {
            $data = random_bytes(16);
        }
        catch (RandomException)
        {
            return '';
        }

        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
