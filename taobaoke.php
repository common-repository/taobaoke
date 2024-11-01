<?php
/*
Plugin Name: WordPress 淘宝客插件
Plugin URI: http://www.hkbaihuo.com/
Description: WordPress淘宝客插件，能根据淘宝商品链接自动生成商品cps链接和掌柜店铺cps链接 
Author: CodeCTO
Author URI: http://codecto.com/
Version: 1.1.2
*/

/*
define('TBK_appkey', 12255345);
define('TBK_secretKey', 'ba8ff45d26ab2490f187dcd1f4008486');
define('TBK_nick', 'boogeyman');
define('TBK_pid', '25084610');
*/

function TBK_feature_hosting($html = 1){
	$hosting = array();
	$hosting[] = array(
		'name' => 'Linost(支持支付宝)',
		'link' => 'https://my.linost.com/aff.php?aff=745',
	);
	$hosting[] = array(
		'name' => 'Gegehost(支持支付宝)',
		'link' => 'http://client.gegehost.com/aff.php?aff=138',
	);
	$hosting[] = array(
		'name' => 'ixwebhosting(支持支付宝)',
		'link' => 'https://www.ixwebhosting.com/templates/ix/v2/affiliate/clickthru.cgi?id=cantonbolo',
	);
	$hosting[] = array(
		'name' => 'DreamHost(专用优惠码:WPTAOBAOKE)',
		'link' => 'http://www.dreamhost.com/r.cgi?1157464',
	);
	$hosting[] = array(
		'name' => 'HostGator(专用优惠码:WPTAOBAOKE25)',
		'link' => 'http://secure.hostgator.com/~affiliat/cgi-bin/affiliates/clickthru.cgi?id=cantonbolo',
	);
	if($html){
		$output = array();
		foreach($hosting as $item){
			$output[] = '<a href="'.$item['link'].'" target="_blank">'.$item['name'].'</a>';
		}
		return implode(', ', $output);
	}else{
		return $hosting;
	}
}

function TBK_warning() {
	echo "
	<div class='updated fade'>
		<p><strong>检测出服务器配置问题：</strong> 您的服务器环境不支持 <a href='http://php.net/manual/en/book.curl.php' target='_blank'>curl</a> 函数，会影响 WordPress 淘宝客插件工作，请联系管理员解决此问题，或者购买下面的专业服务器。</p>
		<p>".TBK_feature_hosting()."</p>
		<p style='text-align:right;'>----- <a href='http://wordpress.org/extend/plugins/taobaoke/' target='_blank'>WordPress 淘宝客插件</a></p>
	</div>
	";
}
if(!function_exists('curl_init')){
	add_action('admin_notices', 'TBK_warning');
}

add_action('wp_ajax_TBK_api', 'TBK_api');
add_action('wp_ajax_nopriv_TBK_api', 'TBK_api');
function TBK_api(){
	if(!$_REQUEST['request']) return;
	//淘宝客sdk初始化
	if(!class_exists('TopClient')) include_once('sdk/top/TopClient.php');
	//淘宝客sdk工具
	if(!class_exists('RequestCheckUtil')) include_once('sdk/top/RequestCheckUtil.php');
	//淘宝客店铺转换
	//if(!class_exists('TaobaokeShopsConvertRequest')) include_once('sdk/top/request/TaobaokeShopsConvertRequest.php');
	//include_once('sdk/top/request/TaobaokeItemsDetailGetRequest.php');
	$TBK_API = get_option('TBK_API');
	$c = new TopClient;
	$c->appkey = $TBK_API['AKEY']?$TBK_API['AKEY']:'12255345';
	$c->secretKey = $TBK_API['SKEY']?$TBK_API['SKEY']:'ba8ff45d26ab2490f187dcd1f4008486';
	switch($_REQUEST['request']){
		case 'TaobaokeItemsDetailGetRequest':
			//淘宝客商品详情
			if(!class_exists('TaobaokeItemsDetailGetRequest')) include_once('sdk/top/request/TaobaokeItemsDetailGetRequest.php');
			$req = new TaobaokeItemsDetailGetRequest;
			$req->setFields("click_url,price,pic_url,shop_click_url,seller_credit_score,num_iid,title,nick,shop_title");
			$req->setNick($TBK_API['tid']?$TBK_API['tid']:'qq191227790');
			$req->setPid($TBK_API['pid']?$TBK_API['pid']:'25972995');
			$req->setNumIids($_REQUEST['items']);
			$req->setOuterCode('WPTaobaoke');
			$resp = $c->execute($req);
		break;
	}
	if($_REQUEST['callback']){
		echo $_REQUEST['callback'].'('.json_encode($resp).')';
	}else{
		echo json_encode($resp);
	}
	die;
}

add_action('admin_menu','TBK_admin_menu');
function TBK_admin_menu(){
	$TBK_post_types = get_option('TBK_post_type')?get_option('TBK_post_type'):array();
	if($TBK_post_types){
		foreach($TBK_post_types as $TBK_post_type){
			add_meta_box('TBK_item_detail', '淘宝客转换', 'TBK_itemdetail_metabox', $TBK_post_type, 'normal', 'high');
		}
	}
}

function TBK_itemdetail_metabox(){
	global $post_ID;
	
	$TBK_item_source_url = get_post_meta($post_ID,'TBK_item_source_url',true);
	$TBK_item_link = get_post_meta($post_ID,'TBK_item_link',true);
	$TBK_item_img = get_post_meta($post_ID,'TBK_item_img',true);
	$TBK_item_price = get_post_meta($post_ID,'TBK_item_price',true);
	$TBK_shop = get_post_meta($post_ID,'TBK_shop',true);
	$TBK_shop_link = get_post_meta($post_ID,'TBK_shop_link',true);
?>
<input type="hidden" name="item_detail" value="1" />
<script language=javascript>
/*
var txt='http://item.taobao.com/item.htm?id=10541553210&ali_refid=a3_619362_1007:1103038518:7:46702465U84y78608587678s868v3I:3000e20c6227149d12f915e39b69433d&ali_trackid=1_3000e20c6227149d12f915e39b69433d';

var re1='.*?';	// Non-greedy match on filler
var re2='(\\d+)';	// Integer Number 1

var p = new RegExp(re1+re2,["i"]);
var m = p.exec(txt);
if (m != null){
	var int1=m[1];
	document.write("("+int1.replace(/</,"&lt;")+")"+"\n");
}
*/

var api = '<?php echo site_url('/wp-admin/admin-ajax.php');?>';
function get_item_datail(item_id){
	if(typeof item_id == 'undefined') return;
	var $ = jQuery;
	$('#TBK_status').text();
	$.getJSON(api+'?callback=?',{
		action : 'TBK_api',
		request : 'TaobaokeItemsDetailGetRequest',
		items : item_id
		//callback : '?'
		},function(data){
			//console.log(data);
			if(typeof data != 'undefined'){
				if(data == [] || data.total_results < 1){
					$('#TBK_status').text('该商品可能不是淘宝客商品。');
					return;
				}else if(typeof data.msg != 'undefined'){
					$('#TBK_status').text(data.msg);
					return;
				}else{
					//alert(data.msg);
					$('#title').val(data.taobaoke_item_details.taobaoke_item_detail[0].item.title);
					$('#TBK_item_link').val(data.taobaoke_item_details.taobaoke_item_detail[0].click_url);
					$('#TBK_item_img').val(data.taobaoke_item_details.taobaoke_item_detail[0].item.pic_url);
					$('#TBK_item_img_preview').attr('src', data.taobaoke_item_details.taobaoke_item_detail[0].item.pic_url);
					$('#TBK_item_price').val(data.taobaoke_item_details.taobaoke_item_detail[0].item.price);
					$('#TBK_shop').val(data.taobaoke_item_details.taobaoke_item_detail[0].item.nick);
					$('#TBK_shop_link').val(data.taobaoke_item_details.taobaoke_item_detail[0].shop_click_url);
					return;
				}
			}
			//return '获取不到数据，可能服务器网络有问题或者淘宝api抽风中';
		}
	)
}

function TBK_request(){
	var $ = jQuery, item_id = $('#TBK_item_source_url').val(), re1 = '.*?', re2 = '(\\d+)', p = new RegExp(re1+re2,["i"]), m = p.exec(item_id);
	if(m != null){
		item_id = m[1];
		//console.log(item_id);
		var data = get_item_datail(item_id);
		/*
		if(typeof data == 'string'){
			$('#TBK_status').text(data);
			return;
		}else{
			//console(data.taobaoke_items.taobaoke_item[1]);
			$('#title').val(data.taobaoke_items.taobaoke_item[0].title);
			$('#item_url').val(data.taobaoke_items.taobaoke_item[0].click_url);
			$('#item_price').val(data.taobaoke_items.taobaoke_item[0].price);
			$('#item_source').val(data.taobaoke_items.taobaoke_item[0].nick);
			return;
		}
		*/
	}else{
		$('#TBK_status').text('无法获取店铺信息，请检查输入的来源是否正确。');
		return;
	}
}
</script>
<p id="TBK_status"></p>
<table class="form-table">
<tbody>
<tr valign="top">
	<td class="first">淘宝商品源链接：</td>
	<td><input type="text" id="TBK_item_source_url" name="TBK_item_source_url" size="90" value="<?php echo esc_attr($TBK_item_source_url);?>" /> <a class="preview button" href="#" onclick="TBK_request();return false;">填充产品信息</a></td>
</tr>
<tr valign="top">
	<td class="first">产品链接(CPS)：</td>
	<td><input type="text" id="TBK_item_link" name="TBK_item_link" size="90" value="<?php echo esc_attr($TBK_item_link);?>" /></td>
</tr>
<tr valign="top">
	<td class="first">产品图片：</td>
	<td><input type="text" id="TBK_item_img" name="TBK_item_img" size="90" value="<?php echo esc_attr($TBK_item_img);?>" /><br /><img id="TBK_item_img_preview" src="" /></td>
</tr>
<tr valign="top">
	<td class="first">价格：</td>
	<td><input type="text" id="TBK_item_price" name="TBK_item_price" size="30" value="<?php echo esc_attr($TBK_item_price);?>" /></td>
</tr>
<tr valign="top">
	<td class="first">店铺：</td>
	<td><input type="text" id="TBK_shop" name="TBK_shop" size="30" value="<?php echo esc_attr($TBK_shop);?>" /></td>
</tr>
<tr valign="top">
	<td class="first">店铺链接(CPS)：</td>
	<td><input type="text" id="TBK_shop_link" name="TBK_shop_link" size="90" value="<?php echo esc_attr($TBK_shop_link);?>" /></td>
</tr>
</tbody>
</table>
<?php 
}

add_action('save_post', 'TBK_save_post');
function TBK_save_post($post_id){
	if(!current_user_can('edit_post',$post_id)){
		return $post_id;
	}
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return $post_id;
	}
	if(!isset($_POST['TBK_item_source_url'])){
		return $post_id;
	}
	update_post_meta($post_id,'TBK_item_source_url',$_POST['TBK_item_source_url']);
	update_post_meta($post_id,'TBK_item_link',$_POST['TBK_item_link']);
	update_post_meta($post_id,'TBK_item_img',$_POST['TBK_item_img']);
	update_post_meta($post_id,'TBK_item_price',$_POST['TBK_item_price']);
	update_post_meta($post_id,'TBK_shop',$_POST['TBK_shop']);
	update_post_meta($post_id,'TBK_shop_link',$_POST['TBK_shop_link']);
	return $post_id;
}

add_action('admin_menu', 'TBK_option_page');
function TBK_option_page() {
	add_options_page('淘宝客插件设置', '淘宝客插件设置', 'manage_options', 'wordpress-taobaoke', 'TBK_option_page_content');
}

function TBK_option_page_content(){
	if($_POST){
		check_admin_referer('TBK_setting_save','TBK_setting_save_nonce');
		$TBK_API = array(
			'AKEY' => $_POST['TBK_app_key'],
			'SKEY' => $_POST['TBK_app_secret'],
			'tid' => $_POST['TBK_taobao_id'],
			'pid' => $_POST['TBK_pid'],
		);
		$TBK_post_type = $_POST['TBK_post_type'];
		update_option('TBK_API', $TBK_API);
		update_option('TBK_post_type', $TBK_post_type);
		update_option('TBK_backlink', $_POST['TBK_backlink']);
	}
	$TBK_API = get_option('TBK_API');
	$TBK_post_types = get_option('TBK_post_type')?get_option('TBK_post_type'):array();
	$TBK_backlink = get_option('TBK_backlink');
	?>
<div class="wrap">
<div class="icon32" id="icon-options-general"><br></div><h2>WordPress淘宝客设置</h2>

<form method="post" action="<?php echo admin_url('options-general.php?page=wordpress-taobaoke'); ?>" name="form">
<?php wp_nonce_field('TBK_setting_save','TBK_setting_save_nonce'); ?>
<p>在这里设置WordPress淘宝客插件的相关参数，包括淘宝App Key、淘宝客账户和插件支持的文章类型等等。淘宝App Key可以到 <a href="http://my.open.taobao.com/common/createApp.htm" target="_blank">淘宝开放平台申请</a>。请认真设置，否则你有可能损失收入。</p>

<h3>淘宝API设置</h3>
<table class="form-table">
	<tbody>
		<tr>
			<th>
				<label for="TBK_app_key">App Key：</label>
			</th>
			<td>
				<input type="text" class="regular-text code" value="<?php echo esc_attr($TBK_API['AKEY']?$TBK_API['AKEY']:'12232214');?>" id="TBK_app_key" name="TBK_app_key">
			</td>
		</tr>
		<tr>
			<th>
				<label for="TBK_app_secret">App Secret：</label>
			</th>
			<td>
				<input type="text" class="regular-text code" value="<?php echo esc_attr($TBK_API['SKEY']?$TBK_API['SKEY']:'7b2e35f3a014cf283d1c301df1f62044');?>" id="TBK_app_secret" name="TBK_app_secret">
			</td>
		</tr>
		<tr>
			<th>
				<label for="TBK_taobao_id">淘宝用户名：</label>
			</th>
			<td>
				<input type="text" class="regular-text code" value="<?php echo esc_attr($TBK_API['tid']?$TBK_API['tid']:'qq191227790');?>" id="TBK_taobao_id" name="TBK_taobao_id">
			</td>
		</tr>
		<tr>
			<th>
				<label for="TBK_pid">淘宝客pid：</label>
			</th>
			<td>
				<input type="text" class="regular-text code" value="<?php echo esc_attr($TBK_API['pid']?$TBK_API['pid']:'25972995');?>" id="TBK_pid" name="TBK_pid">
				<p>淘宝客pid可以到 <a href="http://www.alimama.com/union/myunion/myOverview.htm" target="_blank">淘宝客个人中心</a> 查看，假设你的pid是 mm_xxxxxxxx_0_0，你只需填写 xxxxxxxx 部分即可。</p>
			</td>
		</tr>
	</tbody>
</table>

<h3>文章类型选项</h3>
	<p>在这里可以设置需要本插件提供支持的文章类型。</p>
	<?php
	$post_types = get_post_types(array(
		'public' => true,
		'show_ui' => true
	));
	foreach($post_types as $post_type){
		$args = get_post_type_object($post_type);
		//print_r($args);
	}
	//print_r($post_types);
	?>
<table class="form-table">
	<tbody>
	<?php foreach($post_types as $post_type):
		$args = get_post_type_object($post_type);?>
		<tr>
			<td><label><input type="checkbox" value="<?php echo $post_type;?>" id="TBK_post_type_<?php echo $post_type;?>" name="TBK_post_type[]"<?php if(in_array($post_type, $TBK_post_types)) echo ' checked="checked"'; ?>> <?php echo $args->label;?></label></td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>
<br />
<p class="submit" style="background-color:#FFFFCC;padding:1.5em;">本插件由 Bolo 进行开发，如果你觉得好的话，可以到我的<a href="https://me.alipay.com/bolo" target="_blank">个人收款页面</a>对赞助开发。也可以勾选下面的选项，在你博客的底部添加一个我网站的链接。<br />
<label><input type="checkbox" value="1" name="TBK_backlink"<?php if($TBK_backlink) echo ' checked="checked"';?> /> 在页脚添加反链</label><br />
<strong>推荐WordPress主机：</strong><br />
<?php echo TBK_feature_hosting(); ?>
</p>

<p class="submit"><input type="submit" value="保存更改" class="button-primary" id="submit" name="submit"></p>  </form>

</div>
	<?php
}

if(get_option('TBK_backlink')) add_action('wp_footer', 'TBK_backlink');
function TBK_backlink(){
	echo '<a href="http://www.hkbaihuo.com" target="_blank" title="好看百货-淘宝皇冠店精选">好看百货</a>';
}