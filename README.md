EvaPermission
=======

EvaPermission依赖EvaUser，加载后默认监听两个事件：

- `dispatch:beforeExecuteRoute`
- `user:afterLogin`

在`dispatch:beforeExecuteRoute`事件中，模块基于Phalcon ACL（实际上更接近RBAC）检查当前访问的权限，如果没有权限则停止程序继续进行。

权限检测可以在配置文件中通过`disableAll`强制关闭，同时可以设置`superusers`和`superkeys`来设置对指定的用户ID或Key不进行检测

``` php
    'permission' => array(
        'disableAll' => false,
        'superusers' => array(
            1, 2, 3
        ),
        'superkeys' => array(
            'XXXXX'
    ),
  ```

### RBAC设计

RBAC基于7个表实现

- `user_users` 用户表
- `permission_roles` 角色表
- `permission_users_roles` 用户与角色关系表
- `permission_resources` 资源表（资源对应Controller）
- `permission_operations` 操作表（操作对应Action）
- `permission_roles_operations` 资源与操作关系表
- `permission_apikeys` APIKey表，第一版中APIKey暂时充当token的作用

数据表关系为

`apikeys` ← 一对一→ `users`  ← 一对多→  `roles`  ← 多对多→  `operations`  ← 多对一→  `resources`

### 资源的分类及检测

我们将Controller抽象为资源，并且通过让Controller实现不同接口来区分权限认证的方式，有以下分类：

- Controller不实现任何接口，此时Controller作为公共资源，不进行权限检测
- Controller实现接口`Eva\EvaEngine\Mvc\Controller\SessionAuthorityControllerInterface`，基于Session验证此资源
- Controller实现接口`Eva\EvaEngine\Mvc\Controller\TokenAuthorityControllerInterface`，基于Token验证此资源
- Controller实现接口`Eva\EvaEngine\Mvc\Controller\RateLimitControllerInterface`，基于Token验证的基础上加入访问次数限制

为了调试方便，Permission模块会在响应中添加头信息`X-Permission-Auth`来辅助判断权限检测Allow和Deny的原因

- `X-Permission-Auth:Allow-By-Disabled-Auth` 权限模块已关闭
- `X-Permission-Auth:Deny-By-Session` 基于Session检测并被Deny
- `X-Permission-Auth:Allow-By-Session` 基于Session检测并被Allow
- `X-Permission-Auth:Deny-By-Token` 基于Token检测并被Deny
- `X-Permission-Auth:Allow-By-Token` 基于Token检测并被Allow
- `X-Permission-Auth:Allow-By-Public-Resource` 公共资源未进行权限检测


### 资源的自动录入

由于所有资源都可以对应到代码，因此可以通过脚本扫描源代码将所有资源入库。

运行

    php utilities/aclscanner.php appName
   
即可。

脚本会实例化`Eva\EvaPermission\Utils\Scanner`，并执行以下操作：

1. 通过AppName找到该App加载的所有的模块
2. 扫描模块Controller代码，如果Controller实现权限相关接口，则进一步扫描
3. 查找规定的注解，如果符合规范，则将Controller及Action作为资源录入数据库

#### 注解规范

参考以下代码

``` php
/**
* @resourceName("用户中心")
* @resourceDescription("用户中心相关资源")
*/
class MineController extends ControllerBase implements SessionAuthorityControllerInterface
{
    /**
    * @operationName("用户中心首页")
    * @operationDescription("用户中心首页")
    */
    public function dashboardAction()
    {
    }
}
```


### 权限检测过程

权限检测有两个核心类：

- `Eva\EvaPermission\Auth\SessionAuthority`  基于Session的权限检测
- `Eva\EvaPermission\Auth\TokenAuthority`   基于Token的权限检测

权限检测负责：

1. 从数据库中查出资源和操作，将其转换为ACL
2. 如果设置缓存，则将ACL存入缓存
3. 通过`checkAuth`方法接受资源和操作，在ACL中判断输入的资源和操作是否具备权限

Session示例代码，`checkAuth`方法中接受资源名（即Controller类全名）及操作名（即Action名），`setCache`方法接受`Phalcon\Cache\Backend`类型。

``` php
use Eva\EvaPermission\Auth;
$auth = new Auth\SessionAuthority();
$auth->setCache($di->getGlobalCache());
if (!$auth->checkAuth('Wscn\Controllers\MineController', 'index')) {
    //权限检测不通过
} else {
   //权限检测通过
}
```

Token示例代码：

``` php
use Eva\EvaPermission\Auth;
use Eva\EvaEngine\Service\TokenStorage;
$auth = new Auth\TokenAuthority();
$auth->setCache($di->getGlobalCache());
$auth->setApikey(TokenStorage::dicoverToken($di->getRequest()));
if (!$auth->checkAuth('Wscn\Controllers\MineController', 'index')) {
    //权限检测不通过
} else {
   //权限检测通过
}
```

### 权限检测与系统整合

权限检测通过`dispatch:beforeExecuteRoute`事件与整个系统整合


### Token设计（暂定）

目前暂时把用户拥有的APIKey做为Token使用，以后会基于OAuth规范实现真正意义上的Token。

Token的获取通过`Eva\EvaEngine\Service\TokenStorage`，User模块也会基于这个类做Session/Token登录的区分。

目前token位置允许两种，在Url Query以及在Http Header。

如：

    curl http://api.wallstreetcn.com/v2/posts/stars?api_key=XXXX

或

    curl -H "Authorization: token XXXX" http://api.wallstreetcn.com/v2/posts/stars
    http http://api.wallstreetcn.com/v2/posts/stars "Authorization: token XXXX"

### 新增一个带权限资源的流程

1. 编写Controller，实现`SessionAuthorityControllerInterface`或`TokenAuthorityControllerInterface`接口
2. 对Controller按照规范编写注解
3. 运行`aclscanner`脚本，将资源及操作添加到数据库
4. 在后台给有权限的角色分配资源
