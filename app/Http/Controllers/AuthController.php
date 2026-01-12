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
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Inject AuthService via constructor
     */
    public function __construct(protected readonly AuthService $authService){}

    #[OA\Post(
        path: "/auth/register",
        summary: "Register a new user",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User registered successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "User registered successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(
                                    property: "user",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "name", type: "string", example: "John Doe"),
                                        new OA\Property(property: "email", type: "string", example: "john@example.com")
                                    ],
                                    type: "object"
                                )
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Server error")
        ]
    )]
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

    #[OA\Post(
        path: "/auth/login",
        summary: "Login user",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Login successful"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "access_token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGc..."),
                                new OA\Property(property: "token_type", type: "string", example: "bearer"),
                                new OA\Property(property: "expires_in", type: "integer", example: 3600)
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Invalid credentials"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
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

    #[OA\Get(
        path: "/auth/me",
        summary: "Get authenticated user profile",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "User profile retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "User profile retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(
                                    property: "user",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "name", type: "string", example: "John Doe"),
                                        new OA\Property(property: "email", type: "string", example: "john@example.com")
                                    ],
                                    type: "object"
                                )
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - Invalid or expired token")
        ]
    )]
    public function me()
    {
        try {
            // Get user details
            $result = $this->authService->getUserDetails();

            return ApiResponseService::success(
                ['user' => $result['user']],
                'User profile retrieved successfully'
            );
        } catch (\Exception $e) {
            return ApiResponseService::serverError($e->getMessage());
        }
    }

    #[OA\Post(
        path: "/auth/logout",
        summary: "Logout user",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logout successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Logout successful")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - Invalid or expired token")
        ]
    )]
    public function logout(Request $request)
    {
        try {
            // Logout
            $result = $this->authService->logoutUser();

            return ApiResponseService::success(
                null,
                $result['message']
            );
        } catch (\Exception $e) {
            return ApiResponseService::serverError($e->getMessage());
        }
    }

    #[OA\Post(
        path: "/auth/refresh",
        summary: "Refresh JWT token",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Token refreshed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Token refreshed successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "access_token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGc..."),
                                new OA\Property(property: "token_type", type: "string", example: "bearer"),
                                new OA\Property(property: "expires_in", type: "integer", example: 3600)
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - Invalid or expired token")
        ]
    )]
    public function refresh()
    {
        try {
            // Refresh token
            $result = $this->authService->refreshToken();

            $tokenData = $this->authService->formatTokenResponse($result['token']);

            return ApiResponseService::success(
                $tokenData,
                'Token refreshed successfully'
            );
        } catch (\Exception $e) {
            return ApiResponseService::serverError($e->getMessage());
        }
    }
}