<?php
namespace Rupadana\ApiService\Scramble\Extensions;
use Dedoc\Scramble\Support\RouteInfo;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Types\StringType;

class HeaderParameters extends OperationExtension
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

        if (class_exists($class) && method_exists($class, 'extraHeaders')) {
            $extraHeaders = $class::extraHeaders();
            if (count($extraHeaders) > 0) {
                foreach ($extraHeaders as $key => $value) {
                    $operation->addParameters([
                        Parameter::make($key, 'header')
                            ->setSchema(
                                Schema::fromType(new StringType())
                            )
                            ->description($value['description'] ?? "")
                            ->required($value['required'] ?? false)
                            ->example($value['sample'] ?? "")
                    ]);
                }
            }

        }

    }
}