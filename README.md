<h1>欢迎使用Rain PHP Framework(RPF)</h1>
************************************************************************************************
<h3>框架核心理念</h3>
框架整体是基于MVCA架构模式，之所以自创这样的模式，是为了更好的解放C即controller，让它真正只做好它的控制器功能,做到流程控制,把真正处理逻辑放在action中，让它可以互不干扰，不同的action加载不同的action操作类，让它们不必要加载程序用不到的代码，一定程度上提升性能。

<h3>框架功能简述</h3>
<ul>
	<li>基于MVCA模式，有效避免加载不必要的代码</li>
	<li>支持URL四种路由模式即：普通模式 / URL的rewrite模式 / PATHINFO模式 / 兼容模式, 让你的URL更加利于SEO</li>
	<li>支持基于PDO的mysql操作模式，支持mysql操作的实时缓存处理,全面杜绝SQL注入问题</li>
	<li>支持有效防止CSRF攻击的token校验模式开启 / 关闭</li>
	<li>支持开启安全模式，全方面，全局为您过滤SERVER / COOKIE / REQUEST / GET / POST 让XSS成为浮云</li>
	<li>支持简单易用的上传类，全面屏蔽上传漏洞问题</li>
	<li>支持国际化，简单语言包配置,轻松开发多个语言版本的WEB系统</li>
	<li>支持系统函数 / 系统类库 / 用户函数 / 用户自定义类库 自动加载功能，不再需要您进行任何的require或include</li>
	<li>支持自由控制是否开启ORM功能，该功能类库使用第三方的类库，详见：http://www.redbeanphp.com/</li>
	<li>支持所有的Model层只要简单的使用where / tb / find /select / save / add 等方法就可轻松操作数据库，无需自己编写SQL语句</li>
	<li>扩展了Http和HttpClient类库分别使用curl和socket模式进行发送http请求,支持简单易用的POST和GET请求的操作</li>
	<li>基于Action类，有简单的set方法设置值到模板，用简单的display方法渲染模板，简单有效</li>
	<li>扩展支持zip压缩包操作类</li>
	<li>扩展支持Apache操作类</li>
	<li>扩展支持Email操作类,支持简单易用的发送邮件功能</li>
	<li>扩展支持字符串String操作类，方便开发过程中的字符串处理操作</li>
	<li>扩展支持SESSION操作类，方便针对SESSION的各项操作</li>
	<li>扩展支持FTP操作类，方便针对FTP的各项操作</li>
	<li>扩展支持Cache操作类，方便针对数据进行文件/内存memcache缓存处理操作</li>
	<li>扩展支持Config操作类，方便针对配置文件中的配置项的处理操作</li>
	<li>扩展支持Lang操作类，方便针对语言包文件中的语言项的处理操作</li>
	<li>扩展支持Gd操作类，方便针对图像的处理操作</li>
</ul>

<h3>框架API文档</h3>
详见doc目录，直接浏览器访问例如：http://localhost/doc/即可查看文档

<h3>几点误区澄清</h3>

1.构建框架本身并不是为了所谓的重复造轮子，更多考虑自我提升和实践。很多时候只有亲身尝试做过，才会发现自己的不足，才有可能不断的进行自我完善。发布框架本身也仅供学习参考，并未涉及其他层面的考量。

2.框架整体实现上或多或少参考了一些个人熟悉的框架模式，但绝非照抄照搬，总体而言是在借鉴的基础上做更多的自己认可的架构调整。个人并不觉得完全的自主创新是完美的方案，否则更像是重复的造轮子工程。能够把握细节，大同小异的基础上构建自己的创新点，不仅仅可以减少熟悉和使用框架的成本，更加减少不必要的探索和挖掘道路的成本。

<h3>框架流程映射</h3>

单入口映射，例如针对通常web网站，index.php前台入口/admin.php后台入口

所有项目入口文件都必须包含框架库中的ini.php文件，该文件内置定义大量的常量，以及相关的php的选项设置，然后ini.php文件末尾初始化kernel/Kernel类，调用start方法进行包括加载配置/加载语言包/加载用户自定义函数/初始化session/解析请求URL/项目目录检测，最终根据请求URL，初始化相应的controller/action类，然后调用controller的init方法以及action的init和run方法,整体框架引导结束，进入用户代码逻辑层。
