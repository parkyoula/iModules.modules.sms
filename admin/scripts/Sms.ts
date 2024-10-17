/**
 * 이 파일은 아이모듈 SMS모듈의 일부입니다. (https://www.imodules.io)
 *
 * SMS 이벤트를 관리하는 클래스를 정의한다.
 *
 * @file /modules/sms/admin/scripts/message.ts
 * @author youlapark <youlapark@naddle.net>
 * @license MIT License
 * @modified 2024. 10. 15.
 */
namespace modules {
    export namespace sms {
        export namespace admin {
            export class Sms extends modules.admin.admin.Component {
                /**
                 * 모모 관리자 클래스를 가져온다.
                 *
                 * @return {modules.naddle.momo.admin.Momo} Momo
                 */
                getMomo(): modules.naddle.momo.admin.Momo {
                    return Admin.getModule('naddle/momo') as modules.naddle.momo.admin.Momo;
                }

                /**
                 * 메세지관리
                 */
                messages = {
                    /**
                     * 메세지 상세보기 기능을 가져온다.
                     */
                    show: async (message_id: string) => {
                        console.log('here! : ', message_id);
                        new Aui.Window({
                            title: '메세지 상세보기',
                            width: 600,
                            modal: true,
                            resizable: false,
                            items: [
                                new Aui.Form.Panel({
                                    border: false,
                                    layout: 'fit',
                                    readonly: true,
                                    items: [
                                        new Aui.Form.FieldSet({
                                            title: '문자 내역',
                                            items: [
                                                new Aui.Form.Field.Container({
                                                    direction: 'column',
                                                    items: [
                                                        new Aui.Form.Field.Text({
                                                            label: '전송시간',
                                                            name: 'sended_at',
                                                        }),
                                                        new Aui.Form.Field.TextArea({
                                                            label: '문자내용',
                                                            name: 'content',
                                                            readonly: false,
                                                        }),
                                                        new Aui.Form.Field.Text({
                                                            label: '발송타입',
                                                            name: 'type',
                                                        }),
                                                        new Aui.Form.Field.Text({
                                                            label: '성공여부',
                                                            name: 'status',
                                                        }),
                                                    ],
                                                }),
                                            ],
                                        }),
                                        new Aui.Form.FieldSet({
                                            title: '수신자 기본정보',
                                            items: [
                                                new Aui.Form.Field.Container({
                                                    direction: 'row',
                                                    items: [
                                                        new Aui.Form.Field.Text({
                                                            label: (await this.getText(
                                                                'admin.columns.receiver'
                                                            )) as string,
                                                            name: 'name',
                                                        }),
                                                        new Aui.Form.Field.Text({
                                                            label: (await this.getText(
                                                                'admin.columns.receiveNumber'
                                                            )) as string,
                                                            name: 'cellphone',
                                                        }),
                                                    ],
                                                }),
                                            ],
                                        }),
                                        new Aui.Form.FieldSet({
                                            title: '발신자 기본정보',
                                            items: [
                                                new Aui.Form.Field.Container({
                                                    direction: 'row',
                                                    items: [
                                                        new Aui.Form.Field.Text({
                                                            label: (await this.getText(
                                                                'admin.columns.sender'
                                                            )) as string,
                                                            name: 'sender',
                                                        }),
                                                        new Aui.Form.Field.Text({
                                                            label: (await this.getText(
                                                                'admin.columns.senderNumber'
                                                            )) as string,
                                                            name: 'sended_cellphone',
                                                        }),
                                                    ],
                                                }),
                                            ],
                                        }),
                                    ],
                                }),
                            ],
                            buttons: [
                                new Aui.Button({
                                    text: '취소',
                                    handler: (button) => {
                                        const window = button.getParent() as Aui.Window;
                                        window.close();
                                    },
                                }),
                                new Aui.Button({
                                    text: '확인',
                                    buttonClass: 'confirm',
                                    handler: async (button) => {
                                        const window = button.getParent() as Aui.Window;
                                        const form = button.getParent().getItemAt(0) as Aui.Form.Panel;
                                        const results = await form.submit({
                                            url: this.getProcessUrl('message'),
                                            params: { message_id: message_id },
                                        });

                                        if (results.success == true) {
                                            Aui.Message.show({
                                                title: Aui.getErrorText('INFO'),
                                                message: Aui.printText('actions.saved'),
                                                icon: Aui.Message.INFO,
                                                buttons: Aui.Message.OK,
                                                handler: async () => {
                                                    const contacts = Aui.getComponent(
                                                        'messages-contacts'
                                                    ) as Aui.Grid.Panel;
                                                    if (contacts !== null) {
                                                        await contacts.getStore().reload();
                                                        if (contacts instanceof Aui.Grid.Panel) {
                                                            contacts.select({ contact_id: results.contact_id });
                                                        }
                                                    }

                                                    Aui.Message.close();
                                                    window.close();
                                                },
                                            });
                                        }
                                    },
                                }),
                            ],
                            listeners: {
                                show: async (window) => {
                                    if (message_id !== null) {
                                        const form = window.getItemAt(0) as Aui.Form.Panel;
                                        const results = await form.load({
                                            url: this.getProcessUrl('message'),
                                            params: { message_id: message_id },
                                        });

                                        if (results.success == false) {
                                            window.close();
                                        }
                                    }
                                },
                            },
                        }).show();
                    },
                };
            }
        }
    }
}
