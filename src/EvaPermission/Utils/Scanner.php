<?php
/**
* EvaEngine (http://evaengine.com/)
*
* @copyright Copyright (c) 2014 AlloVince (allo.vince@gmail.com)
* @license   http://framework.zend.com/license/new-bsd New BSD License
*/

namespace Eva\EvaPermission\Utils;

use Eva\EvaPermission\Entities;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Scanner
{
    protected $scanPath;

    public function scan()
    {
        $scanPath = $this->scanPath;
        $finder = new Finder();
        $iterator = $finder
        ->files()
        ->name('*Controller.php')
        ->in($scanPath)
        ->exclude('vendor');
        $this->controllers = $iterator;
        return $this;
    }

    public function getControllers()
    {
        return $this->controllers;
    }

    public function process(SplFileInfo $controller)
    {
        $controllerPath = $controller->getPathName();
        $controllerClass = $this->getClassName($controller->getContents());
        if (!$controllerClass) {
            return $this;
        }

        $resource = $this->getResource($controllerClass);

        if (!$resource) {
            return $this;
        }

        $operations = $this->getOperations($controllerClass);

        if (!$operations) {
            return $this;
        }

        $resourceModel = Entities\Resources::findFirstByResourceKey($resource['resourceKey']);
        if (!$resourceModel) {
            $resourceModel = new Entities\Resources();
        }
        $resourceModel->assign($resource);

        $operationModels = array();
        foreach ($operations as $operation) {
            $operation['operationKey'] = strtolower($operation['operationKey']);
            $operationModel = Entities\Operations::findFirst(array(
                "conditions" => "resourceKey = :resourceKey: AND operationKey = :operationKey:",
                "bind"       => array(
                    'resourceKey' => $operation['resourceKey'],
                    'operationKey' => $operation['operationKey'],
                )
            ));
            if (!$operationModel) {
                $operationModel = new Entities\Operations();
            }
            $operationModel->assign($operation);
            $operationModels[] = $operationModel;
        }
        $resourceModel->operations = $operationModels;
        if ($resourceModel->save()) {
            echo sprintf("Resource %s already added to DB\n", $resource['resourceKey']);
        } else {
            print_r($resourceModel->getMessages());
        }

        /*
        p($resource);
        p($operations);
        p('-----------------');
        */
        return $this;
    }

    public function getResource($controllerClass)
    {
        $ref = new \ReflectionClass($controllerClass);
        //Not a private resource
        if (!$ref->implementsInterface('Eva\EvaEngine\Mvc\Controller\TokenAuthorityControllerInterface') &&
          !$ref->implementsInterface('Eva\EvaEngine\Mvc\Controller\SessionAuthorityControllerInterface')) {
            return false;
        }
        
        $resourceGroup = 'app';
        if ($ref->implementsInterface('Eva\EvaEngine\Mvc\Controller\TokenAuthorityControllerInterface')) {
            $resourceGroup = 'api';
        } else {
            if ($ref->isSubclassOf('Eva\EvaEngine\Mvc\Controller\AdminControllerBase')) {
                $resourceGroup = 'admin';
            }
        }

        $reader = new \Phalcon\Annotations\Adapter\Memory();
        $reflector = $reader->get($controllerClass);
        $resourceAnnotations = $reflector->getClassAnnotations();
        $resourceName = $controllerClass;
        $resourceDes = '';

        if ($resourceAnnotations
                && $resourceAnnotations->has('resourceName')
                && $annotation = $resourceAnnotations->get('resourceName')
            ) {
            $resourceName = implode('', $annotation->getArguments());
        }

        if ($resourceAnnotations
                && $resourceAnnotations->has('resourceDescription')
                && $annotation = $resourceAnnotations->get('resourceDescription')
            ) {
            $resourceDes = implode('', $annotation->getArguments());
        }

        $resource = array(
            'name' => $resourceName,
            'resourceKey' => $controllerClass,
            'resourceGroup' => $resourceGroup,
            'description' => $resourceDes,
        );

        return $resource;
    }

    public function getOperations($controllerClass)
    {
        $operations = array();

        $ref = new \ReflectionClass($controllerClass);
        $methods = $ref->getMethods();
        $reader = new \Phalcon\Annotations\Adapter\Memory();
        $reflector = $reader->get($controllerClass);
        $operationAnnotations = $reflector->getMethodsAnnotations();

        foreach ($methods as $method) {
            if (!\Phalcon\Text::endsWith($method->name, 'Action')) {
                continue;
            }

            $operationKey = substr($method->name, 0, -6);
            $operationName = $operationKey;
            $operationDes = '';

            if (isset($operationAnnotations[$method->name]) && $annotations = $operationAnnotations[$method->name]) {
                if (!$annotations->has('operationName') || !$annotations->has('operationDescription')) {
                    continue;
                }
                $annotation = $annotations->get('operationName');
                $operationName = implode('', $annotation->getArguments());
                $annotation = $annotations->get('operationDescription');
                $operationDes = implode('', $annotation->getArguments());
            }

            $operation = array(
                'name' => $operationName,
                'resourceKey' => $controllerClass,
                'operationKey' => $operationKey,
                'description' => $operationDes,
            );
            $operations[] = $operation;
        }
        return $operations;
    }

    protected function getClassName($sourceCode)
    {
        $tokens = token_get_all($sourceCode);
        $tokenLength = count($tokens);
        for ($i = 0; $i < $tokenLength; $i++) {
            if (isset($tokens[$i][1]) && strtolower($tokens[$i][1]) == 'namespace') {
                $j = 1;
                $namespace = '';
                while (true) {
                    if (empty($tokens[$i + $j][1])) {
                        if ($j != 1) {
                            break;
                        }
                    }
                    $namespace .= $tokens[$i + $j][1];
                    $j++;
                }
                $namespaces[] = trim($namespace); //there can be only one (=> highlander) per file, but by not assuming we could find errors/throw exceptions
                $i += $j;
            }

            if (isset($tokens[$i][1]) && strtolower($tokens[$i][1]) == 'class') {
                $j = 1;
                $class = '';
                while (true) {
                    if (isset($tokens[$i + $j][1]) && trim($tokens[$i + $j][1]) == '') {
                        if ($j != 1) {
                            break;
                        }
                    }
                    $class .= $tokens[$i + $j][1];
                    $j++;
                }
                $classes[] = trim($class); //there can be only one (=> highlander) per file, but by not assuming we could find errors/throw exceptions
                $i += $j;
            }
        }

        if (empty($namespaces) || empty($classes)) {
            return '';
        }
        return $namespaces[0] . '\\' . $classes[0];
    }

    public function __construct($scanPath = __DIR__)
    {
        $this->scanPath = $scanPath;
    }
}
