<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Store;

use Crastlin\LaravelAnnotation\Annotation\AnnotationException;
use Illuminate\Support\Facades\DB;

class GeneratorStoreTable
{

    protected string $nodeStoreTable = "CREATE TABLE IF NOT EXISTS `<tableName>` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned NOT NULL DEFAULT '0' COMMENT 'parent id',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'menu name',
  `auth` tinyint unsigned DEFAULT '1' COMMENT 'need to verify permissions status',
  `menu` tinyint unsigned DEFAULT '0' COMMENT 'Whether to display as a menu',
  `sort` float NOT NULL DEFAULT '10000' COMMENT 'Sort Number',
  `module` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Application name, controller root directory, subordinate directory',
  `controller` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Controller name (excluding controller suffix) is case sensitive',
  `action` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Controller Method Name',
  `rule` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Validation rules',
  `param` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Verify additional parameters',
  `icon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Icons displayed in the menu',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'NodeAnnotation remarks',
  `path` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Configure front-end associated routing page',
  `component` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Configure front-end associated component names',
  `code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Custom interface classification',
  `is_deleted` tinyint unsigned DEFAULT '0' COMMENT 'is deleted status',
  `is_ignored` tinyint unsigned DEFAULT '0' COMMENT 'is ignored status',
  `created_at` datetime DEFAULT NULL COMMENT 'create time',
  `updated_at` datetime DEFAULT NULL COMMENT 'update time',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_path` (`module`,`controller`,`action`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `code` (`code`) USING BTREE,
  KEY `rule` (`rule`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='node and Permissions data';";

    protected array $nodeConfig = [];


    function builder(string $table, string $connection): string
    {
        try {
            DB::connection($connection)->update(str_replace('<tableName>', $table, $this->nodeStoreTable));
            return '';
        } catch (\Throwable $throwable) {
            return $throwable->getMessage();
        }
    }

    function nodeById(int $id = 0): NodeModel
    {
        $node = new NodeModel();
        $md = $node->setConnection($this->nodeConfig['connection'])->setTable($this->nodeConfig['table']);
        return $id > 0 ? $md->find($id) : $node;
    }

    function node(string $module, string $controller, string $action): ?NodeModel
    {
        $node = new NodeModel();
        return $node->setConnection($this->nodeConfig['connection'])->setTable($this->nodeConfig['table'])->where(['module' => $module, 'controller' => $controller, 'action' => $action])->first();
    }

    function store(\stdClass $std, string $module, string $controller): bool
    {
        $isTree = !empty($std->virtualNode);
        $action = $std->action ?? ($isTree ? $std->virtualNode : '');
        $parentId = 0;
        if (empty($this->nodeConfig))
            $this->nodeConfig = config('annotation.node');

        if (!empty($std->parent)) {
            $split = explode('/', $std->parent);
            $parent = $this->node($split[0], $split[1], $split[2]);
            if (!$parent)
                throw new AnnotationException("[{$std->name}] The parent node {$std->parent} of node {$module}/{$controller}->{$action} has not been generated", 600);
            $parentId = $parent->id;
        }
        if (empty($action))
            throw new AnnotationException('the node action is not defined | node: ' . json_encode($std), 500);
        $node = $this->node($module, $controller, $action);
        if (!$node) {
            $node = $this->nodeById();
            $node->module = $module;
            $node->controller = $controller;
            $node->action = $action;
            $node->rule = "{$module}/{$controller}/{$action}";
        }
        $node->name = $std->name;
        $node->parent_id = $parentId;
        $node->menu = $std->isMenuNode;
        $node->auth = $std->isAuthNode;
        $node->sort = $std->sort;
        $node->icon = $std->icon;
        $node->remark = $std->remark;
        $node->is_deleted = $std->delete;
        $node->component = $std->component;
        $node->is_ignored = $std->ignore;
        if (!$isTree) {
            $node->code = $std->code->value;
        }
        $dict = $node->getDirty();
        $dict && $node->save();
        return !empty($dict);
    }


}
