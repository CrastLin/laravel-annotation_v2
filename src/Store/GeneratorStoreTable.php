<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Store;

use Crastlin\LaravelAnnotation\Annotation\AnnotationException;
use Illuminate\Support\Facades\DB;

class GeneratorStoreTable
{

    static string $generator = "CREATE TABLE IF NOT EXISTS `<tableName>` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'parent id',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'menu name',
  `auth` tinyint(1) unsigned NULL DEFAULT '1' COMMENT 'need to verify permissions status',
  `menu` tinyint(1) unsigned NULL DEFAULT '0' COMMENT 'Whether to display as a menu',
  `sort` float NOT NULL DEFAULT '10000' COMMENT 'Sort Number',
  `module` varchar(40) NOT NULL DEFAULT '' COMMENT 'Application name, controller root directory, subordinate directory',
  `controller` varchar(30) NOT NULL DEFAULT '' COMMENT 'Controller name (excluding controller suffix) is case sensitive',
  `action` varchar(30) NOT NULL DEFAULT '' COMMENT 'Controller Method Name',
  `rule` varchar(100) DEFAULT NULL COMMENT 'Validation rules',
  `param` varchar(50) NOT NULL DEFAULT '' COMMENT 'Verify additional parameters',
  `icon` varchar(20) NOT NULL DEFAULT '' COMMENT 'Icons displayed in the menu',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'NodeAnnotation remarks',
  `path` varchar(50) DEFAULT NULL COMMENT 'Configure front-end associated routing page',
  `component` varchar(50) DEFAULT NULL COMMENT 'Configure front-end associated component names',
  `code` varchar(30) DEFAULT NULL COMMENT 'Custom interface classification',
  `is_deleted` tinyint(1) unsigned NULL DEFAULT '0' COMMENT 'is deleted status',
  `is_ignored` tinyint(1) unsigned NULL DEFAULT '0' COMMENT 'is ignored status',
  `created_at` datetime DEFAULT NULL COMMENT 'create time',
  `updated_at` datetime DEFAULT NULL COMMENT 'update time',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_path` (`module`,`controller`,`action`) USING BTREE,
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `code` (`code`) USING BTREE,
  KEY `rule` (`rule`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='node and Permissions data';";


    static function builder(string $table, string $connection): string
    {
        try {
            DB::connection($connection)->update(str_replace('<tableName>', $table, self::$generator));
            return '';
        } catch (\Throwable $throwable) {
            return $throwable->getMessage();
        }
    }

    static function nodeById(int $id = 0): NodeModel
    {
        return $id > 0 ? NodeModel::find($id) : new NodeModel();
    }

    static function node(string $module, string $controller, string $action): ?NodeModel
    {
        return NodeModel::where(['module' => $module, 'controller' => $controller, 'action' => $action])->first();
    }

    static function store(\stdClass $std, string $module, string $controller): void
    {
        $isTree = !empty($std->virtualNode);
        $action = $std->action ?? ($isTree ? $std->virtualNode : '');
        $parentId = 0;
        if (!empty($std->parent)) {
            $split = explode('/', $std->parent);
            $parent = self::node($split[0], $split[1], $split[2]);
            if (!$parent)
                throw new AnnotationException("[{$std->name}] The parent node {$std->parent} of node {$module}/{$controller}->{$action} has not been generated", 600);
            $parentId = $parent->id;
        }
        if (empty($action))
            throw new AnnotationException('the node action is not defined | node: ' . json_encode($std), 500);
        $node = self::node($module, $controller, $action);
        if (!$node) {
            $node = self::nodeById();
            $node->module = $module;
            $node->controller = $controller;
            $node->action = $action;
        }
        $node->name = $std->name;
        $node->parent_id = $parentId;
        $node->menu = $std->isMenuNode;
        $node->auth = $std->isAuthNode;
        $node->sort = $std->sort;
        if (!$isTree) {
            $node->code = $std->code->value;
            $node->icon = $std->icon;
            $node->remark = $std->remark;
            $node->is_deleted = $std->delete;
            $node->component = $std->component;
            $node->is_ignored = $std->ignore;
        }
        $node->save();
    }


}
