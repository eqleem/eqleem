<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class ClientUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a client by ID without tenantable global scopes.
     *
     * Client auth must resolve across tenant requests independently of
     * MorphTenantable scoping; tenant membership is enforced separately.
     */
    public function retrieveById($identifier): ?UserContract
    {
        $model = $this->createModel();

        return $model->newQueryWithoutScopes()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
    }

    /**
     * Retrieve a client by remember-me token without tenantable scopes.
     */
    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?UserContract
    {
        $model = $this->createModel();

        $retrievedModel = $model->newQueryWithoutScopes()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();

        if (! $retrievedModel) {
            return null;
        }

        $rememberToken = $retrievedModel->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $retrievedModel
            : null;
    }
}
