<?php

$moduleRouteFiles = glob(app_path('Modules/*/Presentation/Routes/api.php')) ?: [];
sort($moduleRouteFiles);

foreach ($moduleRouteFiles as $routeFile) {
    require $routeFile;
}
