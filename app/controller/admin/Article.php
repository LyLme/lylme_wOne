<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\Article as AM;
use app\model\ArticleCategory as AC;
use app\model\CaseInfo;
use think\facade\View;

/**
 * 后台文章/新闻/案例统一管理
 */
class Article extends Base
{
    public function index()
    {
        $kw         = $this->request->get('keyword', '');
        $categoryId = (int)$this->request->get('category_id', 0);
        $type       = $this->request->get('type', 'news');
        $page       = (int)$this->request->get('page', 1);

        $query = AM::order('sort', 'asc')->order('id', 'desc');
        if (!empty($kw)) $query->whereLike('title', '%' . $kw . '%');
        if ($categoryId > 0) $query->where('category_id', $categoryId);
        $query->where('type', $type);

        $list = $query->paginate(['list_rows' => 15, 'page' => $page]);
        $categories = AC::where('type', $type)->order('sort', 'asc')->select()->toArray();

        // 案例文章加载 CaseInfo
        if ($type === 'case') {
            try {
                $articleIds = [];
                foreach ($list as $item) {
                    $articleIds[] = is_array($item) ? ($item['id'] ?? 0) : ($item->id ?? 0);
                }
                $articleIds = array_filter($articleIds);
                if (!empty($articleIds)) {
                    $caseInfos = CaseInfo::whereIn('article_id', $articleIds)->column('*', 'article_id');
                    View::assign('case_infos', $caseInfos);
                } else {
                    View::assign('case_infos', []);
                }
            } catch (\Throwable $e) {
                View::assign('case_infos', []);
            }
        }

        $typeLabel = $type === 'case' ? '案例管理' : '新闻管理';

        return View::fetch('admin/article/index', [
            'page_title'  => $typeLabel,
            'list'        => $list,
            'categories'  => $categories,
            'keyword'     => $kw,
            'category_id' => $categoryId,
            'type'        => $type,
        ]);
    }

    public function add()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $data = $this->request->post();
        $err  = $this->validateArticle($data);
        if ($err) return $this->error($err);

        $saveData = $this->buildArticleData($data);

        try {
            $a = new AM();
            $a->save($saveData);

            // 案例类型：保存 CaseInfo
            if ($data['type'] === 'case') {
                $caseData = $this->buildCaseData($data, $a->id);
                $ci = new CaseInfo();
                $ci->save($caseData);
            }

            return $this->success(['id' => $a->id], '添加成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '添加失败：'));
        }
    }

    public function update()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $id   = (int)$this->request->post('id', 0);
        $data = $this->request->post();
        if (empty($id)) return $this->error('参数错误');

        $err = $this->validateArticle($data);
        if ($err) return $this->error($err);

        $saveData = $this->buildArticleData($data);

        try {
            $a = AM::find($id);
            if (!$a) return $this->error('文章不存在');
            $a->save($saveData);

            // 案例类型：保存/更新 CaseInfo
            if ($data['type'] === 'case') {
                $caseData = $this->buildCaseData($data, $id);
                $ci = CaseInfo::where('article_id', $id)->find();
                if ($ci) {
                    $ci->save($caseData);
                } else {
                    $ci = new CaseInfo();
                    $ci->save($caseData);
                }
            }

            return $this->success(null, '更新成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '更新失败：'));
        }
    }

    public function delete()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $this->checkSuperAdmin();
        $id = $this->request->post('id', 0);
        if (empty($id)) return $this->error('参数错误');

        try {
            $a = AM::find($id);
            if (!$a) return $this->error('文章不存在');
            // 同时删除 CaseInfo
            CaseInfo::where('article_id', $id)->delete();
            $a->delete();
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '删除失败：'));
        }
    }

    public function toggleStatus()
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        // $this->checkSuperAdmin();
        $id     = $this->request->post('id', 0);
        $status = $this->request->post('status', 1);

        try {
            $a = AM::find($id);
            if (!$a) return $this->error('文章不存在');
            $a->status = (int)$status;
            $a->save();
            return $this->success(null, $status ? '已启用' : '已禁用');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '操作失败：'));
        }
    }

    public function upload()
    {
        $file = $this->request->file('file');
        if (!$file) return $this->error('请选择文件');

        try {
            $ext = strtolower($file->getOriginalExtension());
            $allow = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
            if (!in_array($ext, $allow)) return $this->error('不支持的文件类型');
            // 验证真实 MIME 类型
            $allowMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
            if (!in_array($file->getMime(), $allowMime)) return $this->error('不支持的文件类型');

            $subPath  = date('Ymd');
            $fileName = date('His') . '_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '.' . $ext;
            \think\facade\Filesystem::disk('public')->putFileAs($subPath, $file, $fileName);
            $url = '/static/uploads/' . $subPath . '/' . $fileName;
            return $this->success(['url' => $url, 'name' => $fileName], '上传成功');
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '上传失败：'));
        }
    }

    private function validateArticle(array $data): string
    {
        if (empty($data['title'])) return '文章标题不能为空';
        if (($data['type'] ?? 'news') !== 'case' && empty($data['category_id'])) return '请选择文章分类';
        return '';
    }

    private function buildArticleData(array $data): array
    {
        $type       = $data['type'] ?? 'news';
        $categoryId = (int)($data['category_id'] ?? 0);
        if ($type === 'case') {
            $categoryId = $this->resolveCaseCategoryId($data['industry'] ?? 'other', $categoryId);
        }
        return [
            'category_id' => $categoryId,
            'title'       => trim($data['title']),
            'type'        => $type,
            'summary'     => trim($data['summary'] ?? ''),
            'content'     => $data['content'] ?? '',
            'image'       => trim($data['image'] ?? ''),
            'sort'        => (int)($data['sort'] ?? 0),
            'status'      => (int)($data['status'] ?? 1),
        ];
    }

    /**
     * 根据行业分类自动解析案例文章分类
     */
    private function resolveCaseCategoryId(string $industry, int $fallback): int
    {
        $map = [
            'government' => '政府机关',
            'education'  => '教育机构',
            'enterprise' => '企业单位',
            'medical'    => '医疗机构',
            'finance'    => '金融机构',
            'other'      => '其他行业',
        ];
        $name = $map[$industry] ?? '';
        if ($name) {
            $cat = AC::where('type', 'case')->where('name', $name)->find();
            if ($cat) return $cat->id;
        }
        $cat = AC::where('type', 'case')->where('slug', $industry)->find();
        if ($cat) return $cat->id;
        if ($fallback > 0) return $fallback;
        $cat = AC::where('type', 'case')->order('sort', 'asc')->find();
        return $cat ? $cat->id : 0;
    }

    /**
     * 获取文章详情（供编辑时 AJAX 加载正文和案例图）
     */
    public function detail()
    {
        $id   = (int)$this->request->get('id', 0);
        $type = $this->request->get('type', '');
        if (empty($id)) return $this->error('参数错误');

        try {
            $a = AM::find($id);
            if (!$a) return $this->error('文章不存在');

            $result = [
                'id'      => $a->id,
                'content' => $a->content ?? '',
            ];

            // 案例类型：一并返回 CaseInfo
            if ($type === 'case' || $a->type === 'case') {
                $ci = CaseInfo::where('article_id', $id)->find();
                $result['case_info'] = $ci ? [
                    'industry'    => $ci->industry ?? 'other',
                    'client_name' => $ci->client_name ?? '',
                    'devices'     => $ci->devices ?? '',
                    'service_date'=> $ci->service_date ?? '',
                    'requirement' => $ci->requirement ?? '',
                    'solution'    => $ci->solution ?? '',
                    'result'      => $ci->result ?? '',
                    'cover'       => $ci->cover ?? '',
                    'images'      => $ci->images ?? [],
                ] : [
                    'industry' => 'other', 'client_name' => '', 'devices' => '',
                    'service_date' => '', 'requirement' => '', 'solution' => '',
                    'result' => '', 'cover' => '', 'images' => [],
                ];
            }

            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($this->errMsg($e, '获取失败：'));
        }
    }

    private function buildCaseData(array $data, int $articleId): array
    {
        $images = [];
        if (!empty($data['case_imgs'])) {
            if (is_array($data['case_imgs'])) {
                $images = $data['case_imgs'];
            } else {
                $decoded = json_decode($data['case_imgs'], true);
                $images = is_array($decoded) ? $decoded : explode(',', $data['case_imgs']);
            }
        }
        return [
            'article_id'  => $articleId,
            'industry'    => $data['industry'] ?? 'other',
            'client_name' => trim($data['client_name'] ?? ''),
            'devices'     => trim($data['devices'] ?? ''),
            'service_date'=> trim($data['service_date'] ?? ''),
            'requirement' => trim($data['requirement'] ?? ''),
            'solution'    => trim($data['solution'] ?? ''),
            'result'      => trim($data['result'] ?? ''),
            'cover'       => trim($data['cover'] ?? ''),
            'images'      => $images,
        ];
    }
}
