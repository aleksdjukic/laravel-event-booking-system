<?php

$moduleProviders = [];

foreach (glob(app_path('Modules/*/Providers/*ModuleServiceProvider.php')) ?: [] as $providerFile) {
    $relativePath = str_replace([app_path().DIRECTORY_SEPARATOR, '.php'], '', $providerFile);
    $moduleProviders[] = 'App\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
}

sort($moduleProviders);

return array_merge(
    [
        App\Providers\AppServiceProvider::class,
    ],
    $moduleProviders
);
