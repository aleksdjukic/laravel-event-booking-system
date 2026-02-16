<?php

$moduleRouteFiles = [
    'Health',
    'Auth',
    'User',
    'Event',
    'Ticket',
    'Booking',
    'Payment',
];

foreach ($moduleRouteFiles as $moduleName) {
    require base_path(sprintf('app/Modules/%s/Presentation/Routes/api.php', $moduleName));
}
