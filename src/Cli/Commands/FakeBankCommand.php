<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;
use Random\RandomException;

#[Command(
    name: 'fake:bank',
    description: 'Generate random bank test data (IBAN, BIC, Institute)',
    group: 'Testdata',
    aliases: ['--fb']
)]
class FakeBankCommand extends BaseCommand
{
    public function handle(?string $argument): void
    {
        $countryCode = strtoupper($argument ?? 'DE');
        $iban = $this->generateIban($countryCode);
        $bic = $this->generateBic($countryCode);
        $institute = $this->randomInstitute();

        $this->printDataTable([
            'Bank Testdata' => [
                'IBAN'      => $iban,
                'BIC'       => $bic,
                'Institut'  => $institute,
            ]
        ]);
    }

    /**
     * @param string $countryCode
     * @return string
     */
    private function generateIban(string $countryCode): string
    {
        $checkDigits = '';
        $bankCode = '';
        $accountNumber = '';

        try
        {
            $checkDigits = str_pad((string)random_int(10, 99), 2, '0', STR_PAD_LEFT);
            $bankCode = str_pad((string)random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            $accountNumber = str_pad((string)random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
        }
        catch (RandomException)
        {

        }
        return "{$countryCode}{$checkDigits}{$bankCode}{$accountNumber}";
    }

    private function generateBic(string $countryCode): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $bic = '';

        try
        {
            for ($i = 0; $i < 4; $i++)
            {
                $bic .= $letters[random_int(0, 25)];
            }
            $bic .= $countryCode;

            for ($i = 0; $i < 3; $i++)
            {
                $bic .= $letters[random_int(0, 25)];
            }
        }
        catch (RandomException)
        {
        }

        return $bic;
    }

    private function randomInstitute(): string
    {
        $institutes = [
            'Deutsche Bank',
            'Commerzbank',
            'Sparkasse Musterstadt',
            'Volksbank Beispiel',
            'N26 Bank',
            'DKB',
            'HypoVereinsbank',
        ];
        return $institutes[array_rand($institutes)];
    }
}
