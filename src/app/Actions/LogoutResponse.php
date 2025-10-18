<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // guardで分岐（今後拡張）
        if ($request->is('admin/*')) {
            return redirect('/admin/login');
        }

        // 通常ユーザー
        return redirect('/login');
    }
}
