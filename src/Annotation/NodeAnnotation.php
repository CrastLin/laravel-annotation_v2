<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation;

use Crastlin\LaravelAnnotation\Annotation\Attributes\Node;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Tree;
use Crastlin\LaravelAnnotation\Enum\NodeMode;
use Crastlin\LaravelAnnotation\Store\GeneratorStoreTable;
use ReflectionAttribute;
use stdClass;

/**
 * Permission menu node annotation parsing class
 * @Author crastlin@163.com
 * @date 2024-1-8
 */
class NodeAnnotation extends Annotation
{

    static int $triesTimes = 0;

    /**
     * Analyze annotations
     * @param mixed ...$parameters
     * @return array
     * @throws AnnotationException
     */
    protected function analysis(mixed ...$parameters): array
    {
        if ($this->reflectClass->isAbstract())
            return [];
        $classAttributes = $this->reflectClass->getAttributes(Tree::class) ?: $this->reflectClass->getAttributes(Node::class);
        $tree = new stdClass();
        $tree->module = $module = !empty($parameters[0]) ? $parameters[0] : 'Single';
        [$tree->controller, $tree->ct] = $this->getController();
        $tree->virtualNode = 'defaultPage';
        $tree->checkMode = NodeMode::LOOSE_MODE;
        $this->matchAllAttribute($classAttributes, $tree);
        if (!$tree)
            return [];

        $tree->nodeList = [];
        $methods = $this->reflectClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $node = new stdClass();
            $node->action = $method->getName();
            if (str_starts_with($node->action, '_'))
                continue;
            $attributes = $method->getAttributes(Node::class);
            if (!empty($attributes)) {
                $this->matchAllAttribute($attributes, $node, $tree);
            } elseif ($tree->checkMode == NodeMode::STRICT_MODE && $method->getDeclaringClass()->getName() == $this->reflectClass->getName())
                throw new AnnotationException("{$tree->module}/{$tree->controller}->{$node->action} No node annotations or node names configured", 503);

            if (empty($node->ignore) && !empty($node->name))
                $tree->nodeList[] = $node;
        }
        return [
            'module' => $module,
            'path' => $this->basePath,
            'data' => $tree,
        ];
    }


    /**
     * Match all tree node annotations
     * @param array<ReflectionAttribute> $classAttributes
     * @return void
     * @throws AnnotationException
     */
    protected function matchAllAttribute(array $classAttributes, stdClass &$node, ?stdClass $tree = null): void
    {
        $isMatched = false;
        $path = $tree ? "{$tree->module}/{$tree->controller}->{$node->action}" : '';
        foreach ($classAttributes as $classAttribute) {
            $attributeClass = $classAttribute->getName();
            if (!in_array($attributeClass, !$tree ? [Tree::class, Node::class] : [Node::class]))
                continue;
            $isMatched = true;
            $annotation = $classAttribute->newInstance();
            switch ($attributeClass) {
                case Tree::class:
                    if ($annotation->isMenuNode)
                        $node->virtualNode = !empty($annotation->virtualNode) ? $annotation->virtualNode : 'defaultPage';
                    $node->name = !empty($annotation->name) ? $annotation->name : $node->ct;
                    $node->sort = $annotation->sort ?? 0;
                    $node->preNamedSubMethods = !empty($annotation->preNamedSubMethods) ? explode(',', $annotation->preNamedSubMethods) : [];
                    $node->checkMode = $annotation->checkMode;
                    $node->icon = $annotation->icon ?? '';
                    $node->remark = $annotation->remark ?? '';
                    $node->ignore = $annotation->ignore;
                    $node->delete = $annotation->delete;
                    $node->component = $annotation->component;
                    break;
                case Node::class:
                    // Since this version no longer requires the creation of a root node, if there is a method name that matches the virtual root node name, it will be ignored
                    if (!empty($node->action) && !empty($tree->virtualNode) && $node->action == $tree->virtualNode)
                        return;
                    if (!$tree && $annotation->isMenuNode)
                        $node->virtualNode = 'defaultPage';
                    $node->name = !empty($annotation->name) ? $annotation->name : $node->action;
                    if (!empty($tree->preNamedSubMethods) && in_array($node->action, $tree->preNamedSubMethods))
                        $node->name = "{$tree->name}{$node->name}";
                    $node->sort = $annotation->sort ?? 0;
                    if (!$tree) {
                        $node->preNamedSubMethods = !empty($annotation->preNamedSubMethods) ? explode(',', $annotation->preNamedSubMethods) : [];
                        if (!empty($annotation->parent)) {
                            $ps = explode('/', $annotation->parent);
                            $count = count($ps);
                            $node->parent = match ($count) {
                                1 => "{$node->module}/{$node->ct}/{$annotation->parent}",
                                2 => "{$node->module}/{$annotation->parent}",
                                3 => $annotation->parent,
                            };
                        }
                    } else {
                        if (empty($node->name))
                            throw new AnnotationException("{$path} Node name cannot be empty", 500);
                        $node->parent = "{$tree->module}/{$tree->ct}/{$tree->virtualNode}";
                        if (!empty($annotation->parent)) {
                            $list = explode('/', $annotation->parent);
                            $count = count($list);
                            $node->parent = match ($count) {
                                1 => "{$tree->module}/{$tree->ct}/{$annotation->parent}",
                                2 => "{$tree->module}/{$annotation->parent}",
                                3 => $annotation->parent,
                                default => 'none',
                            };
                            if ($node->parent == 'none')
                                throw new AnnotationException("{$path} Incorrect parent node setting", 501);
                        }
                        $node->code = $annotation->code;
                    }
                    $node->icon = $annotation->icon ?? '';
                    $node->remark = $annotation->remark ?? '';
                    $node->ignore = $annotation->ignore;
                    $node->delete = $annotation->delete;
                    $node->component = $annotation->component;
                    break;
                default:
                    throw new AnnotationException("The current module does not support annotation classes: {$attributeClass}", 502);
            }
            $node->isMenuNode = $annotation->isMenuNode;
            $node->isAuthNode = $annotation->isAuthNode;
            break;
        }
        if (!$isMatched && !$tree)
            $node = null;
    }

    static function build(array $analysisResult, string $savePath): void
    {
        $hasParentErrors = false;
        foreach ($analysisResult as $items) {
            foreach ($items as $item) {
                if (empty($item['data']))
                    continue;
                try {
                    [$tree, $path, $module] = [$item['data'], $item['path'], $item['module']];
                    if (!empty($tree->virtualNode)) {
                        GeneratorStoreTable::store($tree, $module, $tree->ct);
                        echo "+- <Tree> [SUCCESS] [{$tree->name}] {$module}/{$tree->ct}/{$tree->virtualNode} </Tree>" . PHP_EOL;
                    }
                    foreach ($tree->nodeList as $node) {

                        GeneratorStoreTable::store($node, $module, $tree->ct);
                        echo "+--- <Node> [SUCCESS] [{$node->name}] {$module}/{$tree->ct}/{$node->action} </Node>" . PHP_EOL;
                    }
                } catch (\Throwable $exception) {
                    if (!$exception instanceof AnnotationException || $exception->getCode() < 600)
                        throw $exception;
                    echo "=== [ERROR] {$exception->getMessage()} ===" . PHP_EOL;
                    ++self::$triesTimes;
                    $hasParentErrors = true;
                }
            }
        }
        if ($hasParentErrors && self::$triesTimes <= 3) {
            echo "=== [INFO] Repeat Regenerating based on the parent node ... ===" . PHP_EOL;
            self::build($analysisResult, $savePath);
        }
    }


}
