<?php
/**
 * LaraClassified - Classified Ads Web Application
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: https://bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from CodeCanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\ResetPasswordRequest;
use App\Helpers\Auth\Traits\ResetsPasswordsForEmail;
use App\Helpers\Auth\Traits\ResetsPasswordsForPhone;
use App\Http\Resources\UserResource;

/**
 * @group Authentication
 */
class ResetPasswordController extends BaseController
{
    use ResetsPasswordsForEmail, ResetsPasswordsForPhone;
    
    /**
     * Reset password
	 *
	 * @bodyParam login string required The user's login (Can be email address or phone number). Example: john.doe@domain.tld
	 * @bodyParam password string required The user's password. Example: js!X07$z61hLA
	 * @bodyParam password_confirmation string required The confirmation of the user's password. Example: js!X07$z61hLA
	 * @bodyParam captcha_key string Key generated by the CAPTCHA endpoint calling (Required if the CAPTCHA verification is enabled from the Admin panel).
     *
	 * @param \App\Http\Requests\ResetPasswordRequest $request
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
	 */
    public function reset(ResetPasswordRequest $request)
    {
        // Get the right login field
        $field = getLoginField($request->input('login'));
        $request->merge([$field => $request->input('login')]);
        if ($field != 'email') {
            $request->merge(['email' => $request->input('login')]);
        }
        
        // Go to the custom process (Phone)
        if ($field == 'phone') {
            return $this->resetForPhone($request);
        }
        
        // Go to the core process (Email)
        return $this->resetForEmail($request);
    }
	
	/**
	 * Create an API token for the User
	 *
	 * @param $user
	 * @param null $deviceName
	 * @param null $message
	 * @return \Illuminate\Http\JsonResponse
	 */
    protected function createUserApiToken($user, $deviceName = null, $message = null)
	{
		// Revoke previous tokens
		$user->tokens()->delete();
		
		// Create the API access token
		$deviceName = $deviceName ?? 'Desktop Web';
		$token = $user->createToken($deviceName);
		
		$data = [
			'success' => true,
			'message' => $message,
			'result'  => new UserResource($user),
			'extra'   => [
				'authToken' => $token->plainTextToken,
				'tokenType'   => 'Bearer',
			],
		];
		
		return $this->apiResponse($data);
	}
}
