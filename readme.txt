=== Plugin Name ===
Contributors: bolo1988
Donate link: http://www.hkbaihuo.com/
Tags: taobaoke, cps
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 1.0

WordPress淘宝客插件，能根据淘宝商品链接自动生成商品cps链接和掌柜店铺cps链接

== Description ==

通过WordPress淘宝客插件，你可以给任何文章类型加上淘宝客选项，在你的博客上进行淘宝客商品推广，并获取销售提成。


== Installation ==

1. 解压后把 `taobaoke` 目录上传到 /wp-content/plugins
1. 在后台激活 `WordPress淘宝客插件`
1. 到 `淘宝客插件设置` 页面对本插件进行设置

== Frequently Asked Questions ==

= 在文章模板中怎么调用淘宝客数据？ =

本插件生成的淘宝客数据使用 post meta 进行储存，在模板中可以通过 `get_post_meta($post_id, $key, $single)` 函数进行调用。

例：

`get_post_meta($post->ID,'TBK_item_url',true); //调用商品淘宝客链接`

`get_post_meta($post->ID,'TBK_item_img',true); //调用商品图片`

`get_post_meta($post->ID,'TBK_item_price',true); //调用商品价格`

`get_post_meta($post->ID,'TBK_shop',true); //调用掌柜名称`

`get_post_meta($post->ID,'TBK_shop_link',true); //调用店铺链接`

== Screenshots ==

1.  `/screenshot-1.gif`
插件参数设置页面

2.  `/screenshot-2.gif`
插件截图

== Changelog ==

= 1.1.2 =
* 增加了服务器环境检测功能

= 1.1.1 =
* 修复了商品信息不能保存的bug

= 1.1 =
* 修复和 wp-taobaoke 插件得冲突（强烈建议安装了1.0版用户把插件完全删除安装新版！）
* 删除了没用的代码

= 1.0 =
* 支持自定义文章类型设置

== Upgrade Notice ==

None
