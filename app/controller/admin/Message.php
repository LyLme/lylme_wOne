<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\Message as MM;
use app\model\MessageReply;
use think\facade\View;

/**
 * 后台留言管理
 */
class Message extends Base
{
    public function index()
    {
        $isRead = $this->request->get('is_read', '');
        $page   = (int)$this->request->get('page', 1);

        $query = MM::order('id', 'desc');
        if ($isRead !== '') $query->where('is_read', (int)$isRead);

        $list = $query->paginate(['list_rows' => 15, 'page' => $page]);

        // 未读数量
        $unreadCount = MM::where('is_read', 0)->count();

        return View::fetch('admin/message/index', [
            'page_title'   => '留言管理',
            'list'         => $list,
            'is_read'      => $isRead,
            'unread_count' => $unreadCount,
        ]);
    }

    public function reply()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        // $this->checkSuperAdmin();
        $id      = $this->request->post('id', 0);
        $content = $this->request->post('reply_content', '');

        if (empty($id)) return $this->error('参数错误');
        if (empty($content)) return $this->error('回复内容不能为空');

        try {
            $m = MM::find($id);
            if (!$m) return $this->error('留言不存在');

            $adminId = $this->adminInfo['id'] ?? 0;
            $now     = date('Y-m-d H:i:s');

            // 更新主留言记录（兼容旧逻辑）
            $m->reply          = $content;
            $m->is_replied      = 1;
            $m->is_read         = 1;
            $m->reply_admin_id  = $adminId;
            $m->reply_time      = $now;
            $m->save();

            // 写入会话回复表
            $reply = new MessageReply();
            $reply->save([
                'message_id'  => $id,
                'sender_type' => 'admin',
                'content'     => $content,
                'admin_id'    => $adminId,
                'create_time' => $now,
            ]);

            return $this->success(null, '回复成功');
        } catch (\Exception $e) {
            return $this->error('回复失败：' . $e->getMessage());
        }
    }

    /**
     * 获取某条留言的完整会话历史
     */
    public function conversation()
    {
        $id = $this->request->get('id', 0);
        if (empty($id)) return $this->error('参数错误');

        $m = MM::find($id);
        if (!$m) return $this->error('留言不存在');

        // 构建会话记录
        $thread = [];

        // 用户原始留言
        $thread[] = [
            'sender'      => $m->name,
            'sender_type' => 'user',
            'content'     => $m->content,
            'time'        => $m->create_time,
        ];

        // 所有后续回复
        $replies = MessageReply::where('message_id', $id)->order('id', 'asc')->select();
        foreach ($replies as $r) {
            $thread[] = [
                'sender'      => $r->sender_type === 'admin' ? '客服' : $m->name,
                'sender_type' => $r->sender_type,
                'content'     => $r->content,
                'time'        => $r->create_time,
            ];
        }

        return $this->success(['thread' => $thread, 'message_id' => $id]);
    }

    public function markRead()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        // $this->checkSuperAdmin();
        $id = $this->request->post('id', 0);
        if (empty($id)) return $this->error('参数错误');

        try {
            $m = MM::find($id);
            if (!$m) return $this->error('留言不存在');
            $m->is_read = 1;
            $m->save();
            return $this->success(null, '已标记为已读');
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $this->checkSuperAdmin(); //禁止员工删除
        $id = $this->request->post('id', 0);
        if (empty($id)) return $this->error('参数错误');

        try {
            $m = MM::find($id);
            if (!$m) return $this->error('留言不存在');
            // 级联删除会话记录
            MessageReply::where('message_id', $id)->delete();
            $m->delete();
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
