<?php

namespace D076\SanctumRefreshTokens\Services;

use D076\SanctumRefreshTokens\DTOs\LoginDTO;
use D076\SanctumRefreshTokens\DTOs\TokensDTO;
use D076\SanctumRefreshTokens\Helpers\ConfigHelper;
use D076\SanctumRefreshTokens\Models\AuthenticatableUser;
use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService implements IAuthService
{
    public function __construct(
        protected ?AuthenticatableUser $user = null,
        protected ?LoginDTO $loginDTO = null
    ) {
    }

    public function setUser(AuthenticatableUser $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setCredentials(LoginDTO $loginDTO): self
    {
        $this->loginDTO = $loginDTO;

        return $this;
    }

    /**
     * @throws AuthenticationException
     */
    public function login(bool $remember = false): TokensDTO
    {
        if ($this->loginDTO) {
            $this->user = $this->getUserFromCredentials();
            $remember = $remember ?: $this->loginDTO->remember;
        }

        if ($this->user) {
            $accessTokenExpiresAt = ConfigHelper::getAccessTokenExpiresAt();
            $refreshTokenExpiresAt = ConfigHelper::getRefreshTokenExpiresAt($remember);

            return app(ITokenService::class, ['user' => $this->user])
                ->createTokens($accessTokenExpiresAt, $refreshTokenExpiresAt);
        }

        throw new AuthenticationException();
    }

    /**
     * @throws AuthenticationException
     */
    public function logout(): void
    {
        if (! $this->user) {
            throw new AuthenticationException();
        }

        app(ITokenService::class, ['user' => $this->user])->deleteCurrentTokens();
    }

    /**
     * @throws AuthenticationException
     */
    public function refresh(string $refreshToken): TokensDTO
    {
        $personalRefreshToken = PersonalRefreshToken::findToken($refreshToken);

        if (! $personalRefreshToken?->tokenable) {
            throw new AuthenticationException();
        }

        $tokenService = new TokenService($personalRefreshToken->tokenable);
        [$accessTokenExpiresAt, $refreshTokenExpiresAt] = $this->getExpiresAtRefresh($personalRefreshToken);

        $personalRefreshToken->delete();

        return $tokenService->createTokens($accessTokenExpiresAt, $refreshTokenExpiresAt);
    }

    public function resetPassword(string $password): IAuthService
    {
        if ($this->user) {
            $passwordField = 'password';
            if (method_exists($this->user, 'getPasswordField')) {
                $passwordField = $this->user->getPasswordField();
            }

            $this->user->$passwordField = Hash::make($password);
            $this->user->save();

            app(ITokenService::class, ['user' => $this->user])->deleteAllTokens();
        }

        return $this;
    }

    protected function getExpiresAtRefresh(PersonalRefreshToken $personalRefreshToken): array
    {
        $accessTokenExpiresAt = $personalRefreshToken->accessToken?->expires_at
            ? now()->addMinutes(
                $personalRefreshToken->accessToken->expires_at->diffInMinutes(
                    $personalRefreshToken->accessToken->created_at,
                    true
                )
            )
            : ConfigHelper::getAccessTokenExpiresAt();
        $refreshTokenExpiresAt = $personalRefreshToken->expires_at
            ? now()->addMinutes(
                $personalRefreshToken->expires_at->diffInMinutes($personalRefreshToken->created_at, true)
            )
            : ConfigHelper::getRefreshTokenExpiresAt(false);

        return [$accessTokenExpiresAt, $refreshTokenExpiresAt];
    }

    protected function getUserFromCredentials(): ?AuthenticatableUser
    {
        if ($this->loginDTO) {
            /** @var AuthenticatableUser $model */
            $model = $this->loginDTO->model;

            $emailField = 'email';
            if (method_exists($model, 'getEmailField')) {
                $emailField = $model->getEmailField();
            }

            /** @var AuthenticatableUser $user */
            $user = $model::query()->where($emailField, $this->loginDTO->email)->first();

            return $user && Hash::check($this->loginDTO->password, $user->password ?? Hash::make(''))
                ? $user
                : null;
        }

        return null;
    }
}
