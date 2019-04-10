<?php
/**文章列表
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 22:11
 */
if(!defined('IN_IA')){
    exit('Access Denied');
}

class Articles_EweiShopV2Page extends PluginMobileLoginPage{

    public function main(){
        global $_W,$_GPC;
        $cates = pdo_fetchcolumn('select articles from ' . tablename('ewei_shop_company_set') . ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
        $cates = unserialize($cates);
        if(empty($cates)){
            $this->message('请设置分类', $this->createMobileUrl('index'), 'error');
            //show_json(0,array('message' => '请设置文章分类', 'url' => $this->createMobileUrl('index')));
            exit;
        }
        foreach ($cates as $key => $cate){
            $cate = pdo_fetch('select * from ' . tablename('site_category') . ' where id=:id and uniacid=:uniacid', array(':id' => $cate, ':uniacid' => $_W['uniacid']));
            $cates[$key] = $cate;
        }

        include $this->template('company/articles/index');
    }
}