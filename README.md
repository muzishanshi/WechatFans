### Typecho微信公众号涨粉插件

---
一款利于微信公众号涨粉的Typecho插件

#### 安装方法：

	第一步：下载本插件，放在 `usr/plugins/` 目录中（插件文件夹名必须为WechatFans）；
	第二步：激活插件；
	第三步：填写微博小号等等配置；
	第四步：完成。
	
#### 使用方法：

	1、配置各项参数；
	2、编写文章时点击编辑器VX按钮，插入以下代码：
	<!--wechatfans start-->
	<!--wechatfans end-->
	代码，中间的文字即为隐藏内容；
	3、替换主题目录下post.php中输出内容的代码，如：
	<?php $this->content; ?>替换成<?php echo WechatFans_Plugin::parseContent($this); ?>
	4、替换主题目录下archive.php或index.php中输出摘要或内容的代码，没有则不替换，如：
	<?php $this->excerpt(140, "..."); ?>替换成<?php echo WechatFans_Plugin::parseExcerpt($this,140, "..."); ?>

#### 与我联系：

	作者：二呆
	网站：http://www.tongleer.com/
	公众号：同乐儿

#### 更新记录：
2019-01-23 V1.0.1

	第一版本