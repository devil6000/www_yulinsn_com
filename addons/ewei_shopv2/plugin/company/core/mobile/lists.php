<?php
/**
 * 列表
 * Created by PhpStorm.
 * User: administer
 * Date: 2019/4/6
 * Time: 12:15
 */
if(!defined('IN_IA')){
    exit('Access Denied');
}

class Lists_EweiShopV2Page extends PluginMobilePage{

    public function get_list(){
        global $_W,$_GPC;
        $ccate = intval($_GPC['ccate']);
        $pageIndex = max(1,intval($_GPC['page']));
        $pageSize = 6;

        $articles = pdo_fetchall('select * from ' . tablename('site_article') . ' where ccate=:ccate and uniacid=:uniacid order by id desc limit ' . ($pageIndex - 1) * $pageSize . ',' . $pageSize, array(':ccate' => $ccate, ':uniacid' => $_W['uniacid']));
        foreach ($articles as &$item){
            $item['createtime'] = date('Y-m-d', $item['createtime']);
        }
        unset($item);

        show_json(1,array('list' => $articles, 'pagesize' => $pageSize));
    }
}