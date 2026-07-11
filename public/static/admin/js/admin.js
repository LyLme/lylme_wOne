/**
 * 后台管理系统通用脚本 v3
 * 采用事件委托 + 直接绑定双保险
 */
$(function () {
    'use strict';

    console.log('[admin.js] 初始化开始');

    // ===== 侧边栏 =====
    // 创建遮罩层
    var $overlay = $('<div class="sidebar-overlay"></div>').appendTo('body');

    $overlay.on('click', function () {
        $('#adminSidebar').removeClass('mobile-show');
        $(this).removeClass('show');
    });

    $('#sidebarToggle').on('click', function () {
        if (window.innerWidth <= 992) {
            $('#adminSidebar').toggleClass('mobile-show');
            $overlay.toggleClass('show');
        } else {
            $('#adminSidebar').toggleClass('collapsed');
            localStorage.setItem('admin_sidebar_collapsed', $('#adminSidebar').hasClass('collapsed') ? '1' : '0');
        }
    });

    if (localStorage.getItem('admin_sidebar_collapsed') === '1' && window.innerWidth > 992) {
        $('#adminSidebar').addClass('collapsed');
    }

    // ===== Toastr 默认配置 =====
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-center',
            timeOut: 3000,
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };
    }

    // ====================================================================
    //  图片上传组件
    // ====================================================================
    $(document).on('click', '.img-upload-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $btn = $(this);
        var $container = $btn.closest('.img-upload-group');
        var $input = $container.find('.img-upload-val');
        var $preview = $container.find('.img-upload-preview img');
        var $clear = $container.find('.img-upload-clear');

        var fileInput = $('<input type="file" accept="image/*" style="display:none;">');
        $('body').append(fileInput);

        fileInput.on('change', function () {
            var file = this.files[0];
            if (!file) { fileInput.remove(); return; }

            var fd = new FormData();
            fd.append('file', file);

            var origHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: (typeof ADMIN_PATH !== 'undefined' ? ADMIN_PATH : '/admin') + '/config/upload',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (res) {
                    if (res.code === 0 && res.data && res.data.url) {
                        $input.val(res.data.url);
                        $preview.attr('src', res.data.url).show();
                        $clear.show();
                        $container.addClass('has-img');
                        if (typeof toastr !== 'undefined') toastr.success('上传成功');
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(res.msg || '上传失败');
                        else alert(res.msg || '上传失败');
                    }
                },
                error: function () {
                    if (typeof toastr !== 'undefined') toastr.error('网络错误，上传失败');
                    else alert('网络错误，上传失败');
                },
                complete: function () {
                    $btn.prop('disabled', false).html(origHtml);
                }
            });

            fileInput.remove();
        });

        fileInput.trigger('click');
    });

    // 清除图片
    $(document).on('click', '.img-upload-clear', function (e) {
        e.preventDefault();
        var $container = $(this).closest('.img-upload-group');
        $container.find('.img-upload-val').val('');
        $container.find('.img-upload-preview img').attr('src', '').hide();
        $container.removeClass('has-img');
        $(this).hide();
    });

    // ====================================================================
    //  Tab 切换（双保险：直接绑定 + 委托）
    // ====================================================================
    function switchTab(targetId) {
        console.log('[tab] 切换到:', targetId);

        // 激活 tab 按钮
        $('.config-tabs .nav-link').removeClass('active');
        $('.config-tabs .nav-link[data-tab="' + targetId + '"]').addClass('active');

        // 切换内容面板（config-pane 非 tab-pane，避免 Bootstrap 冲突）
        $('.config-pane').removeClass('active').hide();
        var $pane = $('#' + targetId);
        if ($pane.length) {
            $pane.addClass('active').show();
        }

        // 记录
        try { localStorage.setItem('admin_config_tab', targetId); } catch (e) {}
    }

    // 绑定 tab 点击
    $(document).on('click', '.config-tabs .nav-link[data-tab]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var target = $(this).data('tab');
        if (target) switchTab(target);
    });

    // 恢复上次 tab
    var savedTab;
    try { savedTab = localStorage.getItem('admin_config_tab'); } catch (e) {}
    if (savedTab && $('#' + savedTab).length) {
        switchTab(savedTab);
    }

    // ====================================================================
    //  表格行操作
    // ====================================================================
    function renumberTable($table) {
        $table.find('tbody tr').each(function (i) {
            $(this).find('td:first').text(i + 1);
        });
    }

    // 删除行
    $(document).on('click', '.del-row-btn', function () {
        var $tr = $(this).closest('tr');
        var $tbody = $tr.parent();
        if ($tbody.find('tr').length > 1) {
            $tr.remove();
            renumberTable($tbody.closest('table'));
        } else {
            if (typeof toastr !== 'undefined') toastr.warning('至少保留一行');
        }
    });

    // 添加行
    $(document).on('click', '.add-row-btn', function () {
        var targetId = $(this).data('target');
        var $table = $('#' + targetId);
        if (!$table.length) return;

        var $tbody = $table.find('tbody');
        var $lastRow = $tbody.find('tr:last');

        var $newRow = $lastRow.clone();
        $newRow.find('input').val('');
        $newRow.find('textarea').val('');
        $newRow.find('.img-upload-preview img').attr('src', '').hide();
        $newRow.find('.img-upload-clear').hide();
        $newRow.find('.img-upload-group').removeClass('has-img');
        // 关键的隐藏字段（team-avatar, pt-logo）也要清空
        $newRow.find('.team-avatar').val('');
        // 重置图标选择器预览
        $newRow.find('.icon-preview i').attr('class', 'fa fa-circle');
        // 重置颜色选择器及预览
        $newRow.find('.color-picker').val('#1A5FDC');
        $newRow.find('.color-preview').css('background-color', 'transparent');
        // 服务分类：清空特性标签
        if (targetId === 'serviceTypesTable') {
            $newRow.find('.st-feature-tag').remove();
        }

        $tbody.append($newRow);
        renumberTable($table);
    });

    // ====================================================================
    //  保存分组配置
    // ====================================================================
    // 通用保存 AJAX，支持令牌失效自动重试一次 + 可选自定义回调
    // options: { url, timeout, onSuccess(res), onError(res), onAjaxError() }
    function doSaveAjax($btn, origHtml, data, retryCount, options) {
        retryCount = retryCount || 0;
        options = options || {};
        $.ajax({
            url: options.url || (typeof ADMIN_PATH !== 'undefined' ? ADMIN_PATH : '/admin') + '/config/save',
            type: 'POST',
            data: data,
            dataType: 'json',
            timeout: options.timeout,
            success: function (res, textStatus, jqXHR) {
                console.log('[save] 响应:', res);
                if (res.code === 0) {
                    if (options.onSuccess) {
                        options.onSuccess(res);
                    } else {
                        if (typeof toastr !== 'undefined') toastr.success(res.msg || '保存成功');
                        else alert('保存成功');
                        $btn.prop('disabled', false).html(origHtml);
                    }
                } else if (res.code === 1001 && retryCount < 1) {
                    // 令牌失效：立即用响应头中的新 token 更新 meta / 表单，并同步到 data 后重试
                    var newToken = jqXHR.getResponseHeader('X-CSRF-TOKEN');
                    if (newToken) {
                        $('meta[name="csrf-token"]').attr('content', newToken);
                        $('input[name="__token__"]').val(newToken);
                        if (data && typeof data === 'object') {
                            data.__token__ = newToken;
                        }
                        console.log('[save] 令牌已刷新，自动重试...');
                    }
                    doSaveAjax($btn, origHtml, data, retryCount + 1, options);
                } else {
                    if (options.onError) {
                        options.onError(res);
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(res.msg || '保存失败');
                        else alert('保存失败: ' + (res.msg || ''));
                    }
                    $btn.prop('disabled', false).html(origHtml);
                }
            },
            error: function (xhr, status, err) {
                console.error('[save] AJAX错误:', status, err);
                if (options.onAjaxError) {
                    options.onAjaxError(xhr, status, err);
                } else {
                    if (typeof toastr !== 'undefined') toastr.error('网络错误，请重试');
                    else alert('网络错误: ' + status);
                }
                $btn.prop('disabled', false).html(origHtml);
            }
        });
    }

    $(document).on('click', '.save-group-btn', function () {
        var $btn = $(this);
        var group = $btn.data('group');
        var $pane = $('#' + group);

        console.log('[save] 保存分组:', group);

        if (!$pane.length) {
            console.error('[save] 找不到面板:', group);
            if (typeof toastr !== 'undefined') toastr.error('找不到对应面板');
            return;
        }

        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 保存中...');

        // 确保 Summernote 富文本内容已同步到 textarea
        $pane.find('.rich-email-editor').each(function () {
            if ($(this).next('.note-editor').length) {
                $(this).val($(this).summernote('code'));
            }
        });

        // 收集数据
        var data = {};

        // 1. 普通表单字段（有 name 属性的 input/textarea/select）
        $pane.find('input[name], textarea[name], select[name]').each(function () {
            var name = $(this).attr('name');
            if (name && name.indexOf('[]') === -1) {
                var $el = $(this);
                if ($el.attr('type') === 'checkbox') {
                    data[name] = $el.is(':checked') ? '1' : '0';
                } else {
                    data[name] = $el.val();
                }
            }
        });

        // 2. 表格 JSON 数据（根据分组收集）
        collectTableData(group, data);

        console.log('[save] 发送数据:', data);

        // 联系方式分组：同时保存配置字段和联系列表
        if (group === 'tab-contact') {
            saveContactGroup($btn, origHtml, data);
            return;
        }

        // 3. AJAX 提交（支持令牌失效自动重试一次）
        doSaveAjax($btn, origHtml, data, 0);
    });

    /**
     * 保存联系方式分组：先保存配置字段，再批量保存联系列表
     */
    function saveContactGroup($btn, origHtml, configData) {
        function resetBtn() {
            if ($btn && $btn.length) {
                $btn.prop('disabled', false).html(origHtml || '<i class="fa fa-save"></i> 保存联系方式');
            }
        }

        // 收集联系列表
        var items = [];
        $('#contactInfoTbody tr').each(function () {
            var type = $(this).find('.ci-type').val().trim();
            var value = $(this).find('.ci-value').val().trim();
            if (!type || !value) return;
            items.push({
                sort: parseInt($(this).find('.ci-sort').val()) || 0,
                type: type,
                value: value,
                contact_person: $(this).find('.ci-person').val().trim()
            });
        });

        var apiBase = (typeof ADMIN_PATH !== 'undefined' ? ADMIN_PATH : '/admin');

        // 先保存配置字段（支持令牌自动重试）
        doSaveAjax($btn, origHtml, configData, 0, {
            onSuccess: function () {
                // 再批量保存联系列表，必须带最新 __token__，也支持令牌失效重试
                var token = $('input[name="__token__"]').val() || $('meta[name="csrf-token"]').attr('content') || '';
                doSaveAjax($btn, origHtml, {
                    items: JSON.stringify(items),
                    __token__: token
                }, 0, {
                    url: apiBase + '/contact-info/batch-save',
                    onSuccess: function (res2) {
                        if (typeof toastr !== 'undefined') toastr.success(res2.msg || '联系方式保存成功');
                        else alert(res2.msg || '联系方式保存成功');
                        if (typeof loadContactInfo === 'function') {
                            try { loadContactInfo(); } catch (e) {}
                        }
                        resetBtn();
                    },
                    onError: function (res2) {
                        if (typeof toastr !== 'undefined') toastr.error(res2.msg || '联系方式保存失败');
                        else alert('联系方式保存失败: ' + (res2.msg || ''));
                        resetBtn();
                    },
                    onAjaxError: function () {
                        if (typeof toastr !== 'undefined') toastr.error('联系方式保存请求失败');
                        else alert('联系方式保存请求失败');
                        resetBtn();
                    }
                });
            },
            onError: function (res) {
                if (typeof toastr !== 'undefined') toastr.error(res.msg || '基础信息保存失败');
                else alert('基础信息保存失败: ' + (res.msg || ''));
                resetBtn();
            },
            onAjaxError: function () {
                if (typeof toastr !== 'undefined') toastr.error('基础信息保存请求失败');
                else alert('基础信息保存请求失败');
                resetBtn();
            }
        });
    }

    // ====================================================================
    //  收集表格数据（各分组）
    // ====================================================================
    function collectTableData(group, data) {
        switch (group) {
            case 'tab-nav':
                data.nav_menus = collectMenuTable('#navMenusTable', ['menu-name', 'menu-url', 'menu-active'], ['name', 'url', 'active']);
                break;

            case 'tab-home':
                data.home_core_data    = collectCoreTable('#coreDataTable', ['core-number', 'core-unit', 'core-label'], ['number', 'unit', 'label']);
                data.home_services     = collectCoreTable('#homeServicesTable', ['svc-icon', 'svc-title', 'svc-desc', 'svc-url', 'svc-color'], ['icon', 'title', 'desc', 'url', 'color']);
                data.home_advantages   = collectCoreTable('#homeAdvantagesTable', ['adv-icon', 'adv-title', 'adv-desc'], ['icon', 'title', 'desc']);
                break;

            case 'tab-service':
                data.service_types = collectServiceTypes();
                data.service_detail_content = collectServiceDetailContent();
                data.service_flow_steps = collectCoreTable('#flowStepsTable', ['flow-icon', 'flow-title', 'flow-desc'], ['icon', 'title', 'desc']);
                break;

            case 'tab-about':
                data.about_timeline       = collectCoreTable('#timelineTable', ['tl-year', 'tl-title', 'tl-desc'], ['year', 'title', 'desc']);
                data.about_qualifications = collectCoreTable('#qualificationsTable', ['qual-name', 'qual-icon'], ['name', 'icon']);
                data.about_team           = collectTeamTable();
                data.about_bidding        = collectCoreTable('#biddingTable', ['bid-title', 'bid-desc', 'bid-icon'], ['title', 'desc', 'icon']);
                break;

            case 'tab-footer':
                data.case_industries      = collectKVTable('#caseIndustriesTable', 'ci-key', 'ci-name');
                data.footer_service_links = collectCoreTable('#footerServiceTable', ['fs-name', 'fs-url'], ['name', 'url']);
                data.footer_quick_links   = collectCoreTable('#footerQuickTable', ['fq-name', 'fq-url'], ['name', 'url']);
                break;

            case 'tab-notify':
                data.notification_channels = collectNotifyChannels();
                break;
        }
    }

    /** 收集通知渠道 */

    /** 通用表格收集：class列表 → JSON数组 */
    function collectCoreTable(tableId, classes, keys) {
        var items = [];
        $(tableId + ' tbody tr').each(function () {
            var item = {};
            for (var i = 0; i < keys.length; i++) {
                var val = $(this).find('.' + classes[i]).val() || '';
                item[keys[i]] = val;
            }
            // 只要第一个 key 有值就收集
            if (item[keys[0]]) items.push(item);
        });
        return JSON.stringify(items);
    }

    /** 导航菜单收集 */
    function collectMenuTable(tableId, classes, keys) {
        return collectCoreTable(tableId, classes, keys);
    }

    /** 团队收集（含头像上传字段） */
    function collectTeamTable() {
        var items = [];
        $('#teamTable tbody tr').each(function () {
            // 同步上传值到隐藏字段
            var avatarVal = $(this).find('.img-upload-val').val() || '';
            $(this).find('.team-avatar').val(avatarVal);

            var name = $(this).find('.team-name').val() || '';
            var title = $(this).find('.team-title').val() || '';
            var desc = $(this).find('.team-desc').val() || '';
            if (name) items.push({ name: name, title: title, desc: desc, avatar: avatarVal });
        });
        return JSON.stringify(items);
    }

    /** 键值对收集（如 case_industries） */
    function collectKVTable(tableId, keyClass, valClass) {
        var obj = {};
        $(tableId + ' tbody tr').each(function () {
            var k = $(this).find('.' + keyClass).val();
            var v = $(this).find('.' + valClass).val();
            if (k && v) obj[k] = v;
        });
        return JSON.stringify(obj);
    }

    /** 服务分类收集（key => object） */
    function collectServiceTypes() {
        var obj = {};
        $('#serviceTypesTable tbody tr').each(function () {
            var key = ($(this).find('.st-key').val() || '').trim();
            if (!key) return;
            var features = [];
            $(this).find('.st-feature-tag input').each(function () {
                features.push($(this).val());
            });
            obj[key] = {
                name: ($(this).find('.st-name').val() || '').trim(),
                icon: ($(this).find('.st-icon').val() || '').trim(),
                desc: ($(this).find('.st-desc').val() || '').trim(),
                color: ($(this).find('.st-color').val() || '').trim(),
                features: features
            };
        });
        return JSON.stringify(obj);
    }

    /** 服务详情内容收集 */
    function collectServiceDetailContent() {
        var obj = {};
        $('#serviceDetailAccordion .sd-category').each(function () {
            var key = $(this).data('key');
            var sections = [];
            $(this).find('.sd-section').each(function () {
                var title = ($(this).find('.sd-section-title').val() || '').trim();
                if (!title) return;
                var items = [];
                $(this).find('.sd-item-table tbody tr').each(function () {
                    var itemTitle = ($(this).find('.sdi-title').val() || '').trim();
                    if (!itemTitle) return;
                    items.push({
                        icon: ($(this).find('.sdi-icon').val() || '').trim(),
                        title: itemTitle,
                        desc: ($(this).find('.sdi-desc').val() || '').trim()
                    });
                });
                sections.push({ title: title, items: items });
            });
            obj[key] = {
                intro: ($(this).find('.sd-intro').val() || '').trim(),
                sections: sections
            };
        });
        return JSON.stringify(obj);
    }

    // ====================================================================
    //  服务分类：特性标签交互
    // ====================================================================
    $(document).on('keydown', '.st-feature-input', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var val = $(this).val().trim();
            if (!val) return;
            var tag = $('<span class="badge bg-light text-dark border st-feature-tag">' + val + ' <i class="fa fa-times ms-1" style="cursor:pointer;"></i><input type="hidden" value="' + val + '"></span>');
            $(this).before(tag);
            $(this).val('');
        }
    });
    $(document).on('click', '.st-feature-tag .fa-times', function () {
        $(this).closest('.st-feature-tag').remove();
    });

    // ====================================================================
    //  服务详情：添加/删除区块、条目
    // ====================================================================
    $(document).on('click', '.add-section-btn', function () {
        var html = '<div class="sd-section card mb-3 border">' +
            '<div class="card-body">' +
            '<div class="mb-2"><label class="form-label small text-muted">区块标题</label>' +
            '<div class="input-group"><input type="text" class="form-control sd-section-title" placeholder="如：服务优势">' +
            '<button type="button" class="btn btn-outline-danger remove-section-btn"><i class="fa fa-trash"></i></button></div></div>' +
            '<table class="table table-bordered table-sm sd-item-table"><thead class="table-light"><tr><th style="width:150px;">图标</th><th>标题</th><th>描述</th><th style="width:60px;">操作</th></tr></thead><tbody></tbody></table>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary add-item-btn"><i class="fa fa-plus"></i> 添加条目</button>' +
            '</div></div>';
        $(this).closest('.sd-sections').append(html);
    });
    $(document).on('click', '.remove-section-btn', function () {
        $(this).closest('.sd-section').remove();
    });
    $(document).on('click', '.add-item-btn', function () {
        var tbody = $(this).siblings('.sd-item-table').find('tbody');
        var html = '<tr><td><div class="input-group input-group-sm"><span class="input-group-text icon-preview"><i class="fa fa-circle"></i></span>' +
            '<input type="text" class="form-control sdi-icon" placeholder="fa-check"><button type="button" class="btn btn-outline-secondary icon-picker-btn"><i class="fa fa-smile"></i></button></div></td>' +
            '<td><input type="text" class="form-control form-control-sm sdi-title"></td>' +
            '<td><input type="text" class="form-control form-control-sm sdi-desc"></td>' +
            '<td><button type="button" class="btn btn-sm btn-danger del-row-btn"><i class="fa fa-trash"></i></button></td></tr>';
        tbody.append(html);
    });

    // 颜色选择器联动文本框
    $(document).on('input', '.color-picker', function () {
        var $this = $(this);
        $this.siblings('.svc-color, .st-color').val($this.val());
        $this.siblings('.color-preview').css('background-color', $this.val());
    });
    $(document).on('input', '.svc-color, .st-color', function () {
        var $this = $(this);
        $this.siblings('.color-picker').val($this.val());
        $this.siblings('.color-preview').css('background-color', $this.val());
    });

    // 图标输入联动预览
    $(document).on('input', '.svc-icon, .st-icon, .sdi-icon', function () {
        var icon = $(this).val() || 'fa-circle';
        $(this).closest('td, .input-group').find('.icon-preview i').attr('class', 'fa ' + icon);
    });

    // ====================================================================
    //  消息通知：渠道开关联动 + 测试发送 + 模板重置
    // ====================================================================

    /** 收集通知渠道 */
    function collectNotifyChannels() {
        var channels = [];
        $('.notify-channel-switch:checked').each(function () {
            channels.push($(this).val());
        });
        return JSON.stringify(channels);
    }

    // 渠道开关 → 显示/隐藏对应配置卡片
    $(document).on('change', '.notify-channel-switch', function () {
        var val = $(this).val();
        var cfgId = '#notify-' + val + '-cfg';
        if ($(this).is(':checked')) {
            $(cfgId).slideDown(200);
        } else {
            $(cfgId).slideUp(200);
        }
    });

    // 重置模板：从后端获取默认模板
    $(document).on('click', '.reset-template-btn', function () {
        var $btn = $(this);
        var target = $btn.data('target');
        var type   = $btn.data('type');
        var $textarea = $('textarea[name="' + target + '"]');
        if (!$textarea.length) return;

        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: (typeof ADMIN_PATH !== 'undefined' ? ADMIN_PATH : '/admin') + '/config/get-default-templates',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.code === 0 && res.data && res.data[type] !== undefined) {
                    var defaultText = res.data[type];
                    // 邮件模板使用 Summernote 编辑器
                    if (type && type.indexOf('email_') === 0 && $textarea.next('.note-editor').length) {
                        $textarea.summernote('code', defaultText);
                    } else {
                        $textarea.val(defaultText);
                    }
                    if (typeof toastr !== 'undefined') toastr.success('模板已恢复为默认值');
                } else {
                    if (typeof toastr !== 'undefined') toastr.error(res.msg || '获取默认模板失败');
                }
            },
            error: function () {
                if (typeof toastr !== 'undefined') toastr.error('网络错误，获取默认模板失败');
            },
            complete: function () {
                $btn.prop('disabled', false).html(origHtml);
            }
        });
    });

    // 测试发送
    $(document).on('click', '#testNotifyBtn', function () {
        var $btn = $(this);
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> 发送中...');

        var channel = $('#testChannel').val();
        $.ajax({
            url: (typeof ADMIN_PATH !== 'undefined' ? ADMIN_PATH : '/admin') + '/config/test-notify',
            type: 'POST',
            data: { channel: channel },
            dataType: 'json',
            timeout: 30000,  // 30秒超时，防止无限等待
            success: function (res) {
                if (res.code === 0) {
                    if (typeof toastr !== 'undefined') toastr.success(res.msg || '测试消息发送成功');
                    else alert(res.msg || '发送成功');
                } else {
                    if (typeof toastr !== 'undefined') toastr.error(res.msg || '发送失败，请检查配置');
                    else alert('发送失败: ' + (res.msg || ''));
                }
            },
            error: function (xhr, status, error) {
                var errMsg = '网络错误，请重试';
                if (status === 'timeout') {
                    errMsg = '请求超时，可能是 SMTP 服务器不可达，请检查：\n1. 服务器地址和端口是否正确\n2. 加密方式是否匹配\n3. 防火墙是否放行对应端口';
                }
                if (typeof toastr !== 'undefined') toastr.error(errMsg);
                else alert(errMsg);
            },
            complete: function () {
                $btn.prop('disabled', false).html(origHtml);
            }
        });
    });

    console.log('[admin.js] 初始化完成');
});