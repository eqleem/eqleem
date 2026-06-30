<?php

namespace App\Actions;
 
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Actions\CreateUser;
use App\Actions\CreateTenant;
 
class RegisterTenant
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return [
            'tenant_name' => 'required|min:2|max:200',
            'tenant_handle' => 'required|min:2|max:100|alpha_dash:ascii|unique:tenants,handle',
            'user_name' => 'required|min:2|max:200',
            'user_email' => 'required|email|unique:users,email',
            'user_password' => 'required|min:6|max:200',
        ];
    }

    public function handle(array $data): array
    {
        $this->fill($data);
        
        $validatedData = $this->validateAttributes();
         
        $user = CreateUser::run([
            'name' => $validatedData['user_name'],
            'email' => $validatedData['user_email'],
            'password' => $validatedData['user_password'],
        ]); 

        if($user) {
            $tenant = CreateTenant::run([
                'tenant_name' => $validatedData['tenant_name'],
                'tenant_handle' => $validatedData['tenant_handle'],
                'email' => $validatedData['user_email'],
                'user_id' => $user->id,
            ]); 
        }

        if($tenant) {
            $user->update([
                'current_tenant_id' => $tenant->id,
            ]);
        }

        return ['tenant' => $tenant ?? null, 'user' => $user ?? null];
    }

}
