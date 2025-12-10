# laravel-annotation_v2 使用指南

> 从PHP8开始已经对[注解（Attribute）](https://www.php.net/releases/8.0/zh) 原生支持了，这有利于我们创建更快更好用的注解工具利器，为我们的编码工作带来更高的效率。之前开发的 [PHP7.x + Laravel5.8.x 注解插件](https://github.com/CrastLin/laravel-annotation) 在leanKu上受到不少朋友的关注，但大部分朋友已经全面切到PHP8+，希望能发布PHP8系列注解插件，虽然现在很多PHPer都转战Golang了，但PHP在我心中依旧占一席之地！所以我依然希望能为PHP开源尽点微薄之力，希望它越来越好，重回巅峰时刻。

- laravel-annotation_v2已实现的模块有：路由、菜单权限、拦截器（包含并发锁、Laravel验证器集成）、依赖注入。支持的注解位置类（Class）、属性（Property）、构造方法（Constructor）、Setter方法（Method）、参数（Parameter），可支持Laravel
  config配置注入和Env环境配置注入。

* [Laravel5.8 + PHP7.x 系列的 laravel-annotation 传送 ](https://github.com/CrastLin/laravel-annotation)
* [Laravel5.8 + PHP7.x 系列的 laravel-annotation使用demo](https://github.com/CrastLin/laravel-annotation-demo)

## 1、环境和安装
### 1.1. 环境要求
- 由于使用了[PHP8.1和枚举特性](https://www.php.net/releases/8.1/en.php)，因此PHP版本最低要求 >= 8.1，推荐版本 >= 8.2.24，框架版本使用的是 Laravel 9.x ([LeanKu Laravel9.x中文文档](https://learnku.com/docs/laravel/9.x))

### 1.2. 安装依赖包
````shell
composer require crastlin/laravel-annotation_v2:v1.2.0
```` 
> Tips: 也可以在composer.json的 require内定义：`"crastlin/laravel-annotation_v2": "^v1.1.9-alpha"`


## 2、初始化配置文件

- 输入以下命令创建注解配置: config/annotation.php

````shell
sudo -u www php artisan annotation:config
````

* 配置项在以下具体功能中会详情说明

* 注意：根据实现的功能的需要，程序会生成缓存文件，以便更快的运行。文件根目录在`config/annotation.php`中的`annotation_path`项，默认目录在根目录的`data`目录，需要创建该目录，并且授权读写权限

## 3、路由模块

### 3.1 路由定义

* 在控制器中使用以下注解，快速创建一条路由

> Tips: 需要在类上配置 Controller 注解，否则将被排除扫描

````php
namespace App\Http\Controllers\Portal;
use \Illuminate\Routing\Controller;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

#[Controller]
class IndexController extends \Illuminate\Routing\Controller
{
     #[Route("index")]
     function home()
     {
        // todo
     }
}
````

- 以上注解会在`/data/routes`目录下生成对应路由文件

````php
Route::controller(App\Http\Controllers\Portal\IndexController::class)->group(function(){
  Route::any('index', 'home')->name('index');
});
````

- Route注解默认请求类型是any类型，可通过设置参数`method`来指定请求类型，例如

````php
namespace App\Http\Controllers\Portal;
use \Illuminate\Routing\Controller;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Route;
use Crastlin\LaravelAnnotation\Enum\Method;

#[Controller]
class IndexController extends \Illuminate\Routing\Controller
{
     #[Route("index", method: Method::POST)]
     function home()
     {
        // todo
     }
}
````

或者使用`PostMapping`

````php

#[Controller]
class IndexController extends \Illuminate\Routing\Controller
{
     #[PostMapping("index")]
     function home()
     {
        // todo
     }
}
````

- 以上限制了只接受post请求，对应生成的路由文件

````php
Route::controller(App\Http\Controllers\Portal\IndexController::class)->group(function(){
  Route::post('index', 'home')->name('index');
});
````

- Route支持的参数有：

-
    - `path`
-
    - 路由目录，可自定义，未配置时，默认以`Controllers`目录下的层级生成，如以上示例，未定义`path`时，地址为：`/Portal/Index/home`


-
    - `method`
-
    - 限制请求类型，如果`POST / GET / REQUEST`等，默认为any类，支持的类型在包目录 `Enum\Method` 枚举类中，可以根据您的需要使用


-
    - `methods`
-
    - 数组类型，定义多个请求类型限制，即定义多个`Method`枚举对象


-
    - `name`
-
    - 定义路由名称


-
    - `where`
-
    - 定义路由条件，可定义验证请求的路由参数，例如：`path`定义了，`/index/{id}`，可配置where: `['id' => '[0-9]+']` ，更多条件路由配置可使用
      `Annotation/Attributes/Route` 目录下的条件（包含`Where`）路由注解

> 更多的路由注解，请查看Router接口的实现类，包括常用的 `PostMapping` / `GetMapping` / `AnyMapping` 等

### 3.2 路由分组

* 使用`Group`注解，实现路由分组

````php
 #[Group("api")]
 #[Controller]
 class IndexController extends \Illuminate\Routing\Controller
{
     #[PostMapping("index")]
     function home()
     {
        // todo
     }
     
     #[PostMapping("article")]
     function news()
     {
        // todo
     }
}
````

- 以上注解生成的路由如下

````php
Route::prefix('api')
 ->controller(App\Http\Controllers\Portal\IndexController::class)->group(function(){
  Route::post('index', 'home')->name('api.home');
  Route::post('article', 'news')->name('api.news');
 });
````

-- 对应的路由地址：`/api/index` 和 `/api/article`

- 使用路由中间件

````php
 #[Group("api", middleware: "checker")]
 #[Controller]
 class IndexController extends \Illuminate\Routing\Controller
{
     #[PostMapping("index")]
     function home()
     {
        // todo
     }
     
     #[PostMapping("article")]
     function news()
     {
        // todo
     }
}

// 在App\Http\Kernel.php中的属性：$routeMiddleware 配置 中间件映射
protected $routeMiddleware = [
  'checker' => \App\Http\Middleware\Checker::class,
];
````

- 生成的路由配置

````php
Route::prefix('api')
->middleware(array (
  0 => 'checker',
))
 ->controller(App\Http\Controllers\Portal\IndexController::class)->group(function(){
  Route::post('index', 'home')->name('api.home');
  Route::post('article', 'news')->name('api.news');
 });
````
> 中间件还可使用 `Middleware` 注解绑定，中间件名称可以直接指定中间件文件地址，多个中间件可以定义成数组，例如
````php
 #[Group("api")]
 #[Middleware([\App\Http\Middleware\Check::class, \App\Http\Middleware\ParamFilter::class])]
 #[Controller]
 class IndexController extends \Illuminate\Routing\Controller
{
     #[PostMapping("index")]
     function home()
     {
        // todo
     }
     
     #[PostMapping("article")]
     function news()
     {
        // todo
     }
}

````

- 绑定域名可配置 `Group` 的参数：`domain` 或者使用 `Domain` 注解

````php
 #[Group("api", middleware: "checker")]
 #[Domain("xxx.com")]
 #[Controller]
 class IndexController extends \Illuminate\Routing\Controller
{
     #[PostMapping("index")]
     function home()
     {
        // todo
     }
     
     #[PostMapping("article")]
     function news()
     {
        // todo
     }
}

````

> Tips: `Group` 支持类注解和方法注解，可以配合 `Domain` / `Middleware` / `Domain` 注解使用    
> 可以在 `config/annotation.php` 的 `route` 配置项中，配置 `root_group` 项，可实现模块化分组

* 根路由分组，定义格式为（默认不分组）

````php
return [
  'route' => [ 
      // .....
      'root_group' => [
         // 用户模块
         'User' => ['prefix' => 'user', 'domain' => 'user.xxx.com', 'middleware' => 'user.check'],          
         // 管理后台
         'Admin' => ['prefix' => 'admin', 'domain' => 'admin.xxx.com', 'middleware' => 'admin.check']
         ], 
         // ... 更多模块
     ],
     // ....
];
````

### 3.3 资源路由

> 资源路由（Restful Api）是常用的接口实现方式，其以固定的请求方式和路由参数组合标准，可快速注册路由，可以使用注解：`ResourceMapping` 和
 `ApiResourceMapping` 生成一组资源路由，实现接口：`Crastlin\LaravelAnnotation\Extra\ResourceInterface`，快速生成对应的方法

- 使用以下注解创建一个资源路由

````php
 #[ResourceMapping("product")]
 #[Controller]
 class IndexController extends \Illuminate\Routing\Controller implements ResourceInterface
{
     function index()
    {

        var_dump("=== is index cate ===");
    }

    function create()
    {
        var_dump("=== is create cate ===");
    }

    function store()
    {
        var_dump("=== is store cate ===");
    }

    function show(int $id)
    {
        var_dump("=== is show cate {$id} ===");
    }

    function edit(int $id)
    {
        var_dump("=== is edit cate {$id} ===");
    }

    function update(int $id)
    {
        var_dump("=== is update cate {$id} ===");
    }

    function destroy(int $id)
    {
        var_dump("=== is destroy cate {$id} ===");
    }
}


````

### 3.4 路由生成

- 路由自动生成开关可以在`config/annotation.php`中修改（`route` > `auto_create_case` ），默认在开发环境自动生成，可以执行以下命令主动生成路由

````shell
sudo -u www php artisan annotation:route 
````

> Tips: 建议在生产环境配置hook，每次发版完成后，自动更新路由

## 4、菜单权限

> 在开发后台时，经常会需要使用到功能菜单和角色权限分配的功能，使用注解的好处在于，开发时定义好菜单树和权限节点信息，无需在数据库繁琐添加，只需要使用生成命令，快速将注解的菜单树和权限节点保存到数据库，方便环境切换和移植，为开发者整理菜单节约宝贵的时间。

### 4.1 配置第一个节点

- 使用 `Tree` 配置一个根节点，并使用 `Node` 配置一个子节点

````php
#[Tree("首页")]
#[Controller]
class IndexController
{

     #[Node("数据")]
     #[PostMapping("data_list")]
     function dataList()
     {
        // todo
     }
     
     #[Node("新闻")]
     #[PostMapping("articles")]
     function newsList()
     {
        // todo
     } 
}
````

- 使用命令行生成节点

````shell
sudo -u www php artisan annotation:node
````

- 如果是首次生成节点时，则需要先执行以下命令，在数据库生成`node`表，用于保存节点数据

````shell
sudo -u www php artisan annotation:node_store
````

生成对应的节点如下：

- 首页
-
    - 数据
-
    - 新闻
-
    - ...

> Tips: 每个控制器可配置成一个根节点，控制器内的所有方法默认都是该根节点的子节点。

### 4.2 配置父节点

- 可以使用`parent`参数自定义父节点

````php
#[Tree("首页")]
#[Controller]
class IndexController extends \Illuminate\Routing\Controller
{

     #[Node("数据")]
     #[PostMapping("data")]
     function dataList()
     {
        // todo
     }
     
     #[Node(name: "数据详情", parent: "dataList")]
     #[PostMapping]
     function detail()
     {
      // todo
     }
     
     #[Node("新闻")]
     #[PostMapping("articles")]
     function newsList()
     {
        // todo
     } 
     
     #[Node("新闻详情", parent: "newsList")]
     #[PostMapping("article")]
     function news()
     {
        // todo
     } 
}
````

生成对应的节点如下：

- 首页
-
    - 数据
-
    -
        - 数据详情
-
    - 新闻
-
    -
        - 新闻详情
-
    - ...


### 4.3 参数说明

- ====== 通用（`Tree` / `Node`）注解参数 ======


- `name`
- 节点名称，用权限于节点名称配置显示、前端菜单名称显示


- `sort`
- 菜单排序，存在多个根（相对）节点时，设置该参数排序，默认按生成顺序排序


- `isMenuNode`
- 是否设置为菜单，用于前端菜单显示配置，Tree注解默认为菜单节点，Node注解则默认隐藏菜单。


- `isAuthNode`
- 是否需要验证权限，用于角色权限验证配置，Tree注解默认不验证，Node注解默认需要鉴权。


- `preNamedSubMethods`
- 定义需要前置名称的方法名，多个用逗号分隔。如果控制器为多态实现，方法在父类中定义时，如果需要在父类方法增加前置名称（子类的`Tree`注解定义），则需在子类`Tree`注解定义该参数。


- `virtualNode`
- 定义根节点虚拟节点方法名称，默认为：`defaultPage`


- `checkMode`
- 检测模式，配置强验证模式时（`NodeMode::STRICT_MODE`）生成节点时，检查当前控制器是否存在未配置`Node`注解的方法，否则抛出
  `AnnotationException` 异常错误


- ====== 以下是 `Node` 注解参数 ======


- `parent`
- 定义父节点名称，默认为当前控制器的 `virtualNode` 节点，如果父节点不在当前控制器，则`parent`配置控制器目录 `{controller}/{action}`，例如：`parent: "User/index"`，如果是跨模块。则需配置 `{prefix}/{controller}/{action}`, 例如：`parent: "portal/User/index"`。


- `code`
- 定义节点分类，用于日志、按钮级权限配置，`code`是一个`Enum`类型，可定义类型在 `Crastlin\LaravelAnnotation\Enum\NodeCode` 。


- `icon`
- 定义前端主菜单图标，一般为前端样式名称，或图标url地址


- `remark`
- 节点备注，用于角色授权时，节点显示


- `ignore`
- 忽略该节点，配置为 `true` 时，则该节点跳过生成


- `delete`
- 删除该节点，用于废弃的方法，删除权限节点


- `component`
- 组件名称，用于前端Vue项目中组件页面的引用


> Tips: 执行生成节点后，修改方法名将会产生无用的节点数据，无法删除，建议方法确定好后，再执行命令生成，否则只能清空 node 表中的数据，重新生成。

### 4.4 多态控制器应用
````php
 #[Tree("动物园")]
 abstract class Animal extends \Illuminate\Routing\Controller
 {
   #[Node("主页", isMenuNode: true)] 
   #[PostMapping] 
   function index()
   {
     // todo
   }
   
   #[Node("观看时间", isMenuNode: true)]
   #[PostMapping]
   function schedule()
   {
     // todo
   }
 }
 
 #[Tree("长颈鹿", preNamedSubMethods: "index,schedule")]
 #[Controller]
 class GiraffeController extends Animal
 {

 }

 #[Tree("老虎", preNamedSubMethods: "index,schedule")]
 #[Controller]
 class TigerController extends Animal
 {

 }
````
- 以上两个方法对应生成节点名称： 长颈鹿主页，长颈鹿观看时间，老虎主页，老虎观看时间

## 5、拦截器
### 5.1 请求并发锁
> 经常会遇到这样的场景，当同一个请求（公共级 / 用户级 / 项目级等）需要限制并发，可以使用`SyncLock`，注解控制器方法，达到限制并发的效果，`SyncLock` 采用的是 redis 的 set 实现方式，redis配置默认使用的Laravel `env`配置项，可自行在生成的 `config/annotation.php` 中配置。
#### 5.1.1 使用 `SyncLock` 注解
````php
#[Tree("首页")]
#[Controller]
class IndexController extends \Illuminate\Routing\Controller
{

     #[Node("数据")]
     #[PostMapping("data_list")]
     function dataList()
     {
        // todo
     }
     
     #[Node("新闻")]
     #[PostMapping("articles")]
     function newsList()
     {
        // todo
     } 
     
     #[SyncLock(suffix: "{post.id}")]
     #[Node("更新", parent: "newsList")]
     #[PostMapping]
     function updateNews()
     {
        // todo
     }
}
````
- 以上的 `updateNews` 方法，当并发相同 `id` 请求时，则会响应正在更新中的提示

#### 5.1.2 可用参数说明

- `expire`
- 加锁有效期，超过有效期后，将自动解除该锁，当请求内有异常未处理，或者配置了 `once: true`时，会在一个加锁周期后解除。


- `name`
- 锁名称，不配置时，默认为当前`{模块名}_{控制器名}_{方法名}`


- `once`
- 定义限制在锁有效期内只能请求一次，请求完不会自动释放锁


- `code`
- 锁定被阻断访问时返回的响应code码，在 `Extra/ResponseCode` 枚举中已定义了常用的代码，可自定义枚举类并实现接口 `Extra/ResponseCodeEnum` 。


- `msg`
- 锁定被阻断访问时返回的响应错误信息。


- `response`
- 自定义响应数据格式，数组类型，以`json`格式响应。


- `prefix`
- 自定义锁前置key


- `suffix`
- 自定义锁名后缀，可通过花括号取请求变量，例如使用 post请求的id 为后缀，则使用 `{post.id}`，已支持的请求类型有：`input` / `get` / `post` / `header` / `query` / `date`


- `suffixes`
- 组合后缀定义，可配置多个后缀名或请求变量。

> 如果需要按登录身份进行并发限制，则可以使用 `SyncLockByToken` `suffix`参数可通过参数或配置文件 `config/annotation.php` 中的`interceptor` -> `lock` -> `token` 配置，默认配置为： `{header.token}` 


### 5.2 验证器
#### 5.2.1. 使用`Validation`注解快速开启一个验证器
````php
#[Controller]
class IndexController extends \Illuminate\Routing\Controller
{

    #[Validation(field: "message",rule: "required", attribute: "留言", message: ":attribute不能为空")]
    #[PostMapping("submitMsg")]
    function saveMessage()
    {
       // todo
    }
}
````
- 以上注解等同于以下代码
```php
namespace App\Http\Controllers\Portal;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
 #[Controller]
 class IndexController extends \Illuminate\Routing\Controller
 { 
    #[PostMapping("submitMsg")]
    function saveMessage(Request $request)
    {
       $validator = Validator::make($request->all(), [
         'message' => 'required',
       ], [
         'message.required' => ':attribute不能为空',
       ],[
         'message' => '留言',
       ]);
       if($validator->fails())
         return ['code' => 500, 'msg' => $validator->errors()->first()];
         
         // todo
    }
}
```
#### 5.2.2. 创建多条件验证
````php
#[Controller]
class IndexController extends \Illuminate\Routing\Controller
{

    #[Validation(rules: [
        "username" => ["required", "alpha_dash"],
        "message" => ["required"]
    ], messages: [
        'username.required' => ":attribute不能为空",
        'username.alpha_dash' => ":attribute只能是大小写字母、数字或下划线",
        'message.required' => ":attribute不能为空",
    ], attributes: [
        "username" => "用户名",
        "message" => "留言",
    ])
    ]
    #[PostMapping("submitMsg")]
    function saveMessage()
    {
       // todo
    }
}
````
#### 5.2.3. 创建验证器类
> 创建验证类需要继承基类`Crastlin\LaravelAnnotation\Extra\Validate`，该验证基类已实现了很多常用的方法，以及增加了`callback`验证器，可实现自定义方法实现验证
- 在`app`目录下创建验证器目录`Validate`，并创建`MessageCheck`类

```php
namespace App\Validate;
use Crastlin\LaravelAnnotation\Extra\Validate;
class MessageCheck extends Validate
{
   protected array $rules = [
        "username" => ["required", "alpha_dash"],
        "message" => 'required|callback:checkSafeTags',
       ], $attributes = [
        "username" => "用户名",
        "message" => "留言",
       ];
       
   protected function checkSafeTags() :bool
   {
      if(str_contains($this->data['message'],'<script'))
        return false;
      return true;  
   }
}
```
> `Validate`验证基类集成了大部分错误提示消息模板，可以省去每次定义
- 在控制器中定义`Validation`注解，并设置参数：`class` 为 `\App\Validate\MessageCheck::class`
````php
namespace App\Http\Controllers\Portal;
use Crastlin\LaravelAnnotation\Extra\Validate;
#[Controller]
class IndexController extends \Illuminate\Routing\Controller
{

    #[Validation(class: \App\Validate\MessageCheck::class)]
    #[PostMapping("submitMsg")]
    function saveMessage()
    {
       // todo
    }
}
````
- 以上注解等同于
```php
namespace App\Http\Controllers\Portal;
use Crastlin\LaravelAnnotation\Extra\Validate;
#[Controller]
class IndexController extends \Illuminate\Routing\Controller
{
    #[PostMapping("submitMsg")]
    function saveMessage(Request $request)
    {
       $validate = Validate::make(\App\Validate\MessageCheck::class, $request->all());
       if($validate->fails())
         return ['code' => 500, 'msg' => $validate->errors()->first()];
         
         // todo
    }
}
```
- `Crastlin\LaravelAnnotation\Extra\Validate`使用`匿名类`扩展实现验证示例
```php
namespace App\Http\Controllers\Portal;
use Crastlin\LaravelAnnotation\Extra\Validate;
#[Controller]
class IndexController extends \Illuminate\Routing\Controller
{
    #[PostMapping("submitMsg")]
    function saveMessage(Request $request)
    {
       $validate = new class extends Validate{
          protected array $rules = [
            "username" => ["required", "alpha_dash"],
            "message" => 'required|callback:checkSafeTags',
           ], $attributes = [
            "username" => "用户名",
            "message" => "留言",
           ];
          protected function checkSafeTags() :bool
          {
            if(str_contains($this->data['message'],'<script'))
              return false;
            return true;  
          }
       };
       $validator = $validate->setData($request->all())->validate();
       if($validator->fails())
         return ['code' => 500, 'msg' => $validator->errors()->first()];
         
         // todo 
    }
}
```

#### 5.2.4 使用独立验证器注解
- 注解类位于`Crastlin\LaravelAnnotation\Attributes\Validation`目录下
```php
namespace App\Http\Controllers\Portal;

use Crastlin\LaravelAnnotation\Attributes\Validation\Required;
use Crastlin\LaravelAnnotation\Attributes\Validation\Regex;
#[Controller]
class IndexController extends \Illuminate\Routing\Controller
{

    #[Required("username", attribute: "用户名")]
    #[Regex("username", ruleValue: "~^\w+$~", attribute: "用户名")]
    #[Required("message", attribute: "留言")]
    #[PostMapping("submitMsg")]
    function saveMessage()
    {
       // todo
    }
}
```
- 以上注解实现等同于以下代码
```php
namespace App\Http\Controllers\Portal;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
 #[Controller]
 class IndexController extends \Illuminate\Routing\Controller
 { 
    #[PostMapping("submitMsg")]
    function saveMessage(Request $request)
    {
       $validator = Validator::make($request->all(), [
         'username' => ['required', 'regex:~^\w+$~'],
         'message' => 'required',
       ], [
         'required' => ':attribute不能为空',
         'username.regex' => ':attribute格式不正确',
       ],[
         'username' => '用户名',
         'message' => '留言',
       ]);
       if($validator->fails())
         return ['code' => 500, 'msg' => $validator->errors()->first()];
         
         // todo
    }
}
```
#### 5.3. 参数验证
- 不同于方法验证器注解，参数验证注解依赖`Crastlin\LaravelAnnotation\Extra\BaseController`类中的`InvokeTrait`，包括后续要讲到的依赖注入功能以及自定义类验证、依赖注入的实现。

```php
namespace App\Http\Controllers\Portal;
 
use Crastlin\LaravelAnnotation\Annotation\Attributes\Validation\Between;
use Crastlin\LaravelAnnotation\Extra\BaseController;
 #[Controller]
 class IndexController extends BaseController
 { 
    #[PostMapping("submitMsg/{id}")]
    function saveMessage(#[Between(ruleValue: "1,2")] int $id)
    {
         // todo
    }
}
```
- 以上注解就实现了请求的`id`长度必须为1到2位的验证
> Tips: 参数验证器和方法验证器一样，可以定义多个同时生效

## 6、依赖注入

### 6.1. 实现依赖注入（包括：属性注入、构造方法注入、setter方法注入、参数注入）有以下三种方式：
 1. 控制器类需要继承`Crastlin\LaravelAnnotation\Extra\BaseController`
 2. 自定义类需要使用`InvokeTrait`，并使用类调用方式实现中间方法转发调用，（即魔术方法`__invoke`的使用）
 3. 接口方式注入的实现需要定义`Service`接口层和`Impl`类实现层，通过匿名代理类，实现懒加载的效果，性能更强（推荐使用）

> **Tips: `InvokeTrait`中已经定义好常用的：请求数据`data`，响应码`resCode`，错误信息`errText`、 返回数据`result`，以及对应的get方法，可以使用统一方法返回值（bool）和类响应标准，有利于开发效率和标准化，减少BUG，强烈推荐使用！**

#### 6.1.1. 控制器类依赖注入，以下示例实现了`环境配置注入`、`配置注入`、`请求参数注入`
```php
namespace App\Http\Controllers\Portal;
 
use Crastlin\LaravelAnnotation\Annotation\Attributes\Env;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Value;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Input\All; 
use Crastlin\LaravelAnnotation\Extra\BaseController;
 #[Controller]
 class IndexController extends BaseController
 { 
    // 环境配置注入
    #[Env("APP_ENV")]
    protected string env;
    
    // config注入
    #[Value("app.debug")]
    protected bool $debug;
    
    // 请求参数注入，还可以使用Input/Get/Post/Query等
    #[All]
    protected array $params;
 
    #[PostMapping("submitMsg/{id}")]
    function saveMessage()
    {
         // todo
    }
}
```
#### 6.1.2. 自定义注入
- 首先在上文绑定一个数据到容器，以下示例为中间件绑定
```php
namespace App\Http\Middleware;

use Crastlin\LaravelAnnotation\Facades\Injection;
use Closure;
use Illuminate\Http\Request;

class TokenCheck
{

    function handle(Request $request, Closure $next)
    {
       // var_dump('===== is Token Middleware ====');
        Injection::bind('params', $request->all());
        Injection::bind('header.token', $request->header("token"));
 
        return $next($request);
    }
}
```
- 然后在控制器中使用`Autowired`或带参数的`Inject`注解注入

```php
namespace App\Http\Controllers\Portal;
  
use Crastlin\LaravelAnnotation\Annotation\Attributes\Autowired;
use Crastlin\LaravelAnnotation\Extra\BaseController;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Inject;
 #[Controller]
 class IndexController extends BaseController
 {  
    #[Autowired]
    protected array $params;
    
    #[Inject("header.token")]
    protected string $token
    
    #[PostMapping("submitMsg/{id}")]
    function saveMessage()
    {
         // todo
    }
}
```

#### 6.1.3. 参数注入
```php
namespace App\Http\Controllers\Portal;
  
use Crastlin\LaravelAnnotation\Annotation\Attributes\Autowired;
use Crastlin\LaravelAnnotation\Extra\BaseController;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Inject;
 #[Controller]
 class IndexController extends BaseController
 {   
    
    #[PostMapping("submitMsg/{id}")]
    function saveMessage(#[Autowired] array $params, #[Inject("header.token")] string $token)
    {
         // todo
    }
}
```
#### 6.1.4. 自定义类依赖注入

- 中间件中获取到用户数据对象
```php
namespace App\Http\Middleware;

use Crastlin\LaravelAnnotation\Facades\Injection;
use Closure;
use Illuminate\Http\Request;

class TokenCheck
{

    function handle(Request $request, Closure $next)
    {
       // var_dump('===== is Token Middleware ====');
        Injection::bind('params', $request->all());
        $token = $request->header("token");
        Injection::bind('header.token', $token);
        $userId = TokenAuth::getUserId($token);
        $user = User::find($userId);
        if(!$user)
         return response()->json(['code' => 400, 'msg' => '用户不存在']);
         
        Injection::bind('model.user', $user);
 
        return $next($request);
    }
}
```
- 创建一个自定义类`app/Service/UserService.php`，并 use `Crastlin\LaravelAnnotation\Utils\Traits\InvokeTrait`

```php
 namespace App\Service;
 
 use Crastlin\LaravelAnnotation\Annotation\Attributes\Autowired;
 use Crastlin\LaravelAnnotation\Annotation\Attributes\Inject;
 use Crastlin\LaravelAnnotation\Extra\ResponseCode;
 use Crastlin\LaravelAnnotation\Utils\Traits\SingletonTrait;
 use Crastlin\LaravelAnnotation\Utils\Traits\InvokeTrait;
 use App\Model\User;
 class UserService
 {
   use SingletonTrait, InvokeTrait;
   
   #[Inject("model.user")]
   protected ?User $user;
   
   function updateAvatar(#[Autowired] string $avatar): bool
   {
      var_dump($this->user);
      $this->user->avatar = $avatar;
      $this->user->save();
      
      $this->resCode = ResponseCode::SUCCESS;
      return true;
   }
 }
```

- 在控制器中调用

```php
namespace App\Http\Controllers\Portal;
  
use Crastlin\LaravelAnnotation\Annotation\Attributes\Autowired;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Validation\Numeric;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Validation\Required;
use Crastlin\LaravelAnnotation\Extra\BaseController;
use Crastlin\LaravelAnnotation\Facades\Injection;
use App\Service\UserService;
 #[Controller]
 class UserController extends BaseController
 {   
 
    #[Autowired]
    protected array $params;
    
    
    #[Required("user_id")]
    #[Numeric("user_id")]
    #[PostMapping("updateAvatar")]
    function updateAvatar()
    {  
       return $this->callService(UserService::class, 'updateAvatar');
    }
}
```
> 以上示例中，`updateAvatar`方法参数`$avatar`注入规则：自定义绑定数据 > 请求参数（form 或 json）`avatar`字段

### 6.2. 接口依赖注入
> 使用接口依赖注入前先要定义好`Service`层和`Impl`实现层
- 先定义好一个Service层接口 `app/Service/UserService.php`

```php
namespace App\Service;

use Crastlin\LaravelAnnotation\Extra\BaseService;
/**
 * @mixin App\Service\Impl\User
 */
interface UserService extends BaseService
{
   function updateAvatar(string $avatar = ''): bool;
    
}
```
- 再创建一个实现UserService的类 `app/Service/Impl/User.php`，并且在该实现类标记`Service`注解

```php
namespace App\Service\Impl;

use App\Service\UserService;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Autowired;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Service;
use Crastlin\LaravelAnnotation\Extra\BaseImplement;
#[Service]
class User extends BaseImplement implements UserService
{

    #[Inject("model.user")]
    protected ?User $user;

    function updateAvatar(#[Autowired] string $avatar = ''): bool
    {
      var_dump($this->user);
      $this->user->avatar = $avatar;
      $this->user->save();
      
      $this->resCode = ResponseCode::SUCCESS;
      return true;
    }
   
    function myOrders(): bool
    {
        // todo
        return true;
    }
}
```

- 然后在控制器层调用
```php
namespace App\Http\Controllers\Portal;
  
use Crastlin\LaravelAnnotation\Annotation\Attributes\Autowired;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Validation\Numeric;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Validation\Required;
use Crastlin\LaravelAnnotation\Extra\BaseController;
use Crastlin\LaravelAnnotation\Facades\Injection;
use App\Service\UserService;
 #[Controller]
 class UserController extends BaseController
 {   
 
    #[Autowired]
    protected UserService $userService;
    
    
    #[Required("user_id")]
    #[Numeric("user_id")]
    #[PostMapping("updateAvatar")]
    function updateAvatar()
    {  
       if(!$this->userService->updateAvatar()){
          var_dump($this->userService->getError());
          var_dump($this->userService->getResCode());
       }else{
         var_dump($this->userService->getResult());  
       }
    }
}
```
- 如果需要指定实现类，则可以使用`Qualifier`注解指定

```php
namespace App\Http\Controllers\Portal;
  
use Crastlin\LaravelAnnotation\Annotation\Attributes\Autowired;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Qualifier;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Validation\Numeric;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Validation\Required;
use Crastlin\LaravelAnnotation\Extra\BaseController;
use Crastlin\LaravelAnnotation\Facades\Injection;
use App\Service\UserService;
 #[Controller]
 class UserController extends BaseController
 {   
 
    #[Autowired]
    #[Qualifier(\App\Service\Impl\User::class)]
    protected UserService $userService;
    
    
    #[Required("user_id")]
    #[Numeric("user_id")]
    #[PostMapping("updateAvatar")]
    function updateAvatar()
    {  
       if(!$this->userService->updateAvatar()){
          var_dump($this->userService->getError());
          var_dump($this->userService->getResCode());
       }else{
         var_dump($this->userService->getResult());  
       }
    }
}
```
> Tips 使用接口类型注入只能使用 `Autowired`注解，且实现类必须标记`Service`注解，否则会被排除。使用`Qualifier`指定实现类可以是类名或类命名空间地址。如果非工厂模式，Service层可以不定义任何方法，只需在头部增加 `@mixin App\Service\Impl\User` 则Idea会自动映射方法提示

## 7、代码贡献

#### [Crastlin博客主页](https://learnku.com/blog/Crastlin) crastlin@163.com

## 8、使用必读

- ___使用此插件请遵守法律法规，请勿在非法和违法应用中使用，产生的一切后果和法律责任均与作者无关！___
