/**
 * Created by appleimac on 19/4/3.
 */
define(['core', 'tpl'], function(core, tpl) {
    var modal = {
        page: 1,
        ccate: ''
    };
    modal.init = function(params) {
        modal.ccate = params.ccate;
        modal.page = 1;
        $('.fui-content').infinite({
            onLoading: function() {
                modal.getList()
            }
        });
        if (modal.page == 1) {
            $('#container').html('');
            modal.getList()
        }
        $('[data-toggle="nav-button"]').on('click',function () {
            var ccate = $(this).data('id');
            modal.changeTab(ccate);
        });
        /*
        FoxUI.tab({
            container: $('#tab'),
            handlers: {
                status: function() {
                    modal.changeTab('')
                },
                status0: function() {
                    modal.changeTab(0)
                },
                status1: function() {
                    modal.changeTab(1)
                },
                status3: function() {
                    modal.changeTab(3)
                }
            }
        })
        */
    };
    modal.changeTab = function(ccate) {
        $('.fui-content').infinite('init');
        $('.content-empty').hide(), $('.infinite-loading').show(), $('#container').html('');
        modal.page = 1, modal.ccate = ccate, modal.getList()
    };
    modal.loading = function() {
        modal.page++
    };
    modal.getList = function() {
        core.json('company/lists/get_list', {
            page: modal.page,
            ccate: modal.ccate
        }, function(ret) {
            var result = ret.result;
            if (result.list.length <= 0 && modal.page == 1) {
                $('.content-empty').show();
                $('.fui-content').infinite('stop')
            } else {
                $('.content-empty').hide();
                $('.fui-content').infinite('init');
                if (result.list.length <= 0 || result.list.length < result.pagesize) {
                    $('.fui-content').infinite('stop')
                }
            }
            modal.page++;
            core.tpl('#container', 'get_list', result, modal.page > 1);
            FoxUI.according.init()
        })
    };
    return modal
});
