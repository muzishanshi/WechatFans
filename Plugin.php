<?php
/**
 * Typecho微信公众号涨粉插件<div class="WechatFansSet"><br /><a href="javascript:;" title="插件因兴趣于闲暇时间所写，故会有代码不规范、不专业和bug的情况，但完美主义促使代码还说得过去，如有bug或使用问题进行反馈即可。">鼠标轻触查看备注</a>&nbsp;<a href="http://club.tongleer.com" target="_blank">论坛</a>&nbsp;<a href="https://www.tongleer.com/api/web/pay.png" target="_blank">打赏</a>&nbsp;<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=diamond0422@qq.com" target="_blank">反馈</a></div><style>.WechatFansSet a{background: #4DABFF;padding: 5px;color: #fff;}</style>
 * @package WechatFans For Typecho
 * @author 二呆
 * @version 1.0.3<br /><span id="WechatFansUpdateInfo"></span><script>WechatFansXmlHttp=new XMLHttpRequest();WechatFansXmlHttp.open("GET","https://www.tongleer.com/api/interface/WechatFans.php?action=update&version=3",true);WechatFansXmlHttp.send(null);WechatFansXmlHttp.onreadystatechange=function () {if (WechatFansXmlHttp.readyState ==4 && WechatFansXmlHttp.status ==200){document.getElementById("WechatFansUpdateInfo").innerHTML=WechatFansXmlHttp.responseText;}}</script>
 * @link http://www.tongleer.com/
 * @date 2019-08-16
 */
date_default_timezone_set('Asia/Shanghai');

class WechatFans_Plugin implements Typecho_Plugin_Interface{
    // 激活插件
    public static function activate(){
		$db = Typecho_Db::get();
		Typecho_Plugin::factory('admin/write-post.php')->bottom = array('WechatFans_Plugin', 'tleWechatFansToolbar');
		Typecho_Plugin::factory('admin/write-page.php')->bottom = array('WechatFans_Plugin', 'tleWechatFansToolbar');
		Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('WechatFans_Plugin', 'contentEx');
		Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('WechatFans_Plugin', 'excerptEx');
        return _t('插件已经激活，需先配置微博图床的信息！');
    }

    // 禁用插件
    public static function deactivate(){
		$db = Typecho_Db::get();
        return _t('插件已被禁用');
    }
	
    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form){
		$options = Typecho_Widget::widget('Widget_Options');
		$plug_url = $options->pluginUrl;
		//版本检查
		$div=new Typecho_Widget_Helper_Layout();
		$div->html('
			<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
			<h6>使用方法</h6>
			<span><p>1、配置下方各项参数；</p></span>
			<span><p>2、编写文章时点击编辑器VX按钮，插入以下代码：<br />&lt;!--wechatfans start--><br />&lt;!--wechatfans end--><br />代码，中间的文字即为隐藏内容；</p></span>
			<span>
				<p>
					3、<font color="blue">【旧版本（需手动修改代码），可选】</font><br />
					替换主题目录下post.php中输出内容的代码，如：<br />
					&lt;?php $this->content; ?>替换成<font color="red">&lt;?php echo WechatFans_Plugin::parseContent($this); ?></font><br />
					替换主题目录下archive.php或index.php中输出摘要或内容的代码，没有则不替换，如：<br />
					&lt;?php $this->excerpt(140, "..."); ?>替换成<font color="red">&lt;?php echo WechatFans_Plugin::parseExcerpt($this,140, "..."); ?></font>
				</p>
			</span>
			<span>
				<p>
					4、<font color="blue">【新版本（自动匹配标签规则）】</font><br />
					替换主题目录下post.php中输出内容的代码，<font color="red">若已有自定义的输出内容代码，则可以不替换</font>，如：<br />
					&lt;?php $this->content; ?>替换成&lt;?php echo $this->content; ?>
				</p>
			</span>
		</small>');
		$div->render();
		
        $wechat_name = new Typecho_Widget_Helper_Form_Element_Text('wechat_name', array("value"), '同乐儿', _t('微信公众号名称'), _t('微信公众号平台→公众号设置→名称，例如：同乐儿'));
        $form->addInput($wechat_name);
		$wechat_account = new Typecho_Widget_Helper_Form_Element_Text('wechat_account', array("value"), 'Diamond0422', _t('微信公众号名称'), _t(' 微信公众号平台→公众号设置→微信号，例如：Diamond0422'));
        $form->addInput($wechat_account);
		$wechat_keyword = new Typecho_Widget_Helper_Form_Element_Text('wechat_keyword', array("value"), '微信验证码', _t('回复以下关键词获取验证码'), _t('例如：微信验证码，访客回复这个关键词就可以获取到验证码'));
        $form->addInput($wechat_keyword);
		$wechat_code = new Typecho_Widget_Helper_Form_Element_Text('wechat_code', array("value"), '123456', _t('自动回复的验证码'), _t('该验证码要和微信公众号平台自动回复的内容一致，最好定期两边都修改下'));
        $form->addInput($wechat_code);
		$wechat_qrimg = new Typecho_Widget_Helper_Form_Element_Text('wechat_qrimg', array("value"), 'https://ws3.sinaimg.cn/large/ecabade5ly1fxgw6cvsrfj20u00u0n0x.jpg', _t('微信公众号二维码地址'), _t('填写您的微信公众号的二维码图片地址，建议150X150像素'));
        $form->addInput($wechat_qrimg);
		$wechat_day = new Typecho_Widget_Helper_Form_Element_Text('wechat_day', array("value"), '30', _t('Cookie有效期天数'), _t('在有效期内，访客无需再获取验证码可直接访问隐藏内容'));
        $form->addInput($wechat_day);
		$wechat_key = new Typecho_Widget_Helper_Form_Element_Text('wechat_key', array("value"), md5('tongleer.com'.time().rand(10000,99999)), _t('加密密钥'), _t('用于加密Cookie，默认是自动生成，一般无需修改，如果修改，所有访客需要重新输入验证码才能查看隐藏内容'));
        $form->addInput($wechat_key);

    }
	
    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form){
    }

    // 获得插件配置信息
    public static function getConfig(){
        return Typecho_Widget::widget('Widget_Options')->plugin('WechatFans');
    }
	
	/**
     * 后台编辑器添加微信公众号涨粉按钮
     * @access public
     * @return void
     */
	public static function tleWechatFansToolbar(){
		?>
		<script type="text/javascript">
			$(function(){
				if($('#wmd-button-row').length>0){
					$('#wmd-button-row').append('<li class="wmd-button" id="wmd-button-wechatfans" style="font-size:20px;float:left;color:#AAA;width:20px;" title=微信公众号涨粉><b>VX</b></li>');
				}else{
					$('#text').before('<a href="#" id="wmd-button-wechatfans" title="微信公众号涨粉"><b>VX</b></a>');
				}
				$(document).on('click', '#wmd-button-wechatfans', function(){
					$('#text').val($('#text').val()+'\r\n<!--wechatfans start-->\r\n\r\n<!--wechatfans end-->');
				});
				/*移除弹窗*/
				if(($('.wmd-prompt-dialog').length != 0) && e.keyCode == '27') {
					cancelAlert();
				}
			});
			function cancelAlert() {
				$('.wmd-prompt-dialog').remove()
			}
		</script>
		<?php
	}
	
	/**
     * 输出摘要
     * @access public
     * @return void
     */
    public static function parseExcerpt($obj,$length=140,$trim="..."){
		$excerpt=trim($obj->excerpt);
		$wechatfansRule='/<!--wechatfans start-->([\s\S]*?)<!--wechatfans end-->/i';
		preg_match_all($wechatfansRule, $excerpt, $hide_words);
		if(!$hide_words[0]){
			$wechatfansRule='/&lt;!--wechatfans start--&gt;([\s\S]*?)&lt;!--wechatfans end--&gt;/i';
		}
		$WeMediaRule='/<!--WeMedia start-->([\s\S]*?)<!--WeMedia end-->/i';
		preg_match_all($WeMediaRule, $excerpt, $hide_words);
		if(!$hide_words[0]){
			$WeMediaRule='/&lt;!--WeMedia start--&gt;([\s\S]*?)&lt;!--WeMedia end--&gt;/i';
		}
		
		if (preg_match_all($wechatfansRule, $excerpt, $hide_words)){
			$excerpt = str_replace($hide_words[0], '', $excerpt);
		}
		if (preg_match_all($WeMediaRule, $excerpt, $hide_words)){
			$excerpt = str_replace($hide_words[0], '', $excerpt);
		}
		$excerpt=Typecho_Common::subStr(strip_tags($excerpt), 0, $length, $trim);
		return $excerpt;
	}

	/**
     * 输出内容
     * @access public
     * @return void
     */
    public static function parseContent($obj){
		$wechatfansRule='/<!--wechatfans start-->([\s\S]*?)<!--wechatfans end-->/i';
		$content=trim($obj->content);
		preg_match_all($wechatfansRule, $content, $hide_words);
		if(!$hide_words[0]){
			$wechatfansRule='/&lt;!--wechatfans start--&gt;([\s\S]*?)&lt;!--wechatfans end--&gt;/i';
		}
		$option=self::getConfig();
		$cookie_name = 'tongleer_wechat_fans';
		
		if (preg_match_all($wechatfansRule, $content, $hide_words)){
			$cv = md5($option->wechat_key.$cookie_name.'tongleer.com');
			$vtips='';
			if(isset($_POST['tongleer_verifycode'])){
				if($_POST['tongleer_verifycode']==$option->wechat_code){
					setcookie($cookie_name, $cv ,time()+(int)$option->wechat_day*86400, "/");
					$_COOKIE[$cookie_name] = $cv;
				}else{
					$vtips='<script>alert("验证码错误！请输入正确的验证码！");</script>';
				}
			}
			$cookievalue = isset($_COOKIE[$cookie_name])?$_COOKIE[$cookie_name]:'';
			
			if($cookievalue==$cv){
				$content = str_replace($hide_words[0], '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.$hide_words[0][0].'</div>', $content);	
			}else{
				
				$hide_notice = '<div class="huoduan_hide_box" style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;"><img class="wxpic" align="right" src="'.$option->wechat_qrimg.'" style="width:150px;height:150px;margin-left:20px;display:inline;border:none" width="150" height="150"  alt="'.$option->wechat_name.'" /><span style="font-size:18px;">此处内容已经被作者隐藏，请输入验证码查看内容</span><form method="post" style="margin:10px 0;"><span class="yzts" style="font-size:18px;float:left;">验证码：</span><input name="tong'.'le'.'er_verifycode" id="verifycode" type="text" value="" style="border:none;float:left;width:80px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;" /><input id="verifybtn" style="border:none;float:left;width:80px; height:32px; line-height:32px; padding:0 5px; background-color:#F60; text-align:center; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="submit" value="提交查看" /></form><div style="clear:left;"></div><span style="color:#00BF30">请关注本站微信公众号，回复“<span style="color:blue">'.$option->wechat_keyword.'</span>”，获取验证码。在微信里搜索“<span style="color:blue">'.$option->wechat_name.'</span>”或者“<span style="color:blue">'.$option->wechat_account.'</span>”或者微信扫描右侧二维码都可以关注本站微信公众号。</span><div class="cl"></div></div>'.$vtips;
				$content = str_replace($hide_words[0], $hide_notice, $content);
			}
		}
		return $content;
	}
	
	/**
     * 自动输出摘要
     * @access public
     * @return void
     */
    public static function excerptEx($html, $widget, $lastResult){
		$wechatfansRule='/<!--wechatfans start-->([\s\S]*?)<!--wechatfans end-->/i';
		preg_match_all($wechatfansRule, $html, $hide_words);
		if(!$hide_words[0]){
			$wechatfansRule='/&lt;!--wechatfans start--&gt;([\s\S]*?)&lt;!--wechatfans end--&gt;/i';
		}
		$WeMediaRule='/<!--WeMedia start-->([\s\S]*?)<!--WeMedia end-->/i';
		preg_match_all($WeMediaRule, $html, $hide_words);
		if(!$hide_words[0]){
			$WeMediaRule='/&lt;!--WeMedia start--&gt;([\s\S]*?)&lt;!--WeMedia end--&gt;/i';
		}
		$html=trim($html);
		if (preg_match_all($wechatfansRule, $html, $hide_words)){
			$html = str_replace($hide_words[0], '', $html);
		}
		if (preg_match_all($WeMediaRule, $html, $hide_words)){
			$html = str_replace($hide_words[0], '', $html);
		}
		$html=Typecho_Common::subStr(strip_tags($html), 0, 140, "...");
		return $html;
	}
	
	/**
     * 自动输出内容
     * @access public
     * @return void
     */
    public static function contentEx($html, $widget, $lastResult){
		$wechatfansRule='/<!--wechatfans start-->([\s\S]*?)<!--wechatfans end-->/i';
		preg_match_all($wechatfansRule, $html, $hide_words);
		if(!$hide_words[0]){
			$wechatfansRule='/&lt;!--wechatfans start--&gt;([\s\S]*?)&lt;!--wechatfans end--&gt;/i';
		}
		$html = empty( $lastResult ) ? $html : $lastResult;
		$option=self::getConfig();
		$cookie_name = 'tongleer_wechat_fans';
		$html=trim($html);
		if (preg_match_all($wechatfansRule, $html, $hide_words)){
			$cv = md5($option->wechat_key.$cookie_name.'tongleer.com');
			$vtips='';
			if(isset($_POST['tongleer_verifycode'])){
				if($_POST['tongleer_verifycode']==$option->wechat_code){
					setcookie($cookie_name, $cv ,time()+(int)$option->wechat_day*86400, "/");
					$_COOKIE[$cookie_name] = $cv;
				}else{
					$vtips='<script>alert("验证码错误！请输入正确的验证码！");</script>';
				}
			}
			$cookievalue = isset($_COOKIE[$cookie_name])?$_COOKIE[$cookie_name]:'';
			
			if($cookievalue==$cv){
				$html = str_replace($hide_words[0], '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.$hide_words[0][0].'</div>', $html);	
				$html = str_replace("&lt;","<", $html);
				$html = str_replace("&gt;",">", $html);
			}else{
				
				$hide_notice = '<div class="huoduan_hide_box" style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;"><img class="wxpic" align="right" src="'.$option->wechat_qrimg.'" style="width:150px;height:150px;margin-left:20px;display:inline;border:none" width="150" height="150"  alt="'.$option->wechat_name.'" /><span style="font-size:18px;">此处内容已经被作者隐藏，请输入验证码查看内容</span><form name="wechatFansForm" method="post" style="margin:10px 0;"><span class="yzts" style="font-size:18px;float:left;">验证码：</span><input name="tong'.'le'.'er_verifycode" id="verifycode" type="text" value="" style="border:none;float:left;width:80px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;" /><input id="verifybtn" style="border:none;float:left;width:80px; height:32px; line-height:32px; padding:0 5px; background-color:#F60; text-align:center; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="submit" value="提交查看" /></form><div style="clear:left;"></div><span style="color:#00BF30">请关注本站微信公众号，回复“<span style="color:blue">'.$option->wechat_keyword.'</span>”，获取验证码。在微信里搜索“<span style="color:blue">'.$option->wechat_name.'</span>”或者“<span style="color:blue">'.$option->wechat_account.'</span>”或者微信扫描右侧二维码都可以关注本站微信公众号。</span><div class="cl"></div></div>'.$vtips;
				$html = str_replace($hide_words[0], $hide_notice, $html);
			}
		}
		return $html;
	}
}