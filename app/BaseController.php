<?php
declare(strict_types=1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     */
    protected \think\Request $request;

    /**
     * 应用实例
     */
    protected App $app;

    /**
     * 是否批量验证
     */
    protected bool $batchValidate = true;

    /**
     * 验证失败是否抛出异常
     */
    protected bool $failException = true;

    /**
     * 构造方法
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $app->request;
        // 控制器初始化
        $this->initialize();
    }

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        // CSRF Token 由视图模板中的 {:token()} 函数在渲染时生成，
        // 此时 SessionInit 中间件已完成 session 初始化。
    }

    /**
     * 验证数据
     * @access protected
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @return bool
     */
    protected function validate(array $data, string|array $validate, array $message = [], bool $batch = false): bool
    {
        try {
            if (is_array($validate)) {
                $v = new Validate();
                $v->rule($validate);
            } else {
                if (str_contains($validate, '.')) {
                    [$validate, $scene] = explode('.', $validate);
                }
                $class = str_contains($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
                $v = new $class();
                if (!empty($scene)) {
                    $v->scene($scene);
                }
            }
            $v->message($message);
            // 是否批量验证
            if ($batch || $this->batchValidate) {
                $v->batch(true);
            }
            return $v->failException($this->failException)->check($data);
        } catch (ValidateException $e) {
            return $e->getError();
        }
    }

    /**
     * 返回JSON成功数据
     */
    protected function success($data = null, string $msg = 'success', int $code = 0): \think\response\Json
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 返回JSON失败数据
     */
    protected function error(string $msg = 'error', int $code = 1, $data = null): \think\response\Json
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 返回分页JSON数据
     */
    protected function paginate($list): \think\response\Json
    {
        return json([
            'code'  => 0,
            'msg'   => 'success',
            'count' => $list->total(),
            'data'  => $list->items(),
        ]);
    }

    /**
     * 安全异常消息: 开发环境显示详细错误，生产环境显示友好提示
     */
    protected function errMsg(\Exception $e, string $prefix = ''): string
    {
        $detail = config('app.app_debug') ? $e->getMessage() : '系统繁忙，请稍后重试';
        return $prefix . $detail;
    }
}
