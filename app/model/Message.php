<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 留言模型
 */
class Message extends Model
{
    protected $name = 'message';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'id'              => 'integer',
        'is_read'         => 'integer',
        'is_replied'      => 'integer',
        'reply_admin_id'  => 'integer',
    ];

    protected $field = [
        'id', 'visitor_id', 'name', 'phone', 'contact', 'content',
        'reply', 'reply_admin_id', 'reply_time',
        'is_read', 'is_replied', 'source', 'create_time', 'update_time'
    ];
    
    /**
     * 搜索器 - 是否已读
     */
    public function searchIsReadAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('is_read', $value);
        }
    }
    
    /**
     * 搜索器 - 来源
     */
    public function searchSourceAttr($query, $value)
    {
        if (!empty($value)) {
            $query->where('source', $value);
        }
    }
}
