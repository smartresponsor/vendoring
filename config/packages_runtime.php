<?php

declare(strict_types=1);

use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $frameworkConfig = [
        'secret' => '%env(default:vendoring_secret:APP_SECRET)%',
        'http_method_override' => false,
        'handle_all_throwables' => true,
        'php_errors' => ['log' => true],
    ];

    if (class_exists('Symfony\\Component\\Form\\AbstractType')) {
        $frameworkConfig['form'] = ['enabled' => true];
    }

    if (class_exists('Symfony\\Component\\Validator\\Validation')) {
        $frameworkConfig['validation'] = ['enabled' => true];
    }

    if (interface_exists('Symfony\\Component\\Security\\Csrf\\CsrfTokenManagerInterface')) {
        $frameworkConfig['csrf_protection'] = ['enabled' => true];
    }

    if (class_exists(FrameworkBundle::class)) {
        $configurator->extension('framework', $frameworkConfig);
    }

    if (class_exists(TwigBundle::class)) {
        $configurator->extension('twig', [
            'default_path' => '%kernel.project_dir%/templates',
            'form_themes' => ['bootstrap_5_layout.html.twig'],
        ]);
    }

    if (class_exists(NelmioApiDocBundle::class)) {
        $configurator->extension('nelmio_api_doc', [
            'documentation' => [
                'info' => [
                    'title' => 'Vendoring API',
                    'description' => 'Release-candidate API documentation surface for the vendoring component.',
                    'version' => 'rc-runtime',
                ],
            ],
            'areas' => [
                'default' => [
                    'path_patterns' => ['^/api'],
                ],
            ],
        ]);
    }
};
