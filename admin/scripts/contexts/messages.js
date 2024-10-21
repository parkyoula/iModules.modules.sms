/**
 * 이 파일은 아이모듈 SMS모듈의 일부입니다. (https://www.imodules.io)
 *
 * SMS 관리화면을 구성한다.
 *
 * @file /modules/sms/admin/scripts/contexts/messages.ts
 * @author youlapark <youlapark@naddle.net>
 * @license MIT License
 * @modified 2024. 10. 15.
 */
Admin.ready(async () => {
    const me = Admin.getModule('sms');
    return new Aui.Tab.Panel({
        id: 'messages-context',
        title: (await me.getText('admin.contexts.messages')),
        border: false,
        layout: 'fit',
        iconClass: 'mi mi-mail',
        topbar: [
            new Aui.Form.Field.Search({
                id: 'keyword',
                width: 200,
                emptyText: (await me.getText('admin.keyword')),
                handler: async (keyword) => {
                    const context = Aui.getComponent('messages-context');
                    const messages = context.getActiveTab().getItemAt(0);
                    if (keyword.length > 0) {
                        messages.getStore().setParam('keyword', keyword);
                    }
                    else {
                        messages.getStore().setParam('keyword', null);
                    }
                    messages.getStore().loadPage(1);
                },
            }),
        ],
        items: [],
        listeners: {
            render: async (tab) => {
                const results = await me.getMomo().viewers.get('messages');
                if (results.success == true) {
                    for (const viewer of results.records) {
                        tab.append(new Aui.Grid.Panel({
                            id: viewer.viewer_id,
                            title: viewer.title,
                            iconClass: viewer.icon,
                            layout: 'fit',
                            border: false,
                            selection: { selectable: true, type: 'check', multiple: false },
                            bottombar: new Aui.Grid.Pagination([
                                new Aui.Button({
                                    iconClass: 'mi mi-refresh',
                                    handler: (button) => {
                                        const grid = button.getParent().getParent();
                                        grid.getStore().reload();
                                    },
                                }),
                            ]),
                            store: new Aui.Store.Remote({
                                url: me.getProcessUrl('messages'),
                                primaryKeys: ['message_id'],
                                filters: viewer.filters,
                                sorters: viewer.sorters ?? { sended_at: 'DESC' },
                                limit: 50,
                                remoteSort: true,
                                remoteFilter: true,
                            }),
                            columns: [
                                {
                                    text: (await me.getText('admin.columns.receiver')),
                                    dataIndex: 'name',
                                    textAlign: 'center',
                                    width: 120,
                                },
                                {
                                    text: (await me.getText('admin.columns.receiveNumber')),
                                    dataIndex: 'cellphone',
                                    textAlign: 'center',
                                    width: 170,
                                },
                                // 발신자에 대해 생각해봐야 함
                                {
                                    text: (await me.getText('admin.columns.sender')),
                                    dataIndex: 'sender',
                                    width: 120,
                                },
                                {
                                    text: (await me.getText('admin.columns.senderNumber')),
                                    dataIndex: 'sended_cellphone',
                                    textAlign: 'center',
                                    width: 170,
                                },
                                {
                                    text: (await me.getText('admin.columns.content')),
                                    dataIndex: 'content',
                                    minWidth: 250,
                                    flex: 1,
                                },
                                {
                                    text: (await me.getText('admin.columns.date')),
                                    dataIndex: 'sended_at',
                                    width: 250,
                                    sortable: true,
                                    filter: new Aui.Grid.Filter.Date({ format: 'timestamp' }),
                                    renderer: (value) => {
                                        return Format.date('Y.m.d(D) H:i:s', value);
                                    },
                                },
                                {
                                    text: (await me.getText('admin.columns.type')),
                                    dataIndex: 'type',
                                    width: 120,
                                    textAlign: 'center',
                                    sortable: true,
                                    filter: new Aui.Grid.Filter.List({
                                        store: new Aui.Store.Local({
                                            fields: ['display', 'value'],
                                            records: [
                                                ['SMS', 'SMS'],
                                                ['LMS', 'LMS'],
                                            ],
                                        }),
                                        displayField: 'display',
                                        valueField: 'value',
                                        multiple: true,
                                    }),
                                    renderer: (value) => {
                                        const types = { SMS: 'SMS', LMS: 'LMS' };
                                        return types[value];
                                    },
                                },
                                {
                                    text: (await me.getText('admin.columns.status')),
                                    sortable: true,
                                    width: 120,
                                    textAlign: 'center',
                                    filter: new Aui.Grid.Filter.List({
                                        dataIndex: 'status',
                                        store: new Aui.Store.Local({
                                            fields: ['display', 'value'],
                                            records: [
                                                ['성공', 'TRUE'],
                                                ['실패', 'FALSE'],
                                            ],
                                        }),
                                    }),
                                    renderer: (_value, record, $dom) => {
                                        const status = record.get('status');
                                        $dom.addClass(status);
                                        const types = {
                                            'TRUE': '성공',
                                            'FALSE': '실패',
                                        };
                                        return types[status];
                                    },
                                },
                            ],
                            listeners: {
                                openItem: (record) => {
                                    console.log(record.get('message_id'));
                                    me.messages.show(record.get('message_id'));
                                },
                            },
                        }));
                    }
                }
                if (Admin.getContextSubUrl(0) !== null) {
                    tab.active(Admin.getContextSubUrl(0));
                }
                else {
                    tab.active(0);
                }
                tab.setDisabled(false);
            },
            active: (panel) => {
                const grid = panel;
                if (grid.getStore().isLoaded() == false) {
                    grid.getStore().loadPage(1);
                }
                const keyword = Aui.getComponent('keyword');
                keyword.setValue(grid.getStore().getParam('keyword') ?? null);
                Aui.getComponent('messages-context').properties.setUrl();
            },
        },
        setUrl: () => {
            const context = Aui.getComponent('messages-context');
            const tab = context.getActiveTab().getId();
            if (Admin.getContextSubUrl(0) !== tab) {
                Admin.setContextSubUrl('/' + tab);
            }
        },
        reloadAll: async () => {
            const context = Aui.getComponent('messages-context');
            const reloads = [];
            for (const grid of context.getItems()) {
                reloads.push(grid.getStore().reload());
            }
            await Promise.all(reloads);
        },
    });
});
