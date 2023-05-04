<?php

return [
    'enabled' => env('BLADE_DEVTOOLS_ENABLED', env('APP_DEBUG', false)),
    'remote_sites_path' => env('BLADE_DEVTOOLS_REMOTE_SITES_PATH', env('IGNITION_REMOTE_SITES_PATH', env('DEBUGBAR_REMOTE_SITES_PATH', base_path()))),
    'local_sites_path' => env('BLADE_DEVTOOLS_LOCAL_SITES_PATH', env('IGNITION_LOCAL_SITES_PATH', env('DEBUGBAR_LOCAL_SITES_PATH', base_path()))),
    'editor' => env('BLADE_DEVTOOLS_EDITOR', env('IGNITION_EDITOR', env('DEBUGBAR_EDITOR', 'vscode'))),
];