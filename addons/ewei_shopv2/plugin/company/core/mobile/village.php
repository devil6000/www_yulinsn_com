<?php
/**
 * 乡村旅游
 * Created by PhpStorm.
 * User: devil
 * Date: 2019/4/5
 * Time: 11:17
 */

if(!defined('IN_IA')){
    exit('Access Denied');
}

class Village_EweiShopV2Page extends PluginMobilePage{

    public function main(){
        global $_W,$_GPC;

        $villages = pdo_fetch('select villages,banaras from ' . tablename('ewei_shop_company_set') . ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
        $cates = unserialize($villages['villages']);
        if(empty($cates)){
            $this->message('请设置分类', $this->createMobileUrl('index'), 'error');
            exit;
        }
        foreach ($cates as $key => $cate){
            $cate = pdo_fetch('select id,name from ' . tablename('site_category') . ' where id=:id and uniacid=:uniacid', array(':id' => $cate, ':uniacid' => $_W['uniacid']));
            $cates[$key] = $cate;
        }
        $banaras = unserialize($villages['banaras']);

        include $this->template('company/village/index');
    }

    public function detail(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $article = pdo_fetch('select * from ' . tablename('site_article') . ' where id=:id', array(':id' => $id));
        $article['click'] += 1;
        pdo_update('site_article', array('click' => $article['click']), array('id' => $id, 'uniacid' => $_W['uniacid']));

        include $this->template('company/village/detail');
    }

}