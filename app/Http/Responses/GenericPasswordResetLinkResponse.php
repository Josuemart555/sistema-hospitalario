<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;

class GenericPasswordResetLinkResponse implements FailedPasswordResetLinkRequestResponse
{
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? response()->json(['message' => trans('passwords.sent')])
            : back()->withInput($request->only('email'))->with('status', trans('passwords.sent'));
    }
}
