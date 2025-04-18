<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        return $this->authService->register($request);
    }

    public function login(Request $request)
    {
        return $this->authService->login($request);
    }

    public function logout(Request $request)
    {
        return $this->authService->logout($request);
    }

    public function user(Request $request)
    {
        return $this->authService->getUser($request);
    }
    
    public function changePassword(Request $request)
    {
        return $this->authService->changePassword($request);
    }
    
    public function forgotPassword(Request $request)
    {
        return $this->authService->forgotPassword($request);
    }
    
    public function resetPassword(Request $request)
    {
        return $this->authService->resetPassword($request);
    }
    
    public function sendOtp(Request $request)
    {
        return $this->authService->sendOtp($request);
    }
    
    public function verifyOtp(Request $request)
    {
        return $this->authService->verifyOtp($request);
    }
}