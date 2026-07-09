<?php
// +----------------------------------------------------------------------
// | 表单令牌验证 + 自动刷新
// | POST/PUT/DELETE 验证通过后自动生成新 token 通过 X-CSRF-TOKEN 响应头下发；
// | 验证失败时也会刷新 token（防止一直卡在旧 token 上）。
// | GET/HEAD/OPTIONS 直接放行，不刷新 token（避免覆盖模板 {:token()} 生成的正确值）。
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

class RefreshTokenCheck
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param string|null $token 令牌名称
     * @return Response
     */
    public function handle(Request $request, Closure $next, ?string $token = null): Response
    {
        $token = $token ?: '__token__';
        $method = $request->method();

        // GET/HEAD/OPTIONS 不验证也不刷新，避免覆盖模板渲染时 {:token()} 生成的 token
        if (\in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $check = $request->checkToken($token);

        if (false === $check) {
            // 验证失败也生成新 token，防止客户端一直卡在旧 token 上
            $newToken = $request->buildToken($token);
            // 返回 JSON 错误并携带新 token，前端 ajaxComplete 会自动更新 meta 标签
            return json(['code' => 0, 'message' => '令牌数据无效，请重新提交'])
                ->header(['X-CSRF-TOKEN' => $newToken]);
        }

        $response = $next($request);

        // 验证通过后自动刷新 token，前端可连续提交
        $newToken = $request->buildToken($token);
        $response->header(['X-CSRF-TOKEN' => $newToken]);

        return $response;
    }
}
