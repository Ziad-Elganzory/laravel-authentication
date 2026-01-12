<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthService
{
    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function registerUser(array $data): array
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            return [
                'success' => true,
                'user' => $user
            ];

        } catch (\Exception $e) {
            throw new \Exception('User registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login user and generate token
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws \Exception
     */
    public function loginUser(string $email, string $password): array
    {
        try {
            $credentials = [
                'email' => $email,
                'password' => $password
            ];

            if (!$token = auth()->attempt($credentials)) {
                throw new \Exception('Invalid email or password');
            }

            return [
                'success' => true,
                'token' => $token,
                'user' => auth()->user()
            ];

        } catch (JWTException $e) {
            throw new \Exception('Could not create token: ' . $e->getMessage());
        }
    }

    /**
     * Logout user (invalidate current token)
     *
     * @return array
     * @throws \Exception
     */
    public function logoutUser(): array
    {
        try {
            auth()->logout();

            return [
                'success' => true,
                'message' => 'Successfully logged out'
            ];

        } catch (JWTException $e) {
            throw new \Exception('Failed to logout: ' . $e->getMessage());
        }
    }

    /**
     * Get authenticated user details
     *
     * @return array
     * @throws \Exception
     */
    public function getUserDetails(): array
    {
        try {
            $user = auth()->user();

            if (!$user) {
                throw new \Exception('User not found');
            }

            return [
                'success' => true,
                'user' => $user
            ];

        } catch (\Exception $e) {
            throw new \Exception('Failed to retrieve user details: ' . $e->getMessage());
        }
    }

    /**
     * Refresh the JWT token
     *
     * @return array
     * @throws TokenExpiredException
     * @throws TokenInvalidException
     * @throws \Exception
     */
    public function refreshToken(): array
    {
        try {
            $newToken = auth()->refresh();

            return [
                'success' => true,
                'token' => $newToken,
                'user' => auth()->user()
            ];

        } catch (TokenExpiredException $e) {
            throw new TokenExpiredException('Token has expired and cannot be refreshed');
        } catch (TokenInvalidException $e) {
            throw new TokenInvalidException('Token is invalid');
        } catch (JWTException $e) {
            throw new \Exception('Could not refresh token: ' . $e->getMessage());
        }
    }

    /**
     * Format token response
     *
     * @param string $token
     * @return array
     */
    public function formatTokenResponse(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ];
    }
}