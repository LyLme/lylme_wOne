<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 案例扩展信息模型
 */
class CaseInfo extends Model
{
    protected $name = 'case_info';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $json = ['images'];
    protected $jsonAssoc = true;
    
    protected $type = [
        'id'           => 'integer',
        'article_id'   => 'integer',
        'client_name'  => 'string',
        'industry'     => 'string',
        'devices'      => 'string',
        'service_date' => 'string',
        'requirement'  => 'string',
        'solution'     => 'string',
        'result'       => 'string',
        'cover'        => 'string',
    ];
    
    /**
     * 关联文章
     */
    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'id');
    }
}
