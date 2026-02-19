<?php declare(strict_types=1);
namespace App\Service\Vendor\Api\Http; final class Router{public function __construct(private $service){} public function handle(array $req): array{ if(($req['method']??'')==='GET' && ($req['path']??'')==='/health'){ return ['status'=>200,'body'=>['ok'=>true]];} return ['status'=>404,'body'=>['error'=>'Not found']]; }}
