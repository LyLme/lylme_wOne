<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 留言回复会话模型（多轮对话）
 */
class MessageReply extends Model
{
    protected $name = 'message_reply';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = false;

    protected $type = [
        'id'         => 'integer',
        'message_id' => 'integer',
        'admin_id'   => 'integer',
    ];

    protected $field = [
        'id', 'message_id', 'sender_type', 'content', 'admin_id', 'create_time'
    ];
}
