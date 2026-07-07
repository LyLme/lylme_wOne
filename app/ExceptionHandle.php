<?php
declare(strict_types=1);

namespace app;

use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者上报数据）
     */
    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): Response
    {
        // 请求参数验证异常
        if ($e instanceof ValidateException) {
            return json([
                'code' => 422,
                'msg'  => $e->getError(),
                'data' => [],
            ]);
        }

        // 调试模式显示详细错误
        if (env('APP_DEBUG')) {
            return parent::render($request, $e);
        }

        // 生产环境友好提示
        $code = $e->getCode() ?: 500;
        $message = match (true) {
            $e instanceof HttpException => $e->getMessage(),
            default => '系统异常，请稍后重试',
        };

        // API请求返回JSON
        if ($request->isAjax() || str_starts_with($request->pathinfo(), 'api')) {
            return json([
                'code' => $code,
                'msg'  => $message,
                'data' => [],
            ]);
        }

        // 普通请求返回错误页面
        return Response::create($message, 'view', 500)
            ->header(['Content-Type' => 'text/html']);
    }
}
