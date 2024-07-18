<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Store;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @method static \Illuminate\Database\Eloquent\Builder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder query()
 * /
 * @property int id
 * @property int parent_id
 * @property string name
 * @property bool auth
 * @property bool menu
 * @property int sort
 * @property string module
 * @property string controller
 * @property string action
 * @property string rule
 * @property string param
 * @property string icon
 * @property string remark
 * @property string path
 * @property string component
 * @property string code
 * @property bool is_deleted
 * @property bool is_ignored
 */
class NodeModel extends Model
{
    protected $table = 'node';
    protected $casts = ['id' => 'int', 'parent_id' => 'int', 'auth' => 'bool', 'menu' => 'bool', 'sort' => 'int', 'is_deleted' => 'bool', 'is_ignored' => 'bool'];
}
