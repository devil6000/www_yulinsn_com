<?php
/**
 * 视频管理
 * Created by PhpStorm.
 * User: appleimac
 * Date: 19/4/10
 * Time: 11:48
 */
if(!defined('IN_IA')){
    exit('Access Denied');
}

class Video_EweiShopV2Page extends PluginWebPage{

    public function main(){
        global $_W,$_GPC;
        $condition = 'uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if(!empty($_GPC['keyword'])){
            $condition .= ' and title like :title';
            $params[':title'] = "%{$_GPC['keyword']}%";
        }

        $pageIndex = max(1,intval($_GPC['page']));
        $pageSize = 20;
        $count = pdo_fetchcolumn('select count(id) from ' . tablename('ewei_shop_company_video') . ' where ' . $condition, $params);
        $pager = pagination($count, $pageIndex, $pageSize);

        $list = pdo_fetchall('select * from ' . tablename('ewei_shop_company_video') . ' where ' . $condition . ' order by id desc limit ' . ($pageIndex - 1) * $pageSize . ',' . $pageSize, $params);
        foreach ($list as &$item){
            $item['url'] = tomedia($item['url']);
        }
        unset($item);

        include $this->template('company/video/index');
    }

    public function add(){
        $this->post();
    }

    public function edit(){
        $this->post();
    }

    public function delete(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
        }

        $items = pdo_fetchall('SELECT id FROM ' . tablename('ewei_shop_company_video') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_delete('ewei_shop_company_video', array('id' => $item['id']));
        }

        show_json(1, array('url' => referer()));
    }

    protected function post(){
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $item = pdo_fetch('select * from ' . tablename('ewei_shop_company_video') . ' where id=:id', array(':id' => $id));
        if($_W['ispost']){
            $data['title'] = $_GPC['title'];
            $data['url'] = $_GPC['url'];
            if(!empty($item)){
                pdo_update('ewei_shop_company_video', $data, array('id' => $id));
            }else{
                $data['uniacid'] = $_W['uniacid'];
                $data['createtime'] = time();
                pdo_insert('ewei_shop_company_video', $data);
            }

            show_json(1, array('url' => webUrl('company/video')));
        }

        include $this->template('company/video/post');
    }
}