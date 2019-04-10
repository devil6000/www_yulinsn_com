<?php
/**
 * 设置
 * Created by PhpStorm.
 * User: devil
 * Date: 2019/4/5
 * Time: 10:30
 */
if(!defined('IN_IA')){
    exit('Access Denied');
}

class Set_EweiShopV2Page extends PluginWebPage {

    public function main(){
        global $_W,$_GPC;

        $item = pdo_fetch('select * from ' . tablename('ewei_shop_company_set') . ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
        if($item){
            $item['articles'] = unserialize($item['articles']);
            $item['villages'] = unserialize($item['villages']);
            $item['banaras'] = unserialize($item['banaras']);
        }

        if($_W['ispost']){
            $data = array(
                'articles' => serialize($_GPC['cates']),
                'villages' => serialize($_GPC['village']),
                'banaras'  => serialize($_GPC['banaras'])
            );
            if(!empty($item)){
                pdo_update('ewei_shop_company_set', $data, array('uniacid' => $_W['uniacid']));
            }else{
                $data['uniacid'] = $_W['uniacid'];
                pdo_insert('ewei_shop_company_set', $data);
            }

            show_json(1, array('url' => webUrl('company/set')));
        }

        $cates = pdo_fetchall('select id,name from' . tablename('site_category') . ' where uniacid=:uniacid and parentid > 0', array(':uniacid' => $_W['uniacid']));

        include $this->template();
    }
}