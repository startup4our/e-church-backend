<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterChurchRequest;
use App\Services\Interfaces\IAreaService;
use App\Services\Interfaces\IChurchService;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Log;

class AuthController extends Controller
{

    protected IAreaService $areaService;
    protected IPermissionService $permissionService;
    protected IChurchService $churchService;

    private string $ufs = 'AC,AL,AP,AM,BA,CE,DF,ES,GO,MA,MT,MS,MG,PA,PB,PR,PE,PI,RJ,RN,RS,RO,RR,SC,SP,SE,TO';

    public function __construct(IAreaService $areaService, IPermissionService $permissionService, IChurchService $churchService)
    {
        $this->areaService = $areaService;
        $this->permissionService = $permissionService;
        $this->churchService = $churchService;
    }
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (! $token = Auth::attempt($credentials)) {
                Log::warning("User tried to login, but its credencials are invalid");
                throw new AppException(
                    ErrorCode::INVALID_CREDENTIALS,
                    userMessage: 'Credenciais inválidas'
                );
            }

            $user = Auth::user();

            // Get user permission
            $permissions = $this->permissionService->getUserPermissions($user->id);

            // Get user area
            $areas = $this->areaService->getUserAreas($user->id);

            switch ($user->status) {
                case UserStatus::INACTIVE:
                    Log::warning('User tried to login, but it is inactive');
                    throw new AppException(
                        ErrorCode::UNAUTHORIZED,
                        userMessage: 'Conta inativa. Contate o administrador.'
                    );

                case UserStatus::WAITING_APPROVAL:
                    Log::warning("User tried to login, but it is waiting for admin approval of church [{$user->church_id}]");
                    throw new AppException(
                        ErrorCode::UNAUTHORIZED,
                        userMessage: 'Conta aguardando aprovação.'
                    );

                case UserStatus::ACTIVE:
                    Log::info("User [{$user->id}] has made login");
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'user' => $user,
                            'permissions' => $permissions,
                            'areas' => $areas,
                            'access_token' => $token,
                            'token_type' => 'bearer'
                        ]
                    ]);
            }
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Login failed: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function register(Request $request){
        try {
            Log::info('Request to register user');
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'birthday' => 'required|date',
                'church_id' => 'required|exists:church,id',
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'birthday' => $data['birthday'],
                'church_id' => $data['church_id'],
                'status' => UserStatus::WAITING_APPROVAL, // sempre WA
            ]);

            Auth::login($user);
            $token = Auth::getToken();

            return $this->respondWithToken($token);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (\Exception $e) {
            Log::error("User registration failed: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }


    public function logout()
    {
        try {
            Auth::logout();
            return response()->json([
                'success' => true,
                'data' => ['message' => 'Logout feito com sucesso']
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function refresh()
    {
        try {
            return $this->respondWithToken(Auth::refresh());
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::TOKEN_EXPIRED,
                userMessage: 'Token expirado'
            );
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => Auth::factory()->getTTL() * 60
            ]
        ]);
    }

    public function registerChurch(RegisterChurchRequest $request)
    {
        try {
            Log::info('Iniciando registro de igreja');

            $data = $request->validated();
            Log::info('Dados validados com sucesso', ['data' => $data]);

            // create church
            $church = $this->churchService->create(data: [
                'name'       => $data['church_name'],
                'cep'        => $data['church_cep'],
                'street'     => $data['church_street'],
                'number'     => $data['church_number'],
                'complement' => $data['church_complement'] ?? null,
                'quarter'    => $data['church_quarter'],
                'city'       => $data['church_city'],
                'state'      => $data['church_state'],
            ]);
            Log::info('Igreja criada com sucesso', ['church_id' => $church->id]);

            // Create master user
            $user = User::create([
                'name'      => $data['user_name'],
                'email'     => $data['user_email'],
                'password'  => bcrypt($data['user_password']),
                'birthday'  => $data['user_birthday'],
                'church_id' => $church->id,
                'status'    => UserStatus::ACTIVE,
            ]);
            Log::info('Usuário master criado com sucesso', ['user_id' => $user->id]);

            // TODO: add template master and use here
            // Create permissions
            $permissionsData = [
                'user_id' => $user->id,
                'create_scale' => true,
                'read_scale'   => true,
                'update_scale' => true,
                'delete_scale' => true,

                'create_music' => true,
                'read_music'   => true,
                'update_music' => true,
                'delete_music' => true,

                'create_role' => true,
                'read_role'   => true,
                'update_role' => true,
                'delete_role' => true,

                'create_area' => true,
                'read_area'   => true,
                'update_area' => true,
                'delete_area' => true,

                'create_chat' => true,
                'read_chat'   => true,
                'update_chat' => true,
                'delete_chat' => true,

                'manage_users'           => true,
                'manage_church_settings' => true,
                'manage_app_settings'    => true,
            ];
            $this->permissionService->create($permissionsData);
            Log::info('Permissões do usuário master criadas com sucesso', ['user_id' => $user->id]);

            // autentica e retorna token
            Auth::login($user);
            $token = Auth::getToken();
            Log::info('Token gerado com sucesso', ['user_id' => $user->id]);

            // Get user permission
            $permissions = $this->permissionService->getUserPermissions($user->id);

            // Get user area
            $areas = $this->areaService->getUserAreas($user->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'permissions' => $permissions,
                    'areas' => $areas,
                    'church_id' => $church->id,
                    'access_token' => $token,
                    'token_type' => 'bearer'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Church registration failed: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }
}
