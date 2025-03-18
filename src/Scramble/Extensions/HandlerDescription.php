<?php
namespace Rupadana\ApiService\Scramble\Extensions;
use Dedoc\Scramble\Support\RouteInfo;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Types\StringType;

class HandlerDescription extends OperationExtension
{
    public function handle(Operation $operation, RouteInfo $routeInfo)
    {

        $action = $routeInfo->route->getAction('uses');
        if (is_string($action) && str_contains($action, '@')) {
            [$class, $method] = explode('@', $action);
        } else {
            // Cas d’un __invoke, ou autre
            $class = $action;
            $method = null;
        }




        if (class_exists($class) && method_exists($class, 'description')) {
            $desc = $class::description();
            if ($desc) {
                $operation->description($desc);
            }
        }

    }
}