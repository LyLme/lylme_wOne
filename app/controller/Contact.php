<?php
declare(strict_types=1);

namespace app\controller;

use app\controller\FrontBase;
use app\model\Message;
use app\model\MessageReply;
use think\facade\View;

/**
 * 前台联系我们控制器
 */
class Contact extends FrontBase
{
    public function index()
    {
        $contactInfo = [
            'address' => $this->contact_address ?: '大理市下关x号',
            'phone'   => $this->contact_phone ?: '0872-8888888',
            'email'   => $this->contact_email ?: 'service@daliblueprint.com',
            'hours'   => $this->contact_hours ?: '周一至周五 8:00-18:00',
            'map_src' => $this->siteConfig['map_src'] ?? '',
            'wechat_qrcode' => $this->wechat_qrcode ?: '',
        ];
        return View::fetch('index/contact', [
            'contact_info'       => $contactInfo,
            'contact_info_list'  => $this->contactInfoList ?? [],
            'page_title'         => '联系我们 - ' . $this->site_name,
        ]);
    }

    public function message()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 1, 'msg' => '非法请求']);
        }
        $data = $this->request->post();
        $captchaCode = $data['captcha'] ?? '';
        if (empty($captchaCode) || !captcha_check($captchaCode)) {
            return json(['code' => 1, 'msg' => '验证码错误']);
        }
        $validate = [
            'name'    => 'require|chs|max:20',
            'phone'   => 'require',
            'content' => 'require|max:500',
        ];
        $messages = [
            'name.require'    => '请输入您的姓名',
            'name.chs'        => '姓名只能包含中文',
            'name.max'        => '姓名最多20个字符',
            'phone.require'  => '请输入联系电话',
            'content.require' => '请输入留言内容',
            'content.max'    => '留言内容最多500个字符',
        ];
        $validateObj = new \think\Validate();
        $validateObj->rule($validate)->message($messages);
        if (!$validateObj->check($data)) {
            return json(['code' => 1, 'msg' => $validateObj->getError()]);
        }
        // 单独正则验证手机/座机号
        if (!preg_match('/^(1[3-9]\d{9}|(0\d{2,3}-?)?\d{7,8})$/', $data['phone'])) {
            return json(['code' => 1, 'msg' => '请输入正确的手机或座机号码']);
        }
        $saveData = [
            'visitor_id' => $data['visitor_id'] ?? '',
            'name'       => $data['name'],
            'phone'      => $data['phone'],
            'contact'    => $data['contact'] ?? '',
            'content'    => $data['content'],
            'source'     => 'website',
            'is_read'    => 0,
        ];
        try {
            $message = new Message();
            $message->save($saveData);
            async_notify(function () use ($data) {
                \app\service\Notification::sendMessage([
                    'name'        => $data['name'],
                    'phone'       => $data['phone'],
                    'content'     => $data['content'],
                    'create_time' => date('Y-m-d H:i:s'),
                ]);
            });
            return json(['code' => 0, 'msg' => '留言提交成功，我们将尽快与您联系！']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '提交失败：' . $e->getMessage()]);
        }
    }

    public function followUp()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 1, 'msg' => '非法请求']);
        }
        $messageId = $this->request->post('message_id', 0);
        $content   = $this->request->post('content', '');
        if (empty($messageId)) return json(['code' => 1, 'msg' => '参数错误']);
        if (empty($content) || mb_strlen($content) > 500) {
            return json(['code' => 1, 'msg' => '回复内容不能为空且不超过500字']);
        }
        try {
            $m = Message::find($messageId);
            if (!$m) return json(['code' => 1, 'msg' => '留言不存在']);
            $reply = new MessageReply();
            $reply->save([
                'message_id'  => $messageId,
                'sender_type' => 'user',
                'content'     => $content,
                'create_time' => date('Y-m-d H:i:s'),
            ]);
            $m->is_read    = 0;
            $m->update_time = date('Y-m-d H:i:s');
            $m->save();
            async_notify(function () use ($m, $content) {
                \app\service\Notification::sendMessage([
                    'name'        => $m->name,
                    'phone'       => $m->phone,
                    'content'     => '用户追评：' . $content,
                    'create_time' => date('Y-m-d H:i:s'),
                ]);
            });
            return json(['code' => 0, 'msg' => '追评成功，客服将尽快回复您！']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '操作失败：' . $e->getMessage()]);
        }
    }

    public function myMessages()
    {
        $visitorId = $this->request->get('visitor_id', '');
        $phone     = $this->request->get('phone', '');
        if (empty($visitorId) && empty($phone)) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }
        $query = Message::order('id', 'desc')->limit(50);
        $query->where(function ($q) use ($visitorId, $phone) {
            if (!empty($visitorId)) $q->where('visitor_id', $visitorId);
            if (!empty($visitorId) && !empty($phone)) {
                $q->whereOr('phone', $phone);
            } elseif (!empty($phone)) {
                $q->where('phone', $phone);
            }
        });
        $list = $query->field('id,content,reply,reply_time,create_time,name')->select();
        $result = [];
        foreach ($list as $item) {
            $thread = [];
            $thread[] = [
                'sender_type' => 'user',
                'content'     => $item->content,
                'time'        => $item->create_time,
            ];
            $replies = MessageReply::where('message_id', $item->id)->order('id', 'asc')->select();
            foreach ($replies as $r) {
                $thread[] = [
                    'sender_type' => $r->sender_type,
                    'content'     => $r->content,
                    'time'        => $r->create_time,
                ];
            }
            $result[] = [
                'message_id'  => $item->id,
                'content'     => $item->content,
                'reply'       => $item->reply,
                'reply_time'  => $item->reply_time,
                'create_time' => $item->create_time,
                'name'        => $item->name,
                'thread'      => $thread,
            ];
        }
        return json(['code' => 0, 'data' => $result]);
    }
}
