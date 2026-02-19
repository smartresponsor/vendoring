<?php declare(strict_types=1);
namespace App\Service\Vendor\Api; use SmartResponsor\Vendor\Api\Http\Router; final class Bootstrap{ public static function app($service): Router { return new Router($service);} }
