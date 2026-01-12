<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    /**
     * Inject AuthService via constructor
     */
    public function __construct(protected readonly AuthService $authService){}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        try {
            $result = $this->authService->registerUser($request->only(['name', 'email', 'password']));

            return ApiResponseService::success(
                ['user' => $result['user']],
                'User registered successfully',
                201
            );

        } catch (\Exception $e) {
            return ApiResponseService::serverError($e->getMessage());
        }
    }

    /**
     * Login user and return JWT token
     */
    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->loginUser(
                $request->email,
                $request->password
            );

            $tokenData = $this->authService->formatTokenResponse($result['token']);

            return ApiResponseService::success(
                $tokenData,
                'Login successful'
            );

        } catch (\Exception $e) {
            // Check if it's an invalid credentials error
            if (str_contains($e->getMessage(), 'Invalid email or password')) {
                return ApiResponseService::unauthorized($e->getMessage());
            }
            
            return ApiResponseService::serverError($e->getMessage());
        }
    }

    /**
     * Get the authenticated user
     */
    public function me(Request $request)
    {
        try {
            // Validate token first
            $this->authService->validateToken($request->bearerToken());

            // Get user details
            $result = $this->authService->getUserDetails();

            return ApiResponseService::success(
                ['user' => $result['user']],
                'User profile retrieved successfully'
            );

        } catch (TokenExpiredException $e) {
            return ApiResponseService::unauthorized($e->getMessage());
        } catch (TokenInvalidException $e) {
            return ApiResponseService::unauthorized($e->getMessage());
        } catch (\Exception $e) {
            return ApiResponseService::serverError($e->getMessage());
        }
    }

    /**
     * Logout user (invalidate token)
     */
    public function logout(Request $request)
    {
        try {
            // Validate token first
            $this->authService->validateToken($request->bearerToken());

            // Logout
            $result = $this->authService->logoutUser();

            return ApiResponseService::success(
                null,
                $result['message']
            );

        } catch (TokenExpiredException $e) {
            return ApiResponseService::unauthorized($e->getMessage());
        } catch (TokenInvalidException $e) {
            return ApiResponseService::unauthorized($e->getMessage());
        } catch (\Exception $e) {
            return ApiResponseService::serverError($e->getMessage());
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh(Request $request)
    {
        try {
            // Validate token first
            $this->authService->validateToken($request->bearerToken());

            // Refresh token
            $result = $this->authService->refreshToken();

            $tokenData = $this->authService->formatTokenResponse($result['token']);

            return ApiResponseService::success(
                $tokenData,
                'Token refreshed successfully'
            );

        } catch (TokenExpiredException $e) {
            return ApiResponseService::unauthorized($e->getMessage());
        } catch (TokenInvalidException $e) {
            return ApiResponseService::unauthorized($e->getMessage());
        } catch (\Exception $e) {
            return ApiResponseService::serverError($e->getMessage());
        }
    }
}