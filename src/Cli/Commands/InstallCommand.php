<?php declare(strict_types=1);

namespace Asterios\Core\Cli\Commands;

use Asterios\Core\Cli\Attributes\Command;
use Asterios\Core\Cli\Base\BaseCommand;

#[Command(
    name: 'install',
    description: 'Initialize application and create .env file',
    group: 'Setup'
)]
class InstallCommand extends BaseCommand
{
    public function handle(?string $argument): void
    {
        $this->printHeader();

        $envPath = $this->getEnvPath();

        if ($this->fileExists($envPath))
        {
            echo "ℹ️  .env file already exists. Skipping installation.\n";

            return;
        }

        $envContent = $this->buildEnvContent();

        $this->writeFile($envPath, $envContent);

        echo "✅  .env file created successfully.\n";
    }

    /**
     * @return string
     */
    protected function getEnvPath(): string
    {
        return getcwd() . DIRECTORY_SEPARATOR . '.env';
    }

    /**
     * @return string
     */
    protected function buildEnvContent(): string
    {
        return <<<ENV
DB_HOST="db"
DB_USER="db"
DB_PASSWORD="db"
DB_DATABASE="db"
DB_CHARSET="utf8"
LOG_DIRECTORY="logs"
LOG_FILENAME="application"
TEMPLATE_PATH="/views/"
TEMPLATE_EXTENSION="htm.php"
AVAILABLE_MEDIA_TYPES="images,gallery,documents"
ASSETS_PATH="/assets/"
MEDIA_BASE_PATH="/assets/media/"
MEDIA_IMAGE_PATH="/assets/media/images/"
MEDIA_FILES_PATH="/assets/media/files/"
MEDIA_GALLERY_PATH="/assets/media/gallery/"
DATABASE_MIGRATION_PATH="/database/migrations/"
DATABASE_SEEDER_PATH="/database/seeder/"
JWT_SECRET=""

ENV;
    }
}