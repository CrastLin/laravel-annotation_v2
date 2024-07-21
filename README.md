# laravel-annotation_v2 使用指南

*** 
> 从PHP8开始已经对[注解（Attribute）](https://www.php.net/releases/8.0/zh) 原生支持了，这有利于我们创建更快更好用的注解工具利器，为我们的编码工作带来更高的效率。之前开发的 [PHP7.x + Laravel5.8.x 注解插件](https://github.com/CrastLin/laravel-annotation) 在leanKu上受到不少朋友的关注，但大部分朋友已经全面切到PHP8+，希望能发布PHP8系列注解插件，虽然现在很多PHPer都转战Golang了，但PHP在我心中依旧占一席之地！所以我依然希望能为PHP开源尽点微薄之力，希望它越来越好，重回巅峰时刻。

- laravel-annotation_v2已实现的模块有：路由、菜单权限、拦截器（包含并发锁、Laravel验证器集成）、依赖注入。支持的注解位置类（Class）、属性（Property）、构造函数（Constructor）、方法（Method）、参数（Parameter），可支持Laravel
  config配置注入和Env环境配置注入。使用框架版本：Laravel 9.x ([LeanKu Laravel9.x中文文档](https://learnku.com/docs/laravel/9.x))

* [Laravel5.8 + PHP7.x 系列的 laravel-annotation 传送 ](https://github.com/CrastLin/laravel-annotation)
* [Laravel5.8 + PHP7.x 系列的 laravel-annotation使用demo](https://github.com/CrastLin/laravel-annotation-demo)

## 1、安装

#### 由于使用了[PHP8.1枚举](https://www.php.net/releases/8.1/en.php)，因此PHP版本要求 >= 8.1，在laravel根目录下，执行以下命令

````shell
composer require crastlin/laravel-annotation_v2:v1.0.6-alpha
````

> Tips: 也可以在composer.json的 require内定义："crastlin/laravel-annotation_v2": "v1.0.6-alpha"

## 2、初始化配置文件

#### 输入以下命令创建注解配置: config/annotation.php

````shell
sudo -u www php artisan annotation:config
````

* 配置项在以下具体功能中会详情说明

* <b style="color:#f60">
  注意：根据实现的功能的需要，程序会生成缓存文件，以便更快的运行。文件根目录在config/annotation.php中的annotation_path项，默认目录在根目录的data目录，需要创建该目录，并且授权读写权限</b>

## 3、路由模块

### 3.1 路由定义

* 在控制器中使用以下注解，快速创建一条路由

> Tips: 需要在类上配置 Controller 注解，否则将被排除扫描

````php
namespace App\Http\Controllers\Portal;
use \Illuminate\Routing\Controller;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

#[Controller]
class IndexController extends Controller
{
     #[Route("index")]
     function home()
     {
        // todo
     }
}
````

- 以上注解会在/data/routes目录下生成对应路由文件

````php
Route::controller(App\Http\Controllers\Portal\IndexController::class)->group(function(){
  Route::any('index', 'home')->name('index');
});
````

- Route注解默认请求类型是any类型，可通过设置参数method来指定请求类型，例如

````php
namespace App\Http\Controllers\Portal;
use \Illuminate\Routing\Controller;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Route;
use Crastlin\LaravelAnnotation\Enum\Method;

#[Controller]
class IndexController extends Controller
{
     #[Route("index", method: Method::POST)]
     function home()
     {
        // todo
     }
}
````

或者使用PostMapping

````php

#[Controller]
class IndexController extends Controller
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
    - ***path***
-
    - 路由目录，可自定义，未配置时，默认以Controllers目录下的层级生成，如以上示例，未定义path时，地址为：/Portal/Index/home


-
    - ***method***
-
    - 限制请求类型，如果POST / GET / REQUEST等，默认为any类，支持的类型在包目录 <b>Enum\Method</b> 枚举类中，可以根据您的需要使用


-
    - ***methods***
-
    - 数组类型，定义多个请求类型限制，即定义多个Method枚举对象


-
    - ***name***
-
    - 定义路由名称


-
    - ***where***
-
    - 定义路由条件，可定义验证请求的路由参数，例如：path定义了，/index/{id}，可配置where: ['id' => '[0-9]+'] ，更多条件路由配置可使用
      Annotation/Attributes/Route 目录下的条件（包含Where）路由注解

> 更多的路由注解，请查看Router接口的实现类，包括常用的PostMapping / GetMapping / AnyMapping等

### 3.2 路由分组

* 使用Group注解，实现路由分组

````php
 #[Group("api")]
 #[Controller]
 class IndexController extends Controller
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

-- 对应的路由地址：<b class="tag">/api/index</b> 和 <b class="tag">/api/article</b>

- 使用路由中间件

````php
 #[Group("api", middleware: "checker")]
 #[Controller]
 class IndexController extends Controller
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

// 在App\Http\Kernel中的属性：$routeMiddleware 配置 中间件映射
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
> 中间件还可使用 Middleware 注解绑定，中间件名称可以直接指定中间件文件地址，多个中间件可以定义成数组，例如
````php
 #[Group("api")]
 #[Middleware([\App\Http\Middleware\Check::class, \App\Http\Middleware\ParamFilter::class])]
 #[Controller]
 class IndexController extends Controller
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

// 在App\Http\Kernel中的属性：$routeMiddleware 配置 中间件映射
protected $routeMiddleware = [
  'checker' => \App\Http\Middleware\Checker::class,
];
````

- 绑定域名可配置domain 或者使用Domain注解

````php
 #[Group("api", middleware: "checker")]
 #[Domain("xxx.com")]
 #[Controller]
 class IndexController extends Controller
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

> Tips: Group支持类注解和方法注解，可以配合Domain / Middleware / Domain 注解使用    
> 可以在 config/annotation.php 的 route 配置项中，配置 root_group 项，可实现模块化分组

* 根路由分组，默认不分组，定义格式为

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

> 资源路由不常用的接口实现方式，其以固定的请求方式和路由参数组合标准，可快速注册路由，可以使用注解：ResourceMapping 和
> ApiResourceMapping 生成一组资源路由，实现接口：Crastlin\LaravelAnnotation\Annotation\Attributes\ResourceInterface，快速生成对应的方法

- 使用以下注解创建一个资源路由

````php
 #[ResourceMapping("product")]
 #[Controller]
 class IndexController extends Controller implements ResourceInterface
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

- 路由配置自动生成开关可以在配置中修改（route > auto_create_case ），默认在开发环境路由自动生成，可以执行以下命令主动生成路由

````shell
sudo -u www php artisan annotation:route 
````

> Tips: 建议在生产环境配置hook，每次发版完成后，自动更新路由

## 4、菜单权限

>
在开发后台时，经常会需要使用到功能菜单和角色权限分配的功能，使用注解的好处在于，开发时定义好菜单树和权限节点信息，无需在数据库繁琐添加，只需要使用生成命令，快速将注解的菜单树和权限节点保存到数据库，方便环境切换和移植，为开发者整理菜单节约宝贵的时间。

### 4.1 配置第一个节点

- 使用 <b>Tree</b> 配置一个根节点，并使用 <b>Node</b> 配置一个子节点

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

- 如果是首次生成节点时，则需要先执行以下命令，在数据生成node表，用于保存节点数据

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

- 可以使用parent参数自定义父节点

````php
#[Tree("首页")]
#[Controller]
class IndexController
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

- ====== 通用（Tree / Node）注解参数 ======


- ***name***
- 节点名称，用权限于节点名称配置显示、前端菜单名称显示


- ***sort***
- 菜单排序，存在多个根（相对）节点时，设置该参数排序，默认按生成顺序排序


- ***isMenuNode***
- 是否设置为菜单，用于前端菜单显示配置，Tree注解默认为菜单节点，Node注解则默认隐藏菜单。


- ***isAuthNode***
- 是否需要验证权限，用于角色权限验证配置，Tree注解默认不验证，Node注解默认需要鉴权。


- ***preNamedSubMethods***
- 定义需要前置名称的方法名，多个用逗号分隔。如果控制器为多态实现，方法在父类中定义时，如果需要在父类方法增加前置名称（子类的Tree注解定义），则需在子类Tree注解定义该参数。


- ***virtualNode***
- 定义根节点虚拟节点方法名称，默认为：defaultPage


- ***checkMode***
- 检测模式，配置强验证模式时（NodeMode::STRICT_MODE）生成节点时，检查当前控制器是否存在未配置Node注解的方法，否则抛出
  AnnotationException 异常错误


- ====== 以下是 Node 注解参数 ======


- ***parent***
- 定义父节点名称，默认为当前控制器的 virtualNode 节点，如果父节点不在当前控制器，则parent配置控制器目录 {controller}/{action}，例如：parent: "User/index"，如果是跨模块。则需配置 {prefix}/{controller}/{action}, 例如：parent: "portal/User/index"。


- ***code***
- 定义节点分类，用于日志、按钮级权限配置，code是一个Enum类型，可定义类型在 Crastlin\LaravelAnnotation\Enum\NodeCode 。


- ***icon***
- 定义前端主菜单图标，一般为前端样式名称，或图标url地址


- ***remark***
- 节点备注，用于角色授权时，节点显示


- ***ignore***
- 忽略该节点，配置为 true 时，则该节点跳过生成


- ***delete***
- 删除该节点，用于废弃的方法，删除权限节点


- ***component***
- 组件名称，用于前端Vue项目中组件页面的引用


> Tips: 执行生成节点后，修改方法名将会产生无用的节点数据，无法删除，建议方法确定好后，再执行命令生成，否则只能清空 node 表中的数据，重新生成。

### 4.4 多态控制器应用
````php
 #[Tree("动物园")]
 abstract class Animal extends BaseController
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
> 经常会遇到这样的场景，当同一个请求（公共级 / 用户级 / 项目级等）需要限制并发，可以使用SyncLock，注解控制器方法，达到限制并发的效果，SyncLock 采用的是 redis 的 set 实现方式，redis配置默认使用的Laravel env配置项，可自行在生成的 cpnfig/annotation.php 中配置。
#### 5.1.1 使用 SyncLock 注解
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
     
     #[SyncLock(suffix: "{post.id}")]
     #[Node("更新", parent: "newsList")]
     #[PostMapping]
     function updateNews()
     {
        // todo
     }
}
````
- 以上的 updateNews 方法，当并发相同 id 请求时，则会响应正在更新中的提示

#### 5.1.2 可用参数说明

- ***expire***
- 加锁有效期，超过有效期后，将自动解除该锁，当请求内有异常未处理，或者配置了 once: true时，会在一个加锁周期后解除。


- ***name***
- 锁名称，不配置时，默认为当前{模块名} _ {控制器名} _ {方法名}


- ***once***
- 定义限制在锁有效期内只能请求一次，请求完不会自动释放锁


- ***code***
- 锁定被阻断访问时返回的响应code码，在 ***Extra/ResponseCode*** 枚举中已定义了常用的代码，可自定义枚举类并实现接口 ***Extra/ResponseCodeEnum*** 。


- ***msg***
- 锁定被阻断访问时返回的响应错误信息。


- ***response***
- 自定义响应数据格式，数组类型，以json格式响应。


- ***prefix***
- 自定义锁前置key


- ***suffix***
- 自定义锁名后缀，可通过花括号取请求变量，例如使用 post请求的id 为后缀，则使用 {post.id}，已支持的请求类型有：input / get / post / header / query / date


- ***suffixes***
- 组合后缀定义，可配置多个后缀名或请求变量。

> 如果需要按登录身份进行并发限制，则可以使用 SyncLockByToken suffix参数可通过参数或配置文件 config/annotation.php 中的interceptor -> lock -> token 配置，默认配置为： {header.token} 


### 5.2 验证器


## 6、依赖注入

### 

## 7、代码贡献

#### [Crastlin博客主页](https://learnku.com/blog/Crastlin) crastlin@163.com

## 8、使用必读

- ___使用此插件请遵守法律法规，请勿在非法和违法应用中使用，产生的一切后果和法律责任均与作者无关！___