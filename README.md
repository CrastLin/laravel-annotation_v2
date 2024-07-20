# laravel-annotation_v2 使用指南
*** 
> 从PHP8开始已经对[注解（Attribute）](https://www.php.net/releases/8.0/zh)原生支持了，这有利于我们创建更快更好用的注解工具利器，为我们的编码工作带来更高的效率。之前开发的 [PHP7.x + Laravel5.8.x 注解插件](https://github.com/CrastLin/laravel-annotation) 在leanKu上受到不少朋友的关注，但大部分朋友已经全面切到PHP8+，希望能发布PHP8系列注解插件，虽然现在很多PHPer都转战Golang了，但PHP在我心中依旧占一席之地！所以我依然希望能为PHP开源尽点微薄之力，希望它越来越好重回巅峰。
>

- ___laravel-annotation_v2支持的注解功能有：路由、菜单权限、拦截器（包含并发锁、Laravel验证器集成）、依赖注入。支持的注解位置类（Class）、属性（Property）、构造函数（Constructor）、方法（Method）、参数（Parameter），可支持Laravel config配置注入和Env环境配置注入。___

* [Laravel5.8 + PHP7.x 系列的 laravel-annotation 传送 ](https://github.com/CrastLin/laravel-annotation)
* [Laravel5.8 + PHP7.x 系列的 laravel-annotation使用demo](https://github.com/CrastLin/laravel-annotation-demo)

## 1、安装注解依赖
####  由于依赖包使用了[PHP8.1枚举](https://www.php.net/releases/8.1/en.php)，因此PHP版本要求 >= 8.1，在laravel根目录下，执行以下命令
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

* <b style="color:#f60">注意：根据实现的功能的需要，程序会生成缓存文件，以便更快的运行。文件根目录在config/annotation.php中的annotation_path项，默认目录在根目录的data目录，需要创建该目录，并且授权读写权限</b>

### 1、路由模块注解
### 1.1 路由定义
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


 - - <b class="tag">path</b> &nbsp;&nbsp;路由目录，可自定义，未配置时，默认以Controllers目录下的层级生成，如以上示例，未定义path时，地址为：/Portal/Index/home


 - - <b class="tag">method</b>  &nbsp;&nbsp;限制请求类型，如果POST / GET / REQUEST等，默认为any类型，支持的类型在 <b>Crastlin\LaravelAnnotation\Enum\Method</b> 枚举类中，可以根据您的需要使用


 - - <b class="tag">methods</b> &nbsp;&nbsp;数组类型，定义多个请求类型限制，即定义多个Method枚举对象


 - - <b class="tag">name</b> &nbsp;&nbsp;定义路由名称


 - - <b class="tag">where</b> &nbsp;&nbsp;定义路由条件，可定义验证请求的路由参数，例如：path定义了，/index/{id}，可配置where: ['id' => '[0-9]+']


> 更新的路由注解，请查看Router接口的实现类，包括常用的PostMapping / GetMapping / AnyMapping等，


- 路由更多的使用请参考[Laravel9中文文档](https://learnku.com/docs/laravel/9.x/routing/12209#296672)


### 1.2 路由分组
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

### 1.3 资源路由
> 资源路由不常用的接口实现方式，其以固定的请求方式和路由参数组合标准，可快速注册路由，可以使用注解：ResourceMapping 和 ApiResourceMapping 生成一组资源路由，实现接口：Crastlin\LaravelAnnotation\Annotation\Attributes\ResourceInterface，快速生成对应的方法

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

- 可以执行以下命令主动生成路由配置
````shell
sudo -u www php artisan annotation:route 
````

> Tips: 路由配置自动生成开关可以在配置项（route）中修改，默认在开发环境路由自动生成，建议在开发环境配置hook，每次发版完成后，执行
## 二、菜单权限注解

## 三、拦截器注解

## 四、依赖注入注解

## 五、代码贡献
### crastlin@163.com

## 六、使用必读
### 使用此插件请遵守法律法规，请勿在非法和违法应用中使用，产生的一切后果和法律责任均与作者无关！