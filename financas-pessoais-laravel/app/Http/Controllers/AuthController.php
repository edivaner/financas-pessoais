<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ImagemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private ImagemService $imagemService) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nome'                  => 'required|string|max:100',
            'sobrenome'             => 'nullable|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'nome'      => $data['nome'],
            'sobrenome' => $data['sobrenome'] ?? '',
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'data'    => ['token' => $token, 'user' => $user->load('imagem')],
            'message' => 'Usuário criado com sucesso.',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'data'    => ['token' => $token, 'user' => $user->load('imagem')],
            'message' => 'Login realizado.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $token->delete();
        } else {
            $request->user()->tokens()->delete();
        }
        return response()->json(['message' => 'Logout realizado.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->load('imagem')]);
    }

    public function updateUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nome'      => 'required|string|max:100',
            'sobrenome' => 'nullable|string|max:100',
        ]);

        $request->user()->update($data);

        return response()->json(['data' => $request->user()->fresh()->load('imagem'), 'message' => 'Perfil atualizado.']);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $data = $request->validate([
            'dark_mode' => 'required|boolean',
            'currency'  => 'required|string|in:BRL,USD',
        ]);

        $request->user()->update($data);

        return response()->json(['data' => $request->user()->fresh()->load('imagem'), 'message' => 'Preferências salvas.']);
    }

    public function avatar(Request $request): JsonResponse
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:10240',
        ], [
            'foto.required'  => 'Selecione uma foto.',
            'foto.image'     => 'O arquivo deve ser uma imagem.',
            'foto.mimes'     => 'Use apenas arquivos JPG ou PNG.',
            'foto.max'       => 'A foto deve ter no máximo 10 MB.',
            'foto.uploaded'  => 'Falha no upload. O arquivo pode exceder o limite do servidor PHP.',
        ]);

        $imagem = $this->imagemService->store($request->file('foto'), 'AVATAR', $request->user()->id);
        $request->user()->update(['imagem_id' => $imagem->id]);

        return response()->json(['data' => $request->user()->load('imagem'), 'message' => 'Foto atualizada.']);
    }
}
