<?php

namespace Deployer;

require 'recipe/symfony4.php';

// Metanet Hack
set('bin/php', function () {
    return '/opt/php74/bin/php';
});

// Metanet Hack
set('bin/composer', function () {
    return '/opt/php74/bin/php /usr/bin/composer';
});

// Project name
set('application', 'ciclassifier-data-backend');

// Project repository
set('repository', 'git@github.com:tzemp/ciclassifier-data-backend.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

inventory('servers.yaml');
