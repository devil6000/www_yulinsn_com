<?php
/**内容管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/30
 * Time: 16:00
 */
if(!defined('IN_IA')){
    exit('Access Denied');
}

class Index_EweiShopV2Page extends PluginWebPage{

    public function main(){
        global $_W,$_GPC;

        $pageIndex = max(1,intval($_GPC['page']));
        $pageSize = 20;

        $count = pdo_fetchcolumn('select count(id) from ' . tablename('ewei_shop_company_page') . ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
        $list = pdo_fetchall('select * from ' . tablename('ewei_shop_company_page') . ' where uniacid=:uniacid order by id desc limit ' . ($pageIndex - 1) * $pageSize . ',' . $pageSize, array(':uniacid' => $_W['uniacid']));
        if($list){
            foreach ($list as $key => $item){
                $htmlCatesStr = '';
                $cates = unserialize($item['cates']);
                foreach ($cates as $cate){
                    $title = pdo_fetchcolumn('select name from ' . tablename('site_category') . ' where uniacid=:uniacid and id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $cate));
                    if(empty($htmlCatesStr)){
                        $htmlCatesStr = $title;
                    }else{
                        $htmlCatesStr .= ',' . $title;
                    }
                }
                $item['cates_str'] = $htmlCatesStr;
                $list[$key] = $item;
            }
        }

        include $this->template('company/page/index');
    }

    public function add(){
        $this->post();
    }

    public function edit(){
        $this->post();
    }

    protected function post(){
        global $_W,$_GPC;

        $id = intval($_GPC['id']);
        if($_W['ispost']){
            $data = array(
                'title' => $_GPC['title'],
                'cates' => serialize($_GPC['cates']),
                'type'  => intval($_GPC['type'])
            );

            if(!empty($id)){
                pdo_update('ewei_shop_company_page', $data, array('id' => $id));
            }else{
                $data['uniacid'] = $_W['uniacid'];
                pdo_insert('ewei_shop_company_page', $data);
            }

            show_json(1, array('url' => webUrl('company')));
        }

        $cates = pdo_fetchall('select id,name from' . tablename('site_category') . ' where uniacid=:uniacid and parentid > 0', array(':uniacid' => $_W['uniacid']));

        $item = pdo_fetch('select * from ' . tablename('ewei_shop_company_page') . ' where id=:id', array(':id' => $id));
        if($item){
            $item['cates'] = unserialize($item['cates']);
        }

        include $this->template('company/page/post');
    }

    public function delete(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);

        if (empty($id)) {
            $id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;
        }

        $items = pdo_fetchall('SELECT id FROM ' . tablename('ewei_shop_company_page') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);

        foreach ($items as $item) {
            pdo_delete('ewei_shop_company_page', array('id' => $item['id']));
        }

        show_json(1, array('url' => referer()));
    }
}