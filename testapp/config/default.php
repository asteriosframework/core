<?php declare(strict_types=1);

use Asterios\Core\Asterios;

return [
    'environment' => env('ENVIRONMENT', Asterios::FEATURE),
    'timezone' => env('TIMEZONE', 'Europe/Berlin'),
    'debug_mode' => env('DEBUG_MODE', 'true'),
    'security' => [
        'input_filter' => env('SECURITY_INPUT_FILTER', null),
    ],
    'assets_path' => env('ASSETS_PATH', '/assets/'),
    'media_base_path' => env('MEDIA_BASE_PATH', '/assets/media/'),
    'media_image_path' => env('MEDIA_IMAGE_PATH', '/assets/media/images/'),
    'media_files_path' => env('MEDIA_FILES_PATH', '/assets/media/files/'),
    'media_gallery_path' => env('MEDIA_GALLERY_PATH', '/assets/media/gallery/'),
    'database' => [
        'migration_path' => env('DATABASE_MIGRATION_PATH', '/database/migrations/'),
        'seeder_path' => env('DATABASE_SEEDER_PATH', '/database/seeders/'),
    ],
    'logger' => [
        'log_dir' => env('LOGGER_LOG_DIR', '/var/logs/'),
    ],
];
